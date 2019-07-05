<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class Address extends Model {

    public static function getCep($nrcep)
    {
        $nrcep = str_replace("-", "", $nrcep);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json/");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = curl_exec($ch);
        
        $data = json_decode($data, true);

        if ((isset($data['erro'])) ? $data['erro'] : false)
        {
            Address::setNotification("Cep : " .  $nrcep . " não foi encontrado.","warning");
        }

        curl_close($ch);

        return $data;
    }

    
    public function loadFromCep($nrcep) 
    {

        $data = Address::getCep($nrcep);

        if (isset($data['logradouro']) && $data['logradouro'])
        {
            $this->setstreet_address($data['logradouro']);
            $this->setstreet_complement($data['complemento']);
            $this->setdistrict_name($data['bairro']);
            $this->setcity_name($data['localidade']);
            $this->setstate_name($data['uf']);            
            $this->setcountry_name('Brasil');
        }
    }

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("call sp_addresses_save(:address_id, :person_id, :street_address, :street_number, :street_complement, 
                :district_name, :city_name, :state_name, :country_name, :zipcode_number)", [
                ':address_id'=>(int) $this->getaddress_id(),
                ':person_id'=>(int) $this->getperson_id(),
                ':street_address'=>$this->getstreet_address(),
                ':street_number'=>$this->getstreet_number(),                
                ':street_complement'=>$this->getstreet_complement(),
                ':district_name'=>$this->getdistrict_name(),
                ':city_name'=>$this->getcity_name(),
                ':state_name'=>$this->getstate_name(),
                ':country_name'=>$this->getcountry_name(),
                ':zipcode_number'=>(int) $this->getzipcode_number()
        ]);

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            Address::setNotification("Erro na Inclusão ou Atualiização do Endereço.","error");
        }
    }

    public function getAddressFromPerson($person_id)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_addresses WHERE person_id = :person_id", [
            ':person_id'=>$person_id
        ]);

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        
    }

}
?>
