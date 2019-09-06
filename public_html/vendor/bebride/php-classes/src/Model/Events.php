<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class Events extends Model 
{

    public function delete() {

        $sql = new Sql();

        try 
        {
            $sql->select("DELETE FROM tb_events WHERE event_id = :event_id;", array(
            ":event_id"=>$this->getevent_id()
            ));

            Events::setNotification("Evento excluido com sucesso.",'success');

        } catch (\PDOException $e) {
            Events::setNotification("Função delete Event Erro:".$e->getMessage(),"error");
            }

    }

   
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
            LEFT JOIN
				(SELECT event_id as eventid ,count(event_id)  as totalguests  FROM tb_eventguests  group by eventid) AS d ON d.eventid = a.event_id
            LEFT JOIN
                (SELECT event_id as eventid ,count(event_id)  as totaldepositions  FROM tb_depositions  group by eventid) AS e ON e.eventid = a.event_id
            LEFT JOIN
                (SELECT event_id as eventid ,count(event_id)  as totaltasks  FROM tb_eventtasks  group by eventid) AS f ON f.eventid = a.event_id            
            WHERE a.event_id = :event_id", array(
            ":event_id"=>$event_id
        ));

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            Events::setNotification("Erro na Função getEvent() Event:".$event_id,"error");
        }

    }

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("call sp_events_save(:event_id, :event_type_id, :event_name, :event_description, :event_start, :event_date, :event_finish, :status_type_id, :address_id)", [
                ':event_id'=>(int) $this->getevent_id(),
                ':event_type_id'=>(int) $this->getevent_type_id(),
                ':event_name'=>$this->getevent_name(),
                ':event_description'=>$this->getevent_description(),                
                ':event_start'=>convertdate($this->getevent_start()),
                ':event_date'=>convertdate($this->getevent_date()),
                ':event_finish'=>convertdate($this->getevent_finish()),
                ':status_type_id'=>$this->getstatus_type_id(),
                ':address_id'=>$this->getaddress_id()
        ]);

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            Events::setNotification("Erro na Inclusão ou Atualização do Evento.","error");
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

    
    public static function getEventsType()    // para uso do site Mostrar na tela
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * 
            FROM tb_eventstype 
            WHERE event_type_show = '1'");
            
       return $results;
    }


    public static function getPage($page = 1, $itensPerPage = 15)
    {

        $start = ($page - 1) * $itensPerPage; 

        $sql = new Sql();

        $results = $sql->select("SELECT sql_calc_found_rows *     
            FROM tb_events a 
            INNER JOIN tb_eventstype b on b.event_type_id = a.event_type_id
            INNER JOIN tb_statustype c on c.status_type_id = a.status_type_id
            LEFT JOIN
                (SELECT event_id as eventid ,count(event_id)  as totalguests  FROM tb_eventguests  group by eventid) AS d ON d.eventid = a.event_id
            LEFT JOIN
                (SELECT event_id as eventid ,count(event_id)  as totaldepositions  FROM tb_depositions  group by eventid) AS e ON e.eventid = a.event_id
            LEFT JOIN
                (SELECT event_id as eventid ,count(event_id)  as totaltasks  FROM tb_eventtasks  group by eventid) AS f ON f.eventid = a.event_id            
            ORDER BY a.status_type_id
            LIMIT $start , $itensPerPage;
            ");


            $resultsTotal = $sql->select("select found_rows() as nrtotal ");

            return [
                'data'=>$results,
                'total'=>(int) $resultsTotal[0]["nrtotal"],
                'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
            ];
    }

    public static function getPageSearch($search, $page = 1, $itensPerPage = 15)
    {

        $start = ($page - 1) * $itensPerPage; 

        $sql = new Sql();

        $results = $sql->select("SELECT sql_calc_found_rows *  
            FROM tb_events a 
            INNER JOIN tb_eventstype b on b.event_type_id = a.event_type_id
            INNER JOIN tb_statustype c on c.status_type_id = a.status_type_id
            LEFT JOIN
                (SELECT event_id as eventid ,count(event_id)  as totalguests  FROM tb_eventguests  group by eventid) AS d ON d.eventid = a.event_id
            LEFT JOIN
                (SELECT event_id as eventid ,count(event_id)  as totaldepositions  FROM tb_depositions  group by eventid) AS e ON e.eventid = a.event_id
            LEFT JOIN
                (SELECT event_id as eventid ,count(event_id)  as totaltasks  FROM tb_eventtasks  group by eventid) AS f ON f.eventid = a.event_id            
            WHERE a.event_name LIKE :search OR a.event_description LIKE :search OR b.event_type_name LIKE :search OR  c.status_type_name LIKE :search 
            ORDER BY a.status_type_id
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
