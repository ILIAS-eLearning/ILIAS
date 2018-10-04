--TEST--
PEAR_Dependency2->checkPackageDependency() recommended version (downloaded)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
require_once 'PEAR/PackageFile/v1.php';
$packageDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR;

$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
//$chan->setBaseURL('REST1.1', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pearweb->addRESTConfig("http://pear.php.net/rest/r/foo/allreleases.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>foo</p>
 <c>pear.php.net</c>
 <r>
  <v>1.10</v>
  <s>stable</s>
  <co>
   <c>pear.php.net</c>
   <p>mine</p>
   <min>0.9</min>
   <max>2.0</max>
  </co>
 </r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/foo/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>foo</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License</l>
 <s>PEAR Base System</s>
 <d>This is a major milestone release for PEAR.  In addition to several killer features,</d>
 <r xlink:href="/rest/r/foo"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/foo/1.10.xml",
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/foo">foo</p>
 <c>pear.php.net</c>
 <v>1.10</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>PEAR Base System</s>
 <d>This is a major milestone release for PEAR.  In addition to several killer features,</d>
 <da>2005-01-01 18:40:51</da>
 <n>test</n>
 <f>252733</f>
 <g>http://www.example.com/test-1.0</g>
 <x xlink:href="package.1.10.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/foo/deps.1.10.txt", 'a:1:{s:8:"required";a:2:{s:3:"php";a:2:{s:3:"min";s:5:"4.2.0";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}}}', 'text/plain');


$dep = new test_PEAR_Dependency2($config, array(),
        array('channel' => 'pear.php.net', 'package' => 'mine'), PEAR_VALIDATE_DOWNLOADING);
$phpunit->assertNoErrors('create 1');
$down = new PEAR_Downloader($fakelog, array(), $config);
$dp = new PEAR_Downloader_Package($down);
$dp->initialize($packageDir . 'package.xml');
$params = array(&$dp);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '1.13',
        'recommended' => '1.9'
    ), true, $params);

$phpunit->assertErrors(array(
    array(
        'package' => 'PEAR_Error',
        'message' => 'pear/mine dependency package "pear/foo" downloaded version 1.0 is not the recommended version 1.9, but may be compatible, use --force to install'
    ),
), 'recommended 1');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'recommended 1 log');
$phpunit->assertIsa('PEAR_Error', $result, 'recommended 1');

$pf = &$dp->getPackageFile();
$pf->setVersion('1.9');
$dp->setPackageFile($pf);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '1.9',
        'recommended' => '1.9'
    ), true, $params);
$phpunit->assertNoErrors('recommended works');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'recommended works log');
$phpunit->assertTrue($result, 'recommended works');

$dp = new PEAR_Downloader_Package($down);
$dp->initialize($packageDir . 'compatpackage.xml');

$parent = new PEAR_PackageFile_v1;
$parent->setPackage('mine');
$parent->setSummary('foo');
$parent->setDescription('foo');
$parent->setDate('2004-10-01');
$parent->setLicense('PHP License');
$parent->setVersion('1.10');
$parent->setState('stable');
$parent->setNotes('foo');
$parent->addFile('/', 'foo.php', array('role' => 'php'));
$parent->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$parent->setConfig($config);

$dl = new test_PEAR_Downloader($fakelog, array(), $config);
$dp2 = new test_PEAR_Downloader_Package($dl);
$dp2->setPackageFile($parent);
$params = array(&$dp, &$dp2);

$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '2.0',
        'recommended' => '1.8'
    ), true, $params);

$phpunit->assertNoErrors('compatible local works');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'compatible local works log');
$phpunit->assertTrue($result, 'compatible local works');

$dp = newFakeDownloaderPackage(array());
$dp->initialize('foo');
$params = array(&$dp, &$dp2);
$result = $dep->validatePackageDependency(
    array(
        'name' => 'foo',
        'channel' => 'pear.php.net',
        'min' => '0.9',
        'max' => '2.0',
        'recommended' => '1.8'
    ), true, $params);

$phpunit->assertNoErrors('compatible local works');
$phpunit->assertEquals(array(
), $fakelog->getLog(), 'compatible local works log');
$phpunit->assertTrue($result, 'compatible local works');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
