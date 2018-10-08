--TEST--
PEAR_Installer_Role::initializeConfig()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
PEAR_Installer_Role::registerRoles(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_initializeConfig');
$phpunit->assertNoErrors('setup');

$conf = new PEAR_Config($temp_path . 'pear.ini');
$phpunit->assertNoErrors('after');
$phpunit->assertEquals('hey baby', $conf->get('smonk'), 'get smonk');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
