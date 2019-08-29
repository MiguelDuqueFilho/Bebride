<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class Deposition extends Model 
{

    public function getDeposition($event_id, $deposition_id) 
    {

        $sql = new Sql();
        
        $results = $sql->select("SELECT * 
        FROM tb_depositions 
        WHERE event_id = :event_id AND deposition_id = :deposition_id;
        ", [
            ':event_id'=>$event_id,
            ':deposition_id'=>$deposition_id
            ]);

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            EventTask::setNotification("Erro na função getDeposition(:event_id, :deposition_id) ","error");
        }

    }

    public static function getDepositionShow() 
    {

        $sql = new Sql();
        
        $results = $sql->select("SELECT * , 'no' as active 
        FROM tb_depositions a
        INNER JOIN tb_events b ON a.event_id = b.event_id
        WHERE deposition_show = '1'
        LIMIT 8;
        ");

        $results[0]['active'] =  'yes';
         return $results;

 
    }



    public function save()
    {
        $sql = new Sql();

        $results = $sql->select("call sp_deposition_save(:deposition_id, :event_id, :deposition_description, :deposition_urlphoto, :deposition_show)", 
            [
                ':deposition_id'=>(int) $this->getdeposition_id(),
                ':event_id'=>(int) $this->getevent_id(), 
                ':deposition_description'=>$this->getdeposition_description(), 
                ':deposition_urlphoto'=>$this->getdeposition_urlphoto(), 
                ':deposition_show'=>$this->getdeposition_show()
        ]);

        if (count($results) > 0) 
        {
            $this->setValues($results[0]);
        }
        else 
        {
            EventTask::setNotification("Erro na Inclusão ou Atualização de depoimentos.","error");
        }
    }

    public function delete() {

        $sql = new Sql();

        try 
        {
            $sql->query("DELETE FROM tb_depositions WHERE event_id = :event_id AND deposition_id = :deposition_id;", array(
                ":event_id"=>$this->getevent_id(),
                ":deposition_id"=>$this->getdeposition_id()
            ));
    
        } catch (\PDOException $e) {
            Events::setNotification("Função delete tb_depositions Erro:".$e->getMessage(),"error");
        }

    }
    
public static function getPage($event_id, $page = 1, $itensPerPage = 15)
{

    $start = ($page - 1) * $itensPerPage; 

    $sql = new Sql();

    $results = $sql->select("SELECT sql_calc_found_rows *  
        FROM tb_depositions a
        WHERE event_id = :event_id
        ORDER BY a.event_id, a.deposition_id
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
    FROM tb_depositions a
    WHERE event_id = :event_id
    AND ( a.deposition_description LIKE :search ) 
    ORDER BY a.event_id, a.deposition_id 
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

public static function calcPageMenu($page, $pagination, $search, $href = '/admin/depositions?')
{
    return parent::calcPageMenu($page, $pagination, $search, $href);
}

public function checkPhoto() 
{
    $dist = 
    $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
    "assets" . DIRECTORY_SEPARATOR . 
    "site" . DIRECTORY_SEPARATOR . 
    "img" . DIRECTORY_SEPARATOR . 
    "depositions" . DIRECTORY_SEPARATOR . 
    'deposition_' .
    $this->getdeposition_id() . ".jpg";


    if (file_exists($dist)) 
    {
        $url = "/assets/site/img/depositions/deposition_" . $this->getdeposition_id() . ".jpg" ;
    }
    else
    {
        $url = "/assets/site/img/depositions/deposition_0.jpg" ;
    }

    return $this->setdeposition_urlphoto($url);
}


public function setPhoto($file)
{


    if ($file['name'] == '')
    {
        $this->checkPhoto();
        return;
    }


    $extention = explode('.',$file['name']);
    $extention = end($extention);
    $extention = strtolower($extention); 


    switch($extention)
    {
        case "jpg":
        case "jpeg":
            $image = imagecreatefromjpeg($file['tmp_name']);
        break;

        case "gif":
            $image = imagecreatefromgif($file['tmp_name']);
        break;

        case "png":
            $image = imagecreatefrompng($file['tmp_name']);
        break;

    }

    $dist = 
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
            "assets" . DIRECTORY_SEPARATOR . 
            "site" . DIRECTORY_SEPARATOR . 
            "img" . DIRECTORY_SEPARATOR . 
            "depositions" . DIRECTORY_SEPARATOR . 
            'deposition_' .
            $this->getdeposition_id() . ".jpg";


    imagejpeg($image, $dist);

    imagedestroy($image);

    $this->checkPhoto();
}

public function getValues()
{
    $this->checkPhoto();

    $values = parent::getValues();

    return $values;
}

}?>