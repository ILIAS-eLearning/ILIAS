--TEST--
channel-delete command failure
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$reg = &$config->getRegistry();
$e = $command->run('channel-delete', array(), array());
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'channel-delete: no channel specified'),
), 'no params');
$e = $command->run('channel-delete', array(), array('@#$#$@'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'channel-delete: channel "@#$#$@" does not exist'),
), 'nonexistent');
$e = $command->run('channel-delete', array(), array('pear'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot delete the pear.php.net channel'),
), 'pear');
$e = $command->run('channel-delete', array(), array('pear.php.net'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot delete the pear.php.net channel'),
), 'pear.php.net');
$e = $command->run('channel-delete', array(), array('pecl'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot delete the pecl.php.net channel'),
), 'pear.php.net');
$e = $command->run('channel-delete', array(), array('pecl.php.net'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot delete the pecl.php.net channel'),
), 'pear.php.net');
$e = $command->run('channel-delete', array(), array('__uri'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Cannot delete the __uri pseudo-channel'),
), '__uri');

$ch = new PEAR_ChannelFile;
$ch->setName('fake');
$ch->setSummary('fake');
$ch->setDefaultPEARProtocols();
$reg->addChannel($ch);
$pf = new PEAR_PackageFile_v2_rw;
$pf->setConfig($config);
$pf->setPackage('foo');
$pf->setChannel('fake');
$pf->setReleaseStability('stable');
$pf->setAPIStability('stable');
$pf->setReleaseVersion('1.0.0');
$pf->setAPIVersion('1.0.0');
$pf->setDate('2004-12-14');
$pf->setSummary('foo');
$pf->setDescription('foo');
$pf->setNotes('foo');
$pf->setPackageType('php');
$pf->clearContents();
$pf->setPhpDep('4.0.0', '6.0.0');
$pf->setPearinstallerDep('1.4.0a1');
$pf->setLicense('PHP License');
$pf->addFile('', 'foo.php', array('role' => 'php'));
$pf->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$reg->addPackage2($pf);
$e = $command->run('channel-delete', array(), array('fake'));
$phpunit->assertErrors(array(
    array('package' => 'PEAR_Error', 'message' => 'Channel "fake" has installed packages, cannot delete'),
), 'fake');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
