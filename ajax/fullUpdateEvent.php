<?php 
    include '../config/config-google.php';

    if(strtotime("now") > strtotime(preg_replace('/\//','-',$_POST['dateStart'])))
    {
        http_response_code(400);
        echo 'Vous ne pouvez pas mettre la date de début dans le passé';
    }
    else
    {
        $dateStart = date('Y-m-d H:i:s',strtotime(preg_replace('/\//','-',$_POST['dateStart'])));
        $dateEnd = date('Y-m-d H:i:s',strtotime(preg_replace('/\//','-',$_POST['dateEnd'])));

        if($dateStart > $dateEnd)
        {
            http_response_code(400);
            echo 'Erreur date, date de début > a la date de fin ou inversement';
        }
        else
        {
            //On récupére les reservation de la BDD pour Google
            $reqSelect = $pdo->prepare('SELECT * FROM reservation WHERE id_reservation = :id_reservation');
            $reqSelect->execute([':id_reservation'=> $_POST['id']]);
            $reservation = $reqSelect->fetch();

            //On fait les modifications sur la BDD
            $reqUpdate = $pdo->prepare('UPDATE reservation SET title = :title, date_start = :date_start, date_stop = :date_stop, id_key = :id_key, id_user = :id_user WHERE id_reservation = :id_reservation');
            $reqUpdate->execute([
                ':title' => $_POST['title'],
                ':date_start' => $dateStart,
                ':date_stop' => $dateEnd,
                ':id_key' => $_POST['key'],
                ':id_user' => $_POST['user'],
                ':id_reservation' => $_POST['id']
            ]);

            //On modifie les reservations lié a Google avec le googleId
            $event = $calendar->events->get('primary', $reservation['googleId']);
            $start = new Google_Service_Calendar_EventDateTime();
            $end = new Google_Service_Calendar_EventDateTime();
            $event->setSummary($_POST['title']);
            $start->setDateTime(preg_replace('/\s+/','T',date('Y-m-d H:i:s',strtotime(preg_replace('/\//','-',$_POST['dateStart'])))));
            $start->setTimeZone('Europe/Paris');
            $event->setStart($start);
            $end->setDateTime(preg_replace('/\s+/','T',date('Y-m-d H:i:s',strtotime(preg_replace('/\//','-',$_POST['dateEnd'])))));
            $end->setTimeZone('Europe/Paris');
            $event->setEnd($end);

            $calendar->events->update('primary', $event->getId(), $event );
            echo 'success';
        }
    }

?>