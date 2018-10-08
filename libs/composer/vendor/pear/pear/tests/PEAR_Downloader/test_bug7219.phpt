--TEST--
PEAR_Downloader_Package->detectDependencies(), bug #7219
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$mainpackage = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'bug_7219'. DIRECTORY_SEPARATOR . 'PEAR-1.4.8.tgz';
$archive = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'bug_7219'. DIRECTORY_SEPARATOR . 'Archive_Tar-1.3.1.tgz';
$console = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'bug_7219'. DIRECTORY_SEPARATOR . 'Console_Getopt-1.2.tgz';
$requiredpackage = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'bug_7219'. DIRECTORY_SEPARATOR . 'XML_Parser-1.2.7.tgz';
$GLOBALS['pearweb']->addHtmlConfig('http://pear.php.net/get/XML_Parser-1.2.7.tgz', $requiredpackage);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_parser/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>XML_Parser</p>
 <c>pear.php.net</c>
 <r><v>1.2.7</v><s>stable</s></r>
 <r><v>1.2.6</v><s>stable</s></r>
 <r><v>1.2.5</v><s>stable</s></r>
 <r><v>1.2.4</v><s>stable</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2.0</v><s>stable</s></r>
 <r><v>1.2.0beta3</v><s>beta</s></r>
 <r><v>1.2.0beta2</v><s>beta</s></r>
 <r><v>1.2.0beta1</v><s>beta</s></r>
 <r><v>1.1.0</v><s>stable</s></r>
 <r><v>1.1.0beta2</v><s>beta</s></r>
 <r><v>1.1.0beta1</v><s>beta</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_parser/1.2.7.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/xml_parser">XML_Parser</p>
 <c>pear.php.net</c>
 <v>1.2.7</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>schst</m>
 <s>XML parsing class based on PHP\'s bundled expat</s>
 <d>This is an XML parser based on PHPs built-in xml extension.
It supports two basic modes of operation: &quot;func&quot; and &quot;event&quot;.  In &quot;func&quot; mode, it will look for a function named after each element (xmltag_ELEMENT for start tags and xmltag_ELEMENT_ for end tags), and in &quot;event&quot; mode it uses a set of generic callbacks.

Since version 1.2.0 there\'s a new XML_Parser_Simple class that makes parsing of most XML documents easier, by automatically providing a stack for the elements.
Furthermore its now possible to split the parser from the handler object, so you do not have to extend XML_Parser anymore in order to parse a document with it.</d>
 <da>2005-09-24 11:10:43</da>
 <n>- implemented request #4774: Error message contains column number</n>
 <f>12939</f>
 <g>http://pear.php.net/get/XML_Parser-1.2.7</g>
 <x xlink:href="package.1.2.7.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_parser/deps.1.2.7.txt", 'a:1:{s:8:"required";a:3:{s:3:"php";a:1:{s:3:"min";s:5:"4.2.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}s:7:"package";a:2:{s:4:"name";s:4:"PEAR";s:7:"channel";s:12:"pear.php.net";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR</p>
 <c>pear.php.net</c>
 <r><v>1.4.8</v><s>stable</s></r>
 <r><v>1.4.7</v><s>stable</s></r>
 <r><v>1.4.6</v><s>stable</s></r>
 <r><v>1.4.5</v><s>stable</s></r>
 <r><v>1.4.4</v><s>stable</s></r>
 <r><v>1.4.3</v><s>stable</s></r>
 <r><v>1.4.2</v><s>stable</s></r>
 <r><v>1.4.1</v><s>stable</s></r>
 <r><v>1.4.0</v><s>stable</s></r>
 <r><v>1.4.0RC2</v><s>beta</s></r>
 <r><v>1.4.0RC1</v><s>beta</s></r>
 <r><v>1.4.0b2</v><s>beta</s></r>
 <r><v>1.4.0b1</v><s>beta</s></r>
 <r><v>1.3.6</v><s>stable</s></r>
 <r><v>1.4.0a12</v><s>alpha</s></r>
 <r><v>1.4.0a11</v><s>alpha</s></r>
 <r><v>1.4.0a10</v><s>alpha</s></r>
 <r><v>1.4.0a9</v><s>alpha</s></r>
 <r><v>1.4.0a8</v><s>alpha</s></r>
 <r><v>1.4.0a7</v><s>alpha</s></r>
 <r><v>1.4.0a6</v><s>alpha</s></r>
 <r><v>1.4.0a5</v><s>alpha</s></r>
 <r><v>1.4.0a4</v><s>alpha</s></r>
 <r><v>1.4.0a3</v><s>alpha</s></r>
 <r><v>1.4.0a2</v><s>alpha</s></r>
 <r><v>1.4.0a1</v><s>alpha</s></r>
 <r><v>1.3.5</v><s>stable</s></r>
 <r><v>1.3.4</v><s>stable</s></r>
 <r><v>1.3.3.1</v><s>stable</s></r>
 <r><v>1.3.3</v><s>stable</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3</v><s>stable</s></r>
 <r><v>1.3b6</v><s>beta</s></r>
 <r><v>1.3b5</v><s>beta</s></r>
 <r><v>1.3b3</v><s>beta</s></r>
 <r><v>1.3b2</v><s>beta</s></r>
 <r><v>1.3b1</v><s>beta</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.2b5</v><s>beta</s></r>
 <r><v>1.2b4</v><s>beta</s></r>
 <r><v>1.2b3</v><s>beta</s></r>
 <r><v>1.2b2</v><s>beta</s></r>
 <r><v>1.2b1</v><s>beta</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>1.0b3</v><s>stable</s></r>
 <r><v>1.0b2</v><s>stable</s></r>
 <r><v>1.0b1</v><s>stable</s></r>
 <r><v>0.90</v><s>beta</s></r>
 <r><v>0.11</v><s>beta</s></r>
 <r><v>0.10</v><s>beta</s></r>
 <r><v>0.9</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.8.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.8</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
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
 <da>2006-03-05 15:22:14</da>
 <n>CRITICAL BUGFIX RELEASE

