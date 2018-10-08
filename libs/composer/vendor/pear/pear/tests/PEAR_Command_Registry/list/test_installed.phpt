--TEST--
list command, pseudo-list-files, installed package
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$reg = $config->getRegistry();
$pkg = new PEAR_PackageFile($config);
$info = $pkg->fromPackageFile(dirname(__FILE__) . DIRECTORY_SEPARATOR .
    DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR . 'package2.xml',
    PEAR_VALIDATE_NORMAL);
foreach ($info->getFilelist() as $file => $atts) {
    $info->setInstalledAs($file, 1 . $file);
}
$reg->addPackage2($info);
$e = $command->run('list', array(), array('PEAR'));
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 
    array (
      'caption' => 'Installed Files For PEAR',
      'border' => true,
      'headline' => 
      array (
        0 => 'Type',
        1 => 'Install Path',
      ),
      'data' => 
      array (
        0 => 
        array (
          0 => 'php',
          1 => '1OS/Guess.php',
        ),
        1 => 
        array (
          0 => 'php',
          1 => '1PEAR/ChannelFile/Parser.php',
        ),
        2 => 
        array (
          0 => 'php',
          1 => '1PEAR/Command/Auth.php',
        ),
        3 => 
        array (
          0 => 'php',
          1 => '1PEAR/Command/Build.php',
        ),
        4 => 
        array (
          0 => 'php',
          1 => '1PEAR/Command/Channels.php',
        ),
        5 => 
        array (
          0 => 'php',
          1 => '1PEAR/Command/Common.php',
        ),
        6 => 
        array (
          0 => 'php',
          1 => '1PEAR/Command/Config.php',
        ),
        7 => 
        array (
          0 => 'php',
          1 => '1PEAR/Command/Install.php',
        ),
        8 => 
        array (
          0 => 'php',
          1 => '1PEAR/Command/Mirror.php',
        ),
        9 => 
        array (
          0 => 'php',
          1 => '1PEAR/Command/Package.php',
        ),
        10 => 
        array (
          0 => 'php',
          1 => '1PEAR/Command/Registry.php',
        ),
        11 => 
        array (
          0 => 'php',
          1 => '1PEAR/Command/Remote.php',
        ),
        12 => 
        array (
          0 => 'php',
          1 => '1PEAR/Downloader/Package.php',
        ),
        13 => 
        array (
          0 => 'php',
          1 => '1PEAR/Frontend/CLI.php',
        ),
        14 => 
        array (
          0 => 'php',
          1 => '1PEAR/Installer/Role/Common.php',
        ),
        15 => 
        array (
          0 => 'php',
          1 => '1PEAR/Installer/Role/Data.php',
        ),
        16 => 
        array (
          0 => 'php',
          1 => '1PEAR/Installer/Role/Doc.php',
        ),
        17 => 
        array (
          0 => 'php',
          1 => '1PEAR/Installer/Role/Ext.php',
        ),
        18 => 
        array (
          0 => 'php',
          1 => '1PEAR/Installer/Role/Php.php',
        ),
        19 => 
        array (
          0 => 'php',
          1 => '1PEAR/Installer/Role/Script.php',
        ),
        20 => 
        array (
          0 => 'php',
          1 => '1PEAR/Installer/Role/Test.php',
        ),
        21 => 
        array (
          0 => 'php',
          1 => '1PEAR/Installer/Role.php',
        ),
        22 => 
        array (
          0 => 'php',
          1 => '1PEAR/PackageFile/Generator/v1.php',
        ),
        23 => 
        array (
          0 => 'php',
          1 => '1PEAR/PackageFile/Generator/v2.php',
        ),
        24 => 
        array (
          0 => 'php',
          1 => '1PEAR/PackageFile/Parser/v1.php',
        ),
        25 => 
        array (
          0 => 'php',
          1 => '1PEAR/PackageFile/Parser/v2.php',
        ),
        26 => 
        array (
          0 => 'php',
          1 => '1PEAR/PackageFile/v2/Validator.php',
        ),
        27 => 
        array (
          0 => 'php',
          1 => '1PEAR/PackageFile/v1.php',
        ),
        28 => 
        array (
          0 => 'php',
          1 => '1PEAR/PackageFile/v2.php',
        ),
        29 => 
        array (
          0 => 'php',
          1 => '1PEAR/Task/Common.php',
        ),
        30 => 
        array (
          0 => 'php',
          1 => '1PEAR/Task/Preinstallscript.php',
        ),
        31 => 
        array (
          0 => 'php',
          1 => '1PEAR/Task/Postinstallscript.php',
        ),
        32 => 
        array (
          0 => 'php',
          1 => '1PEAR/Task/Replace.php',
        ),
        33 => 
        array (
          0 => 'php',
          1 => '1PEAR/Autoloader.php',
        ),
        34 => 
        array (
          0 => 'php',
          1 => '1PEAR/Builder.php',
        ),
        35 => 
        array (
          0 => 'php',
          1 => '1PEAR/ChannelFile.php',
        ),
        36 => 
        array (
          0 => 'php',
          1 => '1PEAR/Command.php',
        ),
        37 => 
        array (
          0 => 'php',
          1 => '1PEAR/Common.php',
        ),
        38 => 
        array (
          0 => 'php',
          1 => '1PEAR/Config.php',
        ),
        39 => 
        array (
          0 => 'php',
          1 => '1PEAR/Dependency.php',
        ),
        40 => 
        array (
          0 => 'php',
          1 => '1PEAR/DependencyDB.php',
        ),
        41 => 
        array (
          0 => 'php',
          1 => '1PEAR/Dependency2.php',
        ),
        42 => 
        array (
          0 => 'php',
          1 => '1PEAR/Downloader.php',
        ),
        43 => 
        array (
          0 => 'php',
          1 => '1PEAR/ErrorStack.php',
        ),
        44 => 
        array (
          0 => 'php',
          1 => '1PEAR/Frontend.php',
        ),
        45 => 
        array (
          0 => 'php',
          1 => '1PEAR/FTP.php',
        ),
        46 => 
        array (
          0 => 'php',
          1 => '1PEAR/Installer.php',
        ),
        47 => 
        array (
          0 => 'php',
          1 => '1PEAR/PackageFile.php',
        ),
        48 => 
        array (
          0 => 'php',
          1 => '1PEAR/Packager.php',
        ),
        49 => 
        array (
          0 => 'php',
          1 => '1PEAR/Registry.php',
        ),
        50 => 
        array (
          0 => 'php',
          1 => '1PEAR/Remote.php',
        ),
        51 => 
        array (
          0 => 'php',
          1 => '1PEAR/RunTest.php',
        ),
        52 => 
        array (
          0 => 'php',
          1 => '1PEAR/Validate.php',
        ),
        53 => 
        array (
          0 => 'php',
          1 => '1PEAR/XMLParser.php',
        ),
        54 => 
        array (
          0 => 'script',
          1 => '1scripts/pear.bat',
        ),
        55 => 
        array (
          0 => 'script',
          1 => '1scripts/pear.sh',
        ),
        56 => 
        array (
          0 => 'php',
          1 => '1scripts/pearcmd.php',
        ),
        57 => 
        array (
          0 => 'data',
          1 => '1package.dtd',
        ),
        58 => 
        array (
          0 => 'php',
          1 => '1PEAR.php',
        ),
        59 => 
        array (
          0 => 'data',
          1 => '1pearchannel.xml',
        ),
        60 => 
        array (
          0 => 'php',
          1 => '1System.php',
        ),
        61 => 
        array (
          0 => 'data',
          1 => '1template.spec',
        ),
      ),
    ),
    'cmd' => 'list',
  ),
), $fakelog->getLog(), 'file list');
$reg->deletePackage('PEAR');

