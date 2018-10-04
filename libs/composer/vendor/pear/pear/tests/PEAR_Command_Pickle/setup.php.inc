<?php
set_include_path(getcwd().PATH_SEPARATOR.get_include_path()); // allows chdir

$server = 'pear.Chiara';
//$server = 'test.pear.php.net';
$temp_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testinstallertemp';

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'download_test_classes.php.inc';

cleanall($temp_path);
$statedir = $temp_path;
if (!is_dir($temp_path)) {
    mkdir($temp_path);
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'php')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'php');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'data')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'data');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'doc')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'doc');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'test')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'test');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'ext')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'ext');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'script')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'script');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'tmp')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'tmp');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'bin')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'bin');
}
if (!is_dir($temp_path . DIRECTORY_SEPARATOR . 'cache')) {
    mkdir($temp_path . DIRECTORY_SEPARATOR . 'cache');
}
// make the fake configuration - we'll use one of these and it should work
$config = serialize(array('master_server' => $server,
    'preferred_state' => 'stable',
    'cache_dir' => $temp_path . DIRECTORY_SEPARATOR . 'cache',
    'php_dir' => $temp_path . DIRECTORY_SEPARATOR . 'php',
    'ext_dir' => $temp_path . DIRECTORY_SEPARATOR . 'ext',
    'data_dir' => $temp_path . DIRECTORY_SEPARATOR . 'data',
    'www_dir' => $temp_path . DIRECTORY_SEPARATOR . 'www',
    'doc_dir' => $temp_path . DIRECTORY_SEPARATOR . 'doc',
    'test_dir' => $temp_path . DIRECTORY_SEPARATOR . 'test',
    'bin_dir' => $temp_path . DIRECTORY_SEPARATOR . 'bin',));
touch($temp_path . DIRECTORY_SEPARATOR . 'pear.conf');
$fp = fopen($temp_path . DIRECTORY_SEPARATOR . 'pear.conf', 'w');
fwrite($fp, $config);
fclose($fp);
touch($temp_path . DIRECTORY_SEPARATOR . 'pear.ini');
$fp = fopen($temp_path . DIRECTORY_SEPARATOR . 'pear.ini', 'w');
fwrite($fp, $config);
fclose($fp);
touch($temp_path . DIRECTORY_SEPARATOR . 'pear.conf');
$fp = fopen($temp_path . DIRECTORY_SEPARATOR . 'pear.conf', 'w');
fwrite($fp, $config);
fclose($fp);

putenv('PHP_PEAR_SYSCONF_DIR='.$temp_path);
$home = getenv('HOME');
if (!empty($home)) {
    // for PEAR_Config initialization
    putenv('HOME="'.$temp_path);
}
require_once "PEAR/ChannelFile.php";
require_once dirname(dirname(__FILE__)) . '/download_test_classes.php.inc';
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'phpt_test.php.inc';
function copyItem($name, $loc = false)
{
    if (!$loc) {
        $loc = $GLOBALS['temp_path'];
    }
    if (file_exists($name)) {
        $dp = opendir($name);
        while ($ent = readdir($dp)) {
            if (in_array($ent, array('.', '..'))) {
                continue;
            }
            if (is_dir($name . DIRECTORY_SEPARATOR . $ent)) {
                if (!is_dir($loc . DIRECTORY_SEPARATOR . $ent)) {
                    mkdir($loc . DIRECTORY_SEPARATOR . $ent);
                }
                copyItem($name . DIRECTORY_SEPARATOR . $ent, $loc . DIRECTORY_SEPARATOR . $ent);
            } else {
                copy($name . DIRECTORY_SEPARATOR . $ent,
                     $loc . DIRECTORY_SEPARATOR . $ent);
            }
        }
    }
    closedir($dp);
}

$phpunit = new PEAR_PHPTest(true);
$fakelog = new fake_log;
$config = new test_PEAR_Config($temp_path . '/pear.ini', $temp_path . '/pear.conf');
require_once 'PEAR/Command/Pickle.php';
class test_PEAR_Command_Pickle extends PEAR_Command_Pickle
{
    function &getPackageFile($c, $d = false)
    {
        $a = new test_PEAR_PackageFile($c, $d);
        $a->setLogger($GLOBALS['fakelog']);
        return $a;
    }

    function &getInstaller()
    {
        $a = new test_PEAR_Installer($this->ui, array(), $this->config);
        return $a;
    }

    function &getPackager()
    {
        require_once 'PEAR/Packager.php';
        $a = new PEAR_Packager;
        $a->ui = &$GLOBALS['fakelog'];
        return  $a;
    }
}
$command = new test_PEAR_Command_Pickle($fakelog, $config);
?>
