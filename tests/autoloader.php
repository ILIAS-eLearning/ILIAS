<?php

require_once("tests/traits.php");

spl_autoload_register(function($className) {
    $parts = explode("\\", $className);
    // remove Lechimp\\Formlets
    array_shift($parts);
    array_shift($parts);

    $path = "src/".implode("/", $parts).".php";
    if (file_exists($path)) {
        require_once($path);
    }
});

?>
