--TEST--
PEAR_PackageFile_Parser_v2->installBinary()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
@include_once 'PEAR.php';
if (!class_exists('PEAR')) {
    die('skip PEAR.php must be in include_path');
}
if (!OS_WINDOWS) {
    echo 'skip can only run test on Windows OS';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
require_once 'PEAR/ChannelFile.php';
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_installBinary'. DIRECTORY_SEPARATOR . 'foo_win-1.1.0.tgz';
$pathtopackagexml2 = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_installBinary'. DIRECTORY_SEPARATOR . 'foo_linux-1.1.0.tgz';
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/foo_win-1.1.0.tgz', $pathtopackagexml);
$GLOBALS['pearweb']->addXmlrpcConfig('grob', 'package.getDownloadURL',
    array(array('channel' => 'grob', 'package' => 'foo_win', 'version' => '1.1.0'), 'stable'),
    array('version' => '1.1.0',
          'info' =>
          '<?xml version="1.0"?>
<package version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd">
 <name>foo_win</name>
 <channel>grob</channel>
 <summary>foo binary</summary>
 <description>foo binary for windows</description>
 <lead>
  <name>Greg Beaver</name>
  <user>cellog</user>
  <email>cellog@php.net</email>
  <active>yes</active>
 </lead>
 <date>2004-12-05</date>
 <time>16:23:15</time>
 <version>
  <release>1.1.0</release>
  <api>1.1.0</api>
 </version>
 <stability>
  <release>stable</release>
  <api>stable</api>
 </stability>
 <license>PHP License</license>
 <notes>foo_win</notes>
 <contents>
  <dir name="/">
   <file md5sum="c81e728d9d4c2f636f067f89cc14862c" name="foo.dll" role="ext" />
  </dir>
 </contents>
 <dependencies>
  <required>
   <php>
    <min>4.3.0</min>
    <max>6.0.0</max>
   </php>
   <pearinstaller>
    <min>1.4.0dev13</min>
   </pearinstaller>
   <os>
    <name>windows</name>
   </os>
  </required>
 </dependencies>
 <providesextension>foo</providesextension>
 <srcpackage>foo</srcpackage>
 <extbinrelease>
  <installconditions />
  <filelist />
 </extbinrelease>
</package>',
          'url' => 'http://www.example.com/foo_win-1.1.0'));
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/foo_linux-1.1.0.tgz', $pathtopackagexml2);
$GLOBALS['pearweb']->addXmlrpcConfig('grob', 'package.getDownloadURL',
    array(array('channel' => 'grob', 'package' => 'foo_linux', 'version' => '1.1.0'), 'stable'),
    array('version' => '1.1.0',
          'info' =>
          '<?xml version="1.0"?>
<package version="2.0" xmlns="http://pear.php.net/dtd/package-2.0" xmlns:tasks="http://pear.php.net/dtd/tasks-1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd">
 <name>foo_linux</name>
 <channel>grob</channel>
 <summary>foo binary</summary>
 <description>foo binary for windows</description>
 <lead>
  <name>Greg Beaver</name>
  <user>cellog</user>
  <email>cellog@php.net</email>
  <active>yes</active>
 </lead>
 <date>2004-12-05</date>
 <time>16:22:47</time>
 <version>
  <release>1.1.0</release>
  <api>1.1.0</api>
 </version>
 <stability>
  <release>stable</release>
  <api>stable</api>
 </stability>
 <license>PHP License</license>
 <notes>foo_linux</notes>
 <contents>
  <dir name="/">
   <file md5sum="c81e728d9d4c2f636f067f89cc14862c" name="foo.so" role="ext" />
  </dir>
 </contents>
 <dependencies>
  <required>
   <php>
    <min>4.3.0</min>
    <max>6.0.0</max>
   </php>
   <pearinstaller>
    <min>1.4.0dev13</min>
   </pearinstaller>
   <os>
    <name>linux</name>
   </os>
  </required>
 </dependencies>
 <providesextension>foo</providesextension>
 <srcpackage>foo</srcpackage>
 <extbinrelease>
  <installconditions />
  <filelist />
 </extbinrelease>
</package>',
          'url' => 'http://www.example.com/foo_linux-1.1.0'));

$_test_dep->setPHPVersion('4.3.9');
$_test_dep->setPEARVersion('1.4.0a1');

$cf = new PEAR_ChannelFile;
$cf->setName('grob');
$cf->setServer('grob');
$cf->setSummary('grob');
$cf->setDefaultPEARProtocols();
$reg = &$config->getRegistry();
$reg->addChannel($cf);
$phpunit->assertNoErrors('channel add');

$a = new test_PEAR_Installer($fakelog);
$pf = new test_PEAR_PackageFile_v2;
$pf->setConfig($config);
$pf->setPackageType('extsrc');
$pf->addBinarypackage('foo_win');
$pf->setPackage('foo');
$pf->setChannel('grob');
$pf->setAPIStability('stable');
$pf->setReleaseStability('stable');
$pf->setAPIVersion('1.0.0');
$pf->setReleaseVersion('1.1.0');
$pf->setDate('2004-11-12');
$pf->setDescription('foo source');
$pf->setSummary('foo');
$pf->setLicense('PHP License');
$pf->setLogger($fakelog);
$pf->clearContents();
$pf->addFile('', 'foo.grop', array('role' => 'src'));
$pf->addBinarypackage('foo_linux');
$pf->addMaintainer('lead', 'cellog', 'Greg Beaver', 'cellog@php.net');
$pf->setNotes('blah');
$pf->setPearinstallerDep('1.4.0a1');
$pf->setPhpDep('4.2.0', '5.0.0');
$pf->setProvidesExtension('foo');

$phpunit->assertNotFalse($pf->validate(), 'first pf');

$dp = newFakeDownloaderPackage(array());
$dp->setPackageFile($pf);
$b = array(&$dp);
$a->setDownloadedPackages($b);
$_test_dep->setOs('windows');
$pf->installBinary($a);
$phpunit->assertNoErrors('post-install');
$dld = $GLOBALS['last_dl']->getDownloadDir();
$cleandld = str_replace('\\\\', '\\', $GLOBALS['last_dl']->getDownloadDir());
if (OS_WINDOWS) {
    $phpunit->assertEquals(array (
      array (
        0 => 0,
        1 => 'Attempting to download binary version of extension "foo"',
      ),
      array (
        0 => 3,
        1 => 'Downloading "http://www.example.com/foo_win-1.1.0.tgz"',
      ),
      array (
        0 => 1,
        1 => 'downloading foo_win-1.1.0.tgz ...',
      ),
      array (
        0 => 1,
        1 => 'Starting to download foo_win-1.1.0.tgz (726 bytes)',
      ),
      array (
        0 => 1,
        1 => '.',
      ),
      array (
        0 => 1,
        1 => '...done: 726 bytes',
      ),
      array (
        0 => 3,
        1 => '+ cp ' . $cleandld . DIRECTORY_SEPARATOR . 'foo_win-1.1.0' . DIRECTORY_SEPARATOR .
            'foo.dll ' . $ext_dir . DIRECTORY_SEPARATOR . '.tmpfoo.dll',
      ),
      array (
        0 => 2,
        1 => 'md5sum ok: ' . $ext_dir . DIRECTORY_SEPARATOR . 'foo.dll',
      ),
      array (
        0 => 3,
        1 => 'adding to transaction: rename ' . $ext_dir . DIRECTORY_SEPARATOR .
            '.tmpfoo.dll ' . $ext_dir . DIRECTORY_SEPARATOR . 'foo.dll 1',
      ),
      array (
        0 => 3,
        1 => 'adding to transaction: installed_as foo.dll ' . $ext_dir . DIRECTORY_SEPARATOR .
            'foo.dll ' . $ext_dir . ' ' . DIRECTORY_SEPARATOR
      ),
      array (
        0 => 2,
        1 => 'about to commit 2 file operations',
      ),
      array (
        0 => 3,
        1 => '+ mv ' . $ext_dir . DIRECTORY_SEPARATOR . '.tmpfoo.dll ' .
            $ext_dir . DIRECTORY_SEPARATOR . 'foo.dll',
      ),
      array (
        0 => 2,
        1 => 'successfully committed 2 file operations',
      ),
      array (
        0 => 0,
        1 => 'Download and install of binary extension "grob/foo_win" successful',
      ),
    ), $fakelog->getLog(), 'log');
} else {
    $phpunit->assertEquals(array (
      0 => 
      array (
        0 => 0,
        1 => 'Attempting to download binary version of extension "foo"',
      ),
      1 => 
      array (
        0 => 3,
        1 => '+ tmp dir created at ' . $dld,
      ),
      2 => 
      array (
        0 => 1,
        1 => 'downloading foo_win-1.1.0.tgz ...',
      ),
      3 => 
      array (
        0 => 1,
        1 => 'Starting to download foo_win-1.1.0.tgz (726 bytes)',
      ),
      4 => 
      array (
        0 => 1,
        1 => '.',
      ),
      5 => 
      array (
        0 => 1,
        1 => '...done: 725 bytes',
      ),
      6 => 
      array (
        0 => 3,
        1 => '+ cp ' . $cleandld . DIRECTORY_SEPARATOR . 'foo_win-1.1.0' . DIRECTORY_SEPARATOR .
            'foo.dll ' . $ext_dir . DIRECTORY_SEPARATOR . '.tmpfoo.dll',
      ),
      7 => 
      array (
        0 => 2,
        1 => 'md5sum ok: ' . $ext_dir . DIRECTORY_SEPARATOR . 'foo.dll',
      ),
      8 =>
      array (
        0 => 3,
        1 => 'adding to transaction: chmod 644 ' . $ext_dir . DIRECTORY_SEPARATOR . '.tmpfoo.dll',
      ),
      9 => 
      array (
        0 => 3,
        1 => 'adding to transaction: rename ' . $ext_dir . DIRECTORY_SEPARATOR .
            '.tmpfoo.dll ' . $ext_dir . DIRECTORY_SEPARATOR . 'foo.dll 1',
      ),
      10 => 
      array (
        0 => 3,
        1 => 'adding to transaction: installed_as foo.dll ' . $ext_dir . DIRECTORY_SEPARATOR .
            'foo.dll ' . $ext_dir . ' ' . DIRECTORY_SEPARATOR
      ),
      11 => 
      array (
        0 => 2,
        1 => 'about to commit 3 file operations',
      ),
      12 =>
      array (
        0 => 3,
        1 => '+ chmod 644 ' . $ext_dir . DIRECTORY_SEPARATOR . '.tmpfoo.dll',
      ),
      13 => 
      array (
        0 => 3,
        1 => '+ mv ' . $ext_dir . DIRECTORY_SEPARATOR . '.tmpfoo.dll ' .
            $ext_dir . DIRECTORY_SEPARATOR . 'foo.dll',
      ),
      14 => 
      array (
        0 => 2,
        1 => 'successfully committed 3 file operations',
      ),
      15 => 
      array (
        0 => 0,
        1 => 'Download and install of binary extension "grob/foo_win" successful',
      ),
    ), $fakelog->getLog(), 'log');
}
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'setup',
    1 => 'self',
  ),
  1 => 
  array (
    0 => 'saveas',
    1 => 'foo_win-1.1.0.tgz',
  ),
  2 => 
  array (
    0 => 'start',
    1 => 
    array (
      0 => 'foo_win-1.1.0.tgz',
      1 => '726',
    ),
  ),
  3 => 
  array (
    0 => 'bytesread',
    1 => 726,
  ),
  4 => 
  array (
    0 => 'done',
    1 => 726,
  ),
), $fakelog->getDownload(), 'log');
$phpunit->assertFileExists($ext_dir . DIRECTORY_SEPARATOR . 'foo.dll', 'not installed');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
