--TEST--
install command, bug #3671
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$packageDir       = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR;
$pathtopackagexml = $packageDir . 'bug3671_1.xml';

$reg  = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR</p>
 <c>pear.php.net</c>
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

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_web/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR_Frontend_Web</p>
 <c>pear.php.net</c>
 <r><v>0.4</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2.2</v><s>beta</s></r>
 <r><v>0.2.1</v><s>beta</s></r>
 <r><v>0.2</v><s>beta</s></r>
 <r><v>0.1</v><s>beta</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear_frontend_web/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR_Frontend_Web</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>Webbased PEAR Package Manager</s>
 <d>The most accessible way to manage your pear-compatible packages.

This frontend\'s most valuable features are:
* Webbased: no remote shell access needed.
* Fully channel aware: no default channel, all channels are managed at the same time
* Unique docviewer: read the installed documentation in your borwser.
* Plus all features of the PEAR Installer.

You can view a demo at http://tias.ulyssis.org/frontweb_demo/


(An include error on PEAR/WebInstaller.php means you upgraded from a very old version, check docs/index.php.txt)</d>
 <r xlink:href="/rest/r/pear_frontend_web"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_web/0.4.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear_frontend_web">PEAR_Frontend_Web</p>
 <c>pear.php.net</c>
 <v>0.4</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>tias</m>
 <s>Webbased PEAR Package Manager</s>
 <d>The most accessible way to manage your pear-compatible packages.

This frontend\'s most valuable features are:
* Webbased: no remote shell access needed.
* Fully channel aware: no default channel, all channels are managed at the same time
* Unique docviewer: read the installed documentation in your borwser.
* Plus all features of the PEAR Installer.</d>
 <da>2003-06-07</da>
 <n>Bugfixes release:
- Remove Pager dep
- Should work well on non apache system (ie IIS)
- The \'installed packages\' is now the entry page
  (no more remote connection during startup)</n>
 <f>53152</f>
 <g>http://pear.php.net/get/PEAR_Frontend_Web-0.4</g>
 <x xlink:href="package.0.4.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_web/deps.0.4.txt", 'a:2:{i:1;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:20:"Net_UserAgent_Detect";}i:2;a:3:{s:4:"type";s:3:"pkg";s:3:"rel";s:3:"has";s:4:"name";s:16:"HTML_Template_IT";}}', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_gtk/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>PEAR_Frontend_Gtk</p>
 <c>pear.php.net</c>
 <r><v>0.4.0</v><s>beta</s></r>
 <r><v>0.3</v><s>beta</s></r>
 <r><v>0.2</v><s>snapshot</s></r>
 <r><v>0.1</v><s>snapshot</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear_frontend_gtk/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR_Frontend_Gtk</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>Gtk (Desktop) PEAR Package Manager</s>
 <d>Desktop Interface to the PEAR Package Manager, Requires PHP-GTK</d>
 <r xlink:href="/rest/r/pear_frontend_gtk"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_gtk/0.4.0.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
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

$pearweb->addRESTConfig("http://pear.php.net/rest/p/pear/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>PEAR</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>PEAR Base System</s>
 <d>The PEAR package contains:
 * the PEAR installer, for creating, distributing
   and installing packages
 * the PEAR_Exception PHP5 error handling mechanism
 * the PEAR_ErrorStack advanced error handling mechanism
 * the PEAR_Error error handling mechanism
 * the OS_Guess class for retrieving info about the OS
   where PHP is running on
 * the System class for quick handling of common operations
   with files and directories
 * the PEAR base class

  Features in a nutshell:
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
  * support for custom file roles and installation tasks</d>
 <r xlink:href="/rest/r/pear"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/1.4.0a5.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
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

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear/deps.1.4.0a5.txt", 'a:3:{s:8:"required";a:4:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}s:7:"package";a:3:{i:0;a:5:{s:4:"name";s:11:"Archive_Tar";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";s:11:"recommended";s:3:"1.2";s:7:"exclude";s:5:"1.3.0";}i:1;a:4:{s:4:"name";s:14:"Console_Getopt";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.2";s:11:"recommended";s:3:"1.2";}i:2;a:4:{s:4:"name";s:7:"XML_RPC";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.2.0RC1";s:11:"recommended";s:5:"1.2.2";}}s:9:"extension";a:2:{i:0;a:1:{s:4:"name";s:3:"xml";}i:1;a:1:{s:4:"name";s:4:"pcre";}}}s:8:"optional";a:1:{s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}i:1;a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}s:5:"group";a:3:{i:0;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:59:"adds the ability to install packages to a remote ftp server";s:4:"name";s:13:"remoteinstall";}s:7:"package";a:4:{s:4:"name";s:7:"Net_FTP";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:8:"1.3.0RC1";s:11:"recommended";s:5:"1.3.0";}}i:1;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:26:"PEAR\'s web-based installer";s:4:"name";s:12:"webinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Web";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.5.0";}}i:2;a:2:{s:7:"attribs";a:2:{s:4:"hint";s:30:"PEAR\'s PHP-GTK-based installer";s:4:"name";s:12:"gtkinstaller";}s:7:"package";a:3:{s:4:"name";s:17:"PEAR_Frontend_Gtk";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:5:"0.4.0";}}}}', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Archive_Tar</p>
 <c>pear.php.net</c>
 <r><v>1.3.2</v><s>stable</s></r>
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

