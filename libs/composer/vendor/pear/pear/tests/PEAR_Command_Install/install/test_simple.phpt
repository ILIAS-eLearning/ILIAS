--TEST--
install command, simplest possible test
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'simplepackage.xml';
$res = $command->run('install', array(), array($pathtopackagexml));
$phpunit->assertNoErrors('after install');
$phpunit->assertTrue($res, 'result');
$dummy = null;
$dl = &$command->getDownloader($dummy, array());
if (OS_WINDOWS) {
    $phpunit->assertEquals(array (
      0 =>
      array (
        0 => 3,
        1 => '+ cp ' . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'foo.php ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.tmpfoo.php',
      ),
      1 =>
      array (
        0 => 3,
        1 => 'adding to transaction: rename ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.tmpfoo.php ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php ',
      ),
      2 =>
      array (
        0 => 3,
        1 => 'adding to transaction: installed_as foo.php ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php ' . $temp_path . DIRECTORY_SEPARATOR . 'php ' . DIRECTORY_SEPARATOR,
      ),
      3 =>
      array (
        0 => 2,
        1 => 'about to commit 2 file operations for PEAR',
      ),
      4 =>
      array (
        0 => 3,
        1 => '+ mv ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.tmpfoo.php ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php',
      ),
      5 =>
      array (
        0 => 2,
        1 => 'successfully committed 2 file operations',
      ),
      6 =>
      array (
        'info' =>
        array (
          'data' => 'install ok: channel://pear.php.net/PEAR-1.4.0a1',
        ),
        'cmd' => 'install',
       ),
    ), $fakelog->getLog(), 'log messages');
} else {
    // Don't forget umask ! permission of new file is 0666
    $umask = decoct(0666 & ( 0777 - umask()));
    $phpunit->assertEquals(array (
      0 =>
      array (
        0 => 3,
        1 => '+ cp ' . dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . 'foo.php ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.tmpfoo.php',
      ),
      1 =>
      array (
        0 => 3,
        1 => 'adding to transaction: chmod '.$umask.' ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.tmpfoo.php',
      ),
      2 =>
      array (
        0 => 3,
        1 => 'adding to transaction: rename ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.tmpfoo.php ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php ',
      ),
      3 =>
      array (
        0 => 3,
        1 => 'adding to transaction: installed_as foo.php ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php ' . $temp_path . DIRECTORY_SEPARATOR . 'php ' . DIRECTORY_SEPARATOR,
      ),
      4 =>
      array (
        0 => 2,
        1 => 'about to commit 3 file operations for PEAR',
      ),
      5 =>
      array (
        0 => 3,
        1 => '+ chmod '.$umask.' ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.tmpfoo.php',
      ),
      6 =>
      array (
        0 => 3,
        1 => '+ mv ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . '.tmpfoo.php ' . $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'foo.php',
      ),
      7 =>
      array (
        0 => 2,
        1 => 'successfully committed 3 file operations',
      ),
      8 =>
      array (
        'info' =>
        array (
          'data' => 'install ok: channel://pear.php.net/PEAR-1.4.0a1',
        ),
        'cmd' => 'install',
       ),
    ), $fakelog->getLog(), 'log messages');
}
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
