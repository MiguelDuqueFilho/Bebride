<?php

use \BeBride\PageAdmin;
use \BeBride\Model\User;
use \BeBride\Model\Address;

$app->get('/admin/user/:user_id/address/create', function($user_id) {

    	User::verifyLogin(1);
    
        $search = (isset($_GET['search'])) ? $_GET['search'] : "";

        // if (isset($_GET['zipcode_number'])) 
        // {
        //     $address->loadFromCep($_GET['zipcode_number']);
        // }

        $user = new User();
    
        $user->getUser($user_id);

        $address = new Address();

        $address->getAddressFromPerson($user->getperson_id());

        $page = new PageAdmin();

        if ($address->getaddress_id() === NULL)
        {
            $page->setTpl("address-create", array(
                "notification"=>Address::getNotification(),
                'search'=>$search,
                "user"=>$user->getValues()
            ));    
        }
        else
        {
            $page->setTpl("address-update", array(
                "notification"=>Address::getNotification(),
                'search'=>$search,
                "user"=>$user->getValues(),
                "address"=>$address->getValues()
            ));    
        }
    
    });

    
$app->post('/admin/user/:user_id/address/create', function($user_id) {

    User::verifyLogin(1);

    $user = new User();

    $user->getUser($user_id);

    $address = new Address();

    $address->setValues($_POST);
 
    $address->setperson_id($user->getperson_id());

	$address->save();

	header("Location: /admin/users");
 	exit;

});

    
$app->post('/admin/user/:user_id/address/update', function($user_id) {

    User::verifyLogin(1);

    $user = new User();

    $user->getUser($user_id);

    $address = new Address();

    $address->getAddressFromPerson($user->getperson_id());

    $address->setValues($_POST);
 
    $address->setperson_id($user->getperson_id());

	$address->save();

	header("Location: /admin/users");
 	exit;

});

?>