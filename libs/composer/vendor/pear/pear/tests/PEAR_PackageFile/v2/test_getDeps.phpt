--TEST--
PEAR_PackageFile_Parser_v2->getDeps
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
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'return of valid parse');
$pfa = &$pf->getRW();
$pf = &$pfa;
$phpunit->assertEquals(array (
  0 => 
  array (
    'type' => 'php',
    'rel' => 'le',
    'version' => '6.0.0',
    'optional' => 'no',
  ),
  1 => 
  array (
    'type' => 'php',
    'rel' => 'ge',
    'version' => '4.3.6',
    'optional' => 'no',
  ),
  2 => 
  array (
    'type' => 'pkg',
    'channel' => 'pear.php.net',
    'name' => 'PEAR',
    'rel' => 'ge',
    'version' => '1.4.0a1',
    'optional' => 'no',
  ),
), $pf->getDeps(), 'pre-set');
$pf->addOsDep('windows');
$phpunit->assertEquals(array (
  0 => 
  array (
    'type' => 'php',
    'rel' => 'le',
    'version' => '6.0.0',
    'optional' => 'no',
  ),
  1 => 
  array (
    'type' => 'php',
    'rel' => 'ge',
    'version' => '4.3.6',
    'optional' => 'no',
  ),
  2 => 
  array (
    'type' => 'pkg',
    'channel' => 'pear.php.net',
    'name' => 'PEAR',
    'rel' => 'ge',
    'version' => '1.4.0a1',
    'optional' => 'no',
  ),
  3 =>
  array (
    'type' => 'os',
    'name' => 'windows',
    'rel' => 'has',
    'optional' => 'no',
  ),
), $pf->getDeps(), 'windows');
$pf->addOsDep('linux', true);
$phpunit->assertEquals(array (
  0 => 
  array (
    'type' => 'php',
    'rel' => 'le',
    'version' => '6.0.0',
    'optional' => 'no',
  ),
  1 => 
  array (
    'type' => 'php',
    'rel' => 'ge',
    'version' => '4.3.6',
    'optional' => 'no',
  ),
  2 => 
  array (
    'type' => 'pkg',
    'channel' => 'pear.php.net',
    'name' => 'PEAR',
    'rel' => 'ge',
    'version' => '1.4.0a1',
    'optional' => 'no',
  ),
  3 =>
  array (
    'type' => 'os',
    'name' => 'windows',
    'rel' => 'has',
    'optional' => 'no',
  ),
  4 =>
  array (
    'type' => 'os',
    'name' => 'linux',
    'rel' => 'not'
  ),
), $pf->getDeps(), 'not linux');
$pf->addConflictingPackageDepWithChannel('zoorgon', 'zonk.example.com');
$phpunit->assertEquals(array (
  0 => 
  array (
    'type' => 'php',
    'rel' => 'le',
    'version' => '6.0.0',
    'optional' => 'no',
  ),
  1 => 
  array (
    'type' => 'php',
    'rel' => 'ge',
    'version' => '4.3.6',
    'optional' => 'no',
  ),
  2 => 
  array (
    'type' => 'pkg',
    'channel' => 'pear.php.net',
    'name' => 'PEAR',
    'rel' => 'ge',
    'version' => '1.4.0a1',
    'optional' => 'no',
  ),
  3 =>
  array (
    'type' => 'pkg',
    'channel' => 'zonk.example.com',
    'name' => 'zoorgon',
    'rel' => 'not',
  ),
  4 =>
  array (
    'type' => 'os',
    'name' => 'windows',
    'rel' => 'has',
    'optional' => 'no',
  ),
  5 =>
  array (
    'type' => 'os',
    'name' => 'linux',
    'rel' => 'not'
  ),
), $pf->getDeps(), 'not zoorgon');
$pf->addSubpackageDepWithChannel('optional', 'blah', 'blah.example.com', '1.0', '2.0', '1.4', array('1.2'));
$phpunit->assertEquals(array (
  0 => 
  array (
    'type' => 'php',
    'rel' => 'le',
    'version' => '6.0.0',
    'optional' => 'no',
  ),
  1 => 
  array (
    'type' => 'php',
    'rel' => 'ge',
    'version' => '4.3.6',
    'optional' => 'no',
  ),
  2 => 
  array (
    'type' => 'pkg',
    'channel' => 'pear.php.net',
    'name' => 'PEAR',
    'rel' => 'ge',
    'version' => '1.4.0a1',
    'optional' => 'no',
  ),
  3 =>
  array (
    'type' => 'pkg',
    'channel' => 'zonk.example.com',
    'name' => 'zoorgon',
    'rel' => 'not',
  ),
  array (
    'type' => 'os',
    'name' => 'windows',
    'rel' => 'has',
    'optional' => 'no',
  ),
  array (
    'type' => 'os',
    'name' => 'linux',
    'rel' => 'not'
  ),
  array (
    'type' => 'pkg',
    'channel' => 'blah.example.com',
    'name' => 'blah',
    'rel' => 'le',
    'version' => '2.0',
    'optional' => 'yes',
  ),
  array (
    'type' => 'pkg',
    'channel' => 'blah.example.com',
    'name' => 'blah',
    'rel' => 'ge',
    'version' => '1.0',
    'optional' => 'yes',
  ),
), $pf->getDeps(), 'optional blah');
$pf->addSubpackageDepWithURI('required', 'arnk', 'arnk.example.com/arnk.tgz');
$phpunit->assertEquals(array (
  0 => 
  array (
    'type' => 'php',
    'rel' => 'le',
    'version' => '6.0.0',
    'optional' => 'no',
  ),
  1 => 
  array (
    'type' => 'php',
    'rel' => 'ge',
    'version' => '4.3.6',
    'optional' => 'no',
  ),
  2 => 
  array (
    'type' => 'pkg',
    'channel' => 'pear.php.net',
    'name' => 'PEAR',
    'rel' => 'ge',
    'version' => '1.4.0a1',
    'optional' => 'no',
  ),
  3 =>
  array (
    'type' => 'pkg',
    'channel' => 'zonk.example.com',
    'name' => 'zoorgon',
    'rel' => 'not',
  ),
  array (
    'type' => 'pkg',
    'uri' => 'arnk.example.com/arnk.tgz',
    'name' => 'arnk',
    'rel' => 'has',
    'optional' => 'no',
  ),
  array (
    'type' => 'os',
    'name' => 'windows',
    'rel' => 'has',
    'optional' => 'no',
  ),
  array (
    'type' => 'os',
    'name' => 'linux',
    'rel' => 'not'
  ),
  array (
    'type' => 'pkg',
    'channel' => 'blah.example.com',
    'name' => 'blah',
    'rel' => 'le',
    'version' => '2.0',
    'optional' => 'yes',
  ),
  array (
    'type' => 'pkg',
    'channel' => 'blah.example.com',
    'name' => 'blah',
    'rel' => 'ge',
    'version' => '1.0',
    'optional' => 'yes',
  ),
), $pf->getDeps(), 'required arnk');
$pf->addPackageDepWithURI('optional', 'zarnk', 'zarnk.example.com/zarnk.tgz');
$phpunit->assertEquals(array (
  0 => 
  array (
    'type' => 'php',
    'rel' => 'le',
    'version' => '6.0.0',
    'optional' => 'no',
  ),
  1 => 
  array (
    'type' => 'php',
    'rel' => 'ge',
    'version' => '4.3.6',
    'optional' => 'no',
  ),
  2 => 
  array (
    'type' => 'pkg',
    'channel' => 'pear.php.net',
    'name' => 'PEAR',
    'rel' => 'ge',
    'version' => '1.4.0a1',
    'optional' => 'no',
  ),
  3 =>
  array (
    'type' => 'pkg',
    'channel' => 'zonk.example.com',
    'name' => 'zoorgon',
    'rel' => 'not',
  ),
  array (
    'type' => 'pkg',
    'uri' => 'arnk.example.com/arnk.tgz',
    'name' => 'arnk',
    'rel' => 'has',
    'optional' => 'no',
  ),
  array (
    'type' => 'os',
    'name' => 'windows',
    'rel' => 'has',
    'optional' => 'no',
  ),
  array (
    'type' => 'os',
    'name' => 'linux',
    'rel' => 'not'
  ),
  array (
    'type' => 'pkg',
    'uri' => 'zarnk.example.com/zarnk.tgz',
    'name' => 'zarnk',
    'rel' => 'has',
    'optional' => 'yes',
  ),
  array (
    'type' => 'pkg',
    'channel' => 'blah.example.com',
    'name' => 'blah',
    'rel' => 'le',
    'version' => '2.0',
    'optional' => 'yes',
  ),
  array (
    'type' => 'pkg',
    'channel' => 'blah.example.com',
    'name' => 'blah',
    'rel' => 'ge',
    'version' => '1.0',
    'optional' => 'yes',
  ),
), $pf->getDeps(), 'optional zarnk');
$pf->addExtensionDep('required', 'fooz');
$phpunit->assertEquals(array (
  0 => 
  array (
    'type' => 'php',
    'rel' => 'le',
    'version' => '6.0.0',
    'optional' => 'no',
  ),
  1 => 
  array (
    'type' => 'php',
    'rel' => 'ge',
    'version' => '4.3.6',
    'optional' => 'no',
  ),
  2 => 
  array (
    'type' => 'pkg',
    'channel' => 'pear.php.net',
    'name' => 'PEAR',
    'rel' => 'ge',
    'version' => '1.4.0a1',
    'optional' => 'no',
  ),
  3 =>
  array (
    'type' => 'pkg',
    'channel' => 'zonk.example.com',
    'name' => 'zoorgon',
    'rel' => 'not',
  ),
  array (
    'type' => 'pkg',
    'uri' => 'arnk.example.com/arnk.tgz',
    'name' => 'arnk',
    'rel' => 'has',
    'optional' => 'no',
  ),
  array (
    'type' => 'ext',
    'name' => 'fooz',
    'rel' => 'has',
    'optional' => 'no',
  ),
  array (
    'type' => 'os',
    'name' => 'windows',
    'rel' => 'has',
    'optional' => 'no',
  ),
  array (
    'type' => 'os',
    'name' => 'linux',
    'rel' => 'not'
  ),
  array (
    'type' => 'pkg',
    'uri' => 'zarnk.example.com/zarnk.tgz',
    'name' => 'zarnk',
    'rel' => 'has',
    'optional' => 'yes',
  ),
  array (
    'type' => 'pkg',
    'channel' => 'blah.example.com',
    'name' => 'blah',
    'rel' => 'le',
    'version' => '2.0',
    'optional' => 'yes',
  ),
  array (
    'type' => 'pkg',
    'channel' => 'blah.example.com',
    'name' => 'blah',
    'rel' => 'ge',
    'version' => '1.0',
    'optional' => 'yes',
  ),
), $pf->getDeps(), 'fooz extension');
$pf->addExtensionDep('optional', 'barz', '1.1', '1.5', '1.3', array('1.6'));
$phpunit->assertEquals(array (
  0 => 
  array (
    'type' => 'php',
    'rel' => 'le',
    'version' => '6.0.0',
    'optional' => 'no',
  ),
  1 => 
  array (
    'type' => 'php',
    'rel' => 'ge',
    'version' => '4.3.6',
    'optional' => 'no',
  ),
  2 => 
  array (
    'type' => 'pkg',
    'channel' => 'pear.php.net',
    'name' => 'PEAR',
    'rel' => 'ge',
    'version' => '1.4.0a1',
    'optional' => 'no',
  ),
  3 =>
  array (
    'type' => 'pkg',
    'channel' => 'zonk.example.com',
    'name' => 'zoorgon',
    'rel' => 'not',
  ),
  array (
    'type' => 'pkg',
    'uri' => 'arnk.example.com/arnk.tgz',
    'name' => 'arnk',
    'rel' => 'has',
    'optional' => 'no',
  ),
  array (
    'type' => 'ext',
    'name' => 'fooz',
    'rel' => 'has',
    'optional' => 'no',
  ),
  array (
    'type' => 'os',
    'name' => 'windows',
    'rel' => 'has',
    'optional' => 'no',
  ),
  array (
    'type' => 'os',
    'name' => 'linux',
    'rel' => 'not'
  ),
  array (
    'type' => 'pkg',
    'uri' => 'zarnk.example.com/zarnk.tgz',
    'name' => 'zarnk',
    'rel' => 'has',
    'optional' => 'yes',
  ),
  array (
    'type' => 'pkg',
    'channel' => 'blah.example.com',
    'name' => 'blah',
    'rel' => 'le',
    'version' => '2.0',
    'optional' => 'yes',
  ),
  array (
    'type' => 'pkg',
    'channel' => 'blah.example.com',
    'name' => 'blah',
    'rel' => 'ge',
    'version' => '1.0',
    'optional' => 'yes',
  ),
  array (
    'type' => 'ext',
    'name' => 'barz',
    'rel' => 'le',
    'version' => '1.5',
    'optional' => 'yes'
  ),
  array (
    'type' => 'ext',
    'name' => 'barz',
    'rel' => 'ge',
    'version' => '1.1',
    'optional' => 'yes'
  ),
), $pf->getDeps(), 'barz works');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
