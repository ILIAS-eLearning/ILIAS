<?php
require_once 'PEAR/RunTest.php';
set_include_path(getcwd().PATH_SEPARATOR.get_include_path()); // allows chdir
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'phpt_test.php.inc';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'download_test_classes.php.inc';


$statedir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testinstallertemp';
cleanall();
mkdir($statedir);

$phpunit = new PEAR_PHPTest(true);
$tester = new PEAR_RunTest($fakelog);
