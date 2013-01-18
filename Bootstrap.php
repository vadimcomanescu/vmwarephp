<?php
error_reporting(E_ALL | E_STRICT);

$root    = realpath(dirname(__FILE__));
$library = "$root/library";
$tests   = "$root/tests";
var_dump($library);

$path = array(
    $library,
    $tests,
    get_include_path(),
);
set_include_path(implode(PATH_SEPARATOR, $path));


require_once 'Vmwarephp/Autoloader.php';
$autoloader = new Autoloader();
$autoloader->register();