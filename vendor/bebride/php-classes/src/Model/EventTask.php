<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class EventTask extends Model 
{


    public function getEventTasks($event_id, $task_id) 
    {

        $sql = new Sql();
        
        $results = $sql->select("SELECT *  
        FROM tb_eventtasks a 
        INNER JOIN tb_statustask b on b.status_task_id = a.task_status_id
        INNER JOIN tb_section_task c on c.section_task_id = a.task_section_id
        WHERE a.event_id = :event_id AND a.task_id = :task_id
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
 
       
        $results = $sql->select("call sp_eventtask_save(:event_id, :task_id, :modeltask_id, :task_section_id, :task_name, :task_status_id, 
            :task_duration, :task_start, :task_finish, :task_completed, :task_responsible, :task_showboard, :task_showcustomer)", 
            [
            ':event_id'=>(int) $this->getevent_id(),
            ':task_id'=>(int) $this->gettask_id(),
            ':modeltask_id'=> (int) $this->getmodeltask_id(),
            ':task_section_id'=> (int) $this->gettask_section_id(),
            ':task_name'=>$this->gettask_name(),                
            ':task_status_id'=>(int) $this->gettask_status_id(),
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

        $sql->select("DELETE FROM tb_eventtasks WHERE event_id = :event_id AND task_id = :task_id;", array(
            ":event_id"=>$this->getevent_id(),
            ":task_id"=>$this->gettask_id()
        ));


    }
    
public static function getPage($event_id, $searchsection, $page = 1, $itensPerPage = 15)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();

    if($searchsection == "0") 
    {
        $results = $sql->select("SELECT sql_calc_found_rows *  
            FROM tb_eventtasks a 
            INNER JOIN tb_statustask b on b.status_task_id = a.task_status_id
            INNER JOIN tb_section_task c on c.section_task_id = a.task_section_id
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
            INNER JOIN tb_statustask b on b.status_task_id = a.task_status_id
            INNER JOIN tb_section_task c on c.section_task_id = a.task_section_id
            WHERE a.event_id = :event_id 
            AND a.task_section_id = :searchsection
            ORDER BY a.event_id, a.task_id  
            LIMIT $start , $itensPerPage;
            ", [
                ':event_id'=>$event_id,
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

public static function getPageSearch($event_id, $search, $searchsection, $page = 1, $itensPerPage = 15)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();
    
    if($searchsection == "0") 
    {

        $results = $sql->select("SELECT sql_calc_found_rows *  
        FROM tb_eventtasks a  
        INNER JOIN tb_statustask b on b.status_task_id = a.task_status_id
        INNER JOIN tb_section_task c on c.section_task_id = a.task_section_id
        WHERE a.event_id = :event_id
        AND ( a.task_name LIKE :search 
        OR b.task_status_name LIKE :search 
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
        INNER JOIN tb_statustask b on b.status_task_id = a.task_status_id
        INNER JOIN tb_section_task c on c.section_task_id = a.task_section_id
        WHERE a.event_id = :event_id
        AND ( a.task_name LIKE :search 
        OR b.task_status_name LIKE :search 
        OR a.task_responsible LIKE :search )
        AND a.task_section_id = :searchsection  
        ORDER BY a.event_id, a.task_id
        LIMIT $start , $itensPerPage;
        ", [
            ':event_id'=>$event_id,
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

    public static function calcPageMenu($page, $pagination, $search, $href = '/admin/eventtasks?')
    {
        return parent::calcPageMenu($page, $pagination, $search, $href);
    }


    public static function calcPageMenuImport($page, $pagination, $search, $href = '/admin/modeltasks?')
    {
    
        $pages = [];
    
        for ($x=0; $x < $pagination['pages_event']; $x++) { 
    
        
    
            if ($x == 0) 
            {
                $active = ($page === 1) ? $active='disabled' : '' ;
    
                array_push($pages, [
                    'href'=>$href . http_build_query([
                        'pages_event'=>$page-1,
                        'search'=>$search
                    ]),
                    'text'=>'Anterior',
                    'active'=>$active
                ]);
            }
    
            $active = ($page === $x+1) ? $active='active' : '' ;
    
            array_push($pages, [
                'href'=>$href . http_build_query([
                    'pages_event'=>$x+1,
                    'search'=>$search
                ]),
                'text'=>$x+1,
                'active'=>$active
            ]);
    
    
            if ($x+1 === (int) $pagination['pages_event']) 
            {
    
                $active = ($page < $pagination['pages_event']) ? '' :  $active='disabled';
    
                array_push($pages, [
                    'href'=>$href. http_build_query([
                        'pages_event'=>$page+1,
                        'search'=>$search
                    ]),
                    'text'=>'Proximo',
                    'active'=>$active
                ]);
            }
    
        }
        return $pages;
    }


   public static function getPageImportRelated($event_id, $searchsection, $page = 1, $itensPerPage = 15)
   {
   
       $start = ($page - 1) * $itensPerPage; 
   
       $sql = new Sql();
   
       if($searchsection == "0") 
       {

            $results = $sql->select("SELECT sql_calc_found_rows *  
                FROM tb_eventtasks a 
                INNER JOIN tb_statustask b ON b.status_task_id = a.task_status_id
                INNER JOIN tb_section_task c ON c.section_task_id = a.task_section_id
                WHERE a.modeltask_id IN 
                (
                    SELECT d.modeltask_id FROM tb_eventtasks d 
                    INNER JOIN tb_modeltasks e ON  d.modeltask_id = e.modeltask_id
                )
                AND         a.event_id = :event_id
                ORDER BY    a.event_id, a.task_id
                LIMIT $start , $itensPerPage;
                ", [
                   ':event_id'=>$event_id
                ]);

        }
       else 
       {
            $results = $sql->select("SELECT sql_calc_found_rows *  
                FROM tb_eventtasks a 
                INNER JOIN tb_statustask b ON b.status_task_id = a.task_status_id
                INNER JOIN tb_section_task c ON c.section_task_id = a.task_section_id
                WHERE a.modeltask_id IN 
                (
                    SELECT d.modeltask_id FROM tb_eventtasks d 
                    INNER JOIN tb_modeltasks e ON  d.modeltask_id = e.modeltask_id
                    WHERE d.task_section_id = :searchsection
                )
                AND       a.event_id = :event_id 
                AND         a.task_section_id = :searchsection
                ORDER BY    a.event_id, a.task_id
                LIMIT $start , $itensPerPage;
                ", [
                    ':event_id'=>$event_id,
                    ':searchsection'=>$searchsection
                ]);
       }
           $resultsTotal = $sql->select("select found_rows() as nrtotal ");
   
           return [
               'data'=>$results,
               'total'=>(int) $resultsTotal[0]["nrtotal"],
               'pages_event'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
           ];
   }
   
   public static function getPageSearchImportRelated($event_id, $search, $searchsection, $page = 1, $itensPerPage = 15)
   {
   
       $start = ($page - 1) * $itensPerPage; 
   
       $sql = new Sql();
       
       if($searchsection == "0") 
       {

            $results = $sql->select("SELECT sql_calc_found_rows *  
            FROM tb_eventtasks a 
            INNER JOIN tb_statustask b ON b.status_task_id = a.task_status_id
            INNER JOIN tb_section_task c ON c.section_task_id = a.task_section_id
            WHERE a.event_id = :event_id
            AND ( a.task_name LIKE :search 
            OR a.task_responsible LIKE :search )
            AND a.modeltask_id IN 
            (
                SELECT d.modeltask_id FROM tb_eventtasks d 
                INNER JOIN tb_modeltasks e ON  d.modeltask_id = e.modeltask_id
            )
            AND         a.event_id = :event_id
            ORDER BY    a.event_id, a.task_id
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
            INNER JOIN tb_statustask b ON b.status_task_id = a.task_status_id
            INNER JOIN tb_section_task c ON c.section_task_id = a.task_section_id
            WHERE a.event_id = :event_id
            AND ( a.task_name LIKE :search 
            OR a.task_responsible LIKE :search )
            AND a.task_section_id = :searchsection  
            AND a.modeltask_id IN 
            (
                SELECT d.modeltask_id FROM tb_eventtasks d 
                INNER JOIN tb_modeltasks e ON  d.modeltask_id = e.modeltask_id
                WHERE d.task_section_id = :searchsection
            )
            AND         a.event_id = :event_id
            ORDER BY    a.event_id, a.task_id
            LIMIT $start , $itensPerPage;
            ", [
               ':event_id'=>$event_id,
               ':search'=>'%'.$search.'%',
               ':searchsection'=>$searchsection
            ]);

 
       }
           $resultsTotal = $sql->select("select found_rows() as nrtotal ");
   
           return [
               'data'=>$results,
               'total'=>(int) $resultsTotal[0]["nrtotal"],
               'pages_event'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
           ];
   }
   

}
?>
