--TEST--
remote-info command (REST-based channel)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseUrl('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pearweb->addRESTConfig("http://pear.php.net/rest/p/archive_zip/info.xml", '<?xml version="1.0"?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>Archive_Zip</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Zip file management class</s>
 <d>This class provides handling of zip files in PHP.
It supports creating, listing, extracting and adding to zip files.</d>
 <r xlink:href="/rest/r/archive_zip"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_zip/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear/info.xml", '<?xml version="1.0"?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
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
 <r xlink:href="/rest/r/pear"/>
</p>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/allreleases.xml", '<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR</p>
 <c>pear.php.net</c>
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
 <r><v>0.91-dev</v><s>beta</s></r>
 <r><v>0.90</v><s>beta</s></r>
 <r><v>0.11</v><s>beta</s></r>
 <r><v>0.10</v><s>beta</s></r>
 <r><v>0.9</v><s>stable</s></r>
</a>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a11.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:7:"package";a:3:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:5:"1.3.1";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.2";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a11.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0a11</v>
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
 <da>2005-04-17 18:40:51</da>
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
  * remove &lt;max&gt; tag requirement for php dependency</n>
 <f>252733</f>
 <g>http://pear.php.net/get/PEAR-1.4.0a11</g>
 <x xlink:href="package.1.4.0a11.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a10.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}s:7:"package";a:3:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:5:"1.3.1";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.2";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a10.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0a10</v>
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
 <da>2005-04-06 00:35:33</da>
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
  * fix Bug #4072: pear list-all -c channel does not list installed packages</n>
 <f>250019</f>
 <g>http://pear.php.net/get/PEAR-1.4.0a10</g>
 <x xlink:href="package.1.4.0a10.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a9.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}s:7:"package";a:3:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:5:"1.3.1";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.2";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a9.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0a9</v>
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
 <da>2005-03-24 23:00:26</da>
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
    PEAR_PackageFile_v2_rw</n>
 <f>246544</f>
 <g>http://pear.php.net/get/PEAR-1.4.0a9</g>
 <x xlink:href="package.1.4.0a9.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a8.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}s:7:"package";a:3:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:5:"1.3.1";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.2";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a8.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0a8</v>
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
 <da>2005-03-21 11:54:03</da>
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
  * make package-validate command work on packaged .tgz files</n>
 <f>245373</f>
 <g>http://pear.php.net/get/PEAR-1.4.0a8</g>
 <x xlink:href="package.1.4.0a8.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a7.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}s:7:"package";a:3:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:5:"1.3.1";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.2";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a7.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0a7</v>
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
 <da>2005-03-17 22:09:34</da>
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

  Specific changes from 1.4.0a5
  * fix Bug #3860 PEAR_Dependency2 not included in 1 case

  Specific changes from 1.4.0a6
  * implement &lt;usesrole&gt;/&lt;usestask&gt; for custom role/task graceful failure
  * REALLY fix the debug_backtrace() issue (modified wrong pearcmd.php)
  * fix Bug #3864 Invalid dependency relation
  * fix Bug #3863 illogical warning about PEAR_Frontend_Gtk 0.4.0 with PEAR 1.4.0a6</n>
 <f>242221</f>
 <g>http://pear.php.net/get/PEAR-1.4.0a7</g>
 <x xlink:href="package.1.4.0a7.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a6.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}s:7:"package";a:3:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:3:"1.2";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.2";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a6.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0a6</v>
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
 <da>2005-03-17 10:11:11</da>
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

  Specific changes from 1.4.0a5
  * fix Bug #3860 PEAR_Dependency2 not included in 1 case</n>
 <f>245543</f>
 <g>http://pear.php.net/get/PEAR-1.4.0a6</g>
 <x xlink:href="package.1.4.0a6.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a5.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}s:7:"package";a:3:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:3:"1.2";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.2";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a5.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0a5</v>
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
 <da>2005-03-17 00:47:09</da>
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
  * upgrade suggested Archive_Tar version to 1.3.0
  * attempt to address memory issues
  * relax validation further
  * disable debug_backtrace() in PEAR_Error constructor of PEAR installer
  * fix a strange version number condition when two packages were upgraded at the same time.
  * fix Bug #3808 channel packages with non-baseinstalldir files will conflict on upgrade
  * fix Bug #3801 [PATCH] analyzeSourceCode() reports PHP4 code as PHP5
  * fix Bug #3671 Installing package features doesn\'t work as expected
  * implement Request #3717 [Patch] Implement Simple run-tests output</n>
 <f>245495</f>
 <g>http://pear.php.net/get/PEAR-1.4.0a5</g>
 <x xlink:href="package.1.4.0a5.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a4.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}s:7:"package";a:3:{i:0;a:4:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:3:"1.2";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.1";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a4.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0a4</v>
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
 <da>2005-03-03 08:17:19</da>
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
  * upgrade suggested XML_RPC version to 1.2.1</n>
 <f>243805</f>
 <g>http://pear.php.net/get/PEAR-1.4.0a4</g>
 <x xlink:href="package.1.4.0a4.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a3.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}s:7:"package";a:3:{i:0;a:4:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:3:"1.2";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.0";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a3.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0a3</v>
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
 <da>2005-03-02 22:32:24</da>
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
  * Fix Bug #3677: Post-Install script message needs to display channel name</n>
 <f>243679</f>
 <g>http://pear.php.net/get/PEAR-1.4.0a3</g>
 <x xlink:href="package.1.4.0a3.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a2.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}s:7:"package";a:3:{i:0;a:4:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:3:"1.2";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.0";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a2.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0a2</v>
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
 <da>2005-02-26 22:32:01</da>
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
  * Use 1.2.0 as recommended version of XML_RPC</n>
 <f>242974</f>
 <g>http://pear.php.net/get/PEAR-1.4.0a2</g>
 <x xlink:href="package.1.4.0a2.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a1.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}s:7:"package";a:3:{i:0;a:4:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:3:"1.2";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:8:"1.2.0RC7";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a1.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.4.0a1</v>
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
 <da>2005-02-26 18:52:10</da>
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
  * Everything above here that you just read</n>
 <f>243134</f>
 <g>http://pear.php.net/get/PEAR-1.4.0a1</g>
 <x xlink:href="package.1.4.0a1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.3.5.txt", 'a:6:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.2";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:14:"Console_Getopt";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:4:"name";s:7:"XML_RPC";}i:5;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"xml";}i:6;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"pcre";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.3.5.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.3.5</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception php5-only exception class
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
 * the PEAR base class

</d>
 <da>2005-02-18 00:38:04</da>
 <n>* fix Bug #3505: pecl can\'t install PDO
* enhance pear run-tests dramatically
* fix Bug #3506: pear install should export the pear version into the environment

</n>
 <f>108423</f>
 <g>http://pear.php.net/get/PEAR-1.3.5</g>
 <x xlink:href="package.1.3.5.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.3.4.txt", 'a:6:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.2";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:14:"Console_Getopt";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:4:"name";s:7:"XML_RPC";}i:5;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"xml";}i:6;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"pcre";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.3.4.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.3.4</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception php5-only exception class
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
 * the PEAR base class
</d>
 <da>2005-01-01 20:26:39</da>
 <n>* fix a serious problem caused by a bug in all versions of PHP that caused multiple registration
  of the shutdown function of PEAR.php
* fix Bug #2861: package.dtd does not define NUMBER
* fix Bug #2946: ini_set warning errors
* fix Bug #3026: Dependency type &quot;ne&quot; is needed, &quot;not&quot; is not handled
  properly
* fix Bug #3061: potential warnings in PEAR_Exception
* implement Request #2848: PEAR_ErrorStack logger extends, PEAR_ERRORSTACK_DIE
* implement Request #2914: Dynamic Include Path for run-tests command
* make pear help listing more useful (put how-to-use info at the bottom of the listing)
</n>
 <f>107207</f>
 <g>http://pear.php.net/get/PEAR-1.3.4</g>
 <x xlink:href="package.1.3.4.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.3.3.1.txt", 'a:6:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.2";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:14:"Console_Getopt";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:4:"name";s:7:"XML_RPC";}i:5;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"xml";}i:6;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"pcre";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.3.3.1.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.3.3.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception php5-only exception class
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
 * the PEAR base class
</d>
 <da>2004-11-12 02:04:57</da>
 <n>add RunTest.php to package.xml, make run-tests display failed tests, and use ui
</n>
 <f>106079</f>
 <g>http://pear.php.net/get/PEAR-1.3.3.1</g>
 <x xlink:href="package.1.3.3.1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.3.3.txt", 'a:6:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.2";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:14:"Console_Getopt";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:4:"name";s:7:"XML_RPC";}i:5;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"xml";}i:6;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"pcre";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.3.3.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.3.3</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception php5-only exception class
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
 * the PEAR base class
</d>
 <da>2004-10-28 13:40:34</da>
 <n>Installer:
 * fix Bug #1186 raise a notice error on PEAR::Common $_packageName
 * fix Bug #1249 display the right state when using --force option
 * fix Bug #2189 upgrade-all stops if dependancy fails
 * fix Bug #1637 The use of interface causes warnings when packaging with PEAR
 * fix Bug #1420 Parser bug for T_DOUBLE_COLON
 * fix Request #2220 pear5 build fails on dual php4/php5 system
 * fix Bug #1163  pear makerpm fails with packages that supply role=&quot;doc&quot;

Other:
 * add PEAR_Exception class for PHP5 users
 * fix critical problem in package.xml for linux in 1.3.2
 * fix staticPopCallback() in PEAR_ErrorStack
 * fix warning in PEAR_Registry for windows 98 users
</n>
 <f>103320</f>
 <g>http://pear.php.net/get/PEAR-1.3.3</g>
 <x xlink:href="package.1.3.3.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.3.1.txt", 'a:6:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.2";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:14:"Console_Getopt";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:4:"name";s:7:"XML_RPC";}i:5;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"xml";}i:6;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"pcre";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.3.1.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.3.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the alpha-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
</d>
 <da>2004-04-06 20:19:35</da>
 <n>PEAR Installer:

 * Bug #534  pear search doesn\'t list unstable releases
 * Bug #933  CMD Usability Patch
 * Bug #937  throwError() treats every call as static
 * Bug #964 PEAR_ERROR_EXCEPTION causes fatal error
 * Bug #1008 safe mode raises warning

PEAR_ErrorStack:

 * Added experimental error handling, designed to eventually replace
   PEAR_Error.  It should be considered experimental until explicitly marked
   stable.  require_once \'PEAR/ErrorStack.php\' to use.
</n>
 <f>95968</f>
 <g>http://pear.php.net/get/PEAR-1.3.1</g>
 <x xlink:href="package.1.3.1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.3.txt", 'a:6:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:14:"Console_Getopt";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:4:"name";s:7:"XML_RPC";}i:5;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"xml";}i:6;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"pcre";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.3.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.3</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>pajoye</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
</d>
 <da>2004-02-20 10:40:19</da>
 <n>PEAR Installer:

* Bug #171 --alldeps with a rel=&quot;eq&quot; should install the required version, if possible
* Bug #249 installing from an url doesnt work
* Bug #248 --force command does not work as expected
* Bug #293 [Patch] PEAR_Error not calling static method callbacks for error-handler
* Bug #324 pear -G gives Fatal Error (PHP-GTK not installed, but error is at engine level)
* Bug #594 PEAR_Common::analyzeSourceCode fails on string with $var and {
* Bug #521 Incorrect filename in naming warnings
* Moved download code into its own class
* Fully unit tested the installer, packager, downloader, and PEAR_Common
</n>
 <f>89121</f>
 <g>http://pear.php.net/get/PEAR-1.3</g>
 <x xlink:href="package.1.3.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.3b6.txt", 'a:6:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:14:"Console_Getopt";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:4:"name";s:7:"XML_RPC";}i:5;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"xml";}i:6;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"pcre";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.3b6.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.3b6</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>pajoye</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
</d>
 <da>2004-01-25 20:57:03</da>
 <n>PEAR Installer:

* Bug #171 --alldeps with a rel=&quot;eq&quot; should install the required version, if possible
* Bug #249 installing from an url doesnt work
* Bug #248 --force command does not work as expected
* Bug #293 [Patch] PEAR_Error not calling static method callbacks for error-handler
* Bug #324 pear -G gives Fatal Error (PHP-GTK not installed, but error is at engine level)
* Bug #594 PEAR_Common::analyzeSourceCode fails on string with $var and {
* Bug #521 Incorrect filename in naming warnings
* Moved download code into its own class
* Fully unit tested the installer, packager, downloader, and PEAR_Common
</n>
 <f>88719</f>
 <g>http://pear.php.net/get/PEAR-1.3b6</g>
 <x xlink:href="package.1.3b6.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.3b5.txt", 'a:6:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.2";s:4:"name";s:14:"Console_Getopt";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:4:"name";s:7:"XML_RPC";}i:5;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:3:"xml";}i:6;a:3:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:4:"name";s:4:"pcre";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.3b5.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.3b5</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>pajoye</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
</d>
 <da>2003-12-19 09:44:01</da>
 <n>PEAR Installer:

* Bug #171 --alldeps with a rel=&quot;eq&quot; should install the required version, if possible
* Bug #249 installing from an url doesnt work
* Bug #248 --force command does not work as expected
* Bug #293 [Patch] PEAR_Error not calling static method callbacks for error-handler
* Bug #324 pear -G gives Fatal Error (PHP-GTK not installed, but error is at engine level)
* Moved download code into its own class
* Fully unit tested the installer, packager, downloader, and PEAR_Common
</n>
 <f>88846</f>
 <g>http://pear.php.net/get/PEAR-1.3b5</g>
 <x xlink:href="package.1.3b5.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.3b3.txt", 'a:5:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:14:"Console_Getopt";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:4:"name";s:7:"XML_RPC";}i:5;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:6:"xmlrpc";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.3b3.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.3b3</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>cox</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
</d>
 <da>2003-10-20 16:02:00</da>
 <n>PEAR Installer:

* Bug #25413 Add local installed packages to list-all (Christian DickMann)
* Bug #23221 Pear installer - extension re-install segfault
* Better error detecting and reporting in &quot;install/upgrade&quot;
* Various other bugfixes and cleanups
</n>
 <f>86146</f>
 <g>http://pear.php.net/get/PEAR-1.3b3</g>
 <x xlink:href="package.1.3b3.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.3b2.txt", 'a:5:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.1";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"1.0";s:4:"name";s:14:"Console_Getopt";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:4:"name";s:7:"XML_RPC";}i:5;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:6:"xmlrpc";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.3b2.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.3b2</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>cox</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
</d>
 <da>2003-10-02 11:53:00</da>
 <n>PEAR Installer:

* Updated deps for Archive_Tar and Console_Getopt
* Fixed #45 preferred_state works incorrectly
* Fixed optional dependency attrib removed from
  package.xml, making them a requirement
</n>
 <f>86107</f>
 <g>http://pear.php.net/get/PEAR-1.3b2</g>
 <x xlink:href="package.1.3b2.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.3b1.txt", 'a:5:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}i:4;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:5:"1.0.4";s:4:"name";s:7:"XML_RPC";}i:5;a:4:{s:4:"type";s:3:"ext";s:3:"rel";s:3:"has";s:8:"optional";s:3:"yes";s:4:"name";s:6:"xmlrpc";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.3b1.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.3b1</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>cox</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2003-09-29 13:19:00</da>
 <n>PEAR Base Class:

* Fixed static calls to PEAR error-handling methods in classes
* Added ability to use a static method callback for error-handling,
  and removed use of inadvisable @ in setErrorHandling

PEAR Installer:

* Fixed #25117 - MD5 checksum should be case-insensitive
* Added dependency on XML_RPC, and optional dependency on xmlrpc extension
* Added --alldeps and --onlyreqdeps options to pear install/pear upgrade
* Sorting of installation/uninstallation so package order on the command-line is
  insignificant (fixes upgrade-all if every package is installed)
* pear upgrade will now install if the package is not installed (necessary for
  pear upgrade --alldeps, as installation is often necessary for new
  dependencies)
* fixed pear.bat if PHP is installed in a path like C:\\Program Files\\php
* Added ability to specify &quot;pear install package-version&quot; or
  &quot;pear install package-state&quot;. For example: &quot;pear install DB-1.2&quot;,
  or &quot;pear install DB-stable&quot;
* Fix #25008 - unhelpful error message
* Fixed optional dependencies in Dependency.php
* Fix #25322 - bad md5sum should be fatal error
* Package uninstall now also removes empty directories
* Fixed locking problems for reading commands (pear list, pear info)

OS_Guess Class:

* Fixed #25131 - OS_Guess warnings on empty lines from
  popen(&quot;/usr/bin/cpp $tmpfile&quot;, &quot;r&quot;);

System Class:

* Fixed recursion deep param in _dirToStruct()
* Added the System::find() command (read API doc for more info)
</n>
 <f>86292</f>
 <g>http://pear.php.net/get/PEAR-1.3b1</g>
 <x xlink:href="package.1.3b1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.2.1.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.2.1.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.2.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>pajoye</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2003-08-15 13:48:00</da>
 <n>- Set back the default library path (BC issues)
</n>
 <f>83126</f>
 <g>http://pear.php.net/get/PEAR-1.2.1</g>
 <x xlink:href="package.1.2.1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.2.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.2.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.2</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cox</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2003-08-13 22:35:00</da>
 <n>Changes from 1.1:

* Changed license from PHP 2.02 to 3.0
* Added support for optional dependencies
* Made upgrade and uninstall package case insensitive
* pear makerpm, now works and generates a better system independent spec file
* pear install|build pecl-package, now exposes the compilation progress
* Installer now checks dependencies on package uninstall
* Added proxy support for remote commands using the xmlrcp C ext (Adam Ashley)
* Added the command &quot;download-all&quot; (Alex Merz)
* Made package dependency checking back to work
* Added support for spaces in path names (Greg)
* Various bugfixes
* Added new pear &quot;bundle&quot; command, which downloads and uncompress a PECL package.
The main purpouse of this command is for easily adding extensions to the PHP sources
before compiling it.
</n>
 <f>83109</f>
 <g>http://pear.php.net/get/PEAR-1.2</g>
 <x xlink:href="package.1.2.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.2b5.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.2b5.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.2b5</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>cox</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2003-08-05 16:32:00</da>
 <n>* Changed license from PHP 2.02 to 3.0
* Added support for optional dependencies
* Made upgrade and uninstall package case insensitive
* pear makerpm, now works and generates a better system independent spec file
* pear install|build pecl-package, now exposes the compilation progress
* Installer now checks dependencies on package uninstall
* Added proxy support for remote commands using the xmlrcp C ext (Adam Ashley)
* Added the command &quot;download-all&quot; (Alex Merz)
* Made package dependency checking back to work
* Added support for spaces in path names (Greg)
* Various bugfixes
* Added new pear &quot;bundle&quot; command, which downloads and uncompress a PECL package.
The main purpouse of this command is for easily adding extensions to the PHP sources
before compiling it.
</n>
 <f>83270</f>
 <g>http://pear.php.net/get/PEAR-1.2b5</g>
 <x xlink:href="package.1.2b5.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.2b4.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.2b4.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.2b4</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>cox</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2003-08-05 03:26:00</da>
 <n>* Changed license from PHP 2.02 to 3.0
* Added support for optional dependencies
* Made upgrade and uninstall package case insensitive
* pear makerpm, now works and generates a better system independent spec file
* pear install|build pecl-package, now exposes the compilation progress
* Installer now checks dependencies on package uninstall
* Added proxy support for remote commands using the xmlrcp C ext (Adam Ashley)
* Added the command &quot;download-all&quot; (Alex Merz)
* Made package dependency checking back to work
* Added support for spaces in path names (Greg)
* Various bugfixes
* Added new pear &quot;bundle&quot; command, which downloads and uncompress a PECL package.
The main purpouse of this command is for easily adding extensions to the PHP sources
before compiling it.
</n>
 <f>83170</f>
 <g>http://pear.php.net/get/PEAR-1.2b4</g>
 <x xlink:href="package.1.2b4.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.2b3.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.2b3.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.2b3</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>cox</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2003-08-03 19:45:00</da>
 <n>* Changed license from PHP 2.02 to 3.0
* Added support for optional dependencies
* Made upgrade and uninstall package case insensitive
* pear makerpm, now works and generates a better system independent spec file
* pear install|build pecl-package, now exposes the compilation progress
* Installer now checks dependencies on package uninstall
* Added proxy support for remote commands using the xmlrcp C ext (Adam Ashley)
* Added the command &quot;download-all&quot; (Alex Merz)
* Made package dependency checking back to work
* Added support for spaces in path names (Greg)
* Added new pear &quot;bundle&quot; command, which downloads and uncompress a PECL package.
The main purpouse of this command is for easily adding extensions to the PHP sources
before compiling it.
</n>
 <f>83093</f>
 <g>http://pear.php.net/get/PEAR-1.2b3</g>
 <x xlink:href="package.1.2b3.xml"/>
</r>', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.2b2.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.2b2.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.2b2</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>cox</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2003-06-23 13:33:00</da>
 <n>* Changed license from PHP 2.02 to 3.0
* Added support for optional dependencies
* Made upgrade and uninstall package case insensitive
* pear makerpm, now works and generates a better system independent spec file
* pear install|build &lt;pecl-package&gt;, now exposes the compilation progress
* Added new pear bundle command, which downloads and uncompress a &lt;pecl-package&gt;.
The main purpouse of this command is for easily adding extensions to the PHP sources
before compiling it.
</n>
 <f>82854</f>
 <g>http://pear.php.net/get/PEAR-1.2b2</g>
 <x xlink:href="package.1.2b2.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.2b1.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.2b1.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.2b1</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>cox</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2003-06-23 10:07:00</da>
 <n>* Changed license from PHP 2.02 to 3.0
* Added support for optional dependencies
* pear makerpm, now works and generates a better system independent spec file
* pear install|build &lt;pecl-package&gt;, now exposes the compilation progress
* Added new pear bundle command, which downloads and uncompress a &lt;pecl-package&gt;.
The main purpouse of this command is for easily adding extensions to the PHP sources
before compiling it.
</n>
 <f>82861</f>
 <g>http://pear.php.net/get/PEAR-1.2b1</g>
 <x xlink:href="package.1.2b1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.1.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.1.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>ssb</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2003-05-10 23:27:00</da>
 <n>PEAR BASE CLASS:

* PEAR_Error now supports exceptions when using Zend Engine 2.  Set the
  error mode to PEAR_ERROR_EXCEPTION to make PEAR_Error throw itself
  as an exception (invoke PEAR errors with raiseError() or throwError()
  just like before).

PEAR INSTALLER:

* Packaging and validation now parses PHP source code (unless
  ext/tokenizer is disabled) and does some coding standard conformance
  checks.  Specifically, the names of classes and functions are
  checked to ensure that they are prefixed with the package name.  If
  your package has symbols that should be without this prefix, you can
  override this warning by explicitly adding a &quot;provides&quot; entry in
  your package.xml file.  See the package.xml file for this release
  for an example (OS_Guess, System and md5_file).

  All classes and non-private (not underscore-prefixed) methods and
  functions are now registered during &quot;pear package&quot;.
</n>
 <f>79666</f>
 <g>http://pear.php.net/get/PEAR-1.1</g>
 <x xlink:href="package.1.1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.0.1.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.0.1.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.0.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>ssb</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2003-01-10 01:26:00</da>
 <n>* PEAR_Error class has call backtrace available by
  calling getBacktrace().  Available if used with
  PHP 4.3 or newer.

* PEAR_Config class uses getenv() rather than $_ENV
  to read environment variables.

* System::which() Windows fix, now looks for
  exe/bat/cmd/com suffixes rather than just exe

* Added &quot;pear cvsdiff&quot; command

* Windows output buffering bugfix for &quot;pear&quot; command
</n>
 <f>75828</f>
 <g>http://pear.php.net/get/PEAR-1.0.1</g>
 <x xlink:href="package.1.0.1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.0.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.0.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.0</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>ssb</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2002-12-27 19:37:00</da>
 <n>* set default cache_ttl to 1 hour
* added &quot;clear-cache&quot; command
</n>
 <f>74736</f>
 <g>http://pear.php.net/get/PEAR-1.0</g>
 <x xlink:href="package.1.0.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.0b3.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.0b3.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.0b3</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>ssb</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2002-12-13 02:24:00</da>
 <n>* fixed &quot;info&quot; shortcut (conflicted with &quot;install&quot;)
* added &quot;php_bin&quot; config parameter
* all &quot;non-personal&quot; config parameters now use
  environment variables for defaults (very useful
  to override the default php_dir on Windows!)
</n>
 <f>74348</f>
 <g>http://pear.php.net/get/PEAR-1.0b3</g>
 <x xlink:href="package.1.0b3.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.0b2.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.0b2.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.0b2</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>ssb</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2002-11-26 01:43:00</da>
 <n>Changes, Installer:
* --force option no longer ignores errors, use
  --ignore-errors instead
* installer transactions: failed installs abort
  cleanly, without leaving half-installed packages
  around
</n>
 <f>73578</f>
 <g>http://pear.php.net/get/PEAR-1.0b2</g>
 <x xlink:href="package.1.0b2.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.0b1.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.0b1.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>1.0b1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>ssb</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2002-10-12 14:21:00</da>
 <n>New Features, Installer:
* new command: &quot;pear makerpm&quot;
* new command: &quot;pear search&quot;
* new command: &quot;pear upgrade-all&quot;
* new command: &quot;pear config-help&quot;
* new command: &quot;pear sign&quot;
* Windows support for &quot;pear build&quot; (requires
  msdev)
* new dependency type: &quot;zend&quot;
* XML-RPC results may now be cached (see
  cache_dir and cache_ttl config)
* HTTP proxy authorization support
* install/upgrade install-root support

Bugfixes, Installer:
* fix for XML-RPC bug that made some remote
  commands fail
* fix problems under Windows with
  DIRECTORY_SEPARATOR
* lots of other minor fixes
* --force option did not work for &quot;pear install
  Package&quot;
* http downloader used &quot;4.2.1&quot; rather than
  &quot;PHP/4.2.1&quot; as user agent
* bending over a little more to figure out how
  PHP is installed
* &quot;platform&quot; file attribute was not included
  during &quot;pear package&quot;

New Features, PEAR Library:
* added PEAR::loadExtension($ext)
* added PEAR::delExpect()
* System::mkTemp() now cleans up at shutdown
* defined PEAR_ZE2 constant (boolean)
* added PEAR::throwError() with a simpler API
  than raiseError()

Bugfixes, PEAR Library:
* ZE2 compatibility fixes
* use getenv() as fallback for $_ENV
</n>
 <f>71486</f>
 <g>http://pear.php.net/get/PEAR-1.0b1</g>
 <x xlink:href="package.1.0b1.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.0.91-dev.txt", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.0.90.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/0.90.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>0.90</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>ssb</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2002-06-06 11:34:00</da>
 <n>* fix: &quot;help&quot; command was broken
* new command: &quot;info&quot;
* new command: &quot;config-help&quot;
* un-indent multi-line data from xml description files
* new command: &quot;build&quot;
* fix: config-set did not work with &quot;set&quot; parameters
* disable magic_quotes_runtime
* &quot;install&quot; now builds and installs C extensions
* added PEAR::delExpect()
* System class no longer inherits PEAR
* grouped PEAR_Config parameters
* add --nobuild option to install/upgrade commands
* new and more generic Frontend API
</n>
 <f>62396</f>
 <g>http://pear.php.net/get/PEAR-0.90</g>
 <x xlink:href="package.0.90.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.0.11.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/0.11.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>0.11</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>ssb</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2002-05-28 01:24:00</da>
 <n>* fix: &quot;help&quot; command was broken
* new command: &quot;info&quot;
* new command: &quot;config-help&quot;
* un-indent multi-line data from xml description files
* new command: &quot;build&quot;
* fix: config-set did not work with &quot;set&quot; parameters
* disable magic_quotes_runtime
</n>
 <f>57738</f>
 <g>http://pear.php.net/get/PEAR-0.11</g>
 <x xlink:href="package.0.11.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.0.10.txt", 'a:3:{i:1;a:3:{s:4:"type";s:3:"php";s:3:"rel";s:2:"ge";s:7:"version";s:3:"4.1";}i:2;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:7:"version";s:3:"0.4";s:4:"name";s:11:"Archive_Tar";}i:3;a:4:{s:4:"type";s:3:"pkg";s:3:"rel";s:2:"ge";s:7:"version";s:4:"0.11";s:4:"name";s:14:"Console_Getopt";}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/0.10.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>0.10</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>ssb</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
</d>
 <da>2002-05-26 12:55:00</da>
 <n>Lots of stuff this time.  0.9 was not actually self-hosting, even
though it claimed to be.  This version finally is self-hosting
(really!), meaning you can upgrade the installer with the command
&quot;pear upgrade PEAR&quot;.

* new config paramers: http_proxy and umask
* HTTP proxy support when downloading packages
* generalized command handling code
* and fixed the bug that would not let commands have the
  same options as &quot;pear&quot; itself
* added long options to every command
* added command shortcuts (&quot;pear help shortcuts&quot;)
* added stub for Gtk installer
* some phpdoc fixes
* added class dependency detector (using ext/tokenizer)
* dependency handling fixes
* added OS_Guess class for detecting OS
* install files with the &quot;platform&quot; attribute set
  only on matching operating systems
* PEAR_Remote now falls back to the XML_RPC package
  if xmlrpc-epi is not available
* renamed command: package-list -&gt; list
* new command: package-dependencies
* lots of minor fixes
</n>
 <f>54699</f>
 <g>http://pear.php.net/get/PEAR-0.10</g>
 <x xlink:href="package.0.10.xml"/>
</r>', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.0.9.txt", 'b:0;', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/0.9.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear">PEAR</p>
 <c>pear.php.net</c>
 <v>0.9</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>ssb</m>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR command-line toolkit, for creating, distributing
   and installing packages
</d>
 <da>2002-04-13 01:04:00</da>
 <n>First package release.  Commands implemented:
   remote-package-info
   list-upgrades
   list-remote-packages
   download
   config-show
   config-get
   config-set
   list-installed
   shell-test
   install
   uninstall
   upgrade
   package
   package-list
   package-info
   login
   logout
</n>
 <f>39994</f>
 <g>http://pear.php.net/get/PEAR-0.9</g>
 <x xlink:href="package.0.9.xml"/>
</r>', 'text/xml');
$e = $command->run('remote-info', array(), array('Archive_Zip'));
$phpunit->assertNoErrors('Archive_Zip');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' =>
    array (
      'name' => 'Archive_Zip',
      'channel' => 'pear.php.net',
      'category' => 'File Formats',
      'stable' => '',
      'license' => 'PHP License',
      'summary' => 'Zip file management class',
      'description' => 'This class provides handling of zip files in PHP.
It supports creating, listing, extracting and adding to zip files.',
      'releases' =>
      array (
      ),
      'deprecated' => false,
      'installed' => '- no -',
    ),
    'cmd' => 'remote-info',
  ),
), $fakelog->getLog(), 'Archive_Zip log');
$e = $command->run('remote-info', array(), array('PEAR'));
$phpunit->assertNoErrors('PEAR');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' =>
    array (
      'name' => 'PEAR',
      'channel' => 'pear.php.net',
      'category' => 'PEAR',
      'stable' => '1.4.0a11',
      'license' => 'PHP License',
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
      'releases' =>
      array (
        '1.4.0a11' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
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
          'releasedate' => '2005-04-17 18:40:51',
          'releasenotes' => 'This is a major milestone release for PEAR.  In addition to several killer features,
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
 "pear channel-update pecl.php.net" prior to packaging
 a pecl release.
  * Fix package.xml version 2.0 generation from package.xml 1.0
  * Fix Bug #3634: still too many pear-specific restrictions on package valid
  * Implement Request #3647: "pear package" only includes one package.xml
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
  * implement <usesrole>/<usestask> for custom role/task graceful failure
  * REALLY fix the debug_backtrace() issue (modified wrong pearcmd.php)
  * fix Bug #3864 Invalid dependency relation
  * fix Bug #3863 illogical warning about PEAR_Frontend_Gtk 0.4.0 with PEAR 1.4.0a6

  Specific changes from 1.4.0a7:
  * greatly improve the flexibility of post-install scripts
    - <param> is no longer required
    - skipParamgroup() method in Frontends allows dynamic manipulation of what input is
      requested from users
  * make error message when a user has no write access absolutely clear and unmistakable
  * update to new header comment block standard
  * slight improvement to speed and possibly memory use of Installer when a lot of
    package.xml version 1.0 packages are installed
  * add "peardev" command for those with memory_limit issue
  * make package-validate command work on packaged .tgz files

  Specific changes from 1.4.0a8:
  * add --package option to run-tests command, to run installed .phpt tests
  * significantly drop pear\'s memory footprint for all commands
  * fix fatal errors when installing pecl packages
  * make download command work for non-root in a shared environment
  * make sure that if 1.4.0a8 (alpha) is installed, and 1.3.6 (newer) exists, pear will not
    attempt to "upgrade" to 1.3.6
  * split PEAR_PackageFile_v2 into two classes, read-only PEAR_PackageFile_v2, and read-write
    PEAR_PackageFile_v2_rw

  Specific changes from 1.4.0a9:
  * add support for writeable tasks
  * fix potential fatal errors in run-tests command, -p option
  * fix --installroot option for installation
  * move run-tests command into its own file (testing may expand)
  * fix fatal error if package.xml has no version="X.0"
  * fix Bug #3966: Improper path in PEAR/PackageFile/v2.php
  * fix Bug #3990: PEAR_Error PEAR_EXCEPTION broken
  * fix Bug #4021: PEAR_Config file_exists can cause warnings
  * fix Bug #1870: pear makerpm dependancies
  * fix Bug #4038: Array to string conversion in PEAR/Frontend/CLI.php
  * fix Bug #4060: pear upgrade Auth_HTTP fails
  * fix Bug #4072: pear list-all -c channel does not list installed packages

  Specific changes from 1.4.0a10:
  * Add new "unusualbaseinstall" role type that allows custom roles similar
    data/test/doc to honor the baseinstalldir attribute
  * fix Bug #4095: System::rm does not handle links correctly
  * fix Bug #4097: Wrong logging in PEAR_Command_Test
  * make pear/pecl commands list only pear/pecl packages
  * fix Bug #4161: pear download always leaves a package.xml in the dir
  * make PEAR_Remote messages more helpful (include server name)
  * make list-upgrades only search channels from which we have installed packages
  * remove <max> tag requirement for php dependency',
          'state' => 'alpha',
          'deps' =>
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
              'version' => '1.4.0a1',
              'optional' => 'no',
            ),
            3 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'Archive_Tar',
              'rel' => 'ge',
              'version' => '1.1',
              'optional' => 'no',
            ),
            4 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'Console_Getopt',
              'rel' => 'ge',
              'version' => '1.2',
              'optional' => 'no',
            ),
            5 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'XML_RPC',
              'rel' => 'ge',
              'version' => '1.2.0RC1',
              'optional' => 'no',
            ),
            6 =>
            array (
              'type' => 'ext',
              'name' => 'xml',
              'rel' => 'has',
              'optional' => 'no',
            ),
            7 =>
            array (
              'type' => 'ext',
              'name' => 'pcre',
              'rel' => 'has',
              'optional' => 'no',
            ),
            8 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Web',
              'rel' => 'ge',
              'version' => '0.5.0',
              'optional' => 'yes',
            ),
            9 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Gtk',
              'rel' => 'ge',
              'version' => '0.4.0',
              'optional' => 'yes',
            ),
          ),
        ),
        '1.4.0a10' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
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
          'releasedate' => '2005-04-06 00:35:33',
          'releasenotes' => 'This is a major milestone release for PEAR.  In addition to several killer features,
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
 "pear channel-update pecl.php.net" prior to packaging
 a pecl release.
  * Fix package.xml version 2.0 generation from package.xml 1.0
  * Fix Bug #3634: still too many pear-specific restrictions on package valid
  * Implement Request #3647: "pear package" only includes one package.xml
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
  * implement <usesrole>/<usestask> for custom role/task graceful failure
  * REALLY fix the debug_backtrace() issue (modified wrong pearcmd.php)
  * fix Bug #3864 Invalid dependency relation
  * fix Bug #3863 illogical warning about PEAR_Frontend_Gtk 0.4.0 with PEAR 1.4.0a6

  Specific changes from 1.4.0a7:
  * greatly improve the flexibility of post-install scripts
    - <param> is no longer required
    - skipParamgroup() method in Frontends allows dynamic manipulation of what input is
      requested from users
  * make error message when a user has no write access absolutely clear and unmistakable
  * update to new header comment block standard
  * slight improvement to speed and possibly memory use of Installer when a lot of
    package.xml version 1.0 packages are installed
  * add "peardev" command for those with memory_limit issue
  * make package-validate command work on packaged .tgz files

  Specific changes from 1.4.0a8:
  * add --package option to run-tests command, to run installed .phpt tests
  * significantly drop pear\'s memory footprint for all commands
  * fix fatal errors when installing pecl packages
  * make download command work for non-root in a shared environment
  * make sure that if 1.4.0a8 (alpha) is installed, and 1.3.6 (newer) exists, pear will not
    attempt to "upgrade" to 1.3.6
  * split PEAR_PackageFile_v2 into two classes, read-only PEAR_PackageFile_v2, and read-write
    PEAR_PackageFile_v2_rw

  Specific changes from 1.4.0a9:
  * add support for writeable tasks
  * fix potential fatal errors in run-tests command, -p option
  * fix --installroot option for installation
  * move run-tests command into its own file (testing may expand)
  * fix fatal error if package.xml has no version="X.0"
  * fix Bug #3966: Improper path in PEAR/PackageFile/v2.php
  * fix Bug #3990: PEAR_Error PEAR_EXCEPTION broken
  * fix Bug #4021: PEAR_Config file_exists can cause warnings
  * fix Bug #1870: pear makerpm dependancies
  * fix Bug #4038: Array to string conversion in PEAR/Frontend/CLI.php
  * fix Bug #4060: pear upgrade Auth_HTTP fails
  * fix Bug #4072: pear list-all -c channel does not list installed packages',
          'state' => 'alpha',
          'deps' =>
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
              'channel' => 'pear.php.net',
              'name' => 'Archive_Tar',
              'rel' => 'ge',
              'version' => '1.1',
              'optional' => 'no',
            ),
            4 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'Console_Getopt',
              'rel' => 'ge',
              'version' => '1.2',
              'optional' => 'no',
            ),
            5 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'XML_RPC',
              'rel' => 'ge',
              'version' => '1.2.0RC1',
              'optional' => 'no',
            ),
            6 =>
            array (
              'type' => 'ext',
              'name' => 'xml',
              'rel' => 'has',
              'optional' => 'no',
            ),
            7 =>
            array (
              'type' => 'ext',
              'name' => 'pcre',
              'rel' => 'has',
              'optional' => 'no',
            ),
            8 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Web',
              'rel' => 'ge',
              'version' => '0.5.0',
              'optional' => 'yes',
            ),
            9 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Gtk',
              'rel' => 'ge',
              'version' => '0.4.0',
              'optional' => 'yes',
            ),
          ),
        ),
        '1.4.0a9' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
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
          'releasedate' => '2005-03-24 23:00:26',
          'releasenotes' => 'This is a major milestone release for PEAR.  In addition to several killer features,
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
 "pear channel-update pecl.php.net" prior to packaging
 a pecl release.
  * Fix package.xml version 2.0 generation from package.xml 1.0
  * Fix Bug #3634: still too many pear-specific restrictions on package valid
  * Implement Request #3647: "pear package" only includes one package.xml
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
  * implement <usesrole>/<usestask> for custom role/task graceful failure
  * REALLY fix the debug_backtrace() issue (modified wrong pearcmd.php)
  * fix Bug #3864 Invalid dependency relation
  * fix Bug #3863 illogical warning about PEAR_Frontend_Gtk 0.4.0 with PEAR 1.4.0a6

  Specific changes from 1.4.0a7:
  * greatly improve the flexibility of post-install scripts
    - <param> is no longer required
    - skipParamgroup() method in Frontends allows dynamic manipulation of what input is
      requested from users
  * make error message when a user has no write access absolutely clear and unmistakable
  * update to new header comment block standard
  * slight improvement to speed and possibly memory use of Installer when a lot of
    package.xml version 1.0 packages are installed
  * add "peardev" command for those with memory_limit issue
  * make package-validate command work on packaged .tgz files

  Specific changes from 1.4.0a8:
  * add --package option to run-tests command, to run installed .phpt tests
  * significantly drop pear\'s memory footprint for all commands
  * fix fatal errors when installing pecl packages
  * make download command work for non-root in a shared environment
  * make sure that if 1.4.0a8 (alpha) is installed, and 1.3.6 (newer) exists, pear will not
    attempt to "upgrade" to 1.3.6
  * split PEAR_PackageFile_v2 into two classes, read-only PEAR_PackageFile_v2, and read-write
    PEAR_PackageFile_v2_rw',
          'state' => 'alpha',
          'deps' =>
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
              'channel' => 'pear.php.net',
              'name' => 'Archive_Tar',
              'rel' => 'ge',
              'version' => '1.1',
              'optional' => 'no',
            ),
            4 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'Console_Getopt',
              'rel' => 'ge',
              'version' => '1.2',
              'optional' => 'no',
            ),
            5 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'XML_RPC',
              'rel' => 'ge',
              'version' => '1.2.0RC1',
              'optional' => 'no',
            ),
            6 =>
            array (
              'type' => 'ext',
              'name' => 'xml',
              'rel' => 'has',
              'optional' => 'no',
            ),
            7 =>
            array (
              'type' => 'ext',
              'name' => 'pcre',
              'rel' => 'has',
              'optional' => 'no',
            ),
            8 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Web',
              'rel' => 'ge',
              'version' => '0.5.0',
              'optional' => 'yes',
            ),
            9 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Gtk',
              'rel' => 'ge',
              'version' => '0.4.0',
              'optional' => 'yes',
            ),
          ),
        ),
        '1.4.0a8' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
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
          'releasedate' => '2005-03-21 11:54:03',
          'releasenotes' => 'This is a major milestone release for PEAR.  In addition to several killer features,
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
 "pear channel-update pecl.php.net" prior to packaging
 a pecl release.
  * Fix package.xml version 2.0 generation from package.xml 1.0
  * Fix Bug #3634: still too many pear-specific restrictions on package valid
  * Implement Request #3647: "pear package" only includes one package.xml
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
  * implement <usesrole>/<usestask> for custom role/task graceful failure
  * REALLY fix the debug_backtrace() issue (modified wrong pearcmd.php)
  * fix Bug #3864 Invalid dependency relation
  * fix Bug #3863 illogical warning about PEAR_Frontend_Gtk 0.4.0 with PEAR 1.4.0a6

  Specific changes from 1.4.0a7:
  * greatly improve the flexibility of post-install scripts
    - <param> is no longer required
    - skipParamgroup() method in Frontends allows dynamic manipulation of what input is
      requested from users
  * make error message when a user has no write access absolutely clear and unmistakable
  * update to new header comment block standard
  * slight improvement to speed and possibly memory use of Installer when a lot of
    package.xml version 1.0 packages are installed
  * add "peardev" command for those with memory_limit issue
  * make package-validate command work on packaged .tgz files',
          'state' => 'alpha',
          'deps' =>
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
              'channel' => 'pear.php.net',
              'name' => 'Archive_Tar',
              'rel' => 'ge',
              'version' => '1.1',
              'optional' => 'no',
            ),
            4 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'Console_Getopt',
              'rel' => 'ge',
              'version' => '1.2',
              'optional' => 'no',
            ),
            5 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'XML_RPC',
              'rel' => 'ge',
              'version' => '1.2.0RC1',
              'optional' => 'no',
            ),
            6 =>
            array (
              'type' => 'ext',
              'name' => 'xml',
              'rel' => 'has',
              'optional' => 'no',
            ),
            7 =>
            array (
              'type' => 'ext',
              'name' => 'pcre',
              'rel' => 'has',
              'optional' => 'no',
            ),
            8 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Web',
              'rel' => 'ge',
              'version' => '0.5.0',
              'optional' => 'yes',
            ),
            9 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Gtk',
              'rel' => 'ge',
              'version' => '0.4.0',
              'optional' => 'yes',
            ),
          ),
        ),
        '1.4.0a7' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
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
          'releasedate' => '2005-03-17 22:09:34',
          'releasenotes' => 'This is a major milestone release for PEAR.  In addition to several killer features,
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
 "pear channel-update pecl.php.net" prior to packaging
 a pecl release.
  * Fix package.xml version 2.0 generation from package.xml 1.0
  * Fix Bug #3634: still too many pear-specific restrictions on package valid
  * Implement Request #3647: "pear package" only includes one package.xml
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

  Specific changes from 1.4.0a5
  * fix Bug #3860 PEAR_Dependency2 not included in 1 case

  Specific changes from 1.4.0a6
  * implement <usesrole>/<usestask> for custom role/task graceful failure
  * REALLY fix the debug_backtrace() issue (modified wrong pearcmd.php)
  * fix Bug #3864 Invalid dependency relation
  * fix Bug #3863 illogical warning about PEAR_Frontend_Gtk 0.4.0 with PEAR 1.4.0a6',
          'state' => 'alpha',
          'deps' =>
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
              'channel' => 'pear.php.net',
              'name' => 'Archive_Tar',
              'rel' => 'ge',
              'version' => '1.1',
              'optional' => 'no',
            ),
            4 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'Console_Getopt',
              'rel' => 'ge',
              'version' => '1.2',
              'optional' => 'no',
            ),
            5 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'XML_RPC',
              'rel' => 'ge',
              'version' => '1.2.0RC1',
              'optional' => 'no',
            ),
            6 =>
            array (
              'type' => 'ext',
              'name' => 'xml',
              'rel' => 'has',
              'optional' => 'no',
            ),
            7 =>
            array (
              'type' => 'ext',
              'name' => 'pcre',
              'rel' => 'has',
              'optional' => 'no',
            ),
            8 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Web',
              'rel' => 'ge',
              'version' => '0.5.0',
              'optional' => 'yes',
            ),
            9 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Gtk',
              'rel' => 'ge',
              'version' => '0.4.0',
              'optional' => 'yes',
            ),
          ),
        ),
        '1.4.0a6' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
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
          'releasedate' => '2005-03-17 10:11:11',
          'releasenotes' => 'This is a major milestone release for PEAR.  In addition to several killer features,
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
 "pear channel-update pecl.php.net" prior to packaging
 a pecl release.
  * Fix package.xml version 2.0 generation from package.xml 1.0
  * Fix Bug #3634: still too many pear-specific restrictions on package valid
  * Implement Request #3647: "pear package" only includes one package.xml
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

  Specific changes from 1.4.0a5
  * fix Bug #3860 PEAR_Dependency2 not included in 1 case',
          'state' => 'alpha',
          'deps' =>
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
              'channel' => 'pear.php.net',
              'name' => 'Archive_Tar',
              'rel' => 'ge',
              'version' => '1.1',
              'optional' => 'no',
            ),
            4 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'Console_Getopt',
              'rel' => 'ge',
              'version' => '1.2',
              'optional' => 'no',
            ),
            5 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'XML_RPC',
              'rel' => 'ge',
              'version' => '1.2.0RC1',
              'optional' => 'no',
            ),
            6 =>
            array (
              'type' => 'ext',
              'name' => 'xml',
              'rel' => 'has',
              'optional' => 'no',
            ),
            7 =>
            array (
              'type' => 'ext',
              'name' => 'pcre',
              'rel' => 'has',
              'optional' => 'no',
            ),
            8 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Web',
              'rel' => 'ge',
              'version' => '0.5.0',
              'optional' => 'yes',
            ),
            9 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Gtk',
              'rel' => 'ge',
              'version' => '0.4.0',
              'optional' => 'yes',
            ),
          ),
        ),
        '1.4.0a5' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
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
          'releasedate' => '2005-03-17 00:47:09',
          'releasenotes' => 'This is a major milestone release for PEAR.  In addition to several killer features,
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
 "pear channel-update pecl.php.net" prior to packaging
 a pecl release.
  * Fix package.xml version 2.0 generation from package.xml 1.0
  * Fix Bug #3634: still too many pear-specific restrictions on package valid
  * Implement Request #3647: "pear package" only includes one package.xml
  * Fix Bug #3677: Post-Install script message needs to display channel name

  Specific changes from 1.4.0a3:
  * upgrade suggested XML_RPC version to 1.2.1

  Specific changes from 1.4.0a4:
  * upgrade suggested XML_RPC version to 1.2.2
  * upgrade suggested Archive_Tar version to 1.3.0
  * attempt to address memory issues
  * relax validation further
  * disable debug_backtrace() in PEAR_Error constructor of PEAR installer
  * fix a strange version number condition when two packages were upgraded at the same time.
  * fix Bug #3808 channel packages with non-baseinstalldir files will conflict on upgrade
  * fix Bug #3801 [PATCH] analyzeSourceCode() reports PHP4 code as PHP5
  * fix Bug #3671 Installing package features doesn\'t work as expected
  * implement Request #3717 [Patch] Implement Simple run-tests output',
          'state' => 'alpha',
          'deps' =>
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
              'channel' => 'pear.php.net',
              'name' => 'Archive_Tar',
              'rel' => 'ge',
              'version' => '1.1',
              'optional' => 'no',
            ),
            4 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'Console_Getopt',
              'rel' => 'ge',
              'version' => '1.2',
              'optional' => 'no',
            ),
            5 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'XML_RPC',
              'rel' => 'ge',
              'version' => '1.2.0RC1',
              'optional' => 'no',
            ),
            6 =>
            array (
              'type' => 'ext',
              'name' => 'xml',
              'rel' => 'has',
              'optional' => 'no',
            ),
            7 =>
            array (
              'type' => 'ext',
              'name' => 'pcre',
              'rel' => 'has',
              'optional' => 'no',
            ),
            8 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Web',
              'rel' => 'ge',
              'version' => '0.5.0',
              'optional' => 'yes',
            ),
            9 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Gtk',
              'rel' => 'ge',
              'version' => '0.4.0',
              'optional' => 'yes',
            ),
          ),
        ),
        '1.4.0a4' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
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
          'releasedate' => '2005-03-03 08:17:19',
          'releasenotes' => 'This is a major milestone release for PEAR.  In addition to several killer features,
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
 "pear channel-update pecl.php.net" prior to packaging
 a pecl release.
  * Fix package.xml version 2.0 generation from package.xml 1.0
  * Fix Bug #3634: still too many pear-specific restrictions on package valid
  * Implement Request #3647: "pear package" only includes one package.xml
  * Fix Bug #3677: Post-Install script message needs to display channel name

  Specific changes from 1.4.0a3:
  * upgrade suggested XML_RPC version to 1.2.1',
          'state' => 'alpha',
          'deps' =>
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
              'channel' => 'pear.php.net',
              'name' => 'Archive_Tar',
              'rel' => 'ge',
              'version' => '1.1',
              'optional' => 'no',
            ),
            4 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'Console_Getopt',
              'rel' => 'ge',
              'version' => '1.2',
              'optional' => 'no',
            ),
            5 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'XML_RPC',
              'rel' => 'ge',
              'version' => '1.2.0RC1',
              'optional' => 'no',
            ),
            6 =>
            array (
              'type' => 'ext',
              'name' => 'xml',
              'rel' => 'has',
              'optional' => 'no',
            ),
            7 =>
            array (
              'type' => 'ext',
              'name' => 'pcre',
              'rel' => 'has',
              'optional' => 'no',
            ),
            8 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Web',
              'rel' => 'ge',
              'version' => '0.5.0',
              'optional' => 'yes',
            ),
            9 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Gtk',
              'rel' => 'ge',
              'version' => '0.4.0',
              'optional' => 'yes',
            ),
          ),
        ),
        '1.4.0a3' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
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
          'releasedate' => '2005-03-02 22:32:24',
          'releasenotes' => 'This is a major milestone release for PEAR.  In addition to several killer features,
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
 "pear channel-update pecl.php.net" prior to packaging
 a pecl release.
  * Fix package.xml version 2.0 generation from package.xml 1.0
  * Fix Bug #3634: still too many pear-specific restrictions on package valid
  * Implement Request #3647: "pear package" only includes one package.xml
  * Fix Bug #3677: Post-Install script message needs to display channel name',
          'state' => 'alpha',
          'deps' =>
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
              'channel' => 'pear.php.net',
              'name' => 'Archive_Tar',
              'rel' => 'ge',
              'version' => '1.1',
              'optional' => 'no',
            ),
            4 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'Console_Getopt',
              'rel' => 'ge',
              'version' => '1.2',
              'optional' => 'no',
            ),
            5 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'XML_RPC',
              'rel' => 'ge',
              'version' => '1.2.0RC1',
              'optional' => 'no',
            ),
            6 =>
            array (
              'type' => 'ext',
              'name' => 'xml',
              'rel' => 'has',
              'optional' => 'no',
            ),
            7 =>
            array (
              'type' => 'ext',
              'name' => 'pcre',
              'rel' => 'has',
              'optional' => 'no',
            ),
            8 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Web',
              'rel' => 'ge',
              'version' => '0.5.0',
              'optional' => 'yes',
            ),
            9 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Gtk',
              'rel' => 'ge',
              'version' => '0.4.0',
              'optional' => 'yes',
            ),
          ),
        ),
        '1.4.0a2' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
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
          'releasedate' => '2005-02-26 22:32:01',
          'releasenotes' => 'This is a major milestone release for PEAR.  In addition to several killer features,
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
  * Use 1.2.0 as recommended version of XML_RPC',
          'state' => 'alpha',
          'deps' =>
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
              'channel' => 'pear.php.net',
              'name' => 'Archive_Tar',
              'rel' => 'ge',
              'version' => '1.1',
              'optional' => 'no',
            ),
            4 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'Console_Getopt',
              'rel' => 'ge',
              'version' => '1.2',
              'optional' => 'no',
            ),
            5 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'XML_RPC',
              'rel' => 'ge',
              'version' => '1.2.0RC1',
              'optional' => 'no',
            ),
            6 =>
            array (
              'type' => 'ext',
              'name' => 'xml',
              'rel' => 'has',
              'optional' => 'no',
            ),
            7 =>
            array (
              'type' => 'ext',
              'name' => 'pcre',
              'rel' => 'has',
              'optional' => 'no',
            ),
            8 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Web',
              'rel' => 'ge',
              'version' => '0.5.0',
              'optional' => 'yes',
            ),
            9 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Gtk',
              'rel' => 'ge',
              'version' => '0.4.0',
              'optional' => 'yes',
            ),
          ),
        ),
        '1.4.0a1' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
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
          'releasedate' => '2005-02-26 18:52:10',
          'releasenotes' => 'This is a major milestone release for PEAR.  In addition to several killer features,
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
  * Everything above here that you just read',
          'state' => 'alpha',
          'deps' =>
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
              'channel' => 'pear.php.net',
              'name' => 'Archive_Tar',
              'rel' => 'ge',
              'version' => '1.1',
              'optional' => 'no',
            ),
            4 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'Console_Getopt',
              'rel' => 'ge',
              'version' => '1.2',
              'optional' => 'no',
            ),
            5 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'XML_RPC',
              'rel' => 'ge',
              'version' => '1.2.0RC1',
              'optional' => 'no',
            ),
            6 =>
            array (
              'type' => 'ext',
              'name' => 'xml',
              'rel' => 'has',
              'optional' => 'no',
            ),
            7 =>
            array (
              'type' => 'ext',
              'name' => 'pcre',
              'rel' => 'has',
              'optional' => 'no',
            ),
            8 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Web',
              'rel' => 'ge',
              'version' => '0.5.0',
              'optional' => 'yes',
            ),
            9 =>
            array (
              'type' => 'pkg',
              'channel' => 'pear.php.net',
              'name' => 'PEAR_Frontend_Gtk',
              'rel' => 'ge',
              'version' => '0.4.0',
              'optional' => 'yes',
            ),
          ),
        ),
        '1.3.5' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception php5-only exception class
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
 * the PEAR base class',
          'releasedate' => '2005-02-18 00:38:04',
          'releasenotes' => '* fix Bug #3505: pecl can\'t install PDO
