<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;

$app = new Slim();

$app->config('debug', true);

require_once("functions.php");
require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-address.php");
require_once("admin-events.php");
require_once("admin-tasks.php");
require_once("admin-depositions.php");
require_once("admin-guests.php");

$app->run();

?>