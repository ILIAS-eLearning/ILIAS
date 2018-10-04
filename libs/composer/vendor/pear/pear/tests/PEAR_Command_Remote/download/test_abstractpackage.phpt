--TEST--
download command (abstract package)
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

$packageDir       = dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'packages'. DIRECTORY_SEPARATOR;
$pathtopackagexml =  $packageDir . 'test-1.0.tgz';
$pathtopackagexml2 = $packageDir . 'test-1.0.tar';

$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/test-1.0.tgz', $pathtopackagexml);
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/test-1.0.tar', $pathtopackagexml2);

$pearweb->addRESTConfig("http://pear.php.net/rest/r/test/allreleases.xml", '<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>test</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>devel</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.10-b1</v><s>beta</s></r>
 <r><v>0.9</v><s>stable</s></r>
 <r><v>0.4</v><s>stable</s></r>
 <r><v>0.3</v><s>stable</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/test/1.0.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/test">test</p>
 <c>pear.php.net</c>
 <v>1.0</v>
 <st>alpha</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>test</s>
 <d>test</d>
 <da>2005-04-17 18:40:51</da>
 <n>test</n>
 <f>252733</f>
 <g>http://www.example.com/test-1.0</g>
 <x xlink:href="package.1.0.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/test/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>test</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/test">test</ca>
 <l>PHP License</l>
 <s>test</s>
 <d>test</d>
 <r xlink:href="/rest/r/test"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/test/deps.1.0.txt", 'b:0;', 'text/plain');

mkdir($temp_path . DIRECTORY_SEPARATOR . 'bloob');
chdir($temp_path . DIRECTORY_SEPARATOR . 'bloob');
$e = $command->run('download', array(), array('test'));

$phpunit->assertNoErrors('download');
$phpunit->assertEquals(array (
  array (
    0 => 3,
    1 => 'Downloading "http://www.example.com/test-1.0.tgz"',
  ),
  array (
    0 => 1,
    1 => 'downloading test-1.0.tgz ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download test-1.0.tgz (785 bytes)',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '...done: 785 bytes',
  ),
  array (
    'info' => 'File ' . $temp_path . DIRECTORY_SEPARATOR . 'bloob' .
        DIRECTORY_SEPARATOR . 'test-1.0.tgz downloaded',
    'cmd' => 'download',
  ),
), $fakelog->getLog(), 'log');

$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 'setup',
    1 => 'self',
  ),
  1 =>
  array (
    0 => 'saveas',
    1 => 'test-1.0.tgz',
  ),
  2 =>
  array (
    0 => 'start',
    1 =>
    array (
      0 => 'test-1.0.tgz',
      1 => '785',
    ),
  ),
  3 =>
  array (
    0 => 'bytesread',
    1 => 785,
  ),
  4 =>
  array (
    0 => 'done',
    1 => 785,
  ),
), $fakelog->getDownload(), 'download log');

$e = $command->run('download', array('nocompress' => true), array('test'));
$phpunit->assertNoErrors('download --nocompress');
$phpunit->assertEquals(array (
  array (
    0 => 3,
    1 => 'Downloading "http://www.example.com/test-1.0.tar"',
  ),
  array (
    0 => 1,
    1 => 'downloading test-1.0.tar ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download test-1.0.tar (6,656 bytes)',
  ),
  array (
    0 => 1,
    1 => '...done: 6,656 bytes',
  ),
  array (
    'info' => 'File ' . $temp_path . DIRECTORY_SEPARATOR . 'bloob' .
        DIRECTORY_SEPARATOR . 'test-1.0.tar downloaded',
    'cmd' => 'download',
  ),
), $fakelog->getLog(), '--nocompress log');

$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 'setup',
    1 => 'self',
  ),
  1 =>
  array (
    0 => 'saveas',
    1 => 'test-1.0.tar',
  ),
  2 =>
  array (
    0 => 'start',
    1 =>
    array (
      0 => 'test-1.0.tar',
      1 => '6656',
    ),
  ),
  3 =>
  array (
    0 => 'bytesread',
    1 => 1024,
  ),
  4 =>
  array (
    0 => 'bytesread',
    1 => 2048,
  ),
  5 =>
  array (
    0 => 'bytesread',
    1 => 3072,
  ),
  6 =>
  array (
    0 => 'bytesread',
    1 => 4096,
  ),
  7 =>
  array (
    0 => 'bytesread',
    1 => 5120,
  ),
  8 =>
  array (
    0 => 'bytesread',
    1 => 6144,
  ),
  9 =>
  array (
    0 => 'bytesread',
    1 => 6656,
  ),
  10 =>
  array (
    0 => 'done',
    1 => 6656,
  ),
), $fakelog->getDownload(), 'download --nocompress log');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
