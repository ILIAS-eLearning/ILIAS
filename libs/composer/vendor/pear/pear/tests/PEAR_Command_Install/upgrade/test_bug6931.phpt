--TEST--
upgrade command, test for bug #6931
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$dir = dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'packages'. DIRECTORY_SEPARATOR;
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$chan->setBaseURL('REST1.1', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pearweb->addHTMLConfig('http://pear.php.net/get/PEAR-1.4.0a12.tgz', $dir . 'PEAR-1.4.0a12.tgz');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/allreleases.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>XML_RPC</p>
 <c>pear.php.net</c>
 <r><v>1.4.5</v><s>stable</s></r>
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

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/1.4.5.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/xml_rpc">XML_RPC</p>
 <c>pear.php.net</c>
 <v>1.4.5</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>danielc</m>
 <s>PHP implementation of the XML-RPC protocol</s>
 <d>A PEAR-ified version of Useful Inc\'s XML-RPC for PHP.

It has support for HTTP/HTTPS transport, proxies and authentication.</d>
 <da>2006-01-14 17:34:28</da>
 <n>* Have server send headers individualy as opposed to sending them all at once. Necessary due to changes PHP 4.4.2.</n>
 <f>29172</f>
 <g>http://pear.php.net/get/XML_RPC-1.4.5</g>
 <x xlink:href="package.1.4.5.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/deps.1.4.5.txt", 'a:1:{s:8:"required";a:2:{s:3:"php";a:1:{s:3:"min";s:5:"4.2.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}}}', 'text/xml');
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

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a12.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0a12</v>
 <st>alpha</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception PHP5 error handling mechanism
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling of common operations
   with files and directories
 * the PEAR base class</d>
 <da>2005-05-28 23:19:58</da>
 <n>This is a major milestone release for PEAR.  In addition to several killer features,
  every single element of PEAR has a regression test, and so stability is much higher
  than any previous PEAR release, even with the alpha label.

  New features in a nutshell:
  * full support for channels
  * pre-download dependency validation
  * new package.xml 2.0 format allows tremendous flexibility while maintaining BC
  * support for optional dependency groups and limited support for sub-packaging
  * robust dependency support
  * full dependency validation on uninstall
  * support for binary PECL packages
  * remote install for hosts with only ftp access - no more problems with
    restricted host installation
  * full support for mirroring
  * support for bundling several packages into a single tarball
  * support for static dependencies on a url-based package

  Specific changes from 1.3.5:
  * Implement request #1789: SSL support for xml-rpc and download
  * Everything above here that you just read

  Specific changes from 1.4.0a1:
  * Fix Bug #3610: fix for PDO package in 1.3.5 was never merged to 1.4.0a1
  * Fix Bug #3612: fatal error in PEAR_Downloader_Package
  * Use 1.2.0 as recommended version of XML_RPC

  Specific changes from 1.4.0a2:
 BC BREAK FOR PECL DEVS ONLY:
 In order to circumvent strict package-validation, use
 &quot;pear channel-update pecl.php.net&quot; prior to packaging
 a pecl release.
  * Fix package.xml version 2.0 generation from package.xml 1.0
  * Fix Bug #3634: still too many pear-specific restrictions on package valid
  * Implement Request #3647: &quot;pear package&quot; only includes one package.xml
  * Fix Bug #3677: Post-Install script message needs to display channel name

  Specific changes from 1.4.0a3:
  * upgrade suggested XML_RPC version to 1.2.1

  Specific changes from 1.4.0a4:
  * upgrade suggested XML_RPC version to 1.2.2
  * attempt to address memory issues
  * relax validation further
  * disable debug_backtrace() in PEAR_Error constructor of PEAR installer
  * fix a strange version number condition when two packages were upgraded at the same time.
  * fix Bug #3808 channel packages with non-baseinstalldir files will conflict on upgrade
  * fix Bug #3801 [PATCH] analyzeSourceCode() reports PHP4 code as PHP5
  * fix Bug #3671 Installing package features doesn\'t work as expected
  * implement Request #3717 [Patch] Implement Simple run-tests output

  Specific changes from 1.4.0a5:
  * fix Bug #3860 PEAR_Dependency2 not included in 1 case

  Specific changes from 1.4.0a6:
  * implement &lt;usesrole&gt;/&lt;usestask&gt; for custom role/task graceful failure
  * REALLY fix the debug_backtrace() issue (modified wrong pearcmd.php)
  * fix Bug #3864 Invalid dependency relation
  * fix Bug #3863 illogical warning about PEAR_Frontend_Gtk 0.4.0 with PEAR 1.4.0a6

  Specific changes from 1.4.0a7:
  * greatly improve the flexibility of post-install scripts
    - &lt;param&gt; is no longer required
    - skipParamgroup() method in Frontends allows dynamic manipulation of what input is
      requested from users
  * make error message when a user has no write access absolutely clear and unmistakable
  * update to new header comment block standard
  * slight improvement to speed and possibly memory use of Installer when a lot of
    package.xml version 1.0 packages are installed
  * add &quot;peardev&quot; command for those with memory_limit issue
  * make package-validate command work on packaged .tgz files

  Specific changes from 1.4.0a8:
  * add --package option to run-tests command, to run installed .phpt tests
  * significantly drop pear\'s memory footprint for all commands
  * fix fatal errors when installing pecl packages
  * make download command work for non-root in a shared environment
  * make sure that if 1.4.0a8 (alpha) is installed, and 1.3.6 (newer) exists, pear will not
    attempt to &quot;upgrade&quot; to 1.3.6
  * split PEAR_PackageFile_v2 into two classes, read-only PEAR_PackageFile_v2, and read-write
    PEAR_PackageFile_v2_rw

  Specific changes from 1.4.0a9:
  * add support for writeable tasks
  * fix potential fatal errors in run-tests command, -p option
  * fix --installroot option for installation
  * move run-tests command into its own file (testing may expand)
  * fix fatal error if package.xml has no version=&quot;X.0&quot;
  * fix Bug #3966: Improper path in PEAR/PackageFile/v2.php
  * fix Bug #3990: PEAR_Error PEAR_EXCEPTION broken
  * fix Bug #4021: PEAR_Config file_exists can cause warnings
  * fix Bug #1870: pear makerpm dependancies
  * fix Bug #4038: Array to string conversion in PEAR/Frontend/CLI.php
  * fix Bug #4060: pear upgrade Auth_HTTP fails
  * fix Bug #4072: pear list-all -c channel does not list installed packages

  Specific changes from 1.4.0a10:
  * Add new &quot;unusualbaseinstall&quot; role type that allows custom roles similar
    data/test/doc to honor the baseinstalldir attribute
  * fix Bug #4095: System::rm does not handle links correctly
  * fix Bug #4097: Wrong logging in PEAR_Command_Test
  * make pear/pecl commands list only pear/pecl packages
  * fix Bug #4161: pear download always leaves a package.xml in the dir
  * make PEAR_Remote messages more helpful (include server name)
  * make list-upgrades only search channels from which we have installed packages
  * remove &lt;max&gt; tag requirement for php dependency

  Specific changes from 1.4.0a11:
  * Implement REST 1.0 as per Request #2781
  * REST is the default connection method if available
  * fix bugs in PEAR_ChannelFile REST handling
  * fix Bug #4069: pear list-all -c &lt;ChannelAlias&gt; does not work
  * fix Bug #4249: download-all broken in 1.4.0a11
  * fix Bug #4257: if rel=&quot;has&quot; is used with a version=&quot;&quot; attribute, the warning does not work
  * fix Bug #4278: Parser V1: error handling borked !
  * fix Bug #4279: Typo in DependencyDB (_version)
  * fix Bug #4285: pear install *.tgz miss dependencies
  * fix Bug #4353: fatal error if using remote_config variable
  * fix Bug #4354: Remote PEAR upgrade and uninstall operation fail
  * fix Bug #4355: PEAR 1.4.0a11 ftpInstall chokes on package2.xml packages
  * fix Bug #4400: pear download chiara/Chiara_XML_RPC5-alpha fails
  * fix Bug #4458: packaging error message better description
  * implement Request #2781: support for static channel releases.xml summary
  * implement versioned conflicting dependencies
  * fix major problems in subpackages
  * The next version will split off PEAR_ErrorStack into its own package
  * fix problems with zero-length files that have tasks on installation
  * add a check for channel.xml up-to-dateness and gentle warning to users on
    installing a package from that channel</n>
 <f>264326</f>
 <g>http://pear.php.net/get/PEAR-1.4.0a12</g>
 <x xlink:href="package.1.4.0a12.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a12.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:8:"1.4.0a10";}s:7:"package";a:3:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:5:"1.3.1";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.2";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.1";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/allreleases.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
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

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/1.3.1.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
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
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/allreleases.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Console_Getopt</p>
 <c>pear.php.net</c>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.11</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/1.2.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
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
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/1.4.3.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/xml_rpc">XML_RPC</p>
 <c>pear.php.net</c>
 <v>1.4.3</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>danielc</m>
 <s>PHP implementation of the XML-RPC protocol</s>
 <d>A PEAR-ified version of Useful Inc\'s XML-RPC for PHP.