Channels with &quot;-&quot; in their name were suddenly invalid, and
caused crashes in many places due to improper error handling
* fix Bug #6960: channels are not allowed to have &quot;-&quot; in their name
* fix critical Bug #6969: PEAR list-upgrades crashes
* fix Bug #6991: Class \'PEAR_PackageFile_v1\' not found in Registry.php at line 1657
* fix Bug #7008: PEAR_Frontend::setFrontendObject doesn\'t set the object
* fix Bug #7015: install a package.tgz with unknown channel, fatal error in PEAR/Registry.php
* fix Bug #7020: tests/PEAR_Registry/api1_1/test_getChannelValidator.phpt crashes PEAR</n>
 <f>283109</f>
 <g>http://pear.php.net/get/PEAR-1.4.8</g>
 <x xlink:href="package.1.4.8.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.8.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:1:{s:3:"min";s:3:"4.2";}s:13:"pearinstaller";a:1:{s:3:"min";s:8:"1.4.0a12";}s:7:"package";a:4:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:5:"1.3.1";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:5:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"max";s:5:"0.5.0";s:7:"exclude";s:5:"0.5.0";s:9:"conflicts";s:0:"";}i:3;a:5:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"max";s:5:"0.4.0";s:7:"exclude";s:5:"0.4.0";s:9:"conflicts";s:0:"";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:3:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"1.4.0";}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:31:"PEAR\'s PHP-GTK2-based installer";s:4:"name";s:13:"gtk2installer";}s:7:"package";a:2:{s:4:"name";s:18:"PEAR_Frontend_Gtk2";s:7:"channel";s:12:"pear.php.net";}}}}', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/xml_parser/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>XML_Parser</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/XML">XML</ca>
 <l>PHP License</l>
 <s>XML parsing class based on PHP\'s bundled expat</s>
 <d>This is an XML parser based on PHPs built-in xml extension.
It supports two basic modes of operation: &quot;func&quot; and &quot;event&quot;.  In &quot;func&quot; mode, it will look for a function named after each element (xmltag_ELEMENT for start tags and xmltag_ELEMENT_ for end tags), and in &quot;event&quot; mode it uses a set of generic callbacks.

Since version 1.2.0 there\'s a new XML_Parser_Simple class that makes parsing of most XML documents easier, by automatically providing a stack for the elements.
Furthermore its now possible to split the parser from the handler object, so you do not have to extend XML_Parser anymore in order to parse a document with it.</d>
 <r xlink:href="/rest/r/xml_parser"/>
</p>', 'text/xml');
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
$pearc = $reg->getChannel('pear.php.net');
$pearc->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$pearc->setBaseURL('REST1.1', 'http://pear.php.net/rest/');
$reg->updateChannel($pearc);
$p = new test_PEAR_PackageFile($config);
$pear = &$p->fromTgzFile($mainpackage, PEAR_VALIDATE_DOWNLOADING);
$reg->addPackage2($pear);
$pear = &$p->fromTgzFile($archive, PEAR_VALIDATE_DOWNLOADING);
$reg->addPackage2($pear);
$pear = &$p->fromTgzFile($console, PEAR_VALIDATE_DOWNLOADING);
$reg->addPackage2($pear);
$phpunit->assertTrue($reg->packageExists('PEAR'), 'pear is installed');

$dp = new test_PEAR_Downloader($fakelog, array(), $config);
$phpunit->assertNoErrors('after create');
$_test_dep->setPHPVersion('5.1.3');
$_test_dep->setPEARVersion('1.4.8');
$result = $dp->download(array('XML_Parser'));
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 3,
    1 => 'pear/XML_Parser: Skipping required dependency "pear/PEAR" version 1.4.8, already installed as version 1.4.8',
  ),
  array (
    0 => 3,
    1 => 'Downloading "http://pear.php.net/get/XML_Parser-1.2.7.tgz"',
  ),
  array (
    0 => 1,
    1 => 'downloading XML_Parser-1.2.7.tgz ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download XML_Parser-1.2.7.tgz (12,940 bytes)',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '...done: 12,940 bytes',
  ),
)
, $fakelog->getLog(), 'log');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
