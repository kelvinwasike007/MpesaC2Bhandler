<?php
header('Content-Type : application/json');
require 'DB.php';
require 'handlers.php';
require 'migrations.php';
if(!isset($_GET["client"])){
    die(json_encode(['msg'=>'Unknown Client Please check your client query or register a new client']));
}

if(!in_array($_GET['client'], scandir('databases/')) && $_GET['mode'] !== 'new'){
    die(json_encode(['msg'=>'Unknown client']));
}

$db = new DB('databases/'.$_GET['client']);

if($_GET["mode"] == "confirm"){
    $body = json_decode(file_get_contents('php://input'), true);
    $data = [
        'id'=>$body['TransID'],
        'name'=>$body['FirstName'].' '.$body['MiddleName'].' '.$body['LastName'] ,
        'time'=>$body['TransTime'],
        'amount'=>$body['TransAmount'],
        'processed'=>'false'
    ];
    addTransaction($db, $data);

    echo json_encode(['msg'=>'Saved']);
}

if($_GET["mode"] == "list"){
    echo json_encode(listTransactions($db));
}

if($_GET["mode"]=="queue"){
    echo json_encode(queue($db));
}

if($_GET["mode"]=="update"){
    process($db, $_GET["id"]);
    echo json_encode(["msg"=>"processed"]);
}

if($_GET["mode"]=='new'){
    $driver = new SQLite3('databases/'.$_GET['client']);
    $driver->exec('create table init(active)');
    $db = new DB('databases/'.$_GET['client']);
    migrations($db);
    echo json_encode(['msg'=>'Client Registered']);
}