--TEST--
PEAR_Registry->channelInfo() (API v1.1)
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
require_once 'PEAR/Registry.php';
$pv = phpversion() . '';
$av = $pv{0} == '4' ? 'apiversion' : 'apiVersion';
if (!in_array($av, get_class_methods('PEAR_Registry'))) {
    echo 'skip';
}
if (PEAR_Registry::apiVersion() != '1.1') {
    echo 'skip';
}
?>
--FILE--
<?php

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$ch = new PEAR_ChannelFile;
$ch->setName('test.test.test');
$ch->setAlias('foo');
$ch->setSummary('blah');
$ch->setDefaultPEARProtocols();
$ch->setBaseURL('REST1.0', 'http://pear.example.org/rest/');
$ch->setBaseURL('REST1.1', 'http://pear.example.org/rest/');
$ch->setBaseURL('REST1.2', 'http://pear.example.org/rest/');
$ch->setBaseURL('REST1.3', 'http://pear.example.org/rest/');
$reg->addChannel($ch);
$phpunit->assertNoErrors('setup');
$ret = $reg->channelInfo('snark');
$phpunit->assertNull($ret, 'snark');
$ret = $reg->channelInfo('foo', true);
$phpunit->assertNull($ret, 'foo strict');
$ret = $reg->channelInfo('foo');
$ret1 = $reg->channelInfo('test.test.test');
$ret2 = $reg->channelInfo('test.test.test', true);
$phpunit->assertTrue(isset($ret['_lastmodified']), 'lastmodified is set');
unset($ret['_lastmodified']);
$phpunit->assertTrue(isset($ret1['_lastmodified']), 'lastmodified is set1');
unset($ret1['_lastmodified']);
$phpunit->assertTrue(isset($ret2['_lastmodified']), 'lastmodified is set2');
unset($ret2['_lastmodified']);
$phpunit->assertEquals(array (
  'name' => 'test.test.test',
  'suggestedalias' => 'foo',
  'summary' => 'blah',
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
            '_content' => 'http://pear.example.org/rest/',
          ),
          1 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.1',
            ),
            '_content' => 'http://pear.example.org/rest/',
          ),
          2 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.2',
            ),
            '_content' => 'http://pear.example.org/rest/',
          ),
          3 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.3',
            ),
            '_content' => 'http://pear.example.org/rest/',
          ),
        ),
      ),
    ),
  ),
), $ret, 'foo');

$phpunit->assertEquals(array (
  'name' => 'test.test.test',
  'suggestedalias' => 'foo',
  'summary' => 'blah',
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
            '_content' => 'http://pear.example.org/rest/',
          ),
          1 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.1',
            ),
            '_content' => 'http://pear.example.org/rest/',
          ),
          2 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.2',
            ),
            '_content' => 'http://pear.example.org/rest/',
          ),
          3 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.3',
            ),
            '_content' => 'http://pear.example.org/rest/',
          ),
        ),
      ),
    ),
  ),
), $ret1, 'test.test.test');
$phpunit->assertEquals(array (
  'name' => 'test.test.test',
  'suggestedalias' => 'foo',
  'summary' => 'blah',
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
            '_content' => 'http://pear.example.org/rest/',
          ),
          1 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.1',
            ),
            '_content' => 'http://pear.example.org/rest/',
          ),
          2 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.2',
            ),
            '_content' => 'http://pear.example.org/rest/',
          ),
          3 =>
          array (
            'attribs' =>
            array (
              'type' => 'REST1.3',
            ),
            '_content' => 'http://pear.example.org/rest/',
          ),
        ),
      ),
    ),
  ),
), $ret, 'test.test.test strict');
echo 'tests done';
?>
--CLEAN--
<?php
require_once dirname(__FILE__) . '/teardown.php.inc';
?>
--EXPECT--
tests done