It has support for HTTP/HTTPS transport, proxies and authentication.</d>
 <da>2005-09-24 14:22:55</da>
 <n>* Make XML_RPC_encode() properly handle dateTime.iso8601.  Request 5117.</n>
 <f>27198</f>
 <g>http://pear.php.net/get/XML_RPC-1.4.3</g>
 <x xlink:href="package.1.4.3.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/deps.1.4.3.txt", 'a:1:{s:8:"required";a:2:{s:3:"php";a:1:{s:3:"min";s:5:"4.2.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_web/allreleases.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR_Frontend_Web</p>
 <c>pear.php.net</c>
 <r><v>0.5.0</v><s>alpha</s></r>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2.2</v><s>beta</s></r>
 <r><v>0.2.1</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_web/0.5.0.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear_frontend_web">PEAR_Frontend_Web</p>
 <c>pear.php.net</c>
 <v>0.5.0</v>
 <st>alpha</st>
 <l>PHP License</l>
 <m>pajoye</m>
 <s>HTML (Web) PEAR Installer</s>
 <d>Web Interface to the PEAR Installer

</d>
 <da>2006-03-01 22:53:24</da>
 <n>Major features addition: channel support
Also, support for post-install scripts on install, and package.xml 2.0

</n>
 <f>38606</f>
 <g>http://pear.php.net/get/PEAR_Frontend_Web-0.5.0</g>
 <x xlink:href="package.0.5.0.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_web/deps.0.5.0.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:20:"Net_UserAgent_Detect";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:16:"HTML_Template_IT";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:7:"1.4.0a1";s:4:"name";s:4:"PEAR";}}', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_gtk/allreleases.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR_Frontend_Gtk</p>
 <c>pear.php.net</c>
 <r><v>0.4.0</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>snapshot</s></r>
 <r><v>0.1</v><s>snapshot</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_gtk/0.4.0.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear_frontend_gtk">PEAR_Frontend_Gtk</p>
 <c>pear.php.net</c>
 <v>0.4.0</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>alan_k</m>
 <s>Gtk (Desktop) PEAR Package Manager</s>
 <d>Desktop Interface to the PEAR Package Manager, Requires PHP-GTK

