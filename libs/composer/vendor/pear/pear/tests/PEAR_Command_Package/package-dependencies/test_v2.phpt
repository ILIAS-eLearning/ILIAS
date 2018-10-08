--TEST--
package-dependencies command, package.xml 2.0
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
    'packagefiles' . DIRECTORY_SEPARATOR . 'v2.xml'));
$phpunit->assertNoErrors('v2');
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 
    array (
      'caption' => 'Dependencies for fakebar',
      'border' => true,
      'headline' => 
      array (
        0 => 'Required?',
        1 => 'Type',
        2 => 'Name',
        3 => 'Versioning',
        4 => 'Group',
      ),
      'data' => 
      array (
        0 => 
        array (
          0 => 'Yes',
          1 => 'Php',
          2 => '',
          3 => ' (version >= 4.3.0, version <= 6.0.0)',
          4 => '',
        ),
        1 => 
        array (
          0 => 'Yes',
          1 => 'Pear Installer',
          2 => '',
          3 => ' (version >= 1.4.0a1)',
          4 => '',
        ),
        2 => 
        array (
          0 => 'Yes',
          1 => 'Package',
          2 => 'pear/Console_Getopt',
          3 => ' (version <= 1.2, excluded versions: 1.2)',
          4 => '',
        ),
        3 => 
        array (
          0 => 'Yes',
          1 => 'Subpackage',
          2 => '(channel?) gronk.onk.net/foo_child',
          3 => ' (version <= 1.2, excluded versions: 1.2)',
          4 => '',
        ),
        4 => 
        array (
          0 => 'Yes',
          1 => 'Extension',
          2 => 'hi',
          3 => ' (version <= 2.0, excluded versions: 1.4)',
          4 => '',
        ),
        5 => 
        array (
          0 => 'Yes',
          1 => 'Os',
          2 => 'windows',
          3 => 'conflicts',
          4 => '',
        ),
        6 => 
        array (
          0 => 'Yes',
          1 => 'Arch',
          2 => '*',
          3 => '',
          4 => '',
        ),
        7 => 
        array (
          0 => 'No',
          1 => 'Package',
          2 => 'boomnp [http://www.bloop.example.com/boomnp.tgz]',
          3 => '',
          4 => '',
        ),
        8 => 
        array (
          0 => 'No',
          1 => 'Package',
          2 => 'pear/Archive_Tar',
          3 => ' (version >= 1.2, excluded versions: 1.2)',
          4 => '',
        ),
        9 => 
        array (
          0 => 'No',
          1 => 'Subpackage',
          2 => '(channel?) gronk.onk.net/foo_helper',
          3 => ' (version <= 1.2, excluded versions: 1.2)',
          4 => '',
        ),
        10 => 
        array (
          0 => 'No',
          1 => 'Extension',
          2 => 'xmlrpc',
          3 => ' (version >= 1.0)',
          4 => '',
        ),
        11 => 
        array (
          0 => 'No',
          1 => 'Package',
          2 => '(channel?) hi.example.com/hithere',
          3 => '',
          4 => 'default',
        ),
        12 => 
        array (
          0 => 'No',
          1 => 'Subpackage',
          2 => 'pear/hithere2',
          3 => '',
          4 => 'default',
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
