--TEST--
PEAR_Installer->install() with simple local package.xml [BC-compatible version]
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
    'packages'. DIRECTORY_SEPARATOR . 'simplepackage.xml';

$ret = $installer->install($pathtopackagexml);
$phpunit->assertNoErrors('after install');
$phpunit->assertEquals(array (
  'provides' =>
  array (
    'class;OS_Guess' =>
    array (
      'type' => 'class',
      'name' => 'OS_Guess',
      'explicit' => true,
    ),
    'class;System' =>
    array (
      'type' => 'class',
      'name' => 'System',
      'explicit' => true,
    ),
    'function;md5_file' =>
    array (
      'type' => 'function',
      'name' => 'md5_file',
      'explicit' => true,
    ),
  ),
  'filelist' =>
  array (
    'foo.php' =>
    array (
      'role' => 'php',
      'installed_as' => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php',
    ),
  ),
  'xsdversion' => '1.0',
  'package' => 'PEAR',
  'summary' => 'PEAR Base System',
  'description' => 'The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception PHP5 error handling mechanism
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling of common operations
   with files and directories
 * the PEAR base class
 
',
  'maintainers' =>
  array (
    0 =>
    array (
      'handle' => 'ssb',
      'role' => 'lead',
      'name' => 'Stig Bakken',
      'email' => 'stig@php.net',
    ),
    1 =>
    array (
      'handle' => 'cellog',
      'role' => 'lead',
      'name' => 'Greg Beaver',
      'email' => 'cellog@php.net',
    ),
    2 =>
    array (
      'handle' => 'cox',
      'role' => 'lead',
      'name' => 'Tomas V.V.Cox',
      'email' => 'cox@idecnet.com',
    ),
    3 =>
    array (
      'handle' => 'pajoye',
      'role' => 'lead',
      'name' => 'Pierre-Alain Joye',
      'email' => 'pajoye@pearfr.org',
    ),
    4 =>
    array (
      'handle' => 'mj',
      'role' => 'developer',
      'name' => 'Martin Jansen',
      'email' => 'mj@php.net',
    ),
  ),
  'version' => '1.4.0a1',
  'release_date' => '2004-10-21',
  'release_license' => 'PHP License',
  'release_state' => 'alpha',
  'release_notes' => 'Installer Roles/Tasks:

 * package.xml 2.0 uses a command pattern, allowing extensibility
 * implement the replace, postinstallscript, and preinstallscript tasks

Installer Dependency Support:

 * package.xml 2.0 has continued to improve and evolve
 * Downloader/Package.php is now used to coordinate downloading.  Old code
   has not yet been deleted, as error handling is crappy right now.  Uninstall
   ordering is broken, and needs to be redone.
 * Pre-download dependency resolution works, mostly.
 * There is no way to disable dependency resolution at the moment, this will be done.
 * Dependency2.php is used by the new PEAR_Downloader_Channel to resolve dependencies
   and include downloaded files in the calculations.
 * DependencyDB.php is used to resolve complex dependencies between installed packages
   and any dependencies installed later (a conflicts/not dependency cannot be honored
   without this DB)

Installer Channel Support:

 * channel XSD is available on pearweb
 * add channel.listAll and channel.update to default PEAR protocols
 * add ability to "pear channel-update channelname" to
   retrieve updates manually for individual channels
 * fix channel.xml generation to use a valid schema declaration

Installer:

 * with --remoteconfig option, it is possible to remotely install and uninstall packages
   to an FTP server.  It works by mirroring a local installation, and requires a
   special, separate local install.
 * Channels implemented
 * Bug #1242: array-to-string conversion
 * fix Bug #2189 upgrade-all stops if dependancy fails
 * fix Bug #1637 The use of interface causes warnings when packaging with PEAR
 * fix Bug #1420 Parser bug for T_DOUBLE_COLON
 * fix Request #2220 pear5 build fails on dual php4/php5 system
 * Major bug in Registry - false file conflicts on data/doc/test role
   was possible (and would happen if HTML_Template_IT was installed
   and HTML_Template_Flexy installation was attempted)',
  'changelog' =>
  array (
    0 =>
    array (
      'version' => '1.3.3',
      'release_date' => '2004-10-28',
      'release_state' => 'stable',
      'release_notes' => 'Installer:
 * fix Bug #1186 raise a notice error on PEAR::Common $_packageName
 * fix Bug #1249 display the right state when using --force option
 * fix Bug #2189 upgrade-all stops if dependancy fails
 * fix Bug #1637 The use of interface causes warnings when packaging with PEAR
 * fix Bug #1420 Parser bug for T_DOUBLE_COLON
 * fix Request #2220 pear5 build fails on dual php4/php5 system
 * fix Bug #1163  pear makerpm fails with packages that supply role="doc"

Other:
 * add PEAR_Exception class for PHP5 users
 * fix critical problem in package.xml for linux in 1.3.2
 * fix staticPopCallback() in PEAR_ErrorStack
 * fix warning in PEAR_Registry for windows 98 users',
    ),
  ),
  '_lastversion' => null,
  'dirtree' =>
  array (
    $temp_path . DIRECTORY_SEPARATOR . 'php' => true,
  ),
), $ret, 'return of install');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php',
    'installed file');
$reg = &$config->getRegistry();
$info = $reg->packageInfo('PEAR');
$phpunit->assertTrue(isset($info['_lastmodified']), 'lastmodified is set?');
unset($info['_lastmodified']);
$phpunit->assertEquals($ret, $info, 'test installation');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
