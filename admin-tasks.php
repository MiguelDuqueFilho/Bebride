<?php

use \BeBride\PageAdmin;
use \BeBride\Model\User;
use \BeBride\Model\Events;
use \BeBride\Model\EventTask;



$app->get('/admin/events/:event_id/eventtasks/create', function($event_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$event = new Events();

	$event->getEvent((int) $event_id);

	$section_task = EventTask::getSectionTask();

	$page = new PageAdmin();

	$page->setTpl("event-task-create", array(
		"notification"=>EventTask::getNotification(),
		'search'=>$search,
		"event"=>$event->getValues(),
		'session'=>$section_task,
		'status'=>EventTask::statusTasks()
	));    

});


$app->post('/admin/events/:event_id/eventtasks/create', function($event_id) {

	User::verifyLogin(1);

	// $events = new Events();

	// $events->getEvent((int) $event_id);

	$event_task = new EventTask();

	$event_task->setValues($_POST);

	$event_task->setevent_id($event_id);

	// $event_task->settask_id('0');

	// $event_task->settask_status('0');


	$event_task->save();


	header("Location: /admin/events/".$event_id."/eventtasks");
 	exit;
});    


$app->get('/admin/events/:event_id/eventtasks/:eventtask_id/update', function($event_id,$task_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$event = new Events();

	$event->getEvent($event_id);

	$event_task = new EventTask();

	$event_task->getEventTasks( $event_id, $task_id);

	$page = new PageAdmin();

	$page->setTpl("event-task-update", array(
		"notification"=>EventTask::getNotification(),
		'search'=>$search,
		"event"=>$event->getValues(),
		'section'=>EventTask::getSectionTask(),
		'status'=>EventTask::statusTasks(),
		'eventtasks'=>$event_task->getValues()
	));    
});    


$app->post('/admin/events/:event_id/eventtasks/:eventtask_id/update', function($event_id,$task_id) {

	User::verifyLogin(1);

	$event_task = new EventTask();

	$event_task = new EventTask();

	$event_task->getEventTasks( $event_id, $task_id);

	$event_task->setValues($_POST);

	$event_task->save();


	header("Location: /admin/events/".$event_id."/eventtasks");
 	exit;
});    



$app->get('/admin/events/:event_id/eventtasks/:eventtask_id/delete', function($event_id,$task_id) {

	User::verifyLogin(1);

	$event_task = new EventTask();

	$event_task->setevent_id($event_id);
	$event_task->settask_id($task_id);


	$event_task->delete();

	$event_task->setNotification("Tarefa excluida com sucesso.",'success');

	header("Location: /admin/events/".$event_id."/eventtasks");
	exit;
});


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
 

?>