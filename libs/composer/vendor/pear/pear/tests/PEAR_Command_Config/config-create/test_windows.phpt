--TEST--
config-create command --windows
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$temp_path2 = str_replace(DIRECTORY_SEPARATOR, '\\', $temp_path);
$e = $command->run('config-create', array('windows' => true), array($temp_path2 . DIRECTORY_SEPARATOR . 'nomake', $temp_path . DIRECTORY_SEPARATOR
    . 'remote.ini'));
$phpunit->assertNoErrors('log errors');
$log = $fakelog->getLog();
$log[0]['info']['data']['Internet Access'] = array_slice($log[0]['info']['data']['Internet Access'], 0, 6);
$log[0]['info']['data']['File Locations'] = array_slice($log[0]['info']['data']['File Locations'], 0, 4);
$log[0]['info']['data']['File Locations (Advanced)'] = array_slice($log[0]['info']['data']['File Locations (Advanced)'], 0, 13);
$log[0]['info']['data']['Advanced'] = array_slice($log[0]['info']['data']['Advanced'], 0, 4);
$log[0]['info']['data']['Maintainers'] = array_slice($log[0]['info']['data']['Maintainers'], 0, 6);
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' =>
    array (
      'caption' => 'Configuration (channel pear.php.net):',
      'data' =>
      array (
        'Internet Access' =>
        array (
          0 =>
          array (
            0 => 'Auto-discover new Channels',
            1 => 'auto_discover',
            2 => NULL,
          ),
          1 =>
          array (
            0 => 'Default Channel',
            1 => 'default_channel',
            2 => 'pear.php.net',
          ),
          2 =>
          array (
            0 => 'HTTP Proxy Server Address',
            1 => 'http_proxy',
            2 => NULL,
          ),
          3 =>
          array (
            0 => 'PEAR server [DEPRECATED]',
            1 => 'master_server',
            2 => NULL,
          ),
          4 =>
          array (
            0 => 'Default Channel Mirror',
            1 => 'preferred_mirror',
            2 => NULL,
          ),
          5 =>
          array (
            0 => 'Remote Configuration File',
            1 => 'remote_config',
            2 => NULL,
          ),
        ),
        'File Locations' =>
        array (
          0 =>
          array (
            0 => 'PEAR executables directory',
            1 => 'bin_dir',
            2 => '' . $temp_path2 . '\\nomake\\pear',
          ),
          1 =>
          array (
            0 => 'PEAR documentation directory',
            1 => 'doc_dir',
            2 => '' . $temp_path2 . '\\nomake\\pear\\docs',
          ),
          2 =>
          array (
            0 => 'PHP extension directory',
            1 => 'ext_dir',
            2 => '' . $temp_path2 . '\\nomake\\pear\\ext',
          ),
          3 =>
          array (
            0 => 'PEAR directory',
            1 => 'php_dir',
            2 => '' . $temp_path2 . '\\nomake\\pear\\php',
          ),
        ),
        'File Locations (Advanced)' =>
        array (
          array (
            0 => 'PEAR Installer cache directory',
            1 => 'cache_dir',
            2 => '' . $temp_path2 . '\\nomake\\pear\\cache',
          ),
          array (
            0 => 'PEAR configuration file directory',
            1 => 'cfg_dir',
            2 => $temp_path2 . '\\nomake\\pear\\cfg',
          ),
          array (
            0 => 'PEAR data directory',
            1 => 'data_dir',
            2 => '' . $temp_path2 . '\\nomake\\pear\\data',
          ),
          array (
            0 => 'PEAR Installer download directory',
            1 => 'download_dir',
            2 => $temp_path2 . '\\nomake\\pear\\download',
          ),
          array (
            0 => 'Systems manpage files directory',
            1 => 'man_dir',
            2 => $temp_path2 . '\\nomake\\pear\\man',
          ),
          array (
            0 => 'PEAR metadata directory',
            1 => 'metadata_dir',
            2 => NULL,
          ),
          array (
            0 => 'PHP CLI/CGI binary',
            1 => 'php_bin',
            2 => NULL,
          ),
          array (
            0 => 'php.ini location',
            1 => 'php_ini',
            2 => NULL,
          ),
          array (
            0 => '--program-prefix passed to PHP\'s ./configure',
            1 => 'php_prefix',
            2 => NULL,
          ),
          array (
            0 => '--program-suffix passed to PHP\'s ./configure',
            1 => 'php_suffix',
            2 => NULL,
          ),
          array (
            0 => 'PEAR Installer temp directory',
            1 => 'temp_dir',
            2 => $temp_path2 . '\\nomake\\pear\\temp',
          ),
          array (
            0 => 'PEAR test directory',
            1 => 'test_dir',
            2 => '' . $temp_path2 . '\\nomake\\pear\\tests',
          ),
          array (
            0 => 'PEAR www files directory',
            1 => 'www_dir',
            2 => '' . $temp_path2 . '\\nomake\\pear\\www',
          ),
        ),
        'Advanced' =>
        array (
          0 =>
          array (
            0 => 'Cache TimeToLive',
            1 => 'cache_ttl',
            2 => NULL,
          ),
          1 =>
          array (
            0 => 'Preferred Package State',
            1 => 'preferred_state',
            2 => NULL,
          ),
          2 =>
          array (
            0 => 'Unix file mask',
            1 => 'umask',
            2 => NULL,
          ),
          3 =>
          array (
            0 => 'Debug Log Level',
            1 => 'verbose',
            2 => NULL,
          ),
        ),
        'Maintainers' =>
        array (
          0 =>
          array (
            0 => 'PEAR password (for maintainers)',
            1 => 'password',
            2 => NULL,
          ),
          1 =>
          array (
            0 => 'Signature Handling Program',
            1 => 'sig_bin',
            2 => NULL,
          ),
          2 =>
          array (
            0 => 'Signature Key Directory',
            1 => 'sig_keydir',
            2 => NULL,
          ),
          3 =>
          array (
            0 => 'Signature Key Id',
            1 => 'sig_keyid',
            2 => NULL,
          ),
          4 =>
          array (
            0 => 'Package Signature Type',
            1 => 'sig_type',
            2 => NULL,
          ),
          5 =>
          array (
            0 => 'PEAR username (for maintainers)',
            1 => 'username',
            2 => NULL,
          ),
        ),
        'Config Files' =>
        array (
          0 =>
          array (
            0 => 'User Configuration File',
            1 => 'Filename',
            2 => $temp_path . DIRECTORY_SEPARATOR . 'remote.ini',
          ),
          1 =>
          array (
            0 => 'System Configuration File',
            1 => 'Filename',
            2 => '#no#system#config#',
          ),
        ),
      ),
    ),
    'cmd' => 'config-show',
  ),
  1 =>
  array (
    'info' => 'Successfully created default configuration file "' .
    $temp_path . DIRECTORY_SEPARATOR . 'remote.ini"',
    'cmd' => 'config-create',
  ),
), $log, 'log');
$phpunit->assertFileExists($temp_path . DIRECTORY_SEPARATOR . 'remote.ini', 'not created');
$contents = explode("\n", implode('', file($temp_path . DIRECTORY_SEPARATOR . 'remote.ini')));
$contents = unserialize($contents[1]);
$config->readConfigFile($temp_path . DIRECTORY_SEPARATOR . 'remote.ini');
$phpunit->assertEquals(array (
  'php_dir' => $temp_path2 . '\\nomake\\pear\\php',
  'data_dir' => $temp_path2 . '\\nomake\\pear\\data',
  'www_dir' => $temp_path2 . '\\nomake\\pear\\www',
  'cfg_dir' => $temp_path2 . '\\nomake\\pear\\cfg',
  'ext_dir' => $temp_path2 . '\\nomake\\pear\\ext',
  'doc_dir' => $temp_path2 . '\\nomake\\pear\\docs',
  'test_dir' => $temp_path2 . '\\nomake\\pear\\tests',
  'cache_dir' => $temp_path2 . '\\nomake\\pear\\cache',
  'download_dir' => $temp_path2 . '\\nomake\\pear\\download',
  'temp_dir' => $temp_path2 . '\\nomake\\pear\\temp',
  'bin_dir' => $temp_path2 . '\\nomake\\pear',
  'man_dir' => $temp_path2 . '\\nomake\\pear\\man',
), $contents, 'ok');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
