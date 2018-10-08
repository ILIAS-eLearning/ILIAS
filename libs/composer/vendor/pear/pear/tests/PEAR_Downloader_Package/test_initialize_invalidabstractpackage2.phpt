--TEST--
PEAR_Downloader_Package->initialize() with invalid abstract package (no releases with preferred state)
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
$chan = $reg->getChannel('pear.php.net');
$chan->setBaseURL('REST1.0', 'http://pear.php.net/rest/');
$reg->updateChannel($chan);

$pearweb->addRESTConfig("http://pear.php.net/rest/r/test/allreleases.xml", '<?xml version="1.0"?>
<a xmlns="http://pear.php.net/dtd/rest.allreleases"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases
    http://pear.php.net/dtd/rest.allreleases.xsd">
 <p>test</p>
 <c>pear.php.net</c>
 <r><v>0.2.0</v><s>beta</s></r>
</a>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/test/0.2.0.xml", '<?xml version="1.0"?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="/rest/p/test">test</p>
 <c>pear.php.net</c>
 <v>0.2.0</v>
 <st>beta</st>
 <l>PHP License</l>
 <m>cellog</m>
 <s>test</s>
 <d>test</d>
 <da>2005-04-17 18:40:51</da>
 <n>test</n>
 <f>252733</f>
 <g>http://www.example.com/test-1.0</g>
 <x xlink:href="package.0.2.0.xml"/>
</r>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/p/test/info.xml", '<?xml version="1.0" encoding="UTF-8" ?>
<p xmlns="http://pear.php.net/dtd/rest.package"    xsi:schemaLocation="http://pear.php.net/dtd/rest.package    http://pear.php.net/dtd/rest.package.xsd">
 <n>test</n>
 <c>pear.php.net</c>
 <ca xlink:href="/rest/c/test">test</ca>
 <l>PHP License</l>
 <s>test</s>
 <d>test</d>
 <r xlink:href="/rest/r/test"/>
</p>', 'text/xml');

$pearweb->addRESTConfig("http://pear.php.net/rest/r/test/deps.0.2.0.txt", 'b:0;', 'text/plain');

$dp = newDownloaderPackage(array());
$phpunit->assertNoErrors('after create');
$result = $dp->initialize('test');

$phpunit->assertErrors(
    array(
        array(
            'package' => 'PEAR_Error',
            'message' => 'Failed to download pear/test within preferred state "stable", ' .
                         'latest release is version 0.2.0, stability "beta", use "channel://pear.php.net/test-0.2.0" to install'
        ),
        array(
            'package' => 'PEAR_Error',
            'message' => ""
        ),
    ), 'after initialize');

$phpunit->assertEquals(array (
  array (
    0 => 0,
    1 => 'Failed to download pear/test within preferred state "stable", latest release is version 0.2.0, stability "beta", use "channel://pear.php.net/test-0.2.0" to install'
  ),
  array (
    0 => 2,
    1 => 'Cannot initialize \'test\', invalid or missing package file',
   ),
), $fakelog->getLog(), 'log messages');

$phpunit->assertEquals(array (), $fakelog->getDownload(), 'download callback messages');
$phpunit->assertIsa('PEAR_Error', $result, 'after initialize');
$phpunit->assertNull($dp->getPackageFile(), 'downloadable test');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
