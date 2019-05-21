<?php 
    include '../config/config-google.php';

    //On récupére les reservations   
    $reqSelectEvent = $pdo->prepare('SELECT * FROM reservation WHERE id_reservation = :id_reservation');

    $reqSelectEvent->execute(['id_reservation' => $_POST['id']]);

    $reservation = $reqSelectEvent->fetch();

    //On vérifie que la réservation que l'on veux supprimer n'est pas en cours
    if(strtotime('now') >= strtotime($reservation['date_start']) && strtotime('now') <= strtotime($reservation['date_stop']))
    {
        http_response_code(400);
        echo 'Vous ne pouvez pas annuler une réservation en cours';
    }
    else
    {  
        //On la supprime en BDD et sur Google
        $reqDeleteEvent = $pdo->prepare('DELETE FROM reservation WHERE id_reservation = :id_reservation');
        $calendar->events->delete('primary', $reservation['googleId']);
        $reqDeleteEvent->execute([':id_reservation' => $_POST['id']]);
       
    }

    
?>