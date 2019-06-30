<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;
use \BeBride\Mailer;


class User extends Model {

    const SESSION = "user"; 
    const SECRET_USER = "BeBrideSecret_US";
    const SECRET_IV = '';

    // public static function getFromSession() 
    // {

    //     $user = new User();

    //     if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]["iduser"] > 0) 
    //     {

    //         $user->setValues($_SESSION[User::SESSION]);

    //     }

    //     return $user;
    // }

    

    // public static function login($login, $password) 
    // {
    //     $sql = new Sql();
        
    //     $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE deslogin = :LOGIN", array(
    //         ":LOGIN"=>$login
    //     ));

    //     if (count($results) === 0) 
    //     {
    //         throw new \Exception("Usuário inexistente ou Senha Invaida.");
    //     }

    //     $data = $results[0];

    //     if (password_verify($password,$data["despassword"]) === true) 
    //     {
    //         $user = new User();

    //         $data['desperson'] = utf8_encode($data['desperson']);
            
    //         $user->setValues($data);

    //         $_SESSION[User::SESSION] = $user->getValues();

    //         return $user;
    //     }
    //     else
    //     {
    //         throw new \Exception("Usuário inexistente ou Senha Invaida.");
    //     }


    // }

public static function checkLogin($user_type_id = 1) //não revisado
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
            // rota de administrador
            if ($user_type_id = 1 && (bool)$_SESSION[User::SESSION]["user_type_id"] === true ) 
            {
               return true;
            }
            else if ($user_type_id === false) 
            {
                // Ele esta logado , mas não estamos exigindo que seja uma rota de administração
                return true;
            } 
            else 
            {
                // Se saiu deste padrão e por que não esta logado 
                   return false;
            }
        }

    }

    public static function verifyLogin($user_type_id = 1) 
    {
        if  (!User::checkLogin($user_type_id))
        {
            // if ($user_type_id === 1) // usuário administrador
            // {
			// 	header("Location: /admin/login");
			// } else {
			// 	header("Location: /login");
            // }
            header("Location: /login");
			exit;
        }
    }

    // public static function logout()
    // {
    //     $_SESSION[User::SESSION] = null;
    // }


    public static function listAll() 
    {
        $sql = new Sql();
        
        return $sql->select("SELECT * , CONCAT_WS(' ',b.person_firstname,b.person_lastname) AS person_name  
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
        
        $results = $sql->select("SELECT * , CONCAT_WS(' ',b.person_firstname,b.person_lastname) AS person_name  
            FROM tb_users a 
            INNER JOIN tb_persons b on b.person_id = a.person_id
            INNER JOIN tb_userstype c on c.user_type_id = a.user_type_id
            WHERE a.user_id = :user_id", array(
            ":user_id"=>$user_id
        ));

        $data = $results[0];

        $this->setValues($data);

    }

//     public function update() 
//     {

//         $sql = new Sql();

//         $results = $sql->select("call sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassord, :desemail, :nrphone, :inadmin)", array(
//             ":iduser"=>$this->getiduser(),
//             ":desperson"=>utf8_decode($this->getdesperson()),
//             ":deslogin"=>$this->getdeslogin(),
//             ":despassord"=>User::getPasswordHash($this->getdespassword()),
//             ":desemail"=>$this->getdesemail(),
//             ":nrphone"=>$this->getnrphone(),
//             ":inadmin"=>$this->getinadmin()
//         ));

//         $this->setValues($results[0]);
//     }


//     public static function getForgot($email, $inadmin = true) {

//         $sql = new Sql();

//         $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email",array(
//             ":email"=>$email
//         ));

//         if (count($results) === 0) 
//         {
//             throw new \Exception("Não foi possivel recuparar a senha. 1");
//         }
//         else
//         {
//             $data = $results[0];
//             $resultsforgot = $sql->select("call sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
//                 ":iduser"=>$data["iduser"],
//                 ":desip"=>$_SERVER["REMOTE_ADDR"] 
//             ));

//             if (count($resultsforgot) === 0) 
//             {
//                 throw new \Exception("Não foi possivel recuparar a senha 2.");
//             }
//             else
//             {
//                 $dataRecovery = $resultsforgot[0];

//                 $openssl = openssl_encrypt(
//                     $dataRecovery["idrecovery"], 
//                     "AES-128-ECB",
//                     User::SECRET_USER,
//                     0,
//                     User::SECRET_IV
//                 );
//                 $code = base64_encode($openssl);
// //                $code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, USER::SECRET_USER, $dataRecovery["idrecovery"], MCRYPT_MODE_CBC));

//                 if ($inadmin === true) 
//                 {
//                     $link = "http://www.bebridecasamentos.com.br/admin/forgot/reset?code=$code";

//                 }
//                 else
//                 {
//                     $link = "http://www.bebridecasamentos.com.br/forgot/reset?code=$code";

//                 }

//                 $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha da BeBride Casamentos","forgot",
//                     array(
//                         "name"=>$data["desperson"],
//                         "link"=>$link
//                     )
//                 );

//                 $mailer->send();

//                 return $data;
                
//             };
//         }

//     }

//     public static function validForgotDecrypt($code) {

//         $codeCrypt = base64_decode($code);

//         $idRecovery = openssl_decrypt(
//             $codeCrypt, 
//             "AES-128-ECB",
//             User::SECRET_USER,
//             0,
//             User::SECRET_IV
//         );

//         $sql = new Sql();

//         $results = $sql->select("
//             select * from tb_userspasswordsrecoveries a
//             inner join tb_users b using (iduser) 
//             inner join tb_persons c using (idperson)
//             where 	a.idrecovery = :idrecovery  
//             and     a.dtrecovery is null 
//             and		date_add(a.dtregister, interval 1 hour) >= now()
//             ",
//             Array
//             (
//                 ":idrecovery"=>$idRecovery
//             )
//         );

//         if (count($results) === 0) 
//         {    
//             throw new \Exception("Não foi possivel recuperar a Senha.", 1);                
//         }
//         else
//         {
//             return $results[0];
//         };
//     } 

//     public static function setForgotUsed($idRecovery) 
//     {
//         $sql = new Sql();

//         $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = now() WHERE idrecovery = :idrecovery", array(
//             ":idrecovery"=>$idRecovery
//         ));
//     }

//     public function setPassword($password) {
//         $sql = new Sql();

//         $sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
//             ":password"=>$password,
//             ":iduser"=>$this->getiduser()
//         ));

//     }

	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
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

        $results = $sql->select("SELECT sql_calc_found_rows * , CONCAT_WS(' ',b.person_firstname,b.person_lastname) AS person_name  
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

        $results = $sql->select("SELECT sql_calc_found_rows * , CONCAT_WS(' ',b.person_firstname,b.person_lastname) AS person_name  
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