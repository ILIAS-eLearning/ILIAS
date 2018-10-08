--TEST--
PEAR_Installer_Role::factory()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pf2 = new PEAR_PackageFile_v2_rw;
$pf2->setPackageType('php');
$phpunit->assertFalse(PEAR_Installer_Role::factory($pf2, 'src', $config), 'src php');
$phpunit->assertFalse(PEAR_Installer_Role::factory($pf2, 'ext', $config), 'ext php');
$phpunit->assertIsa('PEAR_Installer_Role_Php',
    PEAR_Installer_Role::factory($pf2, 'php', $config), 'php php');
$phpunit->assertIsa('PEAR_Installer_Role_Doc',
    PEAR_Installer_Role::factory($pf2, 'doc', $config), 'doc php');
$phpunit->assertIsa('PEAR_Installer_Role_Data',
    PEAR_Installer_Role::factory($pf2, 'data', $config), 'data php');
$phpunit->assertIsa('PEAR_Installer_Role_Script',
    PEAR_Installer_Role::factory($pf2, 'script', $config), 'script php');
$phpunit->assertIsa('PEAR_Installer_Role_Test',
    PEAR_Installer_Role::factory($pf2, 'test', $config), 'test php');
$pf2->setPackageType('extsrc');
$phpunit->assertEquals(false, PEAR_Installer_Role::factory($pf2, 'ext', $config), 'ext extsrc');
$phpunit->assertIsa('PEAR_Installer_Role_Php',
    PEAR_Installer_Role::factory($pf2, 'php', $config), 'php extsrc');
$phpunit->assertIsa('PEAR_Installer_Role_Src',
    PEAR_Installer_Role::factory($pf2, 'src', $config), 'src extsrc');
$phpunit->assertIsa('PEAR_Installer_Role_Doc',
    PEAR_Installer_Role::factory($pf2, 'doc', $config), 'doc extsrc');
$phpunit->assertIsa('PEAR_Installer_Role_Data',
    PEAR_Installer_Role::factory($pf2, 'data', $config), 'data extsrc');
$phpunit->assertIsa('PEAR_Installer_Role_Script',
    PEAR_Installer_Role::factory($pf2, 'script', $config), 'script extsrc');
$phpunit->assertIsa('PEAR_Installer_Role_Test',
    PEAR_Installer_Role::factory($pf2, 'test', $config), 'test extsrc');
$pf2->setPackageType('extbin');
$phpunit->assertEquals(false, PEAR_Installer_Role::factory($pf2, 'src', $config), 'src extbin');
$phpunit->assertIsa('PEAR_Installer_Role_Php',
    PEAR_Installer_Role::factory($pf2, 'php', $config), 'php extbin');
$phpunit->assertIsa('PEAR_Installer_Role_Ext',
    PEAR_Installer_Role::factory($pf2, 'ext', $config), 'ext extbin');
$phpunit->assertIsa('PEAR_Installer_Role_Doc',
    PEAR_Installer_Role::factory($pf2, 'doc', $config), 'doc extbin');
$phpunit->assertIsa('PEAR_Installer_Role_Data',
    PEAR_Installer_Role::factory($pf2, 'data', $config), 'data extbin');
$phpunit->assertIsa('PEAR_Installer_Role_Script',
    PEAR_Installer_Role::factory($pf2, 'script', $config), 'script extbin');
$phpunit->assertIsa('PEAR_Installer_Role_Test',
    PEAR_Installer_Role::factory($pf2, 'test', $config), 'test extbin');
$pf2->setPackageType('bundle');
$phpunit->assertEquals(false, PEAR_Installer_Role::factory($pf2, 'php', $config), 'php bundle');
$phpunit->assertEquals(false, PEAR_Installer_Role::factory($pf2, 'data', $config), 'data bundle');
$phpunit->assertEquals(false, PEAR_Installer_Role::factory($pf2, 'doc', $config), 'doc bundle');
$phpunit->assertEquals(false, PEAR_Installer_Role::factory($pf2, 'script', $config), 'script bundle');
$phpunit->assertEquals(false, PEAR_Installer_Role::factory($pf2, 'test', $config), 'test bundle');
$phpunit->assertEquals(false, PEAR_Installer_Role::factory($pf2, 'ext', $config), 'ext bundle');
$phpunit->assertEquals(false, PEAR_Installer_Role::factory($pf2, 'src', $config), 'src bundle');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
