--TEST--
Bug #14702: PEAR package installer ignores installed packages
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$pathtopackagexml = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bug14702'. DIRECTORY_SEPARATOR . 'pecl_http-1.6.0.tgz';
$GLOBALS['pearweb']->addHtmlConfig('http://pecl.php.net/get/pecl_http-1.6.0.tgz', $pathtopackagexml);

$pearweb->addRESTConfig("http://pecl.php.net/rest/r/pecl_http/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>pecl_http</p>
 <c>pecl.php.net</c>
 <r><v>1.7.0b1</v><s>beta</s></r>
 <r><v>1.6.3</v><s>stable</s></r>
 <r><v>1.6.2</v><s>stable</s></r>
 <r><v>1.6.1</v><s>stable</s></r>
 <r><v>1.6.0</v><s>stable</s></r>
 <r><v>1.6.0RC1</v><s>beta</s></r>
 <r><v>1.6.0b2</v><s>beta</s></r>
 <r><v>1.5.6</v><s>stable</s></r>
 <r><v>1.6.0b1</v><s>beta</s></r>
 <r><v>1.5.5</v><s>stable</s></r>
 <r><v>1.5.4</v><s>stable</s></r>
 <r><v>1.5.3</v><s>stable</s></r>
 <r><v>1.5.2</v><s>stable</s></r>
 <r><v>1.5.1</v><s>stable</s></r>
 <r><v>1.5.0</v><s>stable</s></r>
 <r><v>1.5.0RC2</v><s>beta</s></r>
 <r><v>1.5.0RC1</v><s>beta</s></r>
 <r><v>1.4.1</v><s>stable</s></r>
 <r><v>1.4.0</v><s>stable</s></r>
 <r><v>1.4.0RC2</v><s>beta</s></r>
 <r><v>1.4.0RC1</v><s>beta</s></r>
 <r><v>1.3.3</v><s>stable</s></r>
 <r><v>1.3.2</v><s>stable</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0.0</v><s>stable</s></r>
 <r><v>1.0.0RC5</v><s>beta</s></r>
 <r><v>1.0.0RC4</v><s>beta</s></r>
 <r><v>1.0.0RC3</v><s>beta</s></r>
 <r><v>1.0.0RC2</v><s>beta</s></r>
 <r><v>1.0.0RC1</v><s>beta</s></r>
 <r><v>0.25.0</v><s>beta</s></r>
 <r><v>0.24.1</v><s>beta</s></r>
 <r><v>0.24.0</v><s>beta</s></r>
 <r><v>0.23.1</v><s>beta</s></r>
 <r><v>0.23.0</v><s>beta</s></r>
 <r><v>0.22.0</v><s>beta</s></r>
 <r><v>0.21.0</v><s>beta</s></r>
 <r><v>0.20.0</v><s>beta</s></r>
 <r><v>0.19.0</v><s>beta</s></r>
 <r><v>0.18.1</v><s>beta</s></r>
 <r><v>0.18.0</v><s>beta</s></r>
 <r><v>0.17.0</v><s>beta</s></r>
 <r><v>0.16.0</v><s>beta</s></r>
 <r><v>0.15.0</v><s>beta</s></r>
 <r><v>0.14.2</v><s>beta</s></r>
 <r><v>0.14.1</v><s>beta</s></r>
 <r><v>0.14.0</v><s>beta</s></r>
 <r><v>0.13.0</v><s>beta</s></r>
 <r><v>0.12.0</v><s>beta</s></r>
 <r><v>0.11.0</v><s>beta</s></r>
 <r><v>0.10.1</v><s>beta</s></r>
 <r><v>0.10.0</v><s>beta</s></r>
 <r><v>0.9.0</v><s>beta</s></r>
 <r><v>0.8.0</v><s>beta</s></r>
 <r><v>0.7.0</v><s>beta</s></r>
 <r><v>0.6.1</v><s>alpha</s></r>
 <r><v>0.6.0</v><s>alpha</s></r>
 <r><v>0.5.1</v><s>alpha</s></r>
 <r><v>0.5.0</v><s>alpha</s></r>
 <r><v>0.4.0</v><s>alpha</s></r>
 <r><v>0.3.0</v><s>alpha</s></r>
 <r><v>0.2.0</v><s>alpha</s></r>
 <r><v>0.1.0</v><s>alpha</s></r>
</a>', 'text/xml');


$pearweb->addRESTConfig("http://pecl.php.net/rest/p/pecl_http/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>pecl_http</n>
 <c>pecl.php.net</c>
 <ca xlink:href="/rest/c/HTTP">HTTP</ca>
 <l>BSD, revised</l>
 <s>Extended HTTP Support</s>
 <d>This HTTP extension aims to provide a convenient and powerful
set of functionality for one of PHPs major applications.

It eases handling of HTTP urls, dates, redirects, headers and
messages, provides means for negotiation of clients preferred
language and charset, as well as a convenient way to send any
arbitrary data with caching and resuming capabilities.

It provides powerful request functionality, if built with CURL
support. Parallel requests are available for PHP 5 and greater.</d>
 <r xlink:href="/rest/r/pecl_http"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pecl.php.net/rest/r/pecl_http/1.6.0.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pecl_http">pecl_http</p>
 <c>pecl.php.net</c>
 <v>1.6.0</v>
 <st>stable</st>
 <l>BSD, revised</l>
 <m>mike</m>
 <s>Extended HTTP Support</s>
 <d>This HTTP extension aims to provide a convenient and powerful
set of functionality for one of PHPs major applications.

It eases handling of HTTP urls, dates, redirects, headers and
messages, provides means for negotiation of clients preferred
language and charset, as well as a convenient way to send any
arbitrary data with caching and resuming capabilities.

It provides powerful request functionality, if built with CURL
support. Parallel requests are available for PHP 5 and greater.</d>
 <da>2007-11-26 09:56:01</da>
 <n>+ Added HttpRequest::flushCookies() (libcurl &gt;= 7.17.1)
+ Added constant HTTP_URL_FROM_ENV
+ Added \'retrycount\' and \'retrydelay\' request options
+ Added libevent support for libcurl (&gt;= 7.16.0):
  o added --with-http-curl-libevent configure option
  o added HttpRequestPool::enableEvents()
* Fixed problems with cookiestore request option introduced with persistent handles
* Fixed crash on prematurely called HttpMessage::next()
* Fixed possible shutdown crash with http_parse_params() and PHP4
* Fixed a possible crash at module shutdown in the persistent handle API
  (probably fixing bug #11509)
* Fixed test suite for PHP4
* Fixed missing PHP_LIBDIR definition in config.m4 for PHP4
* Fixed non-standard shell support in config.m4</n>
 <f>172432</f>
 <g>http://pecl.php.net/get/pecl_http-1.6.0</g>
 <x xlink:href="package.1.6.0.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pecl.php.net/rest/r/pecl_http/deps.1.6.0.txt", 'a:2:{s:8:"required";a:2:{s:3:"php";a:3:{s:3:"min";s:3:"4.3";s:3:"max";s:5:"6.0.0";s:7:"exclude";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:5:"1.4.1";}}s:8:"optional";a:1:{s:9:"extension";a:4:{i:0;a:1:{s:4:"name";s:3:"spl";}i:1;a:1:{s:4:"name";s:7:"session";}i:2;a:1:{s:4:"name";s:4:"hash";}i:3;a:1:{s:4:"name";s:5:"iconv";}}}}', 'text/xml');


$_test_dep->setPEARVersion('1.6.1');
$_test_dep->setPHPVersion('4.3.11');
$_test_dep->setExtensions(array('pecl_http' => '1.6.0'));

$pathtopackagexml = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bug14702'. DIRECTORY_SEPARATOR . 'package.xml';

$dp = new test_PEAR_Downloader($fakelog, array(), $config);
$phpunit->assertNoErrors('after create');
$result = $dp->download(array($pathtopackagexml));

$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => 'Channel validator warning: field "providesextension" - package name "pecl_http" is different from extension name "http"'),
), 'errors');

