<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateAvailabilities extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/guides/writing-migrations/migration-methods.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('availabilities');
        $table->addColumn('user_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => false,
        ]);
        $table->addColumn('day_of_week', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
        ]);
        $table->addColumn('date', 'date', [
            'default' => null,
            'null' => true,
        ]);
        $table->addColumn('start_time', 'time', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('end_time', 'time', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('is_available', 'boolean', [
            'default' => true,
            'null' => false,
        ]);
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        $table->addIndex(['user_id']);
        $table->addForeignKey('user_id', 'users', 'id', [
            'delete' => 'CASCADE',
            'update' => 'CASCADE',
        ]);
        $table->create();
    }
}
