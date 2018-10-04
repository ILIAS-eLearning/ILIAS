--TEST--
PEAR_Downloader bug 4285 test
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$_test_dep->setPhpversion('4.2');
$_test_dep->setPEARVersion('1.4.0a1');

$chan = new PEAR_ChannelFile;
$chan->setName('pear.chiaraquartet.net');
$chan->setSummary('hi');
$chan->setDefaultPEARProtocols();
$reg = &$config->getRegistry();
$reg->addChannel($chan);

$mainpackage = dirname(__FILE__) . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'package-bug4285.xml';
$requiredpackage = dirname(__FILE__) . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'Chiara_XML_RPC5-0.3.0.tgz';
$dp = new test_PEAR_Downloader($fakelog, array(), $config);
$phpunit->assertNoErrors('after create');
$result = $dp->download(array($mainpackage, $requiredpackage));
$phpunit->assertEquals(array(), $result, 'result');
$phpunit->assertEquals(array (
  array (
    0 => 2,
    1 => 'pear/PEAR: Skipping required dependency "pear.chiaraquartet.net/Chiara_XML_RPC5", will be installed',
  ),
  array (
    0 => 0,
    1 => 'pear.chiaraquartet.net/Chiara_XML_RPC5 requires PHP (version >= 5.0.3, version <= 6.0.0), installed version is 4.2',
  ),
  array (
    0 => 0,
    1 => 'pear/PEAR requires package "pear.chiaraquartet.net/Chiara_XML_RPC5" (version >= 0.3.0)',
  ),
)
, $fakelog->getLog(), 'log');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
