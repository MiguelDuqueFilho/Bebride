<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class Events extends Model {

    
    public function getValues()
    {
        // $dt = new DateTime($this->getevent_date());
        // $dt->format("l, d/m/Y H:i:s");
        // $this->setevent_date_format($dt);

        // var_dump($dt);
        // var_dump($this->getevent_date());
        // var_dump($this->get_event_date_format());
        // exit;
        $values = parent::getValues();

        return $values;
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

    public function getEventsFromPerson($person_id)
    {

        // $sql = new Sql();

        // $results = $sql->select("SELECT * 
        //     FROM tb_events a
        //     INNER JOIN tb_eventstype b on b.event_id = a.event_id
        //     WHERE events_type_id = :events_type_id 
        //     ORDER BY events_name ", [
        //     ':person_id'=>$person_id
        // ]);

        // if (count($results) > 0) 
        // {
        //     $this->setValues($results[0]);
        // }
        
    }

    
    public static function getEventsType()
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * 
            FROM tb_eventstype 
            WHERE event_type_show = '1'");
            
       return $results;
    }


    public static function getPage($page = 1, $itensPerPage = 10)
    {

        $start = ($page - 1) * $itensPerPage; 

        $sql = new Sql();


        setlocale(LC_TIME, "pt_BR", "pt_BR.utf-8", "portuguese");

        $results = $sql->select("SELECT sql_calc_found_rows * , DATE_FORMAT(a.event_date, '%d/%m/%Y') as event_date_format    
            FROM tb_events a 
            INNER JOIN tb_eventstype b on b.event_type_id = a.event_type_id
            INNER JOIN tb_statustype c on c.status_type_id = a.status_type_id
            ORDER BY a.event_name
            LIMIT $start , $itensPerPage;
            ");


            $resultsTotal = $sql->select("select found_rows() as nrtotal ");

            return [
                'data'=>$results,
                'total'=>(int) $resultsTotal[0]["nrtotal"],
                'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
            ];
    }

    public static function getPageSearch($search, $page = 1, $itensPerPage = 10)
    {

        $start = ($page - 1) * $itensPerPage; 

        $sql = new Sql();

        $results = $sql->select("SELECT sql_calc_found_rows * , DATE_FORMAT(a.event_date, '(%W), %d/%m/%Y') as event_date_format  
            FROM tb_events a 
            INNER JOIN tb_eventstype b on b.event_type_id = a.event_type_id
            INNER JOIN tb_statustype c on c.status_type_id = a.status_type_id
            WHERE a.event_name LIKE :search OR a.event_description LIKE :search OR b.event_type_name LIKE :search OR  c.status_type_name LIKE :search 
            LIMIT $start , $itensPerPage;
            ", [
                ':search'=>'%'.$search.'%'
            ]);

            

            $resultsTotal = $sql->select("select found_rows() as nrtotal ");

            return [
                'data'=>$results,
                'total'=>(int) $resultsTotal[0]["nrtotal"],
                'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
            ];
    }

    public static function calcPageMenu($page, $pagination, $search, $href = '/admin/events?')
    {
        return parent::calcPageMenu($page, $pagination, $search, $href);
    }


}
?>
