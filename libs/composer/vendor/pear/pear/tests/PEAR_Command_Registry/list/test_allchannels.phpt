--TEST--
list command, --allchannels opt
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$reg = &$config->getRegistry();
$workingcopy = array (
  'pear.php.net' =>
  array (
    'info' =>
    array (
      'caption' => 'Installed packages, channel pear.php.net:',
      'border' => true,
      'data' =>
      array (
        0 =>
        array (
          0 => '(no packages installed)',
        ),
        array (
        ),
      ),
      'channel' => 'pear.php.net',
    ),
    'cmd' => 'list',
  ),
  'doc.php.net' =>
  array (
    'info' =>
    array (
      'caption' => 'Installed packages, channel doc.php.net:',
      'border' => true,
      'data' =>
      array (
        0 =>
        array (
          0 => '(no packages installed)',
        ),
        1 =>
        array (
        ),
      ),
      'channel' => 'doc.php.net',
    ),
    'cmd' => 'list',
  ),
  '__uri' =>
  array (
    'info' =>
    array (
      'caption' => 'Installed packages, channel __uri:',
      'border' => true,
      'data' =>
      array (
        0 =>
        array (
          0 => '(no packages installed)',
        ),
        array (
        ),
      ),
      'channel' => '__uri',
    ),
    'cmd' => 'list',
  ),
  'pecl.php.net' =>
  array (
    'info' =>
    array (
      'caption' => 'Installed packages, channel pecl.php.net:',
      'border' => true,
      'data' =>
      array (
        0 =>
        array (
          0 => '(no packages installed)',
        ),
        array (
        ),
      ),
      'channel' => 'pecl.php.net',
    ),
    'cmd' => 'list',
  ),
);
$actual = array();
foreach ($reg->listChannels() as $chan) {
    $actual[] = $workingcopy[$chan];
}
$e = $command->run('list', array('allchannels' => true), array());
$phpunit->assertEquals($actual, $fakelog->getLog(), 'no packages installed');

$reg = &$config->getRegistry();
$pkg = new PEAR_PackageFile($config);
$i = $pkg->fromPackageFile(dirname(__FILE__) . DIRECTORY_SEPARATOR .
    DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR . 'package2.xml',
    PEAR_VALIDATE_NORMAL);
$info = &$i->getRW();
$reg->addPackage2($info);
require_once 'PEAR/ChannelFile.php';
$ch = new PEAR_ChannelFile;
$ch->setName('gronk');
$ch->setServer('gronk');
$ch->setSummary('gronk');
$reg->addChannel($ch);
$info->setChannel('gronk');
$reg->addPackage2($info);
$info = $pkg->fromPackageFile(dirname(__FILE__) . DIRECTORY_SEPARATOR .
    DIRECTORY_SEPARATOR . 'packagefiles' . DIRECTORY_SEPARATOR . 'package-Console_Getopt.xml',
    PEAR_VALIDATE_NORMAL);
$reg->addPackage2($info);
$workingcopy = array (
  'gronk' =>
  array (
    'info' =>
    array (
      'caption' => 'Installed packages, channel gronk:',
      'border' => true,
      'headline' =>
      array (
        0 => 'Package',
        1 => 'Version',
        2 => 'State',
      ),
      'channel' => 'gronk',
      'data' =>
      array (
        0 =>
        array (
          0 => 'PEAR',
          1 => '1.4.0a1',
          2 => 'alpha',
        ),
        1 =>
        array (
        ),
      ),
    ),
    'cmd' => 'list',
  ),
  'pear.php.net' =>
  array (
    'info' =>
    array (
      'caption' => 'Installed packages, channel pear.php.net:',
      'border' => true,
      'headline' =>
      array (
        0 => 'Package',
        1 => 'Version',
        2 => 'State',
      ),
      'channel' => 'pear.php.net',
      'data' =>
      array (
        0 =>
        array (
          0 => 'Console_Getopt',
          1 => '1.2',
          2 => 'stable',
        ),
        1 =>
        array (
          0 => 'PEAR',
          1 => '1.4.0a1',
          2 => 'alpha',
        ),
        2 =>
        array (
        ),
      ),
    ),
    'cmd' => 'list',
  ),
  '__uri' =>
  array (
    'info' =>
    array (
      'caption' => 'Installed packages, channel __uri:',
      'border' => true,
      'data' =>
      array (
        0 =>
        array (
          0 => '(no packages installed)',
        ),
        1 =>
        array (
        ),
      ),
      'channel' => '__uri',
    ),
    'cmd' => 'list',
  ),
  'doc.php.net' =>
  array (
    'info' =>
    array (
      'caption' => 'Installed packages, channel doc.php.net:',
      'border' => true,
      'data' =>
      array (
        0 =>
        array (
          0 => '(no packages installed)',
        ),
        1 =>
        array (
        ),
      ),
      'channel' => 'doc.php.net',
    ),
    'cmd' => 'list',
  ),
  'pecl.php.net' =>
  array (
    'info' =>
    array (
      'caption' => 'Installed packages, channel pecl.php.net:',
      'border' => true,
      'data' =>
      array (
        0 =>
        array (
          0 => '(no packages installed)',
        ),
        1 =>
        array (
        ),
      ),
      'channel' => 'pecl.php.net',
    ),
    'cmd' => 'list',
  ),
);
$actual = array();
foreach ($reg->listChannels() as $chan) {
    $actual[] = $workingcopy[$chan];
}
$e = $command->run('list', array('allchannels' => true), array());
$phpunit->assertEquals($actual, $fakelog->getLog(), 'installed');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
