--TEST--
upgrade command, test for bug #5683 - classical deadlock if deps between downloaded/installed don't match
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);
$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setPEARVersion('1.4.2');
$_test_dep->setExtensions(array('xml' => 0, 'pcre' => 1));
$dir = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR;
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Archive_Tar</p>
 <c>pear.php.net</c>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.10-b1</v><s>beta</s></r>
 <r><v>0.9</v><s>stable</s></r>
 <r><v>0.4</v><s>stable</s></r>
 <r><v>0.3</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/1.3.1.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/archive_tar">Archive_Tar</p>
 <c>pear.php.net</c>
 <v>1.3.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>vblavet</m>
 <s>Tar file management class</s>
 <d>This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.
</d>
 <da>2005-03-17 16:09:16</da>
 <n>Correct Bug #3855
</n>
 <f>15102</f>
 <g>http://pear.php.net/get/Archive_Tar-1.3.1</g>
 <x xlink:href="package.1.3.1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/deps.1.3.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Console_Getopt</p>
 <c>pear.php.net</c>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.11</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/1.2.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/console_getopt">Console_Getopt</p>
 <c>pear.php.net</c>
 <v>1.2</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>andrei</m>
 <s>Command-line option parser</s>
 <d>This is a PHP implementation of &quot;getopt&quot; supporting both
short and long options.
</d>
 <da>2003-12-11 14:26:46</da>
 <n>Fix to preserve BC with 1.0 and allow correct behaviour for new users
</n>
 <f>3370</f>
 <g>http://pear.php.net/get/Console_Getopt-1.2</g>
 <x xlink:href="package.1.2.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/deps.1.2.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>XML_RPC</p>
 <c>pear.php.net</c>
 <r><v>1.4.4</v><s>stable</s></r>
 <r><v>1.4.3</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.1</max></co>
</r>
 <r><v>1.4.2</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a14</max></co>
</r>
 <r><v>1.4.1</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a14</max></co>
</r>
 <r><v>1.4.0</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a12</max></co>
</r>
 <r><v>1.3.3</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a12</max></co>
</r>
 <r><v>1.3.2</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a12</max></co>
</r>
 <r><v>1.3.1</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a12</max></co>
</r>
 <r><v>1.3.0</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a12</max></co>
</r>
 <r><v>1.3.0RC3</v><s>beta</s></r>
 <r><v>1.3.0RC2</v><s>beta</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a10</max></co>
</r>
 <r><v>1.3.0RC1</v><s>beta</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a10</max></co>
</r>
 <r><v>1.2.2</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a4</max></co>
</r>
 <r><v>1.2.1</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a2</max></co>
</r>
 <r><v>1.2.0</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0a1</min><max>1.4.0a1</max></co>
</r>
 <r><v>1.2.0RC7</v><s>beta</s></r>
 <r><v>1.2.0RC6</v><s>beta</s></r>
 <r><v>1.2.0RC5</v><s>beta</s></r>
 <r><v>1.2.0RC4</v><s>beta</s></r>
 <r><v>1.2.0RC3</v><s>beta</s></r>
 <r><v>1.2.0RC2</v><s>beta</s></r>
 <r><v>1.2.0RC1</v><s>beta</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/1.4.4.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/xml_rpc">XML_RPC</p>
 <c>pear.php.net</c>
 <v>1.4.4</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>danielc</m>
 <s>PHP implementation of the XML-RPC protocol</s>
 <d>A PEAR-ified version of Useful Inc\'s XML-RPC for PHP.

It has support for HTTP/HTTPS transport, proxies and authentication.
</d>
 <da>2005-10-15 16:36:02</da>
 <n>* Properly deal with empty values in struct\'s.
</n>
 <f>24447</f>
 <g>http://pear.php.net/get/XML_RPC-1.4.4</g>
 <x xlink:href="package.1.4.4.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/deps.1.4.4.txt", 'b:0;', 'text/xml');
$pearweb->addHTMLConfig('http://pear.php.net/get/XML_RPC-1.4.4.tgz', $dir . 'XML_RPC-1.4.4.tgz');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the beta-quality PEAR_Exception PHP5 error handling mechanism
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling of common operations
   with files and directories
 * the PEAR base class

  New features in a nutshell:
  * full support for channels
  * pre-download dependency validation
  * new package.xml 2.0 format allows tremendous flexibility while maintaining BC
  * support for optional dependency groups and limited support for sub-packaging
  * robust dependency support
  * full dependency validation on uninstall
  * remote install for hosts with only ftp access - no more problems with
    restricted host installation
  * full support for mirroring
  * support for bundling several packages into a single tarball
  * support for static dependencies on a url-based package
  * support for custom file roles and installation tasks

  NOTE: users of PEAR_Frontend_Web/PEAR_Frontend_Gtk must upgrade their installations
  to the latest version, or PEAR will not upgrade properly</d>
 <r xlink:href="/rest/r/pear"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/archive_tar/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Archive_Tar</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Tar file management class</s>
 <d>This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.</d>
 <r xlink:href="/rest/r/archive_tar"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/console_getopt/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Console_Getopt</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Console">Console</ca>
 <l>PHP License</l>
 <s>Command-line option parser</s>
 <d>This is a PHP implementation of &quot;getopt&quot; supporting both
short and long options.</d>
 <r xlink:href="/rest/r/console_getopt"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/p/xml_rpc/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>XML_RPC</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>PHP implementation of the XML-RPC protocol</s>
 <d>A PEAR-ified version of Useful Inc\'s XML-RPC for PHP.

It has support for HTTP/HTTPS transport, proxies and authentication.</d>
 <r xlink:href="/rest/r/xml_rpc"/>
</p>', 'text/xml');
$command->run('install', array(), array($dir . 'PEAR-1.4.3.tgz', $dir . 'XML_RPC-1.4.3.tgz',
    $dir . 'Console_Getopt-1.2.tgz', $dir . 'Archive_Tar-1.3.1.tgz'));
$phpunit->assertNoErrors('setup');
$phpunit->assertEquals(4, count($reg->listPackages()), 'num packages');
$phpunit->assertEquals('1.4.3', $reg->packageInfo('PEAR', 'version'), 'PEAR version');
$phpunit->assertEquals('1.4.3', $reg->packageInfo('XML_RPC', 'version'), 'XML_RPC version');
unset($GLOBALS['__Stupid_php4_a']); // reset downloader
$command->run('upgrade', array(), array($dir . 'PEAR-1.4.4.tgz'));
$phpunit->assertNoErrors('full test');
$phpunit->assertEquals(4, count($reg->listPackages()), 'num packages 2');
$phpunit->assertEquals('1.4.4', $reg->packageInfo('PEAR', 'version'), 'PEAR version 2');
$phpunit->assertEquals('1.4.4', $reg->packageInfo('XML_RPC', 'version'), 'XML_RPC version 2');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
