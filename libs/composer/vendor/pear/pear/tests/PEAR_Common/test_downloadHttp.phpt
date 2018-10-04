--TEST--
PEAR_Common::downloadHttp test
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
$fp = @fsockopen('test.pear.php.net', 80);
if (!$fp) {
    echo 'skip test.pear.php.net is down';
} else {
    fclose($fp);
    if (!($fp = @fopen('http://test.pear.php.net/testdownload.tgz'))) {
        echo 'skip test.pear.php.net/testdownload.tgz appears to be missing?';
    } else {
        fclose($fp);
    }
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$php5 = version_compare(phpversion(), '5.0.0', '>=');
$windows = substr(PHP_OS, 0, 3) == 'WIN';

$common = new PEAR_Common;

ob_start();
PEAR_Common::downloadHttp('http://test.pear.php.net/testdownload.tgz', $ui, $statedir);
$caught = ob_get_contents();
ob_end_clean();
$phpunit->assertNoErrors('wrong simple');
$phpunit->assertEquals('', $caught, 'wrong simple');
$firstone = implode('', file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR . 'testdownload.tgz'));
$secondone = implode('', file($statedir . DIRECTORY_SEPARATOR . 'testdownload.tgz'));
$phpunit->assertTrue($firstone == $secondone, 'files are not the same');

ob_start();
PEAR_Common::downloadHttp('http://test.poop.php.net/stuff.tgz', $ui, $statedir);
ob_end_clean();
$phpunit->assertErrors(array(
array('package' => 'PEAR_Error', 'message' => 'Connection to `test.poop.php.net:80\' failed: ' .
    ($php5 ? '' : 'The operation completed successfully.' . ($windows ? "\r\n" : "\n")))
), 'static fail');
$phpunit->assertEquals('', $caught, 'wrong static simple');

//echo "Test callback:\n";
$phpunit->showall();
ob_start();
PEAR_Common::downloadHttp('http://test.pear.php.net/testdownload.tgz', $fakelog, $statedir,
    array(&$fakelog, '_downloadCallback'));
$caught = ob_get_contents();
ob_end_clean();
$firstone = implode('', file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR . 'testdownload.tgz'));
$secondone = implode('', file($statedir . DIRECTORY_SEPARATOR . 'testdownload.tgz'));
$phpunit->assertNoErrors('test callback');
$phpunit->assertEquals('', $caught, 'wrong simple');
$firstone = implode('', file(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR . 'testdownload.tgz'));
$secondone = implode('', file($statedir . DIRECTORY_SEPARATOR . 'testdownload.tgz'));
$phpunit->assertTrue($firstone == $secondone, 'callback files are not the same');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'callback log');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'setup',
    1 => 'self',
  ),
  1 => 
  array (
    0 => 'saveas',
    1 => 'testdownload.tgz',
  ),
  2 => 
  array (
    0 => 'start',
    1 => 
    array (
      0 => 'testdownload.tgz',
      1 => '41655',
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
    1 => 41655,
  ),
  44 => 
  array (
    0 => 'done',
    1 => 41655,
  ),
), $fakelog->getDownload(), 'download log');



//echo "Callback fail:\n";
ob_start();
PEAR_Common::downloadHttp('http://test.poop.php.net/stuff.tgz', $ui, $statedir,
    array(&$fakelog, '_downloadCallback'));
$caught = ob_get_contents();
ob_end_clean();
$phpunit->assertErrors(array(
array('package' => 'PEAR_Error', 'message' => 'Connection to `test.poop.php.net:80\' failed: ' .
    ($php5 ? '' : 'The operation completed successfully.' . ($windows ? "\r\n" : "\n")))
), 'callback fail');
$phpunit->assertEquals(array(), $fakelog->getLog(), 'callback fail log');
$testarr = array (
      0 => 'test.poop.php.net',
      1 => 80,
      2 => $php5 ? 8526320 : 0,
      3 => ($php5 ? '' : 'The operation completed successfully.' . ($windows ? "\r\n" : "\n")),
    );
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'setup',
    1 => 'self',
  ),
  1 => 
  array (
    0 => 'connfailed',
    1 => 
    $testarr
  ),
), $fakelog->getDownload(), 'download fail log');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
