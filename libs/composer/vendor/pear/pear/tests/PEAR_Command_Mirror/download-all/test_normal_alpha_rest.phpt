--TEST--
download-all command, preferred_state = alpha (REST)
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

$ch = new PEAR_ChannelFile;
$ch->setName('smoog');
$ch->setBaseURL('REST1.0', 'http://smoog/rest/');
$ch->setSummary('smoog');
$reg->addChannel($ch);

$packageDir      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR;
$pathtoStableAPC = $packageDir . 'APC-1.3.0.tgz';
$pathtoAlphaAPC  = $packageDir . 'APC-1.4.0a1.tgz';
$pathtoSmoogAPC  = $packageDir . 'APC-1.5.0a1.tgz';
$pathtoAT        = $packageDir . 'Archive_Tar-1.5.0a1.tgz';

$pearweb->addHtmlConfig('http://pear.php.net/get/APC-1.3.0.tgz',           $pathtoStableAPC);
$pearweb->addHtmlConfig('http://pear.php.net/get/APC-1.4.0a1.tgz',         $pathtoAlphaAPC);
$pearweb->addHtmlConfig('http://pear.php.net/get/Archive_Tar-1.5.0a1.tgz', $pathtoAT);
$pearweb->addHtmlConfig('http://smoog/get/APC-1.5.0a1.tgz',                $pathtoSmoogAPC);

$pearweb->addRESTConfig('http://smoog/rest/p/packages.xml',
'<?xml version="1.0" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>smoog</c>
 <p>APC</p>
</a>',
'text/xml');

$pearweb->addRESTConfig('http://pear.php.net/rest/r/apc/allreleases.xml',
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>APC</p>
 <c>pear.php.net</c>
 <r><v>1.4.0a1</v><s>alpha</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
</a>',
'text/xml');

$pearweb->addRESTConfig('http://pear.php.net/rest/r/archive_tar/allreleases.xml',
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Archive_Tar</p>
 <c>pear.php.net</c>
 <r><v>1.5.0a1</v><s>alpha</s></r>
</a>',
'text/xml');

$pearweb->addRESTConfig('http://smoog/rest/r/apc/allreleases.xml',
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>APC</p>
 <c>smoog</c>
 <r><v>1.5.0a1</v><s>alpha</s></r>
</a>',
'text/xml');

$pearweb->addRESTConfig('http://smoog/rest/r/apc/1.5.0a1.xml',
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/apc">APC</p>
 <c>smoog</c>
 <v>1.5.0a1</v>
 <st>alpha</st>
 <l>PHP License</l>
 <m>rasmus</m>
 <s>Alternative PHP Cache</s>
 <d>APC is the Alternative PHP Cache. It was conceived of to provide a free, open, and robust framework for caching and optimizing PHP intermediate code.</d>
 <da>2005-04-17 18:40:51</da>
 <n>Release notes</n>
 <f>252733</f>
 <g>http://smoog/get/APC-1.5.a1</g>
 <x xlink:href="package.1.5.0a1.xml"/>

</r>',
'text/xml');

$pearweb->addRESTConfig('http://pear.php.net/rest/r/apc/1.3.0.xml',
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/apc">APC</p>
 <c>pear.php.net</c>
 <v>1.3.0</v>
 <st>stable</st>
 <l>PHP License</l>
 <m>rasmus</m>
 <s>Alternative PHP Cache</s>
 <d>APC is the Alternative PHP Cache. It was conceived of to provide a free, open, and robust framework for caching and optimizing PHP intermediate code.</d>
 <da>2005-04-17 18:40:51</da>
 <n>Release notes</n>
 <f>252733</f>
 <g>http://pear.php.net/get/APC-1.3.0</g>
 <x xlink:href="package.1.3.0.xml"/>

</r>',
'text/xml');

$pearweb->addRESTConfig('http://pear.php.net/rest/r/apc/1.4.0a1.xml',
'<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/apc">APC</p>
 <c>pear.php.net</c>
 <v>1.4.0a1</v>
 <st>alpha</st>
 <l>PHP License</l>
 <m>rasmus</m>
 <s>Alternative PHP Cache</s>
 <d>APC is the Alternative PHP Cache. It was conceived of to provide a free, open, and robust framework for caching and optimizing PHP intermediate code.</d>
 <da>2006-04-17 18:40:51</da>
 <n>Release notes</n>
 <f>262733</f>
 <g>http://pear.php.net/get/APC-1.4.0a1</g>
 <x xlink:href="package.1.4.0a1.xml"/>

</r>',
'text/xml');

$pearweb->addRESTConfig('http://pear.php.net/rest/r/archive_tar/1.5.0a1.xml',
'<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/archive_tar">Archive_Tar</p>
 <c>pear.php.net</c>
 <v>1.5.0a1</v>
 <st>alpha</st>
 <l>PHP License</l>

 <m>cellog</m>
 <s>Tar file management class</s>
 <d>This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.</d>
 <da>2007-01-03 16:32:15</da>
 <n>Correct Bug #4016
Remove duplicate remove error display with "@"
Correct Bug #3909 : Check existence of OS_WINDOWS constant
Correct Bug #5452 fix for &quot;lone zero block&quot; when untarring packages
Change filemode (from pear-core/Archive/Tar.php v.1.21)
Correct Bug #6486 Can not extract symlinks
Correct Bug #6933 Archive_Tar (Tar file management class) Directory traversal
Correct Bug #8114 Files added on-the-fly not storing date
Correct Bug #9352 Bug on _dirCheck function over nfs path</n>

 <f>17150</f>
 <g>http://pear.php.net/get/Archive_Tar-1.5.0a1</g>
 <x xlink:href="package.1.5.0a1.xml"/>
</r>',
'text/xml');

