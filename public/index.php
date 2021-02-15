<?php

$data = json_decode(file_get_contents("php://input"));

if (is_null($data)) {
	exit("If you have access you know what can you do :)");
}
require '..\vendor\autoload.php';
$config = require '..\config\config.php';

use Pardayev\botHelper\Main;

new Main($config, $data);