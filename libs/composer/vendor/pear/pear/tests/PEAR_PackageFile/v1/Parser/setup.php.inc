<?php
require_once 'PEAR/PackageFile/Parser/v1.php';
require_once 'PEAR/Config.php';
set_include_path(getcwd().PATH_SEPARATOR.get_include_path()); // allows chdir

require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'download_test_classes.php.inc';


register_shutdown_function('cleanall');
$statedir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'registry_tester';
cleanall($statedir);
mkdir($statedir);
$config = serialize(
array(
    'php_dir' => $statedir . DIRECTORY_SEPARATOR . 'php',
)
);
$fp = fopen($statedir . DIRECTORY_SEPARATOR . 'pear.ini', 'wb');
fwrite($fp, $config, strlen($config));
fclose($fp);
$config = new PEAR_Config($statedir . DIRECTORY_SEPARATOR . 'pear.ini');
$parser = new PEAR_PackageFile_Parser_v1;
$parser->setConfig($config);
require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'phpt_test.php.inc';
$phpunit = new PEAR_PHPTest(true);
$fakelog = new fake_log;
$parser->setLogger($fakelog);
?>
