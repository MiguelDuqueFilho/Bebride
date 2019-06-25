<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class Address extends Model {

    const ERROR = "AddressError";

    public static function getCep($nrcep)
    {
        $nrcep = str_replace("-", "", $nrcep);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json/");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = curl_exec($ch);
        
        $data = json_decode($data, true);

        curl_close($ch);

        return $data;
    }

    
    public function loadFromCep($nrcep) 
    {

        $data = Address::getCep($nrcep);

        if (isset($data['logradouro']) && $data['logradouro'])
        {
            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdesstate($data['uf']);            
            $this->setdescountry('Brasil');
            $this->setdeszipcode($nrcep);
        }
    }

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("call sp_addresses_save(:idaddress, :idperson, :desaddress, :desnumber, :descomplement, 
            :descity, :desstate, :descountry, :deszipcode, :desdistrict)", [
                ':idaddress'=>$this->getidaddress(),
                ':idperson'=>$this->getidperson(),
                ':desaddress'=>$this->getdesaddress(),
                ':desnumber'=>$this->getdesnumber(),                
                ':descomplement'=>$this->getdescomplement(),
                ':descity'=>$this->getdescity(),
                ':desstate'=>$this->getdesstate(),
                ':descountry'=>$this->getdescountry(),
                ':deszipcode'=>$this->getdeszipcode(),
                ':desdistrict'=>$this->getdesdistrict()
        ]);


        if (count($results) > 0) 
        {
            $this->setData($results[0]);
        }
    }

    public static function setMsgError($msg) 
    {
        $_SESSION[Address::ERROR] = $msg;
    }

    public static function getMsgError() 
    {
        $msg = (isset($_SESSION[Address::ERROR]) && $_SESSION[Address::ERROR]) ? $_SESSION[Address::ERROR] : '';

        Address::clearMsgError();

        return $msg;
    }

    public static function clearMsgError() 
    {
        $_SESSION[Address::ERROR] = NULL;
    }
}
?>