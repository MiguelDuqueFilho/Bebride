<?php

use \BeBride\PageAdmin;
use \BeBride\Model;
use \BeBride\Model\User;
use \BeBride\Model\EventTask;


$app->get('/admin', function() {
	
	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	
	$page = new PageAdmin();
	
	$page->setTpl("index",[
		"qtdtaskstatusbysection"=>EventTask::getQtdTaskStatusBySection(),
		"notification"=>Model::getNotification(),
		'search'=>$search
	]);

});


// select para dashboard total de usuários por tipo de usuário

// SELECT 	IFNULL(user_type_name, 'TOTAL') AS user_type_name,
// 	   		count(user_type_name)
// from tb_users a
// inner join tb_userstype b on a.user_type_id = b.user_type_id
// group by user_type_name WITH ROLLUP;



// select para dashboard total de eventos  por tipo de evento

// SELECT  	IFNULL(event_type_name, 'TOTAL') AS event_type_name,
// 	   		count(event_type_name)    
// FROM tb_events a 
// INNER JOIN tb_eventstype b on b.event_type_id = a.event_type_id
// INNER JOIN tb_statustype c on c.status_type_id = a.status_type_id
// group by event_type_name WITH ROLLUP;


// select para dashboard total tasks por status de eventos em atividade

// SELECT  IFNULL(status_task_name, 'TOTAL') AS status_task_name,
// 	   count(status_task_name)      
//             FROM tb_eventtasks a 
//             INNER JOIN tb_statustask b on b.status_task_id = a.task_status_id
//             INNER JOIN tb_section_task c on c.section_task_id = a.task_section_id
//             INNER JOIN tb_events d on a.event_id = d.event_id
//             WHERE d.status_type_id < 6
//             group by status_task_name WITH ROLLUP;

// select para dashboard total tasks por section, status de eventos em atividade

// SELECT  IFNULL(section_task_name, 'TOTAL') AS section_task_name,
// 		IFNULL(status_task_name, 'TOTAL') AS status_task_name,
// 		count(section_task_name)      
//             FROM tb_eventtasks a 
//             INNER JOIN tb_statustask b on b.status_task_id = a.task_status_id
//             INNER JOIN tb_section_task c on c.section_task_id = a.task_section_id
//             INNER JOIN tb_events d on a.event_id = d.event_id
//             WHERE d.status_type_id < 6
//             group by section_task_name, status_task_name WITH ROLLUP;


/* 
$app->get('/admin/login', function() {
	
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");

});


$app->post('/admin/login', function() {
	
	User::login($_POST["login"], $_POST["password"]);

	header("location: /admin");
	exit;
});

$app->get('/admin/logout', function() {

	User::logout();

	header("location: /admin/login");
	exit;
});


$app->get("/admin/forgot/sent", function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset", function() {

	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);


	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});


$app->post("/admin/forgot/reset", function() {

	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int) $forgot["iduser"]);

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
		"cost"=>12
	]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");
});

$app->get("/admin/forgot", function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");
});


$app->post("/admin/forgot", function() {

	$user = User::getForgot($_POST["email"]);

	header("location: /admin/forgot/sent");
	exit;

});
 */

?>