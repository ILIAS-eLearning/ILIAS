<?php
/**
 * go-pear.phar creator.  Requires PHP_Archive version 0.11.0 or newer
 *
 * PHP version 5.1+
 *
 * To use, in pear-core create a directory
 * named go-pear-tarballs, and run these commands in the directory
 *
 * <pre>
 * $ pear download -Z PEAR Archive_Tar Console_Getopt Structures_Graph XML_Util
 * $ mkdir src && cd src
 * $ for i in ../*.tar; do tar xvf $i; done
 * $ mv *\/* .
 * </pre>
 *
 * finally, run this script using PHP 5.1's cli php in the main directory
 *
 * @category   pear
 * @package    PEAR
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2005-2009 The Authors
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 */
error_reporting(error_reporting() & ~E_STRICT & ~E_DEPRECATED);

function replaceVersion($contents, $path)
{
    return str_replace(array('@PEAR-VER@', '@package_version@'), $GLOBALS['pearver'], $contents);
}

$peardir    = dirname(__FILE__);
$srcdir     = dirname(__FILE__) . '/go-pear-tarballs/src/';
$outputFile = 'go-pear.phar';

$dp = @scandir($peardir . '/go-pear-tarballs');
if ($dp === false) {
    die("while locating packages to install: opendir('" . $peardir . "/go-pear-tarballs') failed\n");
}

$packages = array();
foreach ($dp as $entry) {
    if ($entry{0} == '.' || !in_array(substr($entry, -4), array('.tar'))) {
        continue;
    }

    $packages[] = $entry;
}

$y = array();
foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
    if ($path == '.') {
        continue;
    }

    $y[] = $path;
}

// remove current dir, we will otherwise include SVN files, which is not good
set_include_path(implode(PATH_SEPARATOR, $y));
require_once 'PEAR/PackageFile.php';
require_once 'PEAR/Config.php';
require_once 'PHP/Archive/Creator.php';
$config = &PEAR_Config::singleton();

chdir($peardir);

$pkg = new PEAR_PackageFile($config);
$pf = $pkg->fromPackageFile($peardir . DIRECTORY_SEPARATOR . 'package2.xml', PEAR_VALIDATE_NORMAL);
if (PEAR::isError($pf)) {
    foreach ($pf->getUserInfo() as $warn) {
        echo $warn['message'] . "\n";
    }
    die($pf->getMessage());
}
$pearver = $pf->getVersion();

$creator = new PHP_Archive_Creator('index.php', $outputFile); // no compression
$creator->useDefaultFrontController('PEAR.php');
$creator->useSHA1Signature();

foreach ($packages as $package) {
    echo "adding PEAR/go-pear-tarballs/$package\n";
    $creator->addFile("go-pear-tarballs/$package", "PEAR/go-pear-tarballs/$package");
}

$commandcontents = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'go-pear-phar.php');
$commandcontents = str_replace('require_once \'', 'require_once \'phar://' . $outputFile . '/', $commandcontents);
$creator->addString($commandcontents, 'index.php');

$commandcontents = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . '/PEAR/Frontend.php');
$commandcontents = str_replace(
    array(
        "\$file = str_replace('_', '/', \$uiclass) . '.php';"
    ),
    array(
        "\$file = 'phar://" . $outputFile . "/' . str_replace('_', '/', \$uiclass) . '.php';"
    ), $commandcontents);
$commandcontents = replaceVersion($commandcontents, '');
$creator->addString($commandcontents, 'PEAR/Frontend.php');

$commandcontents = file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . '/PEAR/PackageFile/v2.php');
$commandcontents = str_replace(
    array(
        '$fp = @fopen("PEAR/Task/$taskfile.php", \'r\', true);',
    ),
    array(
        '$fp = @fopen("phar://' . $outputFile . '/PEAR/Task/$taskfile.php", \'r\', true);'
    ), $commandcontents);
$commandcontents = replaceVersion($commandcontents, '');
$commandcontents = $creator->tokenMagicRequire($commandcontents, 'a.php');
$creator->addString($commandcontents, 'PEAR/PackageFile/v2.php');

$creator->addMagicRequireCallback(array($creator, 'limitedSmartMagicRequire'));
$creator->addMagicRequireCallback('replaceVersion');
$creator->addFile($peardir . '/PEAR/Command.php', 'PEAR/Command.php');

