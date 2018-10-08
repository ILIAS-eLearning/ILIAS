--TEST--
PEAR_PackageFile_v1->_analyzeSourceCode(), bug #5717
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
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
$testdir = $statedir;
@mkdir($testdir);
$fp = fopen($testdir . DIRECTORY_SEPARATOR . 'test1.php', 'w');
fwrite($fp, '<?php
class blah {
function blah()
{
    foreach ($this->dsns as $i => $dsn) {
         $text .= "[$count] " . ($i ? "(Package $i) " : \'\') . $dsn . "\n";
         $count++;
    }
}

function ignored(){}
}
?>');
fclose($fp);

$res = $val->_analyzeSourceCode($testdir . DIRECTORY_SEPARATOR . 'test1.php');
$phpunit->assertNoErrors('post-parse');
$phpunit->assertEquals(array (
  'source_file' => $testdir . DIRECTORY_SEPARATOR . 'test1.php',
  'declared_classes' => 
  array (
    0 => 'blah',
  ),
  'declared_interfaces' => 
  array (
  ),
  'declared_methods' => 
  array (
    'blah' => 
    array (
      0 => 'blah',
      1 => 'ignored',
    ),
  ),
  'declared_functions' => 
  array (
  ),
  'used_classes' => 
  array (
  ),
  'inheritance' => 
  array (
  ),
  'implements' => 
  array (
  ),
), $res, 'analysis');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
