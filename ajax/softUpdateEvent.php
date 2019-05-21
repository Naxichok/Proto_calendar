<?php
   include '../config/config-google.php';

    if(strtotime("now") > strtotime($_POST['newDateStart'])-7200)
    {
        http_response_code(400);
        echo 'Vous ne pouvez pas déplacer l\'événement dans le passé';
    }
    else
    {
        //On récupére les réservations pour Google
        $reqSelect = $pdo->prepare('SELECT * FROM reservation WHERE id_reservation = :id_reservation');
        $reqSelect->execute([':id_reservation'=> $_POST['id']]);
        $reservation = $reqSelect->fetch();

        //On fait la modification dans la BDD
        $reqUpdateRes = $pdo->prepare('UPDATE reservation SET date_start = :date_start, date_stop = :date_stop WHERE id_reservation = :id_reservation');
        $reqUpdateRes->execute([
            ':date_start' => date('Y-m-d H:i:s', strtotime($_POST['newDateStart'])-7200),
            ':date_stop' => date('Y-m-d H:i:s', strtotime($_POST['newDateEnd'])-7200),
            ':id_reservation'=> $_POST['id']
            ]);
            
        //On fait la mise a jour du Google Calendar en fonction de l'event lié (si aucun alors ne fait rien)
        $event = $calendar->events->get('primary', $reservation['googleId']);
        $start = new Google_Service_Calendar_EventDateTime();
        $end = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime(preg_replace('/\s+/','T',date('Y-m-d H:i:s', strtotime($_POST['newDateStart'])-7200)));
        $start->setTimeZone('Europe/Paris');
        $event->setStart($start);
        $end->setDateTime(preg_replace('/\s+/','T',date('Y-m-d H:i:s', strtotime($_POST['newDateEnd'])-7200)));
        $end->setTimeZone('Europe/Paris');
        $event->setEnd($end);

        $calendar->events->update('primary', $event->getId(), $event );
    }

?>