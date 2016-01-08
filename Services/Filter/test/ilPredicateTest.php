<?php

require_once("Services/Filter/classes/class.ilPredicateFactory.php");
require_once("Services/Filter/classes/class.ilDictionaryPredicateInterpreter.php");

class ilPredicateTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		//include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		//ilUnitUtil::performInitialisation();

		$this->factory = new ilPredicateFactory();
		$this->interpreter = new ilDictionaryPredicateInterpreter();
	}

	protected function tearDown() {
	}

	public function test_TRUE() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->_TRUE();

		$this->assertTrue($i->interpret($p, array()));
	}

	public function test_FALSE() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->_FALSE();

		$this->assertFalse($i->interpret($p, array()));
	}

	public function test_ALL() {
		$f = $this->factory;
		$i = $this->interpreter;

		$true = $f->_TRUE();
		$false = $f->_FALSE();

		$p = $f->_ANY($true, $true, $false);

		$this->assertFalse($i->interpret($p, array()));
		$this->assertEquals($p->subs(), array($true, $true, $false));

		$p2 = $f->_ANY($true, $true, $true);

		$this->assertTrue($i->interpret($p2, array()));
		$this->assertEquals($p->subs(), array($true, $true, $true));
	}

	public function test_AND() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->_TRUE()->_AND()->_FALSE();

		$this->assertFalse($i->interpret($p, array()));
	}

	public function test_ANY() {
		$f = $this->factory;
		$i = $this->interpreter;

		$true = $f->_TRUE();
		$false = $f->_FALSE();

		$p = $f->_ANY($true, $false, $false);

		$this->assertTrue($i->interpret($p, array()));
		$this->assertEquals($p->subs(), array($true, $false, $false));

		$p2 = $f->_ANY($false, $false, $false);

		$this->assertFalse($i->interpret($p2, array()));
		$this->assertEquals($p->subs(), array($false, $false, $false));
	}

	public function test_OR() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->_TRUE()->_OR()->_FALSE();

		$this->assertTrue($i->interpret($p, array()));
	}

	public function test_NOT() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->_NOT()->_TRUE();

		$this->assertFalse($i->interpret($p, array()));
	}
}