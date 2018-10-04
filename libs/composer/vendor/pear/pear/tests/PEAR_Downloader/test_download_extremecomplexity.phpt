--TEST--
PEAR_Downloader->download() with downloadable abstract package, extreme dependency complexity [stable]
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$packageDir = dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'packages'. DIRECTORY_SEPARATOR;
$pkg1 = $packageDir . 'pkg1-1.1.tgz';
$pkg2 = $packageDir . 'pkg2-1.1.tgz';
$pkg3 = $packageDir . 'pkg3-1.1.tgz';
$pkg4 = $packageDir . 'pkg4-1.1.tgz';
$pkg5 = $packageDir . 'pkg5-1.1.tgz';
$pkg6 = $packageDir . 'pkg6-1.1.tgz';

$GLOBALS['pearweb']->addHtmlConfig('http://pear.php.net/get/pkg1-1.1.tgz', $pkg1);
$GLOBALS['pearweb']->addHtmlConfig('http://pear.php.net/get/pkg2-1.1.tgz', $pkg2);
$GLOBALS['pearweb']->addHtmlConfig('http://pear.php.net/get/pkg3-1.1.tgz', $pkg3);
$GLOBALS['pearweb']->addHtmlConfig('http://pear.php.net/get/pkg4-1.1.tgz', $pkg4);
$GLOBALS['pearweb']->addHtmlConfig('http://pear.php.net/get/pkg5-1.1.tgz', $pkg5);
$GLOBALS['pearweb']->addHtmlConfig('http://pear.php.net/get/pkg6-1.1.tgz', $pkg6);

$reg = &$config->getRegistry();
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg1/allreleases.xml",
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>pkg1</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg2/allreleases.xml",
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>pkg2</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg3/allreleases.xml",
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>pkg3</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg4/allreleases.xml",
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>pkg4</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg5/allreleases.xml",
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>pkg5</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg6/allreleases.xml",
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>pkg6</p>
 <c>pear.php.net</c>
 <r><v>1.1</v><s>stable</s></r>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/pkg1/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>pkg1</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License 3.0</l>
 <s>required test for PEAR_Installer</s>
 <d>fake package</d>
 <r xlink:href="/rest/r/pkg1"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/pkg2/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>pkg2</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License 3.0</l>
 <s>required test for PEAR_Installer</s>
 <d>fake package</d>
 <r xlink:href="/rest/r/pkg2"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/pkg3/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>pkg3</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License 3.0</l>
 <s>required test for PEAR_Installer</s>
 <d>fake package</d>
 <r xlink:href="/rest/r/pkg3"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/pkg4/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>pkg4</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License 3.0</l>
 <s>required test for PEAR_Installer</s>
 <d>fake package</d>
 <r xlink:href="/rest/r/pkg4"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/pkg5/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>pkg5</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License 3.0</l>
 <s>required test for PEAR_Installer</s>
 <d>fake package</d>
 <r xlink:href="/rest/r/pkg5"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/pkg6/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>pkg6</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/PEAR">PEAR</ca>
 <l>PHP License 3.0</l>
 <s>required test for PEAR_Installer</s>
 <d>fake package</d>
 <r xlink:href="/rest/r/pkg6"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg1/1.1.xml",
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pkg1">pkg1</p>
 <c>pear.php.net</c>
 <v>1.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>fakeuser</m>
 <s>fake package required for PEAR Installer</s>
 <d>fake package</d>
 <da>2004-04-17 18:40:51</da>
 <n>required dependency test</n>
 <f>700</f>
 <g>http://pear.php.net/get/pkg1-1.1</g>
 <x xlink:href="package.1.1.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg2/1.1.xml",
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pkg2">pkg2</p>
 <c>pear.php.net</c>
 <v>1.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>fakeuser</m>
 <s>fake package required for PEAR Installer</s>
 <d>fake package</d>
 <da>2004-04-17 18:40:51</da>
 <n>required dependency test</n>
 <f>700</f>
 <g>http://pear.php.net/get/pkg2-1.1</g>
 <x xlink:href="package.1.1.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg3/1.1.xml",
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pkg3">pkg3</p>
 <c>pear.php.net</c>
 <v>1.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>fakeuser</m>
 <s>fake package required for PEAR Installer</s>
 <d>fake package</d>
 <da>2004-04-17 18:40:51</da>
 <n>required dependency test</n>
 <f>700</f>
 <g>http://pear.php.net/get/pkg3-1.1</g>
 <x xlink:href="package.1.1.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg4/1.1.xml",
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pkg4">pkg4</p>
 <c>pear.php.net</c>
 <v>1.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>fakeuser</m>
 <s>fake package required for PEAR Installer</s>
 <d>fake package</d>
 <da>2004-04-17 18:40:51</da>
 <n>required dependency test</n>
 <f>700</f>
 <g>http://pear.php.net/get/pkg4-1.1</g>
 <x xlink:href="package.1.1.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg5/1.1.xml",
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pkg5">pkg5</p>
 <c>pear.php.net</c>
 <v>1.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>fakeuser</m>
 <s>fake package required for PEAR Installer</s>
 <d>fake package</d>
 <da>2004-04-17 18:40:51</da>
 <n>required dependency test</n>
 <f>700</f>
 <g>http://pear.php.net/get/pkg5-1.1</g>
 <x xlink:href="package.1.1.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg6/1.1.xml",
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/pkg6">pkg6</p>
 <c>pear.php.net</c>
 <v>1.1</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>fakeuser</m>
 <s>fake package required for PEAR Installer</s>
 <d>fake package</d>
 <da>2004-04-17 18:40:51</da>
 <n>required dependency test</n>
 <f>700</f>
 <g>http://pear.php.net/get/pkg6-1.1</g>
 <x xlink:href="package.1.1.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg1/deps.1.1.txt",
