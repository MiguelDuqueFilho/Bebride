<?php

use \BeBride\PageAdmin;
use \BeBride\Model\User;
use \BeBride\Model\Events;
use \BeBride\Model\EventGuest;


$app->get('/admin/events/:event_id/eventguests/create', function($event_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$event = new Events();

	$event->getEvent((int) $event_id);

	$page = new PageAdmin();

	$page->setTpl("event-guest-create", array(
		"notification"=>EventGuest::getNotification(),
		'search'=>$search,
		"event"=>$event->getValues(),
		"guestgroup"=>EventGuest::getGuestGroup(),		
		"guesttype"=>EventGuest::getGuestType()
	));    

});


$app->post('/admin/events/:event_id/eventguests/create', function($event_id) {

	User::verifyLogin(1);

	if ( !isset($_POST['eventguest_name']) || $_POST['eventguest_name'] ==='' )
	{
		EventGuest::setNotification("Preencha o nome Convidado do Evento.",'warning');

		header("location: /admin/events/".$event_id."/eventguests/create");
		exit;
	}

	$eventguest = new EventGuest();

	$eventguest->setValues($_POST);

	$eventguest->setevent_id($event_id);
	
	$eventguest->seteventguest_id(0);

	$eventguest->save();

	header("Location: /admin/events/".$event_id."/eventguests");
 	exit;
});    


$app->get('/admin/events/:event_id/eventguests/:eventguest_id/update', function($event_id,$eventguest_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$event = new Events();

	$event->getEvent($event_id);

	$eventguest = new EventGuest();

	$eventguest->getEventGuest( $event_id, $eventguest_id);

	$page = new PageAdmin();

	$page->setTpl("event-guest-update", array(
		"notification"=>EventGuest::getNotification(),
		'search'=>$search,
		"event"=>$event->getValues(),
		"guestgroup"=>EventGuest::getGuestGroup(),		
		"guesttype"=>EventGuest::getGuestType(),
		'eventguests'=>$eventguest->getValues()
	));    
});    



$app->post('/admin/events/:event_id/eventguests/:eventguest_id/update', function($event_id,$eventguest_id) {

	User::verifyLogin(1);

	if ( !isset($_POST['eventguest_name']) || $_POST['eventguest_name'] ==='' )
	{
		EventGuest::setNotification("Preencha o nome Convidado do Evento.",'warning');

		header("location: /admin/events/".$event_id."/eventguests/".$eventguest_id."/update");
		exit;
	}

	$eventguest = new EventGuest();

	$eventguest->getEventGuest( $event_id, $eventguest_id);

	$eventguest->setValues($_POST);

	$eventguest->setPhoto($_FILES["file"]);

	$eventguest->save();

	header("Location: /admin/events/".$event_id."/eventguests");
 	exit;
});    


$app->get('/admin/events/:event_id/eventguests/:eventguest_id/delete', function($event_id,$eventguest_id) {

	User::verifyLogin(1);

	$eventguest = new EventGuest();

	$eventguest->setevent_id($event_id);

	$eventguest->seteventguest_id($eventguest_id);

	$eventguest->delete();

	$eventguest->setNotification("Convidado excluida com sucesso.",'success');

	header("Location: /admin/events/".$event_id."/eventguests");
	exit;
});


$app->get('/admin/events/:event_id/eventguests', function($event_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;

	if ($search != "")
	{
		
		$pagination = EventGuest::getPageSearch($event_id, $search, $page);
	}
	else
	{
		$pagination = EventGuest::getPage($event_id, $page);
	}

	$event = new Events();

	$event->getEvent((int) $event_id);

	$href = '/admin/events/'.$event_id.'/eventguests?';

	$pages = [];

	$pages = EventGuest::calcPageMenu($page, $pagination, $search, $href);

	$page = new PageAdmin();

	$page->setTpl("event-guests", array(
		"notification"=>EventGuest::getNotification(),
		"event"=>$event->getValues(),
		"eventguests"=>$pagination['data'],
		'search'=>$search,
		'pages'=>$pages
	));

});


?>