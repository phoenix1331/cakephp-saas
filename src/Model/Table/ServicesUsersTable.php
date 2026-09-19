<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * The services_users join table backing Services<->Users belongsToMany.
 * No application code queries this directly - it exists only so fixtures
 * (which need a Table class to reflect schema from, since this app disables
 * the generic fallback Table class) can describe the join table's columns.
 */
class ServicesUsersTable extends Table
{
    /**
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('services_users');
    }
}
