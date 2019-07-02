<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;
use \BeBride\Mailer;


class User extends Model {

    const SESSION = "user"; 
    const SECRET_USER = "BeBrideSecret_US";
    const SECRET_IV = '';

    public static function getFromSession() 
    {

        $user = new User();

        if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]["user_id"] > 0) 
        {

            $user->setValues($_SESSION[User::SESSION]);

        }

        return $user;
    }

    

    public static function login($login, $password) 
    {
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.person_id = b.person_id WHERE login_name = :LOGIN", array(
            ":LOGIN"=>$login
        ));

        if (count($results) === 0) 
        {
            User::setNotification("Login inexistente ou Senha Invalida.",'warning');
            return null;
        }

        $data = $results[0];

        if (password_verify($password,$data["password_hash"])) 
        {
            $user = new User();
            
            $user->setValues($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
        }
        else
        {
            return null;
        }
    }

public static function checkLogin($user_type_id = 0) //não revisado totalmente 
    {

        if (
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["user_type_id"] > 0
        )
        {
            // Não esta logado
           return false;
        }
        else
        {
            switch ($user_type_id) {
                case 0:  // visitante não implementado
                return false;
                break;
                case 1:  // administrador do site
                return true;
                break;
                case 2:  // clientes do site (somente algumas visualizações)
                return true;
                break;
                case 3:  // fornecedor ainda não implementado 
                return false;
                break;
            }
            
            // rota de administrador
            // if ($user_type_id = 1 && (bool)$_SESSION[User::SESSION]["user_type_id"] === 1 ) 
            // {
            //     echo "true 1";
            //     exit;

            //    return true;
            // }
            // else if ($user_type_id === false) 
            // {
            //     // Ele esta logado , mas não estamos exigindo que seja uma rota de administração
            //     echo "true 2 ";
            //     exit;

            //     return true;
            // } 
            // else 
            // {
            //     // Se saiu deste padrão e por que não esta logado 
            //         echo "false 1";
            //         exit;

            //        return false;
            // }
        }

    }

    public static function verifyLogin($user_type_id = 1) 
    {
        if  (!User::checkLogin($user_type_id))
        {
            if ($user_type_id === 1) // usuário administrador
            {
				header("Location: /admin/login");
			} else {
				header("Location: /login");
            }
            header("Location: /login");
			exit;
        }
    }

    public static function logout()
    {
        $_SESSION[User::SESSION] = null;
    }


    public static function listAll() 
    {
        $sql = new Sql();
        
        return $sql->select("SELECT * , CONCAT_WS(' ',b.person_firstname,b.person_lastname) AS person_fullname  
            FROM tb_users a 
            INNER JOIN tb_persons b on b.person_id = a.person_id
            INNER JOIN tb_userstype c on c.user_type_id = a.user_type_id
            ORDER BY b.person_firstname, b.person_lastname");

    }

    public function save() 
    {

        $sql = new Sql();

        $results = $sql->select("call sp_users_save(:person_firstname, :person_lastname, :login_name, :password_hash, :person_email, :person_phone, :user_type_id)", array(
            ":person_firstname"=>$this->getperson_firstname(),
            ":person_lastname"=>$this->getperson_lastname(),
            ":login_name"=>$this->getlogin_name(),
            ":password_hash"=>User::getPasswordHash($this->getpassword_hash()),
            ":person_email"=>$this->getperson_email(),
            ":person_phone"=>$this->getperson_phone(),
            ":user_type_id"=>$this->getuser_type_id()
       ));

        $this->setValues($results[0]);
    }

    public function delete() {

        $sql = new Sql();

        $sql->select("call sp_users_delete(:user_id)", array(
            ":user_id"=>$this->getuser_id()
        ));


    }

    public function getUser($user_id) {

        $sql = new Sql();
        
        $results = $sql->select("SELECT * , CONCAT_WS(' ',b.person_firstname,b.person_lastname) AS person_fullname  
            FROM tb_users a 
            INNER JOIN tb_persons b on b.person_id = a.person_id
            INNER JOIN tb_userstype c on c.user_type_id = a.user_type_id
            WHERE a.user_id = :user_id", array(
            ":user_id"=>$user_id
        ));

        $data = $results[0];

        $this->setValues($data);

    }

    public function update() 
    {

        $sql = new Sql();

        $results = $sql->select("call sp_usersupdate_save(
            :user_id, 
            :person_firstname, 
            :person_lastname, 
            :login_name, 
            :password_hash, 
            :person_email, 
            :person_phone, 
            :person_whatsapp
            :person_facebook
            :person_instagram
            :user_type_id,
            :company_name,
            person_jobrole
            )", 
            array(
            ":user_id"=>$this->getuser_id(),
            ":person_firstname"=>$this->getperson_firstname(),
            ":person_lastname"=>$this->getperson_lastname(),
            ":login_name"=>$this->getlogin_name(),
            ":password_hash"=>User::getPasswordHash($this->getpassword_hash()),
            ":person_email"=>$this->getperson_email(),
            ":person_phone"=>$this->getperson_phone(),
            ":person_whatsapp"=>$this->getperson_whatsapp(),
            ":person_facebook"=>$this->getperson_facebook(),
            ":person_instagram"=>$this->getperson_instagram(),
            ":user_type_id"=>$this->getuser_type_id(),
            ":company_name"=>$this->getcompany_name(),
            ":person_jobrole"=>$this->getperson_jobrole()

        ));

        $this->setValues($results[0]);
    }


    public static function getForgot($person_email, $user_type_id = 1) {


        $sql = new Sql();

        $results = $sql->select("SELECT * , CONCAT_WS(' ',a.person_firstname,a.person_lastname) AS person_fullname FROM tb_persons a INNER JOIN tb_users b USING(person_id) WHERE a.person_email = :person_email",array(
            ":person_email"=>$person_email
        ));

        if (count($results) === 0) 
        {
            User::setNotification("Não foi possivel recuparar a senha.",'warning');
        }
        else
        {
            $data = $results[0];

            $resultsforgot = $sql->select("call sp_userspasswordsrecoveries_create(:user_id, :remote_ip)", array(
                ":user_id"=>$data["user_id"],
                ":remote_ip"=>$_SERVER["REMOTE_ADDR"] 
            ));

            if (count($resultsforgot) === 0) 
            {
                User::setNotification("Não foi possivel recuparar a senha.",'warning');
            }
            else
            {
                $dataRecovery = $resultsforgot[0];

                $openssl = openssl_encrypt(
                    $dataRecovery["recovery_id"], 
                    "AES-128-ECB",
                    User::SECRET_USER,
                    0,
                    User::SECRET_IV
                );
                $code = base64_encode($openssl);

                if ($user_type_id === 1) 
                {
//                    $link = "http://www.bebrideassessoria.com.br/admin/forgot/reset?code=$code";
                    $link = "http://www.bebrideassessoria.com.br/forgot/reset?code=$code";
                }
                else
                {
                    $link = "http://www.bebrideassessoria.com.br/forgot/reset?code=$code";

                }

                $mailer = new Mailer($data["person_email"], $data["person_fullname"], "Redefinir senha da BeBride Assessoria","forgot",
                    array(
                        "name"=>$data["person_fullname"],
                        "link"=>$link
                    )
                );

                $mailer->send();

                return $data;
                
            };
        }

    }

    public static function validForgotDecrypt($code) {

        $codeCrypt = base64_decode($code);

        $recovery_id = openssl_decrypt(
            $codeCrypt, 
            "AES-128-ECB",
            User::SECRET_USER,
            0,
            User::SECRET_IV
        );


        $sql = new Sql();

        $results = $sql->select("
            select * , CONCAT_WS(' ',c.person_firstname,c.person_lastname) AS person_fullname  from tb_userspasswordsrecoveries a
            inner join tb_users b  on a.user_id = b.user_id
            inner join tb_persons c on b.person_id = c.person_id
            where 	a.recovery_id = :recovery_id 
            and     a.recovery_date is null 
            and		date_add(a.created_at, interval 1 hour) >= now()
            ",
            Array
            (
                ":recovery_id"=>$recovery_id
            )
        );

        if (count($results) === 0) 
        {    
            User::setNotification("Não foi possivel recuparar a senha.",'warning');
            return null;             
        }
        else
        {
            return $results[0];
        };
    } 

    public static function setForgotUsed($recovery_id) 
    {
        $sql = new Sql();

        $sql->query("UPDATE tb_userspasswordsrecoveries SET recovery_date = now() WHERE recovery_id = :recovery_id", array(
            ":recovery_id"=>$recovery_id
        ));
    }

    public function setPassword($password_hash) {
        $sql = new Sql();

        $sql->query("UPDATE tb_users SET password_hash = :password_hash WHERE user_id = :user_id", array(
            ":password_hash"=>$password_hash,
            ":user_id"=>$this->getuser_id()
        ));

    }

	public static function getPasswordHash($password_hash)
	{

		return password_hash($password_hash, PASSWORD_DEFAULT, [
			'cost'=>12
		]);

	}

    public static function checkLoginExist($login) 
    {
        
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE login_name = :login_name", [
            ':login_name'=>$login
        ]);

        return (count($results) > 0 );

    }

