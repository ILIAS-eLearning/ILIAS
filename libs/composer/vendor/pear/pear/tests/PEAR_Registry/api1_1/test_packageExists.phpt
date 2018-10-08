--TEST--
PEAR_Registry->packageExists() (API v1.1)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
require_once 'PEAR/Registry.php';
$pv = phpversion() . '';
$av = $pv{0} == '4' ? 'apiversion' : 'apiVersion';
if (!in_array($av, get_class_methods('PEAR_Registry'))) {
    echo 'skip';
}
if (PEAR_Registry::apiVersion() != '1.1') {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$ret = $reg->packageExists('gromp');
$phpunit->assertFalse($ret, 'gromp');
$ret = $reg->packageExists('foop');
$phpunit->assertFalse($ret, 'foop not installed');

$pf = new PEAR_PackageFile_v1;
$pf->setConfig($config);
$pf->setSummary('sum');
$pf->setDescription('desc');
$pf->setLicense('PHP License');
$pf->setVersion('1.0.0');
$pf->setState('stable');
$pf->setDate('2004-11-17');
$pf->setNotes('sum');
$pf->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$pf->addPackageDep('Floop', '1.0', 'ge');
$pf->addFile('', 'foo.php', array('role' => 'php'));
$pf->setPackage('foop');
$ret = $reg->addPackage($pf->getPackage(), $pf->getArray());
$phpunit->assertTrue($ret, 'install of valid package');

$ret = $reg->packageExists('gromp');
$phpunit->assertFalse($ret, 'gromp after foop');
$ret = $reg->packageExists('foop');
$phpunit->assertTrue($ret, 'foop installed');
$ret = $reg->packageExists('foo', 'grob');
$phpunit->assertFalse($ret, 'foo/grob');

$pf2 = new PEAR_PackageFile_v2_rw;
$pf2->setConfig($config);
$pf2->setPackageType('extsrc');
$pf2->addBinarypackage('foo_win');
$pf2->setPackage('foo');
$pf2->setChannel('grob');
$pf2->setAPIStability('stable');
$pf2->setReleaseStability('stable');
$pf2->setAPIVersion('1.0.0');
$pf2->setReleaseVersion('1.0.0');
$pf2->setDate('2004-11-12');
$pf2->setDescription('foo source');
$pf2->setSummary('foo');
$pf2->setLicense('PHP License');
$pf2->setLogger($fakelog);
$pf2->clearContents();
$pf2->addFile('', 'foo.grop', array('role' => 'src'));
$pf2->addBinarypackage('foo_linux');
$pf2->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$pf2->setNotes('blah');
$pf2->setPearinstallerDep('1.4.0a1');
$pf2->setPhpDep('4.2.0', '5.0.0');
$pf2->addPackageDepWithChannel('optional', 'frong', 'floop');
$pf2->setProvidesExtension('foo');
$cf = new PEAR_ChannelFile;
$cf->setName('grob');
$cf->setServer('grob');
$cf->setSummary('grob');
$cf->setDefaultPEARProtocols();
$reg = &$config->getRegistry();
$reg->addChannel($cf);
$phpunit->assertNoErrors('channel add');

$ret = $reg->addPackage2($pf2);
$phpunit->assertTrue($ret, 'valid pf2');

$ret = $reg->packageExists('gromp');
$phpunit->assertFalse($ret, 'gromp after grob/foo');
$ret = $reg->packageExists('foop');
$phpunit->assertTrue($ret, 'foop installed after grob/foo');
$ret = $reg->packageExists('foo', 'grob');
$phpunit->assertTrue($ret, 'foo/grob after install');

$ret = $reg->packageExists('foop', 'grob');
$phpunit->assertFalse($ret, 'fakeout 1');
$ret = $reg->packageExists('foo');
$phpunit->assertFalse($ret, 'fakeout 2');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