$phpunit->assertEquals(2, count($result), 'return');
$phpunit->assertIsa('test_PEAR_Downloader_Package', $result[0], 'right class');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf = $result[0]->getPackageFile(), 'right kind of pf');
$phpunit->assertEquals('PEAR_T',       $pf->getPackage(), 'right package');
$phpunit->assertEquals('pear.php.net', $pf->getChannel(), 'right channel');

$dlpackages = $dp->getDownloadedPackages();
$phpunit->assertEquals(2, count($dlpackages), 'downloaded packages count');
$phpunit->assertEquals(3, count($dlpackages[0]), 'internals package count');
$phpunit->assertEquals(array('file', 'info', 'pkg'), array_keys($dlpackages[0]), 'indexes');
$phpunit->assertEquals($pathtopackagexml, $dlpackages[0]['file'], 'file');
$phpunit->assertIsa('PEAR_PackageFile_v2',$dlpackages[0]['info'], 'info');
$phpunit->assertEquals('PEAR_T',          $dlpackages[0]['pkg'],  'PEAR_T');

$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(0, count($after), 'after getdp count');

$installer->setOptions($dp->getOptions());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages');

$ret = $installer->install($result[1], $dp->getOptions());
$phpunit->assertNoErrors('after install');
$phpunit->assertEquals(array (
  'attribs' =>
  array (
    'version' => '2.0',
    'xmlns' => 'http://pear.php.net/dtd/package-2.0',
    'xmlns:tasks' => 'http://pear.php.net/dtd/tasks-1.0',
    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
    'xsi:schemaLocation' => 'http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd',
  ),
  'name' => 'PEAR_T',
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
  'date' => '2004-09-30',
  'version' =>
  array (
    'release' => '1.4.0a1',
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
        'min' => '4.2',
        'max' => '6.0.0',
      ),
      'pearinstaller' =>
      array (
        'min' => '1.4.0dev13',
      ),
      'package' =>
      array (
        'name' => 'pecl_http',
        'channel' => 'pecl.php.net',
        'recommended' => '1.6.0',
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
        'api' => '1.3.0',
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
    'foo.php' =>
    array (
      'name' => 'foo.php',
      'role' => 'php',
      'md5sum' => '718d8596a14d123d83afb0d5d6d6fd96',
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
    'version' => '1.4.0a1',
    'release_date' => '2004-09-30',
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
        'version' => '4.2',
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
        'channel' => 'pecl.php.net',
        'name' => 'pecl_http',
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
), $ret, 'return of install');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php', 'installed file');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
