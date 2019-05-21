<?php
 include 'config-bdd.php';
 session_start();
 require __DIR__ . '/../vendor/autoload.php';
 $client = new Google_Client();
 $client->setAuthConfig('../credentials.json');
 $client->addScope(Google_Service_Calendar::CALENDAR);
 $client->setRedirectUri('http://localhost/sga-calendar/index.php');
 $client->setAccessType('offline');        // offline access
 $client->setIncludeGrantedScopes(true);   // incremental auth

 if(isset($_SESSION['access_token']) && $_SESSION['access_token'])
 {
     $client->setAccessToken($_SESSION['access_token']);
     $calendar = new Google_Service_Calendar($client);
 }

?>