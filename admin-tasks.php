<?php

use \BeBride\PageAdmin;
use \BeBride\Model\User;
use \BeBride\Model\Events;
use \BeBride\Model\EventTask;
use \BeBride\Model\ModelTask;



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

	if ( !isset($_POST['task_start']) || $_POST['task_start'] ==='' )
	{
		User::setNotification("Preencha a data de início.",'warning');

		header("location: /admin/events/".$event_id."/eventtasks/create");
		exit;
	}

	if ( !isset($_POST['task_finish']) || $_POST['task_finish'] ==='' )
	{
		User::setNotification("Preencha a data de início.",'warning');

		header("location: /admin/events/".$event_id."/eventtasks/create");
		exit;
	}

	$startDate = strtotime($_POST['task_start']);
	$finishDate = strtotime($_POST['task_finish']);
	
	if($startDate > $finishDate)
	{
		User::setNotification("Data de início da tarefa não pode ser maior que a data de término.",'error');

		header("location: /admin/events/".$event_id."/eventtasks/create");
		exit;
	}


	$event_task = new EventTask();

	$event_task->setValues($_POST);

	$event_task->settask_status('1');

	$event_task->setevent_id($event_id);

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

	if ( !isset($_POST['task_start']) || $_POST['task_start'] ==='' )
	{
		User::setNotification("Preencha a data de início.",'warning');

		header("location: /admin/events/".$event_id."/eventtasks/".$task_id."/update");
		exit;
	}

	if ( !isset($_POST['task_finish']) || $_POST['task_finish'] ==='' )
	{
		User::setNotification("Preencha a data de início.",'warning');

		header("location: /admin/events/".$event_id."/eventtasks/".$task_id."/update");
		exit;
	}

	$startDate = strtotime($_POST['task_start']);
	$finishDate = strtotime($_POST['task_finish']);
	
	if($startDate > $finishDate)
	{
		User::setNotification("Data de início da tarefa não pode ser maior que a data de término.",'error');

		header("location: /admin/events/".$event_id."/eventtasks/".$task_id."/update");
		exit;
	}

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

	header("Location: /admin/modeltasks");
	exit;
});


$app->get('/admin/events/:event_id/eventtasks', function($event_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	
	$searchSection = (isset($_GET['searchsection'])) ? $_GET['searchsection'] : "0";

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;

	if ($search != "")
	{
		
		$pagination = EventTask::getPageSearch($event_id, $search, $searchSection, $page);
	}
	else
	{
		$pagination = EventTask::getPage($event_id, $searchSection, $page);
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
		'sessiontask'=>EventTask::getSectionTask(),
		'search'=>$search,
		'searchsection'=>$searchSection,
		'pages'=>$pages
	));

});





$app->get('/admin/events/:event_id/eventtasks/import', function($event_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	
	$searchSection = (isset($_GET['searchsection'])) ? $_GET['searchsection'] : "0";

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;

	if ($search != "")
	{
		
		$pagination = EventTask::getPageSearch($event_id, $search, $searchSection, $page);
	}
	else
	{
		$pagination = EventTask::getPage($event_id, $searchSection, $page);
	}

	$event = new Events();

	$event->getEvent((int) $event_id);

	$href = '/admin/events/'.$event_id.'/eventtasks?';

	$pages = [];

	$pages = EventTask::calcPageMenu($page, $pagination, $search, $href);

	$page = new PageAdmin();

	$page->setTpl("event-tasks-import", array(
		"notification"=>EventTask::getNotification(),
		"event"=>$event->getValues(),
		"eventtasks"=>$pagination['data'],
		'sessiontask'=>EventTask::getSectionTask(),
		'search'=>$search,
		'searchsection'=>$searchSection,
		'pages'=>$pages
	));

});
 
// ***************  modelo de tasks ****************************


$app->get('/admin/modeltasks', function() {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	
	$searchSection = (isset($_GET['searchsection'])) ? $_GET['searchsection'] : "0";

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;

	if ($search != "")
	{
		$pagination = ModelTask::getPageSearch( $search, $searchSection, $page);
	}
	else
	{
		$pagination = ModelTask::getPage( $searchSection, $page);
	}

	$pages = [];

	$pages = ModelTask::calcPageMenu($page, $pagination, $search);

	$page = new PageAdmin();

	$page->setTpl("model-tasks", array(
		"notification"=>ModelTask::getNotification(),
		"modeltasks"=>$pagination['data'],
		'sessiontask'=>EventTask::getSectionTask(),
		'search'=>$search,
		'searchsection'=>$searchSection,
		'pages'=>$pages
	));

});


$app->get('/admin/modeltasks/create', function() {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$page = new PageAdmin();

	$page->setTpl("model-task-create", array(
		"notification"=>ModelTask::getNotification(),
		'search'=>$search,
		'sessiontask'=>EventTask::getSectionTask()
	));    

});


$app->post('/admin/modeltasks/create', function() {

	User::verifyLogin(1);

	$modeltask = new ModelTask();

	$modeltask->setValues($_POST);

	$modeltask->save();

	header("Location: /admin/modeltasks");
 	exit;
});    


$app->get('/admin/modeltasks/:modeltask_id/update', function($modeltask_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$modeltask = new ModelTask();

	$modeltask->getModelTasks($modeltask_id);

	$page = new PageAdmin();

	$page->setTpl("model-task-update", array(
		"notification"=>EventTask::getNotification(),
		'search'=>$search,
		'section'=>EventTask::getSectionTask(),
		'modeltask'=>$modeltask->getValues()
	));    
});    


$app->post('/admin/modeltasks/:modeltask_id/update', function($modeltask_id) {

	User::verifyLogin(1);

	$event_task = new ModelTask();

	$event_task->setValues($_POST);


	$event_task->setmodeltask_id($modeltask_id);

	$event_task->save();

	header("Location: /admin/modeltasks");
 	exit;
});    


$app->get('/admin/modeltasks/:modeltask_id/delete', function($modeltask_id) {

	User::verifyLogin(1);

	$modeltask = new ModelTask();

	$modeltask->setmodeltask_id($modeltask_id);

	$modeltask->delete();

	$modeltask->setNotification("Tarefa excluida com sucesso.",'success');

	header("Location: /admin/modeltasks");
	exit;
});

?>