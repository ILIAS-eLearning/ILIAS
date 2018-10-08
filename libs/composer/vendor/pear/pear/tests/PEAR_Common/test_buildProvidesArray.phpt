--TEST--
PEAR_Common::buildProvidesArray test
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

$phpunit->assertFalse(PEAR_Common::analyzeSourceCode('=+"\\//452'), 'invalid filename');

$testdir = $statedir;
@mkdir($testdir);

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

$ret = PEAR_Common::analyzeSourceCode($testdir . DIRECTORY_SEPARATOR . 'test5.php');
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
$common = new PEAR_Common;
$ret2 = $common->buildProvidesArray($ret);
$phpunit->assertNoErrors('provides');
$phpunit->showall();
$phpunit->assertEquals(array (
  'provides' => 
  array (
    'class;test2' => 
    array (
      'file' => 'test5.php',
      'type' => 'class',
      'name' => 'test2',
    ),
    'class;blah' => 
    array (
      'file' => 'test5.php',
      'type' => 'class',
      'name' => 'blah',
      'extends' => 'test2',
    ),
    'function;test' => 
    array (
      'file' => 'test5.php',
      'type' => 'function',
      'name' => 'test',
    ),
    'function;fool' => 
    array (
      'file' => 'test5.php',
      'type' => 'function',
      'name' => 'fool',
    ),
  ),
), $common->pkginfo, 'provides');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
