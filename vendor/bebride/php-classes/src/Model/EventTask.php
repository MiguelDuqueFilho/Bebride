<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class EventTask extends Model {

    public function getEventTasks($event_id, $task_id) {

        $sql = new Sql();
        
        $results = $sql->select("SELECT sql_calc_found_rows *  
        FROM tb_eventtasks a 
        WHERE a.event_id = :event_id AND a.task_id = :task_id
        ORDER BY a.event_id, a.task_id
        ", [
            ':event_id'=>$event_id,
            ':task_id'=>$task_id
        ]);

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            EventTask::setNotification("Erro na função getEventTasks(:event_id, :task_id) ","error");
        }

    }

    public static function getSectionTask()
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * 
            FROM tb_section_task 
            ORDER BY  section_task_id");

       return $results;
    }

    public static function statusTasks() 
    {
        $status = array(
            array(
                'status_id' => '0',
                'status_name' => 'inicial',
                'status_color' => 'info',
            ),
            array(
                'status_id' => '1',
                'status_name' => 'em dia',
                'status_color' => 'success',
            ),
            array(
                'status_id' => '2',
                'status_name' => 'pendente',
                'status_color' => 'warning',
            ),
            array(
                'status_id' => '3',
                'status_name' => 'inicial',
                'status_color' => 'danger',
            )
        );

        return $status;
    }

    public function save()
    {
        $sql = new Sql();
 
        $results = $sql->select("call sp_eventtask_save(
            :event_id, 
            :task_id
            :task_section_id, 
            :task_name, 
            :task_status, 
            :task_duration, 
            :task_start, 
            :task_finish, 
            :task_completed, 
            :task_responsible, 
            :task_showboard, 
            :task_showcustomer
            )", [
            ':event_id'=>(int) $this->getevent_id(),
            ':task_id'=>(int) $this->gettask_id(),
            ':task_section_id'=> (int) $this->gettask_section_id(),
            ':task_name'=>$this->gettask_name(),                
            ':task_status'=>(int) $this->gettask_status(),
            ':task_duration'=>(int) $this->gettask_duration(),
            ':task_start'=>convertdate($this->gettask_start()),
            ':task_finish'=>convertdate($this->gettask_finish()),
            ':task_completed'=>(int) $this->gettask_completed(),
            ':task_responsible'=>$this->gettask_responsible(),            
            ':task_showboard'=> $this->gettask_showboard(),           
            ':task_showcustomer'=> $this->gettask_showcustomer()
        ]);


        $teste = [
            ':event_id'=>(int) $this->getevent_id(),
            ':task_id'=>(int) $this->gettask_id(),
            ':task_section_id'=> (int) $this->gettask_section_id(),
            ':task_name'=>$this->gettask_name(),                
            ':task_status'=>(int) $this->gettask_status(),
            ':task_duration'=>(int) $this->gettask_duration(),
            ':task_start'=>convertdate($this->gettask_start()),
            ':task_finish'=>convertdate($this->gettask_finish()),
            ':task_completed'=>(int) $this->gettask_completed(),
            ':task_responsible'=>$this->gettask_responsible(),            
            ':task_showboard'=> $this->gettask_showboard(),           
            ':task_showcustomer'=> $this->gettask_showcustomer()
        ];
        var_dump($teste);
        echo "<br>";
        var_dump($results);
        exit;
 

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            EventTask::setNotification("Erro na Inclusão ou Atualização de Tarefas.","error");
        }
    }

    public function delete() {

        $sql = new Sql();

        $sql->select("DELETE FROM tb_eventtasks WHERE event_id = :event_id AND task_id = :task_id;", array(
            ":event_id"=>$this->getevent_id(),
            ":task_id"=>$this->gettask_id()
        ));


    }
    
public static function getPage($event_id, $searchtype, $page = 1, $itensPerPage = 18)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();

    if($searchtype == "0") 
    {
        $results = $sql->select("SELECT sql_calc_found_rows *  
            FROM tb_eventtasks a 
            WHERE a.event_id = :event_id
            ORDER BY a.event_id, a.task_id
            LIMIT $start , $itensPerPage;
            ", [
                ':event_id'=>$event_id
        ]);
    }
    else 
    {
        $results = $sql->select("SELECT sql_calc_found_rows *  
            FROM tb_eventtasks a 
            WHERE a.event_id = :event_id 
            AND a.task_type_id = :searchtype
            ORDER BY a.event_id, a.task_id  
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
        WHERE a.event_id = :event_id
        AND ( a.task_name LIKE :search 
        OR a.task_status LIKE :search 
        OR a.task_responsible LIKE :search ) 
        ORDER BY a.event_id, a.task_id 
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
        WHERE a.event_id = :event_id
        AND ( a.task_name LIKE :search 
        OR a.task_status LIKE :search 
        OR a.task_responsible LIKE :search )
        AND a.task_type_id = :searchtype  
        ORDER BY a.event_id, a.task_id
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
