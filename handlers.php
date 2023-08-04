<?php
function addTransaction($db,$data){
    $db->insert('TRANSACTIONS', $data)->execute();
    return true;
}

function listTransactions($db){
    $list = $db->select('*')->from('TRANSACTIONS')->execute();

    return $list->fetchAll(PDO::FETCH_ASSOC);
}

function queue($db){
    $list = $db->select('*')->from('TRANSACTIONS')->where('processed', '=' ,'false')->execute();

    return $list->fetchAll(PDO::FETCH_ASSOC);
}

function process($db,$id){
    $param = [
        'processed' => 'true'
    ];
    $db->update('TRANSACTIONS', $param)->where('id', '=', $id)->execute();
    return true;
}