$info = $pkg->fromPackageFile(dirname(__FILE__) . DIRECTORY_SEPARATOR .
    DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR . 'package.xml',
    PEAR_VALIDATE_NORMAL);
foreach ($info->getFilelist() as $file => $atts) {
    $info->setInstalledAs($file, 2 . $file);
}
$reg->addPackage2($info);
$e = $command->run('list', array(), array('PEAR'));
$phpunit->assertEquals(array (
  0 => 
  array (
    'info' => 
    array (
      'caption' => 'Installed Files For PEAR',
      'border' => true,
      'headline' => 
      array (
        0 => 'Type',
        1 => 'Install Path',
      ),
      'data' => 
      array (
        0 => 
        array (
          0 => 'php',
          1 => '2OS/Guess.php',
        ),
        1 => 
        array (
          0 => 'php',
          1 => '2PEAR/Autoloader.php',
        ),
        2 => 
        array (
          0 => 'php',
          1 => '2PEAR/Command.php',
        ),
        3 => 
        array (
          0 => 'php',
          1 => '2PEAR/ChannelFile/Parser.php',
        ),
        4 => 
        array (
          0 => 'php',
          1 => '2PEAR/Command/Auth.php',
        ),
        5 => 
        array (
          0 => 'php',
          1 => '2PEAR/Command/Build.php',
        ),
        6 => 
        array (
          0 => 'php',
          1 => '2PEAR/Command/Channels.php',
        ),
        7 => 
        array (
          0 => 'php',
          1 => '2PEAR/Command/Common.php',
        ),
        8 => 
        array (
          0 => 'php',
          1 => '2PEAR/Command/Config.php',
        ),
        9 => 
        array (
          0 => 'php',
          1 => '2PEAR/Command/Install.php',
        ),
        10 => 
        array (
          0 => 'php',
          1 => '2PEAR/Command/Package.php',
        ),
        11 => 
        array (
          0 => 'php',
          1 => '2PEAR/Command/Registry.php',
        ),
        12 => 
        array (
          0 => 'php',
          1 => '2PEAR/Command/Remote.php',
        ),
        13 => 
        array (
          0 => 'php',
          1 => '2PEAR/Command/Mirror.php',
        ),
        14 => 
        array (
          0 => 'php',
          1 => '2PEAR/Downloader/Package.php',
        ),
        15 => 
        array (
          0 => 'php',
          1 => '2PEAR/Frontend/CLI.php',
        ),
        16 => 
        array (
          0 => 'php',
          1 => '2PEAR/Installer/Role/Common.php',
        ),
        17 => 
        array (
          0 => 'php',
          1 => '2PEAR/Installer/Role/Data.php',
        ),
        18 => 
        array (
          0 => 'php',
          1 => '2PEAR/Installer/Role/Doc.php',
        ),
        19 => 
        array (
          0 => 'php',
          1 => '2PEAR/Installer/Role/Ext.php',
        ),
        20 => 
        array (
          0 => 'php',
          1 => '2PEAR/Installer/Role/Php.php',
        ),
        21 => 
        array (
          0 => 'php',
          1 => '2PEAR/Installer/Role/Script.php',
        ),
        22 => 
        array (
          0 => 'php',
          1 => '2PEAR/Installer/Role/Test.php',
        ),
        23 => 
        array (
          0 => 'php',
          1 => '2PEAR/Installer/Role.php',
        ),
        24 => 
        array (
          0 => 'php',
          1 => '2PEAR/PackageFile/Generator/v1.php',
        ),
        25 => 
        array (
          0 => 'php',
          1 => '2PEAR/PackageFile/Generator/v2.php',
        ),
        26 => 
        array (
          0 => 'php',
          1 => '2PEAR/PackageFile/Parser/v1.php',
        ),
        27 => 
        array (
          0 => 'php',
          1 => '2PEAR/PackageFile/Parser/v2.php',
        ),
        28 => 
        array (
          0 => 'php',
          1 => '2PEAR/PackageFile/v2/Validator.php',
        ),
        29 => 
        array (
          0 => 'php',
          1 => '2PEAR/PackageFile/v1.php',
        ),
        30 => 
        array (
          0 => 'php',
          1 => '2PEAR/PackageFile/v2.php',
        ),
        31 => 
        array (
          0 => 'php',
          1 => '2PEAR/Task/Common.php',
        ),
        32 => 
        array (
          0 => 'php',
          1 => '2PEAR/Task/Preinstallscript.php',
        ),
        33 => 
        array (
          0 => 'php',
          1 => '2PEAR/Task/Postinstallscript.php',
        ),
        34 => 
        array (
          0 => 'php',
          1 => '2PEAR/Task/Replace.php',
        ),
        35 => 
        array (
          0 => 'php',
          1 => '2PEAR/ChannelFile.php',
        ),
        36 => 
        array (
          0 => 'php',
          1 => '2PEAR/Common.php',
        ),
        37 => 
        array (
          0 => 'php',
          1 => '2PEAR/Config.php',
        ),
        38 => 
        array (
          0 => 'php',
          1 => '2PEAR/Dependency.php',
        ),
        39 => 
        array (
          0 => 'php',
          1 => '2PEAR/DependencyDB.php',
        ),
        40 => 
        array (
          0 => 'php',
          1 => '2PEAR/Dependency2.php',
        ),
        41 => 
        array (
          0 => 'php',
          1 => '2PEAR/Downloader.php',
        ),
        42 => 
        array (
          0 => 'php',
          1 => '2PEAR/ErrorStack.php',
        ),
        43 => 
        array (
          0 => 'php',
          1 => '2PEAR/Builder.php',
        ),
        44 => 
        array (
          0 => 'php',
          1 => '2PEAR/Frontend.php',
        ),
        45 => 
        array (
          0 => 'php',
          1 => '2PEAR/FTP.php',
        ),
        46 => 
        array (
          0 => 'php',
          1 => '2PEAR/Installer.php',
        ),
        47 => 
        array (
          0 => 'php',
          1 => '2PEAR/Packager.php',
        ),
        48 => 
        array (
          0 => 'php',
          1 => '2PEAR/PackageFile.php',
        ),
        49 => 
        array (
          0 => 'php',
          1 => '2PEAR/Registry.php',
        ),
        50 => 
        array (
          0 => 'php',
          1 => '2PEAR/Remote.php',
        ),
        51 => 
        array (
          0 => 'php',
          1 => '2PEAR/RunTest.php',
        ),
        52 => 
        array (
          0 => 'php',
          1 => '2PEAR/Validate.php',
        ),
        53 => 
        array (
          0 => 'php',
          1 => '2PEAR/XMLParser.php',
        ),
        54 => 
        array (
          0 => 'script',
          1 => '2scripts/pear.sh',
        ),
        55 => 
        array (
          0 => 'script',
          1 => '2scripts/pear.bat',
        ),
        56 => 
        array (
          0 => 'php',
          1 => '2scripts/pearcmd.php',
        ),
        57 => 
        array (
          0 => 'data',
          1 => '2package.dtd',
        ),
        58 => 
        array (
          0 => 'data',
          1 => '2pearchannel.xml',
        ),
        59 => 
        array (
          0 => 'data',
          1 => '2template.spec',
        ),
        60 => 
        array (
          0 => 'php',
          1 => '2PEAR.php',
        ),
        61 => 
        array (
          0 => 'php',
          1 => '2System.php',
        ),
      ),
    ),
    'cmd' => 'list',
  ),
), $fakelog->getLog(), 'file list 2');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
