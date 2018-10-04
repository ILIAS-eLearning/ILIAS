--TEST--
list-upgrades command
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

$reg = &$config->getRegistry();
$chan = $reg->getChannel('pecl.php.net');
$chan->setBaseURL('REST1.0', 'http://pecl.php.net/rest/');
$reg->updateChannel($chan);
;
$ch = new PEAR_ChannelFile;
$ch->setName('smoog');
$ch->setSummary('smoog');
$ch->setBaseURL('REST1.0', 'http://smoog/rest/');
$reg->addChannel($ch);

$ch->setName('empty');
$reg->addChannel($ch);

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


$pearweb->addRESTConfig("http://pear.php.net/rest/p/packages.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>pear.php.net</c>
 <p>Archive_Zip</p>
 <p>APC</p>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_zip/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>Archive_Zip</p>
 <c>pear.php.net</c>
 <r><v>2.0.5</v><s>stable</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/apc/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>apc</p>
 <c>pear.php.net</c>
 <r><v>2.0.4</v><s>stable</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/archive_zip/2.0.5.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/archive_zip">Archive_Zip</p>
 <c>pear.php.net</c>
 <v>2.0.5</v>
 <st>stable</st>
 <l>LGPL</l>

 <m>vblavet</m>
 <s>Zip file management class</s>
 <d>This class provides handling of zip files in PHP.
It supports creating, listing, extracting and adding to zip files.

</d>
 <da>2005-11-26 02:47:56</da>
 <n>Correct package download problem

</n>
 <f>23456789530</f>

 <g>http://pear.php.net/get/Archive_Zip-2.0.5</g>
 <x xlink:href="package.2.0.5.xml"/>
</r>',
'text/xml');


$pearweb->addRESTConfig("http://pear.php.net/rest/r/apc/2.0.4.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/apc">APC</p>
 <c>pear.php.net</c>
 <v>2.0.4</v>
 <st>stable</st>
 <l>LGPL</l>

 <m>cellog</m>
 <s>test</s>
 <d>test</d>
 <da>2005-12-26 02:47:56</da>
 <n>Correct package download problem

</n>
 <f>23456</f>

 <g>http://pear.php.net/get/APC-2.0.4</g>
 <x xlink:href="package.2.0.4.xml"/>
</r>',
'text/xml');

$pearweb->addRESTConfig("http://smoog/rest/p/packages.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>smoog</c>
 <p>APC</p>
</a>',
'text/xml');

$pearweb->addRESTConfig("http://smoog/rest/r/apc/allreleases.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink"     xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>apc</p>
 <c>smoog</c>
 <r><v>2.0.4</v><s>stable</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://smoog/rest/r/apc/2.0.4.xml",
'<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/apc">APC</p>
 <c>smoog</c>
 <v>2.0.4</v>
 <st>stable</st>
 <l>LGPL</l>

 <m>cellog</m>
 <s>test</s>
 <d>test</d>
 <da>2005-12-26 02:47:56</da>
 <n>Correct package download problem

</n>
 <f>23456</f>

 <g>http://smoog/get/APC-2.0.4</g>
 <x xlink:href="package.2.0.4.xml"/>
</r>',
'text/xml');

$e = $command->run('list-upgrades', array(), array());
$phpunit->assertNoErrors('pear.php.net');
$actual = array (
  array (
    'info' =>
    array (
      'caption' => 'pear.php.net Available Upgrades (stable):',
      'border' => 1,
      'headline' =>
      array (
        0 => 'Channel',
        1 => 'Package',
        2 => 'Local',
        3 => 'Remote',
        4 => 'Size',
      ),
      'channel' => 'pear.php.net',
      'data' =>
      array (
        0 =>
        array (
          0 => 'pear.php.net',
          1 => 'Archive_Zip',
          2 => '1.0.0 (stable)',
          3 => '2.0.5 (stable)',
          4 => '22907022kB',
        ),
      ),
    ),
    'cmd' => 'list-upgrades',
  ),
);

$phpunit->assertEquals($actual, $fakelog->getLog(), 'pear log');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
