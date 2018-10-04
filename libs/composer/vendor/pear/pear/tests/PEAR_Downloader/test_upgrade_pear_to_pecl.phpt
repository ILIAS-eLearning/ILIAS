--TEST--
PEAR_Installer->install() upgrade a pecl package when it switches from a pear channel to a pecl channel
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
$_test_dep->setPEARVersion('1.4.0a1');
$_test_dep->setPHPVersion('5.0.3');

$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$chan = $reg->getChannel('pecl.php.net');
$chan->setBaseURL('REST1.0', 'http://pecl.php.net/rest/');
$reg->updateChannel($chan);

$packageDir        = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'test_upgrade_pecl'. DIRECTORY_SEPARATOR;
$pathtopackagexml  = $packageDir . 'package.xml';
$pathtopackagexml2 = $packageDir . 'SQLite-1.0.4.tgz';

$pearweb->addHtmlConfig('http://pecl.php.net/get/SQLite-1.0.4.tgz', $pathtopackagexml2);

$pearweb->addRESTConfig("http://pecl.php.net/rest/r/sqlite/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>SQLite</p>
 <c>pecl.php.net</c>
 <r><v>1.0.4</v><s>stable</s></r>
 <r><v>1.0.3</v><s>stable</s></r>
 <r><v>1.0.2</v><s>stable</s></r>
 <r><v>1.0.1</v><s>stable</s></r>

 <r><v>1.0</v><s>stable</s></r>
 <r><v>0.9b</v><s>beta</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pecl.php.net/rest/p/sqlite/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>SQLite</n>
 <c>pecl.php.net</c>
 <ca xlink:href="/rest/c/Database">Database</ca>
 <l>PHP</l>
 <s>SQLite database bindings</s>

 <d>SQLite is a C library that implements an embeddable SQL database engine.
Programs that link with the SQLite library can have SQL database access
without running a separate RDBMS process.
This extension allows you to access SQLite databases from within PHP.
Windows binary for PHP 4.3 is available from:
http://snaps.php.net/win32/PECL_4_3/php_sqlite.dll
**Note that this extension is built into PHP 5 by default**</d>
 <r xlink:href="/rest/r/sqlite"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pecl.php.net/rest/r/sqlite/1.0.4.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/sqlite">SQLite</p>
 <c>pecl.php.net</c>
 <v>1.0.4</v>
 <st>stable</st>
 <l>PHP</l>

 <m>wez</m>
 <s>SQLite database bindings</s>
 <d>SQLite is a C library that implements an embeddable SQL database engine.
Programs that link with the SQLite library can have SQL database access
without running a separate RDBMS process.
This extension allows you to access SQLite databases from within PHP.
Windows binary for PHP 4.3 is available from:
http://snaps.php.net/win32/PECL_4_3/php_sqlite.dll
**Note that this extension is built into PHP 5 by default**
</d>
 <da>2004-07-18 10:32:00</da>
 <n>Upgraded libsqlite to version 2.8.14

&quot;Fixed&quot; the bug where calling sqlite_query() with multiple SQL statements in a
single string would not work if you looked at the return value.  The fix for
this case is to use the new sqlite_exec() function instead. (Stas)

</n>
 <f>371189</f>
 <g>http://pecl.php.net/get/SQLite-1.0.4</g>
 <x xlink:href="package.1.0.4.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pecl.php.net/rest/r/sqlite/deps.1.0.4.txt", 'b:0;', 'text/plain');

$dp = new test_PEAR_Downloader($fakelog, array('upgrade' => true), $config);
$result = $dp->download(array($pathtopackagexml));

$installer = new test_PEAR_Installer($ui);
$installer->setOptions(array());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$installer->install($result[0]);

$phpunit->assertNoErrors('setup for upgrade');

$fakelog->getLog();
$fakelog->getDownload();

$phpunit->assertEquals(array('sqlite'), $reg->listPackages(),       'pear');
$phpunit->assertEquals(array(),         $reg->listPackages('pecl'), 'pecl');

$dp = new test_PEAR_Downloader($fakelog, array('upgrade' => true), $config);
$phpunit->assertNoErrors('after create');

$result = $dp->download(array('pecl.php.net/SQLite'));
$phpunit->assertEquals(1, count($result), 'return');
$phpunit->assertIsa('test_PEAR_Downloader_Package', $result[0], 'right class');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf = $result[0]->getPackageFile(), 'right kind of pf');
$phpunit->assertEquals('SQLite', $pf->getPackage(), 'right package');
$phpunit->assertEquals('pecl.php.net', $pf->getChannel(), 'right channel');

