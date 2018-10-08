--TEST--
PEAR_Registry->addPackage() (API v1.1)
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
$pf->resetFilelist();
$pf->installedFile('foo.php', array('role' => 'php'));
$pf->setInstalledAs('foo.php', $php_dir . DIRECTORY_SEPARATOR . 'foo.php');
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

$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . '.depdb', 'depdb');
$contents = unserialize(implode('', file($php_dir . DIRECTORY_SEPARATOR . '.depdb')));

$phpunit->assertEquals(array (
  '_version' => '1.0',
  'dependencies' =>
  array (
    'pear.php.net' =>
    array (
      'foop' =>
      array (
        0 =>
        array (
          'dep' =>
          array (
            'name' => 'Floop',
            'channel' => 'pear.php.net',
            'min' => '1.0',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
    ),
  ),
  'packages' =>
  array (
    'pear.php.net' =>
    array (
      'floop' =>
      array (
        0 =>
        array (
          'channel' => 'pear.php.net',
          'package' => 'foop',
        ),
      ),
    ),
  ),
), $contents, 'depdb');
$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . '.filemap', 'filemap');
$contents = unserialize(implode('', file($php_dir . DIRECTORY_SEPARATOR . '.filemap')));
$phpunit->assertEquals(array (
  'php' =>
  array (
    'foo.php' => 'foop',
  ),
), $contents, 'filemap');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
