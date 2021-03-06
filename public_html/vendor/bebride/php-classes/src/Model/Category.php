<?php

namespace BeBride\Model;

use \BeBride\DB\Sql;
use \BeBride\Model;

class Category extends Model {

    public static function listAll() 
    {
        $sql = new Sql();

        return $sql->select("Select * from tb_categories order by descategory");

    }

    public function save() 
    {
        $sql = new Sql();

        $results = $sql->select("call sp_categories_save(:idcategory, :descategory)", array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory()
       ));

        $this->setValues($results[0]);

        Category::UpdateFile();
    }

    public function get($idcategory) 
    {

        $sql = new Sql();

        $results = $sql->select("select * from tb_categories where idcategory = :idcategory", array(
            ":idcategory"=>$idcategory
        ));

        $this->setValues($results[0]);
    }

    public function delete() 
    {

        $sql = new Sql();

        $sql->query("delete from tb_categories where idcategory = :idcategory", array(
            ":idcategory"=>$this->getidcategory()
        ));

        Category::UpdateFile();
    }

    public static function UpdateFile() 
    {

        $categories = Category::listAll();

        $html = [];

        foreach ($categories as $row) {

            array_push($html,'<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');

        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('',$html));
    }

    public function getProduts($related = true)
    {
        $sql = new Sql();

        if ($related === true) 
        {
            return $sql->select("
            select * from tb_products c where c.idproduct in (
                select a.idproduct from tb_products a
                inner join tb_productscategories b
                on a.idproduct = b.idproduct 
                where b.idcategory = :idcategory)", 
                array(
                    ":idcategory"=>$this->getidcategory()
            ));
        } 
        else
        {
            return $sql->select("
            select * from tb_products c where c.idproduct not in (
                select a.idproduct from tb_products a
                inner join tb_productscategories b
                on a.idproduct = b.idproduct 
                where b.idcategory = :idcategory)", 
                array(
                    ":idcategory"=>$this->getidcategory()
            ));
        }
    }

    public function getProductsPage($page = 1, $itensPerPage = 4)
    {

        $start = ($page - 1) * $itensPerPage; 

        $sql = new Sql();

        $results = $sql->select("
        select sql_calc_found_rows * 
            from tb_products a
            inner join tb_productscategories b on a.idproduct = b.idproduct 
            inner join tb_categories c on c.idcategory = b.idcategory
            where c.idcategory = :idcategory
            limit $start , $itensPerPage;
            ", [
                ':idcategory'=>$this->getidcategory()
            ]);

            $resultsTotal = $sql->select("select found_rows() as nrtotal ");

            return [
                'data'=>Product::checkList($results),
                'total'=>(int) $resultsTotal[0]["nrtotal"],
                'pages'=>ceil( $resultsTotal[0]["nrtotal"] / $itensPerPage)
            ];
    }

    public function addProduct(Product $product)
    {
        $sql = new Sql();


        $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)",
        array(
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ));

    }

    public function removeProduct(Product $product)
    {
        $sql = new Sql();
       
        $sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory and idproduct = :idproduct",
        array(
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ));
    }

    public static function getPage($page = 1, $itensPerPage = 10)
    {

        $start = ($page - 1) * $itensPerPage; 

        $sql = new Sql();

        $results = $sql->select("
            SELECT sql_calc_found_rows * 
                FROM tb_categories 
                order by descategory   
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
            FROM tb_categories 
            WHERE descategory LIKE :search  
            ORDER BY descategory                
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