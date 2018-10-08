--TEST--
System::_parseArgs with quoted values
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip ';
}
?>
--FILE--
<?php

require_once 'System.php';

$args = '-t \'R:\applications\PHP 5.3\tmp\' -d pear';
$opts = System::_parseArgs($args, 't:d');
var_dump($opts);

$args = '-t "R:\applications\PHP 5.3\tmp" -d pear';
$opts = System::_parseArgs($args, 't:d');
var_dump($opts);

$args = '-t \'/tmp/pear install/temp\' -d pear';
$opts = System::_parseArgs($args, 't:d');
var_dump($opts);

$args = '-t "/tmp/pear install/temp" -d pear';
$opts = System::_parseArgs($args, 't:d');
var_dump($opts);

?>
--EXPECT--
array(2) {
  [0]=>
  array(2) {
    [0]=>
    array(2) {
      [0]=>
      string(1) "t"
      [1]=>
      string(27) "R:\applications\PHP 5.3\tmp"
    }
    [1]=>
    array(2) {
      [0]=>
      string(1) "d"
      [1]=>
      NULL
    }
  }
  [1]=>
  array(1) {
    [0]=>
    string(4) "pear"
  }
}
array(2) {
  [0]=>
  array(2) {
    [0]=>
    array(2) {
      [0]=>
      string(1) "t"
      [1]=>
      string(27) "R:\applications\PHP 5.3\tmp"
    }
    [1]=>
    array(2) {
      [0]=>
      string(1) "d"
      [1]=>
      NULL
    }
  }
  [1]=>
  array(1) {
    [0]=>
    string(4) "pear"
  }
}
array(2) {
  [0]=>
  array(2) {
    [0]=>
    array(2) {
      [0]=>
      string(1) "t"
      [1]=>
      string(22) "/tmp/pear install/temp"
    }
    [1]=>
    array(2) {
      [0]=>
      string(1) "d"
      [1]=>
      NULL
    }
  }
  [1]=>
  array(1) {
    [0]=>
    string(4) "pear"
  }
}
array(2) {
  [0]=>
  array(2) {
    [0]=>
    array(2) {
      [0]=>
      string(1) "t"
      [1]=>
      string(22) "/tmp/pear install/temp"
    }
    [1]=>
    array(2) {
      [0]=>
      string(1) "d"
      [1]=>
      NULL
    }
  }
  [1]=>
  array(1) {
    [0]=>
    string(4) "pear"
  }
}