'a:1:{s:8:"required";a:3:{s:3:"php";a:2:{s:3:"min";s:5:"4.3.6";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:7:"package";a:3:{s:4:"name";s:4:"pkg2";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";}}}',
'text/plain');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg2/deps.1.1.txt",
'a:1:{s:8:"required";a:3:{s:3:"php";a:2:{s:3:"min";s:5:"4.3.6";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:7:"package";a:3:{s:4:"name";s:4:"pkg3";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";}}}',
'text/plain');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg3/deps.1.1.txt",
'a:1:{s:8:"required";a:3:{s:3:"php";a:2:{s:3:"min";s:5:"4.3.6";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:7:"package";a:2:{i:0;a:3:{s:4:"name";s:4:"pkg4";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";}i:1;a:3:{s:4:"name";s:4:"pkg5";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";}}}}',
'text/plain');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg4/deps.1.1.txt",
'a:1:{s:8:"required";a:3:{s:3:"php";a:2:{s:3:"min";s:5:"4.3.6";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:7:"package";a:3:{s:4:"name";s:4:"pkg6";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";}}}',
'text/plain');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg5/deps.1.1.txt",
'a:1:{s:8:"required";a:3:{s:3:"php";a:2:{s:3:"min";s:5:"4.3.6";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0a1";}s:7:"package";a:3:{s:4:"name";s:4:"pkg6";s:7:"channel";s:12:"pear.php.net";s:3:"min";s:3:"1.1";}}}',
'text/plain');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/pkg6/deps.1.1.txt", 'b:0;', 'text/plain');

$_test_dep->setPHPversion('4.3.10');
$_test_dep->setPEARversion('1.4.0a1');
$dp = new test_PEAR_Downloader($fakelog, array('alldeps' => true), $config);
$phpunit->assertNoErrors('after create');

$reg = &$config->getRegistry();
$result = $dp->download(array('pkg1'));
$phpunit->assertEquals(6, count($result), 'return');

$dlpackages = $dp->getDownloadedPackages();
$phpunit->assertEquals(6, count($dlpackages), 'downloaded packages count');

$phpunit->assertEquals(array (
  array (
    0 => 3,
    1 => 'Downloading "http://pear.php.net/get/pkg1-1.1.tgz"',
  ),
  array (
    0 => 1,
    1 => 'downloading pkg1-1.1.tgz ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download pkg1-1.1.tgz (700 bytes)',
  ),
  array (
    0 => 1,
    1 => '.'
  ),
  array (
    0 => 1,
    1 => '...done: 700 bytes',
  ),
  array (
    0 => 3,
    1 => 'Downloading "http://pear.php.net/get/pkg2-1.1.tgz"',
  ),
  array (
    0 => 1,
    1 => 'downloading pkg2-1.1.tgz ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download pkg2-1.1.tgz (704 bytes)',
  ),
  array (
    0 => 1,
    1 => '...done: 704 bytes',
  ),
  array (
    0 => 3,
    1 => 'Downloading "http://pear.php.net/get/pkg3-1.1.tgz"',
  ),
  array (
    0 => 1,
    1 => 'downloading pkg3-1.1.tgz ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download pkg3-1.1.tgz (714 bytes)',
  ),
  array (
    0 => 1,
    1 => '...done: 714 bytes',
  ),
  array (
    0 => 3,
    1 => 'Downloading "http://pear.php.net/get/pkg4-1.1.tgz"',
  ),
  array (
    0 => 1,
    1 => 'downloading pkg4-1.1.tgz ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download pkg4-1.1.tgz (702 bytes)',
  ),
  array (
    0 => 1,
    1 => '...done: 702 bytes',
  ),
  array (
    0 => 3,
    1 => 'Downloading "http://pear.php.net/get/pkg5-1.1.tgz"',
  ),
  array (
    0 => 1,
    1 => 'downloading pkg5-1.1.tgz ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download pkg5-1.1.tgz (706 bytes)',
  ),
  array (
    0 => 1,
    1 => '...done: 706 bytes',
  ),
  array (
    0 => 3,
    1 => 'Downloading "http://pear.php.net/get/pkg6-1.1.tgz"',
  ),
  array (
    0 => 1,
    1 => 'downloading pkg6-1.1.tgz ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download pkg6-1.1.tgz (673 bytes)',
  ),
  array (
    0 => 1,
    1 => '...done: 673 bytes',
   ),
), $fakelog->getLog(), 'log messages');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
