--TEST--
info command, installed package, package.xml 2.0
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$_test_dep->setPEARVersion('1.7.0');
$_test_dep->setPHPVersion('4.3.11');

$downloader = new test_PEAR_Downloader($fakelog, array(), $config);
$installer = new test_PEAR_Installer($fakelog);
$downloaded = &$downloader->download(array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'packagefiles' .
    DIRECTORY_SEPARATOR . 'Console_Getopt-1.2.1.tgz'));
$phpunit->assertNoErrors('"download"');
$phpunit->assertEquals(array(), $downloader->getErrorMsgs(), 'downloader errmessages');
$installer->setOptions(array());
$installer->setDownloadedPackages($downloaded);
$installer->install($downloaded[0], array());
$phpunit->assertNoErrors('setup');
$fakelog->getLog();
$e = $command->run('info', array(), array('Console_Getopt'));
$reg = &$config->getRegistry();
$lastmodified = $reg->packageInfo('Console_Getopt', '_lastmodified');
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 
    array (
      'caption' => 'About pear.php.net/Console_Getopt-1.2.1',
      'border' => true,
      'data' => 
      array (
        0 => 
        array (
          0 => 'Release Type',
          1 => 'PEAR-style PHP-based Package',
        ),
        1 => 
        array (
          0 => 'Name',
          1 => 'Console_Getopt',
        ),
        2 => 
        array (
          0 => 'Channel',
          1 => 'pear.php.net',
        ),
        3 => 
        array (
          0 => 'Summary',
          1 => 'Command-line option parser',
        ),
        4 => 
        array (
          0 => 'Description',
          1 => 'This is a PHP implementation of "getopt" supporting both
short and long options.',
        ),
        5 => 
        array (
          0 => 'Maintainers',
          1 => 'Andrei Zmievski <andrei@php.net> (lead)
Stig Bakken <stig@php.net> (developer)
Greg Beaver <cellog@php.net> (helper)',
        ),
        6 => 
        array (
          0 => 'Release Date',
          1 => '2004-12-06 11:57:00',
        ),
        7 => 
        array (
          0 => 'Release Version',
          1 => '1.2.1 (stable)',
        ),
        8 => 
        array (
          0 => 'API Version',
          1 => '1.2.1 (stable)',
        ),
        9 => 
        array (
          0 => 'License',
          1 => 'PHP License (http://www.php.net/license/3_0.txt)',
        ),
        10 => 
        array (
          0 => 'Release Notes',
          1 => 'Fix to preserve BC with 1.0 and allow correct behaviour for new users',
        ),
        11 => 
        array (
          0 => 'Required Dependencies',
          1 => 'PHP version 4.3.6-6.0.0
PEAR installer version 1.4.0a1 or newer',
        ),
        12 => 
        array (
          0 => 'package.xml version',
          1 => '2.0',
        ),
        13 => 
        array (
          0 => 'Last Modified',
          1 => date('Y-m-d H:i', $lastmodified),
        ),
        14 => 
        array (
          0 => 'Previous Installed Version',
          1 => '- None -',
        ),
      ),
      'raw' => 
      array (
        'attribs' => 
        array (
          'packagerversion' => '1.4.0a1',
          'version' => '2.0',
          'xmlns' => 'http://pear.php.net/dtd/package-2.0',
          'xmlns:tasks' => 'http://pear.php.net/dtd/tasks-1.0',
          'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
          'xsi:schemaLocation' => 'http://pear.php.net/dtd/tasks-1.0 http://pear.php.net/dtd/tasks-1.0.xsd http://pear.php.net/dtd/package-2.0 http://pear.php.net/dtd/package-2.0.xsd',
        ),
        'name' => 'Console_Getopt',
        'channel' => 'pear.php.net',
        'summary' => 'Command-line option parser',
        'description' => 'This is a PHP implementation of "getopt" supporting both
short and long options.',
        'lead' => 
        array (
          'name' => 'Andrei Zmievski',
          'user' => 'andrei',
          'email' => 'andrei@php.net',
          'active' => 'yes',
        ),
        'developer' => 
        array (
          'name' => 'Stig Bakken',
          'user' => 'ssb',
          'email' => 'stig@php.net',
          'active' => 'yes',
        ),
        'helper' => 
        array (
          'name' => 'Greg Beaver',
          'user' => 'cellog',
          'email' => 'cellog@php.net',
          'active' => 'yes',
        ),
        'date' => '2004-12-06',
        'time' => '11:57:00',
        'version' => 
        array (
          'release' => '1.2.1',
          'api' => '1.2.1',
        ),
        'stability' => 
        array (
          'release' => 'stable',
          'api' => 'stable',
        ),
        'license' => 
        array (
          'attribs' => 
          array (
            'uri' => 'http://www.php.net/license/3_0.txt',
          ),
          '_content' => 'PHP License',
        ),
        'notes' => 'Fix to preserve BC with 1.0 and allow correct behaviour for new users',
        'contents' => 
        array (
          'dir' => 
          array (
            'attribs' => 
            array (
              'name' => '/',
            ),
            'file' => 
            array (
              'attribs' => 
              array (
                'md5sum' => '3bd7b9b81b24bc7f538a1bd62ac87300',
                'name' => 'Console/Getopt.php',
                'role' => 'php',
              ),
            ),
          ),
        ),
        'dependencies' => 
        array (
          'required' => 
          array (
            'php' => 
            array (
              'min' => '4.3.6',
              'max' => '6.0.0',
            ),
            'pearinstaller' => 
            array (
              'min' => '1.4.0a1',
            ),
          ),
        ),
        'phprelease' => '',
        'changelog' => 
        array (
          'release' => 
          array (
            0 => 
            array (
              'version' => 
              array (
                'release' => '1.0',
                'api' => '1.0',
              ),
              'stability' => 
              array (
                'release' => 'stable',
                'api' => 'stable',
              ),
              'date' => '2002-09-13',
              'license' => 
              array (
                'attribs' => 
                array (
                  'uri' => 'http://www.php.net/license/3_0.txt',
                ),
                '_content' => 'PHP License',
              ),
              'notes' => 'Stable release',
            ),
            1 => 
            array (
              'version' => 
              array (
                'release' => '0.11',
                'api' => '0.11',
              ),
              'stability' => 
              array (
                'release' => 'beta',
                'api' => 'beta',
              ),
              'date' => '2002-05-26',
              'license' => 
              array (
                'attribs' => 
                array (
                  'uri' => 'http://www.php.net/license/3_0.txt',
                ),
                '_content' => 'PHP License',
              ),
              'notes' => 'POSIX getopt compatibility fix: treat first element of args
        array as command name',
            ),
            2 => 
            array (
              'version' => 
              array (
                'release' => '0.10',
                'api' => '0.10',
              ),
              'stability' => 
              array (
                'release' => 'beta',
                'api' => 'beta',
              ),
              'date' => '2002-05-12',
              'license' => 
              array (
                'attribs' => 
                array (
                  'uri' => 'http://www.php.net/license/3_0.txt',
                ),
                '_content' => 'PHP License',
              ),
              'notes' => 'Packaging fix',
            ),
            3 => 
            array (
              'version' => 
              array (
                'release' => '0.9',
                'api' => '0.9',
              ),
              'stability' => 
              array (
                'release' => 'beta',
                'api' => 'beta',
              ),
              'date' => '2002-05-12',
              'license' => 
              array (
                'attribs' => 
                array (
                  'uri' => 'http://www.php.net/license/3_0.txt',
                ),
                '_content' => 'PHP License',
              ),
              'notes' => 'Initial release',
            ),
          ),
        ),
        'filelist' => 
        array (
          'Console/Getopt.php' => 
          array (
            'md5sum' => '3bd7b9b81b24bc7f538a1bd62ac87300',
            'name' => 'Console/Getopt.php',
            'role' => 'php',
            'installed_as' => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR .
                'Console' . DIRECTORY_SEPARATOR . 'Getopt.php',
          ),
        ),
        '_lastversion' => null,
        '_lastmodified' => $lastmodified,
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
