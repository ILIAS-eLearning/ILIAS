--TEST--
PEAR_Installer->install() with bundle [preferred_state = alpha]
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$ch = new PEAR_ChannelFile;
$ch->setName('smork');
$ch->setSummary('smork');
$ch->setDefaultPEARProtocols();
$reg = &$config->getRegistry();
$phpunit->assertTrue($reg->addChannel($ch), 'smork setup');
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'PEARbundle-1.4.0a1.tgz';
$_test_dep->setPHPVersion('4.3.11');
$_test_dep->setPEARVersion('1.4.0a1');
$dp = new test_PEAR_Downloader($fakelog, array(), $config);
$phpunit->assertNoErrors('after create');
$config->set('preferred_state', 'alpha');
$result = &$dp->download(array($pathtopackagexml));
$phpunit->assertEquals(3, count($result), 'return');
$phpunit->assertIsa('PEAR_Downloader_Package', $result[0], 'right class 0');
$phpunit->assertIsa('PEAR_Downloader_Package', $result[1], 'right class 1');
$phpunit->assertIsa('PEAR_Downloader_Package', $result[2], 'right class 2');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf = $result[0]->getPackageFile(), 'right kind of pf 0');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf1 = $result[1]->getPackageFile(), 'right kind of pf 1');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf2 = $result[2]->getPackageFile(), 'right kind of pf 2');
$phpunit->assertEquals('PEAR1', $pf->getPackage(), 'right package');
$phpunit->assertEquals('pear.php.net', $pf->getChannel(), 'right channel');
$phpunit->assertEquals('Bar', $pf1->getPackage(), 'right package 1');
$phpunit->assertEquals('pear.php.net', $pf1->getChannel(), 'right channel 1');
$phpunit->assertEquals('Foobar', $pf2->getPackage(), 'right package 2');
$phpunit->assertEquals('smork', $pf2->getChannel(), 'right channel 2');
$dlpackages = $dp->getDownloadedPackages();
$phpunit->assertEquals(3, count($dlpackages), 'downloaded packages count');
$phpunit->assertEquals(3, count($dlpackages[0]), 'internals package count');
$phpunit->assertEquals(3, count($dlpackages[1]), 'internals package count 1');
$phpunit->assertEquals(3, count($dlpackages[2]), 'internals package count 2');
$phpunit->assertEquals(array('file', 'info', 'pkg'), array_keys($dlpackages[0]), 'indexes');
$phpunit->assertEquals(array('file', 'info', 'pkg'), array_keys($dlpackages[1]), 'indexes 1');
$phpunit->assertEquals(array('file', 'info', 'pkg'), array_keys($dlpackages[2]), 'indexes 2');
$phpunit->assertEquals($result[0]->_downloader->getDownloadDir() . DIRECTORY_SEPARATOR .
    'PEAR1-1.5.0a1.tgz',
    $dlpackages[0]['file'], 'file');
$phpunit->assertIsa('PEAR_PackageFile_v2',
    $dlpackages[0]['info'], 'info');
$phpunit->assertEquals('PEAR1',
    $dlpackages[0]['pkg'], 'PEAR1');
$phpunit->assertEquals($result[1]->_downloader->getDownloadDir() . DIRECTORY_SEPARATOR .
    'Bar-1.5.2.tgz',
    $dlpackages[1]['file'], 'file 1');
$phpunit->assertIsa('PEAR_PackageFile_v2',
    $dlpackages[1]['info'], 'info 1');
$phpunit->assertEquals('Bar',
    $dlpackages[1]['pkg'], 'Bar');
$phpunit->assertEquals($result[2]->_downloader->getDownloadDir() . DIRECTORY_SEPARATOR .
    'Foobar-1.5.0a1.tgz',
    $dlpackages[2]['file'], 'file 2');
$phpunit->assertIsa('PEAR_PackageFile_v2',
    $dlpackages[2]['info'], 'info 2');
$phpunit->assertEquals('Foobar',
    $dlpackages[2]['pkg'], 'Foobar');
