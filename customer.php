<?php

use \BeBride\PageAdmin;
use \BeBride\Model;
use \BeBride\Model\User;


$app->get('/customer', function() {
	
	User::verifyLogin(1);

	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	
	$page = new PageCustomer();

	Model::setNotification("Tela ainda não implementada");
	
	$page->setTpl("index",[
		// "menu"=>PageCustomer::setMenuItem("dashboard"),
		"notification"=>Model::getNotification(),
		'search'=>$search
	]);

});




?>