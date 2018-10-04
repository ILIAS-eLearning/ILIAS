--TEST--
PEAR_Command::factory()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
if (@!include_once 'PEAR/Command/Remoteinstall.php') {
    die('skip standard test will be used');
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
PEAR_Command::registerCommands();
$phpunit->assertEquals(array (
  'build' => 'PEAR_Command_Build',
  'bundle' => 'PEAR_Command_Install',
  'channel-add' => 'PEAR_Command_Channels',
  'channel-alias' => 'PEAR_Command_Channels',
  'channel-delete' => 'PEAR_Command_Channels',
  'channel-discover' => 'PEAR_Command_Channels',
  'channel-info' => 'PEAR_Command_Channels',
  'channel-login' => 'PEAR_Command_Channels',
  'channel-logout' => 'PEAR_Command_Channels',
  'channel-update' => 'PEAR_Command_Channels',
  'clear-cache' => 'PEAR_Command_Remote',
  'config-create' => 'PEAR_Command_Config',
  'config-get' => 'PEAR_Command_Config',
  'config-help' => 'PEAR_Command_Config',
  'config-set' => 'PEAR_Command_Config',
  'config-show' => 'PEAR_Command_Config',
  'convert' => 'PEAR_Command_Package',
  'cvsdiff' => 'PEAR_Command_Package',
  'cvstag' => 'PEAR_Command_Package',
  'download' => 'PEAR_Command_Remote',
  'download-all' => 'PEAR_Command_Mirror',
  'info' => 'PEAR_Command_Registry',
  'install' => 'PEAR_Command_Install',
  'list' => 'PEAR_Command_Registry',
  'list-all' => 'PEAR_Command_Remote',
  'list-categories' => 'PEAR_Command_Categories',
  'list-category' => 'PEAR_Command_Categories',
  'list-channels' => 'PEAR_Command_Channels',
  'list-files' => 'PEAR_Command_Registry',
  'list-packages' => 'PEAR_Command_Categories',
  'list-upgrades' => 'PEAR_Command_Remote',
  'login' => 'PEAR_Command_Auth',
  'logout' => 'PEAR_Command_Auth',
  'makerpm' => 'PEAR_Command_Package',
  'package' => 'PEAR_Command_Package',
  'package-dependencies' => 'PEAR_Command_Package',
  'package-validate' => 'PEAR_Command_Package',
  'pickle' => 'PEAR_Command_Pickle',
  'remote-info' => 'PEAR_Command_Remote',
  'remote-install' => 'PEAR_Command_Remoteinstall',
  'remote-list' => 'PEAR_Command_Remote',
  'remote-uninstall' => 'PEAR_Command_Remoteinstall',
  'remote-upgrade' => 'PEAR_Command_Remoteinstall',
  'remote-upgrade-all' => 'PEAR_Command_Remoteinstall',
  'run-scripts' => 'PEAR_Command_Install',
  'run-tests' => 'PEAR_Command_Test',
  'search' => 'PEAR_Command_Remote',
  'shell-test' => 'PEAR_Command_Registry',
  'sign' => 'PEAR_Command_Package',
  'svntag' => 'PEAR_Command_Package',
  'uninstall' => 'PEAR_Command_Install',
  'update-channels' => 'PEAR_Command_Channels',
  'upgrade' => 'PEAR_Command_Install',
  'upgrade-all' => 'PEAR_Command_Install',
), PEAR_Command::getCommands(), 'getcommands');
$phpunit->assertEquals(54, count(PEAR_Command::getCommands()), 'count commands');
$phpunit->assertEquals(54, count(PEAR_Command::getShortcuts()), 'count shortcuts');
$phpunit->assertEquals(array (
  'b' => 'build',
  'bun' => 'bundle',
  'c2' => 'convert',
  'ca' => 'channel-add',
  'cat' => 'list-category',
  'cats' => 'list-categories',
  'cc' => 'clear-cache',
  'cd' => 'cvsdiff',
  'cde' => 'channel-delete',
  'cg' => 'config-get',
  'ch' => 'config-help',
  'cha' => 'channel-alias',
  'ci' => 'channel-info',
  'cli' => 'channel-login',
  'clo' => 'channel-logout',
  'coc' => 'config-create',
  'cs' => 'config-set',
  'csh' => 'config-show',
  'ct' => 'cvstag',
  'cu' => 'channel-update',
  'd' => 'download',
  'da' => 'download-all',
  'di' => 'channel-discover',
  'fl' => 'list-files',
  'i' => 'install',
  'in' => 'info',
  'inr' => 'remote-install',
  'l' => 'list',
  'la' => 'list-all',
  'lc' => 'list-channels',
  'li' => 'login',
  'lo' => 'logout',
  'lp' => 'list-packages',
  'lu' => 'list-upgrades',
  'p' => 'package',
  'pd' => 'package-dependencies',
  'pi' => 'pickle',
  'pv' => 'package-validate',
  'ri' => 'remote-info',
  'rl' => 'remote-list',
  'rpm' => 'makerpm',
  'rs' => 'run-scripts',
  'rt' => 'run-tests',
  'si' => 'sign',
  'sp' => 'search',
  'st' => 'shell-test',
  'sv' => 'svntag',
  'ua' => 'upgrade-all',
  'uar' => 'remote-upgrade-all',
  'uc' => 'update-channels',
  'un' => 'uninstall',
  'unr' => 'remote-uninstall',
  'up' => 'upgrade',
  'upr' => 'remote-upgrade',
), PEAR_Command::getShortcuts(), 'getshortcuts');
PEAR_Command::getGetoptArgs('build', $s, $l);
$phpunit->assertEquals('', $s, 'short build');
$phpunit->assertEquals(array(), $l, 'long build');
PEAR_Command::getGetoptArgs('bundle', $s, $l);
$phpunit->assertEquals('d:f', $s, 'short bundle');
$phpunit->assertEquals(array('destination=', 'force'), $l, 'long bundle');
PEAR_Command::getGetoptArgs('channel-add', $s, $l);
$phpunit->assertEquals('', $s, 'short channel-add');
$phpunit->assertEquals(array(), $l, 'long channel-add');
PEAR_Command::getGetoptArgs('channel-alias', $s, $l);
$phpunit->assertEquals('', $s, 'short channel-alias');
$phpunit->assertEquals(array(), $l, 'long channel-alias');
PEAR_Command::getGetoptArgs('channel-delete', $s, $l);
$phpunit->assertEquals('', $s, 'short channel-delete');
$phpunit->assertEquals(array(), $l, 'long channel-delete');
PEAR_Command::getGetoptArgs('channel-discover', $s, $l);
$phpunit->assertEquals('', $s, 'short channel-discover');
$phpunit->assertEquals(array(), $l, 'long channel-discover');
PEAR_Command::getGetoptArgs('channel-info', $s, $l);
$phpunit->assertEquals('', $s, 'short channel-info');
$phpunit->assertEquals(array(), $l, 'long channel-info');
PEAR_Command::getGetoptArgs('channel-update', $s, $l);
$phpunit->assertEquals('fc:', $s, 'short channel-update');
$phpunit->assertEquals(array('force', 'channel='), $l, 'long channel-update');
PEAR_Command::getGetoptArgs('clear-cache', $s, $l);
$phpunit->assertEquals('', $s, 'short clear-cache');
$phpunit->assertEquals(array(), $l, 'long clear-cache');
PEAR_Command::getGetoptArgs('config-create', $s, $l);
$phpunit->assertEquals('w', $s, 'short config-create');
$phpunit->assertEquals(array('windows'), $l, 'long config-create');
PEAR_Command::getGetoptArgs('config-get', $s, $l);
$phpunit->assertEquals('c:', $s, 'short config-get');
$phpunit->assertEquals(array('channel='), $l, 'long config-get');
PEAR_Command::getGetoptArgs('config-set', $s, $l);
$phpunit->assertEquals('c:', $s, 'short config-set');
$phpunit->assertEquals(array('channel='), $l, 'long config-set');
PEAR_Command::getGetoptArgs('config-show', $s, $l);
$phpunit->assertEquals('c:', $s, 'short config-show');
$phpunit->assertEquals(array('channel='), $l, 'long config-show');
PEAR_Command::getGetoptArgs('convert', $s, $l);
$phpunit->assertEquals('f', $s, 'short convert');
$phpunit->assertEquals(array('flat'), $l, 'long convert');
PEAR_Command::getGetoptArgs('cvsdiff', $s, $l);
$phpunit->assertEquals('qQD:R:r:cuibBn', $s, 'short cvsdiff');
$phpunit->assertEquals(array (
  0 => 'quiet',
  1 => 'reallyquiet',
  2 => 'date=',
  3 => 'release=',
  4 => 'revision=',
  5 => 'context',
  6 => 'unified',
  7 => 'ignore-case',
  8 => 'ignore-whitespace',
  9 => 'ignore-blank-lines',
  10 => 'brief',
  11 => 'dry-run',
), $l, 'long cvsdiff');
PEAR_Command::getGetoptArgs('cvstag', $s, $l);
$phpunit->assertEquals('qQFdn', $s, 'short cvstag');
$phpunit->assertEquals(array (
  0 => 'quiet',
  1 => 'reallyquiet',
  2 => 'slide',
  3 => 'delete',
  4 => 'dry-run',
), $l, 'long cvstag');
PEAR_Command::getGetoptArgs('download', $s, $l);
$phpunit->assertEquals('Z', $s, 'short download');
$phpunit->assertEquals(array('nocompress'), $l, 'long download');
PEAR_Command::getGetoptArgs('download-all', $s, $l);
$phpunit->assertEquals('c:', $s, 'short download-all');
$phpunit->assertEquals(array('channel='), $l, 'long download-all');
PEAR_Command::getGetoptArgs('info', $s, $l);
$phpunit->assertEquals('', $s, 'short info');
$phpunit->assertEquals(array(), $l, 'long info');
PEAR_Command::getGetoptArgs('install', $s, $l);
$phpunit->assertEquals('flnrsBZR:P:aoOp', $s, 'short install');
$phpunit->assertEquals(array (
  0 => 'force',
  1 => 'loose',
  2 => 'nodeps',
  3 => 'register-only',
  4 => 'soft',
  5 => 'nobuild',
  6 => 'nocompress',
  7 => 'installroot=',
  'packagingroot=',
  'ignore-errors',
  'alldeps',
  'onlyreqdeps',
  'offline',
  'pretend',
), $l, 'long install');
PEAR_Command::getGetoptArgs('list', $s, $l);
$phpunit->assertEquals('c:ai', $s, 'short list');
$phpunit->assertEquals(array('channel=', 'allchannels', 'channelinfo'), $l, 'long list');
PEAR_Command::getGetoptArgs('list-all', $s, $l);
$phpunit->assertEquals('c:i', $s, 'short list-all');
$phpunit->assertEquals(array('channel=', 'channelinfo'), $l, 'long list-all');
PEAR_Command::getGetoptArgs('list-channels', $s, $l);
$phpunit->assertEquals('', $s, 'short list-channels');
$phpunit->assertEquals(array(), $l, 'long list-channels');
PEAR_Command::getGetoptArgs('list-files', $s, $l);
$phpunit->assertEquals('', $s, 'short list-files');
$phpunit->assertEquals(array(), $l, 'long list-files');
PEAR_Command::getGetoptArgs('list-upgrades', $s, $l);
$phpunit->assertEquals('i', $s, 'short list-upgrades');
$phpunit->assertEquals(array('channelinfo'), $l, 'long list-upgrades');
PEAR_Command::getGetoptArgs('login', $s, $l);
$phpunit->assertEquals('', $s, 'short login');
$phpunit->assertEquals(array(), $l, 'long login');
PEAR_Command::getGetoptArgs('logout', $s, $l);
$phpunit->assertEquals('', $s, 'short logout');
$phpunit->assertEquals(array(), $l, 'long logout');
PEAR_Command::getGetoptArgs('makerpm', $s, $l);
$phpunit->assertEquals('t:p:', $s, 'short makerpm');
$phpunit->assertEquals(array(
  0 => 'spec-template=',
  1 => 'rpm-pkgname=',
), $l, 'long makerpm');
PEAR_Command::getGetoptArgs('package', $s, $l);
$phpunit->assertEquals('Zn', $s, 'short package');
$phpunit->assertEquals(array (
  0 => 'nocompress',
  1 => 'showname',
), $l, 'long package');
PEAR_Command::getGetoptArgs('package-dependencies', $s, $l);
$phpunit->assertEquals('', $s, 'short package-dependencies');
$phpunit->assertEquals(array (), $l, 'long package-dependencies');
PEAR_Command::getGetoptArgs('package-validate', $s, $l);
$phpunit->assertEquals('', $s, 'short package-validate');
$phpunit->assertEquals(array (), $l, 'long package-validate');
PEAR_Command::getGetoptArgs('remote-info', $s, $l);
$phpunit->assertEquals('', $s, 'short remote-info');
$phpunit->assertEquals(array (), $l, 'long remote-info');
PEAR_Command::getGetoptArgs('remote-install', $s, $l);
$phpunit->assertEquals('fnrsBZR:aoF:Op', $s, 'short remote-install');
$phpunit->assertEquals( array (
  0 => 'force',
  1 => 'nodeps',
  2 => 'register-only',
  3 => 'soft',
  4 => 'nobuild',
  5 => 'nocompress',
  6 => 'installroot=',
  7 => 'ignore-errors',
  8 => 'alldeps',
  9 => 'onlyreqdeps',
  10 => 'remoteconfig=',
  11 => 'offline',
  12 => 'pretend',
 )
, $l, 'long remote-install');
PEAR_Command::getGetoptArgs('remote-list', $s, $l);
$phpunit->assertEquals('c:', $s, 'short remote-list');
$phpunit->assertEquals(array ('channel='), $l, 'long remote-list');
PEAR_Command::getGetoptArgs('remote-uninstall', $s, $l);
$phpunit->assertEquals('nrR:F:O', $s, 'short remote-uninstall');
$phpunit->assertEquals(array (
  0 => 'nodeps',
  1 => 'register-only',
  2 => 'installroot=',
  3 => 'ignore-errors',
  4 => 'remoteconfig=',
  5 => 'offline',
), $l, 'long remote-uninstall');
PEAR_Command::getGetoptArgs('remote-upgrade', $s, $l);
$phpunit->assertEquals('fnrBZR:aoF:Op', $s, 'short remote-upgrade');
$phpunit->assertEquals(array (
  0 => 'force',
  1 => 'nodeps',
  2 => 'register-only',
  3 => 'nobuild',
  4 => 'nocompress',
  5 => 'installroot=',
  6 => 'ignore-errors',
  7 => 'alldeps',
  8 => 'onlyreqdeps',
  9 => 'remoteconfig=',
  10 => 'offline',
  11 => 'pretend',
), $l, 'long remote-upgrade');
PEAR_Command::getGetoptArgs('remote-upgrade-all', $s, $l);
$phpunit->assertEquals('nrBZR:F:', $s, 'short remote-upgrade-all');
$phpunit->assertEquals(array (
  0 => 'nodeps',
  1 => 'register-only',
  2 => 'nobuild',
  3 => 'nocompress',
  4 => 'installroot=',
  5 => 'ignore-errors',
  6 => 'remoteconfig=',
), $l, 'long remote-upgrade-all');
PEAR_Command::getGetoptArgs('run-tests', $s, $l);
$phpunit->assertEquals('ri:lqsputc:x', $s, 'short run-tests');
$phpunit->assertEquals(array (
    'recur',
    'ini=',
    'realtimelog',
    'quiet',
    'simple',
    'package',
    'phpunit',
    'tapoutput',
    'cgi=',
    'coverage',
    ), $l, 'long run-tests');
PEAR_Command::getGetoptArgs('search', $s, $l);
$phpunit->assertEquals('c:ai', $s, 'short search');
$phpunit->assertEquals(array ('channel=', 'allchannels', 'channelinfo',), $l, 'long search');
PEAR_Command::getGetoptArgs('shell-test', $s, $l);
$phpunit->assertEquals('', $s, 'short shell-test');
$phpunit->assertEquals(array (), $l, 'long shell-test');
PEAR_Command::getGetoptArgs('sign', $s, $l);
$phpunit->assertEquals('v', $s, 'short sign');
$phpunit->assertEquals(array ('verbose'), $l, 'long sign');
PEAR_Command::getGetoptArgs('uninstall', $s, $l);
$phpunit->assertEquals('nrR:O', $s, 'short uninstall');
$phpunit->assertEquals(array (
  0 => 'nodeps',
  1 => 'register-only',
  2 => 'installroot=',
  3 => 'ignore-errors',
  4 => 'offline',
), $l, 'long uninstall');
PEAR_Command::getGetoptArgs('update-channels', $s, $l);
$phpunit->assertEquals('', $s, 'short update-channels');
$phpunit->assertEquals(array (), $l, 'long update-channels');
PEAR_Command::getGetoptArgs('upgrade', $s, $l);
$phpunit->assertEquals('c:flnrBZR:aoOp', $s, 'short upgrade');
$phpunit->assertEquals(array (
  0 => 'channel=',
  1 => 'force',
  2 => 'loose',
  3 => 'nodeps',
  4 => 'register-only',
  5 => 'nobuild',
  6 => 'nocompress',
  7 => 'installroot=',
  8 => 'ignore-errors',
  9 => 'alldeps',
  10 => 'onlyreqdeps',
  11 => 'offline',
  12 => 'pretend',
), $l, 'long upgrade');
PEAR_Command::getGetoptArgs('upgrade-all', $s, $l);
$phpunit->assertEquals('c:nrBZR:', $s, 'short upgrade-all');
$phpunit->assertEquals(array (
  0 => 'channel=',
  1 => 'nodeps',
  2 => 'register-only',
  3 => 'nobuild',
  4 => 'nocompress',
  5 => 'installroot=',
  6 => 'ignore-errors',
  7 => 'loose',
), $l, 'long upgrade-all');
$phpunit->assertEquals('Build an Extension From C Source'
    , PEAR_Command::getDescription('build'), 'build');
$phpunit->assertEquals('Unpacks a Pecl Package'
    , PEAR_Command::getDescription('bundle'), 'bundle');
$phpunit->assertEquals('Add a Channel'
    , PEAR_Command::getDescription('channel-add'), 'channel-add');
$phpunit->assertEquals('Specify an alias to a channel name'
    , PEAR_Command::getDescription('channel-alias'), 'channel-alias');
$phpunit->assertEquals('Remove a Channel From the List'
    , PEAR_Command::getDescription('channel-delete'), 'channel-delete');
$phpunit->assertEquals('Initialize a Channel from its server'
    , PEAR_Command::getDescription('channel-discover'), 'channel-discover');
$phpunit->assertEquals('Retrieve Information on a Channel'
    , PEAR_Command::getDescription('channel-info'), 'channel-info');
$phpunit->assertEquals('Update an Existing Channel'
    , PEAR_Command::getDescription('channel-update'), 'channel-update');
$phpunit->assertEquals('Clear Web Services Cache'
    , PEAR_Command::getDescription('clear-cache'), 'clear-cache');
$phpunit->assertEquals('Create a Default configuration file'
    , PEAR_Command::getDescription('config-create'), 'config-create');
$phpunit->assertEquals('Show One Setting'
    , PEAR_Command::getDescription('config-get'), 'config-get');
$phpunit->assertEquals('Show Information About Setting'
    , PEAR_Command::getDescription('config-help'), 'config-help');
$phpunit->assertEquals('Change Setting'
    , PEAR_Command::getDescription('config-set'), 'config-set');
$phpunit->assertEquals('Show All Settings'
    , PEAR_Command::getDescription('config-show'), 'config-show');
$phpunit->assertEquals('Convert a package.xml 1.0 to package.xml 2.0 format'
    , PEAR_Command::getDescription('convert'), 'convert');
$phpunit->assertEquals('Run a "cvs diff" for all files in a package'
    , PEAR_Command::getDescription('cvsdiff'), 'cvsdiff');
$phpunit->assertEquals('Set CVS Release Tag'
    , PEAR_Command::getDescription('cvstag'), 'cvstag');
$phpunit->assertEquals('Download Package'
    , PEAR_Command::getDescription('download'), 'download');
$phpunit->assertEquals('Downloads each available package from the default channel'
    , PEAR_Command::getDescription('download-all'), 'download-all');
$phpunit->assertEquals('Display information about a package'
    , PEAR_Command::getDescription('info'), 'info');
$phpunit->assertEquals('Install Package'
    , PEAR_Command::getDescription('install'), 'install');
$phpunit->assertEquals('List Installed Packages In The Default Channel'
    , PEAR_Command::getDescription('list'), 'list');
$phpunit->assertEquals('List All Packages'
    , PEAR_Command::getDescription('list-all'), 'list-all');
$phpunit->assertEquals('List Available Channels'
    , PEAR_Command::getDescription('list-channels'), 'list-channels');
$phpunit->assertEquals('List Files In Installed Package'
    , PEAR_Command::getDescription('list-files'), 'list-files');
$phpunit->assertEquals('List Available Upgrades'
    , PEAR_Command::getDescription('list-upgrades'), 'list-upgrades');
$phpunit->assertEquals('Connects and authenticates to remote server [Deprecated in favor of channel-login]'
    , PEAR_Command::getDescription('login'), 'login');
$phpunit->assertEquals('Logs out from the remote server [Deprecated in favor of channel-logout]'
    , PEAR_Command::getDescription('logout'), 'logout');
$phpunit->assertEquals('Builds an RPM spec file from a PEAR package'
    , PEAR_Command::getDescription('makerpm'), 'makerpm');
$phpunit->assertEquals('Build Package'
    , PEAR_Command::getDescription('package'), 'package');
$phpunit->assertEquals('Show package dependencies'
    , PEAR_Command::getDescription('package-dependencies'), 'package-dependencies');
$phpunit->assertEquals('Validate Package Consistency'
    , PEAR_Command::getDescription('package-validate'), 'package-validate');
$phpunit->assertEquals('Information About Remote Packages'
    , PEAR_Command::getDescription('remote-info'), 'remote-info');
$phpunit->assertEquals('List Remote Packages'
    , PEAR_Command::getDescription('remote-list'), 'remote-list');
$phpunit->assertEquals('Run Regression Tests'
    , PEAR_Command::getDescription('run-tests'), 'run-tests');
$phpunit->assertEquals('Search remote package database'
    , PEAR_Command::getDescription('search'), 'search');
$phpunit->assertEquals('Shell Script Test'
    , PEAR_Command::getDescription('shell-test'), 'shell-test');
$phpunit->assertEquals('Sign a package distribution file'
    , PEAR_Command::getDescription('sign'), 'sign');
$phpunit->assertEquals('Un-install Package'
    , PEAR_Command::getDescription('uninstall'), 'uninstall');
$phpunit->assertEquals('Update the Channel List'
    , PEAR_Command::getDescription('update-channels'), 'update-channels');
$phpunit->assertEquals('Upgrade Package'
    , PEAR_Command::getDescription('upgrade'), 'upgrade');
$phpunit->assertEquals('Upgrade All Packages [Deprecated in favor of calling upgrade with no parameters]'
    , PEAR_Command::getDescription('upgrade-all'), 'upgrade-all');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
