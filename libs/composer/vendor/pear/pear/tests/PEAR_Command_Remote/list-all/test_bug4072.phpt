--TEST--
list-all command, bug #4072 - installed packages not listed for list-all -c option
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$_test_dep->setPHPVersion('4.3.10');
$_test_dep->setPEARVersion('1.4.0a10');
$reg = &$config->getRegistry();

$ch = new PEAR_ChannelFile;
$ch->setName('smoog');
$ch->setSUmmary('smooging about');
$ch->setBaseURL('REST1.0', 'http://smoog/rest/');
$reg->addChannel($ch);

$pf = new PEAR_PackageFile_v2_rw;
$pf->setConfig($config);
$pf->setPackage('APC');
$pf->setChannel('smoog');
$pf->setAPIStability('stable');
$pf->setReleaseStability('stable');
$pf->setAPIVersion('1.2.0');
$pf->setReleaseVersion('1.2.0');
$pf->setSummary('foo');
$pf->setDate(date('Y-m-d'));
$pf->setDescription('foo');
$pf->setLicense('PHP License');
$pf->setNotes('foo');
$pf->addMaintainer('lead', 'cellog', 'Greg', 'cellog@php.net');
$pf->setPackageType('php');
$pf->clearContents();
$pf->addFile('', 'foo.dat', array('role' => 'data'));
$pf->setPhpDep('4.0.0', '6.0.0');
$pf->setPearinstallerDep('1.4.0a10');
$pf->addRelease();
$pf->validate();
$phpunit->assertNoErrors('setup');
$reg->addPackage2($pf);

$pearweb->addRESTConfig('http://smoog/rest/p/packages.xml',
'<?xml version="1.0" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>smoog</c>
 <p>APC</p>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://smoog/rest/p/apc/info.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>APC</n>
 <c>smoog</c>
 <ca xlink:href="/rest/c/Caching">Caching</ca>
 <l>PHP License</l>
 <s>Alternative PHP Cache</s>

 <d>APC is a free, open, and robust framework for caching and optimizing PHP intermediate code.</d>
 <r xlink:href="/rest/r/apc"/>
</p>',
'text/xml');

$pearweb->addRESTConfig("http://smoog/rest/r/apc/deps.2.0.4.txt", 'b:0;', 'text/plain');

$pearweb->addRESTConfig('http://smoog/rest/r/apc/allreleases.xml',
'<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>APC</p>
 <c>smoog</c>
 <r><v>2.0.4</v><s>stable</s></r>
</a>',
'text/xml');


$reg = &$config->getRegistry();
$e = $command->run('list-all', array('channel' => 'smoog'), array());
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
      'caption' => 'All packages [Channel smoog]:',
      'border' => true,
      'headline' =>
      array (
        0 => 'Package',
        1 => 'Latest',
        2 => 'Local',
      ),
      'channel' => 'smoog',
      'data' =>
      array (
        'Caching' =>
        array (
          0 =>
          array (
            0 => 'smoog/APC',
            1 => '2.0.4',
            2 => '1.2.0',
            3 => 'Alternative PHP Cache',
            4 =>
            array (
            ),
          ),
        ),
      ),
    ),
    'cmd' => 'list-all',
  ),
), $fakelog->getLog(), 'smoog log');
$phpunit->assertNoErrors('smoog');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
