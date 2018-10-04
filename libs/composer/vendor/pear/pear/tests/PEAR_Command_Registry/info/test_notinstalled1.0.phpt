--TEST--
info command, package.xml 1.0
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('info', array(), array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'Console_Getopt-1.2.0.tgz'));
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 
    array (
      'caption' => 'About Console_Getopt-1.2.0',
      'border' => true,
      'data' => 
      array (
        0 => 
        array (
          0 => 'Provides',
          1 => 'Classes: Console_Getopt',
        ),
        1 => 
        array (
          0 => 'Package',
          1 => 'Console_Getopt',
        ),
        2 => 
        array (
          0 => 'Summary',
          1 => 'Command-line option parser',
        ),
        3 => 
        array (
          0 => 'Description',
          1 => 'This is a PHP implementation of "getopt" supporting both
short and long options.',
        ),
        4 => 
        array (
          0 => 'Maintainers',
          1 => 'Andrei Zmievski <andrei@php.net> (lead)
Stig Bakken <stig@php.net> (developer)
Greg Beaver <cellog@php.net> (helper)',
        ),
        5 => 
        array (
          0 => 'Version',
          1 => '1.2.0',
        ),
        6 => 
        array (
          0 => 'Release Date',
          1 => '2004-12-06',
        ),
        7 => 
        array (
          0 => 'Release License',
          1 => 'PHP License',
        ),
        8 => 
        array (
          0 => 'Release State',
          1 => 'stable',
        ),
        9 => 
        array (
          0 => 'Release Notes',
          1 => 'Fix to preserve BC with 1.0 and allow correct behaviour for new users',
        ),
        10 => 
        array (
          0 => 'Package.xml Version',
          1 => '1.0',
        ),
        11 => 
        array (
          0 => 'Packaged With PEAR Version',
          1 => '1.4.0a1',
        ),
      ),
      'raw' => 
      array (
        'provides' => 'Classes: Console_Getopt',
        'package' => 'Console_Getopt',
        'summary' => 'Command-line option parser',
        'description' => 'This is a PHP implementation of "getopt" supporting both
short and long options.',
        'maintainers' => 'Andrei Zmievski <andrei@php.net> (lead)
Stig Bakken <stig@php.net> (developer)
Greg Beaver <cellog@php.net> (helper)',
        'version' => '1.2.0',
        'release_date' => '2004-12-06',
        'release_license' => 'PHP License',
        'release_state' => 'stable',
        'release_notes' => 'Fix to preserve BC with 1.0 and allow correct behaviour for new users',
        'package.xml version' => '1.0',
        'packaged with PEAR version' => '1.4.0a1',
      ),
    ),
    'cmd' => 'package-info',
  ),
), $fakelog->getLog(), 'command');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