$pearweb->addRESTConfig("http://pear.php.net/rest/p/archive_tar/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
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

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/1.2.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/archive_tar">Archive_Tar</p>
 <c>pear.php.net</c>
 <v>1.2</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>vblavet</m>
 <s>Tar file management class</s>
 <d>This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.
</d>
 <da>2004-05-08 10:03:17</da>
 <n>Add support for other separator than the space char and bug
	correction
</n>
 <f>14792</f>
 <g>http://pear.php.net/get/Archive_Tar-1.2</g>
 <x xlink:href="package.1.2.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/deps.1.2.txt", 'b:0;', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Console_Getopt</p>
 <c>pear.php.net</c>
 <r><v>1.2.3</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0</min><max>1.6.0</max></co>
</r>
 <r><v>1.2.2</v><s>stable</s><co><c>pear.php.net</c><p>PEAR</p><min>1.4.0</min><max>1.5.0</max></co>
</r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.11</v><s>beta</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/console_getopt/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Console_Getopt</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Console">Console</ca>
 <l>PHP License</l>
 <s>Command-line option parser</s>
 <d>This is a PHP implementation of &quot;getopt&quot; supporting both
short and long options.</d>
 <r xlink:href="/rest/r/console_getopt"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/1.2.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
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
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>XML_RPC</p>
 <c>pear.php.net</c>
 <r><v>1.5.1</v><s>stable</s></r>
 <r><v>1.5.0</v><s>stable</s></r>
 <r><v>1.5.0RC2</v><s>beta</s></r>
 <r><v>1.5.0RC1</v><s>beta</s></r>
 <r><v>1.4.8</v><s>stable</s></r>
 <r><v>1.4.7</v><s>stable</s></r>
 <r><v>1.4.6</v><s>stable</s></r>
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

$pearweb->addRESTConfig("http://pear.php.net/rest/p/xml_rpc/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>XML_RPC</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/Web+Services">Web Services</ca>
 <l>PHP License</l>
 <s>PHP implementation of the XML-RPC protocol</s>
 <d>A PEAR-ified version of Useful Inc\'s XML-RPC for PHP.

It has support for HTTP/HTTPS transport, proxies and authentication.</d>
 <r xlink:href="/rest/r/xml_rpc"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/xml_rpc/1.4.3.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
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

$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setPEARversion('1.4.0a5');

$at = $packageDir . 'Archive_Tar-1.2.tgz';
$cg = $packageDir . 'Console_Getopt-1.2.tgz';
$xr = $packageDir . 'XML_RPC-1.2.0RC6.tgz';
$res = $command->run('install', array(), array($at, $cg, $xr));

$phpunit->assertTrue($res, 'result');
$fakelog->getLog();
$fakelog->getDownload();

$res = $command->run('install', array(), array($pathtopackagexml));
$phpunit->assertNoErrors('after install setup');
$fakelog->getLog();
$fakelog->getDownload();
$config->set('preferred_state', 'alpha');

$res = $command->run('install', array(), array('pear#webinstaller'));

$phpunit->assertEquals(array(
   array (
    0 => 3,
    1 => 'pear/PEAR: Skipping required dependency "pear/Archive_Tar" version 1.2, already installed as version 1.2',
   ),
   1 =>
   array (
   0 => 3,
    1 => 'pear/PEAR: Skipping required dependency "pear/Console_Getopt" version 1.2, already installed as version 1.2',
   ),
  array (
    0 => 3,
    1 => 'pear/PEAR: Skipping required dependency "pear/XML_RPC" version 1.4.3, already installed as version 1.2.0RC6',
  ),
  array (
    0 => 3,
    1 => 'Notice: package "pear/PEAR" optional dependency "pear/PEAR_Frontend_Web" will not be automatically downloaded',
  ),
  array (
    0 => 3,
    1 => 'Notice: package "pear/PEAR" optional dependency "pear/PEAR_Frontend_Gtk" will not be automatically downloaded',
  ),
  array (
    0 => 1,
    1 => 'Did not download optional dependencies: pear/PEAR_Frontend_Web, pear/PEAR_Frontend_Gtk, use --alldeps to download automatically',
  ),
  array (
    0 => 0,
    1 => 'Failed to download pear/PEAR_Frontend_Web (version >= 0.5.0), latest release is version 0.4, stability "beta", use "channel://pear.php.net/PEAR_Frontend_Web-0.4" to install',
  ),
  array (
    0 => 1,
    1 => 'Skipping package "pear/PEAR", already installed as version 1.4.0a5',
  ),
  array (
    'info' =>
    array (
      'data' =>
      array (
        0 =>
        array (
          0 => 'No valid packages found',
        ),
      ),
      'headline' => 'Install Errors',
    ),
    'cmd' => 'no command',
  ),
), $fakelog->getLog(), 'log');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
