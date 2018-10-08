--TEST--
search command
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


$pf = new PEAR_PackageFile_v1;
$pf->setConfig($config);
$pf->setPackage('Archive_Tar');
$pf->setSummary('foo');
$pf->setDate(date('Y-m-d'));
$pf->setDescription('foo');
$pf->setVersion('1.0.0');
$pf->setState('stable');
$pf->setLicense('PHP License');
$pf->setNotes('foo');
$pf->addMaintainer('lead', 'cellog', 'Greg', 'cellog@php.net');
$pf->addFile('', 'foo.dat', array('role' => 'data'));
$pf->clearDeps();
$pf->validate();
$phpunit->assertNoErrors('setup');
$reg->addPackage2($pf);

$pearweb->addRESTConfig("http://pear.php.net/rest/p/packages.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>pear.php.net</c>
 <p>Archive_Tar</p>
 <p>AsteriskManager</p>
 <p>Auth</p>
 <p>Auth_HTTP</p>
 <p>Auth_PrefManager</p>
 <p>Auth_PrefManager2</p>
 <p>Auth_RADIUS</p>
 <p>Auth_SASL</p>
 <p>Cache</p>
 <p>Cache_Lite</p>
 <p>CodeGen</p>
 <p>CodeGen_MySQL</p>
 <p>CodeGen_MySQL_Plugin</p>
 <p>CodeGen_MySQL_UDF</p>
 <p>CodeGen_PECL</p>
 <p>Config</p>
 <p>Console_Color</p>
 <p>System_Socket</p>
 <p>System_WinDrives</p>
 <p>Testing_DocTest</p>
 <p>Testing_FIT</p>
 <p>Testing_Selenium</p>
 <p>Text_CAPTCHA</p>
 <p>Text_CAPTCHA_Numeral</p>
 <p>Text_Diff</p>
 <p>Text_Figlet</p>
 <p>Text_Highlighter</p>
 <p>Text_Huffman</p>
 <p>Text_LanguageDetect</p>
 <p>Text_Password</p>
 <p>Text_PathNavigator</p>
 <p>Text_Spell_Audio</p>
 <p>Text_Statistics</p>
 <p>Text_TeXHyphen</p>
 <p>Text_Wiki</p>
 <p>Text_Wiki_BBCode</p>
 <p>Text_Wiki_Cowiki</p>
 <p>Text_Wiki_Creole</p>
 <p>Text_Wiki_Doku</p>
 <p>Text_Wiki_Mediawiki</p>
 <p>Text_Wiki_Tiki</p>
 <p>Translation</p>
 <p>Translation2</p>
 <p>Tree</p>
 <p>UDDI</p>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/archive_tar/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
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
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Archive_Tar</p>
 <c>pear.php.net</c>
 <r><v>1.3.2</v><s>stable</s></r>
 <r><v>1.3.1</v><s>stable</s></r>
 <r><v>1.3.0</v><s>stable</s></r>
 <r><v>1.2</v><s>stable</s></r>
 <r><v>1.1</v><s>stable</s></r>
 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.10-b1</v><s>beta</s></r>
 <r><v>0.9</v><s>stable</s></r>
 <r><v>0.4</v><s>stable</s></r>
 <r><v>0.3</v><s>stable</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_tar/deps.1.3.2.txt", 'a:1:{s:8:"required";a:2:{s:3:"php";a:1:{s:3:"min";s:5:"4.0.0";}s:13:"pearinstaller";a:1:{s:3:"min";s:7:"1.4.0b1";}}}', 'text/xml');


$e = $command->run('search', array(), array('Ar'));
$phpunit->assertNoErrors('after');
$phpunit->assertTrue($e, 'after');
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 'Retrieving data...0%',
    1 => true,
  ),
  1 =>
  array (
    'info' =>
    array (
      'caption' => 'Matched packages, channel pear.php.net:',
      'border' => true,
      'headline' =>
      array (
        0 => 'Package',
        1 => 'Stable/(Latest)',
        2 => 'Local',
      ),
      'channel' => 'pear.php.net',
      'data' =>
      array (
        'File Formats' =>
        array (
          0 =>
          array (
            0 => 'Archive_Tar',
            1 => '1.3.2 (stable)',
            2 => '1.0.0',
            3 => 'Tar file management class',
          ),
        ),
      ),
    ),
    'cmd' => 'search',
  ),
), $fakelog->getLog(), 'log after');

$e = $command->run('search', array(), array('XZ'));
$phpunit->assertNoErrors('errors');
$phpunit->assertEquals(array (
  0 =>
  array (
    0 => 'Retrieving data...0%',
    1 => true,
  ),
  1 =>
   array (
    'info' => 'no packages found that match pattern "xz", for channel pear.php.net.',
    'cmd' => 'no command',
   ),
), $fakelog->getLog(), 'log notfound');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