$dlpackages = $dp->getDownloadedPackages();
$phpunit->assertEquals(1, count($dlpackages), 'downloaded packages count');
$phpunit->assertEquals(3, count($dlpackages[0]), 'internals package count');
$phpunit->assertEquals(array('file', 'info', 'pkg'), array_keys($dlpackages[0]), 'indexes');
$phpunit->assertIsa('PEAR_PackageFile_v2', $dlpackages[0]['info'], 'info');
$phpunit->assertEquals('SQLite',           $dlpackages[0]['pkg'], 'SQLite');

$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(0, count($after), 'after getdp count');

$phpunit->assertEquals(array (
  array (
    0 => 3,
    1 => 'Downloading "http://pecl.php.net/get/SQLite-1.0.4.tgz"',
  ),
  array (
    0 => 1,
    1 => 'downloading SQLite-1.0.4.tgz ...',
  ),
  array (
    0 => 1,
    1 => 'Starting to download SQLite-1.0.4.tgz (371,001 bytes)',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '.',
  ),
  array (
    0 => 1,
    1 => '...done: 371,001 bytes',
  ),
), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals( array (
  0 =>
  array (
    0 => 'setup',
    1 => 'self',
  ),
  1 =>
  array (
    0 => 'saveas',
    1 => 'SQLite-1.0.4.tgz',
  ),
  2 =>
  array (
    0 => 'start',
    1 =>
    array (
      0 => 'SQLite-1.0.4.tgz',
      1 => '371001',
    ),
  ),
  3 =>
  array (
    0 => 'bytesread',
    1 => 1024,
  ),
  4 =>
  array (
    0 => 'bytesread',
    1 => 2048,
  ),
  5 =>
  array (
    0 => 'bytesread',
    1 => 3072,
  ),
  6 =>
  array (
    0 => 'bytesread',
    1 => 4096,
  ),
  7 =>
  array (
    0 => 'bytesread',
    1 => 5120,
  ),
  8 =>
  array (
    0 => 'bytesread',
    1 => 6144,
  ),
  9 =>
  array (
    0 => 'bytesread',
    1 => 7168,
  ),
  10 =>
  array (
    0 => 'bytesread',
    1 => 8192,
  ),
  11 =>
  array (
    0 => 'bytesread',
    1 => 9216,
  ),
  12 =>
  array (
    0 => 'bytesread',
    1 => 10240,
  ),
  13 =>
  array (
    0 => 'bytesread',
    1 => 11264,
  ),
  14 =>
  array (
    0 => 'bytesread',
    1 => 12288,
  ),
  15 =>
  array (
    0 => 'bytesread',
    1 => 13312,
  ),
  16 =>
  array (
    0 => 'bytesread',
    1 => 14336,
  ),
  17 =>
  array (
    0 => 'bytesread',
    1 => 15360,
  ),
  18 =>
  array (
    0 => 'bytesread',
    1 => 16384,
  ),
  19 =>
  array (
    0 => 'bytesread',
    1 => 17408,
  ),
  20 =>
  array (
    0 => 'bytesread',
    1 => 18432,
  ),
  21 =>
  array (
    0 => 'bytesread',
    1 => 19456,
  ),
  22 =>
  array (
    0 => 'bytesread',
    1 => 20480,
  ),
  23 =>
  array (
    0 => 'bytesread',
    1 => 21504,
  ),
  24 =>
  array (
    0 => 'bytesread',
    1 => 22528,
  ),
  25 =>
  array (
    0 => 'bytesread',
    1 => 23552,
  ),
  26 =>
  array (
    0 => 'bytesread',
    1 => 24576,
  ),
  27 =>
  array (
    0 => 'bytesread',
    1 => 25600,
  ),
  28 =>
  array (
    0 => 'bytesread',
    1 => 26624,
  ),
  29 =>
  array (
    0 => 'bytesread',
    1 => 27648,
  ),
  30 =>
  array (
    0 => 'bytesread',
    1 => 28672,
  ),
  31 =>
  array (
    0 => 'bytesread',
    1 => 29696,
  ),
  32 =>
  array (
    0 => 'bytesread',
    1 => 30720,
  ),
  33 =>
  array (
    0 => 'bytesread',
    1 => 31744,
  ),
  34 =>
  array (
    0 => 'bytesread',
    1 => 32768,
  ),
  35 =>
  array (
    0 => 'bytesread',
    1 => 33792,
  ),
  36 =>
  array (
    0 => 'bytesread',
    1 => 34816,
  ),
  37 =>
  array (
    0 => 'bytesread',
    1 => 35840,
  ),
  38 =>
  array (
    0 => 'bytesread',
    1 => 36864,
  ),
  39 =>
  array (
    0 => 'bytesread',
    1 => 37888,
  ),
  40 =>
  array (
    0 => 'bytesread',
    1 => 38912,
  ),
  41 =>
  array (
    0 => 'bytesread',
    1 => 39936,
  ),
  42 =>
  array (
    0 => 'bytesread',
    1 => 40960,
  ),
  43 =>
  array (
    0 => 'bytesread',
    1 => 41984,
  ),
  44 =>
  array (
    0 => 'bytesread',
    1 => 43008,
  ),
  45 =>
  array (
    0 => 'bytesread',
    1 => 44032,
  ),
  46 =>
  array (
    0 => 'bytesread',
    1 => 45056,
  ),
  47 =>
  array (
    0 => 'bytesread',
    1 => 46080,
  ),
  48 =>
  array (
    0 => 'bytesread',
    1 => 47104,
  ),
  49 =>
  array (
    0 => 'bytesread',
    1 => 48128,
  ),
  50 =>
  array (
    0 => 'bytesread',
    1 => 49152,
  ),
  51 =>
  array (
    0 => 'bytesread',
    1 => 50176,
  ),
  52 =>
  array (
    0 => 'bytesread',
    1 => 51200,
  ),
  53 =>
  array (
    0 => 'bytesread',
    1 => 52224,
  ),
  54 =>
  array (
    0 => 'bytesread',
    1 => 53248,
  ),
  55 =>
  array (
    0 => 'bytesread',
    1 => 54272,
  ),
  56 =>
  array (
    0 => 'bytesread',
    1 => 55296,
  ),
  57 =>
  array (
    0 => 'bytesread',
    1 => 56320,
  ),
  58 =>
  array (
    0 => 'bytesread',
    1 => 57344,
  ),
  59 =>
  array (
    0 => 'bytesread',
    1 => 58368,
  ),
  60 =>
  array (
    0 => 'bytesread',
    1 => 59392,
  ),
  61 =>
  array (
    0 => 'bytesread',
    1 => 60416,
  ),
  62 =>
  array (
    0 => 'bytesread',
    1 => 61440,
  ),
  63 =>
  array (
    0 => 'bytesread',
    1 => 62464,
  ),
  64 =>
  array (
    0 => 'bytesread',
    1 => 63488,
  ),
  65 =>
  array (
    0 => 'bytesread',
    1 => 64512,
  ),
  66 =>
  array (
    0 => 'bytesread',
    1 => 65536,
  ),
  67 =>
  array (
    0 => 'bytesread',
    1 => 66560,
  ),
  68 =>
  array (
    0 => 'bytesread',
    1 => 67584,
  ),
  69 =>
  array (
    0 => 'bytesread',
    1 => 68608,
  ),
  70 =>
  array (
    0 => 'bytesread',
    1 => 69632,
  ),
  71 =>
  array (
    0 => 'bytesread',
    1 => 70656,
  ),
  72 =>
  array (
    0 => 'bytesread',
    1 => 71680,
  ),
  73 =>
  array (
    0 => 'bytesread',
    1 => 72704,
  ),
  74 =>
  array (
    0 => 'bytesread',
    1 => 73728,
  ),
  75 =>
  array (
    0 => 'bytesread',
    1 => 74752,
  ),
  76 =>
  array (
    0 => 'bytesread',
    1 => 75776,
  ),
  77 =>
  array (
    0 => 'bytesread',
    1 => 76800,
  ),
  78 =>
  array (
    0 => 'bytesread',
    1 => 77824,
  ),
  79 =>
  array (
    0 => 'bytesread',
    1 => 78848,
  ),
  80 =>
  array (
    0 => 'bytesread',
    1 => 79872,
  ),
  81 =>
  array (
    0 => 'bytesread',
    1 => 80896,
  ),
  82 =>
  array (
    0 => 'bytesread',
    1 => 81920,
  ),
  83 =>
  array (
    0 => 'bytesread',
    1 => 82944,
  ),
  84 =>
  array (
    0 => 'bytesread',
    1 => 83968,
  ),
  85 =>
  array (
    0 => 'bytesread',
    1 => 84992,
  ),
  86 =>
  array (
    0 => 'bytesread',
    1 => 86016,
  ),
  87 =>
  array (
    0 => 'bytesread',
    1 => 87040,
  ),
  88 =>
  array (
    0 => 'bytesread',
    1 => 88064,
  ),
  89 =>
  array (
    0 => 'bytesread',
    1 => 89088,
  ),
  90 =>
  array (
    0 => 'bytesread',
    1 => 90112,
  ),
  91 =>
  array (
    0 => 'bytesread',
    1 => 91136,
  ),
  92 =>
  array (
    0 => 'bytesread',
    1 => 92160,
  ),
  93 =>
  array (
    0 => 'bytesread',
    1 => 93184,
  ),
  94 =>
  array (
    0 => 'bytesread',
    1 => 94208,
  ),
  95 =>
  array (
    0 => 'bytesread',
    1 => 95232,
  ),
  96 =>
  array (
    0 => 'bytesread',
    1 => 96256,
  ),
  97 =>
  array (
    0 => 'bytesread',
    1 => 97280,
  ),
  98 =>
  array (
    0 => 'bytesread',
    1 => 98304,
  ),
  99 =>
  array (
    0 => 'bytesread',
    1 => 99328,
  ),
  100 =>
  array (
    0 => 'bytesread',
    1 => 100352,
  ),
  101 =>
  array (
    0 => 'bytesread',
    1 => 101376,
  ),
  102 =>
  array (
    0 => 'bytesread',
    1 => 102400,
  ),
  103 =>
  array (
    0 => 'bytesread',
    1 => 103424,
  ),
  104 =>
  array (
    0 => 'bytesread',
    1 => 104448,
  ),
  105 =>
  array (
    0 => 'bytesread',
    1 => 105472,
  ),
  106 =>
  array (
    0 => 'bytesread',
    1 => 106496,
  ),
  107 =>
  array (
    0 => 'bytesread',
    1 => 107520,
  ),
  108 =>
  array (
    0 => 'bytesread',
    1 => 108544,
  ),
  109 =>
  array (
    0 => 'bytesread',
    1 => 109568,
  ),
  110 =>
  array (
    0 => 'bytesread',
    1 => 110592,
  ),
  111 =>
  array (
    0 => 'bytesread',
    1 => 111616,
  ),
  112 =>
  array (
    0 => 'bytesread',
    1 => 112640,
  ),
  113 =>
  array (
    0 => 'bytesread',
    1 => 113664,
  ),
  114 =>
  array (
    0 => 'bytesread',
    1 => 114688,
  ),
  115 =>
  array (
    0 => 'bytesread',
    1 => 115712,
  ),
  116 =>
  array (
    0 => 'bytesread',
    1 => 116736,
  ),
  117 =>
  array (
    0 => 'bytesread',
    1 => 117760,
  ),
  118 =>
  array (
    0 => 'bytesread',
    1 => 118784,
  ),
  119 =>
  array (
    0 => 'bytesread',
    1 => 119808,
  ),
  120 =>
  array (
    0 => 'bytesread',
    1 => 120832,
  ),
  121 =>
  array (
    0 => 'bytesread',
    1 => 121856,
  ),
  122 =>
  array (
    0 => 'bytesread',
    1 => 122880,
  ),
  123 =>
  array (
    0 => 'bytesread',
    1 => 123904,
  ),
  124 =>
  array (
    0 => 'bytesread',
    1 => 124928,
  ),
  125 =>
  array (
    0 => 'bytesread',
    1 => 125952,
  ),
  126 =>
  array (
    0 => 'bytesread',
    1 => 126976,
  ),
  127 =>
  array (
    0 => 'bytesread',
    1 => 128000,
  ),
  128 =>
  array (
    0 => 'bytesread',
    1 => 129024,
  ),
  129 =>
  array (
    0 => 'bytesread',
    1 => 130048,
  ),
  130 =>
  array (
    0 => 'bytesread',
    1 => 131072,
  ),
  131 =>
  array (
    0 => 'bytesread',
    1 => 132096,
  ),
  132 =>
  array (
    0 => 'bytesread',
    1 => 133120,
  ),
  133 =>
  array (
    0 => 'bytesread',
    1 => 134144,
  ),
  134 =>
  array (
    0 => 'bytesread',
    1 => 135168,
  ),
  135 =>
  array (
    0 => 'bytesread',
    1 => 136192,
  ),
  136 =>
  array (
    0 => 'bytesread',
    1 => 137216,
  ),
  137 =>
  array (
    0 => 'bytesread',
    1 => 138240,
  ),
  138 =>
  array (
    0 => 'bytesread',
    1 => 139264,
  ),
  139 =>
  array (
    0 => 'bytesread',
    1 => 140288,
  ),
  140 =>
  array (
    0 => 'bytesread',
    1 => 141312,
  ),
  141 =>
  array (
    0 => 'bytesread',
    1 => 142336,
  ),
  142 =>
  array (
    0 => 'bytesread',
    1 => 143360,
  ),
  143 =>
  array (
    0 => 'bytesread',
    1 => 144384,
  ),
  144 =>
  array (
    0 => 'bytesread',
    1 => 145408,
  ),
  145 =>
  array (
    0 => 'bytesread',
    1 => 146432,
  ),
  146 =>
  array (
    0 => 'bytesread',
    1 => 147456,
  ),
  147 =>
  array (
    0 => 'bytesread',
    1 => 148480,
  ),
  148 =>
  array (
    0 => 'bytesread',
    1 => 149504,
  ),
  149 =>
  array (
    0 => 'bytesread',
    1 => 150528,
  ),
  150 =>
  array (
    0 => 'bytesread',
    1 => 151552,
  ),
  151 =>
  array (
    0 => 'bytesread',
    1 => 152576,
  ),
  152 =>
  array (
    0 => 'bytesread',
    1 => 153600,
  ),
  153 =>
  array (
    0 => 'bytesread',
    1 => 154624,
  ),
  154 =>
  array (
    0 => 'bytesread',
    1 => 155648,
  ),
  155 =>
  array (
    0 => 'bytesread',
    1 => 156672,
  ),
  156 =>
  array (
    0 => 'bytesread',
    1 => 157696,
  ),
  157 =>
  array (
    0 => 'bytesread',
    1 => 158720,
  ),
  158 =>
  array (
    0 => 'bytesread',
    1 => 159744,
  ),
  159 =>
  array (
    0 => 'bytesread',
    1 => 160768,
  ),
  160 =>
  array (
    0 => 'bytesread',
    1 => 161792,
  ),
  161 =>
  array (
    0 => 'bytesread',
    1 => 162816,
  ),
  162 =>
  array (
    0 => 'bytesread',
    1 => 163840,
  ),
  163 =>
  array (
    0 => 'bytesread',
    1 => 164864,
  ),
  164 =>
  array (
    0 => 'bytesread',
    1 => 165888,
  ),
  165 =>
  array (
    0 => 'bytesread',
    1 => 166912,
  ),
  166 =>
  array (
    0 => 'bytesread',
    1 => 167936,
  ),
  167 =>
  array (
    0 => 'bytesread',
    1 => 168960,
  ),
  168 =>
  array (
    0 => 'bytesread',
    1 => 169984,
  ),
  169 =>
  array (
    0 => 'bytesread',
    1 => 171008,
  ),
  170 =>
  array (
    0 => 'bytesread',
    1 => 172032,
  ),
  171 =>
  array (
    0 => 'bytesread',
    1 => 173056,
  ),
  172 =>
  array (
    0 => 'bytesread',
    1 => 174080,
  ),
  173 =>
  array (
    0 => 'bytesread',
    1 => 175104,
  ),
  174 =>
  array (
    0 => 'bytesread',
    1 => 176128,
  ),
  175 =>
  array (
    0 => 'bytesread',
    1 => 177152,
  ),
  176 =>
  array (
    0 => 'bytesread',
    1 => 178176,
  ),
  177 =>
  array (
    0 => 'bytesread',
    1 => 179200,
  ),
  178 =>
  array (
    0 => 'bytesread',
    1 => 180224,
  ),
  179 =>
  array (
    0 => 'bytesread',
    1 => 181248,
  ),
  180 =>
  array (
    0 => 'bytesread',
    1 => 182272,
  ),
  181 =>
  array (
    0 => 'bytesread',
    1 => 183296,
  ),
  182 =>
  array (
    0 => 'bytesread',
    1 => 184320,
  ),
  183 =>
  array (
    0 => 'bytesread',
    1 => 185344,
  ),
  184 =>
  array (
    0 => 'bytesread',
    1 => 186368,
  ),
  185 =>
  array (
    0 => 'bytesread',
    1 => 187392,
  ),
  186 =>
  array (
    0 => 'bytesread',
    1 => 188416,
  ),
  187 =>
  array (
    0 => 'bytesread',
    1 => 189440,
  ),
  188 =>
  array (
    0 => 'bytesread',
    1 => 190464,
  ),
  189 =>
  array (
    0 => 'bytesread',
    1 => 191488,
  ),
  190 =>
  array (
    0 => 'bytesread',
    1 => 192512,
  ),
  191 =>
  array (
    0 => 'bytesread',
    1 => 193536,
  ),
  192 =>
  array (
    0 => 'bytesread',
    1 => 194560,
  ),
  193 =>
  array (
    0 => 'bytesread',
    1 => 195584,
  ),
  194 =>
  array (
    0 => 'bytesread',
    1 => 196608,
  ),
  195 =>
  array (
    0 => 'bytesread',
    1 => 197632,
  ),
  196 =>
  array (
    0 => 'bytesread',
    1 => 198656,
  ),
  197 =>
  array (
    0 => 'bytesread',
    1 => 199680,
  ),
  198 =>
  array (
    0 => 'bytesread',
    1 => 200704,
  ),
  199 =>
  array (
    0 => 'bytesread',
    1 => 201728,
  ),
  200 =>
  array (
    0 => 'bytesread',
    1 => 202752,
  ),
  201 =>
  array (
    0 => 'bytesread',
    1 => 203776,
  ),
  202 =>
  array (
    0 => 'bytesread',
    1 => 204800,
  ),
  203 =>
  array (
    0 => 'bytesread',
    1 => 205824,
  ),
  204 =>
  array (
    0 => 'bytesread',
    1 => 206848,
  ),
  205 =>
  array (
    0 => 'bytesread',
    1 => 207872,
  ),
  206 =>
  array (
    0 => 'bytesread',
    1 => 208896,
  ),
  207 =>
  array (
    0 => 'bytesread',
    1 => 209920,
  ),
  208 =>
  array (
    0 => 'bytesread',
    1 => 210944,
  ),
  209 =>
  array (
    0 => 'bytesread',
    1 => 211968,
  ),
  210 =>
  array (
    0 => 'bytesread',
    1 => 212992,
  ),
  211 =>
  array (
    0 => 'bytesread',
    1 => 214016,
  ),
  212 =>
  array (
    0 => 'bytesread',
    1 => 215040,
  ),
  213 =>
  array (
    0 => 'bytesread',
    1 => 216064,
  ),
  214 =>
  array (
    0 => 'bytesread',
    1 => 217088,
  ),
  215 =>
  array (
    0 => 'bytesread',
    1 => 218112,
  ),
  216 =>
  array (
    0 => 'bytesread',
    1 => 219136,
  ),
  217 =>
  array (
    0 => 'bytesread',
    1 => 220160,
  ),
  218 =>
  array (
    0 => 'bytesread',
    1 => 221184,
  ),
  219 =>
  array (
    0 => 'bytesread',
    1 => 222208,
  ),
  220 =>
  array (
    0 => 'bytesread',
    1 => 223232,
  ),
  221 =>
  array (
    0 => 'bytesread',
    1 => 224256,
  ),
  222 =>
  array (
    0 => 'bytesread',
    1 => 225280,
  ),
  223 =>
  array (
    0 => 'bytesread',
    1 => 226304,
  ),
  224 =>
  array (
    0 => 'bytesread',
    1 => 227328,
  ),
  225 =>
  array (
    0 => 'bytesread',
    1 => 228352,
  ),
  226 =>
  array (
    0 => 'bytesread',
    1 => 229376,
  ),
  227 =>
  array (
    0 => 'bytesread',
    1 => 230400,
  ),
  228 =>
  array (
    0 => 'bytesread',
    1 => 231424,
  ),
  229 =>
  array (
    0 => 'bytesread',
    1 => 232448,
  ),
  230 =>
  array (
    0 => 'bytesread',
    1 => 233472,
  ),
  231 =>
  array (
    0 => 'bytesread',
    1 => 234496,
  ),
  232 =>
  array (
    0 => 'bytesread',
    1 => 235520,
  ),
  233 =>
  array (
    0 => 'bytesread',
    1 => 236544,
  ),
  234 =>
  array (
    0 => 'bytesread',
    1 => 237568,
  ),
  235 =>
  array (
    0 => 'bytesread',
    1 => 238592,
  ),
  236 =>
  array (
    0 => 'bytesread',
    1 => 239616,
  ),
  237 =>
  array (
    0 => 'bytesread',
    1 => 240640,
  ),
  238 =>
  array (
    0 => 'bytesread',
    1 => 241664,
  ),
  239 =>
  array (
    0 => 'bytesread',
    1 => 242688,
  ),
  240 =>
  array (
    0 => 'bytesread',
    1 => 243712,
  ),
  241 =>
  array (
    0 => 'bytesread',
    1 => 244736,
  ),
  242 =>
  array (
    0 => 'bytesread',
    1 => 245760,
  ),
  243 =>
  array (
    0 => 'bytesread',
    1 => 246784,
  ),
  244 =>
  array (
    0 => 'bytesread',
    1 => 247808,
  ),
  245 =>
  array (
    0 => 'bytesread',
    1 => 248832,
  ),
  246 =>
  array (
    0 => 'bytesread',
    1 => 249856,
  ),
  247 =>
  array (
    0 => 'bytesread',
    1 => 250880,
  ),
  248 =>
  array (
    0 => 'bytesread',
    1 => 251904,
  ),
  249 =>
  array (
    0 => 'bytesread',
    1 => 252928,
  ),
  250 =>
  array (
    0 => 'bytesread',
    1 => 253952,
  ),
  251 =>
  array (
    0 => 'bytesread',
    1 => 254976,
  ),
  252 =>
  array (
    0 => 'bytesread',
    1 => 256000,
  ),
  253 =>
  array (
    0 => 'bytesread',
    1 => 257024,
  ),
  254 =>
  array (
    0 => 'bytesread',
    1 => 258048,
  ),
  255 =>
  array (
    0 => 'bytesread',
    1 => 259072,
  ),
  256 =>
  array (
    0 => 'bytesread',
    1 => 260096,
  ),
  257 =>
  array (
    0 => 'bytesread',
    1 => 261120,
  ),
  258 =>
  array (
    0 => 'bytesread',
    1 => 262144,
  ),
  259 =>
  array (
    0 => 'bytesread',
    1 => 263168,
  ),
  260 =>
  array (
    0 => 'bytesread',
    1 => 264192,
  ),
  261 =>
  array (
    0 => 'bytesread',
    1 => 265216,
  ),
  262 =>
  array (
    0 => 'bytesread',
    1 => 266240,
  ),
  263 =>
  array (
    0 => 'bytesread',
    1 => 267264,
  ),
  264 =>
  array (
    0 => 'bytesread',
    1 => 268288,
  ),
  265 =>
  array (
    0 => 'bytesread',
    1 => 269312,
  ),
  266 =>
  array (
    0 => 'bytesread',
    1 => 270336,
  ),
  267 =>
  array (
    0 => 'bytesread',
    1 => 271360,
  ),
  268 =>
  array (
    0 => 'bytesread',
    1 => 272384,
  ),
  269 =>
  array (
    0 => 'bytesread',
    1 => 273408,
  ),
  270 =>
  array (
    0 => 'bytesread',
    1 => 274432,
  ),
  271 =>
  array (
    0 => 'bytesread',
    1 => 275456,
  ),
  272 =>
  array (
    0 => 'bytesread',
    1 => 276480,
  ),
  273 =>
  array (
    0 => 'bytesread',
    1 => 277504,
  ),
  274 =>
  array (
    0 => 'bytesread',
    1 => 278528,
  ),
  275 =>
  array (
    0 => 'bytesread',
    1 => 279552,
  ),
  276 =>
  array (
    0 => 'bytesread',
    1 => 280576,
  ),
  277 =>
  array (
    0 => 'bytesread',
    1 => 281600,
  ),
  278 =>
  array (
    0 => 'bytesread',
    1 => 282624,
  ),
  279 =>
  array (
    0 => 'bytesread',
    1 => 283648,
  ),
  280 =>
  array (
    0 => 'bytesread',
    1 => 284672,
  ),
  281 =>
  array (
    0 => 'bytesread',
    1 => 285696,
  ),
  282 =>
  array (
    0 => 'bytesread',
    1 => 286720,
  ),
  283 =>
  array (
    0 => 'bytesread',
    1 => 287744,
  ),
  284 =>
  array (
    0 => 'bytesread',
    1 => 288768,
  ),
  285 =>
  array (
    0 => 'bytesread',
    1 => 289792,
  ),
  286 =>
  array (
    0 => 'bytesread',
    1 => 290816,
  ),
  287 =>
  array (
    0 => 'bytesread',
    1 => 291840,
  ),
  288 =>
  array (
    0 => 'bytesread',
    1 => 292864,
  ),
  289 =>
  array (
    0 => 'bytesread',
    1 => 293888,
  ),
  290 =>
  array (
    0 => 'bytesread',
    1 => 294912,
  ),
  291 =>
  array (
    0 => 'bytesread',
    1 => 295936,
  ),
  292 =>
  array (
    0 => 'bytesread',
    1 => 296960,
  ),
  293 =>
  array (
    0 => 'bytesread',
    1 => 297984,
  ),
  294 =>
  array (
    0 => 'bytesread',
    1 => 299008,
  ),
  295 =>
  array (
    0 => 'bytesread',
    1 => 300032,
  ),
  296 =>
  array (
    0 => 'bytesread',
    1 => 301056,
  ),
  297 =>
  array (
    0 => 'bytesread',
    1 => 302080,
  ),
  298 =>
  array (
    0 => 'bytesread',
    1 => 303104,
  ),
  299 =>
  array (
    0 => 'bytesread',
    1 => 304128,
  ),
  300 =>
  array (
    0 => 'bytesread',
    1 => 305152,
  ),
  301 =>
  array (
    0 => 'bytesread',
    1 => 306176,
  ),
  302 =>
  array (
    0 => 'bytesread',
    1 => 307200,
  ),
  303 =>
  array (
    0 => 'bytesread',
    1 => 308224,
  ),
  304 =>
  array (
    0 => 'bytesread',
    1 => 309248,
  ),
  305 =>
  array (
    0 => 'bytesread',
    1 => 310272,
  ),
  306 =>
  array (
    0 => 'bytesread',
    1 => 311296,
  ),
  307 =>
  array (
    0 => 'bytesread',
    1 => 312320,
  ),
  308 =>
  array (
    0 => 'bytesread',
    1 => 313344,
  ),
  309 =>
  array (
    0 => 'bytesread',
    1 => 314368,
  ),
  310 =>
  array (
    0 => 'bytesread',
    1 => 315392,
  ),
  311 =>
  array (
    0 => 'bytesread',
    1 => 316416,
  ),
  312 =>
  array (
    0 => 'bytesread',
    1 => 317440,
  ),
  313 =>
  array (
    0 => 'bytesread',
    1 => 318464,
  ),
  314 =>
  array (
    0 => 'bytesread',
    1 => 319488,
  ),
  315 =>
  array (
    0 => 'bytesread',
    1 => 320512,
  ),
  316 =>
  array (
    0 => 'bytesread',
    1 => 321536,
  ),
  317 =>
  array (
    0 => 'bytesread',
    1 => 322560,
  ),
  318 =>
  array (
    0 => 'bytesread',
    1 => 323584,
  ),
  319 =>
  array (
    0 => 'bytesread',
    1 => 324608,
  ),
  320 =>
  array (
    0 => 'bytesread',
    1 => 325632,
  ),
  321 =>
  array (
    0 => 'bytesread',
    1 => 326656,
  ),
  322 =>
  array (
    0 => 'bytesread',
    1 => 327680,
  ),
  323 =>
  array (
    0 => 'bytesread',
    1 => 328704,
  ),
  324 =>
  array (
    0 => 'bytesread',
    1 => 329728,
  ),
  325 =>
  array (
    0 => 'bytesread',
    1 => 330752,
  ),
  326 =>
  array (
    0 => 'bytesread',
    1 => 331776,
  ),
  327 =>
  array (
    0 => 'bytesread',
    1 => 332800,
  ),
  328 =>
  array (
    0 => 'bytesread',
    1 => 333824,
  ),
  329 =>
  array (
    0 => 'bytesread',
    1 => 334848,
  ),
  330 =>
  array (
    0 => 'bytesread',
    1 => 335872,
  ),
  331 =>
  array (
    0 => 'bytesread',
    1 => 336896,
  ),
  332 =>
  array (
    0 => 'bytesread',
    1 => 337920,
  ),
  333 =>
  array (
    0 => 'bytesread',
    1 => 338944,
  ),
  334 =>
  array (
    0 => 'bytesread',
    1 => 339968,
  ),
  335 =>
  array (
    0 => 'bytesread',
    1 => 340992,
  ),
  336 =>
  array (
    0 => 'bytesread',
    1 => 342016,
  ),
  337 =>
  array (
    0 => 'bytesread',
    1 => 343040,
  ),
  338 =>
  array (
    0 => 'bytesread',
    1 => 344064,
  ),
  339 =>
  array (
    0 => 'bytesread',
    1 => 345088,
  ),
  340 =>
  array (
    0 => 'bytesread',
    1 => 346112,
  ),
  341 =>
  array (
    0 => 'bytesread',
    1 => 347136,
  ),
  342 =>
  array (
    0 => 'bytesread',
    1 => 348160,
  ),
  343 =>
  array (
    0 => 'bytesread',
    1 => 349184,
  ),
  344 =>
  array (
    0 => 'bytesread',
    1 => 350208,
  ),
  345 =>
  array (
    0 => 'bytesread',
    1 => 351232,
  ),
  346 =>
  array (
    0 => 'bytesread',
    1 => 352256,
  ),
  347 =>
  array (
    0 => 'bytesread',
    1 => 353280,
  ),
  348 =>
  array (
    0 => 'bytesread',
    1 => 354304,
  ),
  349 =>
  array (
    0 => 'bytesread',
    1 => 355328,
  ),
  350 =>
  array (
    0 => 'bytesread',
    1 => 356352,
  ),
  351 =>
  array (
    0 => 'bytesread',
    1 => 357376,
  ),
  352 =>
  array (
    0 => 'bytesread',
    1 => 358400,
  ),
  353 =>
  array (
    0 => 'bytesread',
    1 => 359424,
  ),
  354 =>
  array (
    0 => 'bytesread',
    1 => 360448,
  ),
  355 =>
  array (
    0 => 'bytesread',
    1 => 361472,
  ),
  356 =>
  array (
    0 => 'bytesread',
    1 => 362496,
  ),
  357 =>
  array (
    0 => 'bytesread',
    1 => 363520,
  ),
  358 =>
  array (
    0 => 'bytesread',
    1 => 364544,
  ),
  359 =>
  array (
    0 => 'bytesread',
    1 => 365568,
  ),
  360 =>
  array (
    0 => 'bytesread',
    1 => 366592,
  ),
  361 =>
  array (
    0 => 'bytesread',
    1 => 367616,
  ),
  362 =>
  array (
    0 => 'bytesread',
    1 => 368640,
  ),
  363 =>
  array (
    0 => 'bytesread',
    1 => 369664,
  ),
  364 =>
  array (
    0 => 'bytesread',
    1 => 370688,
  ),
  365 =>
  array (
    0 => 'bytesread',
    1 => 371001,
  ),
  366 =>
  array (
    0 => 'done',
    1 => 371001,
  ),
 )
, $fakelog->getDownload(), 'download callback messages');

$installer->setOptions($dp->getOptions());
$installer->sortPackagesForInstall($result);
$installer->setDownloadedPackages($result);
$phpunit->assertNoErrors('set of downloaded packages');

$ret = $installer->install($result[0], $dp->getOptions());
$phpunit->assertNoErrors('after install');
$phpunit->assertEquals(array(), $reg->listPackages(), 'pear');
$phpunit->assertEquals(array('sqlite'), $reg->listPackages('pecl'), 'pecl');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
