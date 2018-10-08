--TEST--
PEAR_Validate->validate(), version tests (extends stable)
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
    'Parser'. DIRECTORY_SEPARATOR .
    'test_basicparse'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = $v2parser->parse('<?xml version="1.0"?>
<package version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0
http://pear.php.net/dtd/tasks-1.0.xsd
http://pear.php.net/dtd/package-2.0
http://pear.php.net/dtd/package-2.0.xsd">
 <name>PEAR</name>
 <channel>pear.php.net</channel>
 <summary>PEAR Base System</summary>
 <description>The PEAR package contains:
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
 </description>
 <lead>
  <name>Stig Bakken</name>
  <user>ssb</user>
  <email>stig@php.net</email>
  <active>yes</active>
 </lead>
 <lead>
  <name>Tomas V.V.Cox</name>
  <user>cox</user>
  <email>cox@idecnet.com</email>
  <active>yes</active>
 </lead>
 <lead>
  <name>Pierre-Alain Joye</name>
  <user>pajoye</user>
  <email>pajoye@pearfr.org</email>
  <active>yes</active>
 </lead>
 <lead>
  <name>Greg Beaver</name>
  <user>cellog</user>
  <email>cellog@php.net</email>
  <active>yes</active>
 </lead>
 <developer>
  <name>Martin Jansen</name>
  <user>mj</user>
  <email>mj@php.net</email>
  <active>yes</active>
 </developer>
 <date>2004-09-30</date>
 <version>
  <release>1.4.0a1</release>
  <api>1.4.0</api>
 </version>
 <stability>
  <release>alpha</release>
  <api>alpha</api>
 </stability>
 <license uri="http://www.php.net/license/3_0.txt">PHP License</license>
 <notes>Installer Roles/Tasks:

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
 * add ability to &quot;pear channel-update channelname&quot; to
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
   and HTML_Template_Flexy installation was attempted)
 </notes>
 <contents>
  <dir name="/">
   <dir name="OS">
    <file name="Guess.php" role="php" />
   </dir> <!-- /OS -->
   <dir name="PEAR">
    <dir name="Command">
     <file name="Auth.php" role="php" />
     <file name="Build.php" role="php" />
     <file name="Channels.php" role="php" />
     <file name="Common.php" role="php" />
     <file name="Config.php" role="php" />
     <file name="Install.php" role="php" />
     <file name="Mirror.php" role="php" />
     <file name="Package.php" role="php" />
     <file name="Registry.php" role="php" />
     <file name="Remote.php" role="php" />
    </dir> <!-- /PEAR/Command -->
    <dir name="Downloader">
     <file name="Package.php" role="php">
      <tasks:replace from="@PEAR-VER@" to="version" type="package-info" />
     </file>
    </dir> <!-- /PEAR/Downloader -->
    <dir name="Frontend">
     <file name="CLI.php" role="php" />
    </dir> <!-- /PEAR/Frontend -->
    <dir name="Installer">
     <dir name="Role">
      <file name="Common.php" role="php" />
      <file name="Data.php" role="php" />
      <file name="Doc.php" role="php" />
      <file name="Ext.php" role="php" />
      <file name="Php.php" role="php" />
      <file name="Script.php" role="php" />
      <file name="Test.php" role="php" />
     </dir> <!-- /PEAR/Installer/Role -->
     <file name="Role.php" role="php" />
    </dir> <!-- /PEAR/Installer -->
    <dir name="PackageFile">
     <dir name="Generator">
      <file name="v1.php" role="php">
       <tasks:replace from="@PEAR-VER@" to="version" type="package-info" />
      </file>
      <file name="v2.php" role="php" />
     </dir> <!-- /PEAR/PackageFile/Generator -->
     <dir name="Parser">
      <file name="v1.php" role="php" />
      <file name="v2.php" role="php" />
     </dir> <!-- /PEAR/PackageFile/Parser -->
     <dir name="v2">
      <file role="php" name="Validator.php"/>
     </dir> <!-- /PEAR/PackageFile/v2 -->
     <file name="v1.php" role="php" />
     <file name="v2.php" role="php" />
    </dir> <!-- /PEAR/PackageFile -->
    <dir name="Task">
     <file name="Common.php" role="php" />
     <file name="Preinstallscript.php" role="php" />
     <file name="Postinstallscript.php" role="php" />
     <file name="Replace.php" role="php" />
    </dir> <!-- /PEAR/Task -->
    <file name="Autoloader.php" role="php" />
    <file name="Builder.php" role="php" />
    <file name="ChannelFile.php" role="php" />
    <file name="Command.php" role="php" />
    <file name="Common.php" role="php" />
    <file name="Config.php" role="php" />
    <file name="Dependency.php" role="php" />
    <file name="DependencyDB.php" role="php" />
    <file name="Dependency2.php" role="php">
     <tasks:replace from="@PEAR-VER@" to="version" type="package-info"/>
    </file>
    <file name="Downloader.php" role="php" />
    <file name="ErrorStack.php" role="php" />
    <file name="FTP.php" role="php" />
    <file name="Installer.php" role="php" />
    <file name="PackageFile.php" role="php">
     <tasks:replace from="@PEAR-VER@" to="version" type="package-info" />
    </file>
    <file name="Packager.php" role="php" />
    <file name="Registry.php" role="php" />
    <file name="Remote.php" role="php" />
    <file name="RunTest.php" role="php" />
    <file name="Validate.php" role="php" />
   </dir> <!-- /PEAR -->
   <dir name="scripts" baseinstalldir="/">
    <file name="pear.bat" role="script">
     <tasks:replace from="@bin_dir@" to="bin_dir" type="pear-config" />
     <tasks:replace from="@php_bin@" to="php_bin" type="pear-config" />
     <tasks:replace from="@include_path@" to="php_dir" type="pear-config" />
    </file>
    <file name="pear.sh" role="script">
     <tasks:replace from="@php_bin@" to="php_bin" type="pear-config" />
     <tasks:replace from="@php_dir@" to="php_dir" type="pear-config" />
     <tasks:replace from="@pear_version@" to="version" type="package-info" />
     <tasks:replace from="@include_path@" to="php_dir" type="pear-config" />
    </file>
    <file name="pearcmd.php" role="php">
     <tasks:replace from="@php_bin@" to="php_bin" type="pear-config" />
     <tasks:replace from="@php_dir@" to="php_dir" type="pear-config" />
     <tasks:replace from="@pear_version@" to="version" type="package-info" />
     <tasks:replace from="@include_path@" to="php_dir" type="pear-config" />
    </file>
   </dir> <!-- /scripts -->
   <file name="package.dtd" role="data" />
   <file name="PEAR.php" role="php" />
   <file name="pearchannel.xml" role="data" />
   <file name="System.php" role="php" />
   <file name="template.spec" role="data" />
  </dir> <!-- / -->
 </contents>
 <dependencies>
  <required>
   <php>
    <min>4.2</min>
    <max>6.0.0</max>
   </php>
   <pearinstaller>
    <min>1.4.0dev13</min>
   </pearinstaller>
   <package>
    <name>Archive_Tar</name>
    <channel>pear.php.net</channel>
    <min>1.1</min>
   </package>
   <package>
    <name>Console_Getopt</name>
    <channel>pear.php.net</channel>
    <min>1.2</min>
   </package>
   <package>
    <name>XML_RPC</name>
    <channel>pear.php.net</channel>
    <min>1.0.4</min>
   </package>
   <extension>
    <name>xml</name>
   </extension>
   <extension>
    <name>pcre</name>
   </extension>
  </required>
  <group name="remoteinstall" hint="adds the ability to install packages to a remote ftp server">
   <package>
    <name>Net_FTP</name>
    <channel>pear.php.net</channel>
    <min>1.3.0RC1</min>
   </package>
  </group>
 </dependencies>
 <phprelease>
  <installconditions>
   <os>
    <name>windows</name>
   </os>
  </installconditions>
  <filelist>
   <install as="pear.bat" name="scripts/pear.bat" />
   <install as="pearcmd.php" name="scripts/pearcmd.php" />
   <ignore name="scripts/pear.sh" />
  </filelist>
 </phprelease>
 <phprelease>
  <filelist>
   <install as="pear" name="scripts/pear.sh" />
   <install as="pearcmd.php" name="scripts/pearcmd.php" />
   <ignore name="scripts/pear.bat" />
  </filelist>
 </phprelease>
 <changelog>
  <release>
   <version>
    <release>1.3.3</release>
    <api>1.3.0</api>
   </version>
   <stability>
    <release>stable</release>
    <api>stable</api>
   </stability>
   <date>2004-10-28</date>
   <license uri="http://www.php.net/license/3_0.txt">PHP License</license>
   <notes>Installer:
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
 * fix warning in PEAR_Registry for windows 98 users
  </notes>
  </release>
 </changelog>