//     public function getorders() 
//     {
//         $sql = new Sql();

//         $results = $sql->select("
//             SELECT * FROM tb_orders a
//                 INNER JOIN tb_ordersstatus b USING(idstatus) 
//                 INNER JOIN tb_carts c USING(idcart) 
//                 INNER JOIN tb_users d on d.iduser = a.iduser 
//                 INNER JOIN tb_addresses e USING(idaddress) 
//                 INNER JOIN tb_persons f on f.idperson = d.idperson 
//                 WHERE d.iduser  = :iduser ", [
//                 ':iduser'=>$this->getiduser()
//         ]);

//         return $results;
//     }

    public static function getPage($page = 1, $itensPerPage = 10)
    {

        $start = ($page - 1) * $itensPerPage; 

        $sql = new Sql();

        $results = $sql->select("SELECT sql_calc_found_rows * , CONCAT_WS(' ',b.person_firstname,b.person_lastname) AS person_fullname  
            FROM tb_users a 
            INNER JOIN tb_persons b on b.person_id = a.person_id
            INNER JOIN tb_userstype c on c.user_type_id = a.user_type_id
            ORDER BY b.person_firstname, b.person_lastname
            LIMIT $start , $itensPerPage;
            ");

            $resultsTotal = $sql->select("select found_rows() as nrtotal ");

            return [
                'data'=>$results,
                'total'=>(int) $resultsTotal[0]["nrtotal"],
                'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
            ];
    }

    public static function getPageSearch($search, $page = 1, $itensPerPage = 10)
    {

        $start = ($page - 1) * $itensPerPage; 

        $sql = new Sql();

        $results = $sql->select("SELECT sql_calc_found_rows * , CONCAT_WS(' ',b.person_firstname,b.person_lastname) AS person_fullname  
            FROM tb_users a 
            INNER JOIN tb_persons b on b.person_id = a.person_id
            INNER JOIN tb_userstype c on c.user_type_id = a.user_type_id
            WHERE b.person_firstname LIKE :search OR b.person_lastname LIKE :search OR b.person_email LIKE :search OR  a.login_name LIKE :search 
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

    public static function calcPageMenu($page, $pagination, $search, $href = '/admin/users?')
    {

        $pages = [];

        for ($x=0; $x < $pagination['pages']; $x++) { 

        

            if ($x == 0) 
            {
                $active = ($page === 1) ? $active='disabled' : '' ;

                array_push($pages, [
                    'href'=>$href . http_build_query([
                        'page'=>$page-1,
                        'search'=>$search
                    ]),
                    'text'=>'Anterior',
                    'active'=>$active
                ]);
            }

            $active = ($page === $x+1) ? $active='active' : '' ;

            array_push($pages, [
                'href'=>'/admin/users?' . http_build_query([
                    'page'=>$x+1,
                    'search'=>$search
                ]),
                'text'=>$x+1,
                'active'=>$active
            ]);


            if ($x+1 === (int) $pagination['pages']) 
            {

                $active = ($page < $pagination['pages']) ? '' :  $active='disabled';

                array_push($pages, [
                    'href'=>'/admin/users?' . http_build_query([
                        'page'=>$page+1,
                        'search'=>$search
                    ]),
                    'text'=>'Proximo',
                    'active'=>$active
                ]);
            }

        }
        return $pages;
    }

}

?>