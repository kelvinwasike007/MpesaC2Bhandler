<?php

function migrations($db){
    $db->createTable('TRANSACTIONS', [
        'id'=>'PRIMARY KEY',
        'name'=>'VARCHAR',
        'amount' => 'INTEGER',
        'time'=>'VARCHAR',
        'processed'=>'VARCHAR'
    ])->migrate();
}