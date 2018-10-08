--TEST--
install command, test pear install Installed#group where Installed is already installed
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
if (strolower(substr(php_uname('s'), 0, 3)) == 'win') {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'simplepackage.xml';
$cg = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'Console_Getopt-1.2.tgz';
$pearweb->addRESTConfig("http://pear.php.net/rest/r/console_getopt/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Console_Getopt</p>
 <c>pear.php.net</c>
 <r><v>1.2</v><s>stable</s></r>
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
$pearweb->addHTMLConfig('http://pear.php.net/get/Console_Getopt-1.2.tgz', $cg);
$_test_dep->setPHPVersion('5.2.1');
$_test_dep->setPEARVersion('1.5.1');
$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$chan->setBaseURL('REST1.1', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'installed.xml';
$res = $command->run('install', array(), array($pathtopackagexml));
$phpunit->assertNoErrors('after install');
$phpunit->assertTrue($res, 'result');
$fakelog->getLog();
// install sub-group

$res = $command->run('install', array(), array($pathtopackagexml . '#test'));
$phpunit->assertNoErrors('after install');
$phpunit->assertTrue($res, 'result');
$phpunit->showAll();
$dummy = null;
$dl = &$command->getDownloader($dummy, array());

echoFakelog($fakelog);
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECTF--
1;Skipping package "pear/Installed", already installed as version 1.4.0a1
3;Downloading "http://pear.php.net/get/Console_Getopt-1.2.tgz"
1;downloading Console_Getopt-1.2.tgz ...
1;Starting to download Console_Getopt-1.2.tgz (3,371 bytes)
1;.
1;...done: 3,371 bytes
3;adding to transaction: mkdir %s/php/Console
2;+ create dir %s/php/Console
3;+ mkdir %s/php/Console
3;+ cp %s/Console_Getopt-1.2/Console/Getopt.php %s/php/Console/.tmpGetopt.php
2;md5sum ok: %s/php/Console/Getopt.php
3;adding to transaction: chmod 6%d4 %s/php/Console/.tmpGetopt.php
3;adding to transaction: rename %s/php/Console/.tmpGetopt.php %s/php/Console/Getopt.php 
3;adding to transaction: installed_as Console/Getopt.php %s/php/Console/Getopt.php %s/php /Console
2;about to commit 4 file operations for Console_Getopt
3;+ chmod 6%d4 %s/php/Console/.tmpGetopt.php
3;+ mv %s/php/Console/.tmpGetopt.php %s/php/Console/Getopt.php
2;successfully committed 4 file operations
array (
  'info' => 
  array (
    'data' => 'install ok: channel://pear.php.net/Console_Getopt-1.2',
  ),
  'cmd' => 'install',
)
tests done
