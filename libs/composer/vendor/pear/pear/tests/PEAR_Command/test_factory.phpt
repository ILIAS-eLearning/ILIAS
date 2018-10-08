--TEST--
PEAR_Command::factory()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
PEAR_Command::registerCommands(false, dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fakecommands');
$err = &PEAR_Command::factory('smogin', $config);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'unknown command `smogin\''),
), 'smogin');
$val = &PEAR_Command::factory('sm', $config);
$phpunit->assertIsa('PEAR_Command_Foo', $val, 'sm');
$val = &PEAR_Command::factory('smong', $config);
$phpunit->assertIsa('PEAR_Command_Foo', $val, 'smong');
$val = &PEAR_Command::factory('ye', $config);
$phpunit->assertIsa('PEAR_Command_Foo', $val, 'ye');
$val = &PEAR_Command::factory('yertl', $config);
$phpunit->assertIsa('PEAR_Command_Foo', $val, 'yertl');
$val = &PEAR_Command::factory('li', $config);
$phpunit->assertIsa('PEAR_Command_Grunk', $val, 'li');
$val = &PEAR_Command::factory('login', $config);
$phpunit->assertIsa('PEAR_Command_Grunk', $val, 'login');
$val = &PEAR_Command::factory('lo', $config);
$phpunit->assertIsa('PEAR_Command_Grunk', $val, 'lo');
$val = &PEAR_Command::factory('logout', $config);
$phpunit->assertIsa('PEAR_Command_Grunk', $val, 'logout');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
