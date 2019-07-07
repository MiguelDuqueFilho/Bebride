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

    public static function getUserTypeFromSession() 
    {

        $user_type_id = "";

        if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]["user_id"] > 0) 
        {

            $user_type_id = $_SESSION[User::SESSION]["user_type_id"];

        }

        return $user_type_id;
    }

    

    public static function login($login, $password) 
    {
        $sql = new Sql();
        
        $results = $sql->select("SELECT * , CONCAT_WS(' ',b.person_firstname,b.person_lastname) AS person_fullname  
            FROM tb_users a 
            INNER JOIN tb_persons b on b.person_id = a.person_id
            INNER JOIN tb_userstype c on c.user_type_id = a.user_type_id
            WHERE login_name = :LOGIN", array(
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
            !(int)$_SESSION[User::SESSION]["user_id"] > 0
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
                    if ((int)$_SESSION[User::SESSION]["user_type_id"] === 1 )
                    { 
                        return true;
                    }
                    return false;
                break;
                case 2:  // clientes do site (somente algumas visualizações)
                    if ((int)$_SESSION[User::SESSION]["user_type_id"] === 2 )
                    { 
                        return true;
                    }
                    return false;
                break;
                case 3:  // fornecedor ainda não implementado 
                    return false;
                break;
                default:
                    // fora do padrão  
                    return false;
                break;
            }
            
        }

    }

    public static function verifyLogin($user_type_id = 0) 
    {
        if  (!User::checkLogin($user_type_id))
        {
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

       $results = $sql->select("call sp_users_save(
        :person_firstname, 
        :person_lastname, 
        :login_name, 
        :password_hash,
        :person_email, 
        :person_phone, 
        :user_type_id,
        :person_whatsapp,
        :person_facebook,
        :person_instagram,
        :company_name,
        :person_jobrole,
        :person_about,
        :person_urlphoto
        )", 
        array(
        ":person_firstname"=>$this->getperson_firstname(),
        ":person_lastname"=>$this->getperson_lastname(),
        ":login_name"=>$this->getperson_email(),
        ":password_hash"=>User::getPasswordHash($this->getpassword_hash()),
        ":person_email"=>$this->getperson_email(),
        ":person_phone"=>(int) $this->getperson_phone(),
        ":user_type_id"=>$this->getuser_type_id(),
        ":person_whatsapp"=>(int) $this->getperson_whatsapp(),
        ":person_facebook"=>$this->getperson_facebook(),
        ":person_instagram"=>$this->getperson_instagram(),
        ":company_name"=>$this->getcompany_name(),
        ":person_jobrole"=>$this->getperson_jobrole(),
        ":person_about"=>$this->getperson_about(),
        ':person_urlphoto'=>$this->getperson_urlphoto()
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
            :person_email, 
            :person_phone, 
            :person_whatsapp,
            :person_facebook,
            :person_instagram,
            :user_type_id,
            :company_name,
            :person_jobrole,
            :person_about,
            :person_urlphoto
            )", 
            array(
            ":user_id"=>$this->getuser_id(),
            ":person_firstname"=>$this->getperson_firstname(),
            ":person_lastname"=>$this->getperson_lastname(),
            ":login_name"=>$this->getlogin_name(),
            ":person_email"=>$this->getperson_email(),
            ":person_phone"=>(int) $this->getperson_phone(),
            ":person_whatsapp"=>(int) $this->getperson_whatsapp(),
            ":person_facebook"=>$this->getperson_facebook(),
            ":person_instagram"=>$this->getperson_instagram(),
            ":user_type_id"=>$this->getuser_type_id(),
            ":company_name"=>$this->getcompany_name(),
            ":person_jobrole"=>$this->getperson_jobrole(),
            ":person_about"=>$this->getperson_about(),
            ':person_urlphoto'=>$this->getperson_urlphoto()
        ));

        $this->setValues($results[0]);
    }


    public static function getForgot($login_name, $user_type_id = 1) {


        $sql = new Sql();

        $results = $sql->select("SELECT * , CONCAT_WS(' ',a.person_firstname,a.person_lastname) AS person_fullname 
        FROM tb_persons a 
        INNER JOIN tb_users b USING(person_id) 
        INNER JOIN tb_userstype c on c.user_type_id = b.user_type_id
        WHERE b.login_name = :login_name",array(
            ":login_name"=>$login_name
        ));

        if (count($results) === 0) 
        {
            User::setNotification("Não foi possivel recuperar a senha.",'warning');
            $data = NULL;
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
                User::setNotification("Não foi possivel recuperar a senha.",'error');
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
                    $link = "http://www.bebrideassessoria.com.br/forgot/reset?code=$code";
                }
                else
                {
                    $link = "http://www.bebrideassessoria.com.br/forgot/reset?code=$code";

                }

                $mailer = new Mailer($data["person_email"], $data["person_fullname"], "Redefinir senha da BeBride Assessoria","forgot",
                    array(
                        "name"=>$data["person_firstname"],
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
            INNER JOIN tb_userstype d on d.user_type_id = b.user_type_id
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

    public function setPassword($password_hash) 
    {
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

        $results = $sql->select("SELECT * 
        FROM tb_users a
        INNER JOIN tb_userstype b on b.user_type_id = a.user_type_id
        WHERE login_name = :login_name", [
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


public function checkPhoto() 
{
    $dist = 
    $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
    "assets" . DIRECTORY_SEPARATOR . 
    "site" . DIRECTORY_SEPARATOR . 
    "img" . DIRECTORY_SEPARATOR . 
    "faces" . DIRECTORY_SEPARATOR . 
    'avatar_' .
    $this->getuser_id() . ".jpg";


    if (file_exists($dist)) 
    {
        $url = "/assets/site/img/faces/avatar_" . $this->getuser_id() . ".jpg" ;
    }
    else
    {
        $url = "/assets/site/img/faces/avatar_0.jpg" ;
    }

    return $this->setperson_urlphoto($url);
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
            "faces" . DIRECTORY_SEPARATOR . 
            'avatar_' .
            $this->getuser_id() . ".jpg";


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

public static function sendMailfromclient()
{

    $data = array (
        "person_email"=>Mailer::USERNAME,
        "person_fullname"=>Mailer::NAME_FROM,
        "subject"=>"Dúvidas ou Sugestões",
        "tplname"=>"client");

    $mailer = new Mailer(
        $data["person_email"],                      //  $toAddress  
        $data["person_fullname"],                   //  $toName, 
        $data["subject"],                           //  $subject, 
        $data["tplname"],                           //  $tplname,
    array(
        "name"=>$_POST["client_name"],
        "email"=>$_POST["client_email"],
        "message"=>$_POST["message_email"]
    ));

    $mailer->send();
}


}


?>