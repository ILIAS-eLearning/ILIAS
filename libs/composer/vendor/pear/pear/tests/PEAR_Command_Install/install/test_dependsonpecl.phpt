--TEST--
install command, package.xml 1.0 package depends on pecl
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
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$chan = $reg->getChannel('pecl.php.net');
$chan->setBaseURL('REST1.0', 'http://pecl.php.net/rest/');
$reg->updateChannel($chan);

$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'packages'. DIRECTORY_SEPARATOR . 'dependsonpecl.xml';
$pathtopackage    = dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'packages'. DIRECTORY_SEPARATOR . 'peclpkg-1.3.0.tgz';

$pearweb->addHtmlConfig('http://pecl.php.net/get/peclpackage-1.3.0.tgz', $pathtopackage);

$pearweb->addRESTConfig("http://pear.php.net/rest/r/peclpkg/allreleases.xml", false, false);

$pearweb->addRESTConfig("http://pecl.php.net/rest/r/peclpkg/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>peclpkg</p>
 <c>pecl.php.net</c>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2.5</v><s>stable</s></r>
 <r><v>1.2.4</v><s>stable</s></r>
 <r><v>1.2.3</v><s>stable</s></r>
 <r><v>1.2.2</v><s>stable</s></r>
 <r><v>1.2.1</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pecl.php.net/rest/p/peclpkg/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>peclpkg</n>
 <c>pecl.php.net</c>
 <ca xlink:href="/rest/c/Authentication">Authentication</ca>
 <l>BSD</l>
 <s>extension package source package</s>
 <d>extension source</d>
 <r xlink:href="/rest/r/peclpkg"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pecl.php.net/rest/r/peclpkg/1.3.0.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/peclpkg">peclpkg</p>
 <c>pecl.php.net</c>
 <v>1.3.0</v>
 <st>stable</st>
 <l>BSD</l>
 <m>cellog</m>
 <s>extension package source package</s>
 <d>extension source</d>
 <da>2007-03-18 17:02:49</da>
 <n>stuff</n>
 <f>29750</f>
 <g>http://pecl.php.net/get/peclpackage-1.3.0</g>
 <x xlink:href="package.1.3.0.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pecl.php.net/rest/r/peclpkg/deps.1.3.0.txt", 'a:1:{s:8:"required";a:2:{s:3:"php";a:1:{s:3:"min";s:5:"4.2.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}}}', 'text/xml');

$res = $command->run('install', array(), array($pathtopackagexml));
$phpunit->assertErrors(array(
    array(
        'package' => 'PEAR_Error',
        'message' => 'install failed'
    ),
), 'after install');

$dummy = null;
$dl = &$command->getDownloader($dummy, array());

$log = $fakelog->getLog();
$phpunit->assertEquals(array (
  array (
    0 => 3,
    1 => 'Notice: package "pear/PEAR" required dependency "pecl/peclpkg" will not be automatically downloaded',
  ),
  array (
    0 => 1,
    1 => 'Did not download dependencies: pecl/peclpkg, use --alldeps or --onlyreqdeps to download automatically',
  ),
  array (
    0 => 0,
    1 => 'pear/PEAR requires package "pear/peclpkg"',
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
), $log, 'log messages');

$phpunit->assertEquals( array (
  0 =>
  array (
    0 => 'http://pear.php.net/rest/r/peclpkg/allreleases.xml',
    1 => '404',
  ),
  1 =>
  array (
    0 => 'http://pecl.php.net/rest/r/peclpkg/allreleases.xml',
    1 => '200',
  ),
  2 =>
  array (
    0 => 'http://pecl.php.net/rest/p/peclpkg/info.xml',
    1 => '200',
  ),
  3 =>
  array (
    0 => 'http://pecl.php.net/rest/r/peclpkg/1.3.0.xml',
    1 => '200',
  ),
  4 =>
  array (
    0 => 'http://pecl.php.net/rest/r/peclpkg/deps.1.3.0.txt',
    1 => '200',
  ),
 )
, $pearweb->getRESTCalls(), 'rest calls');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
