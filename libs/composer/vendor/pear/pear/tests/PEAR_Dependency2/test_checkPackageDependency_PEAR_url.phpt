--TEST--
PEAR_Dependency2->checkPackageDependency() url-style dependency
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$requiredpackage = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'foo-1.0.tgz';
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/foo-1.0.tgz', $requiredpackage);
$dp = newFakeDownloaderPackage(array());
$result = $dp->initialize('http://www.example.com/foo-1.0.tgz');
$phpunit->assertNoErrors('after create 1');
$fakelog->getLog();
$fakelog->getDownload();

$dep = new test_PEAR_Dependency2($config, array(), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 1');

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'uri' => 'http://www.example.com/foo-1.0',
    ), true, array(&$dp));
$phpunit->assertNoErrors('1');
$phpunit->assertEquals(array(), $fakelog->getLog(), '1');
$phpunit->assertTrue($result, 'required');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
