--TEST--
PEAR_Registry->deletePackage (API v1.1) (packagefile_v2)
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
$pf2file = $statedir  . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.registry' .
    DIRECTORY_SEPARATOR . '.channel.grob' . DIRECTORY_SEPARATOR . 'foo.reg';
$phpunit->assertFileExists($pf2file, 'reg file of foop.reg');
$contents = unserialize(implode('', file($pf2file)));
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

$reg->deletePackage('foo', 'grob');
$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . '.depdb', 'depdb');
$contents = unserialize(implode('', file($php_dir . DIRECTORY_SEPARATOR . '.depdb')));
$phpunit->assertEquals(array (
  '_version' => '1.0',
), $contents, 'depdb after delete');
$phpunit->assertFileNotExists($pf2file, 'reg file of foop.reg after delete');
$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . '.filemap', 'filemap');
$contents = unserialize(implode('', file($php_dir . DIRECTORY_SEPARATOR . '.filemap')));
$phpunit->assertEquals(array (
), $contents, 'filemap');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
