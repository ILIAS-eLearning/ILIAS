--TEST--
PEAR_PackageFile_Parser_v2->getFilelist()
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
    'test_getFileList'. DIRECTORY_SEPARATOR . 'package2.xml';
$pf = $parser->parse(implode('', file($pathtopackagexml)), $pathtopackagexml);
$pf->flattenFilelist();
$phpunit->assertNoErrors('valid xml parse');
$phpunit->assertIsa('PEAR_PackageFile_v2', $pf, 'return of valid parse');
$phpunit->assertEquals('pear.php.net', $pf->getChannel(), 'pre-set');

$phpunit->assertEquals(array (
  'dir' => 
  array (
    'attribs' => 
    array (
      'name' => '/',
    ),
    'file' => 
    array (
      0 => 
      array (
        'attribs' => 
        array (
          'name' => 'OS/Guess.php',
          'role' => 'php',
        ),
      ),
      1 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Command/Auth.php',
          'role' => 'php',
        ),
      ),
      2 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Command/Build.php',
          'role' => 'php',
        ),
      ),
      3 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Command/Channels.php',
          'role' => 'php',
        ),
      ),
      4 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Command/Common.php',
          'role' => 'php',
        ),
      ),
      5 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Command/Config.php',
          'role' => 'php',
        ),
      ),
      6 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Command/Install.php',
          'role' => 'php',
        ),
      ),
      7 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Command/Mirror.php',
          'role' => 'php',
        ),
      ),
      8 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Command/Package.php',
          'role' => 'php',
        ),
      ),
      9 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Command/Registry.php',
          'role' => 'php',
        ),
      ),
      10 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Command/Remote.php',
          'role' => 'php',
        ),
      ),
      11 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Downloader/Package.php',
          'role' => 'php',
        ),
        'tasks:replace' => 
        array (
          'attribs' => 
          array (
            'from' => '@PEAR-VER@',
            'to' => 'version',
            'type' => 'package-info',
          ),
        ),
      ),
      12 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Frontend/CLI.php',
          'role' => 'php',
        ),
      ),
      13 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Installer/Role/Common.php',
          'role' => 'php',
        ),
      ),
      14 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Installer/Role/Data.php',
          'role' => 'php',
        ),
      ),
      15 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Installer/Role/Doc.php',
          'role' => 'php',
        ),
      ),
      16 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Installer/Role/Ext.php',
          'role' => 'php',
        ),
      ),
      17 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Installer/Role/Php.php',
          'role' => 'php',
        ),
      ),
      18 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Installer/Role/Script.php',
          'role' => 'php',
        ),
      ),
      19 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Installer/Role/Test.php',
          'role' => 'php',
        ),
      ),
      20 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Installer/Role.php',
          'role' => 'php',
        ),
      ),
      21 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/PackageFile/Generator/v1.php',
          'role' => 'php',
        ),
        'tasks:replace' => 
        array (
          'attribs' => 
          array (
            'from' => '@PEAR-VER@',
            'to' => 'version',
            'type' => 'package-info',
          ),
        ),
      ),
      22 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/PackageFile/Generator/v2.php',
          'role' => 'php',
        ),
      ),
      23 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/PackageFile/Parser/v1.php',
          'role' => 'php',
        ),
      ),
      24 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/PackageFile/Parser/v2.php',
          'role' => 'php',
        ),
      ),
      25 => 
      array (
        'attribs' => 
        array (
          'role' => 'php',
          'name' => 'PEAR/PackageFile/v2/Validator.php',
        ),
      ),
      26 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/PackageFile/v1.php',
          'role' => 'php',
        ),
      ),
      27 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/PackageFile/v2.php',
          'role' => 'php',
        ),
      ),
      28 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Task/Common.php',
          'role' => 'php',
        ),
      ),
      29 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Task/Preinstallscript.php',
          'role' => 'php',
        ),
      ),
      30 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Task/Postinstallscript.php',
          'role' => 'php',
        ),
      ),
      31 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Task/Replace.php',
          'role' => 'php',
        ),
      ),
      32 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Autoloader.php',
          'role' => 'php',
        ),
      ),
      33 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Builder.php',
          'role' => 'php',
        ),
      ),
      34 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/ChannelFile.php',
          'role' => 'php',
        ),
      ),
      35 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Command.php',
          'role' => 'php',
        ),
      ),
      36 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Common.php',
          'role' => 'php',
        ),
      ),
      37 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Config.php',
          'role' => 'php',
        ),
      ),
      38 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Dependency.php',
          'role' => 'php',
        ),
      ),
      39 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/DependencyDB.php',
          'role' => 'php',
        ),
      ),
      40 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Dependency2.php',
          'role' => 'php',
        ),
        'tasks:replace' => 
        array (
          'attribs' => 
          array (
            'from' => '@PEAR-VER@',
            'to' => 'version',
            'type' => 'package-info',
          ),
        ),
      ),
      41 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Downloader.php',
          'role' => 'php',
        ),
      ),
      42 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/ErrorStack.php',
          'role' => 'php',
        ),
      ),
      43 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/FTP.php',
          'role' => 'php',
        ),
      ),
      44 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Installer.php',
          'role' => 'php',
        ),
      ),
      45 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/PackageFile.php',
          'role' => 'php',
        ),
        'tasks:replace' => 
        array (
          'attribs' => 
          array (
            'from' => '@PEAR-VER@',
            'to' => 'version',
            'type' => 'package-info',
          ),
        ),
      ),
      46 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Packager.php',
          'role' => 'php',
        ),
      ),
      47 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Registry.php',
          'role' => 'php',
        ),
      ),
      48 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Remote.php',
          'role' => 'php',
        ),
      ),
      49 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/RunTest.php',
          'role' => 'php',
        ),
      ),
      50 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR/Validate.php',
          'role' => 'php',
        ),
      ),
      51 => 
      array (
        'attribs' => 
        array (
          'name' => 'scripts/pear.bat',
          'role' => 'script',
          'baseinstalldir' => '/',
        ),
        'tasks:replace' => 
        array (
          0 => 
          array (
            'attribs' => 
            array (
              'from' => '@bin_dir@',
              'to' => 'bin_dir',
              'type' => 'pear-config',
            ),
          ),
          1 => 
          array (
            'attribs' => 
            array (
              'from' => '@php_bin@',
              'to' => 'php_bin',
              'type' => 'pear-config',
            ),
          ),
          2 => 
          array (
            'attribs' => 
            array (
              'from' => '@include_path@',
              'to' => 'php_dir',
              'type' => 'pear-config',
            ),
          ),
        ),
      ),
      52 => 
      array (
        'attribs' => 
        array (
          'name' => 'scripts/pear.sh',
          'role' => 'script',
          'baseinstalldir' => '/',
        ),
        'tasks:replace' => 
        array (
          0 => 
          array (
            'attribs' => 
            array (
              'from' => '@php_bin@',
              'to' => 'php_bin',
              'type' => 'pear-config',
            ),
          ),
          1 => 
          array (
            'attribs' => 
            array (
              'from' => '@php_dir@',
              'to' => 'php_dir',
              'type' => 'pear-config',
            ),
          ),
          2 => 
          array (
            'attribs' => 
            array (
              'from' => '@pear_version@',
              'to' => 'version',
              'type' => 'package-info',
            ),
          ),
          3 => 
          array (
            'attribs' => 
            array (
              'from' => '@include_path@',
              'to' => 'php_dir',
              'type' => 'pear-config',
            ),
          ),
        ),
      ),
      53 => 
      array (
        'attribs' => 
        array (
          'name' => 'scripts/pearcmd.php',
          'role' => 'php',
          'baseinstalldir' => '/',
        ),
        'tasks:replace' => 
        array (
          0 => 
          array (
            'attribs' => 
            array (
              'from' => '@php_bin@',
              'to' => 'php_bin',
              'type' => 'pear-config',
            ),
          ),
          1 => 
          array (
            'attribs' => 
            array (
              'from' => '@php_dir@',
              'to' => 'php_dir',
              'type' => 'pear-config',
            ),
          ),
          2 => 
          array (
            'attribs' => 
            array (
              'from' => '@pear_version@',
              'to' => 'version',
              'type' => 'package-info',
            ),
          ),
          3 => 
          array (
            'attribs' => 
            array (
              'from' => '@include_path@',
              'to' => 'php_dir',
              'type' => 'pear-config',
            ),
          ),
        ),
      ),
      54 => 
      array (
        'attribs' => 
        array (
          'name' => 'package.dtd',
          'role' => 'data',
        ),
      ),
      55 => 
      array (
        'attribs' => 
        array (
          'name' => 'PEAR.php',
          'role' => 'php',
        ),
      ),
      56 => 
      array (
        'attribs' => 
        array (
          'name' => 'pearchannel.xml',
          'role' => 'data',
        ),
      ),
      57 => 
      array (
        'attribs' => 
        array (
          'name' => 'System.php',
          'role' => 'php',
        ),
      ),
      58 => 
      array (
        'attribs' => 
        array (
          'name' => 'template.spec',
          'role' => 'data',
        ),
      ),
    ),
  ),
), $pf->getContents(), 'contents');
$phpunit->assertEquals(array (
  'OS/Guess.php' => 
  array (
    'name' => 'OS/Guess.php',
    'role' => 'php',
  ),
  'PEAR/Command/Auth.php' => 
  array (
    'name' => 'PEAR/Command/Auth.php',
    'role' => 'php',
  ),
  'PEAR/Command/Build.php' => 
  array (
    'name' => 'PEAR/Command/Build.php',
    'role' => 'php',
  ),
  'PEAR/Command/Channels.php' => 
  array (
    'name' => 'PEAR/Command/Channels.php',
    'role' => 'php',
  ),
  'PEAR/Command/Common.php' => 
  array (
    'name' => 'PEAR/Command/Common.php',
    'role' => 'php',
  ),
  'PEAR/Command/Config.php' => 
  array (
    'name' => 'PEAR/Command/Config.php',
    'role' => 'php',
  ),
  'PEAR/Command/Install.php' => 
  array (
    'name' => 'PEAR/Command/Install.php',
    'role' => 'php',
  ),
  'PEAR/Command/Mirror.php' => 
  array (
    'name' => 'PEAR/Command/Mirror.php',
    'role' => 'php',
  ),
  'PEAR/Command/Package.php' => 
  array (
    'name' => 'PEAR/Command/Package.php',
    'role' => 'php',
  ),
  'PEAR/Command/Registry.php' => 
  array (
    'name' => 'PEAR/Command/Registry.php',
    'role' => 'php',
  ),
  'PEAR/Command/Remote.php' => 
  array (
    'name' => 'PEAR/Command/Remote.php',
    'role' => 'php',
  ),
  'PEAR/Downloader/Package.php' => 
  array (
    'name' => 'PEAR/Downloader/Package.php',
    'role' => 'php',
  ),
  'PEAR/Frontend/CLI.php' => 
  array (
    'name' => 'PEAR/Frontend/CLI.php',
    'role' => 'php',
  ),
  'PEAR/Installer/Role/Common.php' => 
  array (
    'name' => 'PEAR/Installer/Role/Common.php',
    'role' => 'php',
  ),
  'PEAR/Installer/Role/Data.php' => 
  array (
    'name' => 'PEAR/Installer/Role/Data.php',
    'role' => 'php',
  ),
  'PEAR/Installer/Role/Doc.php' => 
  array (
    'name' => 'PEAR/Installer/Role/Doc.php',
    'role' => 'php',
  ),
  'PEAR/Installer/Role/Ext.php' => 
  array (
    'name' => 'PEAR/Installer/Role/Ext.php',
    'role' => 'php',
  ),
  'PEAR/Installer/Role/Php.php' => 
  array (
    'name' => 'PEAR/Installer/Role/Php.php',
    'role' => 'php',
  ),
  'PEAR/Installer/Role/Script.php' => 
  array (
    'name' => 'PEAR/Installer/Role/Script.php',
    'role' => 'php',
  ),
  'PEAR/Installer/Role/Test.php' => 
  array (
    'name' => 'PEAR/Installer/Role/Test.php',
    'role' => 'php',
  ),
  'PEAR/Installer/Role.php' => 
  array (
    'name' => 'PEAR/Installer/Role.php',
    'role' => 'php',
  ),
  'PEAR/PackageFile/Generator/v1.php' => 
  array (
    'name' => 'PEAR/PackageFile/Generator/v1.php',
    'role' => 'php',
  ),
  'PEAR/PackageFile/Generator/v2.php' => 
  array (
    'name' => 'PEAR/PackageFile/Generator/v2.php',
    'role' => 'php',
  ),
  'PEAR/PackageFile/Parser/v1.php' => 
  array (
    'name' => 'PEAR/PackageFile/Parser/v1.php',
    'role' => 'php',
  ),
  'PEAR/PackageFile/Parser/v2.php' => 
  array (
    'name' => 'PEAR/PackageFile/Parser/v2.php',
    'role' => 'php',
  ),
  'PEAR/PackageFile/v2/Validator.php' => 
  array (
    'role' => 'php',
    'name' => 'PEAR/PackageFile/v2/Validator.php',
  ),
  'PEAR/PackageFile/v1.php' => 
  array (
    'name' => 'PEAR/PackageFile/v1.php',
    'role' => 'php',
  ),
  'PEAR/PackageFile/v2.php' => 
  array (
    'name' => 'PEAR/PackageFile/v2.php',
    'role' => 'php',
  ),
  'PEAR/Task/Common.php' => 
  array (
    'name' => 'PEAR/Task/Common.php',
    'role' => 'php',
  ),
  'PEAR/Task/Preinstallscript.php' => 
  array (
    'name' => 'PEAR/Task/Preinstallscript.php',
    'role' => 'php',
  ),
  'PEAR/Task/Postinstallscript.php' => 
  array (
    'name' => 'PEAR/Task/Postinstallscript.php',
    'role' => 'php',
  ),
  'PEAR/Task/Replace.php' => 
  array (
    'name' => 'PEAR/Task/Replace.php',
    'role' => 'php',
  ),
  'PEAR/Autoloader.php' => 
  array (
    'name' => 'PEAR/Autoloader.php',
    'role' => 'php',
  ),
  'PEAR/Builder.php' => 
  array (
    'name' => 'PEAR/Builder.php',
    'role' => 'php',
  ),
  'PEAR/ChannelFile.php' => 
  array (
    'name' => 'PEAR/ChannelFile.php',
    'role' => 'php',
  ),
  'PEAR/Command.php' => 
  array (
    'name' => 'PEAR/Command.php',
    'role' => 'php',
  ),
  'PEAR/Common.php' => 
  array (
    'name' => 'PEAR/Common.php',
    'role' => 'php',
  ),
  'PEAR/Config.php' => 
  array (
    'name' => 'PEAR/Config.php',
    'role' => 'php',
  ),
  'PEAR/Dependency.php' => 
  array (
    'name' => 'PEAR/Dependency.php',
    'role' => 'php',
  ),
  'PEAR/DependencyDB.php' => 
  array (
    'name' => 'PEAR/DependencyDB.php',
    'role' => 'php',
  ),
  'PEAR/Dependency2.php' => 
  array (
    'name' => 'PEAR/Dependency2.php',
    'role' => 'php',
  ),
  'PEAR/Downloader.php' => 
  array (
    'name' => 'PEAR/Downloader.php',
    'role' => 'php',
  ),
  'PEAR/ErrorStack.php' => 
  array (
    'name' => 'PEAR/ErrorStack.php',
    'role' => 'php',
  ),
  'PEAR/FTP.php' => 
  array (
    'name' => 'PEAR/FTP.php',
    'role' => 'php',
  ),
  'PEAR/Installer.php' => 
  array (
    'name' => 'PEAR/Installer.php',
    'role' => 'php',
  ),
  'PEAR/PackageFile.php' => 
  array (
    'name' => 'PEAR/PackageFile.php',
    'role' => 'php',
  ),
  'PEAR/Packager.php' => 
  array (
    'name' => 'PEAR/Packager.php',
    'role' => 'php',
  ),
  'PEAR/Registry.php' => 
  array (
    'name' => 'PEAR/Registry.php',
    'role' => 'php',
  ),
  'PEAR/Remote.php' => 
  array (
    'name' => 'PEAR/Remote.php',
    'role' => 'php',
  ),
  'PEAR/RunTest.php' => 
  array (
    'name' => 'PEAR/RunTest.php',
    'role' => 'php',
  ),
  'PEAR/Validate.php' => 
  array (
    'name' => 'PEAR/Validate.php',
    'role' => 'php',
  ),
  'scripts/pear.bat' => 
  array (
    'name' => 'scripts/pear.bat',
    'role' => 'script',
    'baseinstalldir' => '/',
  ),
  'scripts/pear.sh' => 
  array (
    'name' => 'scripts/pear.sh',
    'role' => 'script',
    'baseinstalldir' => '/',
  ),
  'scripts/pearcmd.php' => 
  array (
    'name' => 'scripts/pearcmd.php',
    'role' => 'php',
    'baseinstalldir' => '/',
  ),
  'package.dtd' => 
  array (
    'name' => 'package.dtd',
    'role' => 'data',
  ),
  'PEAR.php' => 
  array (
    'name' => 'PEAR.php',
    'role' => 'php',
  ),
  'pearchannel.xml' => 
  array (
    'name' => 'pearchannel.xml',
    'role' => 'data',
  ),
  'System.php' => 
  array (
    'name' => 'System.php',
    'role' => 'php',
  ),
  'template.spec' => 
  array (
    'name' => 'template.spec',
    'role' => 'data',
  ),
), $pf->getFilelist(), 'filelist');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
