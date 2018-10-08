--TEST--
PEAR_DependencyDB->installPackage()
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
      ),
      'mboogongle' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'foo',
        ),
      ),
    ),
  ),
), $contents, 'initial create');

// extensive set up needed for this one :)

require_once 'PEAR/ChannelFile.php';
$ch = new PEAR_ChannelFile;
$ch->setName('grnok');
$ch->setSummary('gronk');
$ch->setServer('grnok');
$ch->setDefaultPEARProtocols();
$reg = &$config->getRegistry();
$reg->addChannel($ch);

$pf = new PEAR_PackageFile_v2_rw;
$pf->setConfig($config);
$pf->setAPIStability('stable');
$pf->setAPIVersion('1.0.0');
$pf->setChannel('grnok');
$pf->setDate('2004-11-16');
$pf->setDescription('flongle');
$pf->setLicense('LGPL', false, 'LICENSE');
$pf->setNotes('flongle');
$pf->setPackage('flongle');
$pf->setPackageType('php');
$pf->setPearinstallerDep('1.4.0a1');
$pf->setPhpDep('4.2.0', '5.0.0');
$pf->setReleaseStability('stable');
$pf->setReleaseVersion('1.0.2');
$pf->setSummary('flongle');
$pf->addConflictingPackageDepWithChannel('Conflicts', 'badboys');
$pf->addPackageDepWithUri('required', 'uribaby', 'http://www.example.com/flooble.tgz');
$pf->addPackageDepWithChannel('optional', 'Mboogongle', 'pear.php.net');
$pf->addDependencyGroup('floo', 'floo powder group?');
$pf->addGroupPackageDepWithChannel('subpackage', 'floo', 'Foozrbindie', 'pear.php.net');
$pf->clearContents();
$pf->addFile('', 'LICENSE', array('role' => 'doc'));
$pf->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$ret = $pf->validate();
$phpunit->assertNotFalse($ret, 'validate v2');

$db->installPackage($pf);
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
), $contents, 'complex example');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