</package>', 'package2.xml');
$a = &$pf->getRW();
$pf = &$a;
$phpunit->assertNoErrors('parse');
$pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertNoErrors('validate');
$val->setPackageFile($pf);

$res = $val->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertNoErrors('$val->validate');
$phpunit->assertTrue($res, '$val->validate');

/****************************************** stable tests *****************************************/
$pf->setReleaseStability('stable');
$pf->setExtends('PEAR');
$pf->setPackage('PEAR2');
$pf->setReleaseVersion('2.4.0a1');
$res = $val->validate(PEAR_VALIDATE_PACKAGING);
$phpunit->assertTrue($res, 'attempt 2.4.0a1 stable');
$phpunit->assertEquals(array (
  'warnings' => 
  array (
    0 => 
    array (
      'field' => 'version',
      'reason' => 'version "2.4.0a1" or any RC/beta/alpha version cannot be stable',
    ),
    1 => 
    array (
      'field' => 'date',
      'reason' => 'Release Date "2004-09-30" is not today',
    ),
  ),
  'errors' => 
  array (
  ),
), $val->getFailures(), 'failures attempt 2.4.0a1 stable');

$pf->setReleaseVersion('0.4.5');
$res = $val->validate(PEAR_VALIDATE_PACKAGING);
$phpunit->assertTrue($res, 'attempt 0.4.5 stable');
$phpunit->assertEquals(array (
  'warnings' => 
  array (
    0 => 
    array (
      'field' => 'version',
      'reason' => 'versions less than 1.0.0 cannot be stable',
    ),
    array (
      'field' => 'date',
      'reason' => 'Release Date "2004-09-30" is not today',
    ),
  ),
  'errors' => 
  array (
  ),
), $val->getFailures(), 'failures attempt 0.4.5 stable');

