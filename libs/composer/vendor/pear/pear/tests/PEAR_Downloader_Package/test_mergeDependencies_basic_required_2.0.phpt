--TEST--
PEAR_Downloader_Package->detectDependencies(), required dep package.xml 2.0
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$packageDir      = dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'test_mergeDependencies'. DIRECTORY_SEPARATOR;
$mainpackage     = $packageDir . 'main-1.1.tgz';
$requiredpackage = $packageDir . 'required-1.1.tgz';

$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/main-1.1.tgz', $mainpackage);
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/required-1.1.tgz', $requiredpackage);

$pearweb->addRESTConfig("http://pear.php.net/rest/r/main/allreleases.xml",
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>main</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/main/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>main</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License 3.0</l>
 <s>Main Package</s>
 <d>Main Package</d>
 <r xlink:href="/rest/r/main"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/main/1.1.xml",
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/main">main</p>
 <c>pear.php.net</c>
 <v>1.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>Main Package</s>
 <d>Main Package</d>
 <da>2004-09-30</da>
 <n>test</n>
 <f>639</f>
 <g>http://www.example.com/main-1.1</g>
 <x xlink:href="package.1.1.xml"/>
</r>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/main/deps.1.1.txt",
'a:1:{s:8:"required";a:3:{s:3:"php";a:2:{s:3:"min";s:5:"4.2.0";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:7:"package";a:3:{s:4:"name";s:8:"required";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";}}}',
'text/plain');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/required/allreleases.xml",
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>required</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/required/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>required</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License 3.0</l>
 <s>Required Package</s>
 <d>Required Package</d>
 <r xlink:href="/rest/r/main"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/required/1.1.xml",
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/required">required</p>
 <c>pear.php.net</c>
 <v>1.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>Required Package</s>
 <d>Required Package</d>
 <da>2004-09-30</da>
 <n>test</n>
 <f>639</f>
 <g>http://www.example.com/required-1.1</g>
 <x xlink:href="package.1.1.xml"/>
</r>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/required/deps.1.1.txt",
'a:1:{s:8:"required";a:2:{s:3:"php";a:2:{s:3:"min";s:5:"4.2.0";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}}}',
'text/plain');

$dp = newDownloaderPackage(array());
$result = $dp->initialize('main');
$phpunit->assertNoErrors('after create 1');

$params = array(&$dp);
$dp->detectDependencies($params);
$phpunit->assertNoErrors('after detect');
$phpunit->assertEquals(array(
), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array(), $fakelog->getDownload(), 'download callback messages');
$phpunit->assertEquals(1, count($params), 'detectDependencies');

$result = PEAR_Downloader_Package::mergeDependencies($params);
$phpunit->assertNoErrors('after merge 1');
$phpunit->assertTrue($result, 'first return');
$phpunit->assertEquals(2, count($params), 'mergeDependencies');
$phpunit->assertEquals('main', $params[0]->getPackage(), 'main package');
$phpunit->assertEquals('required', $params[1]->getPackage(), 'main package');

$result = PEAR_Downloader_Package::mergeDependencies($params);
$phpunit->assertNoErrors('after merge 2');
$phpunit->assertFalse($result, 'second return');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
