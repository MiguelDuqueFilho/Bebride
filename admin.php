<?php

use \BeBride\PageAdmin;
use \BeBride\Model;
use \BeBride\Model\User;


$app->get('/admin', function() {
	
//	User::verifyLogin();

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	
	$page = new PageAdmin();

	Model::setNotification("Tela ainda não implementada");
	
	$page->setTpl("index",[
		"notification"=>Model::getNotification(),
		'search'=>$search
	]);

});

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