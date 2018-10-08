--TEST--
PEAR_Dependency2->checkPearinstallerDependency() valid
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
    'package' => 'mine'), PEAR_VALIDATE_INSTALLING);
$phpunit->assertNoErrors('create 1');

$dep->setPEARversion('4.3.11');

$result = $dep->validatePearinstallerDependency(
    array(
        'min' => '4.0.0',
        'max' => '5.0.0',
        'exclude' => array('4.3.9','4.3.10')
    ));
$phpunit->assertNoErrors('exclude 3');
$phpunit->assertTrue($result, 'exclude 3');

$dep = new test_PEAR_Dependency2($config, array(), array('channel' => 'pear.php.net',
    'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 2');

$dep->setPEARversion('4.3.11');

$result = $dep->validatePearinstallerDependency(
    array(
        'min' => '4.0.0',
        'max' => '5.0.0',
        'exclude' => array('4.3.9','4.3.10')
    ));
$phpunit->assertNoErrors('exclude 3');
$phpunit->assertTrue($result, 'exclude 3');

$result = $dep->validatePearinstallerDependency(
    array(
        'min' => '4.3.11',
        'max' => '5.0.0',
        'exclude' => array('4.3.9','4.3.10')
    ));
$phpunit->assertNoErrors('min bounds');
$phpunit->assertTrue($result, 'min bounds');

$result = $dep->validatePearinstallerDependency(
    array(
        'min' => '4.3.8',
        'max' => '4.3.11',
        'exclude' => array('4.3.9','4.3.10')
    ));
$phpunit->assertNoErrors('max bounds');
$phpunit->assertTrue($result, 'max bounds');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
