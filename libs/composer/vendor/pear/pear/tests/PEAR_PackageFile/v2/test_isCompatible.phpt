--TEST--
PEAR_PackageFile_Parser_v2->isCompatible()
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
    'test_basicparse'. DIRECTORY_SEPARATOR . 'package2.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$pf->flattenFilelist();
$pfa = &$pf->getRW();
$pf = &$pfa;

$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'return of valid parse');
$pf2 = new PEAR_PackageFile_v2_rw;
$pf2->setConfig($config);
$pf2->setPackage('frong');
$pf2->setChannel('glook');
$pf2->setReleaseVersion('0.1.0');
$pf->addCompatiblePackage('frong', 'glook', '0.2.0', '1.2.0', array('0.8.0', '0.7.5'));
$a = $pf->isCompatible($pf2);
$phpunit->assertFalse($a, '0.1.0');

$pf2->setReleaseVersion('0.2.0');
$a = $pf->isCompatible($pf2);
$phpunit->assertTrue($a, '0.2.0');

$pf2->setReleaseVersion('1.2.0');
$a = $pf->isCompatible($pf2);
$phpunit->assertTrue($a, '1.2.0');

$pf2->setReleaseVersion('1.2.1');
$a = $pf->isCompatible($pf2);
$phpunit->assertFalse($a, '1.2.1');

$pf2->setReleaseVersion('0.8.0');
$a = $pf->isCompatible($pf2);
$phpunit->assertFalse($a, '0.8.0');

$pf2->setReleaseVersion('0.7.5');
$a = $pf->isCompatible($pf2);
$phpunit->assertFalse($a, '0.7.5');

$pf2->setPackage('not');
$a = $pf->isCompatible($pf2);
$phpunit->assertFalse($a, 'not');

$pf2->setPackage('frong');
$pf2->setChannel('glonk');
$a = $pf->isCompatible($pf2);
$phpunit->assertFalse($a, 'glonk');

$pf2->setURI('http://www.example.com/duh.tgz');
$a = $pf->isCompatible($pf2);
$phpunit->assertFalse($a, 'uri');

$pf2->setPackage('frong');
$pf2->setChannel('glook');
$pf->setUri('blah');
$a = $pf->isCompatible($pf2);
$phpunit->assertFalse($a, 'no channel');

$pf->setChannel('pear.php.net');
$pf->clearCompatible();
$a = $pf->isCompatible($pf2);
$phpunit->assertFalse($a, 'no compatible');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
