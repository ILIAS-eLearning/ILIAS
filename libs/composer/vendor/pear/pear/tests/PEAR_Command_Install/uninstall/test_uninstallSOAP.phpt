--TEST--
uninstall command - real-world example (uninstall the SOAP package)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$packageDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR;
$phpDir     = $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR;
$docDir     = $temp_path . DIRECTORY_SEPARATOR . 'doc' . DIRECTORY_SEPARATOR;
$dataDir    = $temp_path . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
$testDir    = $temp_path . DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR;

$packages[] = $packageDir . 'SOAP-0.8.1.tgz';
$packages[] = $packageDir . 'Mail_Mime-1.2.1.tgz';
$packages[] = $packageDir . 'HTTP_Request-1.2.4.tgz';
$packages[] = $packageDir . 'Net_URL-1.0.14.tgz';
$packages[] = $packageDir . 'Net_DIME-0.3.tgz';
$packages[] = $packageDir . 'Net_Socket-1.0.5.tgz';

$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setExtensions(array('pcre' => '1.0'));
$command->run('install', array(), $packages);
$phpunit->assertNoErrors('after install');

$fakelog->getLog();
$paramnames = array('Mail_Mime', 'SOAP', 'Net_DIME', 'HTTP_Request', 'Net_URL', 'Net_Socket');
$command->run('uninstall', array(), $paramnames);

$phpunit->assertNoErrors('after uninstall');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