$pf->setReleaseVersion('3.0.0');
$res = $val->validate(PEAR_VALIDATE_PACKAGING);
$phpunit->assertTrue($res, 'attempt 3.0.0 stable');
$phpunit->assertEquals(array (
  'warnings' => 
  array (
    0 => 
    array (
      'field' => 'package',
      'reason' => 'package PEAR2 extends package PEAR and so the name should have a postfix equal to the major version like "PEAR3"',
    ),
    1 => 
    array (
      'field' => 'version',
      'reason' => 'first version number "3" must match the postfix of package name "PEAR2" (2)',
    ),
    array (
      'field' => 'date',
      'reason' => 'Release Date "2004-09-30" is not today',
    ),
  ),
  'errors' => 
  array (
  ),
), $val->getFailures(), 'failures attempt 3.0.0 stable');

$pf->setReleaseVersion('30.0.0');
$res = $val->validate(PEAR_VALIDATE_PACKAGING);
$phpunit->assertTrue($res, 'attempt 30.0.0 stable');
$phpunit->assertEquals(array (
  'warnings' => 
  array (
    0 => 
    array (
      'field' => 'package',
      'reason' => 'package PEAR2 extends package PEAR and so the name should have a postfix equal to the major version like "PEAR30"',
    ),
    1 => 
    array (
      'field' => 'version',
      'reason' => 'first version number "30" must match the postfix of package name "PEAR2" (2)',
    ),
    array (
      'field' => 'date',
      'reason' => 'Release Date "2004-09-30" is not today',
    ),
  ),
  'errors' => 
  array (
  ),
), $val->getFailures(), 'failures attempt 30.0.0 stable');

$pf->setReleaseVersion('1.0.0');
$pf->setReleaseStability('stable');
$res = $val->validate(PEAR_VALIDATE_PACKAGING);
$phpunit->assertTrue($res, 'attempt 1.0.0 stable');
$phpunit->assertEquals(array (
  'warnings' => 
  array (
    0 => 
    array (
      'field' => 'package',
      'reason' => 'package PEAR2 extends package PEAR and so the name should have a postfix equal to the major version like "PEAR1"',
    ),
    1 => 
    array (
      'field' => 'version',
      'reason' => 'first version number "1" must match the postfix of package name "PEAR2" (2)',
    ),
    array (
      'field' => 'date',
      'reason' => 'Release Date "2004-09-30" is not today',
    ),
  ),
  'errors' => 
  array (
  ),
), $val->getFailures(), 'failures attempt 1.0.0 stable');

$pf->setReleaseVersion('2.0.0pl1');
$pf->setReleaseStability('stable');
$res = $val->validate(PEAR_VALIDATE_PACKAGING);
$phpunit->assertTrue($res, 'attempt 2.0.0pl1 stable');
$phpunit->assertEquals(array (
  'warnings' => 
  array (
    array (
      'field' => 'date',
      'reason' => 'Release Date "2004-09-30" is not today',
    ),
  ),
  'errors' => 
  array (
  ),
), $val->getFailures(), 'failures attempt 2.0.0pl1 stable');

$pf->setPackage('PEAR30');
$pf->setReleaseVersion('30.3.5');
$res = $val->validate(PEAR_VALIDATE_PACKAGING);
$phpunit->assertTrue($res, 'attempt 30.3.5 stable');
$phpunit->assertEquals(array (
  'warnings' => 
  array (
    array (
      'field' => 'date',
      'reason' => 'Release Date "2004-09-30" is not today',
    ),
  ),
  'errors' => 
  array (
  ),
), $val->getFailures(), 'failures attempt 30.3.5 stable');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
