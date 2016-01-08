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

	public function test_int() {
		// Check if one could create integer values via $f->int
		// Check if $f->int throws if not passed an integer.
		$this->assertFalse("Implement me!");
	}

	public function test_string() {
		// Similar to test_int
		$this->assertFalse("Implement me!");
	}

	public function test_date() {
		// Similar to test_int
		$this->assertFalse("Implement me!");
	}

	public function test_EQ() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p1 = $f->int(1)->EQ()->int(1);
		$this->assertTrue($i->interpret($p1, array()));

		$p2 = $f->int(1)->EQ()->int(2);
		$this->assertFalse($i->interpret($p2, array()));

		$p3 = $f->str("hello")->EQ()->str("hello");
		$this->assertTrue($i->interpret($p3, array()));

		$p4 = $f->str("hello")->EQ()->str("world");
		$this->assertFalse($i->interpret($p4, array()));

		$p5 = $f->date("2016-01-01")->EQ()->date("2016-01-01");
		$this->assertTrue($i->interpret($p5, array()));

		$p6 = $f->date("2016-01-01")->EQ()->date("2016-01-02");
		$this->assertFalse($i->interpret($p6, array()));
	}

	public function test_NEQ() {
		$this->assertFalse("Implement me!");
	}

	public function test_LE() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->int(1)->LE()->int(1);
		$this->assertTrue($i->interpret($p, array()));

		$p2 = $f->int(1)->LE()->int(2);
		$this->assertFalse($i->interpret($p, array()));

		// Check behaviour of LE on dates and strings
		$this->assertFalse("Implement me!");
	}

	public function test_LT() {
		$this->assertFalse("Implement me!");
	}

	public function test_GE() {
		$this->assertFalse("Implement me!");
	}

	public function test_GT() {
		$this->assertFalse("Implement me!");
	}

}