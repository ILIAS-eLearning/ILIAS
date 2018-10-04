<?php
include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'pear_registry_inc.php.inc';
include_once "PEAR/Registry.php";
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'phpt_test.php.inc';
set_include_path(getcwd().PATH_SEPARATOR.get_include_path()); // allows chdir

// ------------------------------------------------------------------------- //

require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'download_test_classes.php.inc';


$statedir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testinstallertemp';
cleanall();

$files1 = array(
    "pkg1-1.php" => array(
        "role" => "php",
        ),
    "pkg1-2.php" => array(
        "role" => "php",
        "baseinstalldir" => "pkg1",
        ),
    );
$files1_test = array(
    "pkg12-1.php" => array(
        "role" => "php",
        ),
    "pkg12-2.php" => array(
        "role" => "php",
        "baseinstalldir" => "pkg1",
        ),
    );
$files1_grnok = array(
    "pkg32-1.php" => array(
        "role" => "php",
        ),
    "pkg32-2.php" => array(
        "role" => "php",
        "baseinstalldir" => "pkg1",
        ),
    );
$files2 = array(
    "pkg2-1.php" => array(
        "role" => "php",
        ),
    "pkg2-2.php" => array(
        "role" => "php",
        "baseinstalldir" => "pkg2",
        ),
    );
$files3 = array(
    "pkg3-1.php" => array(
        "role" => "php",
        ),
    "pkg3-2.php" => array(
        "role" => "php",
        "baseinstalldir" => "pkg3",
        ),
    );
$files3_new = array(
    "pkg3-3.php" => array(
        "role" => "php",
        "baseinstalldir" => "pkg3",
        ),
    "pkg3-4.php" => array(
        "role" => "php",
        ),
    );
mkdir($statedir);
touch($statedir . DIRECTORY_SEPARATOR . '.pearrc');
touch($statedir . DIRECTORY_SEPARATOR . 'pear.ini');
$reg = new PEAR_Registry($statedir);
$phpunit = new PEAR_PHPTest(true);
?>
