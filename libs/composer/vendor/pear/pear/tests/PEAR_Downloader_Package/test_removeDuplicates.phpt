--TEST--
PEAR_Downloader_Package::removeDuplicates()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pathtopackagexml  = dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'test_initialize_downloadurl'. DIRECTORY_SEPARATOR . 'test-1.0.tgz';
$pathtopackagexml2 = dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'test_removeDuplicates'. DIRECTORY_SEPARATOR . 'package.xml';

$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/test-1.0.tgz', $pathtopackagexml);

$pearweb->addRESTConfig("http://pear.php.net/rest/r/test/allreleases.xml",
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>test</p>
 <c>www.example.com</c>
 <r><v>1.0</v><s>stable</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/test/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>test</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+System">File System</ca>
 <l>PHP</l>
 <s>Required Package</s>
 <d>Required Package</d>
 <r xlink:href="/rest/r/test"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/test/1.0.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/test">test</p>
 <c>pear.php.net</c>
 <v>1.0</v>
 <st>stable</st>
 <l>PHP</l>
 <m>cellog</m>
 <s>Required Package</s>
 <d>Required Package</d>
 <da>2004-10-10</da>
 <n>test</n>
 <f>8580</f>
 <g>http://www.example.com/test-1.0.tgz</g>
 <x xlink:href="package.1.0.xml"/>
</r>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/test/deps.1.0.txt", 'b:0;', 'text/plain');

$GLOBALS['pearweb']->addRESTConfig('http://www.example.com/rest/r/test/package.1.0.xml',
'<?xml version="1.0" encoding="UTF-8"?>
<package xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://pear.php.net/dtd/package-1.0.xsd" version="1.0">
 <name>test</name>
 <summary>Required Package</summary>
 <description>Required Package</description>
 <maintainers>
  <maintainer>
   <user>cellog</user>
   <role>lead</role>
   <email>cellog@php.net</email>
   <name>Greg Beaver</name>
  </maintainer>
 </maintainers>
 <release>
  <version>1.0</version>
  <date>2004-10-10</date>
  <license>PHP License</license>
  <state>stable</state>
  <notes>test</notes>
  <filelist>
   <dir name="test" baseinstalldir="test">
    <file name="test.php" role="php"/>
    <file name="test2.php" role="php" install-as="hi.php"/>
    <file name="test3.php" role="php" install-as="another.php" platform="windows"/>
    <file name="test4.php" role="data">
     <replace from="@1@" to="version" type="package-info"/>
     <replace from="@2@" to="data_dir" type="pear-config"/>
     <replace from="@3@" to="DIRECTORY_SEPARATOR" type="php-const"/>
    </file>
   </dir>
  </filelist>
 </release>
 <changelog>
  <release>
   <version>1.0</version>
   <date>2004-10-10</date>
   <license>PHP License</license>
   <state>stable</state>
   <notes>test</notes>
  </release>
 </changelog>
</package>
', 'text/xml');

$dp1 = newDownloaderPackage(array());
$result = $dp1->initialize('test#subgroup');
$phpunit->assertNoErrors('after create 1');

$dp2 = newDownloaderPackage(array());
$result = $dp2->initialize('http://www.example.com/test-1.0.tgz');
$phpunit->assertNoErrors('after create 2');

$dp3 = newDownloaderPackage(array());
$result = $dp3->initialize($pathtopackagexml);
$phpunit->assertNoErrors('after create 3');

$dp4 = newDownloaderPackage(array());
$result = $dp4->initialize($pathtopackagexml2);
$phpunit->assertNoErrors('after create 4');

$params = array(&$dp1, &$dp2, &$dp3, &$dp4);
PEAR_Downloader_Package::removeDuplicates($params);
$phpunit->assertEquals(3, count($params), 'unsuccessful removal');
$phpunit->assertEquals('test',     $params[0]->getPackage(), 'first one');
$phpunit->assertEquals('subgroup', $params[0]->getGroup(),   'first one group');
$phpunit->assertEquals('test',     $params[1]->getPackage(), 'second one');
$phpunit->assertEquals('default',  $params[1]->getGroup(),   'second one group');
$phpunit->assertEquals('test2',    $params[2]->getPackage(), 'third one');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
