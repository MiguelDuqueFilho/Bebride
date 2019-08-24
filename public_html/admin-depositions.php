<?php

use \BeBride\PageAdmin;
use \BeBride\Model\User;
use \BeBride\Model\Events;
use \BeBride\Model\Deposition;


$app->get('/admin/events/:event_id/depositions/create', function($event_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$event = new Events();

	$event->getEvent((int) $event_id);

	$page = new PageAdmin();

	$page->setTpl("event-deposition-create", array(
		"notification"=>Deposition::getNotification(),
		'search'=>$search,
		"event"=>$event->getValues()
	));    

});


$app->post('/admin/events/:event_id/depositions/create', function($event_id) {

	User::verifyLogin(1);

	if ( !isset($_POST['deposition_description']) || $_POST['deposition_description'] ==='' )
	{
		Deposition::setNotification("Preencha o Depoimento do Evento.",'warning');

		header("location: /admin/events/".$event_id."/depositions/create");
		exit;
	}

	$deposition = new Deposition();

	$deposition->setValues($_POST);

	$deposition->setevent_id($event_id);
	
	$deposition->setdeposition_id(0);

	$deposition->checkPhoto();

	$deposition->save();

	header("Location: /admin/events/".$event_id."/depositions");
 	exit;
});    


$app->get('/admin/events/:event_id/depositions/:deposition_id/update', function($event_id,$deposition_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$event = new Events();

	$event->getEvent($event_id);

	$deposition = new Deposition();

	$deposition->getDeposition( $event_id, $deposition_id);

	$page = new PageAdmin();

	$page->setTpl("event-deposition-update", array(
		"notification"=>Deposition::getNotification(),
		'search'=>$search,
		"event"=>$event->getValues(),
		'depositions'=>$deposition->getValues()
	));    
});    



$app->post('/admin/events/:event_id/depositions/:deposition_id/update', function($event_id,$deposition_id) {

	User::verifyLogin(1);

	if ( !isset($_POST['deposition_description']) || $_POST['deposition_description'] ==='' )
	{
		Deposition::setNotification("Preencha o Depoimento do Evento.",'warning');

		header("location: /admin/events/".$event_id."/depositions/".$deposition_id."/update");
		exit;
	}


	$deposition = new Deposition();

	$deposition->getDeposition( $event_id, $deposition_id);

	$deposition->setValues($_POST);

	$deposition->setPhoto($_FILES["file"]);

	$deposition->save();

	header("Location: /admin/events/".$event_id."/depositions");
 	exit;
});    


$app->get('/admin/events/:event_id/depositions/:deposition_id/delete', function($event_id,$deposition_id) {

	User::verifyLogin(1);

	$deposition = new Deposition();

	$deposition->setevent_id($event_id);

	$deposition->setdeposition_id($deposition_id);

	$deposition->delete();

	$deposition->setNotification("Depoimento excluida com sucesso.",'success');

	header("Location: /admin/events/".$event_id."/depositions");
	exit;
});


$app->get('/admin/events/:event_id/depositions', function($event_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;

	if ($search != "")
	{
		
		$pagination = Deposition::getPageSearch($event_id, $search, $page);
	}
	else
	{
		$pagination = Deposition::getPage($event_id, $page);
	}

	$event = new Events();

	$event->getEvent((int) $event_id);

	$href = '/admin/events/'.$event_id.'/depositions?';

	$pages = [];

	$pages = Deposition::calcPageMenu($page, $pagination, $search, $href);

	$page = new PageAdmin();

	$page->setTpl("event-depositions", array(
		"notification"=>Deposition::getNotification(),
		"event"=>$event->getValues(),
		"depositions"=>$pagination['data'],
		'search'=>$search,
		'pages'=>$pages
	));

});


?>