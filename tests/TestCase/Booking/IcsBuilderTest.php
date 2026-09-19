<?php
declare(strict_types=1);

namespace App\Test\TestCase\Booking;

use App\Booking\IcsBuilder;
use Cake\I18n\DateTime;
use Cake\TestSuite\TestCase;

class IcsBuilderTest extends TestCase
{
    public function testBuildProducesAValidSingleEventCalendar(): void
    {
        $ics = new IcsBuilder(
            uid: 'booking-1@slotwise.local',
            summary: 'Haircut at Alpha Hair Studio',
            description: 'Haircut with Alpha Hair Studio.',
            start: new DateTime('2026-09-21 09:00:00', 'UTC'),
            end: new DateTime('2026-09-21 09:30:00', 'UTC'),
            location: 'Alpha Hair Studio',
        );

        $output = $ics->build();

        $this->assertStringStartsWith("BEGIN:VCALENDAR\r\n", $output);
        $this->assertStringEndsWith("END:VCALENDAR\r\n", $output);
        $this->assertStringContainsString('UID:booking-1@slotwise.local', $output);
        $this->assertStringContainsString('DTSTART:20260921T090000Z', $output);
        $this->assertStringContainsString('DTEND:20260921T093000Z', $output);
        $this->assertStringContainsString('SUMMARY:Haircut at Alpha Hair Studio', $output);
        $this->assertStringContainsString('LOCATION:Alpha Hair Studio', $output);
    }

    public function testConvertsANonUtcDateTimeToUtc(): void
    {
        $ics = new IcsBuilder(
            uid: 'booking-2@slotwise.local',
            summary: 'Test',
            description: 'Test',
            start: new DateTime('2026-09-21 09:00:00', 'Europe/London'),
            end: new DateTime('2026-09-21 09:30:00', 'Europe/London'),
        );

        $output = $ics->build();

        // Europe/London is BST (+1) in September, so 09:00 local is 08:00 UTC.
        $this->assertStringContainsString('DTSTART:20260921T080000Z', $output);
    }

    public function testEscapesCommasSemicolonsNewlinesAndBackslashesInText(): void
    {
        $ics = new IcsBuilder(
            uid: 'booking-3@slotwise.local',
            summary: 'Consult, review; notes\\path',
            description: "Line one\nLine two",
            start: new DateTime('2026-09-21 09:00:00', 'UTC'),
            end: new DateTime('2026-09-21 09:30:00', 'UTC'),
        );

        $output = $ics->build();

        $this->assertStringContainsString('SUMMARY:Consult\\, review\\; notes\\\\path', $output);
        $this->assertStringContainsString('DESCRIPTION:Line one\\nLine two', $output);
    }

    public function testFoldsLongLinesAt75OctetsWithALeadingSpaceContinuation(): void
    {
        $longSummary = str_repeat('A', 200);
        $ics = new IcsBuilder(
            uid: 'booking-4@slotwise.local',
            summary: $longSummary,
            description: 'Test',
            start: new DateTime('2026-09-21 09:00:00', 'UTC'),
            end: new DateTime('2026-09-21 09:30:00', 'UTC'),
        );

        $output = $ics->build();
        $lines = explode("\r\n", $output);

        foreach ($lines as $line) {
            $this->assertLessThanOrEqual(75, strlen($line));
        }

        // A folded continuation line starts with a single space.
        $summaryLineIndex = null;
        foreach ($lines as $index => $line) {
            if (str_starts_with($line, 'SUMMARY:')) {
                $summaryLineIndex = $index;
                break;
            }
        }
        $this->assertNotNull($summaryLineIndex);
        $this->assertStringStartsWith(' ', $lines[$summaryLineIndex + 1]);
    }

    public function testOmitsLocationWhenNotProvided(): void
    {
        $ics = new IcsBuilder(
            uid: 'booking-5@slotwise.local',
            summary: 'Test',
            description: 'Test',
            start: new DateTime('2026-09-21 09:00:00', 'UTC'),
            end: new DateTime('2026-09-21 09:30:00', 'UTC'),
        );

        $this->assertStringNotContainsString('LOCATION:', $ics->build());
    }
}
