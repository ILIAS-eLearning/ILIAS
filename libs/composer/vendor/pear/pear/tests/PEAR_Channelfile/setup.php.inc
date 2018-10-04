<?php
include "PEAR/ChannelFile.php";
set_include_path(getcwd().PATH_SEPARATOR.get_include_path()); // allows chdir
require_once dirname(dirname(__FILE__)) . '/phpt_test.php.inc';
function postprocess($string)
{
    return str_replace("\r", "\n", str_replace("\r\n", "\n", $string));
}
$chf = new PEAR_ChannelFile;
$phpt = new PEAR_PHPTest(true);
?>
