--TEST--
PEAR_DependencyDB->rebuildDB() (warning: slow test)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
$statedir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'registry_tester';
if (file_exists($statedir)) {
    // don't delete existing directories!
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
copyItem('registry'); //setup for nice clean rebuild
$phpunit->assertFileNotExists($php_dir . DIRECTORY_SEPARATOR . '.depdb', 'depdb');
$db = new PEAR_DependencyDB;
$db->setConfig($config);
$phpunit->assertNoErrors('initial');
$ret = $db->rebuildDB();
$phpunit->assertNoErrors('rebuild');
$phpunit->assertTrue($ret, 'rebuild ret');
$phpunit->assertFileExists($php_dir . DIRECTORY_SEPARATOR . '.depdb', 'depdb');
$contents = implode('', file($php_dir . DIRECTORY_SEPARATOR . '.depdb'));
$contents = unserialize($contents);
ksort($contents['dependencies']);
ksort($contents['dependencies']['pear.php.net']);
ksort($contents['packages']['pear.php.net']);
function sortstuff($a, $b)
{
    return strnatcasecmp($a['package'], $b['package']);
}
foreach ($contents['packages']['pear.php.net'] as $p => $cont) {
    usort($contents['packages']['pear.php.net'][$p], 'sortstuff');
}
$phpunit->assertEquals(array (
  '_version' => '1.0',
  'dependencies' => 
  array (
    'pear.php.net' => 
    array (
      'cache' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'HTTP_Request',
            'channel' => 'pear.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'calendar' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Date',
            'channel' => 'pear.php.net',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'db' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR',
            'channel' => 'pear.php.net',
            'min' => '1.0b1',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'db_dataobject' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'DB',
            'channel' => 'pear.php.net',
            'min' => '1.7.0',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'Date',
            'channel' => 'pear.php.net',
            'min' => '1.4.3',
          ),
          'type' => 'required',
          'group' => false,
        ),
        2 => 
        array (
          'dep' => 
          array (
            'name' => 'Validate',
            'channel' => 'pear.php.net',
            'min' => '0.1.1',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'http' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR',
            'channel' => 'pear.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'liveuser' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR',
            'channel' => 'pear.php.net',
            'min' => '1.3.3',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'Event_Dispatcher',
            'channel' => 'pear.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
        2 => 
        array (
          'dep' => 
          array (
            'name' => 'Log',
            'channel' => 'pear.php.net',
            'min' => '1.7.0',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        3 => 
        array (
          'dep' => 
          array (
            'name' => 'DB',
            'channel' => 'pear.php.net',
            'min' => '1.6.0',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        4 => 
        array (
          'dep' => 
          array (
            'name' => 'MDB',
            'channel' => 'pear.php.net',
            'min' => '1.1.4',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        5 => 
        array (
          'dep' => 
          array (
            'name' => 'MDB2',
            'channel' => 'pear.php.net',
            'min' => '2.0.0beta4',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        6 => 
        array (
          'dep' => 
          array (
            'name' => 'MDB2_Schema',
            'channel' => 'pear.php.net',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        7 => 
        array (
          'dep' => 
          array (
            'name' => 'XML_Tree',
            'channel' => 'pear.php.net',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        8 => 
        array (
          'dep' => 
          array (
            'name' => 'Crypt_RC4',
            'channel' => 'pear.php.net',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'mdb2' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR',
            'channel' => 'pear.php.net',
            'min' => '1.0b1',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'pear' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Archive_Tar',
            'channel' => 'pear.php.net',
            'min' => '1.1',
            'recommended' => '1.3.1',
            'exclude' => '1.3.0',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'Console_Getopt',
            'channel' => 'pear.php.net',
            'min' => '1.2',
            'recommended' => '1.2',
          ),
          'type' => 'required',
          'group' => false,
        ),
        2 => 
        array (
          'dep' => 
          array (
            'name' => 'XML_RPC',
            'channel' => 'pear.php.net',
            'min' => '1.4.0',
            'recommended' => '1.4.1',
          ),
          'type' => 'required',
          'group' => false,
        ),
        3 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR_Frontend_Web',
            'channel' => 'pear.php.net',
            'max' => '0.5.0',
            'exclude' => '0.5.0',
            'conflicts' => '',
          ),
          'type' => 'required',
          'group' => false,
        ),
        4 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR_Frontend_Gtk',
            'channel' => 'pear.php.net',
            'max' => '0.4.0',
            'exclude' => '0.4.0',
            'conflicts' => '',
          ),
          'type' => 'required',
          'group' => false,
        ),
        5 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR_Frontend_Web',
            'channel' => 'pear.php.net',
            'min' => '0.5.0',
          ),
          'type' => 'optional',
          'group' => 'webinstaller',
        ),
        6 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR_Frontend_Gtk',
            'channel' => 'pear.php.net',
            'min' => '0.4.0',
          ),
          'type' => 'optional',
          'group' => 'gtkinstaller',
        ),
      ),
      'php_archive' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Archive_Tar',
            'channel' => 'pear.php.net',
            'min' => '1.3.2',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR',
            'channel' => 'pear.php.net',
            'min' => '1.3.5',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'phpdocumentor' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Archive_Tar',
            'channel' => 'pear.php.net',
            'min' => '1.1',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'validate' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Date',
            'channel' => 'pear.php.net',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'xml_parser' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR',
            'channel' => 'pear.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'xml_serializer' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'XML_Util',
            'channel' => 'pear.php.net',
            'min' => '1.1.1',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'XML_Parser',
            'channel' => 'pear.php.net',
            'min' => '1.2.1',
          ),
          'type' => 'required',
          'group' => false,
        ),
        2 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR',
            'channel' => 'pear.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'xml_util' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR',
            'channel' => 'pear.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
    ),
  ),
  'packages' => 
  array (
    'pear.php.net' => 
    array (
      'archive_tar' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'phpdocumentor',
        ),
        2 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'php_archive',
        ),
      ),
      'console_getopt' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear',
        ),
      ),
      'crypt_rc4' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'liveuser',
        ),
      ),
      'date' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'calendar',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'db_dataobject',
        ),
        2 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'validate',
        ),
      ),
      'db' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'db_dataobject',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'liveuser',
        ),
      ),
      'event_dispatcher' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'liveuser',
        ),
      ),
      'http_request' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'cache',
        ),
      ),
      'log' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'liveuser',
        ),
      ),
      'mdb' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'liveuser',
        ),
      ),
      'mdb2' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'liveuser',
        ),
      ),
      'mdb2_schema' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'liveuser',
        ),
      ),
      'pear' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'db',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'http',
        ),
        2 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'liveuser',
        ),
        3 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'mdb2',
        ),
        4 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'php_archive',
        ),
        5 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'xml_parser',
        ),
        6 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'xml_serializer',
        ),
        7 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'xml_util',
        ),
      ),
      'pear_frontend_gtk' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear',
        ),
      ),
      'pear_frontend_web' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear',
        ),
      ),
      'validate' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'db_dataobject',
        ),
      ),
      'xml_parser' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'xml_serializer',
        ),
      ),
      'xml_rpc' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear',
        ),
      ),
      'xml_tree' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'liveuser',
        ),
      ),
      'xml_util' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'xml_serializer',
        ),
      ),
    ),
  ),
), $contents, 'serialized stuff');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
