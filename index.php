<?php
  require __DIR__ . '/vendor/autoload.php';
  session_start();
  include 'config/config-bdd.php';

  //Récupération de la liste des utilisateurs
  $reqSelectUser = $pdo->prepare('SELECT * FROM user');
  $reqSelectUser->execute();

  //Récupération de la liste des clés
  $reqSelectKey = $pdo->prepare('SELECT * FROM eucles_key');
  $reqSelectKey->execute();

  //Récupération des réservations
  $reqSelectRes = $pdo->prepare('SELECT * FROM reservation');
  $reqSelectRes->execute();

  $users = $reqSelectUser->fetchAll();
  $keys = $reqSelectKey->fetchAll();
  $reservations = $reqSelectRes->fetchAll();

  $datetime = new DateTime('now');
  $datetime_string = $datetime->format('c');

  //Connexion a l'api Google
  $client = new Google_Client();
  $client->setAuthConfig('credentials.json');
  $client->addScope(Google_Service_Calendar::CALENDAR);
  $client->setRedirectUri('http://localhost/sga-calendar/index.php');
  $client->setAccessType('offline');        // offline access
  $client->setIncludeGrantedScopes(true);   // incremental auth

  $auth_url = $client->createAuthUrl();

  if(isset($_GET['code'])){
      $client->authenticate($_GET['code']);
      $_SESSION['access_token'] = $client->getAccessToken();
      header('Location: index.php');
  }
  
?>

