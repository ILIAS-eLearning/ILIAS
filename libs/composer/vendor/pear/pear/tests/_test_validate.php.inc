<?php
require_once 'PEAR/Validate.php';
class test_Validate extends PEAR_Validate
{
    var $packageregex = '[a-z][a-z0-9]*(?:\.[a-z0-9]+)*';
}
?>
