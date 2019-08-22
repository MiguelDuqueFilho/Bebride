<?php

namespace BeBride;


class PageAdmin extends Page {

    public function __construct($opts = array(), $tpl_dir = "views/admin/") 
    {

        parent::__construct($opts, $tpl_dir);
    
    }

    // public static function setMenuItem($item) 
    // {
    //     $menu = [];
    //     $menu[$item] = true;
    //     return $menu;
    // }
    
}

?>