--TEST--
PEAR_Channelfile->resetFunctions()
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php


require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$chf->fromXmlString($first = '<?xml version="1.0" encoding="ISO-8859-1" ?>
<channel version="1.0" xmlns="http://pear.php.net/channel-1.0"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://pear.php.net/dtd/channel-1.0.xsd">
 <name>pear.php.net</name>
 <suggestedalias>pear</suggestedalias>
 <summary>PHP Extension and Application Repository</summary>
 <servers>
  <primary>
   <rest>
    <baseurl type="REST1.0">http://pear.php.net/rest/</baseurl>
    <baseurl type="REST1.1">http://pear.php.net/rest/</baseurl>
    <baseurl type="REST1.2">http://pear.php.net/rest/</baseurl>
    <baseurl type="REST1.3">http://pear.php.net/rest/</baseurl>
   </rest>
  </primary>
 </servers>
</channel>');
$phpt->assertTrue($chf->validate(), 'initial parse');
$phpt->assertEquals(array (
  'attribs' =>
  array (
    'version' => '1.0',
    'xmlns' => 'http://pear.php.net/channel-1.0',
    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
    'xsi:schemaLocation' => 'http://pear.php.net/dtd/channel-1.0.xsd',
  ),
  'name' => 'pear.php.net',
  'suggestedalias' => 'pear',
  'summary' => 'PHP Extension and Application Repository',
  'servers' =>
  array (
    'primary' =>
    array (
      'rest' =>
      array (
        'baseurl' =>
        array (
          0 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.0',
            ),
            '_content' => 'http://pear.php.net/rest/',
          ),
          1 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.1',
            ),
            '_content' => 'http://pear.php.net/rest/',
          ),
          2 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.2',
            ),
            '_content' => 'http://pear.php.net/rest/',
          ),
          3 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.3',
            ),
            '_content' => 'http://pear.php.net/rest/',
          ),
        ),
      ),
    ),
  ),
), $chf->toArray(), 'Parsed array of default is not correct');
$chf->fromXmlString($chf->toXml());
$chf->resetFunctions('rest');

$phpt->assertTrue($chf->validate(), 're-parsing validate');
$phpt->assertEquals(array (
  'attribs' =>
  array (
    'version' => '1.0',
    'xmlns' => 'http://pear.php.net/channel-1.0',
    'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
    'xsi:schemaLocation' => 'http://pear.php.net/dtd/channel-1.0 http://pear.php.net/dtd/channel-1.0.xsd',
  ),
  'name' => 'pear.php.net',
  'summary' => 'PHP Extension and Application Repository',
  'suggestedalias' => 'pear',
  'servers' =>
  array (
    'primary' =>
    array (
    ),
  ),
), $chf->toArray(), 'Re-parsed array of default is not correct');
echo 'tests done';
?>
--EXPECT--
tests done
