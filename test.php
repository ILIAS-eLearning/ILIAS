<?php

class Foo {
	function __construct() {
		require_once("foo.php");
	}
}

$foo = new Foo();

echo "->".$foo->foo."<-";