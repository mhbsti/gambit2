<?php
phpinfo();
/*
require 'vendor/autoload.php';
require 'config.php';

use GuzzleHttp\Client;

$client = new Client(['base_uri' => $server]);
$response = $client->request('GET', 'boards/'.$board.'/lists', ['query' => ['key' => $key,'token' => $token]]);

$ret = json_decode((string)$response->getBody());
foreach ($ret as $value) {
    echo "Lista: ".$value->name."<br>";
    $list = $pdo->query("select id from list l where l.list = '".$value->name."'")->fetch();
    if (!$list) {
        $row = [
            'trello_id' => $value->id,
            'list' => $value->name,
            'status' => 'A'
        ];
        $sql = "INSERT INTO list SET trello_id=:trello_id, list=:list, status=:status;";
        $status = $pdo->prepare($sql)->execute($row);        
        if ($status) {
            $listLastId = $pdo->lastInsertId();
            echo $listLastId;
        }
    }
    unset($row);
    unset($sql);
    unset($status);
    
    if($value->name == "DOING")
    {
        $cardsRest = $client->request('GET', 'lists/'.$value->id.'/cards', ['query' => ['key' => $key,'token' => $token]]);
        $cards = json_decode((string)$cardsRest->getBody());
        foreach($cards as $card){
            echo "*** Card: ".$card->name."<br>";
            $cardBd = $pdo->query("select * from card c where c.trello_id = '".$card->id."'")->fetch();                     
            $dateLastActivity = new DateTime($card->dateLastActivity);
            //echo $dateLastActivity->format('Y-m-d h:i:s')."<br>";
            //echo $card->desc;
            if(!$card->dueComplete){
                $dueComplete = null;
            }

            if (!$cardBd) {
                try{
                    $row = [
                        'trello_id' => $card->id,
                        'descri' => utf8_decode($card->desc),
                        'idlist' => 3,
                        'idboard' => $board_bd,
                        'nome' => utf8_decode($card->name),
                        'short_link' => $card->shortLink,
                        'urlcomp' => $card->url,
                        'date_last_activity' => $dateLastActivity->format('Y-m-d h:i:s'),
                        'due' => $card->due,
                        'due_complete' => $dueComplete,
                        'situacao' => 'A'
                    ];
                    $sql = "INSERT INTO card SET trello_id=:trello_id, descri=:descri, idlist=:idlist, idboard=:idboard, name=:nome, 
                            short_link=:short_link, url=:urlcomp, date_last_activity=:date_last_activity, due=:due, due_complete=:due_complete, status=:situacao;";
                    $status = $pdo->prepare($sql)->execute($row);
                }
                catch(PDOException $e){
                    echo $e->getMessage();
                }
                
            }
            echo "<br>";

            /*
            //responsavel
            foreach($card->idMembers as $member){
                $memberRest = $client->request('GET', 'members/'.$member, ['query' => ['key' => $key,'token' => $token]]);
                $member = json_decode((string)$memberRest->getBody());
                echo "**** Responsavel: ".$member->fullName."<br>";
            }
            // Labels
            foreach($card->idLabels as $label){
                $labelRest = $client->request('GET', 'labels/'.$label, ['query' => ['key' => $key,'token' => $token]]);
                $label = json_decode((string)$labelRest->getBody());
                echo "**** Label: ".$label->name."<br>";
            }
            //comentarios
            foreach($card->idLabels as $label){
                $labelRest = $client->request('GET', 'labels/'.$label, ['query' => ['key' => $key,'token' => $token]]);
                $label = json_decode((string)$labelRest->getBody());
                echo "**** Label: ".$label->name."<br>";
            }
           

        }
    }
    
}
 */
