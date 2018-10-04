--TEST--
PEAR_PackageFile_v1->_analyzeSourceCode test
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
if (!function_exists('token_get_all')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'Parser'. DIRECTORY_SEPARATOR .
    'test_basicparse'. DIRECTORY_SEPARATOR . 'package.xml';
$val = &$parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$val->validate(PEAR_VALIDATE_NORMAL); // setup purposes only
$phpunit->assertNoErrors('setup');

$phpunit->assertFalse($val->_analyzeSourceCode('=+"\\//452'), 'invalid filename');

$testdir = $statedir;
@mkdir($testdir);

$test1 = '
<?php
::error();
?>
';
$fp = fopen($testdir . DIRECTORY_SEPARATOR . 'test1.php', 'w');
fwrite($fp, $test1);
fclose($fp);

$ret = $val->_analyzeSourceCode($testdir . DIRECTORY_SEPARATOR . 'test1.php');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 
    'Parser error: invalid PHP found in file "' . $testdir . DIRECTORY_SEPARATOR . 'test1.php"')),
    'invalid php');
$phpunit->assertFalse($ret, 'wrong return value, invalid php in file');
unlink($testdir . DIRECTORY_SEPARATOR . 'test1.php');

$test3 = '
<?php
class test
{
    class test2 {
    }
}
?>
';
$fp = fopen($testdir . DIRECTORY_SEPARATOR . 'test3.php', 'w');
fwrite($fp, $test3);
fclose($fp);

$ret = $val->_analyzeSourceCode($testdir . DIRECTORY_SEPARATOR . 'test3.php');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 
    'Parser error: invalid PHP found in file "' . $testdir . DIRECTORY_SEPARATOR . 'test3.php"')),
    'more invalid php');
$phpunit->assertFalse($ret, 'wrong return value, 2nd invalid PHP test');
unlink($testdir . DIRECTORY_SEPARATOR . 'test3.php');

$test4 = '
<?php
function test()
{
    class test2 {
    }
}
?>
';
$fp = fopen($testdir . DIRECTORY_SEPARATOR . 'test4.php', 'w');
fwrite($fp, $test4);
fclose($fp);

$ret = $val->_analyzeSourceCode($testdir . DIRECTORY_SEPARATOR . 'test4.php');
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1', 'message' => 
    'Parser error: invalid PHP found in file "' . $testdir . DIRECTORY_SEPARATOR . 'test4.php"')),
    '3rd invalid php');
$phpunit->assertFalse($ret, 'wrong return value, 3rd invalid PHP test');
unlink($testdir . DIRECTORY_SEPARATOR . 'test4.php');

$test5 = '
<?php
function test()
{
}

if (trytofool) {
    function fool()
    {
    }
}
class test2 {
    function test2() {
        parent::unused();
        Greg::classes();
        $a = new Pierre;
    }
}

class blah extends test2 {
    /**
     * @nodep Stig
     */
    function blah() 
    {
        Stig::rules();
    }
}
?>
';
$fp = fopen($testdir . DIRECTORY_SEPARATOR . 'test5.php', 'w');
fwrite($fp, $test5);
fclose($fp);

$ret = $val->_analyzeSourceCode($testdir . DIRECTORY_SEPARATOR . 'test5.php');
$phpunit->assertNoErrors('1st valid PHP');
$phpunit->showall();
$phpunit->assertEquals(array (
  'source_file' => $testdir . DIRECTORY_SEPARATOR . 'test5.php',
  'declared_classes' => 
  array (
    0 => 'test2',
    1 => 'blah',
  ),
  'declared_interfaces' => 
  array (
  ),
  'declared_methods' => 
  array (
    'test2' => 
    array (
      0 => 'test2',
    ),
    'blah' => 
    array (
      0 => 'blah',
    ),
  ),
  'declared_functions' => 
  array (
    0 => 'test',
    1 => 'fool',
  ),
  'used_classes' => 
  array (
    0 => 'Greg',
    1 => 'Pierre',
  ),
  'inheritance' => 
  array (
    'blah' => 'test2',
  ),
  'implements' => 
  array (
  ),
), $ret, 'wrong return value, 1st valid PHP test');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
