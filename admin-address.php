<?php

use \BeBride\PageAdmin;
use \BeBride\Model\User;
use \BeBride\Model\Address;

$app->get('/admin/address/create', function() {

    //	User::verifyLogin();
    
        $search = (isset($_GET['search'])) ? $_GET['search'] : "";
    
        $page = new PageAdmin();
    
        $page->setTpl("address-create", array(
            "notification"=>Address::getNotification(),
            'search'=>$search
        ));
    
    });

?>