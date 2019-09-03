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

	$event_task->settask_status_id('1'); // status inicial

	$event_task->setmodeltask_id('0'); // tarefa sem modelo 

	$event_task->setmodeltask_calculatetask('0'); // task pode ser será calculada

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

	$event_task->setmodeltask_calculatetask('0'); // task pode ser calculada

	$event_task->save();

	header("Location: /admin/events/".$event_id."/eventtasks");
 	exit;
});    


$app->get('/admin/events/:event_id/eventtasks/:task_id/delete', function($event_id,$task_id) {

	User::verifyLogin(1);

	$event_task = new EventTask();

	$event_task->setevent_id($event_id);
	$event_task->settask_id($task_id);

	$event_task->delete();

	$event_task->setNotification("Tarefa excluida com sucesso.",'success');

	header("Location: /admin/events/".$event_id."/eventtasks");
	exit;
});


$app->get('/admin/events/:event_id/eventtasks/:task_id/addpercent', function($event_id,$task_id) {

	User::verifyLogin(1);

	$event_task = new EventTask();

	$event_task->getEventTasks( $event_id, $task_id);

	$task_completed =  (int) $event_task->gettask_completed();

	$task_completed = $task_completed + 25;

	if ($task_completed > 100 ) $task_completed = 100;

	$event_task->settask_completed($task_completed);

	$event_task->save();

	header("Location: /admin/events/".$event_id."/eventtasks");
	exit;
});

$app->get('/admin/events/:event_id/eventtasks/:task_id/subtractpercent', function($event_id,$task_id) {

	User::verifyLogin(1);

	$event_task = new EventTask();

	$event_task->getEventTasks( $event_id, $task_id);

	$task_completed =  (int) $event_task->gettask_completed();

	$task_completed = $task_completed - 25;

	if ($task_completed < 0 ) $task_completed = 0;

	$event_task->settask_completed($task_completed);

	$event_task->save();

	header("Location: /admin/events/".$event_id."/eventtasks");
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

	if ($modeltask->setmodeltask_calculatetask(false)) 
	{

	}
	$modeltask->setmodeltask_calculatetask(false);

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



$app->get('/admin/events/:event_id/eventtasks/import', function($event_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	
	$searchSection = (isset($_GET['searchsection'])) ? $_GET['searchsection'] : "0";

	$page_event = (isset($_GET['pages_event'])) ? (int)$_GET['pages_event'] : 1 ;
	$page_model = (isset($_GET['pages_model'])) ? (int)$_GET['pages_model'] : 1 ;


	if ($search != "")
	{
		$pagination_event = EventTask::getPageSearchImportRelated($event_id, $search, $searchSection, $page_event);
		$pagination_model = ModelTask::getPageSearchImportNotRelated($event_id, $search, $searchSection, $page_model);
	}
	else
	{
		$pagination_event = EventTask::getPageImportRelated($event_id, $searchSection, $page_event);
		$pagination_model = ModelTask::getPageImportNotRelated($event_id, $searchSection, $page_model);
	}

	$event = new Events();

	$event->getEvent($event_id);

	$href = '/admin/events/'.$event_id.'/eventtasks/import?';

	$pages_event = [];	
	$pages_model = [];

	$pages_event = EventTask::calcPageMenuImport($page_event, $pagination_event, $search, $href);
	$pages_model = ModelTask::calcPageMenuImport($page_model, $pagination_model, $search, $href);

	$page = new PageAdmin();

	$page->setTpl("event-tasks-import", array(
		"notification"=>EventTask::getNotification(),
		"event"=>$event->getValues(),
		"eventtasks"=>$pagination_event['data'],
		"modeltasks"=>$pagination_model['data'],
		'sessiontask'=>EventTask::getSectionTask(),
		'search'=>$search,
		'searchsection'=>$searchSection,
		'pages_event'=>$pages_event,
		'pages_model'=>$pages_model
	));

});


$app->get('/admin/events/:event_id/eventtasks/import/:modeltask_id', function($event_id,$modeltask_id) 
{

	User::verifyLogin(1);

	$event = new Events();

	$event->getEvent((int) $event_id);	

	$modeltask = new ModelTask();

	$modeltask->getModelTasks($modeltask_id);

	$event_task = new EventTask();

	$event_task->settask_id('0');
	$event_task->setevent_id($event_id);
	$event_task->setmodeltask_id($modeltask->getmodeltask_id());
	$event_task->settask_section_id($modeltask->getmodeltask_section_id());
	$event_task->settask_name($modeltask->getmodeltask_name());
	$event_task->settask_status_id('1');
	$event_task->settask_duration($modeltask->getmodeltask_duration());
	$event_task->settask_predecessors($modeltask->getmodeltask_predecessors());
	$event_task->settask_successors($modeltask->getmodeltask_successors());
	$event_task->settask_start(null);
	$event_task->settask_finish(null);
	$event_task->settask_completed('0');
	$event_task->settask_responsible($modeltask->getmodeltask_responsible());
	$event_task->settask_showboard($modeltask->getmodeltask_showboard());
	$event_task->settask_showcustomer($modeltask->getmodeltask_showcustomer());
	$event_task->settask_calculatetask($modeltask->getmodeltask_calculatetask());

	if ($modeltask->getmodeltask_id() == '1') 
	{
		$event_task->settask_start($event->getevent_start());
		$event_task->settask_finish($event->getevent_start());
	}
	if ($modeltask->getmodeltask_id() == '2') 
	{
		$event_task->settask_start($event->getevent_date());
		$event_task->settask_finish($event->getevent_date());
	}
	if ($modeltask->getmodeltask_id() == '3') 
	{
		$event_task->settask_start($event->getevent_finish());
		$event_task->settask_finish($event->getevent_finish());
	}

	$event_task->save();

	header("Location: /admin/events/".$event_id."/eventtasks/import");
	exit;
	 
});


$app->get('/admin/events/:event_id/eventtasks/:task_id/import/delete', function($event_id,$task_id) 
{
	User::verifyLogin(1);

	$event_task = new EventTask();

	$event_task->setevent_id($event_id);
	$event_task->settask_id($task_id);

	$event_task->delete();

	header("Location: /admin/events/".$event_id."/eventtasks/import");
	exit;
	 	 
});


$app->get('/admin/events/:event_id/eventtasks/processdate', function($event_id) {

	User::verifyLogin(1);

	EventTask::calcTaskPredecessors($event_id);

	EventTask::calcTaskSuccessors($event_id);
	
	header("Location: /admin/events/".$event_id."/eventtasks");
 	exit;
});


?>