<?php
include '../config/config-google.php';

//Récupération des events Google
if(isset($calendar))
{
    $optParams = ['timeMin' => $_GET['start'], 'timeMax' => $_GET['end'],'timeZone' => $_GET['timeZone']];
    $events = $calendar->events->listEvents('primary');
}

//Récupération des events en BDD
$sql = 'SELECT * FROM reservation WHERE date_start >= :date_start AND date_stop <= :date_end';
$dateStart = date('Y-m-d H:i:s',strtotime($_GET['start']));
$dateEnd = date('Y-m-d H:i:s',strtotime($_GET['end']));
$reqSelectRes = $pdo->prepare($sql);
$reqSelectRes->bindParam(':date_start', $dateStart);
$reqSelectRes->bindParam(':date_end', $dateEnd);
$reqSelectRes->execute();

$reservations = $reqSelectRes->fetchAll();



if(isset($events))
{
    foreach($events as $event)
    {
        $ajout = true;
        if($event->getStart()->getDatetime() !== null)
        {
            //Si reservations est vide alors on affiche que les events google
            if(empty($reservations))
            {
                $json[] = ['title' => $event->getSummary(),'start' => $event->getStart()->getDatetime(), 'end' => $event->getEnd()->getDatetime(),'editable' => false, 'backgroundColor' => 'red'];
            }
            else
            {
                foreach($reservations as $reservation)
                {
                    //Sinon on vérifie si les events Google et BDD sont lié , si oui on affiche seulement ceux de la BDD
                    if($event->getId() === $reservation['googleId'])
                    {
                        $ajout = false;

                    }
                }

                if($ajout)
                {
                    $json[] = ['title' => $event->getSummary(),'start' => $event->getStart()->getDatetime(), 'end' => $event->getEnd()->getDatetime(),'editable' => false, 'backgroundColor' => 'red'];
                }
                
            }
            
        }
        
    }

    echo json_encode($json);
}
?>