</d>
 <da>2005-03-14 02:22:46</da>
 <n>Implement channels, support PEAR 1.4.0 (Greg Beaver)
    Tidy up logging a little.

</n>
 <f>69762</f>
 <g>http://pear.php.net/get/PEAR_Frontend_Gtk-0.4.0</g>
 <x xlink:href="package.0.4.0.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_gtk/deps.0.4.0.txt", 'b:0;', 'text/xml');
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
$pearweb->addRESTConfig("http://pear.php.net/rest/p/packages.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>pear.php.net</c>
 <p>Archive_Tar</p>
 <p>Archive_Zip</p>
 <p>Auth</p>
 <p>Auth_HTTP</p>
 <p>Auth_PrefManager</p>
 <p>Auth_PrefManager2</p>
 <p>Auth_RADIUS</p>
 <p>Auth_SASL</p>
 <p>Benchmark</p>
 <p>Cache</p>
 <p>Cache_Lite</p>
 <p>Calendar</p>
 <p>CodeGen</p>
 <p>CodeGen_MySQL</p>
 <p>CodeGen_MySQL_Plugin</p>
 <p>CodeGen_MySQL_UDF</p>
 <p>CodeGen_PECL</p>
 <p>Config</p>
 <p>Console_Color</p>
 <p>Console_Getargs</p>
 <p>Console_Getopt</p>
 <p>Console_ProgressBar</p>
 <p>Console_Table</p>
 <p>Contact_AddressBook</p>
 <p>Contact_Vcard_Build</p>
 <p>Contact_Vcard_Parse</p>
 <p>Crypt_Blowfish</p>
 <p>Crypt_CBC</p>
 <p>Crypt_CHAP</p>
 <p>Crypt_HMAC</p>
 <p>Crypt_RC4</p>
 <p>Crypt_RSA</p>
 <p>Crypt_Xtea</p>
 <p>Crypt_XXTEA</p>
 <p>Date</p>
 <p>Date_Holidays</p>
 <p>DB</p>
 <p>DBA</p>
 <p>DBA_Relational</p>
 <p>DB_ado</p>
 <p>DB_DataObject</p>
 <p>DB_DataObject_FormBuilder</p>
 <p>DB_ldap</p>
 <p>DB_ldap2</p>
 <p>DB_NestedSet</p>
 <p>DB_odbtp</p>
 <p>DB_Pager</p>
 <p>DB_QueryTool</p>
 <p>DB_Sqlite_Tools</p>
 <p>DB_Table</p>
 <p>Event_Dispatcher</p>
 <p>File</p>
 <p>File_Archive</p>
 <p>File_Bittorrent</p>
 <p>File_DICOM</p>
 <p>File_DNS</p>
 <p>File_Find</p>
 <p>File_Fortune</p>
 <p>File_Fstab</p>
 <p>File_Gettext</p>
 <p>File_HtAccess</p>
 <p>File_IMC</p>
 <p>File_MARC</p>
 <p>File_Ogg</p>
 <p>File_Passwd</p>
 <p>File_PDF</p>
 <p>File_SearchReplace</p>
 <p>File_SMBPasswd</p>
 <p>File_XSPF</p>
 <p>FSM</p>
 <p>Games_Chess</p>
 <p>Genealogy_Gedcom</p>
 <p>Gtk2_EntryDialog</p>
 <p>Gtk2_ExceptionDump</p>
 <p>Gtk2_FileDrop</p>
 <p>Gtk2_IndexedComboBox</p>
 <p>Gtk2_PHPConfig</p>
 <p>Gtk2_ScrollingLabel</p>
 <p>Gtk2_VarDump</p>
 <p>Gtk_FileDrop</p>
 <p>Gtk_MDB_Designer</p>
 <p>Gtk_ScrollingLabel</p>
 <p>Gtk_Styled</p>
 <p>Gtk_VarDump</p>
 <p>HTML_AJAX</p>
 <p>HTML_BBCodeParser</p>
 <p>HTML_Common</p>
 <p>HTML_Common2</p>
 <p>HTML_Crypt</p>
 <p>HTML_CSS</p>
 <p>HTML_Form</p>
 <p>HTML_Javascript</p>
 <p>HTML_Menu</p>
 <p>HTML_Page</p>
 <p>HTML_Page2</p>
 <p>HTML_Progress</p>
 <p>HTML_Progress2</p>
 <p>HTML_QuickForm</p>
 <p>HTML_QuickForm2</p>
 <p>HTML_QuickForm_advmultiselect</p>
 <p>HTML_QuickForm_altselect</p>
 <p>HTML_QuickForm_CAPTCHA</p>
 <p>HTML_QuickForm_Controller</p>
 <p>HTML_QuickForm_DHTMLRulesTableless</p>
 <p>HTML_QuickForm_ElementGrid</p>
 <p>HTML_QuickForm_Livesearch</p>
 <p>HTML_QuickForm_Renderer_Tableless</p>
 <p>HTML_QuickForm_SelectFilter</p>
 <p>HTML_Safe</p>
 <p>HTML_Select</p>
 <p>HTML_Select_Common</p>
 <p>HTML_Table</p>
 <p>HTML_Table_Matrix</p>
 <p>HTML_TagCloud</p>
 <p>HTML_Template_Flexy</p>
 <p>HTML_Template_IT</p>
 <p>HTML_Template_PHPLIB</p>
 <p>HTML_Template_Sigma</p>
 <p>HTML_Template_Xipe</p>
 <p>HTML_TreeMenu</p>
 <p>HTTP</p>
 <p>HTTP_Client</p>
 <p>HTTP_Download</p>
 <p>HTTP_FloodControl</p>
 <p>HTTP_Header</p>
 <p>HTTP_Request</p>
 <p>HTTP_Server</p>
 <p>HTTP_Session</p>
 <p>HTTP_Session2</p>
 <p>HTTP_SessionServer</p>
 <p>HTTP_Upload</p>
 <p>HTTP_WebDAV_Client</p>
 <p>HTTP_WebDAV_Server</p>
 <p>I18N</p>
 <p>I18Nv2</p>
 <p>I18N_UnicodeString</p>
 <p>Image_3D</p>
 <p>Image_Barcode</p>
 <p>Image_Canvas</p>
 <p>Image_Color</p>
 <p>Image_Color2</p>
 <p>Image_GIS</p>
 <p>Image_Graph</p>
 <p>Image_GraphViz</p>
 <p>Image_IPTC</p>
 <p>Image_MonoBMP</p>
 <p>Image_Puzzle</p>
 <p>Image_Remote</p>
 <p>Image_Text</p>
 <p>Image_Tools</p>
 <p>Image_Transform</p>
 <p>Image_WBMP</p>
 <p>Image_XBM</p>
 <p>Inline_C</p>
 <p>LiveUser</p>
 <p>LiveUser_Admin</p>
 <p>Log</p>
 <p>Mail</p>
 <p>Mail_IMAP</p>
 <p>Mail_IMAPv2</p>
 <p>Mail_Mbox</p>
 <p>Mail_Mime</p>
 <p>Mail_mimeDecode</p>
 <p>Mail_Queue</p>
 <p>Math_Basex</p>
 <p>Math_BigInteger</p>
 <p>Math_BinaryUtils</p>
 <p>Math_Complex</p>
 <p>Math_Derivative</p>
 <p>Math_Fibonacci</p>
 <p>Math_Finance</p>
 <p>Math_Fraction</p>
 <p>Math_Histogram</p>
 <p>Math_Integer</p>
 <p>Math_Matrix</p>
 <p>Math_Numerical_RootFinding</p>
 <p>Math_Polynomial</p>
 <p>Math_Quaternion</p>
 <p>Math_RPN</p>
 <p>Math_Stats</p>
 <p>Math_TrigOp</p>
 <p>Math_Vector</p>
 <p>MDB</p>
 <p>MDB2</p>
 <p>MDB2_Driver_fbsql</p>
 <p>MDB2_Driver_ibase</p>
 <p>MDB2_Driver_mssql</p>
 <p>MDB2_Driver_mysql</p>
 <p>MDB2_Driver_mysqli</p>
 <p>MDB2_Driver_oci8</p>
 <p>MDB2_Driver_pgsql</p>
 <p>MDB2_Driver_querysim</p>
 <p>MDB2_Driver_sqlite</p>
 <p>MDB2_Schema</p>
 <p>MDB2_Table</p>
 <p>MDB_QueryTool</p>
 <p>Message</p>
 <p>MIME_Type</p>
 <p>MP3_Id</p>
 <p>MP3_IDv2</p>
 <p>MP3_Playlist</p>
 <p>Net_CDDB</p>
 <p>Net_CheckIP</p>
 <p>Net_Curl</p>
 <p>Net_Cyrus</p>
 <p>Net_Dict</p>
 <p>Net_Dig</p>
 <p>Net_DIME</p>
 <p>Net_DNS</p>
 <p>Net_DNSBL</p>
 <p>Net_Finger</p>
 <p>Net_FTP</p>
 <p>Net_FTP2</p>
 <p>Net_GameServerQuery</p>
 <p>Net_Geo</p>
 <p>Net_GeoIP</p>
 <p>Net_Growl</p>
 <p>Net_HL7</p>
 <p>Net_Ident</p>
 <p>Net_IDNA</p>
 <p>Net_IMAP</p>
 <p>Net_IPv4</p>
 <p>Net_IPv6</p>
 <p>Net_IRC</p>
 <p>Net_LDAP</p>
 <p>Net_LMTP</p>
 <p>Net_MAC</p>
 <p>Net_Monitor</p>
 <p>Net_MPD</p>
 <p>Net_NNTP</p>
 <p>Net_Ping</p>
 <p>Net_POP3</p>
 <p>Net_Portscan</p>
 <p>Net_Server</p>
 <p>Net_Sieve</p>
 <p>Net_SmartIRC</p>
 <p>Net_SMPP</p>
 <p>Net_SMPP_Client</p>
 <p>Net_SMS</p>
 <p>Net_SMTP</p>
 <p>Net_Socket</p>
 <p>Net_Traceroute</p>
 <p>Net_URL</p>
 <p>Net_UserAgent_Detect</p>
 <p>Net_UserAgent_Mobile</p>
 <p>Net_Whois</p>
 <p>Net_Wifi</p>
 <p>Numbers_Roman</p>
 <p>Numbers_Words</p>
 <p>OLE</p>
 <p>OpenDocument</p>
 <p>Pager</p>
 <p>Pager_Sliding</p>
 <p>Payment_Clieop</p>
 <p>Payment_DTA</p>
 <p>Payment_Process</p>
 <p>PEAR</p>
 <p>pearweb</p>
 <p>pearweb_channelxml</p>
 <p>pearweb_phars</p>
 <p>PEAR_Command_Packaging</p>
 <p>PEAR_Delegator</p>
 <p>PEAR_ErrorStack</p>
 <p>PEAR_Frontend_Gtk</p>
 <p>PEAR_Frontend_Gtk2</p>
 <p>PEAR_Frontend_Web</p>
 <p>PEAR_Info</p>
 <p>PEAR_PackageFileManager</p>
 <p>PEAR_PackageFileManager_Frontend</p>
 <p>PEAR_PackageFileManager_Frontend_Web</p>
 <p>PEAR_PackageFileManager_GUI_Gtk</p>
 <p>PEAR_PackageUpdate</p>
 <p>PEAR_PackageUpdate_Gtk2</p>
 <p>PEAR_PackageUpdate_Web</p>
 <p>PEAR_RemoteInstaller</p>
 <p>PHPDoc</p>
 <p>PhpDocumentor</p>
 <p>PHP_Annotation</p>
 <p>PHP_Archive</p>
 <p>PHP_Beautifier</p>
 <p>PHP_CodeSniffer</p>
 <p>PHP_Compat</p>
 <p>PHP_CompatInfo</p>
 <p>PHP_Fork</p>
 <p>PHP_LexerGenerator</p>
 <p>PHP_Parser</p>
 <p>PHP_ParserGenerator</p>
 <p>PHP_Parser_DocblockParser</p>
 <p>PHP_Shell</p>
 <p>QA_Peardoc_Coverage</p>
 <p>RDF</p>
 <p>RDF_N3</p>
 <p>RDF_NTriple</p>
 <p>RDF_RDQL</p>
 <p>Science_Chemistry</p>
 <p>ScriptReorganizer</p>
 <p>Search_Mnogosearch</p>
 <p>Services_Amazon</p>
 <p>Services_Blogging</p>
 <p>Services_Delicious</p>
 <p>Services_DynDNS</p>
 <p>Services_Ebay</p>
 <p>Services_ExchangeRates</p>
 <p>Services_Google</p>
 <p>Services_Hatena</p>
 <p>Services_OpenSearch</p>
 <p>Services_Pingback</p>
 <p>Services_Technorati</p>
 <p>Services_Trackback</p>
 <p>Services_W3C_HTMLValidator</p>
 <p>Services_Weather</p>
 <p>Services_Webservice</p>
 <p>Services_Yahoo</p>
 <p>Services_YouTube</p>
 <p>SOAP</p>
 <p>SOAP_Interop</p>
 <p>Spreadsheet_Excel_Writer</p>
 <p>SQL_Parser</p>
 <p>Stream_SHM</p>
 <p>Stream_Var</p>
 <p>Structures_BibTex</p>
 <p>Structures_DataGrid</p>
 <p>Structures_DataGrid_DataSource_Array</p>
 <p>Structures_DataGrid_DataSource_CSV</p>
 <p>Structures_DataGrid_DataSource_DataObject</p>
 <p>Structures_DataGrid_DataSource_DB</p>
 <p>Structures_DataGrid_DataSource_DBQuery</p>
 <p>Structures_DataGrid_DataSource_DBTable</p>
 <p>Structures_DataGrid_DataSource_Excel</p>
 <p>Structures_DataGrid_DataSource_MDB2</p>
 <p>Structures_DataGrid_DataSource_RSS</p>
 <p>Structures_DataGrid_DataSource_XML</p>
 <p>Structures_DataGrid_Renderer_Console</p>
 <p>Structures_DataGrid_Renderer_CSV</p>
 <p>Structures_DataGrid_Renderer_Flexy</p>
 <p>Structures_DataGrid_Renderer_HTMLSortForm</p>
 <p>Structures_DataGrid_Renderer_HTMLTable</p>
 <p>Structures_DataGrid_Renderer_Pager</p>
 <p>Structures_DataGrid_Renderer_Smarty</p>
 <p>Structures_DataGrid_Renderer_XLS</p>
 <p>Structures_DataGrid_Renderer_XML</p>
 <p>Structures_DataGrid_Renderer_XUL</p>
 <p>Structures_Form</p>
 <p>Structures_Form_Gtk2</p>
 <p>Structures_Graph</p>
 <p>Structures_LinkedList</p>
 <p>System_Command</p>
 <p>System_Folders</p>
 <p>System_Mount</p>
 <p>System_ProcWatch</p>
 <p>System_SharedMemory</p>
 <p>System_Socket</p>
 <p>System_WinDrives</p>
 <p>Testing_Selenium</p>
 <p>Text_CAPTCHA</p>
 <p>Text_CAPTCHA_Numeral</p>
 <p>Text_Diff</p>
 <p>Text_Figlet</p>
 <p>Text_Highlighter</p>
 <p>Text_Huffman</p>
 <p>Text_LanguageDetect</p>
 <p>Text_Password</p>
 <p>Text_PathNavigator</p>
 <p>Text_Statistics</p>
 <p>Text_TeXHyphen</p>
 <p>Text_Wiki</p>
 <p>Text_Wiki_BBCode</p>
 <p>Text_Wiki_Cowiki</p>
 <p>Text_Wiki_Creole</p>
 <p>Text_Wiki_Doku</p>
 <p>Text_Wiki_Mediawiki</p>
 <p>Text_Wiki_Tiki</p>
 <p>Translation</p>
 <p>Translation2</p>
 <p>Tree</p>
 <p>UDDI</p>
 <p>Validate</p>
 <p>Validate_AR</p>
 <p>Validate_AT</p>
 <p>Validate_AU</p>
 <p>Validate_BE</p>
 <p>Validate_CA</p>
 <p>Validate_CH</p>
 <p>Validate_DE</p>
 <p>Validate_DK</p>
 <p>Validate_ES</p>
 <p>Validate_FI</p>
 <p>Validate_Finance</p>
 <p>Validate_Finance_CreditCard</p>
 <p>Validate_FR</p>
 <p>Validate_IN</p>
 <p>Validate_IS</p>
 <p>Validate_ISPN</p>
 <p>Validate_IT</p>
 <p>Validate_LV</p>
 <p>Validate_NL</p>
 <p>Validate_NZ</p>
 <p>Validate_PL</p>
 <p>Validate_ptBR</p>
 <p>Validate_UK</p>
 <p>Validate_US</p>
 <p>Validate_ZA</p>
 <p>Var_Dump</p>
 <p>VersionControl_SVN</p>
 <p>VFS</p>
 <p>XML_Beautifier</p>
 <p>XML_CSSML</p>
 <p>XML_DB_eXist</p>
 <p>XML_DTD</p>
 <p>XML_FastCreate</p>
 <p>XML_Feed_Parser</p>
 <p>XML_fo2pdf</p>
 <p>XML_FOAF</p>
 <p>XML_HTMLSax</p>
 <p>XML_HTMLSax3</p>
 <p>XML_image2svg</p>
 <p>XML_Indexing</p>
 <p>XML_MXML</p>
 <p>XML_NITF</p>
 <p>XML_Parser</p>
 <p>XML_Query2XML</p>
 <p>XML_RDDL</p>
 <p>XML_RPC</p>
 <p>XML_RPC2</p>
 <p>XML_RSS</p>
 <p>XML_SaxFilters</p>
 <p>XML_Serializer</p>
 <p>XML_sql2xml</p>
 <p>XML_Statistics</p>
 <p>XML_SVG</p>
 <p>XML_svg2image</p>
 <p>XML_Transformer</p>
 <p>XML_Tree</p>
 <p>XML_Util</p>
 <p>XML_Wddx</p>
 <p>XML_XPath</p>
 <p>XML_XSLT_Wrapper</p>
 <p>XML_XUL</p>
</a>', 'text/xml');



$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setPEARVersion('1.4.8');
$_test_dep->setExtensions(array('xml' => 0, 'pcre' => 1));

$command->run('install', array(), array($dir . 'PEAR-1.4.8.tgz',
    $dir . 'Console_Getopt-1.2.tgz', $dir . 'Archive_Tar-1.3.1.tgz', $dir . 'XML_RPC-1.4.3.tgz'));
$phpunit->assertNoErrors('setup');
$phpunit->assertEquals(4, count($reg->listPackages()), 'num packages');

$fakelog->getLog();
$fakelog->getDownload();
unset($GLOBALS['__Stupid_php4_a']); // reset downloader

$command->run('upgrade', array(), array('PEAR-alpha'));
$phpunit->assertNoErrors('full test');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' => 'Nothing to upgrade',
    'cmd' => 'no command',
  ),
), $fakelog->getLog(), 'bug part');
$phpunit->assertEquals(array(), $fakelog->getDownload(), 'download bug part');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
