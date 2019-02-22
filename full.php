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
            'list' => $value->name,
            'status' => $status
        ];
        $sql = "UPDATE list SET trello_id=:trello_id, list=:list, status=:status WHERE id=:id;";
        $status = $pdo->prepare($sql)->execute($row);
    }
    else{
        $row = [
            'trello_id' => $value->id,
            'list' => $value->name,
            'status' => $status
        ];
        $sql = "INSERT INTO list SET trello_id=:trello_id, list=:list, status=:status;";
        $status = $pdo->prepare($sql)->execute($row);        
        if ($status) {
            $listLastId = $pdo->lastInsertId();
            echo "<br>".$listLastId;
        }
    }

    // atualiza os cards
    
    $cardsRest = $client->request('GET', 'lists/'.$value->id.'/cards', ['query' => ['key' => $key,'token' => $token]]);
    $cards = json_decode((string)$cardsRest->getBody());
    foreach($cards as $card){
        echo "*** Card: ".$card->name."<br>";
        $cardBd = $pdo->query("select id from card c where c.trello_id = '".$card->id."'")->fetch();                     
        $dateLastActivity = new DateTime($card->dateLastActivity);
        if(!$card->dueComplete){
            $dueComplete = null;
        }

        if ($card->closed) {
            $status = 'D';
        } else {
            $status = 'A';
        }

        if(strlen($list['id']) == 0){
            $list['id'] = $listLastId;
        }
        
        //var_dump($cardBd);
        if ($cardBd) {                
            try{
                $row = [
                    'id' => (int)$cardBd['id'],
                    'trello_id' => $card->id,
                    'descri' => utf8_decode($card->desc),
                    'idlist' => $list['id'],
                    'idboard' => $board_bd,
                    'nome' => utf8_decode($card->name),
                    'short_link' => $card->shortLink,
                    'urlcomp' => $card->url,
                    'date_last_activity' => $dateLastActivity->format('Y-m-d h:i:s'),
                    'due' => $card->due,
                    'due_complete' => $dueComplete,
                    'situacao' => $status
                ];
                //var_dump($row);
                $sql = "UPDATE card SET trello_id=:trello_id, descri=:descri, idlist=:idlist, idboard=:idboard,
                        name=:nome, short_link=:short_link, url=:urlcomp, date_last_activity=:date_last_activity,
                        due=:due, due_complete=:due_complete, status=:situacao                           
                        WHERE id=:id;";
                $status = $pdo->prepare($sql)->execute($row);
            }
            catch(PDOException $e){
                echo $e->getMessage();
            }
            
        }
        else{
            try{
                $row = [
                    'trello_id' => $card->id,
                    'descri' => utf8_decode($card->desc),
                    'idlist' => $list['id'],
                    'idboard' => $board_bd,
                    'nome' => utf8_decode($card->name),
                    'short_link' => $card->shortLink,
                    'urlcomp' => $card->url,
                    'date_last_activity' => $dateLastActivity->format('Y-m-d h:i:s'),
                    'due' => $card->due,
                    'due_complete' => $dueComplete,
                    'situacao' => $status
                ];
                $sql = "INSERT INTO card SET trello_id=:trello_id, descri=:descri, idlist=:idlist, idboard=:idboard, name=:nome, 
                        short_link=:short_link, url=:urlcomp, date_last_activity=:date_last_activity, due=:due, 
                        due_complete=:due_complete, status=:situacao;";
                $status = $pdo->prepare($sql)->execute($row);
                if ($status) {
                    $cardLastId = $pdo->lastInsertId();                    
                }
            }
            catch(PDOException $e){
                echo $e->getMessage();
            }
        }

        
        //remove labels do card
        if(strlen($cardLastId) != 0){
            $cardBd['id'] = $cardLastId;
        }
        $where = ['idcard' => $cardBd['id']];
        $pdo->prepare("DELETE FROM card_label WHERE idcard=:idcard")->execute($where);   
        // Labels
        foreach($card->idLabels as $label){
            $labelRest = $client->request('GET', 'labels/'.$label, ['query' => ['key' => $key,'token' => $token]]);
            $label = json_decode((string)$labelRest->getBody());
            echo "**** Label: ".$label->name."<br>";
            $labelBd = $pdo->query("select id from label l where l.trello_id = '".$label->id."'")->fetch();
            if ($labelBd) {
                try{
                    $row = [
                        'id' => (int)$labelBd['id'],
                        'trello_id' => $label->id,
                        'label' => utf8_decode($label->name),
                        'situacao' => 'A'
                    ];
                    $sql = "UPDATE label SET trello_id=:trello_id, label=:label, status=:situacao WHERE id=:id;";
                    $status = $pdo->prepare($sql)->execute($row);
                }
                catch(PDOException $e){
                    echo $e->getMessage();
                }
                
            } else{
                try{
                    $row = [                        
                        'trello_id' => $label->id,
                        'label' => utf8_decode($label->name),
                        'situacao' => 'A'
                    ];
                    $sql = "INSERT INTO label SET trello_id=:trello_id, label=:label, status=:situacao";
                    $status = $pdo->prepare($sql)->execute($row);
                    if ($status) {
                        $labelLastId = $pdo->lastInsertId();                    
                    }
                }
                catch(PDOException $e){
                    echo $e->getMessage();
                }
            }

            // gravar card_label       
            if(strlen($labelLastId) != 0){
                $labelBd['id'] = $labelLastId;
            }
            $row = [
                'idcard' => $cardBd['id'],
                'idlabel' => $labelBd['id']               
            ];
            $sql = "INSERT INTO card_label SET idcard=:idcard, idlabel=:idlabel";
            $status = $pdo->prepare($sql)->execute($row);
            if ($status) {
                $cardLabelLastId = $pdo->lastInsertId();                    
            }
            echo "<br>";
            sleep($espera);
        }//fim foreach label

        //checklists
        foreach($card->idChecklists as $checklist){
            $checklistRest = $client->request('GET', 'checklists/'.$checklist, ['query' => ['key' => $key,'token' => $token]]);
            $checklist = json_decode((string)$checklistRest->getBody());
            echo "**** Checklist: ".$checklist->name."<br>";
            $checklistBd = $pdo->query("select id from checklist c where c.trello_id = '".$checklist->id."'")->fetch();
            if ($checklistBd) {
                try{
                    $row = [
                        'id' => (int)$checklistBd['id'],
                        'trello_id' => $checklist->id,
                        'name' => utf8_decode($checklist->name),
                        'itens' => json_encode($checklist->checkItems),
                        'idcard' => $cardBd['id'],
                        'situacao' => 'A'
                    ];
                    $sql = "UPDATE checklist SET trello_id=:trello_id, name=:name, itens=:itens, idcard=:idcard, status=:situacao WHERE id=:id;";
                    $status = $pdo->prepare($sql)->execute($row);
                }
                catch(PDOException $e){
                    echo $e->getMessage();
                }
                
            } else{
                try{
                    $row = [                        
                        'trello_id' => $checklist->id,
                        'name' => utf8_decode($checklist->name),
                        'itens' => json_encode($checklist->checkItems),
                        'idcard' => $cardBd['id'],
                        'situacao' => 'A'
                    ];
                    $sql = "INSERT INTO checklist SET trello_id=:trello_id, name=:name, itens=:itens, idcard=:idcard, status=:situacao";
                    $status = $pdo->prepare($sql)->execute($row);
                    if ($status) {
                        $checklistLastId = $pdo->lastInsertId();                    
                    }
                }
                catch(PDOException $e){
                    echo $e->getMessage();
                }
            }            
            sleep($espera);
        }







        

        //remove members do card
        if(strlen($cardLastId) != 0){
            $cardBd['id'] = $cardLastId;
        }
        $where = ['idcard' => $cardBd['id']];
        $pdo->prepare("DELETE FROM card_member WHERE idcard=:idcard")->execute($where);      

        //busca os responsaveis do card
        foreach($card->idMembers as $member){
            $memberRest = $client->request('GET', 'members/'.$member, ['query' => ['key' => $key,'token' => $token]]);
            $member = json_decode((string)$memberRest->getBody());
            echo "**** Responsavel: ".$member->fullName."<br>";
            $memberBd = $pdo->query("select id from member m where m.trello_id = '".$member->id."'")->fetch();
            if ($memberBd) {
                $row = [
                    'id' => $memberBd['id'],
                    'trello_id' => $member->id,
                    'trello_user' => $member->username,
                    'nome' => utf8_decode($member->fullName),                    
                    'situacao' => 'A'
                ];
                $sql = "UPDATE member SET trello_id=:trello_id, name=:nome, trello_user=:trello_user,
                        status=:situacao WHERE id=:id;";
                $status = $pdo->prepare($sql)->execute($row);
            } else{
                $row = [
                    'trello_id' => $member->id,
                    'trello_user' => $member->username,
                    'nome' => utf8_decode($member->fullName),                    
                    'situacao' => 'A'
                ];
                $sql = "INSERT INTO member SET trello_id=:trello_id, name=:nome, trello_user=:trello_user,
                        status=:situacao;";
                $status = $pdo->prepare($sql)->execute($row);
                if ($status) {
                    $memberLastId = $pdo->lastInsertId();                    
                }
            }

            // gravar card_member            
            if(strlen($memberLastId) != 0){
                $memberBd['id'] = $memberLastId;
            }
            $row = [
                'idcard' => $cardBd['id'],
                'idmember' => $memberBd['id']               
            ];
            $sql = "INSERT INTO card_member SET idcard=:idcard, idmember=:idmember";
            $status = $pdo->prepare($sql)->execute($row);
            if ($status) {
                $cardMemberLastId = $pdo->lastInsertId();                    
            }
            sleep($espera);

        }//fim foreach member

        echo "<br>";
        sleep($espera);
    }//fim foreach card

 
}
unset($pdo);