<?php

use Phinx\Migration\AbstractMigration;

class Log extends AbstractMigration
{

    public function change()
    {
       // create the table
    
        
        $table = $this->table('log');
        $table->addColumn('status', 'string',['comment'=>'info|warning|error|..'])
              ->addColumn('when', 'datetime',['comment'=>'log time'])
              ->addColumn('sender', 'string',['comment'=>'Message is Produced by'])
              ->addColumn('message', 'string',['comment'=>'Logged message itself'])
              ->create();
    }
}
