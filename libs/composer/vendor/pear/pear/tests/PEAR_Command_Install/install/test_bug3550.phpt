--TEST--
install command, bug #3550
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$packageDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR;

$reg  = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$GLOBALS['pearweb']->addHtmlConfig('http://pear.php.net/get/PEAR_Frontend_Web-0.4.tgz', $packageDir . 'PEAR_Frontend_Web-0.4.tgz');

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

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_gtk/0.3.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pear_frontend_gtk">PEAR_Frontend_Gtk</p>
 <c>pear.php.net</c>
 <v>0.3</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>alan_k</m>
 <s>Gtk (Desktop) PEAR Package Manager</s>
 <d>Desktop Interface to the PEAR Package Manager, Requires PHP-GTK
</d>
 <da>2002-07-25 09:11:00</da>
 <n>Attempt to fix package file so it installs,
           some of the warnings have been fixed
</n>
 <f>70008</f>
 <g>http://pear.php.net/get/PEAR_Frontend_Gtk-0.3</g>
 <x xlink:href="package.0.3.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pear_frontend_gtk/deps.0.3.txt", 'b:0;', 'text/xml');

$nu = $packageDir . 'Net_UserAgent_Detect-2.0.1.tgz';
$hi = $packageDir . 'HTML_Template_IT-1.1.tgz';
$at = $packageDir . 'Archive_Tar-1.2.tgz';
$cg = $packageDir . 'Console_Getopt-1.2.tgz';
$xr = $packageDir . 'XML_RPC-1.2.0RC6.tgz';
$pe = $packageDir . 'PEAR-1.4.0a1.tgz';

$_test_dep->setPEARVersion('1.4.0a1');
$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setExtensions(array('xml' => '1.0', 'pcre' => '1.0'));
$res = $command->run('install', array(), array($nu, $hi, $at, $cg, $xr, $pe));

$phpunit->assertTrue($res, 'result');
$fakelog->getLog();
$fakelog->getDownload();
$res = $command->run('install', array(), array('PEAR_Frontend_Web-beta'));

$phpunit->assertEquals( array (
  0 =>
  array (
    0 => 2,
    1 => 'pear/PEAR_Frontend_Web: Skipping required dependency "pear/Net_UserAgent_Detect", is already installed',
  ),
  1 =>
  array (
    0 => 2,
    1 => 'pear/PEAR_Frontend_Web: Skipping required dependency "pear/HTML_Template_IT", is already installed',
  ),
  2 =>
  array (
    0 => 0,
    1 => 'pear/pear requires package "pear/PEAR_Frontend_Web" (version >= 0.5.0), downloaded version is 0.4',
  ),
  3 =>
  array (
    0 => 0,
    1 => 'pear/pear requires package "pear/PEAR_Frontend_Web" (version >= 0.5.0), downloaded version is 0.4',
  ),
  4 =>
  array (
    0 => 0,
    1 => 'pear/PEAR_Frontend_Web cannot be installed, conflicts with installed packages',
  ),
  5 =>
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
 )
, $fakelog->getLog(), 'log');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
