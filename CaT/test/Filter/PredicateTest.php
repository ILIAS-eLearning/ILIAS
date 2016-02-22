<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

class PredicateTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		//include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		//ilUnitUtil::performInitialisation();

		$this->factory = new \CaT\Filter\PredicateFactory();
		$this->interpreter = new \CaT\Filter\DictionaryPredicateInterpreter();
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

	public function test_IN() {
		$this->assertFalse("Implement me!");
	}

	public function test_ValueList() {
		$ls = $this->factory->vlist(1,2,3,4);
		$this->assertInstanceOf("\\CaT\\Filter\\Predicates\\ValueList", $ls);
		$vals = array_map(function (\CaT\Filter\Predicates\ValueInt $v) {
					return $v->value();
				}, $ls->values());

		$ls = $this->factory->vlist("one","two","three");
		$this->assertInstanceOf("\\CaT\\Filter\\Predicates\\ValueList", $ls);
		$vals = array_map(function (\CaT\Filter\Predicates\ValueStr $v) {
					return $v->value();
				}, $ls->values());
		$this->assertEquals(array("one","two","three"), $vals);

		try {
			$ls = $this->factory->vlist(1,"one");
			$this->assertFalse("Should have been raised.");
		}
		catch (\InvalidArgumentException $e) {
		}
	}

	public function test_one_field() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->int(1)->EQ()->field("one");

		$this->assertFalse($i->interpret($p, array()));
		$this->assertFalse($i->interpret($p, array("one" => 2)));
		$this->assertTrue($i->interpret($p, array("one" => 1)));
		$this->assertTrue($i->interpret($p, array("one" => 1, "two" => 2)));

		// Implement some more variations on the predicate, like switching
		// value and field or using some other comparisons.
		$this->assertFalse("Implement me!");
	}

	public function test_two_field() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->EQ()->field("two");

		$this->assertFalse($i->interpret($p, array()));
		$this->assertFalse($i->interpret($p, array("one" => 1)));
		$this->assertFalse($i->interpret($p, array("one" => 1, "two" => 2)));
		$this->assertTrue($i->interpret($p, array("one" => 1, "two" => 1)));
		$this->assertTrue($i->interpret($p, array("one" => 2, "two" => 2)));

		// Implement some more variations on the predicate, like using some
		// other comparisons.
		$this->assertFalse("Implement me!");
	}

	public function test_fields() {
		$f = $this->factory;
		$i = $this->interpreter;

		$this->assertEquals(array(), $f->_TRUE()->fields());

		$f1 = $f->field("foo");
		$f2 = $f->field("bar");
		$f3 = $f->field("baz");

		$this->assertEquals(array($f1, $f2), $f1->EQ($f2)->fields());
		$this->assertEquals(array($f1, $f2, $f3), $f1->EQ($f2)->OR($f2->LE($f3))->fields());

		// Implement some more variations
		$this->assertFalse("Implement me!");
	}
}