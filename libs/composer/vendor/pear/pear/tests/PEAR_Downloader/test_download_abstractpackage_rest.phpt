--TEST--
PEAR_Downloader->download() with downloadable abstract package (REST-based channel)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
require_once $dir . 'setup.php.inc';

$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pathtopackagexml = $dir . 'packages'. DIRECTORY_SEPARATOR . 'test-1.0.tgz';

$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/test-1.0.tgz', $pathtopackagexml);

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

$dp = new test_PEAR_Downloader($fakelog, array(), $config);
$phpunit->assertNoErrors('after create');
$result = $dp->download(array('test'));

$phpunit->assertEquals(1, count($result), 'return');
$phpunit->assertIsa('test_PEAR_Downloader_Package', $result[0], 'right class');
$phpunit->assertIsa('PEAR_PackageFile_v1', $pf = $result[0]->getPackageFile(), 'right kind of pf');
$phpunit->assertEquals('test', $pf->getPackage(), 'right package');
$phpunit->assertEquals('pear.php.net', $pf->getChannel(), 'right channel');

$dlpackages = $dp->getDownloadedPackages();
$phpunit->assertEquals(1, count($dlpackages), 'downloaded packages count');
$phpunit->assertEquals(3, count($dlpackages[0]), 'internals package count');
$phpunit->assertEquals(array('file', 'info', 'pkg'), array_keys($dlpackages[0]), 'indexes');

$phpunit->assertEquals($dp->getDownloadDir() . DIRECTORY_SEPARATOR . 'test-1.0.tgz',
    $dlpackages[0]['file'], 'file');
$phpunit->assertIsa('PEAR_PackageFile_v1', $dlpackages[0]['info'], 'info');
$phpunit->assertEquals('test', $dlpackages[0]['pkg'], 'test');

$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(0, count($after), 'after getdp count');

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
), $fakelog->getLog(), 'log messages');

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
), $fakelog->getDownload(), 'download callback messages');

$phpunit->assertEquals(array(
  0 =>
  array (
    0 => 'http://pear.php.net/rest/r/test/allreleases.xml',
    1 => '200',
  ),
  array (
    0 => 'http://pear.php.net/rest/p/test/info.xml',
    1 => '200',
  ),
  array (
    0 => 'http://pear.php.net/rest/r/test/1.0.xml',
    1 => '200',
  ),
  array (
    0 => 'http://pear.php.net/rest/r/test/deps.1.0.txt',
    1 => '200',
  ),
), $pearweb->getRestCalls(), 'rest calls');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
