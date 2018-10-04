--TEST--
PEAR_Config->getFTP()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
include_once 'PEAR/Common.php';
if (!class_exists('PEAR_Common')) {
    die('skip must have PEAR_Common');
}
if (!PEAR_Common::isIncludeable('Net/FTP.php') || !PEAR_Common::isIncludeable('PEAR/FTP.php')) {
    die('skip requires PEAR_RemoteInstaller to work');
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
require_once 'PEAR/ChannelFile.php';
define('PEAR_REMOTEINSTALL_OK', 1);
ini_set('include_path', '####');
$config = new PEAR_Config($temp_path . DIRECTORY_SEPARATOR . 'pear.ini', $temp_path . DIRECTORY_SEPARATOR . 'pear.ini', 'ftp://example.com/config.ini');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error','message' => 'Net_FTP must be installed to use remote config'),
), 'no net_ftp');

$phpunit->assertFalse($config->getFTP(), 'getFTP() false');
include_once dirname(__FILE__) . '/test_readFTPConfigFile/FTP.php.inc';
ini_restore('include_path');
$ftp = &Net_FTP::singleton();
$ftp->addRemoteFile('config.ini', dirname(__FILE__) . DIRECTORY_SEPARATOR .
    'test_readFTPConfigFile' . DIRECTORY_SEPARATOR . 'remote.ini');
$config = new PEAR_Config($temp_path .
    DIRECTORY_SEPARATOR . 'nofile', $temp_path .
    DIRECTORY_SEPARATOR . 'nofile', 'ftp://example.com/config.ini');
$phpunit->assertNoErrors('good ftp');
$phpunit->assertIsa('Net_FTP', $config->getFTP(), 'good ftp');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
