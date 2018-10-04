--TEST--
PEAR_Registry->getChannelValidator() (API v1.1)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
require_once 'PEAR/Registry.php';
$pv = phpversion() . '';
$av = $pv{0} == '4' ? 'apiversion' : 'apiVersion';
if (!in_array($av, get_class_methods('PEAR_Registry'))) {
    echo 'skip';
}
if (PEAR_Registry::apiVersion() != '1.1') {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$ch = new PEAR_ChannelFile;
$ch->setName('test.test.test');
$ch->setAlias('foo');
$ch->setSummary('blah');
$ch->setDefaultPEARProtocols();
$reg->addChannel($ch);
$phpunit->assertNoErrors('setup');

$ret = $reg->getChannelValidator('snark');
$phpunit->assertErrors(array(
        array('package' => 'PEAR_Error', 'message' => 'Unknown channel: snark'),
    ), 'snark');
$ret = $reg->getChannelValidator('foo');
$phpunit->assertIsa('PEAR_Validate', $ret, 'foo');
$ret = $reg->getChannelValidator('test.test.test');
$phpunit->assertIsa('PEAR_Validate', $ret, 'test.test.test');
$ch->setValidationPackage('Tester', '1.0');
$reg->updateChannel($ch);
$phpunit->assertNoErrors('setup 2');
ini_set('include_path', $statedir);
$ret = $reg->getChannelValidator('foo');
$phpunit->assertFalse($ret, 'foo pre-"install"');

$fp = fopen($statedir . DIRECTORY_SEPARATOR . 'Tester.php', 'w');
fwrite($fp, '<?php class Tester extends PEAR_Validate {} ?>');
fclose($fp);

$ret = $reg->getChannelValidator('foo');
$phpunit->assertIsa('Tester', $ret, 'foo post-"install"');
$ret = $reg->getChannelValidator('foo'); // test after the class is included too
$phpunit->assertIsa('Tester', $ret, 'foo post-"install"');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
