<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class ModelTask extends Model 
{




    public function getModelTasks($modeltask_id) 
    {

        $sql = new Sql();
        
        $results = $sql->select("SELECT *  
        FROM tb_modeltasks a 
        INNER JOIN tb_section_task b on b.section_task_id = a.modeltask_section_id
        WHERE a.modeltask_id = :modeltask_id
        ", [
            ':modeltask_id'=>$modeltask_id
        ]);

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            ModelTask::setNotification("Erro na função getModelTasks(:getModelTasks) ","error");
        }

    }


    public function save()
    {
        $sql = new Sql();
 
       
        $results = $sql->select("call sp_modeltasks_save(
            :modeltask_id,
            :modeltask_section_id,
            :modeltask_name, 
            :modeltask_duration, 
            :modeltask_predecessors,
            :modeltask_successors,
            :modeltask_responsible, 
            :modeltask_showboard,
            :modeltask_showcustomer)", 
            [
            ':modeltask_id'=>(int) $this->getmodeltask_id(),
            ':modeltask_section_id'=>(int) $this->getmodeltask_section_id(),
            ':modeltask_name'=>$this->getmodeltask_name(),
            ':modeltask_duration'=>(int) $this->getmodeltask_duration(),                
            ':modeltask_predecessors'=>(int) $this->getmodeltask_predecessors(),
            ':modeltask_successors'=>(int) $this->getmodeltask_successors(),
            ':modeltask_responsible'=>$this->getmodeltask_responsible(),
            ':modeltask_showboard'=>(int) $this->getmodeltask_showboard(),
            ':modeltask_showcustomer'=>(int) $this->getmodeltask_showcustomer()
        ]);


        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            ModelTask::setNotification("Erro na Inclusão ou Atualização de Tarefas.","error");
        }
    }

    public function delete() {

        $sql = new Sql();

        $sql->select("DELETE FROM tb_modeltasks WHERE modeltask_id = :modeltask_id", array(
            ":modeltask_id"=>$this->getmodeltask_id()
        ));

    }
    
public static function getPage( $searchsection, $page = 1, $itensPerPage = 15)
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
            'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage),
            'pages_model'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
        ];
}

public static function getPageSearch($search, $searchsection, $page = 1, $itensPerPage = 15)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();
    
    if($searchsection == "0") 
    {

        $results = $sql->select("SELECT sql_calc_found_rows *  
        FROM tb_modeltasks a  
        INNER JOIN tb_section_task b on b.section_task_id = a.modeltask_section_id
        WHERE ( a.modeltask_name LIKE :search 
        OR a.modeltask_responsible LIKE :search 
        OR b.section_task_name LIKE :search )
        ORDER BY a.modeltask_id 
        LIMIT $start , $itensPerPage;
        ", [
            ':search'=>'%'.$search.'%', 
        ]);

    }
    else 
    {
        $results = $sql->select("SELECT sql_calc_found_rows *  
        FROM tb_modeltasks a 
        INNER JOIN tb_section_task b on b.section_task_id = a.task_section_id
        WHERE a.modeltask_section_id = :searchsection 
        AND ( a.modeltask_name LIKE :search 
        OR a.task_responsible LIKE :search
        OR b.section_task_name LIKE :search  )
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
            'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage),
            'pages_model'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)        ];
}

public static function calcPageMenu($page, $pagination, $search, $href = '/admin/modeltasks?')
{
    return parent::calcPageMenu($page, $pagination, $search, $href);
}