$pearweb->addRESTConfig("http://smoog/rest/r/apc/deps.1.5.0a1.txt", 'a:1:{s:8:"required";a:2:{s:3:"php";a:2:{s:3:"min";s:3:"4.2";s:3:"max";s:5:"6.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0dev13";}}}', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/apc/deps.1.3.0.txt", 'b:0;', 'text/plain');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/apc/deps.1.4.0a1.txt", 'b:0;', 'text/plain');
$pearweb->addRESTConfig('http://pear.php.net/rest/r/archive_tar/deps.1.5.0a1.txt', 'a:1:{s:8:"required";a:2:{s:3:"php";a:1:{s:3:"min";s:5:"4.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}}}', 'text/plain');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/packages.xml",
'<?xml version="1.0" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>pear.php.net</c>
 <p>Archive_Tar</p>
 <p>APC</p>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/apc/info.xml",
'<?xml version="1.0"?>
<p xmlns="http://pear.php.net/dtd/rest.package"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.package
    http://pear.php.net/dtd/rest.package.xsd">
 <n>APC</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Zip file management class</s>
 <d>This class provides handling of zip files in PHP.
It supports creating, listing, extracting and adding to zip files.</d>
 <r xlink:href="/rest/r/apc"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/archive_tar/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Archive_Tar</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>PHP License</l>
 <s>Tar file management class</s>
 <d>This class provides handling of tar files in PHP.
It supports creating, listing, extracting and adding to tar files.
Gzip support is available if PHP has the zlib extension built-in or
loaded. Bz2 compression is also supported with the bz2 extension loaded.</d>
 <r xlink:href="/rest/r/archive_tar"/>
</p>',
'text/xml');

$config->set('preferred_state', 'alpha');
$save = getcwd();
chdir($temp_path);
$e = $command->run('download-all', array(), array());
$phpunit->assertNoErrors('after');
$phpunit->showall();

$phpunit->assertEquals(array (
  array (
    'info' => 'Using Channel pear.php.net',
    'cmd' => 'no command',
  ),
  array (
    'info' => 'Using Preferred State of alpha',
    'cmd' => 'no command',
  ),
  array (
    'info' => 'Gathering release information, please wait...',
    'cmd' => 'no command',
  ),
  array (
    0 => 3,
    1 => 'Downloading "http://pear.php.net/get/Archive_Tar-1.5.0a1.tgz"',
  ),
  array (
    0 => 1,
    1 => 'downloading Archive_Tar-1.5.0a1.tgz ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download Archive_Tar-1.5.0a1.tgz (687 bytes)',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '...done: 687 bytes',
  ),
  array (
    0 => 3,
    1 => 'Downloading "http://pear.php.net/get/APC-1.4.0a1.tgz"',
  ),
  array (
    0 => 1,
    1 => 'downloading APC-1.4.0a1.tgz ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download APC-1.4.0a1.tgz (514 bytes)',
  ),
  array (
    0 => 1,
    1 => '...done: 514 bytes',
  ),
  array (
    'info' => 'File ' . $temp_path . DIRECTORY_SEPARATOR . 'Archive_Tar-1.5.0a1.tgz downloaded',
    'cmd' => 'download',
  ),
  array (
    'info' => 'File ' . $temp_path . DIRECTORY_SEPARATOR . 'APC-1.4.0a1.tgz downloaded',
    'cmd' => 'download',
  ),
), $fakelog->getLog(), 'log');

$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'APC-1.4.0a1.tgz', 'APC 1.4.0a1');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'Archive_Tar-1.5.0a1.tgz', 'Archive_Tar 1.5.0a1');

$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 'http://pear.php.net/rest/p/packages.xml',
    1 => '200',
  ),
  1 =>
  array (
    0 => 'http://pear.php.net/rest/r/archive_tar/allreleases.xml',
    1 => '200',
  ),
  2 =>
  array (
    0 => 'http://pear.php.net/rest/p/archive_tar/info.xml',
    1 => '200',
  ),
  3 =>
  array (
    0 => 'http://pear.php.net/rest/r/archive_tar/1.5.0a1.xml',
    1 => '200',
  ),
  4 =>
  array (
    0 => 'http://pear.php.net/rest/r/archive_tar/deps.1.5.0a1.txt',
    1 => '200',
  ),
  5 =>
  array (
    0 => 'http://pear.php.net/rest/r/apc/allreleases.xml',
    1 => '200',
  ),
  6 =>
  array (
    0 => 'http://pear.php.net/rest/p/apc/info.xml',
    1 => '200',
  ),
  7 =>
  array (
    0 => 'http://pear.php.net/rest/r/apc/1.4.0a1.xml',
    1 => '200',
  ),
  8 =>
  array (
    0 => 'http://pear.php.net/rest/r/apc/deps.1.4.0a1.txt',
    1 => '200',
  ),
)
, $pearweb->getRESTCalls(), 'rest calls');
chdir($save);

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
