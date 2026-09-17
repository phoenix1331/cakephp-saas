<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddPlanIdToBusinesses extends BaseMigration
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
        $table = $this->table('businesses');
        $table->addColumn('plan_id', 'integer', [
            'default' => null,
            'limit' => 11,
            'null' => true,
        ]);
        $table->addIndex(['plan_id']);
        $table->addForeignKey('plan_id', 'plans', 'id', [
            'delete' => 'RESTRICT',
            'update' => 'CASCADE',
        ]);
        $table->update();
    }
}
