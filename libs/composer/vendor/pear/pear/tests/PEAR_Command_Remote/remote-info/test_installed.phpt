--TEST--
remote-info command, package is installed
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
$chan->setBaseUrl('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pf = new PEAR_PackageFile_v1;
$pf->setConfig($config);
$pf->setPackage('Archive_Zip');
$pf->setSummary('foo');
$pf->setDate(date('Y-m-d'));
$pf->setDescription('foo');
$pf->setVersion('1.0.0');
$pf->setState('stable');
$pf->setLicense('PHP License');
$pf->setNotes('foo');
$pf->addMaintainer('lead', 'cellog', 'Greg', 'cellog@php.net');
$pf->addFile('', 'foo.dat', array('role' => 'data'));
$pf->validate();

$phpunit->assertNoErrors('setup');
$reg->addPackage2($pf);


/*
$pearweb->addXmlrpcConfig("pear.php.net", "package.info",     array(
    0 =>
        "Archive_Zip",
    ),     array(
    'packageid' =>
        "252",
    'name' =>
        "Archive_Zip",
    'type' =>
        "pear",
    'categoryid' =>
        "33",
    'category' =>
        "File Formats",
    'stable' =>
        "",
    'license' =>
        "PHP License",
    'summary' =>
        "Zip file management class",
    'homepage' =>
        "",
    'description' =>
        "This class provides handling of zip files in PHP.
It supports creating, listing, extracting and adding to zip files.",
    'cvs_link' =>
        "http://cvs.php.net/cvs.php/pear/Archive_Zip",
    'doc_link' =>
        "",
    'releases' =>
        array(
        '1.3.3.1' =>
            array(
            'id' =>
                "1803",
            'doneby' =>
                "cellog",
            'license' =>
                "",
            'summary' =>
                "",
            'description' =>
                "",
            'releasedate' =>
                "2004-11-12 02:04:57",
            'releasenotes' =>
                "add RunTest.php to package.xml, make run-tests display failed tests, and use ui",
            'state' =>
                "stable",
            'deps' =>
                array(
                0 =>
                    array(
                    'type' =>
                        "php",
                    'relation' =>
                        "ge",
                    'version' =>
                        "4.2",
                    'name' =>
                        "PHP",
                    'optional' =>
                        "0",
                    ),
                1 =>
                    array(
                    'type' =>
                        "pkg",
                    'relation' =>
                        "ge",
                    'version' =>
                        "1.1",
                    'name' =>
                        "Archive_Tar",
                    'optional' =>
                        "0",
                    ),
                2 =>
                    array(
                    'type' =>
                        "pkg",
                    'relation' =>
                        "ge",
                    'version' =>
                        "1.2",
                    'name' =>
                        "Console_Getopt",
                    'optional' =>
                        "0",
                    ),
                3 =>
                    array(
                    'type' =>
                        "pkg",
                    'relation' =>
                        "ge",
                    'version' =>
                        "1.0.4",
                    'name' =>
                        "XML_RPC",
                    'optional' =>
                        "0",
                    ),
                4 =>
                    array(
                    'type' =>
                        "ext",
                    'relation' =>
                        "has",
                    'version' =>
                        "",
                    'name' =>
                        "xml",
                    'optional' =>
                        "0",
                    ),
                5 =>
                    array(
                    'type' =>
                        "ext",
                    'relation' =>
                        "has",
                    'version' =>
                        "",
                    'name' =>
                        "pcre",
                    'optional' =>
                        "0",
                    ),
                ),
            ),
        ),
    'notes' =>
        array(
        ),
    ));
*/

$pearweb->addRESTConfig("http://pear.php.net/rest/p/archive_zip/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>Archive_Zip</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/File+Formats">File Formats</ca>
 <l>LGPL</l>
 <s>Zip file archiving management class</s>
 <d>------------------------------------
The PECL Zip extension is faster and more current than this native PHP library. If possible, you should use the PECL extension instead:

  http://pecl.php.net/zip
------------------------------------

Archive_Zip

This class provides the ability to handle Zip files using native PHP. No extra libraries are needed. This class offers tools that can create, list, extract, unpack, append Zip files.

Vincent Blavet wrote this application, but it\'s unmaintained currently. Some ideas for future work (feel free to volunteer):
 A) Add driver for use with pecl/zip
 B) Collaborate with File_Archive
 C) Add documentation

See Also:
http://en.wikipedia.org/wiki/ZIP_%28file_format%29

For a PEAR alternative to this class, consider using File_Archive instead.</d>
 <r xlink:href="/rest/r/archive_zip"/>
 <dc>pecl.php.net</dc>
 <dp> zip</dp>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_zip/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Archive_Zip</p>
 <c>pear.php.net</c>
 <r><v>0.1.1</v><s>beta</s></r>
 <r><v>0.1.0</v><s>beta</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_zip/deps.0.1.1.txt", 'b:0;', 'text/xml');
$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_zip/deps.0.1.0.txt", 'b:0;', 'text/xml');

$e = $command->run('remote-info', array(), array('Archive_Zip'));
$phpunit->assertNoErrors('Archive_Zip');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' =>
    array (
      'name' => 'Archive_Zip',
      'channel' => 'pear.php.net',
      'category' => 'File Formats',
      'stable' => '',
      'license' => 'LGPL',
      'summary' => 'Zip file archiving management class',
      'description' => '------------------------------------
The PECL Zip extension is faster and more current than this native PHP library. If possible, you should use the PECL extension instead:

  http://pecl.php.net/zip
------------------------------------

Archive_Zip

This class provides the ability to handle Zip files using native PHP. No extra libraries are needed. This class offers tools that can create, list, extract, unpack, append Zip files.

Vincent Blavet wrote this application, but it\'s unmaintained currently. Some ideas for future work (feel free to volunteer):
 A) Add driver for use with pecl/zip
 B) Collaborate with File_Archive
 C) Add documentation

See Also:
http://en.wikipedia.org/wiki/ZIP_%28file_format%29

For a PEAR alternative to this class, consider using File_Archive instead.',
      'releases' =>
      array (
      ),
      'deprecated' =>
      array (
        'channel' => 'pecl.php.net',
        'package' => 'zip',
      ),
      'installed' => '1.0.0',
    ),
    'cmd' => 'remote-info',
  ),
), $fakelog->getLog(), 'Archive_Zip log');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
