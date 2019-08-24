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
            WHERE a.event_id = :event_id and a.task_id = :task_id
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

    public function getModelEventTasks($event_id, $task_id) 
    {

        $sql = new Sql();
        
        $results = $sql->select("SELECT *  
            FROM tb_eventtasks a 
            INNER JOIN tb_statustask b on b.status_task_id = a.task_status_id
            INNER JOIN tb_section_task c on c.section_task_id = a.task_section_id
            WHERE a.event_id = :event_id and a.modeltask_id = :modeltask_id
        ", [
            ':event_id'=>$event_id,
            ':modeltask_id'=>$task_id
        ]);

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            EventTask::setNotification("Erro na função getModelEventTasks(".$event_id.", " . $task_id . ") ","error");
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
            :task_duration, :task_predecessors, :task_successors, :task_start, :task_finish, :task_completed, :task_responsible, 
            :task_showboard, :task_showcustomer, :task_calculatetask)", 
            [
            ':event_id'=>(int) $this->getevent_id(),
            ':task_id'=>(int) $this->gettask_id(),
            ':modeltask_id'=> (int) $this->getmodeltask_id(),
            ':task_section_id'=> (int) $this->gettask_section_id(),
            ':task_name'=>$this->gettask_name(),                
            ':task_status_id'=>(int) $this->gettask_status_id(),
            ':task_duration'=>(int) $this->gettask_duration(),
            ':task_predecessors'=>(int) $this->gettask_predecessors(),
            ':task_successors'=>(int) $this->gettask_successors(),
            ':task_start'=>convertdate($this->gettask_start()),
            ':task_finish'=>convertdate($this->gettask_finish()),
            ':task_completed'=>(int) $this->gettask_completed(),
            ':task_responsible'=>$this->gettask_responsible(),            
            ':task_showboard'=> $this->gettask_showboard(),           
            ':task_showcustomer'=> $this->gettask_showcustomer(),
            ':task_calculatetask'=> $this->gettask_calculatetask()
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

        try 
        {
            $sql->query("DELETE FROM tb_eventtasks WHERE event_id = :event_id AND task_id = :task_id;", array(
                ":event_id"=>$this->getevent_id(),
                ":task_id"=>$this->gettask_id()
            ));
    
        } catch (\PDOException $e) {
            Events::setNotification("Função delete tb_eventtasks Erro:".$e->getMessage(),"error");
        }

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
   


   public static function taskEventInitial($event_id) 
   {
    
        $event = new Events();
        $event_task = new EventTask();
        $modeltask = new ModelTask();
        
        $event->getEvent((int) $event_id);

        $modeltask->getModelTasks('1');

        $event_task->settask_id('0');
        $event_task->setevent_id($event_id);
        $event_task->setmodeltask_id($modeltask->getmodeltask_id());
        $event_task->settask_section_id($modeltask->getmodeltask_section_id());
        $event_task->settask_name($modeltask->getmodeltask_name());
        $event_task->settask_status_id('1');
        $event_task->settask_duration($modeltask->getmodeltask_duration());
        $event_task->settask_predecessors($modeltask->getmodeltask_predecessors());
        $event_task->settask_successors($modeltask->getmodeltask_successors());
        $event_task->settask_start($event->getevent_start());
        $event_task->settask_finish($event->getevent_start());
        $event_task->settask_completed('0');
        $event_task->settask_responsible($modeltask->getmodeltask_responsible());
        $event_task->settask_showboard($modeltask->getmodeltask_showboard());
        $event_task->settask_showcustomer($modeltask->getmodeltask_showcustomer());
        $event_task->settask_calculatetask($modeltask->getmodeltask_calculatetask());
        $event_task->save();
    
        $modeltask->getModelTasks('2');
        
        $event_task->settask_id('0');
        $event_task->setevent_id($event_id);
        $event_task->setmodeltask_id($modeltask->getmodeltask_id());
        $event_task->settask_section_id($modeltask->getmodeltask_section_id());
        $event_task->settask_name($modeltask->getmodeltask_name());
        $event_task->settask_status_id('1');
        $event_task->settask_duration($modeltask->getmodeltask_duration());
        $event_task->settask_predecessors($modeltask->getmodeltask_predecessors());
        $event_task->settask_successors($modeltask->getmodeltask_successors());
        $event_task->settask_start($event->getevent_date());
        $event_task->settask_finish($event->getevent_date());
        $event_task->settask_completed('0');
        $event_task->settask_responsible($modeltask->getmodeltask_responsible());
        $event_task->settask_showboard($modeltask->getmodeltask_showboard());
        $event_task->settask_showcustomer($modeltask->getmodeltask_showcustomer());
        $event_task->settask_calculatetask($modeltask->getmodeltask_calculatetask());
        $event_task->save();

        $modeltask->getModelTasks('3');
        
        $event_task->settask_id('0');
        $event_task->setevent_id($event_id);
        $event_task->setmodeltask_id($modeltask->getmodeltask_id());
        $event_task->settask_section_id($modeltask->getmodeltask_section_id());
        $event_task->settask_name($modeltask->getmodeltask_name());
        $event_task->settask_status_id('1');
        $event_task->settask_duration($modeltask->getmodeltask_duration());
        $event_task->settask_predecessors($modeltask->getmodeltask_predecessors());
        $event_task->settask_successors($modeltask->getmodeltask_successors());
        $event_task->settask_start($event->getevent_finish());
        $event_task->settask_finish($event->getevent_finish());
        $event_task->settask_completed('0');
        $event_task->settask_responsible($modeltask->getmodeltask_responsible());
        $event_task->settask_showboard($modeltask->getmodeltask_showboard());
        $event_task->settask_showcustomer($modeltask->getmodeltask_showcustomer());
        $event_task->settask_calculatetask($modeltask->getmodeltask_calculatetask());
        $event_task->save();


    }

   public static function calcTaskPredecessors($event_id) 
   {
        
        $sql = new Sql();
        
        $resultsPred = $sql->select("SELECT * 
            FROM tb_eventtasks 
            WHERE event_id = :event_id 
            AND ( task_section_id != '1' AND modeltask_id != '0' AND task_calculatetask != '1')
            order by task_predecessors
        ", [
            ':event_id'=>$event_id
        ]);


        if (count($resultsPred) == 0)  
        {
            return false;
        }

        $loop = true;
        $icalc = 0;

        do {
            
            $loop = false;

            foreach ( $resultsPred as &$itemPred ) {

                $event_task = new EventTask();
                $event_task->setValues($itemPred);

                $predecessors = $event_task->gettask_predecessors();

                if ($predecessors == '0')
                {
                    $predecessors = '1';
                }

                $event_pred = new EventTask();
                $event_pred->getModelEventTasks($event_id, $predecessors);
 
                if ($event_pred->gettask_section_id() == '1' ||  $event_pred->gettask_calculatetask() == '1')
                {
                    $finishDate = $event_pred->gettask_finish();

                    $event_task->settask_start(somar_dias_uteis($finishDate, 1));
                    $event_task->settask_finish(somar_dias_uteis($finishDate, $event_task->gettask_duration()));

                    $event_task->settask_calculatetask('1');

                    $event_task->save();
                    $icalc++;
                }
                else 
                {
                        $loop = true;
                }                
            }
 
        } while ($loop == true);

        EventTask::setNotification("Recalculada: ".$icalc." tarefas predecessoras.","info");
        return true;
    }

   public function calcTaskSuccessors($event_id) 
   {         
         $sql = new Sql();
         
         $resultsSucc = $sql->select("SELECT * 
             FROM tb_eventtasks 
             WHERE event_id = :event_id 
             AND ( task_section_id != '1' AND modeltask_id != '0'  AND task_successors != '0')
             order by task_start desc
         ", [
             ':event_id'=>$event_id
         ]);
 
 
         if (count($resultsSucc) == 0)  
         {
             return false;
         }
 
         $loop = true;
         $icalc = 0;
 
         do {
             
             $loop = false;

             
             foreach ( $resultsSucc as &$itemPred ) {
 
                 $event_task = new EventTask();
                 $event_task->setValues($itemPred);
 
                 $predecessors = $event_task->gettask_predecessors();
                 $successors = $event_task->gettask_successors();

                 $event_succ = new EventTask();
                 $event_succ->getModelEventTasks($event_id, $successors);
  
                 if ($event_succ->gettask_section_id() == '1' ||  $event_succ->gettask_calculatetask() == '1')
                 {
                     $startDate = $event_succ->gettask_start();
 
                     $startCalc = subtrair_dias_uteis($startDate, $event_task->gettask_duration());
                     $finishCalc = subtrair_dias_uteis($startDate, 1);

                     if ($predecessors == '0')
                     {
                        $event_task->settask_finish($finishCalc);
                        $event_task->settask_start($startCalc);   
                     }

                     $event_task->settask_calculatetask('1');
 
                     $event_task->save();
                     $icalc++;
                 }
                 else 
                 {
                         $loop = true;
                 }                
             }

         } while ($loop == true);
 
         EventTask::setNotification("Recalculada: ".$icalc." tarefas sucessoras.","info");
         return true;
     }
}

?>