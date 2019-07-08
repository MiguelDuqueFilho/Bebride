<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class EventTask extends Model {

    public function getEventTasks($event_id) {

        $sql = new Sql();
        
        $results = $sql->select("SELECT *   
            FROM tb_users a 
            INNER JOIN tb_persons b on b.person_id = a.person_id
            INNER JOIN tb_userstype c on c.user_type_id = a.user_type_id
            WHERE a.user_id = :user_id", array(
            ":user_id"=>$event_id
        ));


        $data = $results[0];

        $this->setValues($data);

    }

    public static function getSessionTask()
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * 
            FROM tb_session_task 
            ORDER BY  session_task_id");
            
       return $results;
    }

    public static function statusTasks() {
        $status = [
            '1'=>'info',
            '2'=>'success',
            '3'=>'warning',
            '4'=>'danger',
        ];
        return $status;
    }

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("call sp_EventTask_save(:address_id, :person_id, :street_address, :street_number, :street_complement, 
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
            Address::setNotification("Erro na Inclusão ou Atualização de Tarefas.","error");
        }
    }

    
public static function getPage($event_id, $searchtype, $page = 1, $itensPerPage = 18)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();

    if($searchtype == "0") 
    {
        $results = $sql->select("SELECT sql_calc_found_rows *  
            FROM tb_eventtasks a 
            INNER JOIN tb_persons b on a.task_responsible = b.person_id 
            WHERE a.event_id = :event_id
            ORDER BY a.task_id
            LIMIT $start , $itensPerPage;
            ", [
                ':event_id'=>$event_id
        ]);
    }
    else 
    {
        $results = $sql->select("SELECT sql_calc_found_rows *  
            FROM tb_eventtasks a 
            INNER JOIN tb_persons b on a.task_responsible = b.person_id 
            WHERE a.event_id = :event_id 
            AND a.task_type_id = :searchtype
            ORDER BY a.task_id  
            LIMIT $start , $itensPerPage;
            ", [
                ':event_id'=>$event_id,
                ':searchtype'=>$searchtype
        ]);
    }
        $resultsTotal = $sql->select("select found_rows() as nrtotal ");

        return [
            'data'=>$results,
            'total'=>(int) $resultsTotal[0]["nrtotal"],
            'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
        ];
}

public static function getPageSearch($event_id, $search, $searchtype, $page = 1, $itensPerPage = 18)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();
    
    if($searchtype == "0") 
    {

        $results = $sql->select("SELECT sql_calc_found_rows *  
        FROM tb_eventtasks a 
        INNER JOIN tb_persons b on a.task_responsible = b.person_id 
        WHERE a.event_id = :event_id
        AND ( a.task_name LIKE :search 
        OR a.task_status LIKE :search 
        OR a.task_responsible LIKE :search ) 
        ORDER BY a.task_id 
        LIMIT $start , $itensPerPage;
        ", [
            ':event_id'=>$event_id,
            ':search'=>'%'.$search.'%'
        ]);
    }
    else 
    {
        $results = $sql->select("SELECT sql_calc_found_rows *  
        FROM tb_eventtasks a 
        INNER JOIN tb_persons b on a.task_responsible = b.person_id 
        WHERE a.event_id = :event_id
        AND ( a.task_name LIKE :search 
        OR a.task_status LIKE :search 
        OR a.task_responsible LIKE :search )
        AND a.task_type_id = :searchtype  
        ORDER BY a.task_id 
        LIMIT $start , $itensPerPage;
        ", [
            ':event_id'=>$event_id,
            ':search'=>'%'.$search.'%',
            ':searchtype'=>$searchtype
        ]);

    }
        $resultsTotal = $sql->select("select found_rows() as nrtotal ");

        return [
            'data'=>$results,
            'total'=>(int) $resultsTotal[0]["nrtotal"],
            'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
        ];
}



    public static function calcPageMenu($page, $pagination, $search, $href = '/admin/eventtasks?')
    {
        return parent::calcPageMenu($page, $pagination, $search, $href);
    }



}
?>
