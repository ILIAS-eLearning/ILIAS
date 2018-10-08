--TEST--
PEAR_PackageFile_Parser_v2_Validator->analyzeSourceCode(), bug #5717
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
    'test_nochanneloruri'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$pf = new PEAR_PackageFile_V2;
$res = $pf->analyzeSourceCode('<?php
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
?>', true);
$phpunit->assertNoErrors('post-parse');
$phpunit->assertEquals(array (
  'source_file' => '<?php
class blah {
function blah()
{
    foreach ($this->dsns as $i => $dsn) {
         $text .= "[$count] " . ($i ? "(Package $i) " : \'\') . $dsn . "\\n";
         $count++;
    }
}

function ignored(){}
}
?>',
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