public static function calcPageMenuImport($page, $pagination, $search, $href = '/admin/modeltasks?')
{

    $pages = [];

    for ($x=0; $x < $pagination['pages_model']; $x++) { 

    

        if ($x == 0) 
        {
            $active = ($page === 1) ? $active='disabled' : '' ;

            array_push($pages, [
                'href'=>$href . http_build_query([
                    'pages_model'=>$page-1,
                    'search'=>$search
                ]),
                'text'=>'Anterior',
                'active'=>$active
            ]);
        }

        $active = ($page === $x+1) ? $active='active' : '' ;

        array_push($pages, [
            'href'=>$href . http_build_query([
                'pages_model'=>$x+1,
                'search'=>$search
            ]),
            'text'=>$x+1,
            'active'=>$active
        ]);


        if ($x+1 === (int) $pagination['pages_model']) 
        {

            $active = ($page < $pagination['pages_model']) ? '' :  $active='disabled';

            array_push($pages, [
                'href'=>$href. http_build_query([
                    'pages_model'=>$page+1,
                    'search'=>$search
                ]),
                'text'=>'Proximo',
                'active'=>$active
            ]);
        }

    }
    return $pages;
}

    
public static function getPageImportNotRelated( $searchsection, $page = 1, $itensPerPage = 15)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();

    if($searchsection == "0") 
    {
        $results = $sql->select("SELECT sql_calc_found_rows * 
            FROM tb_modeltasks c
            INNER JOIN tb_section_task d on d.section_task_id = c.modeltask_section_id 
            WHERE c.modeltask_id NOT IN 
            (
                SELECT a.modeltask_id 
                    FROM tb_eventtasks a 
                    INNER JOIN tb_modeltasks b ON a.modeltask_id = b.modeltask_id
            )
            ORDER BY c.modeltask_id   
            LIMIT $start , $itensPerPage;
        ");
    }
    else 
    {
        $results = $sql->select("SELECT sql_calc_found_rows * from tb_modeltasks c
            INNER JOIN tb_section_task d on d.section_task_id = c.modeltask_section_id 
            where  c.modeltask_id not in 
            (
                select a.modeltask_id 
                    from tb_eventtasks a 
                    inner join tb_modeltasks b on  a.modeltask_id = b.modeltask_id
                    where a.task_section_id = :searchsection
            )
            AND c.modeltask_section_id = :searchsection
            ORDER BY c.modeltask_id  
            LIMIT $start , $itensPerPage;
            ", [
                ':searchsection'=>$searchsection
            ]);
    }      

        $resultsTotal = $sql->select("select found_rows() as nrtotal ");
        
        return [
            'data'=>$results,
            'total'=>(int) $resultsTotal[0]["nrtotal"],
            'pages_model'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
        ];
}

public static function getPageSearchImportNotRelated($search, $searchsection, $page = 1, $itensPerPage = 15)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();
    
    if($searchsection == "0") 
    {

        $results = $sql->select("SELECT sql_calc_found_rows * 
            FROM tb_modeltasks c
            INNER JOIN tb_section_task d on d.section_task_id = c.modeltask_section_id 
            WHERE ( c.modeltask_name LIKE :search 
            OR c.modeltask_responsible LIKE :search)
            AND c.modeltask_id NOT IN 
            (
                SELECT a.modeltask_id 
                    FROM tb_eventtasks a 
                    INNER JOIN tb_modeltasks b ON a.modeltask_id = b.modeltask_id
            )
            ORDER BY c.modeltask_id  
            LIMIT $start , $itensPerPage;
        ", [
            ':search'=>'%'.$search.'%', 
        ]);

    }
    else 
    {
        $results = $sql->select("SELECT sql_calc_found_rows * 
            FROM tb_modeltasks c
            INNER JOIN tb_section_task d on d.section_task_id = c.modeltask_section_id 
            
            WHERE ( c.modeltask_name LIKE :search 
            OR c.modeltask_responsible LIKE :search )
            AND c.modeltask_id NOT IN 
            (
                SELECT a.modeltask_id 
                    FROM tb_eventtasks a 
                    INNER JOIN tb_modeltasks b ON a.modeltask_id = b.modeltask_id
                    WHERE a.task_section_id = :searchsection
            )
            AND c.modeltask_section_id = :searchsection
            ORDER BY c.modeltask_id  
            LIMIT $start , $itensPerPage;
        ",[
            ':search'=>'%'.$search.'%',
            ':searchsection'=>$searchsection
        ]);

    }
        $resultsTotal = $sql->select("select found_rows() as nrtotal ");

           return [
            'data'=>$results,
            'total'=>(int) $resultsTotal[0]["nrtotal"],
            'pages_model'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)        ];
}



}
?>
