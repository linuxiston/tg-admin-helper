<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$data = json_decode(file_get_contents("php://input"));

if (is_null($data)) {
	exit();
}
require '..\vendor\autoload.php';
$config = require '..\config\config.php';

use Pardayev\botHelper\Main;

new Main($config, $data);