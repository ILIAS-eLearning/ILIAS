--TEST--
PEAR_Downloader_Package::analyzeDependencies package.xml 2.0 [force]
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$_test_dep->setPhpversion('4.0');
$_test_dep->setPEARVersion('1.4.0dev13');

$packageDir      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages'. DIRECTORY_SEPARATOR;
$mainpackage     = $packageDir . 'main-1.0.tgz';
$requiredpackage = $packageDir . 'required-1.1.tgz';
$sub1package     = $packageDir . 'sub1-1.1.tgz';
$sub2package     = $packageDir . 'sub2-1.1.tgz';

$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/main-1.0.tgz', $mainpackage);
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/required-1.1.tgz', $requiredpackage);
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/sub1-1.0.tgz', $sub1package);
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/sub2-1.0.tgz', $sub2package);

$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pearweb->addRESTConfig("http://pear.php.net/rest/r/main/allreleases.xml",
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>main</p>
 <c>pear.php.net</c>
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

$pearweb->addRESTConfig("http://pear.php.net/rest/r/main/1.0.xml",
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/main">main</p>
 <c>pear.php.net</c>
 <v>1.0</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>Main Package</s>
 <d>Main Package</d>
 <da>2004-09-30</da>
 <n>test</n>
 <f>639</f>
 <g>http://www.example.com/main-1.0</g>
 <x xlink:href="package.1.0.xml"/>
</r>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/main/deps.1.0.txt",
'a:3:{s:8:"required";a:3:{s:3:"php";a:2:{s:3:"min";s:5:"4.2.0";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}s:7:"package";a:3:{s:4:"name";s:8:"required";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";}}s:8:"optional";a:1:{s:7:"package";a:3:{s:4:"name";s:8:"optional";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";}}s:5:"group";a:2:{s:7:"attribs";a:2:{s:4:"name";s:3:"foo";s:4:"hint";s:13:"testing group";}s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:4:"sub1";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";}i:1;a:3:{s:4:"name";s:4:"sub2";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";}}}}',
'text/plain');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/required/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/optional/allreleases.xml", false, false);
$pearweb->addRESTConfig("http://pear.php.net/rest/r/sub2/allreleases.xml", false, false);

$pearweb->addRESTConfig("http://pear.php.net/rest/r/sub1/allreleases.xml",
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>sub1</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/sub1/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>sub1</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License 3.0</l>
 <s>Sub Package1</s>
 <d>Sub Package1</d>
 <r xlink:href="/rest/r/sub1"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/sub1/1.1.xml",
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/sub1">sub1</p>
 <c>pear.php.net</c>
 <v>1.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>Sub Package 1</s>
 <d>Sub Package 1</d>
 <da>2004-11-10</da>
 <n>test</n>
 <f>639</f>
 <g>http://www.example.com/sub1-1.1</g>
 <x xlink:href="package.1.1.xml"/>
</r>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/sub1/deps.1.1.txt",
'a:1:{s:8:"required";a:2:{s:3:"php";a:2:{s:3:"min";s:3:"4.0";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:10:"1.4.0dev13";}}}',
'text/plain');

$dp = newFakeDownloaderPackage(array('force' => true));
$result = $dp->initialize('main#foo');
$phpunit->assertNoErrors('after create 1');

$params = array(&$dp);
$dp->detectDependencies($params);
$phpunit->assertNoErrors('after detect');
$phpunit->assertEquals(array (
  array (
    0 => 0,
    1 => 'Package "pear.php.net/main" dependency "pear.php.net/required" has no releases',
  ),
  array (
    0 => 3,
    1 => 'Notice: package "pear/main" optional dependency "pear/optional" will not be automatically downloaded',
  ),
  array (
    0 => 0,
    1 => 'Package "pear.php.net/main" dependency "pear.php.net/optional" has no releases',
  ),
  array (
    0 => 1,
    1 => 'Did not download optional dependencies: pear/optional, use --alldeps to download automatically',
  ),
  array (
    0 => 0,
    1 => 'Package "pear.php.net/main" dependency "pear.php.net/sub2" has no releases',
  ),
), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array(), $fakelog->getDownload(), 'download callback messages');
$phpunit->assertEquals(1, count($params), 'detectDependencies');
$result = PEAR_Downloader_Package::mergeDependencies($params);
$phpunit->assertNoErrors('after merge 1');

$err = $dp->_downloader->analyzeDependencies($params);
$phpunit->assertNoErrors('end');
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 0,
    1 => 'warning: pear/main requires PHP (version >= 4.2.0, version <= 6.0.0), installed version is 4.0',
  ),
  1 =>
  array (
    0 => 0,
    1 => 'warning: pear/main requires package "pear/required" (version >= 1.1)',
  ),
  2 =>
  array (
    0 => 0,
    1 => 'pear/main can optionally use package "pear/optional" (version >= 1.1)',
  ),
  3 =>
  array (
    0 => 0,
    1 => 'pear/main can optionally use package "pear/sub2" (version >= 1.1)',
  ),
), $fakelog->getLog(), 'end log');

$phpunit->assertEquals(array(), $fakelog->getDownload(), 'end download');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
