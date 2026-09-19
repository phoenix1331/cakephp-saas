<?php
declare(strict_types=1);

namespace App\Booking;

use Cake\I18n\DateTime;

/**
 * Builds a single-event .ics (RFC 5545) calendar file for a Booking
 * confirmation email. Deliberately hand-rolled rather than a library
 * dependency - a single VEVENT with no recurrence or timezone complexity
 * beyond UTC is simple enough to get right directly, and the format is
 * fully specified (line folding at 75 octets, CRLF line endings, text
 * escaping), so "simple" here doesn't mean "loose".
 */
class IcsBuilder
{
    /**
     * @param string $uid Globally unique identifier for this event (RFC 5545 UID).
     * @param string $summary Event title.
     * @param string $description Event description.
     * @param \Cake\I18n\DateTime $start Event start, in UTC.
     * @param \Cake\I18n\DateTime $end Event end, in UTC.
     * @param string|null $location Optional event location.
     */
    public function __construct(
        protected string $uid,
        protected string $summary,
        protected string $description,
        protected DateTime $start,
        protected DateTime $end,
        protected ?string $location = null,
    ) {
    }

    /**
     * Renders the .ics file contents.
     *
     * @return string
     */
    public function build(): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//SlotWise//Booking Confirmation//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $this->escape($this->uid),
            'DTSTAMP:' . $this->formatUtc(DateTime::now()),
            'DTSTART:' . $this->formatUtc($this->start),
            'DTEND:' . $this->formatUtc($this->end),
            'SUMMARY:' . $this->escape($this->summary),
            'DESCRIPTION:' . $this->escape($this->description),
        ];

        if ($this->location !== null) {
            $lines[] = 'LOCATION:' . $this->escape($this->location);
        }

        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return implode(
            "\r\n",
            array_map($this->fold(...), $lines),
        ) . "\r\n";
    }

    /**
     * @param \Cake\I18n\DateTime $dateTime The value to format.
     * @return string RFC 5545 UTC date-time, e.g. 20260321T140000Z.
     */
    protected function formatUtc(DateTime $dateTime): string
    {
        return $dateTime->setTimezone('UTC')->format('Ymd\THis\Z');
    }

    /**
     * Escapes RFC 5545 TEXT value special characters.
     *
     * @param string $value Raw text.
     * @return string
     */
    protected function escape(string $value): string
    {
        return str_replace(
            ['\\', "\n", ',', ';'],
            ['\\\\', '\\n', '\\,', '\\;'],
            $value,
        );
    }

    /**
     * Folds a content line to at most 75 octets per line, per RFC 5545 -
     * continuation lines start with a single space.
     *
     * @param string $line An unfolded content line.
     * @return string
     */
    protected function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $folded = [];
        $chunk = '';
        foreach (str_split($line) as $char) {
            if (strlen($chunk) >= 74) {
                $folded[] = $chunk;
                $chunk = '';
            }
            $chunk .= $char;
        }
        $folded[] = $chunk;

        return implode("\r\n ", $folded);
    }
}
