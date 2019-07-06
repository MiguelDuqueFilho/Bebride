<?php

use \BeBride\PageAdmin;
use \BeBride\Model;
use \BeBride\Model\User;
use \BeBride\Model\Events;


$app->get('/admin/events', function() {
	
	User::verifyLogin(1);	

    $search = (isset($_GET['search'])) ? $_GET['search'] : "";

    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1 ;
    
    if ($search != "")
    {
        $pagination = Events::getPageSearch($search, $page);
    }
    else
    {
        $pagination = Events::getPage($page);
    }
    
    $pages = [];
    
    $pages = Events::calcPageMenu($page, $pagination, $search);
    
    
    $page = new PageAdmin();
    
    $page->setTpl("events", array(
        "notification"=>Events::getNotification(),
        "events"=>$pagination['data'],
        'search'=>$search,
        'pages'=>$pages
    ));

});




?>