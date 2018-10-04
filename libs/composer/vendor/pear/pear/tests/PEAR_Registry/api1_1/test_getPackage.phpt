--TEST--
PEAR_Registry->getPackage() pf v1 (API v1.1)
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
$phpunit->assertNoErrors('install of valid package');
$phpunit->assertFileExists($statedir  . DIRECTORY_SEPARATOR . 'php' .
    DIRECTORY_SEPARATOR . '.registry' . DIRECTORY_SEPARATOR . 'foop.reg', 'reg file of foop.reg');
$contents = unserialize(implode('', file($statedir  . DIRECTORY_SEPARATOR . 'php' .
    DIRECTORY_SEPARATOR . '.registry' . DIRECTORY_SEPARATOR . 'foop.reg')));
$phpunit->showall();
$phpunit->assertTrue(isset($contents['_lastmodified']), '_lastmodified not set pf1');
unset($contents['_lastmodified']);
$phpunit->assertEquals($pf->getArray(), $contents, 'pf1 file saved');

$newpf = &$reg->getPackage('foop');
$phpunit->assertIsa('PEAR_PackageFile_v1', $newpf, 'newpf');
$phpunit->assertEquals('sum', $newpf->getSummary(), 'summary');
$phpunit->assertEquals('desc', $newpf->getDescription(), 'description');
$phpunit->assertEquals('PHP License', $newpf->getLicense(), 'license');
$phpunit->assertEquals('1.0.0', $newpf->getVersion(), 'version');
$phpunit->assertEquals('stable', $newpf->getState(), 'state');
$phpunit->assertEquals('2004-11-17', $newpf->getDate(), 'date');
$phpunit->assertEquals('sum', $newpf->getNotes(), 'notes');
$phpunit->assertEquals(array(array('handle' => 'cellog', 'role' => 'lead', 'email' => 'cellog@php.net', 'name' => 'Greg Beaver')), $newpf->getMaintainers(), 'maintainers');
$phpunit->assertEquals(array('foo.php' => array('role' => 'php')), $newpf->getFilelist(), 'filelist');
$phpunit->assertEquals('foop', $newpf->getPackage(), 'package');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