$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(0, count($after), 'after getdp count');
$phpunit->assertEquals(array (), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array (
), $fakelog->getDownload(), 'download callback messages');
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages');
$installer->setOptions($dp->getOptions());
$ret = $installer->install($result[0], $dp->getOptions());
$phpunit->assertNoErrors('after install');
$phpunit->assertEquals(array (
  'attribs' =>
  array (
    'packagerversion' => '1.4.0a1',
    'version' => '2.0',
    'xmlns' => 'http://pear.php.net/dtd/package-2.0',
    'xmlns:tasks' => 'http://pear.php.net/dtd/tasks-1.0',
    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
    'xsi:schemaLocation' => 'http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd',
  ),
  'name' => 'Foobar',
  'channel' => 'smork',
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
 * the PEAR base class',
  'lead' =>
  array (
    0 =>
    array (
      'name' => 'Stig Bakken',
      'user' => 'ssb',
      'email' => 'stig@php.net',
      'active' => 'yes',
    ),
    1 =>
    array (
      'name' => 'Greg Beaver',
      'user' => 'cellog',
      'email' => 'cellog@php.net',
      'active' => 'yes',
    ),
    2 =>
    array (
      'name' => 'Tomas V.V.Cox',
      'user' => 'cox',
      'email' => 'cox@idecnet.com',
      'active' => 'yes',
    ),
    3 =>
    array (
      'name' => 'Pierre-Alain Joye',
      'user' => 'pajoye',
      'email' => 'pajoye@pearfr.org',
      'active' => 'yes',
    ),
  ),
  'developer' =>
  array (
    'name' => 'Martin Jansen',
    'user' => 'mj',
    'email' => 'mj@php.net',
    'active' => 'yes',
  ),
  'date' => '2004-12-29',
  'time' => '14:28:55',
  'version' =>
  array (
    'release' => '1.5.0a1',
    'api' => '1.5.0a1',
  ),
  'stability' =>
  array (
    'release' => 'alpha',
    'api' => 'alpha',
  ),
  'license' =>
  array (
    'attribs' =>
    array (
      'uri' => 'http://www.php.net/license/3_0.txt',
    ),
    '_content' => 'PHP License',
  ),
  'notes' => 'Installer Roles/Tasks:

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
  'contents' =>
  array (
    'dir' =>
    array (
      'attribs' =>
      array (
        'name' => '/',
      ),
      'file' =>
      array (
        'attribs' =>
        array (
          'md5sum' => 'ed0384ad29e60110b310a02e95287ee6',
          'name' => 'foo12.php',
          'role' => 'php',
        ),
      ),
    ),
  ),
  'dependencies' =>
  array (
    'required' =>
    array (
      'php' =>
      array (
        'min' => '4.3.6',
        'max' => '6.0.0',
      ),
      'pearinstaller' =>
      array (
        'min' => '1.4.0a1',
      ),
    ),
  ),
  'phprelease' => '',
  'changelog' =>
  array (
    'release' =>
    array (
      'version' =>
      array (
        'release' => '1.3.3',
        'api' => '1.3.3',
      ),
      'stability' =>
      array (
        'release' => 'stable',
        'api' => 'stable',
      ),
      'date' => '2004-10-28',
      'license' =>
      array (
        'attribs' =>
        array (
          'uri' => 'http://www.php.net/license/3_0.txt',
        ),
        '_content' => 'PHP License',
      ),
      'notes' => 'Installer:
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
  'filelist' =>
  array (
    'foo12.php' =>
    array (
      'md5sum' => 'ed0384ad29e60110b310a02e95287ee6',
      'name' => 'foo12.php',
      'role' => 'php',
      'installed_as' => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo12.php',
    ),
  ),
  '_lastversion' => null,
  'dirtree' =>
  array (
    $temp_path . DIRECTORY_SEPARATOR . 'php' => true,
  ),
  'old' =>
  array (
    'version' => '1.5.0a1',
    'release_date' => '2004-12-29',
    'release_state' => 'alpha',
    'release_license' => 'PHP License',
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
    'release_deps' =>
    array (
      0 =>
      array (
        'type' => 'php',
        'rel' => 'le',
        'version' => '6.0.0',
        'optional' => 'no',
      ),
      1 =>
      array (
        'type' => 'php',
        'rel' => 'ge',
        'version' => '4.3.6',
        'optional' => 'no',
      ),
      2 =>
      array (
        'type' => 'pkg',
        'channel' => 'pear.php.net',
        'name' => 'PEAR',
        'rel' => 'ge',
        'version' => '1.4.0a1',
        'optional' => 'no',
      ),
    ),
    'maintainers' =>
    array (
      0 =>
      array (
        'name' => 'Stig Bakken',
        'email' => 'stig@php.net',
        'active' => 'yes',
        'handle' => 'ssb',
        'role' => 'lead',
      ),
      1 =>
      array (
        'name' => 'Greg Beaver',
        'email' => 'cellog@php.net',
        'active' => 'yes',
        'handle' => 'cellog',
        'role' => 'lead',
      ),
      2 =>
      array (
        'name' => 'Tomas V.V.Cox',
        'email' => 'cox@idecnet.com',
        'active' => 'yes',
        'handle' => 'cox',
        'role' => 'lead',
      ),
      3 =>
      array (
        'name' => 'Pierre-Alain Joye',
        'email' => 'pajoye@pearfr.org',
        'active' => 'yes',
        'handle' => 'pajoye',
        'role' => 'lead',
      ),
      4 =>
      array (
        'name' => 'Martin Jansen',
        'email' => 'mj@php.net',
        'active' => 'yes',
        'handle' => 'mj',
        'role' => 'developer',
      ),
    ),
  ),
  'xsdversion' => '2.0',
), $ret, 'return of install 1');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo12.php',
    'installed file');
$reg = &$config->getRegistry();
$info = $reg->packageInfo('Foobar', null, 'smork');
$phpunit->assertTrue(isset($info['_lastmodified']), 'lastmodified is set?');
unset($info['_lastmodified']);
$phpunit->assertEquals($ret, $info, 'test installation, Foobar');

$ret = $installer->install($result[1], $dp->getOptions());
$phpunit->assertNoErrors('after install');
$phpunit->assertEquals(array (
  'attribs' =>
  array (
    'packagerversion' => '1.4.0a1',
    'version' => '2.0',
    'xmlns' => 'http://pear.php.net/dtd/package-2.0',
    'xmlns:tasks' => 'http://pear.php.net/dtd/tasks-1.0',
    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
    'xsi:schemaLocation' => 'http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd',
  ),
  'name' => 'Bar',
  'channel' => 'pear.php.net',
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
 * the PEAR base class',
  'lead' =>
  array (
    0 =>
    array (
      'name' => 'Stig Bakken',
      'user' => 'ssb',
      'email' => 'stig@php.net',
      'active' => 'yes',
    ),
    1 =>
    array (
      'name' => 'Greg Beaver',
      'user' => 'cellog',
      'email' => 'cellog@php.net',
      'active' => 'yes',
    ),
    2 =>
    array (
      'name' => 'Tomas V.V.Cox',
      'user' => 'cox',
      'email' => 'cox@idecnet.com',
      'active' => 'yes',
    ),
    3 =>
    array (
      'name' => 'Pierre-Alain Joye',
      'user' => 'pajoye',
      'email' => 'pajoye@pearfr.org',
      'active' => 'yes',
    ),
  ),
  'developer' =>
  array (
    'name' => 'Martin Jansen',
    'user' => 'mj',
    'email' => 'mj@php.net',
    'active' => 'yes',
  ),
  'date' => '2004-12-29',
  'time' => '21:06:11',
  'version' =>
  array (
    'release' => '1.5.2',
    'api' => '1.5.2',
  ),
  'stability' =>
  array (
    'release' => 'stable',
    'api' => 'stable',
  ),
  'license' =>
  array (
    'attribs' =>
    array (
      'uri' => 'http://www.php.net/license/3_0.txt',
    ),
    '_content' => 'PHP License',
  ),
  'notes' => 'Installer Roles/Tasks:

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
  'contents' =>
  array (
    'dir' =>
    array (
      'attribs' =>
      array (
        'name' => '/',
      ),
      'file' =>
      array (
        'attribs' =>
        array (
          'md5sum' => 'ed0384ad29e60110b310a02e95287ee6',
          'name' => 'foo1.php',
          'role' => 'php',
        ),
      ),
    ),
  ),
  'dependencies' =>
  array (
    'required' =>
    array (
      'php' =>
      array (
        'min' => '4.3.6',
        'max' => '6.0.0',
      ),
      'pearinstaller' =>
      array (
        'min' => '1.4.0a1',
      ),
      'package' =>
      array (
        'name' => 'Foobar',
        'channel' => 'smork',
      ),
    ),
  ),
  'phprelease' => '',
  'changelog' =>
  array (
    'release' =>
    array (
      'version' =>
      array (
        'release' => '1.3.3',
        'api' => '1.3.3',
      ),
      'stability' =>
      array (
        'release' => 'stable',
        'api' => 'stable',
      ),
      'date' => '2004-10-28',
      'license' =>
      array (
        'attribs' =>
        array (
          'uri' => 'http://www.php.net/license/3_0.txt',
        ),
        '_content' => 'PHP License',
      ),
      'notes' => 'Installer:
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
  'filelist' =>
  array (
    'foo1.php' =>
    array (
      'md5sum' => 'ed0384ad29e60110b310a02e95287ee6',
      'name' => 'foo1.php',
      'role' => 'php',
      'installed_as' => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo1.php',
    ),
  ),
  '_lastversion' => null,
  'dirtree' =>
  array (
    $temp_path . DIRECTORY_SEPARATOR . 'php' => true,
  ),
  'old' =>
  array (
    'version' => '1.5.2',
    'release_date' => '2004-12-29',
    'release_state' => 'stable',
    'release_license' => 'PHP License',
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
    'release_deps' =>
    array (
      0 =>
      array (
        'type' => 'php',
        'rel' => 'le',
        'version' => '6.0.0',
        'optional' => 'no',
      ),
      1 =>
      array (
        'type' => 'php',
        'rel' => 'ge',
        'version' => '4.3.6',
        'optional' => 'no',
      ),
      2 =>
      array (
        'type' => 'pkg',
        'channel' => 'pear.php.net',
        'name' => 'PEAR',
        'rel' => 'ge',
        'version' => '1.4.0a1',
        'optional' => 'no',
      ),
      3 =>
      array (
        'type' => 'pkg',
        'channel' => 'smork',
        'name' => 'Foobar',
        'rel' => 'has',
        'optional' => 'no',
      ),
    ),
    'maintainers' =>
    array (
      0 =>
      array (
        'name' => 'Stig Bakken',
        'email' => 'stig@php.net',
        'active' => 'yes',
        'handle' => 'ssb',
        'role' => 'lead',
      ),
      1 =>
      array (
        'name' => 'Greg Beaver',
        'email' => 'cellog@php.net',
        'active' => 'yes',
        'handle' => 'cellog',
        'role' => 'lead',
      ),
      2 =>
      array (
        'name' => 'Tomas V.V.Cox',
        'email' => 'cox@idecnet.com',
        'active' => 'yes',
        'handle' => 'cox',
        'role' => 'lead',
      ),
      3 =>
      array (
        'name' => 'Pierre-Alain Joye',
        'email' => 'pajoye@pearfr.org',
        'active' => 'yes',
        'handle' => 'pajoye',
        'role' => 'lead',
      ),
      4 =>
      array (
        'name' => 'Martin Jansen',
        'email' => 'mj@php.net',
        'active' => 'yes',
        'handle' => 'mj',
        'role' => 'developer',
      ),
    ),
  ),
  'xsdversion' => '2.0',
), $ret, 'return of install');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo1.php',
    'installed file');
$reg = &$config->getRegistry();
$info = $reg->packageInfo('Bar');
$phpunit->assertTrue(isset($info['_lastmodified']), 'lastmodified is set?');
unset($info['_lastmodified']);
$phpunit->assertEquals($ret, $info, 'test installation, Bar');


$ret = $installer->install($result[2], $dp->getOptions());
$phpunit->assertNoErrors('after install');
$phpunit->assertEquals(array (
  'attribs' =>
  array (
    'packagerversion' => '1.4.0a1',
    'version' => '2.0',
    'xmlns' => 'http://pear.php.net/dtd/package-2.0',
    'xmlns:tasks' => 'http://pear.php.net/dtd/tasks-1.0',
    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
    'xsi:schemaLocation' => 'http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd',
  ),
  'name' => 'PEAR1',
  'channel' => 'pear.php.net',
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
 * the PEAR base class',
  'lead' =>
  array (
    0 =>
    array (
      'name' => 'Stig Bakken',
      'user' => 'ssb',
      'email' => 'stig@php.net',
      'active' => 'yes',
    ),
    1 =>
    array (
      'name' => 'Tomas V.V.Cox',
      'user' => 'cox',
      'email' => 'cox@idecnet.com',
      'active' => 'yes',
    ),
    2 =>
    array (
      'name' => 'Pierre-Alain Joye',
      'user' => 'pajoye',
      'email' => 'pajoye@pearfr.org',
      'active' => 'yes',
    ),
    3 =>
    array (
      'name' => 'Greg Beaver',
      'user' => 'cellog',
      'email' => 'cellog@php.net',
      'active' => 'yes',
    ),
  ),
  'developer' =>
  array (
    'name' => 'Martin Jansen',
    'user' => 'mj',
    'email' => 'mj@php.net',
    'active' => 'yes',
  ),
  'date' => '2004-12-29',
  'time' => '21:09:08',
  'version' =>
  array (
    'release' => '1.5.0a1',
    'api' => '1.4.0',
  ),
  'stability' =>
  array (
    'release' => 'alpha',
    'api' => 'alpha',
  ),
  'license' =>
  array (
    'attribs' =>
    array (
      'uri' => 'http://www.php.net/license/3_0.txt',
    ),
    '_content' => 'PHP License',
  ),
  'notes' => 'stuff',
  'contents' =>
  array (
    'dir' =>
    array (
      'attribs' =>
      array (
        'name' => '/',
      ),
      'file' =>
      array (
        'attribs' =>
        array (
          'md5sum' => 'ed0384ad29e60110b310a02e95287ee6',
          'name' => 'foo.php',
          'role' => 'php',
        ),
      ),
    ),
  ),
  'dependencies' =>
  array (
    'required' =>
    array (
      'php' =>
      array (
        'min' => '4.2.0',
        'max' => '6.0.0',
      ),
      'pearinstaller' =>
      array (
        'min' => '1.4.0dev13',
      ),
      'package' =>
      array (
        0 =>
        array (
          'name' => 'Foo',
          'channel' => 'pear.php.net',
          'conflicts' => '',
        ),
        1 =>
        array (
          'name' => 'Bar',
          'channel' => 'pear.php.net',
          'min' => '1.0.0',
        ),
      ),
    ),
  ),
  'phprelease' => '',
  'filelist' =>
  array (
    'foo.php' =>
    array (
      'md5sum' => 'ed0384ad29e60110b310a02e95287ee6',
      'name' => 'foo.php',
      'role' => 'php',
      'installed_as' => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php',
    ),
  ),
  '_lastversion' => null,
  'dirtree' =>
  array (
    $temp_path . DIRECTORY_SEPARATOR . 'php' => true,
  ),
  'old' =>
  array (
    'version' => '1.5.0a1',
    'release_date' => '2004-12-29',
    'release_state' => 'alpha',
    'release_license' => 'PHP License',
    'release_notes' => 'stuff',
    'release_deps' =>
    array (
      0 =>
      array (
        'type' => 'php',
        'rel' => 'le',
        'version' => '6.0.0',
        'optional' => 'no',
      ),
      1 =>
      array (
        'type' => 'php',
        'rel' => 'ge',
        'version' => '4.2.0',
        'optional' => 'no',
      ),
      2 =>
      array (
        'type' => 'pkg',
        'channel' => 'pear.php.net',
        'name' => 'PEAR',
        'rel' => 'ge',
        'version' => '1.4.0dev13',
        'optional' => 'no',
      ),
      3 =>
      array (
        'type' => 'pkg',
        'channel' => 'pear.php.net',
        'name' => 'Foo',
        'rel' => 'not',
      ),
      4 =>
      array (
        'type' => 'pkg',
        'channel' => 'pear.php.net',
        'name' => 'Bar',
        'rel' => 'ge',
        'version' => '1.0.0',
        'optional' => 'no',
      ),
    ),
    'maintainers' =>
    array (
      0 =>
      array (
        'name' => 'Stig Bakken',
        'email' => 'stig@php.net',
        'active' => 'yes',
        'handle' => 'ssb',
        'role' => 'lead',
      ),
      1 =>
      array (
        'name' => 'Tomas V.V.Cox',
        'email' => 'cox@idecnet.com',
        'active' => 'yes',
        'handle' => 'cox',
        'role' => 'lead',
      ),
      2 =>
      array (
        'name' => 'Pierre-Alain Joye',
        'email' => 'pajoye@pearfr.org',
        'active' => 'yes',
        'handle' => 'pajoye',
        'role' => 'lead',
      ),
      3 =>
      array (
        'name' => 'Greg Beaver',
        'email' => 'cellog@php.net',
        'active' => 'yes',
        'handle' => 'cellog',
        'role' => 'lead',
      ),
      4 =>
      array (
        'name' => 'Martin Jansen',
        'email' => 'mj@php.net',
        'active' => 'yes',
        'handle' => 'mj',
        'role' => 'developer',
      ),
    ),
  ),
  'xsdversion' => '2.0',
)
, $ret, 'return of install 2');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php',
    'installed file');
$reg = &$config->getRegistry();
$info = $reg->packageInfo('PEAR1');
$phpunit->assertTrue(isset($info['_lastmodified']), 'lastmodified is set?');
unset($info['_lastmodified']);
$phpunit->assertEquals($ret, $info, 'test installation, PEAR1');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
