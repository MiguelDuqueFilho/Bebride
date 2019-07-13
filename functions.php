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

function formatDateHtml($date)
{
    return date('Y-m-d',strtotime($date));
}

function convertdate($date) 
{ 
    return date("Y-m-d", strtotime($date));
} 

function checkLogin($user_type_id = 0) 
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


   
function somar_dias_uteis($str_data,$int_qtd_dias_somar = 7) {

    // Caso seja informado uma data do MySQL do tipo DATETIME - aaaa-mm-dd 00:00:00

    // Transforma para DATE - aaaa-mm-dd

    $str_data = substr($str_data,0,10);

       

    // Se a data estiver no formato brasileiro: dd/mm/aaaa

    // Converte-a para o padrão americano: aaaa-mm-dd

    if ( preg_match("@/@",$str_data) == 1 ) {

        $str_data = implode("-", array_reverse(explode("/",$str_data)));

    }

    $array_data = explode('-', $str_data);   

    $count_days = 0;

    $int_qtd_dias_uteis = 0;   

    while ( $int_qtd_dias_uteis < $int_qtd_dias_somar ) 
    {

        $count_days++;

                if ( ( $dias_da_semana = gmdate('w', strtotime('+'.$count_days.' day', mktime(0, 0, 0, $array_data[1], $array_data[2], $array_data[0]))) ) != '0' && $dias_da_semana != '6' ) 
                {
                    $int_qtd_dias_uteis++;
                }
    }

    return gmdate('Y-m-d',strtotime('+'.$count_days.' day',strtotime($str_data)));

}


?>