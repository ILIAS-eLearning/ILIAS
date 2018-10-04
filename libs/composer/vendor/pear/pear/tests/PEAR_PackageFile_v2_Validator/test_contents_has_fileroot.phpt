--TEST--
PEAR_PackageFile_Parser_v2_Validator->validate(), <contents> contains <file> and not <dir>
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
    'test_contents'. DIRECTORY_SEPARATOR . 'package.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<contents> can only contain <dir>, contains <file>.  Use <dir name="/"> as the first dir element'),
), 'has <file>');

$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'test_contents'. DIRECTORY_SEPARATOR . 'package2.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'ret');
$pf->validate();
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v2', 'message' => '<contents> must contain <dir>.  Use <dir name="/"> as the first dir element'),
), 'no <dir>');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
