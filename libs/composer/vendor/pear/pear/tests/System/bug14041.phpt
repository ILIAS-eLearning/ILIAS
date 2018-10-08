--TEST--
System test Bug #14041: System::find does not order properly (None windows test)
--SKIPIF--
<?php
require_once 'PEAR.php';
if (OS_WINDOWS) {
  echo 'skip';
}
?>
--FILE--
<?php
require_once 'System.php';

// setup
$path = dirname(__FILE__) . '/bug14041';
mkdir($path);
mkdir($path . '/1');
mkdir($path . '/2');
mkdir($path . '/3');
mkdir($path . '/10');
mkdir($path . '/11');
mkdir($path . '/12');
touch($path . '/test.php');
touch($path . '/test14041.php');
touch($path . '/test4095.php');
touch($path . '/1/test.php');
touch($path . '/2/test.php');
touch($path . '/3/test.php');
touch($path . '/10/test.php');
touch($path . '/11/test.php');
touch($path . '/12/test.php');

$dir = System::find(array($path, '-type', 'f',
                            '-maxdepth', 2,
                            '-name', '*.php'));
var_dump($dir);

// teardown
unlink($path . '/test.php');
unlink($path . '/test14041.php');
unlink($path . '/test4095.php');
unlink($path . '/1/test.php');
unlink($path . '/2/test.php');
unlink($path . '/3/test.php');
unlink($path . '/10/test.php');
unlink($path . '/11/test.php');
unlink($path . '/12/test.php');
rmdir($path . '/1');
rmdir($path . '/2');
rmdir($path . '/3');
rmdir($path . '/10');
rmdir($path . '/11');
rmdir($path . '/12');
rmdir($path);

?>
--EXPECTF--
array(9) {
  [0]=>
  string(%s) "%sbug14041/1/test.php"
  [1]=>
  string(%s) "%sbug14041/2/test.php"
  [2]=>
  string(%s) "%sbug14041/3/test.php"
  [3]=>
  string(%s) "%sbug14041/10/test.php"
  [4]=>
  string(%s) "%sbug14041/11/test.php"
  [5]=>
  string(%s) "%sbug14041/12/test.php"
  [6]=>
  string(%s) "%sbug14041/test.php"
  [7]=>
  string(%s) "%sbug14041/test4095.php"
  [8]=>
  string(%s) "%sbug14041/test14041.php"
}
