--TEST--
channel-info command (channel.xml file)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$c = '<?xml version="1.0" encoding="ISO-8859-1" ?>
<channel version="1.0" xmlns="http://pear.php.net/channel-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://pear.php.net/dtd/channel-1.0.xsd">
 <name>froo</name>
 <suggestedalias>froo</suggestedalias>
 <summary>PHP Extension and Application Repository</summary>
 <validatepackage version="1.0">PEAR_Validate</validatepackage>
 <servers>
  <primary>
   <rest>
    <baseurl type="REST1.0">http://pear.php.net/rest/</baseurl>
    <baseurl type="REST1.1">http://pear.php.net/rest/</baseurl>
    <baseurl type="REST1.2">http://pear.php.net/rest/</baseurl>
    <baseurl type="REST1.3">http://pear.php.net/rest/</baseurl>
   </rest>
  </primary>
  <mirror host="poor.php.net">
   <rest>
    <baseurl type="REST1.0">http://poor.php.net/rest/</baseurl>
    <baseurl type="REST1.1">http://poor.php.net/rest/</baseurl>
    <baseurl type="REST1.2">http://poor.php.net/rest/</baseurl>
    <baseurl type="REST1.3">http://poor.php.net/rest/</baseurl>
   </rest>
  </mirror>
 </servers>
</channel>';
$fp = fopen($temp_path . DIRECTORY_SEPARATOR . 'channel.xml', 'wb');
fwrite($fp, $c);
fclose($fp);
$e = $command->run('channel-info', array(), array($temp_path . DIRECTORY_SEPARATOR . 'channel.xml'));
$phpunit->assertNoErrors('1');
$phpunit->assertEquals(array (
  0 =>
  array (
    'info' =>
    array (
      'main' =>
      array (
        'caption' => 'Channel froo Information:',
        'border' => true,
        'data' =>
        array (
          'server' =>
          array (
            0 => 'Name and Server',
            1 => 'froo',
          ),
          'summary' =>
          array (
            0 => 'Summary',
            1 => 'PHP Extension and Application Repository',
          ),
          'vpackage' =>
          array (
            0 => 'Validation Package Name',
            1 => 'PEAR_Validate',
          ),
          'vpackageversion' =>
          array (
            0 => 'Validation Package Version',
            1 => '1.0',
          ),
        ),
      ),
      'protocols' =>
      array (
        'data' =>
        array (
          0 =>
          array (
            0 => 'rest',
            1 => 'REST1.0',
            2 => 'http://pear.php.net/rest/',
          ),
          1 =>
          array (
            0 => 'rest',
            1 => 'REST1.1',
            2 => 'http://pear.php.net/rest/',
          ),
          2 =>
          array (
            0 => 'rest',
            1 => 'REST1.2',
            2 => 'http://pear.php.net/rest/',
          ),
          3 =>
          array (
            0 => 'rest',
            1 => 'REST1.3',
            2 => 'http://pear.php.net/rest/',
          ),
        ),
        'caption' => 'Server Capabilities',
        'headline' =>
        array (
          0 => 'Type',
          1 => 'Version/REST type',
          2 => 'Function Name/REST base',
        ),
      ),
      'mirrors' =>
      array (
        'data' =>
        array (
          0 =>
          array (
            0 => 'poor.php.net',
          ),
        ),
        'caption' => 'Channel froo Mirrors:',
      ),
      'mirrorprotocols0' =>
      array (
        'data' =>
        array (
          0 =>
          array (
            0 => 'rest',
            1 => 'REST1.0',
            2 => 'http://poor.php.net/rest/',
          ),
          1 =>
          array (
            0 => 'rest',
            1 => 'REST1.1',
            2 => 'http://poor.php.net/rest/',
          ),
          2 =>
          array (
            0 => 'rest',
            1 => 'REST1.2',
            2 => 'http://poor.php.net/rest/',
          ),
          3 =>
          array (
            0 => 'rest',
            1 => 'REST1.3',
            2 => 'http://poor.php.net/rest/',
          ),
        ),
        'caption' => 'Mirror poor.php.net Capabilities',
        'headline' =>
        array (
          0 => 'Type',
          1 => 'Version/REST type',
          2 => 'Function Name/REST base',
        ),
      ),
    ),
    'cmd' => 'channel-info',
  ),
), $fakelog->getLog(), 'log 1');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(dirname(__FILE__)) . '/teardown.php.inc';
?>
--EXPECT--
tests done
