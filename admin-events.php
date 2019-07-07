<?php

use \BeBride\PageAdmin;
use \BeBride\Model\User;
use \BeBride\Model\Events;


$app->get('/admin/events', function() {
	
	User::verifyLogin(1);	

    $search = (isset($_GET['search'])) ? $_GET['search'] : "";

    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;
    
    if ($search != "")
    {
        $pagination = Events::getPageSearch($search, $page);
    }
    else
    {
        $pagination = Events::getPage($page);
    }
    
    $pages = [];
    
    $pages = Events::calcPageMenu($page, $pagination, $search);
    
    
    $page = new PageAdmin();
    
    $page->setTpl("events", array(
        "notification"=>Events::getNotification(),
        "events"=>$pagination['data'],
        'search'=>$search,
        'pages'=>$pages
    ));

});



$app->get('/admin/events/:event_id/delete', function($event_id) {

	User::verifyLogin(1);

	$user = new Events();

	$user->getEvents((int) $event_id);

	$user->delete();

	$user->setNotification("Evento excluido com sucesso.",'success');

	header("Location: /admin/events");
	exit;
});

$app->get('/admin/events/create', function() {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

    $eventstype = '';
    $eventsstatus = '';

	$page = new PageAdmin();

	$page->setTpl("event-create", array(
		"notification"=>Events::getNotification(),
        'search'=>$search,
        'eventstype'=>$eventstype->getValues(),
        'eventsstatus'=>$eventsstatus->getValues()
	));

});


$app->post("/admin/events/create", function () {

 	User::verifyLogin(1);

	$user = new Events();

 	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

 	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

 		"cost"=>12

	 ]);
	 
 	$user->setValues($_POST);

	$user->save();

	header("Location: /admin/events");
 	exit;

});


$app->get('/admin/events/:user_id/update', function($event_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";


    $eventstype = Events::getEventsType();
    
    $eventsstatus = Events::getStatusType();

	$event = new Events();

    $event->getEvent((int) $event_id);
    
	$page = new PageAdmin();

	$page->setTpl("event-update", array(
		"notification"=>Events::getNotification(),
		'search'=>$search,
        'eventstype'=>$eventstype,
        'eventsstatus'=>$eventsstatus,
		"event"=>$event->getValues()
	));

});

$app->post('/admin/events/:event_id/update', function($event_id) {

	User::verifyLogin(1);

	$event = new Events();

	$event->getEvent((int) $event_id);

	$event->setValues($_POST);	

	$event->save();

	header("Location: /admin/events");
	exit;
});


?>