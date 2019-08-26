<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class EventGuest extends Model 
{

    public function getEventGuest($event_id, $eventguest_id) 
    {

        $sql = new Sql();
        
        $results = $sql->select("SELECT * 
        FROM tb_eventguests 
        WHERE event_id = :event_id AND eventguest_id = :eventguest_id;
        ", [
            ':event_id'=>$event_id,
            ':eventguest_id'=>$eventguest_id
            ]);

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            EventGuest::setNotification("Erro na função getEventGuest(:event_id, :eventguest_id) ","error");
        }

    }


    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("call sp_eventguest_save( :eventguest_id, :event_id, :guestgroup_id, :eventguest_name, :guesttype_id, :eventguest_email, :eventguest_special_care)", 
            [
                ':eventguest_id'=>(int) $this->geteventguest_id(),
                ':event_id'=>(int) $this->getevent_id(), 
                ':guestgroup_id'=>(int) $this->getguestgroup_id(), 
                ':eventguest_name'=>$this->geteventguest_name(),                 
                ':guesttype_id'=>(int) $this->getguesttype_id(), 
                ':eventguest_email'=>$this->geteventguest_email(), 
                ':eventguest_special_care'=>$this->geteventguest_special_care()
        ]);

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            EventGuest::setNotification("Erro na Inclusão ou Atualização de convidados.","error");
        }
    }

    public function delete() {

        $sql = new Sql();

        try 
        {
            $sql->query("DELETE FROM tb_eventguests WHERE event_id = :event_id AND eventguest_id = :eventguest_id;", array(
                ":event_id"=>$this->getevent_id(),
                ":eventguest_id"=>$this->geteventguest_id()
            ));
    
        } catch (\PDOException $e) {
            Events::setNotification("Função delete tb_eventguests Erro:".$e->getMessage(),"error");
        }

    }
    
public static function getPage($event_id, $page = 1, $itensPerPage = 15)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();

    $results = $sql->select("SELECT sql_calc_found_rows *  
    FROM tb_eventguests a 
            inner join tb_events b on b.event_id = a.event_id
            inner join tb_guestgroup c on c.guestgroup_id = a.guestgroup_id
            inner join tb_guesttype d on d.guesttype_id = a.guesttype_id
    WHERE a.event_id = :event_id 
    ORDER BY a.event_id, a.eventguest_seq
    LIMIT $start , $itensPerPage;
    ", [
            ':event_id'=>$event_id
    ]);

    $resultsTotal = $sql->select("select found_rows() as nrtotal ");

    return [
        'data'=>$results,
        'total'=>(int) $resultsTotal[0]["nrtotal"],
        'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
    ];
}

public static function getPageSearch($event_id, $search, $page = 1, $itensPerPage = 15)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();

    $results = $sql->select("SELECT sql_calc_found_rows *  
    FROM tb_eventguests a 
            inner join tb_events b on b.event_id = a.event_id
            inner join tb_guestgroup c on c.guestgroup_id = a.guestgroup_id
            inner join tb_guesttype d on d.guesttype_id = a.guesttype_id
    WHERE a.event_id = :event_id 
    AND ( a.eventguest_name LIKE :search or a.eventguest_email LIKE :search or a.eventguest_special_care LIKE :search or c.guestgroup_name LIKE :search or  d.guesttype_name LIKE :search  ) 
    ORDER BY a.event_id, a.eventguest_seq 
    LIMIT $start , $itensPerPage;
    ", [
        ':event_id'=>$event_id,
        ':search'=>'%'.$search.'%'
    ]);

    $resultsTotal = $sql->select("select found_rows() as nrtotal ");

    return [
        'data'=>$results,
        'total'=>(int) $resultsTotal[0]["nrtotal"],
        'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
    ];
}

public static function calcPageMenu($page, $pagination, $search, $href = '/admin/eventguests?')
{
    return parent::calcPageMenu($page, $pagination, $search, $href);
}

public static function getGuestGroup() {

    $sql = new Sql();
    
    $results = $sql->select("SELECT * FROM tb_guestgroup ORDER BY guestgroup_id");

    return $results;
}

public static function getGuestType() {

    $sql = new Sql();
    
    $results = $sql->select("SELECT * FROM tb_guesttype ORDER BY guesttype_id");

    return $results;
}

}?>