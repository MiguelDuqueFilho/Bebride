<?php
use \BeBride\Model\User;
use \BeBride\Model\Cart;

function formatPrice($vlprice) 
{
    if (!$vlprice > 0) $vlprice = 0;
    
    return number_format($vlprice,2,",",".");
}

function formatDate($date)
{
    return date('d/m/Y',strtotime($date));
}

function checkLogin($user_type_id = 1) 
{
    return User::checkLogin($user_type_id);
}

function getUserName() 
{
    $user = User::getFromSession();

    return $user->getperson_firstname();
}

function getCartNrQtd() 
{
    $cart = Cart::getFromSession();

    $totals = $cart->getProductsTotals();

    return $totals['nrqtd'];
}

function getCartVlSubTotal() 
{
    $cart = Cart::getFromSession();

    $totals = $cart->getProductsTotals();

    return formatPrice($totals['vlprice']);
}

?>