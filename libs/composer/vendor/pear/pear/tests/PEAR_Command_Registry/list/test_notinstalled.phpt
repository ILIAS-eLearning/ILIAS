--TEST--
list command, pseudo-list-files, package not installed
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$e = $command->run('list', array(), array(dirname(__FILE__) . DIRECTORY_SEPARATOR .
    DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR . 'package2.xml'));
$phpunit->showall();
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 
    array (
      'caption' => 'Contents of package2.xml',
      'border' => true,
      'headline' => 
      array (
        0 => 'Package File',
        1 => 'Install Path',
      ),
      'data' => 
      array (
        0 => 
        array (
          0 => 'OS/Guess.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'OS' . DIRECTORY_SEPARATOR . 'Guess.php',
        ),
        1 => 
        array (
          0 => 'PEAR/ChannelFile/Parser.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'ChannelFile' . DIRECTORY_SEPARATOR . 'Parser.php',
        ),
        2 => 
        array (
          0 => 'PEAR/Command/Auth.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Auth.php',
        ),
        3 => 
        array (
          0 => 'PEAR/Command/Build.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Build.php',
        ),
        4 => 
        array (
          0 => 'PEAR/Command/Channels.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Channels.php',
        ),
        5 => 
        array (
          0 => 'PEAR/Command/Common.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Common.php',
        ),
        6 => 
        array (
          0 => 'PEAR/Command/Config.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Config.php',
        ),
        7 => 
        array (
          0 => 'PEAR/Command/Install.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Install.php',
        ),
        8 => 
        array (
          0 => 'PEAR/Command/Mirror.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Mirror.php',
        ),
        9 => 
        array (
          0 => 'PEAR/Command/Package.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Package.php',
        ),
        10 => 
        array (
          0 => 'PEAR/Command/Registry.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Registry.php',
        ),
        11 => 
        array (
          0 => 'PEAR/Command/Remote.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Remote.php',
        ),
        12 => 
        array (
          0 => 'PEAR/Downloader/Package.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Downloader' . DIRECTORY_SEPARATOR . 'Package.php',
        ),
        13 => 
        array (
          0 => 'PEAR/Frontend/CLI.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Frontend' . DIRECTORY_SEPARATOR . 'CLI.php',
        ),
        14 => 
        array (
          0 => 'PEAR/Installer/Role/Common.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Common.php',
        ),
        15 => 
        array (
          0 => 'PEAR/Installer/Role/Data.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Data.php',
        ),
        16 => 
        array (
          0 => 'PEAR/Installer/Role/Doc.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Doc.php',
        ),
        17 => 
        array (
          0 => 'PEAR/Installer/Role/Ext.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Ext.php',
        ),
        18 => 
        array (
          0 => 'PEAR/Installer/Role/Php.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Php.php',
        ),
        19 => 
        array (
          0 => 'PEAR/Installer/Role/Script.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Script.php',
        ),
        20 => 
        array (
          0 => 'PEAR/Installer/Role/Test.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Test.php',
        ),
        21 => 
        array (
          0 => 'PEAR/Installer/Role.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role.php',
        ),
        22 => 
        array (
          0 => 'PEAR/PackageFile/Generator/v1.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'Generator' . DIRECTORY_SEPARATOR . 'v1.php',
        ),
        23 => 
        array (
          0 => 'PEAR/PackageFile/Generator/v2.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'Generator' . DIRECTORY_SEPARATOR . 'v2.php',
        ),
        24 => 
        array (
          0 => 'PEAR/PackageFile/Parser/v1.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'Parser' . DIRECTORY_SEPARATOR . 'v1.php',
        ),
        25 => 
        array (
          0 => 'PEAR/PackageFile/Parser/v2.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'Parser' . DIRECTORY_SEPARATOR . 'v2.php',
        ),
        26 => 
        array (
          0 => 'PEAR/PackageFile/v2/Validator.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'v2' . DIRECTORY_SEPARATOR . 'Validator.php',
        ),
        27 => 
        array (
          0 => 'PEAR/PackageFile/v1.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'v1.php',
        ),
        28 => 
        array (
          0 => 'PEAR/PackageFile/v2.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'v2.php',
        ),
        29 => 
        array (
          0 => 'PEAR/Task/Common.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Task' . DIRECTORY_SEPARATOR . 'Common.php',
        ),
        30 => 
        array (
          0 => 'PEAR/Task/Preinstallscript.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Task' . DIRECTORY_SEPARATOR . 'Preinstallscript.php',
        ),
        31 => 
        array (
          0 => 'PEAR/Task/Postinstallscript.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Task' . DIRECTORY_SEPARATOR . 'Postinstallscript.php',
        ),
        32 => 
        array (
          0 => 'PEAR/Task/Replace.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Task' . DIRECTORY_SEPARATOR . 'Replace.php',
        ),
        33 => 
        array (
          0 => 'PEAR/Autoloader.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Autoloader.php',
        ),
        34 => 
        array (
          0 => 'PEAR/Builder.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Builder.php',
        ),
        35 => 
        array (
          0 => 'PEAR/ChannelFile.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'ChannelFile.php',
        ),
        36 => 
        array (
          0 => 'PEAR/Command.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command.php',
        ),
        37 => 
        array (
          0 => 'PEAR/Common.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Common.php',
        ),
        38 => 
        array (
          0 => 'PEAR/Config.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Config.php',
        ),
        39 => 
        array (
          0 => 'PEAR/Dependency.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Dependency.php',
        ),
        40 => 
        array (
          0 => 'PEAR/DependencyDB.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'DependencyDB.php',
        ),
        41 => 
        array (
          0 => 'PEAR/Dependency2.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Dependency2.php',
        ),
        42 => 
        array (
          0 => 'PEAR/Downloader.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Downloader.php',
        ),
        43 => 
        array (
          0 => 'PEAR/ErrorStack.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'ErrorStack.php',
        ),
        44 => 
        array (
          0 => 'PEAR/Frontend.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Frontend.php',
        ),
        45 => 
        array (
          0 => 'PEAR/FTP.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'FTP.php',
        ),
        46 => 
        array (
          0 => 'PEAR/Installer.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer.php',
        ),
        47 => 
        array (
          0 => 'PEAR/PackageFile.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile.php',
        ),
        48 => 
        array (
          0 => 'PEAR/Packager.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Packager.php',
        ),
        49 => 
        array (
          0 => 'PEAR/Registry.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Registry.php',
        ),
        50 => 
        array (
          0 => 'PEAR/Remote.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Remote.php',
        ),
        51 => 
        array (
          0 => 'PEAR/RunTest.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'RunTest.php',
        ),
        52 => 
        array (
          0 => 'PEAR/Validate.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Validate.php',
        ),
        53 => 
        array (
          0 => 'PEAR/XMLParser.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'XMLParser.php',
        ),
        54 => 
        array (
          0 => 'scripts/pear.bat',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pear.bat',
        ),
        55 => 
        array (
          0 => 'scripts/pear.sh',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pear.sh',
        ),
        56 => 
        array (
          0 => 'scripts/pearcmd.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pearcmd.php',
        ),
        57 => 
        array (
          0 => 'package.dtd',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'package.dtd',
        ),
        58 => 
        array (
          0 => 'PEAR.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR.php',
        ),
        59 => 
        array (
          0 => 'pearchannel.xml',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'pearchannel.xml',
        ),
        60 => 
        array (
          0 => 'System.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'System.php',
        ),
        61 => 
        array (
          0 => 'template.spec',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'template.spec',
        ),
      ),
    ),
    'cmd' => 'list',
  ),
), $fakelog->getLog(), 'package2.xml');
$e = $command->run('list', array(), array(dirname(__FILE__) . DIRECTORY_SEPARATOR .
    DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR . 'package.xml'));
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 
    array (
      'caption' => 'Contents of package.xml',
      'border' => true,
      'headline' => 
      array (
        0 => 'Package File',
        1 => 'Install Path',
      ),
      'data' => 
      array (
        0 => 
        array (
          0 => 'OS/Guess.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'OS' . DIRECTORY_SEPARATOR . 'Guess.php',
        ),
        1 => 
        array (
          0 => 'PEAR/Autoloader.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Autoloader.php',
        ),
        2 => 
        array (
          0 => 'PEAR/Command.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command.php',
        ),
        3 => 
        array (
          0 => 'PEAR/ChannelFile/Parser.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'ChannelFile' . DIRECTORY_SEPARATOR . 'Parser.php',
        ),
        4 => 
        array (
          0 => 'PEAR/Command/Auth.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Auth.php',
        ),
        5 => 
        array (
          0 => 'PEAR/Command/Build.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Build.php',
        ),
        6 => 
        array (
          0 => 'PEAR/Command/Channels.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Channels.php',
        ),
        7 => 
        array (
          0 => 'PEAR/Command/Common.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Common.php',
        ),
        8 => 
        array (
          0 => 'PEAR/Command/Config.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Config.php',
        ),
        9 => 
        array (
          0 => 'PEAR/Command/Install.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Install.php',
        ),
        10 => 
        array (
          0 => 'PEAR/Command/Package.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Package.php',
        ),
        11 => 
        array (
          0 => 'PEAR/Command/Registry.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Registry.php',
        ),
        12 => 
        array (
          0 => 'PEAR/Command/Remote.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Remote.php',
        ),
        13 => 
        array (
          0 => 'PEAR/Command/Mirror.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Command' . DIRECTORY_SEPARATOR . 'Mirror.php',
        ),
        14 => 
        array (
          0 => 'PEAR/Downloader/Package.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Downloader' . DIRECTORY_SEPARATOR . 'Package.php',
        ),
        15 => 
        array (
          0 => 'PEAR/Frontend/CLI.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Frontend' . DIRECTORY_SEPARATOR . 'CLI.php',
        ),
        16 => 
        array (
          0 => 'PEAR/Installer/Role/Common.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Common.php',
        ),
        17 => 
        array (
          0 => 'PEAR/Installer/Role/Data.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Data.php',
        ),
        18 => 
        array (
          0 => 'PEAR/Installer/Role/Doc.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Doc.php',
        ),
        19 => 
        array (
          0 => 'PEAR/Installer/Role/Ext.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Ext.php',
        ),
        20 => 
        array (
          0 => 'PEAR/Installer/Role/Php.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Php.php',
        ),
        21 => 
        array (
          0 => 'PEAR/Installer/Role/Script.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Script.php',
        ),
        22 => 
        array (
          0 => 'PEAR/Installer/Role/Test.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role' . DIRECTORY_SEPARATOR . 'Test.php',
        ),
        23 => 
        array (
          0 => 'PEAR/Installer/Role.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Role.php',
        ),
        24 => 
        array (
          0 => 'PEAR/PackageFile/Generator/v1.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'Generator' . DIRECTORY_SEPARATOR . 'v1.php',
        ),
        25 => 
        array (
          0 => 'PEAR/PackageFile/Generator/v2.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'Generator' . DIRECTORY_SEPARATOR . 'v2.php',
        ),
        26 => 
        array (
          0 => 'PEAR/PackageFile/Parser/v1.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'Parser' . DIRECTORY_SEPARATOR . 'v1.php',
        ),
        27 => 
        array (
          0 => 'PEAR/PackageFile/Parser/v2.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'Parser' . DIRECTORY_SEPARATOR . 'v2.php',
        ),
        28 => 
        array (
          0 => 'PEAR/PackageFile/v2/Validator.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'v2' . DIRECTORY_SEPARATOR . 'Validator.php',
        ),
        29 => 
        array (
          0 => 'PEAR/PackageFile/v1.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'v1.php',
        ),
        30 => 
        array (
          0 => 'PEAR/PackageFile/v2.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile' . DIRECTORY_SEPARATOR . 'v2.php',
        ),
        31 => 
        array (
          0 => 'PEAR/Task/Common.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Task' . DIRECTORY_SEPARATOR . 'Common.php',
        ),
        32 => 
        array (
          0 => 'PEAR/Task/Preinstallscript.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Task' . DIRECTORY_SEPARATOR . 'Preinstallscript.php',
        ),
        33 => 
        array (
          0 => 'PEAR/Task/Postinstallscript.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Task' . DIRECTORY_SEPARATOR . 'Postinstallscript.php',
        ),
        34 => 
        array (
          0 => 'PEAR/Task/Replace.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Task' . DIRECTORY_SEPARATOR . 'Replace.php',
        ),
        35 => 
        array (
          0 => 'PEAR/ChannelFile.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'ChannelFile.php',
        ),
        36 => 
        array (
          0 => 'PEAR/Common.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Common.php',
        ),
        37 => 
        array (
          0 => 'PEAR/Config.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Config.php',
        ),
        38 => 
        array (
          0 => 'PEAR/Dependency.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Dependency.php',
        ),
        39 => 
        array (
          0 => 'PEAR/DependencyDB.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'DependencyDB.php',
        ),
        40 => 
        array (
          0 => 'PEAR/Dependency2.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Dependency2.php',
        ),
        41 => 
        array (
          0 => 'PEAR/Downloader.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Downloader.php',
        ),
        42 => 
        array (
          0 => 'PEAR/ErrorStack.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'ErrorStack.php',
        ),
        43 => 
        array (
          0 => 'PEAR/Builder.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Builder.php',
        ),
        44 => 
        array (
          0 => 'PEAR/Frontend.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Frontend.php',
        ),
        45 => 
        array (
          0 => 'PEAR/FTP.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'FTP.php',
        ),
        46 => 
        array (
          0 => 'PEAR/Installer.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Installer.php',
        ),
        47 => 
        array (
          0 => 'PEAR/Packager.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Packager.php',
        ),
        48 => 
        array (
          0 => 'PEAR/PackageFile.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'PackageFile.php',
        ),
        49 => 
        array (
          0 => 'PEAR/Registry.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Registry.php',
        ),
        50 => 
        array (
          0 => 'PEAR/Remote.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Remote.php',
        ),
        51 => 
        array (
          0 => 'PEAR/RunTest.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'RunTest.php',
        ),
        52 => 
        array (
          0 => 'PEAR/Validate.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'Validate.php',
        ),
        53 => 
        array (
          0 => 'PEAR/XMLParser.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'XMLParser.php',
        ),
        54 => 
        array (
          0 => 'scripts/pear.sh',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pear.sh',
        ),
        55 => 
        array (
          0 => 'scripts/pear.bat',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pear.bat',
        ),
        56 => 
        array (
          0 => 'scripts/pearcmd.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pearcmd.php',
        ),
        57 => 
        array (
          0 => 'package.dtd',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'package.dtd',
        ),
        58 => 
        array (
          0 => 'pearchannel.xml',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'pearchannel.xml',
        ),
        59 => 
        array (
          0 => 'template.spec',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR . 'template.spec',
        ),
        60 => 
        array (
          0 => 'PEAR.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'PEAR.php',
        ),
        61 => 
        array (
          0 => 'System.php',
          1 => $temp_path . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR . 'System.php',
        ),
      ),
    ),
    'cmd' => 'list',
  ),
), $fakelog->getLog(), 'package.xml');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
