--TEST--
PEAR_DependencyDB->uninstallPackage()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
$statedir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'registry_tester';
if (file_exists($statedir)) {
    // don't delete existing directories!
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$db = new PEAR_DependencyDB;
$db->setConfig($config);
$db->assertDepsDB();
$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . '.depdb', 'setup');
$contents = unserialize(implode('', file($php_dir . DIRECTORY_SEPARATOR . '.depdb')));
$phpunit->assertEquals(array('_version' => '1.0'), $contents, 'initial create');

require_once 'PEAR/PackageFile/v1.php';
$pf = new PEAR_PackageFile_v1;
$pf->setPackage('foo');
$pf->setConfig($config);
$pf->setDate('2004-11-16');
$pf->setDescription('foo');
$pf->setLicense('PHP License');
$pf->setNotes('foo');
$pf->setState('stable');
$pf->setSummary('foo');
$pf->setVersion('1.0.0');
$pf->addFile('', 'foo.php', array('role' => 'php'));
$pf->addPackageDep('Slonk', '1.0.0', 'ge');
$pf->addPackageDep('Foozrbindie', '2.0.0', 'ge', 'yes');
$pf->addPackageDep('Mboogongle', '0.2.3', 'eq', 'yes');
$pf->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$ret = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertNotFalse($ret, 'validation');

$db->installPackage($pf);

// extensive set up needed for this one :)

require_once 'PEAR/ChannelFile.php';
$ch = new PEAR_ChannelFile;
$ch->setName('grnok');
$ch->setSummary('gronk');
$ch->setServer('grnok');
$ch->setDefaultPEARProtocols();
$reg = &$config->getRegistry();
$reg->addChannel($ch);

$pf2 = new PEAR_PackageFile_v2_rw;
$pf2->setConfig($config);
$pf2->setAPIStability('stable');
$pf2->setAPIVersion('1.0.0');
$pf2->setChannel('grnok');
$pf2->setDate('2004-11-16');
$pf2->setDescription('flongle');
$pf2->setLicense('LGPL', false, 'LICENSE');
$pf2->setNotes('flongle');
$pf2->setPackage('flongle');
$pf2->setPackageType('php');
$pf2->setPearinstallerDep('1.4.0a1');
$pf2->setPhpDep('4.2.0', '5.0.0');
$pf2->setReleaseStability('stable');
$pf2->setReleaseVersion('1.0.2');
$pf2->setSummary('flongle');
$pf2->addConflictingPackageDepWithChannel('Conflicts', 'badboys');
$pf2->addPackageDepWithUri('required', 'uribaby', 'http://www.example.com/flooble.tgz');
$pf2->addPackageDepWithChannel('optional', 'Mboogongle', 'pear.php.net');
$pf2->addDependencyGroup('floo', 'floo powder group?');
$pf2->addGroupPackageDepWithChannel('subpackage', 'floo', 'Foozrbindie', 'pear.php.net');
$pf2->clearContents();
$pf2->addFile('', 'LICENSE', array('role' => 'doc'));
$pf2->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$ret = $pf2->validate();
$phpunit->assertNotFalse($ret, 'validate v2');

$db->installPackage($pf2);
$contents = unserialize(implode('', file($php_dir . DIRECTORY_SEPARATOR . '.depdb')));
$phpunit->assertEquals(array (
  '_version' => '1.0',
  'dependencies' => 
  array (
    'pear.php.net' => 
    array (
      'foo' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Slonk',
            'channel' => 'pear.php.net',
            'min' => '1.0.0',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'Foozrbindie',
            'channel' => 'pear.php.net',
            'min' => '2.0.0',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        2 => 
        array (
          'dep' => 
          array (
            'name' => 'Mboogongle',
            'channel' => 'pear.php.net',
            'min' => '0.2.3',
            'max' => '0.2.3',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
    ),
    'grnok' => 
    array (
      'flongle' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Conflicts',
            'channel' => 'badboys',
            'conflicts' => '',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'uribaby',
            'uri' => 'http://www.example.com/flooble.tgz',
          ),
          'type' => 'required',
          'group' => false,
        ),
        2 => 
        array (
          'dep' => 
          array (
            'name' => 'Mboogongle',
            'channel' => 'pear.php.net',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        3 => 
        array (
          'dep' => 
          array (
            'name' => 'Foozrbindie',
            'channel' => 'pear.php.net',
          ),
          'type' => 'optional',
          'group' => 'floo',
        ),
      ),
    ),
  ),
  'packages' => 
  array (
    'pear.php.net' => 
    array (
      'slonk' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'foo',
        ),
      ),
      'foozrbindie' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'foo',
        ),
        1 => 
        array (
          'channel' => 'grnok',
          'package' => 'flongle',
        ),
      ),
      'mboogongle' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'foo',
        ),
        1 => 
        array (
          'channel' => 'grnok',
          'package' => 'flongle',
        ),
      ),
    ),
    'badboys' => 
    array (
      'conflicts' => 
      array (
        0 => 
        array (
          'channel' => 'grnok',
          'package' => 'flongle',
        ),
      ),
    ),
    '__uri' => 
    array (
      'uribaby' => 
      array (
        0 => 
        array (
          'channel' => 'grnok',
          'package' => 'flongle',
        ),
      ),
    ),
  ),
), $contents, 'complex setup');

$db->uninstallPackage($pf);
$contents = unserialize(implode('', file($php_dir . DIRECTORY_SEPARATOR . '.depdb')));
$phpunit->assertEquals(array (
  '_version' => '1.0',
  'dependencies' => 
  array (
    'grnok' => 
    array (
      'flongle' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Conflicts',
            'channel' => 'badboys',
            'conflicts' => '',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'uribaby',
            'uri' => 'http://www.example.com/flooble.tgz',
          ),
          'type' => 'required',
          'group' => false,
        ),
        2 => 
        array (
          'dep' => 
          array (
            'name' => 'Mboogongle',
            'channel' => 'pear.php.net',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        3 => 
        array (
          'dep' => 
          array (
            'name' => 'Foozrbindie',
            'channel' => 'pear.php.net',
          ),
          'type' => 'optional',
          'group' => 'floo',
        ),
      ),
    ),
  ),
  'packages' => 
  array (
    'pear.php.net' => 
    array (
      'foozrbindie' => 
      array (
        0 => 
        array (
          'channel' => 'grnok',
          'package' => 'flongle',
        ),
      ),
      'mboogongle' => 
      array (
        0 => 
        array (
          'channel' => 'grnok',
          'package' => 'flongle',
        ),
      ),
    ),
    'badboys' => 
    array (
      'conflicts' => 
      array (
        0 => 
        array (
          'channel' => 'grnok',
          'package' => 'flongle',
        ),
      ),
    ),
    '__uri' => 
    array (
      'uribaby' => 
      array (
        0 => 
        array (
          'channel' => 'grnok',
          'package' => 'flongle',
        ),
      ),
    ),
  ),
), $contents, 'uninstall pf1');



$db->uninstallPackage($pf2);
$contents = unserialize(implode('', file($php_dir . DIRECTORY_SEPARATOR . '.depdb')));
$phpunit->assertEquals(array (
  '_version' => '1.0',
), $contents, 'uninstall pf2');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
