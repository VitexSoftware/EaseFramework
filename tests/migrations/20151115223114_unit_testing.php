<?php

use Phinx\Migration\AbstractMigration;

class UnitTesting extends AbstractMigration
{

    public function change()
    {
        $table = $this->table('test');
        $table->addColumn('name', 'string', ['limit' => 32])
            ->addColumn('date', 'datetime')
            ->create();
    }

}
