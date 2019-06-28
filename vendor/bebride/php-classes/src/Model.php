<?php

namespace BeBride;

class Model {

    const NOTIFICATION = "ModelNotification";

    private $values = [];

    public function __call($name, $args)
    {

        $method = substr($name,0, 3);
        $fieldName = substr($name,3, strlen($name));

        switch ($method)
        {
            case 'get':
                return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : null;
            break;

            case 'set':
                $this->values[$fieldName] = $args[0];
            break;
        }
    }

    public function setValues($data = array()) 
    {
        foreach($data as $key => $value)
        {
            $this->{"set".$key}($value);
        }
    }

    public function getValues() 
    {
        return $this->values;
    }

    public static function setNotification($textNotification, $typeNotification = 'normal' ) 
    {
        $notifications = array(
            'normal'=>'',
            'info'=>'',
            'success'=>'',
            'warning'=>'',
            'error'=>''
        );

        switch ($typeNotification) {
            case 'normal':
                 break;
            case 'info':
                 break;
            case 'success':              
                break;
            case 'warning':
                break;
            case 'error':
                break;
            default:
                $notifications = array_merge($notifications, ['error'=>'Notificação de mensagem com tipo invalido.']);
                $notifications = array_merge($notifications, ['warning'=>$typeNotification]);
                $notifications = array_merge($notifications, ['normal'=>$textNotification]);
            break;
        }
        $notifications = array_merge($notifications, [$typeNotification=>$textNotification]);

        $_SESSION[Model::NOTIFICATION] = $notifications; 
    }

    public static function getNotification() 
    {
        $msg = (isset($_SESSION[Model::NOTIFICATION]) && $_SESSION[Model::NOTIFICATION]) ? $_SESSION[Model::NOTIFICATION] : NULL;

        Model::clearNotification();

        return $msg;
    }

    public static function clearNotification() 
    {
        $_SESSION[Model::NOTIFICATION] = NULL;
    }

}

?>