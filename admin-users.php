<?php

use \BeBride\PageAdmin;
use \BeBride\Model;
use \BeBride\Model\User;


$app->get('/admin/user', function() {

	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	
	$page = new PageAdmin();

	$page->setTpl("user",[
		"notification"=>Model::getNotification(),
		'search'=>$search
	]);

});



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

 	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

 	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

 		"cost"=>12

	 ]);
	 

 	$user->setValues($_POST);

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



$app->post('/admin/users/:iduser', function($iduser) {

	User::verifyLogin(1);

	$user = new User();

	$user->getUser((int) $iduser);

	$user->setValues($_POST);	
	
	$user->setPhoto($_FILES["file"]);

	$user->update();

	header("Location: /admin/users");
	exit;
});





/* 
$app->get("/admin/users/:iduser/password", function($iduser) {

	User::verifyLogin();

	$user = new User();

	$user->get((int) $iduser);

	$page = new PageAdmin();

	$page->setTpl("users-password", array(
		"user"=>$user->getValues(),
		"msgError"=>User::getError(),
		"msgSuccess"=>User::getSuccess()
	));


});

$app->post("/admin/users/:iduser/password", function($iduser) {

	User::verifyLogin();

	if (!isset($_POST['despassword']) || $_POST['despassword'] ==='')
	{
		User::setError("Preencha a nova senha.");

		header("location: /admin/users/$iduser/password");
		exit;
	}

	if (!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm'] ==='')
	{
		User::setError("Preencha a confirmaçâo da nova senha.");
		
		header("location: /admin/users/$iduser/password");
		exit;
	}

	if ($_POST['despassword'] !== $_POST['despassword-confirm'])
	{
		User::setError("Confirme corretamente as senhas.");
		
		header("location: /admin/users/$iduser/password");
		exit;
	}

	$user = new User();

	$user->get((int) $iduser);

	$user->setPassword(User::getPasswordHash( $_POST['despassword']));

	User::setSuccess("Senha alterada com sucesso.");
		
	header("location: /admin/users/$iduser/password");
	exit;
});

 */


?>