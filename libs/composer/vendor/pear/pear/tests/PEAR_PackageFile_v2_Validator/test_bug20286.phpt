--TEST--
PEAR_PackageFile_Parser_v2_Validator->analyzeSourceCode, bug #20286: PHP 5.3 static variable on object
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';

require_once 'PEAR/PackageFile/v2/Validator.php';
$validator = new PEAR_PackageFile_v2_Validator;
$validator->_stack = new PEAR_ErrorStack('PEAR_PackageFile_v2', false, null);

$testdir = $statedir;
@mkdir($testdir);
file_put_contents(
    $testdir . '/bug20286-test.php',
    '<?php
$a::$b;
?>
'
);

$ret = $validator->analyzeSourceCode($testdir . '/bug20286-test.php');
$phpunit->assertNoErrors('valid PHP');

echo "tests done\n";
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
