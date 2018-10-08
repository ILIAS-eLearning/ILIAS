--TEST--
PEAR_Channelfile->setPort()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php


require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$phpt->assertTrue($chf->setPort(234), 'first time');
$phpt->assertEquals(234, $chf->getPort(), 'first');
$res = $chf->setPort(234, 'notfound');
$phpt->assertErrors(array(
    array('package' => 'PEAR_ChannelFile', 'message' => 'Mirror "notfound" does not exist')
), 'errors');
$phpt->assertFalse($res, 'notfound time');
$chf->setName('hi');
$chf->addMirror('blah', 142);
$phpt->assertEquals(142, $chf->getPort('blah'), 'blah first');
$res = $chf->setPort(81, 'blah');
$phpt->assertEquals(81, $chf->getPort('blah'), 'blah second');
$chf->addMirror('greg');
$phpt->assertEquals(80, $chf->getPort('greg'), 'greg first');
$res = $chf->setPort(82, 'greg');
$phpt->assertEquals(81, $chf->getPort('blah'), 'blah third');
$phpt->assertEquals(82, $chf->getPort('greg'), 'greg second');
$phpt->assertEquals(234, $chf->getPort(), 'main second');
echo 'tests done';
?>
--EXPECT--
tests done
