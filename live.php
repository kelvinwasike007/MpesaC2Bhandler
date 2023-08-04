<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$params = $_GET;

$client = $params['client'];
$host = 'http://localhost:8080/';
//check if client exists

if(!in_array($client, scandir('databases/'))){
    die(json_encode(['msg'=>'Uknown client']));
}

function sendSSE($data) {
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

#2 modes
#whole transaction list

if($params["mode"] == "list"){
    while (true) {
        $ret = file_get_contents($host.'?mode=list&client='.$client);
        sendSSE(["msg"=>"get"]);
        sleep(2);
    }
}

#Queued for processin transaction list
?>
