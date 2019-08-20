<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class Product extends Model {

    public static function listAll() 
    {
        $sql = new Sql();

        return $sql->select("Select * from tb_products order by desproduct");

    }

    public function checkList($list)
    {
        foreach ($list as &$row) {
            $p = new Product();
            $p->setValues($row);
            $row = $p->getValues();
        }
        return $list;
    }

    public function save() 
    {
		$sql = new Sql();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		));

		$this->setValues($results[0]);

    }

    public function get($idproduct) 
    {

        $sql = new Sql();

        $results = $sql->select("select * from tb_products where idproduct = :idproduct", array(
            ":idproduct"=>$idproduct
        ));

        $this->setValues($results[0]);
    }

    public function delete() 
    {

        $sql = new Sql();

        $sql->query("delete from tb_products where idproduct = :idproduct", array(
            ":idproduct"=>$this->getidproduct()
        ));

    }

    public function checkPhoto() 
    {
        $dist = 
        $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
        "res" . DIRECTORY_SEPARATOR . 
        "site" . DIRECTORY_SEPARATOR . 
        "img" . DIRECTORY_SEPARATOR . 
        "products" . DIRECTORY_SEPARATOR . 
        $this->getidproduct() . ".jpg";

    
        if (file_exists($dist)) 
        {
            $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg" ;
        }
        else
        {
            $url = "/res/site/img/product.jpg" ;
        }

        return $this->setdesphoto($url);
    }

    public function getValues()
    {
        $this->checkPhoto();

        $values = parent::getValues();

        return $values;
    }

    public function setPhoto($file)
    {

        $extention = explode('.',$file['name']);
        $extention = end($extention);


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
                "res" . DIRECTORY_SEPARATOR . 
                "site" . DIRECTORY_SEPARATOR . 
                "img" . DIRECTORY_SEPARATOR . 
                "products" . DIRECTORY_SEPARATOR . 
                $this->getidproduct() . ".jpg";


        imagejpeg($image, $dist);

        imagedestroy($image);

        $this->checkPhoto();
    }

    public function getFromUrl($desurl) 
    {
        $sql = new Sql();

        $rows = $sql->select("select * from tb_products where desurl = :desurl limit 1", array (
            ':desurl'=>$desurl
        ));

        $this->setValues($rows[0]);

    }

    public function getCategories() 
    {

        $sql = new Sql();

        return $sql->select("
        select * from tb_categories a
            inner join tb_productscategories b on b.idcategory = a.idcategory
            where b.idproduct = :idproduct
        ", array (
            ':idproduct'=>$this->getidproduct()
        ));

    }

    public static function getPage($page = 1, $itensPerPage = 10)
    {

        $start = ($page - 1) * $itensPerPage; 

        $sql = new Sql();

        $results = $sql->select("
            SELECT sql_calc_found_rows * 
                FROM tb_products 
                order by desproduct
                LIMIT $start , $itensPerPage;
                ");

            $resultsTotal = $sql->select("select found_rows() as nrtotal ");

            return [
                'data'=>$results,
                'total'=>(int) $resultsTotal[0]["nrtotal"],
                'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
            ];
    }

    public static function getPageSearch($search, $page = 1, $itensPerPage = 9)
    {

        $start = ($page - 1) * $itensPerPage; 

        $sql = new Sql();

        $results = $sql->select("
            SELECT sql_calc_found_rows * 
                FROM tb_products 
                WHERE desproduct LIKE :search  
                ORDER BY desproduct
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