$creator->clearMagicRequire();
$creator->addMagicRequireCallback(array($creator, 'tokenMagicRequire'));
$creator->addMagicRequireCallback('replaceVersion');
$creator->addDir($peardir . DIRECTORY_SEPARATOR . 'PEAR', array(),
    array(
        '*PEAR/Dependency2.php',
        '*PEAR/PackageFile/Generator/v1.php',
        '*PEAR/PackageFile/Generator/v2.php',
        '*PEAR/PackageFile/v2/Validator.php',
        '*PEAR/Downloader/Package.php',
        '*PEAR/Installer/Role.php',
        '*PEAR/ChannelFile/Parser.php',
        '*PEAR/Command/Install.xml',
        '*PEAR/Command/Install.php',
        '*PEAR/Downloader/Package.php',
        '*PEAR/Frontend/CLI.php',
        '*PEAR/Installer/Role/Common.php',
        '*PEAR/Installer/Role/Data.php',
        '*PEAR/Installer/Role/Doc.php',
        '*PEAR/Installer/Role/Php.php',
        '*PEAR/Installer/Role/Script.php',
        '*PEAR/Installer/Role/Test.php',
        '*PEAR/Installer/Role/Data.xml',
        '*PEAR/Installer/Role/Doc.xml',
        '*PEAR/Installer/Role/Php.xml',
        '*PEAR/Installer/Role/Script.xml',
        '*PEAR/Installer/Role/Test.xml',
        '*PEAR/PackageFile.php',
        '*PEAR/PackageFile/v1.php',
        '*PEAR/PackageFile/Parser/v1.php',
        '*PEAR/PackageFile/Parser/v2.php',
        '*PEAR/PackageFile/Generator/v1.php',
        '*PEAR/Proxy.php',
        '*PEAR/REST.php',
        '*PEAR/REST/10.php',
        '*PEAR/Task/Common.php',
        '*PEAR/Task/Postinstallscript.php',
        '*PEAR/Task/Postinstallscript/rw.php',
        '*PEAR/Task/Replace.php',
        '*PEAR/Task/Replace/rw.php',
        '*PEAR/Task/Windowseol.php',
        '*PEAR/Task/Windowseol/rw.php',
        '*PEAR/Task/Unixeol.php',
        '*PEAR/Task/Unixeol/rw.php',
        '*PEAR/Validator/PECL.php',
        '*PEAR/ChannelFile.php',
        '*PEAR/Command/Common.php',
        '*PEAR/Common.php',
        '*PEAR/Config.php',
        '*PEAR/Dependency2.php',
        '*PEAR/DependencyDB.php',
        '*PEAR/Downloader.php',
        '*PEAR/ErrorStack.php',
        '*PEAR/Installer.php',
        '*PEAR/Registry.php',
        '*PEAR/Remote.php',
        '*PEAR/Start.php',
        '*PEAR/Start/CLI.php',
        '*PEAR/Validate.php',
        '*PEAR/XMLParser.php',
    ), false, $peardir);

$creator->addFile($peardir . DIRECTORY_SEPARATOR . 'PEAR.php', 'PEAR.php');
$creator->addFile($peardir . DIRECTORY_SEPARATOR . 'System.php', 'System.php');
$creator->addFile($peardir . DIRECTORY_SEPARATOR . 'OS/Guess.php', 'OS/Guess.php');

$creator->addFile($srcdir . DIRECTORY_SEPARATOR . 'Archive/Tar.php', 'Archive/Tar.php');
$creator->addFile($srcdir . DIRECTORY_SEPARATOR . 'XML/Util.php', 'XML/Util.php');
$creator->addFile($srcdir . DIRECTORY_SEPARATOR . 'Console/Getopt.php', 'Console/Getopt.php');
$creator->addFile($srcdir . DIRECTORY_SEPARATOR . 'Structures/Graph.php', 'Structures/Graph.php');
$creator->addFile($srcdir . DIRECTORY_SEPARATOR . 'Structures/Graph/Node.php', 'Structures/Graph/Node.php');
$creator->addFile($srcdir . DIRECTORY_SEPARATOR . 'Structures/Graph/Manipulator/AcyclicTest.php', 'Structures/Graph/Manipulator/AcyclicTest.php');
$creator->addFile($srcdir . DIRECTORY_SEPARATOR . 'Structures/Graph/Manipulator/TopologicalSorter.php', 'Structures/Graph/Manipulator/TopologicalSorter.php');

$creator->useSHA1Signature();
$creator->savePhar(dirname(__FILE__) . DIRECTORY_SEPARATOR . $outputFile);