* enhance pear run-tests dramatically
* fix Bug #3506: pear install should export the pear version into the environment',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.3.4' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception php5-only exception class
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
 * the PEAR base class',
          'releasedate' => '2005-01-01 20:26:39',
          'releasenotes' => '* fix a serious problem caused by a bug in all versions of PHP that caused multiple registration
  of the shutdown function of PEAR.php
* fix Bug #2861: package.dtd does not define NUMBER
* fix Bug #2946: ini_set warning errors
* fix Bug #3026: Dependency type "ne" is needed, "not" is not handled
  properly
* fix Bug #3061: potential warnings in PEAR_Exception
* implement Request #2848: PEAR_ErrorStack logger extends, PEAR_ERRORSTACK_DIE
* implement Request #2914: Dynamic Include Path for run-tests command
* make pear help listing more useful (put how-to-use info at the bottom of the listing)',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.3.3.1' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception php5-only exception class
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
 * the PEAR base class',
          'releasedate' => '2004-11-12 02:04:57',
          'releasenotes' => 'add RunTest.php to package.xml, make run-tests display failed tests, and use ui',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.3.3' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the alpha-quality PEAR_Exception php5-only exception class
 * the beta-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories
 * the PEAR base class',
          'releasedate' => '2004-10-28 13:40:34',
          'releasenotes' => 'Installer:
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
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.3.1' =>
        array (
          'doneby' => 'cellog',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the alpha-quality PEAR_ErrorStack advanced error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories',
          'releasedate' => '2004-04-06 20:19:35',
          'releasenotes' => 'PEAR Installer:

 * Bug #534  pear search doesn\'t list unstable releases
 * Bug #933  CMD Usability Patch
 * Bug #937  throwError() treats every call as static
 * Bug #964 PEAR_ERROR_EXCEPTION causes fatal error
 * Bug #1008 safe mode raises warning

PEAR_ErrorStack:

 * Added experimental error handling, designed to eventually replace
   PEAR_Error.  It should be considered experimental until explicitly marked
   stable.  require_once \'PEAR/ErrorStack.php\' to use.',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.3' =>
        array (
          'doneby' => 'pajoye',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories',
          'releasedate' => '2004-02-20 10:40:19',
          'releasenotes' => 'PEAR Installer:

* Bug #171 --alldeps with a rel="eq" should install the required version, if possible
* Bug #249 installing from an url doesnt work
* Bug #248 --force command does not work as expected
* Bug #293 [Patch] PEAR_Error not calling static method callbacks for error-handler
* Bug #324 pear -G gives Fatal Error (PHP-GTK not installed, but error is at engine level)
* Bug #594 PEAR_Common::analyzeSourceCode fails on string with $var and {
* Bug #521 Incorrect filename in naming warnings
* Moved download code into its own class
* Fully unit tested the installer, packager, downloader, and PEAR_Common',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.3b6' =>
        array (
          'doneby' => 'pajoye',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories',
          'releasedate' => '2004-01-25 20:57:03',
          'releasenotes' => 'PEAR Installer:

* Bug #171 --alldeps with a rel="eq" should install the required version, if possible
* Bug #249 installing from an url doesnt work
* Bug #248 --force command does not work as expected
* Bug #293 [Patch] PEAR_Error not calling static method callbacks for error-handler
* Bug #324 pear -G gives Fatal Error (PHP-GTK not installed, but error is at engine level)
* Bug #594 PEAR_Common::analyzeSourceCode fails on string with $var and {
* Bug #521 Incorrect filename in naming warnings
* Moved download code into its own class
* Fully unit tested the installer, packager, downloader, and PEAR_Common',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '1.3b5' =>
        array (
          'doneby' => 'pajoye',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories',
          'releasedate' => '2003-12-19 09:44:01',
          'releasenotes' => 'PEAR Installer:

* Bug #171 --alldeps with a rel="eq" should install the required version, if possible
* Bug #249 installing from an url doesnt work
* Bug #248 --force command does not work as expected
* Bug #293 [Patch] PEAR_Error not calling static method callbacks for error-handler
* Bug #324 pear -G gives Fatal Error (PHP-GTK not installed, but error is at engine level)
* Moved download code into its own class
* Fully unit tested the installer, packager, downloader, and PEAR_Common',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '1.3b3' =>
        array (
          'doneby' => 'cox',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories',
          'releasedate' => '2003-10-20 16:02:00',
          'releasenotes' => 'PEAR Installer:

* Bug #25413 Add local installed packages to list-all (Christian DickMann)
* Bug #23221 Pear installer - extension re-install segfault
* Better error detecting and reporting in "install/upgrade"
* Various other bugfixes and cleanups',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '1.3b2' =>
        array (
          'doneby' => 'cox',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling common operations
   with files and directories',
          'releasedate' => '2003-10-02 11:53:00',
          'releasenotes' => 'PEAR Installer:

* Updated deps for Archive_Tar and Console_Getopt
* Fixed #45 preferred_state works incorrectly
* Fixed optional dependency attrib removed from
  package.xml, making them a requirement',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '1.3b1' =>
        array (
          'doneby' => 'cox',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2003-09-29 13:19:00',
          'releasenotes' => 'PEAR Base Class:

* Fixed static calls to PEAR error-handling methods in classes
* Added ability to use a static method callback for error-handling,
  and removed use of inadvisable @ in setErrorHandling

PEAR Installer:

* Fixed #25117 - MD5 checksum should be case-insensitive
* Added dependency on XML_RPC, and optional dependency on xmlrpc extension
* Added --alldeps and --onlyreqdeps options to pear install/pear upgrade
* Sorting of installation/uninstallation so package order on the command-line is
  insignificant (fixes upgrade-all if every package is installed)
* pear upgrade will now install if the package is not installed (necessary for
  pear upgrade --alldeps, as installation is often necessary for new
  dependencies)
* fixed pear.bat if PHP is installed in a path like C:\\Program Files\\php
* Added ability to specify "pear install package-version" or
  "pear install package-state". For example: "pear install DB-1.2",
  or "pear install DB-stable"
* Fix #25008 - unhelpful error message
* Fixed optional dependencies in Dependency.php
* Fix #25322 - bad md5sum should be fatal error
* Package uninstall now also removes empty directories
* Fixed locking problems for reading commands (pear list, pear info)

OS_Guess Class:

* Fixed #25131 - OS_Guess warnings on empty lines from
  popen("/usr/bin/cpp $tmpfile", "r");

System Class:

* Fixed recursion deep param in _dirToStruct()
* Added the System::find() command (read API doc for more info)',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '1.2.1' =>
        array (
          'doneby' => 'pajoye',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2003-08-15 13:48:00',
          'releasenotes' => '- Set back the default library path (BC issues)',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.2' =>
        array (
          'doneby' => 'cox',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2003-08-13 22:35:00',
          'releasenotes' => 'Changes from 1.1:

* Changed license from PHP 2.02 to 3.0
* Added support for optional dependencies
* Made upgrade and uninstall package case insensitive
* pear makerpm, now works and generates a better system independent spec file
* pear install|build pecl-package, now exposes the compilation progress
* Installer now checks dependencies on package uninstall
* Added proxy support for remote commands using the xmlrcp C ext (Adam Ashley)
* Added the command "download-all" (Alex Merz)
* Made package dependency checking back to work
* Added support for spaces in path names (Greg)
* Various bugfixes
* Added new pear "bundle" command, which downloads and uncompress a PECL package.
The main purpouse of this command is for easily adding extensions to the PHP sources
before compiling it.',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.2b5' =>
        array (
          'doneby' => 'cox',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2003-08-05 16:32:00',
          'releasenotes' => '* Changed license from PHP 2.02 to 3.0
* Added support for optional dependencies
* Made upgrade and uninstall package case insensitive
* pear makerpm, now works and generates a better system independent spec file
* pear install|build pecl-package, now exposes the compilation progress
* Installer now checks dependencies on package uninstall
* Added proxy support for remote commands using the xmlrcp C ext (Adam Ashley)
* Added the command "download-all" (Alex Merz)
* Made package dependency checking back to work
* Added support for spaces in path names (Greg)
* Various bugfixes
* Added new pear "bundle" command, which downloads and uncompress a PECL package.
The main purpouse of this command is for easily adding extensions to the PHP sources
before compiling it.',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '1.2b4' =>
        array (
          'doneby' => 'cox',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2003-08-05 03:26:00',
          'releasenotes' => '* Changed license from PHP 2.02 to 3.0
* Added support for optional dependencies
* Made upgrade and uninstall package case insensitive
* pear makerpm, now works and generates a better system independent spec file
* pear install|build pecl-package, now exposes the compilation progress
* Installer now checks dependencies on package uninstall
* Added proxy support for remote commands using the xmlrcp C ext (Adam Ashley)
* Added the command "download-all" (Alex Merz)
* Made package dependency checking back to work
* Added support for spaces in path names (Greg)
* Various bugfixes
* Added new pear "bundle" command, which downloads and uncompress a PECL package.
The main purpouse of this command is for easily adding extensions to the PHP sources
before compiling it.',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '1.2b3' =>
        array (
          'doneby' => 'cox',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2003-08-03 19:45:00',
          'releasenotes' => '* Changed license from PHP 2.02 to 3.0
* Added support for optional dependencies
* Made upgrade and uninstall package case insensitive
* pear makerpm, now works and generates a better system independent spec file
* pear install|build pecl-package, now exposes the compilation progress
* Installer now checks dependencies on package uninstall
* Added proxy support for remote commands using the xmlrcp C ext (Adam Ashley)
* Added the command "download-all" (Alex Merz)
* Made package dependency checking back to work
* Added support for spaces in path names (Greg)
* Added new pear "bundle" command, which downloads and uncompress a PECL package.
The main purpouse of this command is for easily adding extensions to the PHP sources
before compiling it.',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '1.2b2' =>
        array (
          'doneby' => 'cox',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2003-06-23 13:33:00',
          'releasenotes' => '* Changed license from PHP 2.02 to 3.0
* Added support for optional dependencies
* Made upgrade and uninstall package case insensitive
* pear makerpm, now works and generates a better system independent spec file
* pear install|build <pecl-package>, now exposes the compilation progress
* Added new pear bundle command, which downloads and uncompress a <pecl-package>.
The main purpouse of this command is for easily adding extensions to the PHP sources
before compiling it.',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '1.2b1' =>
        array (
          'doneby' => 'cox',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2003-06-23 10:07:00',
          'releasenotes' => '* Changed license from PHP 2.02 to 3.0
* Added support for optional dependencies
* pear makerpm, now works and generates a better system independent spec file
* pear install|build <pecl-package>, now exposes the compilation progress
* Added new pear bundle command, which downloads and uncompress a <pecl-package>.
The main purpouse of this command is for easily adding extensions to the PHP sources
before compiling it.',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '1.1' =>
        array (
          'doneby' => 'ssb',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2003-05-10 23:27:00',
          'releasenotes' => 'PEAR BASE CLASS:

* PEAR_Error now supports exceptions when using Zend Engine 2.  Set the
  error mode to PEAR_ERROR_EXCEPTION to make PEAR_Error throw itself
  as an exception (invoke PEAR errors with raiseError() or throwError()
  just like before).

PEAR INSTALLER:

* Packaging and validation now parses PHP source code (unless
  ext/tokenizer is disabled) and does some coding standard conformance
  checks.  Specifically, the names of classes and functions are
  checked to ensure that they are prefixed with the package name.  If
  your package has symbols that should be without this prefix, you can
  override this warning by explicitly adding a "provides" entry in
  your package.xml file.  See the package.xml file for this release
  for an example (OS_Guess, System and md5_file).

  All classes and non-private (not underscore-prefixed) methods and
  functions are now registered during "pear package".',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.0.1' =>
        array (
          'doneby' => 'ssb',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2003-01-10 01:26:00',
          'releasenotes' => '* PEAR_Error class has call backtrace available by
  calling getBacktrace().  Available if used with
  PHP 4.3 or newer.

* PEAR_Config class uses getenv() rather than $_ENV
  to read environment variables.

* System::which() Windows fix, now looks for
  exe/bat/cmd/com suffixes rather than just exe

* Added "pear cvsdiff" command

* Windows output buffering bugfix for "pear" command',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.0' =>
        array (
          'doneby' => 'ssb',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2002-12-27 19:37:00',
          'releasenotes' => '* set default cache_ttl to 1 hour
* added "clear-cache" command',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.0b3' =>
        array (
          'doneby' => 'ssb',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2002-12-13 02:24:00',
          'releasenotes' => '* fixed "info" shortcut (conflicted with "install")
* added "php_bin" config parameter
* all "non-personal" config parameters now use
  environment variables for defaults (very useful
  to override the default php_dir on Windows!)',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.0b2' =>
        array (
          'doneby' => 'ssb',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2002-11-26 01:43:00',
          'releasenotes' => 'Changes, Installer:
* --force option no longer ignores errors, use
  --ignore-errors instead
* installer transactions: failed installs abort
  cleanly, without leaving half-installed packages
  around',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '1.0b1' =>
        array (
          'doneby' => 'ssb',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2002-10-12 14:21:00',
          'releasenotes' => 'New Features, Installer:
* new command: "pear makerpm"
* new command: "pear search"
* new command: "pear upgrade-all"
* new command: "pear config-help"
* new command: "pear sign"
* Windows support for "pear build" (requires
  msdev)
* new dependency type: "zend"
* XML-RPC results may now be cached (see
  cache_dir and cache_ttl config)
* HTTP proxy authorization support
* install/upgrade install-root support

Bugfixes, Installer:
* fix for XML-RPC bug that made some remote
  commands fail
* fix problems under Windows with
  DIRECTORY_SEPARATOR
* lots of other minor fixes
* --force option did not work for "pear install
  Package"
* http downloader used "4.2.1" rather than
  "PHP/4.2.1" as user agent
* bending over a little more to figure out how
  PHP is installed
* "platform" file attribute was not included
  during "pear package"

New Features, PEAR Library:
* added PEAR::loadExtension($ext)
* added PEAR::delExpect()
* System::mkTemp() now cleans up at shutdown
* defined PEAR_ZE2 constant (boolean)
* added PEAR::throwError() with a simpler API
  than raiseError()

Bugfixes, PEAR Library:
* ZE2 compatibility fixes
* use getenv() as fallback for $_ENV',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
        '0.90' =>
        array (
          'doneby' => 'ssb',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2002-06-06 11:34:00',
          'releasenotes' => '* fix: "help" command was broken
* new command: "info"
* new command: "config-help"
* un-indent multi-line data from xml description files
* new command: "build"
* fix: config-set did not work with "set" parameters
* disable magic_quotes_runtime
* "install" now builds and installs C extensions
* added PEAR::delExpect()
* System class no longer inherits PEAR
* grouped PEAR_Config parameters
* add --nobuild option to install/upgrade commands
* new and more generic Frontend API',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '0.11' =>
        array (
          'doneby' => 'ssb',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2002-05-28 01:24:00',
          'releasenotes' => '* fix: "help" command was broken
* new command: "info"
* new command: "config-help"
* un-indent multi-line data from xml description files
* new command: "build"
* fix: config-set did not work with "set" parameters
* disable magic_quotes_runtime',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '0.10' =>
        array (
          'doneby' => 'ssb',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR installer, for creating, distributing
   and installing packages',
          'releasedate' => '2002-05-26 12:55:00',
          'releasenotes' => 'Lots of stuff this time.  0.9 was not actually self-hosting, even
though it claimed to be.  This version finally is self-hosting
(really!), meaning you can upgrade the installer with the command
"pear upgrade PEAR".

* new config paramers: http_proxy and umask
* HTTP proxy support when downloading packages
* generalized command handling code
* and fixed the bug that would not let commands have the
  same options as "pear" itself
* added long options to every command
* added command shortcuts ("pear help shortcuts")
* added stub for Gtk installer
* some phpdoc fixes
* added class dependency detector (using ext/tokenizer)
* dependency handling fixes
* added OS_Guess class for detecting OS
* install files with the "platform" attribute set
  only on matching operating systems
* PEAR_Remote now falls back to the XML_RPC package
  if xmlrpc-epi is not available
* renamed command: package-list -> list
* new command: package-dependencies
* lots of minor fixes',
          'state' => 'beta',
          'deps' =>
          array (
          ),
        ),
        '0.9' =>
        array (
          'doneby' => 'ssb',
          'license' => 'PHP License',
          'summary' => 'PEAR Base System',
          'description' => 'The PEAR package contains:
 * the PEAR base class
 * the PEAR_Error error handling mechanism
 * the PEAR command-line toolkit, for creating, distributing
   and installing packages',
          'releasedate' => '2002-04-13 01:04:00',
          'releasenotes' => 'First package release.  Commands implemented:
   remote-package-info
   list-upgrades
   list-remote-packages
   download
   config-show
   config-get
   config-set
   list-installed
   shell-test
   install
   uninstall
   upgrade
   package
   package-list
   package-info
   login
   logout',
          'state' => 'stable',
          'deps' =>
          array (
          ),
        ),
      ),
      'deprecated' => false,
      'installed' => '- no -',
    ),
    'cmd' => 'remote-info',
  ),
), $fakelog->getLog(), 'PEAR log');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
