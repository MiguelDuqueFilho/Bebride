<?php

use \BeBride\PageAdmin;
use \BeBride\Model;
use \BeBride\Model\User;



$app->get('/admin/users', function() {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;

	if ($search != "")
	{
		$pagination = User::getPageSearch($search, $page);
	}
	else
	{
		$pagination = User::getPage($page);
	}

	$pages = [];

	$pages = User::calcPageMenu($page, $pagination, $search);


	$page = new PageAdmin();

	$page->setTpl("users", array(
		"notification"=>User::getNotification(),
		"users"=>$pagination['data'],
		'search'=>$search,
		'pages'=>$pages
	));

});


$app->get('/admin/users/profile', function() {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$user = User::getFromSession();

	$page = new PageAdmin();

	$page->setTpl("profile",[
		"notification"=>Model::getNotification(),
		'search'=>$search,
		'user'=>$user->getValues()
	]);

});

$app->get('/admin/users/:iduser/delete', function($user_id) {

	User::verifyLogin(1);

	$user = new User();

	$user->getUser((int) $user_id);

	$user->delete();

	$user->setNotification("Usuário excluido com sucesso.",'success');

	header("Location: /admin/users");
	exit;
});

$app->get('/admin/users/create', function() {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$page = new PageAdmin();

	$page->setTpl("users-create", array(
		"notification"=>User::getNotification(),
		'search'=>$search
	));

});


$app->post("/admin/users/create", function () {

 	User::verifyLogin(1);

	$user = new User();
	 
	$user->setValues($_POST);
	 
	$user->setpassword_hash(str_shuffle('K9j5tRwsPlwz40x'));

	$user->save();

	header("Location: /admin/users");
 	exit;

});


$app->get('/admin/users/:user_id', function($user_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$user = new User();

	$user->getUser((int) $user_id);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"notification"=>User::getNotification(),
		'search'=>$search,
		"user"=>$user->getValues()
	));

});

$app->post('/admin/users/:user_id', function($user_id) {

	User::verifyLogin(1);

	$user = new User();

	$user->getUser((int) $user_id);

	$user->setValues($_POST);	
	
	$user->setPhoto($_FILES["file"]);

	$user->update();

	header("Location: /admin/users");
	exit;
});


$app->get("/admin/users/:iduser/password", function($user_id) {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	$user = new User();

	$user->getUser((int) $user_id);

	$page = new PageAdmin();

	$page->setTpl("users-password", array(
		"notification"=>User::getNotification(),
		'search'=>$search,
		"user"=>$user->getValues()
	));

});

$app->post("/admin/users/:iduser/password", function($user_id) {

	User::verifyLogin(1);

	$user = new User();

	$user->getUser((int) $user_id);

	if (!User::getUserTypeFromSession() === '1') 
	{
		if (!password_verify($_POST['active_password'],$user->getpassword_hash())) 
		{
			User::setNotification("Senha atual Não Confere.",'warning');

			header("location: /admin/users/$user_id/password");
			exit;
		}
	}

	if (!isset($_POST['new_password']) || $_POST['new_password'] ==='')
	{
		User::setNotification("Preencha a nova senha.",'warning');

		header("location: /admin/users/$user_id/password");
		exit;
	}

	if (!isset($_POST['new_password_confirm']) || $_POST['new_password_confirm'] ==='')
	{
		User::setNotification("Preencha a confirmaçâo da nova senha.",'warning');
		
		header("location: /admin/users/$user_id/password");
		exit;
	}


	if ($_POST['new_password'] !== $_POST['new_password_confirm'])
	{
		User::setNotification("Confirme corretamente as senhas.",'warning');
		
		header("location: /admin/users/$user_id/password");
		exit;
	}


	$user->setPassword(User::getPasswordHash( $_POST['new_password']));

	User::setNotification("Senha alterada com sucesso.",'success');
		
	header("location: /admin/users");
	exit;
});


?>