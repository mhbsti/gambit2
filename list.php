<?php
require 'vendor/autoload.php';
require 'config.php';


use GuzzleHttp\Client;

$pdo = new PDO($dsn, $username, $password, $options);

$client = new Client(['base_uri' => $server]);


$response = $client->request('GET', 'boards/'.$board.'/lists', ['query' => ['key' => $key,'token' => $token]]);

$ret = json_decode((string)$response->getBody());


foreach ($ret as $value) {

    echo "Lista: ".$value->name."<br>";

    $list = $pdo->query("select id from list l where l.list = '".$value->name."'")->fetch();
    
    if ($value->closed) {
        $status = 'D';
    } else {
        $status = 'A';
    }

    if ($list) {
        $row = [
            'id' => $list['id'],
            'trello_id' => $value->id,
            'board_id' => $board_bd,
            'list' => $value->name,
            'status' => $status
        ];
        $sql = "UPDATE list SET trello_id=:trello_id, list=:list, board_id=:board_id, status=:status WHERE id=:id;";
        $status = $pdo->prepare($sql)->execute($row);
    }
    else{
        $row = [
            'trello_id' => $value->id,
            'board_id' => $board_bd,
            'list' => $value->name,
            'status' => $status
        ];
        $sql = "INSERT INTO list SET trello_id=:trello_id, list=:list, board_id=:board_id, status=:status;";
        $status = $pdo->prepare($sql)->execute($row);        
        if ($status) {
            $listLastId = $pdo->lastInsertId();
            echo "<br>".$listLastId;
        }
    }

}
unset($pdo);