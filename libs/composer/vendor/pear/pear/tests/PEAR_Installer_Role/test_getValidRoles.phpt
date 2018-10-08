--TEST--
PEAR_Installer_Role::getValidRoles()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$phpunit->assertEquals(array (
  'cfg',
  'data',
  'doc',
  'man',
  'php',
  'script',
  'test',
), array_slice(PEAR_Installer_Role::getValidRoles('php'), 0, 7), 'php');
$phpunit->assertEquals(array (
  'cfg',
  'data',
  'doc',
  'man',
  'php',
  'script',
  'src',
  'test',
), array_slice(PEAR_Installer_Role::getValidRoles('extsrc'), 0, 8), 'extsrc');
$phpunit->assertEquals(array (
  'cfg',
  'data',
  'doc',
  'ext',
  'man',
  'php',
  'script',
  'test',
), array_slice(PEAR_Installer_Role::getValidRoles('extbin'), 0, 8), 'extbin');
$phpunit->assertEquals(array(), PEAR_Installer_Role::getValidRoles('bundle'), 'bundle');

PEAR_Installer_Role::registerRoles(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'sophisticated');
$phpunit->assertEquals(array (
  0 => 'honorsbaseinstall',
  1 => 'isphp',
  2 => 'noextbin',
  3 => 'noextsrc',
  4 => 'nohonorsbaseinstall',
  5 => 'notphp',
), array_slice(PEAR_Installer_Role::getValidRoles('php'), 0, 6), 'php');
$phpunit->assertEquals(array (
  0 => 'honorsbaseinstall',
  1 => 'isphp',
  2 => 'noextbin',
  3 => 'nohonorsbaseinstall',
  4 => 'nophp',
  5 => 'notphp',
), array_slice(PEAR_Installer_Role::getValidRoles('extsrc'), 0, 6), 'extsrc');
$phpunit->assertEquals(array (
  0 => 'honorsbaseinstall',
  1 => 'isphp',
  2 => 'noextsrc',
  3 => 'nohonorsbaseinstall',
  4 => 'nophp',
  5 => 'notphp',
), array_slice(PEAR_Installer_Role::getValidRoles('extbin'), 0, 6), 'extbin');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