<!DOCTYPE html>
<html lang='fr'>
  <head>
    <meta charset='utf-8' />

    <link href='fullcalendar/packages/core/main.css' rel='stylesheet' />
    <link href='fullcalendar/packages/daygrid/main.css' rel='stylesheet' />
    <link href='fullcalendar/packages/timegrid/main.css' rel='stylesheet' />
    <link href='css/bootstrap.min.css' rel='stylesheet'>
    <link href='css/tempusdominus-bootstrap-4.min.css' rel='stylesheet'>  
    <link href='css/style.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">

    <script src='fullcalendar/packages/core/main.js'></script>
    <script src='fullcalendar/packages/daygrid/main.js'></script>
    <script src='fullcalendar/packages/interaction/main.js'></script>
    <script src='fullcalendar/packages/timegrid/main.js'></script>
    <script src='js/jquery.js'></script>  
    <script src='js/moment.min.js'></script>
    <script src='js/bootstrap.min.js'></script>
    <script src='js/transition.min.js'></script>
    <script src='js/popper.js'></script>
    <script src='js/tooltip.min.js'></script>
    <script src='js/tempusdominus-bootstrap-4.min.js'></script>
    

    
  </head>
  <body>
    <?php
      require_once 'modalEvent.php';
    ?>
      <a class="btn btn-primary" href="<?php echo $auth_url; ?>">Lier mon compte google</a><a href="deconnexion.php" class="btn btn-danger">Déconnexion</a>
      <div class="row">
        <div class="col-lg-1">
          
          <h2 class="center">Utilisateur</h2>
            <?php
              foreach ($users as $user){
                echo '<div data-id='.$user['id_user'].'  class="user center">'.$user['lastname_user'].' '.$user['firstname_user'].'</div>';
              }
            ?>
        </div>
        <div class="col-lg-10" data-user="" data-key="" id='calendar'></div>
        <div class="col-lg-1">
          <h2 class="center">Clé</h2>
           <?php
              foreach ($keys as $key){
                echo '<div data-id='.$key['id_key'].' class="key center">'.$key['name_key'].'</div>';
              }
            ?>
        </div>
        
      </div>
      

  </body>
  <script>     
      $(document).ready(function() {
        //Configuration du datetimepicker au format fr
        $(".datetimepicker").datetimepicker({
          locale: 'fr'
        })
        //source des événement de la BDD
        var source = 'ajax/getEvent.php';
        //source des événement Google
        var googleCalendar = 'ajax/getGoogleCalendar.php'
        var calendarEl = document.getElementById('calendar');       
        var array_events = [source, googleCalendar];
        var clickKey = false;
        var clickUser = false;
        var testKey = null;
        var testUser = null;
        var tooltip = null;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            timeZone: 'Europe/Paris',
            locale: 'fr',
            plugins: [ 'interaction', 'dayGrid', 'timeGrid' ],
            defaultView: 'timeGridWeek',
            nowIndicator: true,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            //Empêche d'empiler deux événement avec le même attribut "key"
            eventOverlap: function(stillEvent, movingEvent){
                return (stillEvent.extendedProps.key !== movingEvent.extendedProps.key && stillEvent.extendedProps.user !== movingEvent.extendedProps.user);
            },
            //Ajoute l'attribut "key" au événement
            eventRender: function (info){
              info.el.setAttribute('key', info.event.extendedProps.key);
            },
            //Affiche un tooltip au passage de la souris
            eventMouseEnter: function(mouserEnterInfo){
              tooltip = new Tooltip(mouserEnterInfo.el,{
                title: mouserEnterInfo.event.title,
                trigger: 'hover',
                placement: 'top',
                container: 'body'
              });
              tooltip.show();
            },
            //Retire la tooltip a la sortie de la souris
            eventMouseLeave: function(mouseLeaveInfo){
              tooltip.dispose();
            },
            eventSources: array_events,
            editable: true,
            selectable: true,
            firstDay: 1,
            allDaySlot: false,       
            // Action lors du clique sur une case   
            select: function(info){
              if($('#calendar').attr('data-user') === "" || $('#calendar').attr('data-key') === "")
              {
                alert("Veuillez sélectionner un Utilisateur ET une clé avant de la réserver");
              }
              else
              {
                $.ajax({
                 url: 'ajax/addEvent.php',
                 type: 'POST',
                 data: {
                   'user' : $('#calendar').attr('data-user'), 
                   'key': $('#calendar').attr('data-key'), 
                   'dateStart' : info.startStr,
                   'dateEnd' : info.endStr
                  },
                  success: function(response){
                    //reload les événement après le changement
                    calendar.refetchEvents();                   
                  },
                  error: function(xhr, ajaxOptions, thrownError){
                    alert(xhr.responseText);
                  }               
               })
              }
            },
            //action lorsqu'on lache un événement
            eventDrop: function(info){
                updateEvent(info);
            },
            //action lorsqu'on agrandi un événement
            eventResize: function(info){
                updateEvent(info);
            },
            //affichage de la modal lors du clique sur l'événement
            eventClick: function(eventClickInfo){
              console.log(eventClickInfo.event.id);
              $('.modal-title').text(eventClickInfo.event.title);
              $('.modal-title').attr('data-id', eventClickInfo.event.id);
              $('#title').val(eventClickInfo.event.title);
              $('#date_start').val(eventClickInfo.event.start.toLocaleString('fr-FR', {timeZone: 'UTC'}).replace(' à',''));
              $('#date_end').val(eventClickInfo.event.end.toLocaleString('fr-FR', {timeZone: 'UTC'}).replace(' à',''));
              $('#key option[value="' + eventClickInfo.event.extendedProps.key +'"]').attr('selected','selected');
              $('#user option[value="' + eventClickInfo.event.extendedProps.user +'"]').attr('selected','selected');
              $('#modalEvent').modal('show')
            }        
        });
        calendar.render();

        function updateEvent(info)
        {      
          //softUpdateEvent.php sert a modifier seulement les dates.
          $.ajax({
            url: 'ajax/softUpdateEvent.php',
            type: 'POST',
            data: {
              'id': info.event.id,
              'newDateStart': info.event.start,
              'newDateEnd': info.event.end
            },
            error: function(xhr, ajaxOptions, thrownError){
              alert(xhr.responseText);
              calendar.refetchEvents();
            }
          })
        }
        
        //Action quand on clique sur un utilisateur
        $('.user').click(function(){
          //Si l'utilisateur n'est pas séléctionner
          if(!$(this).hasClass('selectedUser'))
          {
            //On change le css pour afficher de la couleur
            testUser = $(this).attr('data-id');
            $('#calendar').attr('data-user', testUser);
            $('.selectedUser').removeClass('selectedUser');
            $(this).addClass('selectedUser');

            //On retire les sources des événements
            calendar.getEventSources().forEach(function(element){
              element.remove();
            })

            clickUser = true;    
            //On change la source pour la BDD afin de récupérer seulement les événement de l'utilisateur     
            source = changeUrl(clickKey,clickUser,testKey, testUser);
            //On réajoute les sources
            calendar.addEventSource(source);
            calendar.addEventSource(googleCalendar)

          }
          else
          {
            //On retire la couleur
            testUser = null;
            $('#calendar').attr('data-user', "");
            $(this).removeClass('selectedUser');

            //On retire les sources
            calendar.getEventSources().forEach(function(element){
              element.remove();
            })

            //On les réajoute après modifications
            clickUser = false;         
            source = changeUrl(clickKey,clickUser,testKey, testUser);
            calendar.addEventSource(source);
            calendar.addEventSource(googleCalendar)

          }
         
      
        })

        //Action au clique sur une clé
        $('.key').click(function(){
          //Si la clé n'est pas séléctionner
          if(!$(this).hasClass('selectedKey'))
          {
            //On change le css pour afficher la couleur
            testKey = $(this).attr('data-id');
            $('#calendar').attr('data-key', testKey);
            $('.selectedKey').removeClass('selectedKey');
            $(this).addClass('selectedKey'); 
            clickKey = true;

            //On retire les sources
            calendar.getEventSources().forEach(function(element){
              element.remove();
            })

            //on réajoute après modifications
            source = changeUrl(clickKey,clickUser,testKey, testUser);
            calendar.addEventSource(source);
            calendar.addEventSource(googleCalendar)

          }
          else
          {
            //On retire la couleur
            testKey = null;
            $('#calendar').attr('data-key', "");
            $(this).removeClass('selectedKey');
            clickKey = false;

            // on retire les sources
            calendar.getEventSources().forEach(function(element){
              element.remove();
            })

            //on réajoute après modifications
            source = changeUrl(clickKey,clickUser,testKey, testUser);
            calendar.addEventSource(source);
            calendar.addEventSource(googleCalendar)
          }
         
        })

        //Fonction qui change l'url en fonction des modifications éffectuer
        function changeUrl(clickKey, clickUser, testKey, testUser)
        {
          if(clickKey && clickUser)
          {
            source = 'ajax/getEvent.php?key='+testKey+'&user='+testUser;
          }
          else if(clickKey && !clickUser)
          {
            source = 'ajax/getEvent.php?key='+testKey;
          }
          else if(!clickKey && clickUser)
          {
            source = 'ajax/getEvent.php?user='+testUser;
          }
          else
          {
            source = 'ajax/getEvent.php'
          }

          return source;
        }

        //Acion au clique sur le bouton Enregistrer de la modal
        $('#save').click(function(){
          //On récupére toutes les valeurs
          id = $('.modal-title').attr('data-id');
          title = $('#title').val();
          dateStart = $('#date_start').val();
          dateEnd = $('#date_end').val();
          key = $('#key option:selected').val();
          user = $('#user option:selected').val();    
          //Envoi en Ajax    
          $.ajax({
            url: 'ajax/fullUpdateEvent.php',
            type: 'POST',
            data: {
              'id': id,
              'title': title,
              'dateStart': dateStart,
              'dateEnd': dateEnd,
              'key': key,
              'user': user
            },
            success: function(response){
              //On refresh les events et on cache la modal
              calendar.refetchEvents();
              $('#modalEvent').modal('hide');
            },
            error: function(xhr, ajaxOptions, thrownError){
              alert(xhr.responseText);
            }
          })
        })

        $('#delete').click(function(){
          id = $('.modal-title').attr('data-id');
          $.ajax({
            url: 'ajax/deleteEvent.php',
            type: 'POST',
            data: {'id': id},
            success: function(response){
              calendar.refetchEvents();
              $('#modalEvent').modal('hide');
            },
            error: function(xhr, ajaxOptions, thrownError){
              alert(xhr.responseText);
            }
          })
        })

      })
    </script>
</html>