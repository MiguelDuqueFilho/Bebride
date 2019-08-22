<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;
use \BeBride\Model\User;

class Cart extends Model {

    const SESSION = "Cart";
    const SESSION_ERROR = "CartError";

    public static function getFromSession() 
    {
        $cart = new Cart();

        if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {

            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

        } 
        else 
        {
            $cart->getFromSessionId();

            if (!(int)$cart->getidcart() > 0) {
                $data = [
                    'dessessionid'=>session_id()
                ];

                if (User::checkLogin(false))
                {
                    $user = User::getFromSession();

                    $data['iduser'] = $user->getiduser(); 

                }
                $cart->setValues($data);

                $cart->save();

                $cart->setToSession();
            }
        }


        return $cart;
    }

    public function setToSession() 
    {
        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    public function getFromSessionId() 
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
            ':dessessionid'=>session_id()
        ]);

        if (count($results) > 0)
        {
            $this->setValues($results[0]);
        }
    }

    public function get(int $idcart)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
            ':idcart'=>$idcart
        ]);

        if (count($results) > 0)
        {
            $this->setValues($results[0]);
        }
    }

    public function save() 
    {
        $sql = new Sql();

        $results = $sql->select("call sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", 
        array(
            ":idcart"=>$this->getidcart(),
            ":dessessionid"=>$this->getdessessionid(),
            ":iduser"=>$this->getiduser(),
            ":deszipcode"=>$this->getdeszipcode(),
            ":vlfreight"=>$this->getvlfreight(),
            ":nrdays"=>$this->getnrdays()
       ));

        $this->setValues($results[0]);
    }


    public function addProduct(Product $product)
    {

        $sql = new Sql();

        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", [
            ':idcart'=>$this->getidcart(),
            ':idproduct'=>$product->getidproduct()
        ]);

        $this->getCalculateTotal();
    } 

    public function removeProduct(Product $product, $all = false)
    {

		$sql = new Sql();

		if ($all) {

			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]);

		} else {

			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]);

        }
        
        $this->getCalculateTotal();
    } 

    public function getProducts() 
    {

        $sql = new Sql();

        $rows = $sql->select('
            SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl,
                COUNT(*) AS nrqtd,
                SUM(b.vlprice) AS vltotal
                FROM tb_cartsproducts a
                INNER JOIN
                tb_products b ON a.idproduct = b.idproduct
                WHERE
                a.idcart = :idcart AND a.dtremoved IS NULL
                GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
                ORDER BY b.desproduct;', [
                    ':idcart'=>$this->getidcart()
                ]); 
    
        return Product::checkList($rows);
    }                


    public function getProductsTotals() 
    {
        $sql = new Sql();

        $results = $sql->select("
            SELECT sum(vlprice) as vlprice , sum(vlwidth) as vlwidth , sum(vlheight) as vlheight, sum(vllength) as vllength, sum(vlweight) as vlweight, count(*) as nrqtd
                FROM tb_products a 
                inner join tb_cartsproducts b ON a.idproduct = b.idproduct 
                WHERE b.idcart = :idcart and b.dtremoved is null; ", [
                    ':idcart'=>$this->getidcart()
                ]
        );

        if (count($results) > 0) 
        {
            return $results[0];
        }
        else
        {
            return [];
        }
    }

    public function setFreight($nrZipCode) 
    {

        $nrZipCode = str_replace('-', '', $nrZipCode);

        $totals = $this->getProductsTotals();

        if ($totals['nrqtd'] > 0 ) 
        {
            if ($totals['vlheight'] < 2) $totals['vlheight'] = 2;
            if ($totals['vllength'] < 16) $totals['vllength'] = 16;
            if ($totals['vlprice'] < 19.5) $totals['vlprice'] = 19.5;
            if ($totals['vlprice'] > 3000) $totals['vlprice'] = 3000;

            $qs = http_build_query([
                'nCdEmpresa'=>(string)'',
                'sDsSenha'=>(string)'',
                'nCdServico'=>(string)'41106',
                'sCepOrigem'=>(string)'11665000',
                'sCepDestino'=>(string)$nrZipCode,
                'nVlPeso'=>(string)$totals['vlweight'],
                'nCdFormato'=>(string)'1',
                'nVlComprimento'=>(string)$totals['vllength'],
                'nVlAltura'=>(string)$totals['vlheight'],
                'nVlLargura'=>(string)$totals['vlwidth'],
                'nVlDiametro'=>(string)'0',
                'sCdMaoPropria'=>(string)'N',
                'nVlValorDeclarado'=>(string)$totals['vlprice'],
                'sCdAvisoRecebimento'=>(string)'N'
            ]);

            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

            $results = $xml->Servicos->cServico;

            if ($results->MsgErro !== "") 
            {
                Cart::setMsgError($results->msgErro);
            }
            else
            {
                Cart::clearMsgError();
            }

            $this->setnrdays($results->PrazoEntrega);            
            $this->setvlfreight($this->formatValueToDecimal($results->Valor));            
            $this->setdeszipcode($nrZipCode);

            $this->save();

            return $results;
        }
        else
        {

        }

    }

    public static function formatValueToDecimal($value):float
    {
        $value = str_replace('.', '', $value);
        return str_replace(',', '.', $value);
    }

    public static function setMsgError($msg) {
        $_SESSION[Cart::SESSION_ERROR] = $msg;
    }

    public static function getMsgError() {
  
        $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

        Cart::clearMsgError();

        return $msg;
    }

    public static function clearMsgError() {
        $_SESSION[Cart::SESSION_ERROR] = NULL;
    }

    public function updateFreight() 
    {
        if ($this->getdeszipcode() != '')
        {
            $this->setFreight($this->getdeszipcode());
        }
    }

    public function getValues() 
    {

        $this->getCalculateTotal();

        return parent::getValues();

    }

    public function getCalculateTotal() 
    {

        $this->updateFreight();

        $totals = $this->getProductsTotals();

        $this->setvlsubtotal($totals['vlprice']);
        $this->setvltotal($totals['vlprice'] + $this->getvlfreight());

    }

}


?>