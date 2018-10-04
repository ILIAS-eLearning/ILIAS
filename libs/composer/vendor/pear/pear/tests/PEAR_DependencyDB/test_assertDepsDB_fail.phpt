--TEST--
PEAR_DependencyDB->assertDepsDB() (warning: VERY slow test)
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
$contents = array (
  '_version' => '2.0',
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
      'chiara' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'DB_DataObject',
            'channel' => 'pear.php.net',
            'min' => '1.5.3',
          ),
          'type' => 'required',
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
            'min' => '1.6',
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
      'db_dataobject_formbuilder' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'HTML_QuickForm',
            'channel' => 'pear.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'DB_DataObject',
            'channel' => 'pear.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'error_handler' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Error_Raise',
            'channel' => 'pear.php.net',
            'min' => '0.1.0',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
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
      ),
      'error_raise' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Console_Color',
            'channel' => 'pear.php.net',
            'min' => '0.0.3',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'error_stack' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Log',
            'channel' => 'pear.php.net',
            'min' => '1.8.0',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'html_css' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'HTML_Common',
            'channel' => 'pear.php.net',
            'min' => '1.2',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'html_quickform' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'HTML_Common',
            'channel' => 'pear.php.net',
            'min' => '1.2.1',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'html_quickform_controller' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'HTML_QuickForm',
            'channel' => 'pear.php.net',
            'min' => '3.2.2',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'html_table' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'HTML_Common',
            'channel' => 'pear.php.net',
            'min' => '1.2',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'html_template_flexy' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'HTML_Javascript',
            'channel' => 'pear.php.net',
            'min' => '1.1.0',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'File_Gettext',
            'channel' => 'pear.php.net',
            'min' => '0.2.0',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        2 => 
        array (
          'dep' => 
          array (
            'name' => 'Translation2',
            'channel' => 'pear.php.net',
            'min' => '0.0.1',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'http_client' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'HTTP_Request',
            'channel' => 'pear.php.net',
            'min' => '1.0.2',
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
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'DB',
            'channel' => 'pear.php.net',
            'min' => '1.6',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        2 => 
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
        3 => 
        array (
          'dep' => 
          array (
            'name' => 'XML_Tree',
            'channel' => 'pear.php.net',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        4 => 
        array (
          'dep' => 
          array (
            'name' => 'Crypt_RC4',
            'channel' => 'pear.php.net',
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
            'min' => '2.0.0beta2',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'log' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'DB',
            'channel' => 'pear.php.net',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'math_matrix' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Math_Vector',
            'channel' => 'pear.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'PHPUnit',
            'channel' => 'pear.php.net',
            'max' => '0.6.2',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'math_vector' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'PHPUnit',
            'channel' => 'pear.php.net',
            'max' => '0.6.2',
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
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'XML_Parser',
            'channel' => 'pear.php.net',
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
            'name' => 'PEAR',
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
            'name' => 'Archive_Tar',
            'channel' => 'pear.php.net',
            'min' => '1.1',
          ),
          'type' => 'required',
          'group' => false,
        ),
        2 => 
        array (
          'dep' => 
          array (
            'name' => 'Console_Getopt',
            'channel' => 'pear.php.net',
            'min' => '1.2',
          ),
          'type' => 'required',
          'group' => false,
        ),
        3 => 
        array (
          'dep' => 
          array (
            'name' => 'XML_RPC',
            'channel' => 'pear.php.net',
            'min' => '1.0.4',
          ),
          'type' => 'required',
          'group' => false,
        ),
        4 => 
        array (
          'dep' => 
          array (
            'name' => 'Net_FTP',
            'channel' => 'pear.php.net',
            'min' => '1.3.0RC1',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'peartests' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR',
            'channel' => 'pear.php.net',
            'min' => '1.4.0dev11',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
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
        2 => 
        array (
          'dep' => 
          array (
            'name' => 'Console_Getopt',
            'channel' => 'pear.php.net',
            'min' => '1.2',
          ),
          'type' => 'required',
          'group' => false,
        ),
        3 => 
        array (
          'dep' => 
          array (
            'name' => 'XML_RPC',
            'channel' => 'pear.php.net',
            'min' => '1.1.0',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'pear_frontend_web' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Net_UserAgent_Detect',
            'channel' => 'pear.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'HTML_Template_IT',
            'channel' => 'pear.php.net',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'pear_info' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR',
            'channel' => 'pear.php.net',
            'min' => '1.0.1',
          ),
          'type' => 'required',
          'group' => false,
        ),
      ),
      'pear_packagefilemanager' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR',
            'channel' => 'pear.php.net',
            'min' => '1.1',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'PHP_CompatInfo',
            'channel' => 'pear.php.net',
            'min' => '1.0.0RC1',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'pear_server' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'DB_DataObject',
            'channel' => 'pear.php.net',
            'min' => '1.6.1',
          ),
          'type' => 'required',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'HTML_QuickForm',
            'channel' => 'pear.php.net',
            'min' => '3.2.2',
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
      'php_compatinfo' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'Console_Table',
            'channel' => 'pear.php.net',
            'min' => '1.0.1',
          ),
          'type' => 'optional',
          'group' => false,
        ),
        1 => 
        array (
          'dep' => 
          array (
            'name' => 'Console_Getopt',
            'channel' => 'pear.php.net',
            'min' => '1.2',
          ),
          'type' => 'optional',
          'group' => false,
        ),
      ),
      'php_parser' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'PEAR',
            'channel' => 'pear.php.net',
            'min' => '1.3.1dev',
          ),
          'type' => 'required',
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
            'min' => '0.4.2',
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
            'min' => '1.1.0',
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
      'xml_tree' => 
      array (
        0 => 
        array (
          'dep' => 
          array (
            'name' => 'XML_Parser',
            'channel' => 'pear.php.net',
            'min' => '1.1.0',
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
      'http_request' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'cache',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'http_client',
        ),
      ),
      'db_dataobject' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'chiara',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'db_dataobject_formbuilder',
        ),
        2 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear_server',
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
          'package' => 'liveuser',
        ),
        2 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'mdb2',
        ),
        3 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear',
        ),
        4 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'peartests',
        ),
        5 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear_info',
        ),
        6 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear_packagefilemanager',
        ),
        7 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'php_parser',
        ),
        8 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'xml_parser',
        ),
        9 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'xml_serializer',
        ),
        10 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'xml_util',
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
        2 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'log',
        ),
      ),
      'date' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'db_dataobject',
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
      'html_quickform' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'db_dataobject_formbuilder',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'html_quickform_controller',
        ),
        2 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear_server',
        ),
      ),
      'error_raise' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'error_handler',
        ),
      ),
      'log' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'error_handler',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'error_stack',
        ),
      ),
      'console_color' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'error_raise',
        ),
      ),
      'html_common' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'html_css',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'html_quickform',
        ),
        2 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'html_table',
        ),
      ),
      'html_javascript' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'html_template_flexy',
        ),
      ),
      'file_gettext' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'html_template_flexy',
        ),
      ),
      'translation2' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'html_template_flexy',
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
      'xml_tree' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'liveuser',
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
      'mdb2' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'liveuser',
        ),
      ),
      'math_vector' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'math_matrix',
        ),
      ),
      'phpunit' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'math_matrix',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'math_vector',
        ),
      ),
      'xml_parser' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'mdb2',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'xml_serializer',
        ),
        2 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'xml_tree',
        ),
      ),
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
          'package' => 'peartests',
        ),
        2 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'phpdocumentor',
        ),
      ),
      'console_getopt' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'peartests',
        ),
        2 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'php_compatinfo',
        ),
      ),
      'xml_rpc' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear',
        ),
        1 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'peartests',
        ),
      ),
      'net_ftp' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear',
        ),
      ),
      'net_useragent_detect' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear_frontend_web',
        ),
      ),
      'html_template_it' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear_frontend_web',
        ),
      ),
      'php_compatinfo' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'pear_packagefilemanager',
        ),
      ),
      'console_table' => 
      array (
        0 => 
        array (
          'channel' => 'pear.php.net',
          'package' => 'php_compatinfo',
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
);
$fp = fopen($php_dir . DIRECTORY_SEPARATOR . '.depdb', 'wb');
fwrite($fp, serialize($contents));
fclose($fp);

$err = $db->assertDepsDB();
$phpunit->assertErrors(array('package' => 'PEAR_Error', 'message' =>
    'Dependency database is version 2.0, and we are version 1.0, cannot continue'), 'version too new');
$phpunit->assertIsa('PEAR_Error', $err, 'return PEAR_Error');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
