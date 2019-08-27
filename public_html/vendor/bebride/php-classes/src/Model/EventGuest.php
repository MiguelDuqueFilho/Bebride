<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;
use \BeBride\Mailer;

class EventGuest extends Model 
{

    const SECRET_USER = "BeBrideSecret_IG";
    const SECRET_IV = '';

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

public static function EventGuestInvite($event_id, $eventguest_id) {


    $sql = new Sql();


    $results = $sql->select("call sp_guestconfirm_create(:event_id, :eventguest_id)", array(
        ":event_id"=>$event_id,
        ":eventguest_id"=>$eventguest_id 
    ));


    if (count($results) === 0) 
    {
        EventGuest::setNotification("Não foi criar o convite .",'error');
    }
    else
    {
        $dataRecovery = $results[0];

        $openssl = openssl_encrypt(
            $dataRecovery["guestconfirm_id"], 
            "AES-128-ECB",
            EventGuest::SECRET_USER,
            0,
            EventGuest::SECRET_IV
        );

        $code = base64_encode($openssl);

        $link = "http://www.bebrideassessoria.com.br/invit/comfirm?code=$code";

        $mailer = new Mailer($dataRecovery["eventguest_email"], $dataRecovery["eventguest_name"], "Confirme a presença do Evento " . $dataRecovery["event_name"],"invit",
            array(
                "name"=>$dataRecovery["eventguest_name"],
                "link"=>$link
            )
        );

        $mailer->send();

        return $dataRecovery;
            
        };
    }



public static function validinvitDecrypt($code) {

    $codeCrypt = base64_decode($code);

    $guestconfirm_id = openssl_decrypt(
        $codeCrypt, 
        "AES-128-ECB",
        EventGuest::SECRET_USER,
        0,
        EventGuest::SECRET_IV
    );


    $sql = new Sql();

    $results = $sql->select("
        select * from tb_guestconfirm a
        inner join tb_events b  on a.event_id = b.event_id
        inner join tb_eventguests c on a.event_id = b.event_id and a.eventguest_id = c.eventguest_id
        where 	a.guestconfirm_id = :guestconfirm_id
        and     a.guestconfirm_date is null 
        ",
        Array
        (
            ":guestconfirm_id"=>$guestconfirm_id
        )
    );

    if (count($results) === 0) 
    {    
        EventGuest::setNotification("Não foi possivel confirmar a presença do evento.",'warning');
        return null;             
    }
    else
    {
        return $results[0];
    };
} 

public static function setInvitConfirm($eventguest_id, $guestconfirm_id, $eventguest_confirm, $remote_ip) 
{

    $sql = new Sql();


    $results = $sql->select("call sp_invitconfirm_update(:eventguest_id, :guestconfirm_id, :eventguest_confirm, :remote_ip)", array(
        ":eventguest_id"=>$eventguest_id,
        ":guestconfirm_id"=>$guestconfirm_id,
        ":eventguest_confirm"=>$eventguest_confirm,
        ":remote_ip"=>$remote_ip 
    ));


    if (count($results) === 0) 
    {
        EventGuest::setNotification("Não foi possivel atualizar o confirmação do convite.",'error');
        return null;
    }
    else
    {
        EventGuest::setNotification("Confirmação efetuada com sucesso.",'success');
    }

    return $results[0];

}


}?>