<?php

use \BeBride\Page;
use \BeBride\Model\Product;
use \BeBride\Model\Category;
use \BeBride\Model\Cart;
use \BeBride\Model\Address;
use \BeBride\Model\User;
use \Rain\Tpl\Exception;
use \BeBride\Model\Order;
use \BeBride\Model\OrderStatus;

$app->get('/', function() {


	$page = new Page();

	$page->setTpl("index");

});

$app->get('/login', function() {


	$page = new Page();

	$page->setTpl("login");

});

?>