<?php

use \BeBride\PageAdmin;
use \BeBride\Model\User;
use \BeBride\Model\Events;
use \BeBride\Model\EventTask;


$app->get('/admin/events/:event_id/eventtasks', function($event_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	
	$searchtype = (isset($_GET['searchtype'])) ? $_GET['searchtype'] : "0";

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;

	if ($search != "")
	{
		
		$pagination = EventTask::getPageSearch($event_id, $search, $searchtype, $page);
	}
	else
	{
		$pagination = EventTask::getPage($event_id, $searchtype, $page);
	}

	$event = new Events();

	$event->getEvent((int) $event_id);

	$href = '/admin/events/'.$event_id.'/eventtasks?';

	$pages = [];

	$pages = EventTask::calcPageMenu($page, $pagination, $search, $href);

	$page = new PageAdmin();

	$page->setTpl("event-tasks", array(
		"notification"=>EventTask::getNotification(),
		"event"=>$event->getValues(),
		"eventtasks"=>$pagination['data'],
		'sessiontask'=>Events::getEventsType(),
		'search'=>$search,
		'searchtype'=>$searchtype,
		'pages'=>$pages
	));

});
 

$app->get('/admin/events/:event_id/eventtasks/create', function($event_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$event = new Events();

	$event->getEvent((int) $event_id);

	$session_task = EventTask::getSessionTask();

	$page = new PageAdmin();

	$page->setTpl("event-task-create", array(
		"notification"=>EventTask::getNotification(),
		'search'=>$search,
		"event"=>$event->getValues(),
		'session'=>$session_task
	));    

});


$app->post('/admin/events/:event_id/eventtasks/create', function($event_id) {

});    


$app->get('/admin/events/:event_id/eventtasks/:eventtask_id/update', function($event_id,$eventtask_id) {

});    

?>