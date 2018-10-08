--TEST--
PEAR_Dependency2->checkPackageDependency() min (downloaded) failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$dep = new test_PEAR_Dependency2($config, array(), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 1');

require_once 'PEAR/Downloader/Package.php';
require_once 'PEAR/Downloader.php';
$down = new PEAR_Downloader($fakelog, array(), $config);
$dp = new PEAR_Downloader_Package($down);
$dp->initialize(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR .
    'package.xml');
$params = array(&$dp);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.1',
        'max' => '1.9',
    ), true, $params);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires package "pear/foo" (version >= 1.1, version <= 1.9), downloaded version is 1.0')
), 'min');
$phpunit->assertIsa('PEAR_Error', $result, 'min');

// conflicts
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.1',
        'max' => '1.9',
        'conflicts' => true
    ), true, $params);
$phpunit->assertNoErrors('versioned conflicts 1');
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.0',
        'max' => '1.1',
        'conflicts' => true
    ), true, $params);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine conflicts with package "pear/foo" (version >= 1.0, version <= 1.1), downloaded version is 1.0')
), 'versioned conflicts 2');

// optional
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '1.1',
        'max' => '1.9',
    ), false, $params);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error',
          'message' => 'pear/mine requires package "pear/foo" (version >= 1.1, version <= 1.9), downloaded version is 1.0')
), 'min optional');
$phpunit->assertIsa('PEAR_Error', $result, 'min optional');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
