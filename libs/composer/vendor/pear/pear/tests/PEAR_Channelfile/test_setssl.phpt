--TEST--
PEAR_Channelfile->setSSL()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php


require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$phpt->assertTrue($chf->setSSL(), 'first time');
$phpt->assertEquals(true, $chf->getSSL(), 'first');
$res = $chf->setSSL(true, 'notfound');
$phpt->assertErrors(array(
    array('package' => 'PEAR_ChannelFile', 'message' => 'Mirror "notfound" does not exist')
), 'errors');
$phpt->assertFalse($res, 'notfound time');
$chf->setName('hi');
$chf->addMirror('blah');
$phpt->assertEquals(false, $chf->getSSL('blah'), 'blah first');
$res = $chf->setSSL(true, 'blah');
$phpt->assertEquals(true, $chf->getSSL('blah'), 'blah second');
$chf->addMirror('greg');
$phpt->assertEquals(80, $chf->getPort('greg'), 'greg first');
$res = $chf->setPort(82, 'greg');
$phpt->assertEquals(true, $chf->getSSL('blah'), 'blah third');
$phpt->assertEquals(false, $chf->getSSL('greg'), 'greg second');
$phpt->assertEquals(true, $chf->getSSL(), 'main second');
echo 'tests done';
?>
--EXPECT--
tests done
