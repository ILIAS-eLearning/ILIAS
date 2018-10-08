--TEST--
package-dependencies command, package.xml 1.0
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('package-dependencies', array(), array(dirname(__FILE__) . DIRECTORY_SEPARATOR .
    'packagefiles' . DIRECTORY_SEPARATOR . 'v1.xml'));
$phpunit->assertNoErrors('v1');
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 
    array (
      'caption' => 'Dependencies for pear/foo',
      'border' => true,
      'headline' => 
      array (
        0 => 'Required?',
        1 => 'Type',
        2 => 'Name',
        3 => 'Relation',
        4 => 'Version',
      ),
      'data' => 
      array (
        0 => 
        array (
          0 => 'Yes',
          1 => 'PHP',
          2 => '',
          3 => '!=',
          4 => '4.3.10',
        ),
        1 => 
        array (
          0 => 'Yes',
          1 => 'Extension',
          2 => 'brump',
          3 => 'not',
          4 => '',
        ),
        2 => 
        array (
          0 => 'Yes',
          1 => 'Extension',
          2 => 'zoomp',
          3 => '>=',
          4 => '1.0',
        ),
        3 => 
        array (
          0 => 'No',
          1 => 'Extension',
          2 => 'xmlrpc',
          3 => '>=',
          4 => '1.0',
        ),
        4 => 
        array (
          0 => 'Yes',
          1 => 'Package',
          2 => 'Console_Getopt',
          3 => '<',
          4 => '1.2',
        ),
        5 => 
        array (
          0 => 'Yes',
          1 => 'Package',
          2 => 'Archive_Tar',
          3 => '<=',
          4 => '2.0',
        ),
        6 => 
        array (
          0 => 'Yes',
          1 => 'PHP',
          2 => '',
          3 => '>=',
          4 => '4.3.0',
        ),
      ),
    ),
    'cmd' => 'package-dependencies',
  ),
), $fakelog->getLog(), 'log 1');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
