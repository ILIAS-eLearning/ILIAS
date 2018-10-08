--TEST--
PEAR_PackageFile_Parser_v1->addConfigureoption() invalid
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
$pf = &$parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v1', $pf, 'return of valid parse');
$phpunit->assertFalse($pf->getConfigureOptions(), 'pre-set');
$pf->addConfigureoption('', 'hi there');
$phpunit->assertEquals(array (
  0 => 
  array (
    'name' => '',
    'prompt' => 'hi there',
  ),
), $pf->getConfigureOptions(), 'set 1 failed');

$r = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'Configure Option 1 has no name')
), 'first');

$pf->clearConfigureOptions();
$pf->addConfigureoption('blah', '');
$phpunit->assertEquals(array (
  0 => 
  array (
    'name' => 'blah',
    'prompt' => '',
  ),
), $pf->getConfigureOptions(), 'set 1 failed');

$r = $pf->validate(PEAR_VALIDATE_NORMAL);
$phpunit->assertErrors(array(
    array('package' => 'PEAR_PackageFile_v1',
        'message' => 'Configure Option 1 has no prompt')
), 'first');

echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
