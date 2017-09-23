<?php

use Phinx\Migration\AbstractMigration;

class UnitTesting extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('test');
        $table->addColumn('name', 'string', ['limit' => 32])
            ->addColumn('datim', 'datetime')
            ->create();

        $this->query("INSERT INTO test (id, name, datim) VALUES (3, 'alpha', '2015-11-17 00:00:00'), (2, 'beta', '2015-11-18 00:00:00'); ");
    }
}
