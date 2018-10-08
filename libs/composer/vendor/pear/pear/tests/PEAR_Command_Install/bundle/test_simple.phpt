--TEST--
bundle command, simplest possible test
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
if (!extension_loaded('zlib')) {
    echo 'skip zlib extension needed';
}
?>
--FILE--
<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';

$path = dirname(__FILE__)  . DIRECTORY_SEPARATOR;
$pathtopackagexml = $path .
    'packages'. DIRECTORY_SEPARATOR . 'Archive_Tar-1.2.tgz';
$ap = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'Archive_Tar';
chdir($temp_path);
$phpunit->assertFileNotExists($temp_path . '/Archive_Tar', 'before');
$res = $command->run('bundle', array(), array($pathtopackagexml));
$phpunit->assertNoErrors('after bundle');
$phpunit->assertNull($res, 'result');

$phpunit->assertFileExists($temp_path . '/ext/Archive_Tar', 'after');
$phpunit->assertFileExists($temp_path . '/ext/Archive_Tar/Archive', 'after 2');
$phpunit->assertFileExists($temp_path . '/ext/Archive_Tar/docs', 'after 3');
$phpunit->assertFileExists($temp_path . '/ext/Archive_Tar/package.xml', 'package.xml exists');
$tp = $temp_path . '/ext/Archive_Tar';
$phpunit->assertEquals(
    file_get_contents($ap . DIRECTORY_SEPARATOR . 'package.xml'),
    file_get_contents($tp . DIRECTORY_SEPARATOR . 'package.xml'),
    'package.xml same'
    );
$phpunit->assertFileExists($tp . '/Archive/Tar.php', 'Archive/Tar.php exists');
$phpunit->assertEquals(
    file_get_contents($ap . DIRECTORY_SEPARATOR . 'Archive/Tar.php'),
    file_get_contents($tp . DIRECTORY_SEPARATOR . 'Archive/Tar.php'),
    'Archive/Tar.php same'
    );
$phpunit->assertFileExists($tp . '/docs/Archive_Tar.txt', 'doc/Archive_Tar.txt exists');
$phpunit->assertEquals(
    file_get_contents($ap . DIRECTORY_SEPARATOR . 'docs/Archive_Tar.txt'),
    file_get_contents($tp . DIRECTORY_SEPARATOR . 'docs/Archive_Tar.txt'),
    'docs/Archive_Tar.txt same'
    );
$phpunit->assertEquals(array(
  0 =>
  array (
    'info' => "Package ready at '" . dirname($path) . "/testinstallertemp/ext/Archive_Tar'",
    'cmd' => 'no command',
  ),
), $fakelog->getLog(), 'log messages');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
