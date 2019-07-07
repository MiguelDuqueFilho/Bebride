<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class Events extends Model {

    
   
    public static function getEventType() {

        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_eventstype ORDER BY event_type_id");

        return $results;
    }

    public static function getStatusType() {

        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_statustype ORDER BY status_type_id");

        return $results;
    }
    
    public function getEvent($event_id) {

        $sql = new Sql();
        
        $results = $sql->select("SELECT  *    
            FROM tb_events a 
            INNER JOIN tb_eventstype b on b.event_type_id = a.event_type_id
            INNER JOIN tb_statustype c on c.status_type_id = a.status_type_id
            WHERE a.event_id = :event_id", array(
            ":event_id"=>$event_id
        ));


        $data = $results[0];

        $this->setValues($data);

    }

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("call sp_events_save(:event_id, :event_type_id, :event_name, :event_description, :event_date, :status_type_id, :address_id)", [
                ':event_id'=>(int) $this->getevent_id(),
                ':event_type_id'=>(int) $this->getevent_type_id(),
                ':event_name'=>$this->getevent_name(),
                ':event_description'=>$this->getevent_description(),                
                ':event_date'=>convertdate($this->getevent_date()),
                ':status_type_id'=>$this->getstatus_type_id(),
                ':address_id'=>$this->getaddress_id()
        ]);

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            Address::setNotification("Erro na Inclusão ou Atualização do Evento.","error");
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

        $results = $sql->select("SELECT sql_calc_found_rows *     
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

        $results = $sql->select("SELECT sql_calc_found_rows *  
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
