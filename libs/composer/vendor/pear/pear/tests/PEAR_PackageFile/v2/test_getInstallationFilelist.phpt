--TEST--
PEAR_PackageFile_Parser_v2->getInstallationFilelist()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_getFilelist'. DIRECTORY_SEPARATOR . 'package2.xml';
$_test_dep->setOS('windows');
$_test_dep->setArch('Linux host 2.4.17 #2 SMP Tue Feb 12 15:10:04 CET 2002 i686 unknown', '1.2');
$_test_dep->setPHPVersion('4.5.6');
$v2 = new test_PEAR_PackageFile_v2;
$v2->setConfig($config);
$v2->setPackageType('php');
$v2->addFile('', 'always.php', array('role' => 'php'));
$v2->addFile('', 'windows.php', array('role' => 'php'));
$v2->addFile('', 'linux.php', array('role' => 'php'));
$v2->addFile('', 'php4.php', array('role' => 'php'));
$v2->addFile('', 'php5.php', array('role' => 'php'));
$v2->addFile('', 'hasxmlrpc.php', array('role' => 'php'));
$v2->addFile('', 'noxmlrpc.php', array('role' => 'php'));
/*********************************************** PHP tests ****************************************/
$v2->setPhpInstallCondition('5.0.0', '6.0.0');
$v2->addIgnore('php4.php');
$v2->addInstallAs('php5.php', 'php.php');
$v2->addRelease();
$v2->setPhpInstallCondition('4.0.0', '5.0.0');
$v2->addIgnore('php5.php');
$v2->addInstallAs('php4.php', 'php.php');
$phpunit->assertEquals(array (
  'always.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'always.php',
    ),
  ),
  'windows.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'windows.php',
    ),
  ),
  'linux.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'linux.php',
    ),
  ),
  'php4.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'php4.php',
      'install-as' => 'php.php',
    ),
  ),
  'hasxmlrpc.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'hasxmlrpc.php',
    ),
  ),
  'noxmlrpc.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'noxmlrpc.php',
    ),
  ),
), $v2->getInstallationFilelist(), 'php4');
$_test_dep->setPHPVersion('5.1.0');
$phpunit->assertEquals(array (
  'always.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'always.php',
    ),
  ),
  'windows.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'windows.php',
    ),
  ),
  'linux.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'linux.php',
    ),
  ),
  'php5.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'php5.php',
      'install-as' => 'php.php',
    ),
  ),
  'hasxmlrpc.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'hasxmlrpc.php',
    ),
  ),
  'noxmlrpc.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'noxmlrpc.php',
    ),
  ),
), $v2->getInstallationFilelist(), 'php5');

/*********************************************** OS tests ****************************************/
$v2->setPackageType('php');
$v2->setOsInstallCondition('windows');
$v2->addIgnore('linux.php');
$v2->addInstallAs('windows.php', 'os.php');
$v2->addRelease();
$v2->setOsInstallCondition('linux');
$v2->addIgnore('windows.php');
$v2->addInstallAs('linux.php', 'os.php');
$phpunit->assertEquals(array (
  'always.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'always.php',
    ),
  ),
  'windows.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'windows.php',
      'install-as' => 'os.php',
    ),
  ),
  'php4.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'php4.php',
    ),
  ),
  'php5.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'php5.php',
    ),
  ),
  'hasxmlrpc.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'hasxmlrpc.php',
    ),
  ),
  'noxmlrpc.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'noxmlrpc.php',
    ),
  ),
), $v2->getInstallationFilelist(), 'windows');
$_test_dep->setOs('linux');
$phpunit->assertEquals(array (
  'always.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'always.php',
    ),
  ),
  'linux.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'linux.php',
      'install-as' => 'os.php',
    ),
  ),
  'php4.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'php4.php',
    ),
  ),
  'php5.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'php5.php',
    ),
  ),
  'hasxmlrpc.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'hasxmlrpc.php',
    ),
  ),
  'noxmlrpc.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'noxmlrpc.php',
    ),
  ),
), $v2->getInstallationFilelist(), 'linux');

/******************************************** extension tests *************************************/
$_test_dep->setExtensions(array('xmlrpc' => '1.0'));
$v2->setPackageType('php');
$v2->addExtensionInstallCondition('xmlrpc');
$v2->addIgnore('noxmlrpc.php');
$v2->addInstallAs('hasxmlrpc.php', 'xmlrpc.php');
$v2->addRelease();
$v2->addIgnore('hasxmlrpc.php');
$v2->addInstallAs('noxmlrpc.php', 'xmlrpc.php');
$phpunit->assertEquals(array (
  'always.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'always.php',
    ),
  ),
  'windows.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'windows.php',
    ),
  ),
  'linux.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'linux.php',
    ),
  ),
  'php4.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'php4.php',
    ),
  ),
  'php5.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'php5.php',
    ),
  ),
  'hasxmlrpc.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'hasxmlrpc.php',
      'install-as' => 'xmlrpc.php',
    ),
  ),
), $v2->getInstallationFilelist(), 'has xmlrpc');
$_test_dep->setExtensions(array());
$phpunit->assertEquals(array (
  'always.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'always.php',
    ),
  ),
  'windows.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'windows.php',
    ),
  ),
  'linux.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'linux.php',
    ),
  ),
  'php4.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'php4.php',
    ),
  ),
  'php5.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'php5.php',
    ),
  ),
  'noxmlrpc.php' => 
  array (
    'attribs' => 
    array (
      'role' => 'php',
      'name' => 'noxmlrpc.php',
      'install-as' => 'xmlrpc.php',
    ),
  ),
), $v2->getInstallationFilelist(), 'no xmlrpc');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
