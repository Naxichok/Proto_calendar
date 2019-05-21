<?php
    include '../config/config-google.php';

    if(strtotime("now") > strtotime($_POST['dateStart']))
    {
        http_response_code(400);
        echo 'Vous ne pouvez pas créer d\'événement dands le passé';
    }
    else
    {
        $googleId = hash('sha256', rand(0, getrandmax()));
        $reqInsertRes       = $pdo->prepare('INSERT INTO reservation (id_key, id_user, title, date_start, date_stop, googleId) VALUES (:id_key, :id_user, :title, :date_start, :date_stop, :googleId)');
        $reqSelectKeyName   = $pdo->prepare('SELECT name_key FROM key WHERE id_key = :id_key');
        $reqSelectUserName  = $pdo->prepare('SELECT lastname_user, firstname_user FROM user WHERE id_user = :id_user');
        $reqSelectKeyName->execute([
            ':id_key' => $_POST['key']
            ]);
        $reqSelectUserName->execute([
            ':id_user' => $_POST['user']
            ]);
        $key    = $reqSelectKeyName->fetch(PDO::FETCH_ASSOC);
        $user   = $reqSelectUserName->fetch(PDO::FETCH_ASSOC);
    
        $title = $user['lastname_user']." ".$user['firstname_user']." <> ".$key['name_key'];
        
        $reqInsertRes->execute([
            ':id_key' => $_POST['key'],
            ':id_user' => $_POST['user'],
            ':title' => $title,
            ':date_start' => date('Y-m-d H:i:s',strtotime($_POST['dateStart'] )),
            ':date_stop' => date('Y-m-d H:i:s',strtotime($_POST['dateEnd'] )),
            ':googleId' => $googleId
            ]);
        $lastId = $pdo->lastInsertId();
        
        
        
    
        $dates  = ['dateStart' => date('Y-m-d H:i:s',strtotime($_POST['dateStart'] )), 'dateEnd' => date('Y-m-d H:i:s',strtotime($_POST['dateEnd'] ))];   
        
        
        $event = new Google_Service_Calendar_Event([
            'summary' => $title,
            'start' => [
            'dateTime' => $_POST['dateStart'],
            'timeZone' => 'Europe/Paris'
            ],
            'end' => [
            'dateTime' => $_POST['dateEnd'],
            'timeZone' => 'Europe/Paris'
            ],
            'id' => $googleId
        ]);

        $event = $calendar->events->insert('primary', $event);

        $json = ['title' => $title, 'id' =>  $lastId ,'dates' => $dates, 'key_id' => $_POST['key'], 'user_id' => $_POST['user'], 'googleId' => $googleId];

        echo json_encode($json);
    }
    
?>
