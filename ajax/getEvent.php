<?php
    include '../config/config-bdd.php';
    //On récupére les réservations
    $sql = 'SELECT * FROM reservation WHERE date_start >= :date_start AND date_stop <= :date_end';

    //On filtre par les clés si le champs est connu
    if(isset($_GET['key']))
    {
        $sql .= ' AND id_key = :id_key';
    }
    //On filtre par les utilisateurs si le champs est connu
    if(isset($_GET['user']))
    {
        $sql .= ' AND id_user = :id_user';
    }

    $dateStart = date('Y-m-d H:i:s',strtotime($_GET['start']));
    $dateEnd = date('Y-m-d H:i:s',strtotime($_GET['end']));
    $reqSelectRes = $pdo->prepare($sql);
    $reqSelectRes->bindParam(':date_start', $dateStart);
    $reqSelectRes->bindParam(':date_end', $dateEnd);
    if(isset($_GET['key']))
    {
        $reqSelectRes->bindParam(':id_key', $_GET['key']);
    }
    if(isset($_GET['user']))
    {
        $reqSelectRes->bindParam(':id_user', $_GET['user']);
    }

    $reqSelectRes->execute();

    $reservations = $reqSelectRes->fetchAll();

    foreach ($reservations as $reservation)
    {
        $json[] = ['id' => $reservation['id_reservation'], 'title' => $reservation['title'], 'start' => $reservation['date_start'], 'end' => $reservation['date_stop'], 'key' => $reservation['id_key'], 'user' => $reservation['id_user']];
    }

   echo json_encode($json);
?>