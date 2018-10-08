--TEST--
PEAR_Registry->getInstalledGroup (API v1.1)
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
$pf->addFile('', 'foo.php', array('role' => 'php'));
$pf->resetFilelist();
$pf->installedFile('foo.php', array('role' => 'php'));
$pf->setInstalledAs('foo.php', $php_dir . DIRECTORY_SEPARATOR . 'foo.php');
$ret = $reg->addPackage2($pf);
$phpunit->assertErrors(array(
array('package' => 'PEAR_PackageFile_v1', 'message' => 'Missing Package Name'),
), 'pf1 validation errors');
$phpunit->assertFalse($ret, 'install of invalid package');
$pf->setPackage('foop');
$ret = $reg->addPackage2($pf);
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

$pf2 = new PEAR_PackageFile_v2_rw;
$pf2->setConfig($config);
$pf2->setPackageType('php');
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
$pf2->addFile('', 'foor.php', array('role' => 'php'));
$pf2->resetFilelist();
$pf2->installedFile('foor.php', array('attribs' => array('role' => 'php')));
$pf2->setInstalledAs('foor.php', $php_dir . DIRECTORY_SEPARATOR . 'foor.php');
$pf2->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$pf2->setNotes('blah');
$pf2->setPearinstallerDep('1.4.0a1');
$pf2->setPhpDep('4.2.0', '5.0.0');
$pf2->addPackageDepWithChannel('optional', 'frong', 'floop');

$ret = $reg->addPackage2($pf2);
$phpunit->assertErrors(array(
array('package' => 'PEAR_PackageFile_v2', 'message' => 'Unknown channel "grob"'),
array('package' => 'PEAR_Error', 'message' => 'Unknown channel: grob'),
), 'invalid pf2');
$phpunit->assertFalse($ret, 'invalid pf2');

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
$pf2file = $statedir  . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.registry' .
    DIRECTORY_SEPARATOR . '.channel.grob' . DIRECTORY_SEPARATOR . 'foo.reg';
$phpunit->assertFileExists($pf2file, 'reg file of foop.reg');
$contents = unserialize(implode('', file($pf2file)));
$phpunit->showall();
$phpunit->assertTrue(isset($contents['_lastmodified']), '_lastmodified not set pf2');
unset($contents['_lastmodified']);
$phpunit->assertEquals($pf2->getArray(true), $contents, 'pf2 file saved');
$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . '.depdb', 'depdb');
$contents = unserialize(implode('', file($php_dir . DIRECTORY_SEPARATOR . '.depdb')));
$phpunit->assertEquals(array (
  '_version' => '1.0',
  'dependencies' =>
  array (
    'grob' =>
    array (
      'foo' =>
      array (
        0 =>
        array (
          'dep' =>
          array (
            'name' => 'frong',
            'channel' => 'floop',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
    ),
  ),
  'packages' =>
  array (
    'floop' =>
    array (
      'frong' =>
      array (
        0 =>
        array (
          'channel' => 'grob',
          'package' => 'foo',
        ),
      ),
    ),
  ),
), $contents, 'depdb');

$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . '.filemap', 'filemap');
$contents = unserialize(implode('', file($php_dir . DIRECTORY_SEPARATOR . '.filemap')));
ksort($contents['php']);
$phpunit->assertEquals(array (
  'php' =>
  array (
    'foo.php' => 'foop',
    'foor.php' =>
    array (
      0 => 'grob',
      1 => 'foo',
    ),
  ),
), $contents, 'filemap');

$pf2 = new PEAR_PackageFile_v2_rw;
$pf2->addDependencyGroup('flong', 'flong');
$pf2->addGroupPackageDepWithChannel('package', 'flong', 'foop', 'pear.php.net');
$pf2->addGroupPackageDepWithChannel('subpackage', 'flong', 'foo', 'grob');
$ret = $reg->getInstalledGroup($pf2->getDependencyGroup('flong'));
$phpunit->assertEquals(2, count($ret), 'count($ret)');
$phpunit->assertIsa('PEAR_PackageFile_v1', $ret[0], '$ret[0]');
$phpunit->assertIsa('PEAR_PackageFile_v2', $ret[1], '$ret[1]');
$phpunit->assertEquals('foop', $ret[0]->getPackage(), 'package foop');
$phpunit->assertEquals('foo', $ret[1]->getPackage(), 'package foo');


$pf2 = new PEAR_PackageFile_v2_rw;
$pf2->addDependencyGroup('flong', 'flong');
$pf2->addGroupPackageDepWithChannel('package', 'flong', 'foop', 'pear.php.net');
$pf2->addGroupPackageDepWithURI('subpackage', 'flong', 'meme', 'http://example.com');
$ret = $reg->getInstalledGroup($pf2->getDependencyGroup('flong'));
$phpunit->assertEquals(1, count($ret), 'count($ret) 2');
$phpunit->assertIsa('PEAR_PackageFile_v1', $ret[0], '$ret[0]');
$phpunit->assertEquals('foop', $ret[0]->getPackage(), 'package foop');

$pf2 = new PEAR_PackageFile_v2_rw;
$pf2->addDependencyGroup('flong', 'flong');
$pf2->addGroupPackageDepWithChannel('package', 'flong', 'jorp', 'pear.php.net');
$pf2->addGroupPackageDepWithURI('subpackage', 'flong', 'meme', 'http://example.com');
$ret = $reg->getInstalledGroup($pf2->getDependencyGroup('flong'));
$phpunit->assertFalse($ret, 'failure test');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
