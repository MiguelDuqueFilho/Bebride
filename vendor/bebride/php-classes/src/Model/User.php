<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;
use \BeBride\Mailer;


class User extends Model {

    const SESSION = "user"; 
    const SECRET_USER = "BeBrideSecret_US";
    const SECRET_IV = '';
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserErrorRegister";
    const SUCCESS = "UserSuccess";

    public static function getFromSession() 
    {

        $user = new User();

        if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]["iduser"] > 0) 
        {

            $user->setValues($_SESSION[User::SESSION]);

        }

        return $user;
    }

    public static function checkLogin($inadmin = true) 
    {

        if (
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
        )
        {
            // Não esta logado
            return false;
        }
        else
        {
            // rota de administrador
            if ($inadmin = true && (bool)$_SESSION[User::SESSION]["inadmin"] === true ) 
            {
               return true;
            }
            else if ($inadmin === false) 
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

    public static function login($login, $password) 
    {
        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));

        if (count($results) === 0) 
        {
            throw new \Exception("Usuário inexistente ou Senha Invaida.");
        }

        $data = $results[0];

        if (password_verify($password,$data["despassword"]) === true) 
        {
            $user = new User();

            $data['desperson'] = utf8_encode($data['desperson']);
            
            $user->setValues($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
        }
        else
        {
            throw new \Exception("Usuário inexistente ou Senha Invaida.");
        }


    }

    public static function verifyLogin($inadmin = true) 
    {
        if  (!User::checkLogin($inadmin))
        {
			if ($inadmin) {
				header("Location: /admin/login");
			} else {
				header("Location: /login");
			}
			exit;
        }
    }

    public static function logout()
    {
        $_SESSION[User::SESSION] = null;
    }


    //teste temporario
    public static function listPerson() 
    {
        $sql = new Sql();
        
        return $sql->select("SELECT * FROM tb_persons ORDER BY desperson");

    }

    public static function listAll() 
    {
        $sql = new Sql();
        
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

    }

    public function save() 
    {

        $sql = new Sql();

        $results = $sql->select("call sp_users_save(:desperson, :deslogin, :despassord, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassord"=>User::getPasswordHash($this->getdespassword()),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
       ));

        $this->setValues($results[0]);
    }

    public function get($iduser) {

        $sql = new Sql();
        
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :IDUSER", array(
            ":IDUSER"=>$iduser
        ));

        $data = $results[0];

        $data['desperson'] = utf8_encode($data['desperson']);

        $this->setValues($data);

    }

    public function update() 
    {

        $sql = new Sql();

        $results = $sql->select("call sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassord, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>utf8_decode($this->getdesperson()),
            ":deslogin"=>$this->getdeslogin(),
            ":despassord"=>User::getPasswordHash($this->getdespassword()),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));

        $this->setValues($results[0]);
    }

    public function delete() {

        $sql = new Sql();

        $sql->select("call sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }

    public static function getForgot($email, $inadmin = true) {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email",array(
            ":email"=>$email
        ));

        if (count($results) === 0) 
        {
            throw new \Exception("Não foi possivel recuparar a senha. 1");
        }
        else
        {
            $data = $results[0];
            $resultsforgot = $sql->select("call sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"] 
            ));

            if (count($resultsforgot) === 0) 
            {
                throw new \Exception("Não foi possivel recuparar a senha 2.");
            }
            else
            {
                $dataRecovery = $resultsforgot[0];

                $openssl = openssl_encrypt(
                    $dataRecovery["idrecovery"], 
                    "AES-128-ECB",
                    User::SECRET_USER,
                    0,
                    User::SECRET_IV
                );
                $code = base64_encode($openssl);
//                $code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, USER::SECRET_USER, $dataRecovery["idrecovery"], MCRYPT_MODE_CBC));

                if ($inadmin === true) 
                {
                    $link = "http://www.bebridecasamentos.com.br/admin/forgot/reset?code=$code";

                }
                else
                {
                    $link = "http://www.bebridecasamentos.com.br/forgot/reset?code=$code";

                }

                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha da BeBride Casamentos","forgot",
                    array(
                        "name"=>$data["desperson"],
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

        $idRecovery = openssl_decrypt(
            $codeCrypt, 
            "AES-128-ECB",
            User::SECRET_USER,
            0,
            User::SECRET_IV
        );

        $sql = new Sql();

        $results = $sql->select("
            select * from tb_userspasswordsrecoveries a
            inner join tb_users b using (iduser) 
            inner join tb_persons c using (idperson)
            where 	a.idrecovery = :idrecovery  
            and     a.dtrecovery is null 
            and		date_add(a.dtregister, interval 1 hour) >= now()
            ",
            Array
            (
                ":idrecovery"=>$idRecovery
            )
        );

        if (count($results) === 0) 
        {    
            throw new \Exception("Não foi possivel recuperar a Senha.", 1);                
        }
        else
        {
            return $results[0];
        };
    } 

    public static function setForgotUsed($idRecovery) 
    {
        $sql = new Sql();

        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = now() WHERE idrecovery = :idrecovery", array(
            ":idrecovery"=>$idRecovery
        ));
    }

    public function setPassword($password) {
        $sql = new Sql();

        $sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
            ":password"=>$password,
            ":iduser"=>$this->getiduser()
        ));

    }

    public static function setError($msg) 
    {
        $_SESSION[User::ERROR] = $msg;
    }

    public static function getError() 
    {
        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

        User::clearError();

        return $msg;
    }

    public static function clearError() 
    {
        $_SESSION[User::ERROR] = NULL;
    }

    public static function setErrorRegister($msg) 
    {
        $_SESSION[User::ERROR_REGISTER] = $msg;
    }

    public static function getErrorRegister() 
    {
        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

        User::clearErrorRegister();

        return $msg;
    }

    public static function clearErrorRegister() 
    {
        $_SESSION[User::ERROR_REGISTER] = NULL;
    }

	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);

	}

    public function checkLoginExist($login) 
    {
        
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_persons WHERE desemail = :deslogin", [
            ':deslogin'=>$login
        ]);

        return (count($results) > 0 );

    }

    public static function setSuccess($msg) 
    {
        $_SESSION[User::SUCCESS] = $msg;
    }

    public static function getSuccess() 
    {
        $msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';

        User::clearSuccess();

        return $msg;
    }

    public static function clearSuccess() 
    {
        $_SESSION[User::SUCCESS] = NULL;
    }

    public function getorders() 
    {
        $sql = new Sql();

        $results = $sql->select("
            SELECT * FROM tb_orders a
                INNER JOIN tb_ordersstatus b USING(idstatus) 
                INNER JOIN tb_carts c USING(idcart) 
                INNER JOIN tb_users d on d.iduser = a.iduser 
                INNER JOIN tb_addresses e USING(idaddress) 
                INNER JOIN tb_persons f on f.idperson = d.idperson 
                WHERE d.iduser  = :iduser ", [
                ':iduser'=>$this->getiduser()
        ]);

        return $results;
    }

    public static function getPage($page = 1, $itensPerPage = 10)
    {

        $start = ($page - 1) * $itensPerPage; 

        $sql = new Sql();

        $results = $sql->select("
            select sql_calc_found_rows * 
                FROM tb_users a 
                INNER JOIN tb_persons b USING(idperson) 
                ORDER BY b.desperson
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

        $results = $sql->select("
            select sql_calc_found_rows * 
                FROM tb_users a 
                INNER JOIN tb_persons b USING(idperson) 
                WHERE b.desperson LIKE :search OR b.desemail LIKE :search OR  a.deslogin LIKE :search 
                ORDER BY b.desperson
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

}
?>