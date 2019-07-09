<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class ModelTask extends Model 
{

    public function getTasks($related = true)
    {
        $sql = new Sql();

        if ($related === true) 
        {
            return $sql->select("
            select * from tb_products c where c.idproduct in (
                select a.idproduct from tb_products a
                inner join tb_productscategories b
                on a.idproduct = b.idproduct 
                where b.idcategory = :idcategory)", 
                array(
                    ":idcategory"=>$this->getidcategory()
            ));
        } 
        else
        {
            return $sql->select("
            select * from tb_products c where c.idproduct not in (
                select a.idproduct from tb_products a
                inner join tb_productscategories b
                on a.idproduct = b.idproduct 
                where b.idcategory = :idcategory)", 
                array(
                    ":idcategory"=>$this->getidcategory()
            ));
        }
    }



    public function getEventTasks($event_id, $task_id) 
    {

        $sql = new Sql();
        
        $results = $sql->select("SELECT sql_calc_found_rows *  
        FROM tb_eventtasks a 
        INNER JOIN tb_statustask b on b.status_task_id = a.task_status_id
        INNER JOIN tb_section_task c on c.section_task_id = a.task_section_id
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

        $sql = new Sql();

        $results = $sql->select("SELECT * 
            FROM tb_statustask 
            ORDER BY  status_task_id");

        return $results;
    }

    public function save()
    {
        $sql = new Sql();
 
       
        $results = $sql->select("call sp_eventtask_save(:event_id, :task_id, :task_section_id, :task_name, :task_status, 
            :task_duration, :task_start, :task_finish, :task_completed, :task_responsible, :task_showboard, :task_showcustomer)", 
            [
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

        $sql->select("DELETE FROM tb_modeltasks WHERE modeltask_id = :event_id", array(
            ":task_id"=>$this->gettask_id()
        ));

    }
    
public static function getPage( $searchsection, $page = 1, $itensPerPage = 18)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();

    if($searchsection == "0") 
    {
        $results = $sql->select("SELECT sql_calc_found_rows *  
            FROM tb_modeltasks a 
            INNER JOIN tb_section_task b on b.section_task_id = a.modeltask_section_id
            ORDER BY a.modeltask_id
            LIMIT $start , $itensPerPage;");
    }
    else 
    {
        $results = $sql->select("SELECT sql_calc_found_rows *  
            FROM tb_modeltasks a 
            INNER JOIN tb_section_task b on b.section_task_id = a.modeltask_section_id
            WHERE a.modeltask_section_id = :searchsection
            ORDER BY a.modeltask_id  
            LIMIT $start , $itensPerPage;
            ", [
                ':searchsection'=>$searchsection
            ]);
    }
        $resultsTotal = $sql->select("select found_rows() as nrtotal ");

        return [
            'data'=>$results,
            'total'=>(int) $resultsTotal[0]["nrtotal"],
            'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
        ];
}

public static function getPageSearch($search, $searchsection, $page = 1, $itensPerPage = 18)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();
    
    if($searchsection == "0") 
    {

        $results = $sql->select("SELECT sql_calc_found_rows *  
        FROM tb_modeltasks a  
        INNER JOIN tb_section_task b on b.section_task_id = a.modeltask_section_id
        WHERE a.modeltask_name LIKE :search 
        OR a.task_responsible LIKE :search 
        ORDER BY a.modeltask_id 
        LIMIT $start , $itensPerPage;
        ", [
            ':search'=>'%'.$search.'%'
        ]);
    }
    else 
    {
        $results = $sql->select("SELECT sql_calc_found_rows *  
        FROM tb_eventtasks a 
        INNER JOIN tb_statustask b on b.status_task_id = a.task_status_id
        INNER JOIN tb_section_task c on c.section_task_id = a.task_section_id
        WHERE ( a.modeltask_name LIKE :search 
        OR a.task_responsible LIKE :search )
        AND a.task_section_id = :searchsection  
        ORDER BY a.modeltask_id
        LIMIT $start , $itensPerPage;
        ", [
            ':search'=>'%'.$search.'%',
            ':searchsection'=>$searchsection
        ]);

    }
        $resultsTotal = $sql->select("select found_rows() as nrtotal ");

        return [
            'data'=>$results,
            'total'=>(int) $resultsTotal[0]["nrtotal"],
            'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
        ];
}

public static function calcPageMenu($page, $pagination, $search, $href = '/admin/modeltasks?')
{
    return parent::calcPageMenu($page, $pagination, $search, $href);
}

}
?>
