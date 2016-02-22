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
		// $f->_NOT($f->TRUE());

		$this->assertFalse($i->interpret($p, array()));
	}

	/**
	* @dataProvider integer_seed_provider
	*/
	public function test_int($data) {
		// Check if one could create integer values via $f->int
		// Check if $f->int throws if not passed an integer.
		$f = $this->factory;
		try {
			$res = 1;
			$p = $f->int($data["val"]);
		} catch (Exception $e) {
			$res = 0;
		}
		$this->assertEquals($res,$data["res"]["int"]);
	}
	
	/**
	* @dataProvider string_seed_provider
	*/
	public function test_string($data) {
		// Similar to test_int
		$f = $this->factory;
		try {
			$res = 1;
			$p = $f->str($data["val"]);
		} catch (Exception $e) {
			$res = 0;
		}
		$this->assertEquals($res,$data["res"]["str"]);
	}

	/**
	* @dataProvider seed_provider
	*/	
	public function test_date($data) {
				// Similar to test_int
		$f = $this->factory;
		try {
			$res = 1;
			$p = $f->date($data["val"]);
		} catch (Exception $e) {
			$res = 0;
		}
		$this->assertEquals($res,$data["res"]["date"]);
	}

	public function seed_provider() {
		$date = new \DateTime("2016-01-01");
		return array(
			array(1,
				array("int" => 1,"str" => 0,"date" => 0))
			,array("1",
				array("int" => 1,"str" => 1,"date" => 0))
			,array("a",
				array("int" => 0,"str" => 1,"date" => 0))
			,array("100a",
				array("int" => 0,"str" => 1,"date" => 0))
			,array($date,
				array("int" => 0,"str" => 0,"date" => 1))
			,array(null,
				array("int" => 0,"str" => 0,"date" => 0)));
	}


	/**
	* @dataProvider eq_int_provider
	*/	
	public function test_EQ_int($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->int($data["left"])->EQ()->int($data["right"]);
		$this->assertTrue($i->interpret($res, array()) === $data["res"]);
	}

	public function eq_int_provider() {
		return array(
			array("left" => 1, "right" => 1, "res" => true)
			,array("left" => 2, "right" => 1, "res" => false)
			,array("left" => 1, "right" => 2, "res" => false)
			,array("left" => "2", "right" => 1, "res" => false)
			,array("left" => "2", "right" => "1", "res" => false)
			,array("left" => 2, "right" => "2", "res" => true)
			,array("left" => "0", "right" => 0, "res" => true));
	}
	
	/**
	* @dataProvider eq_str_provider
	*/
	public function test_EQ_str($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->str($data["left"])->EQ()->str($data["right"]);
		$this->assertTrue($i->interpret($res, array()) === $data["res"]);
	}

	public function eq_str_provider() {
		return array(
			array("left" => "aa", "right" => "aa", "res" => true)
			,array("left" => "a", "right" => "aa", "res" => false)
			,array("left" => "ab", "right" => "a", "res" => false)
			,array("left" => "1", "right" => "1", "res" => true)
			,array("left" => "a", "right" => "1", "res" => false)
			,array("left" => "a", "right" => "", "res" => false)
			,array("left" => "2", "right" => "1", "res" => true));
	}

	/**
	* @dataProvider eq_date_provider
	*/
	public function test_EQ_date($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->date(new \DateTime($data["left"]))->EQ()->date(new \DateTime($data["right"]));
		$this->assertTrue($i->interpret($res, array()) === $data["res"]);
	}

	public function eq_date_provider() {
		return array(
			array("left" => "2016-01-01", "right" => "2016-01-01", "res" => true)
			,array("left" => "2016-01-02", "right" => "2016-01-01", "res" => false)
			,array("left" => "2016-01-01", "right" => "2016-01-02", "res" => false));
	}

	/**
	* @dataProvider eq_int_provider
	*/	
	public function test_NEQ_int($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->int($data["left"])->EQ()->int($data["right"])->_NOT();
		$this->assertTrue($i->interpret($res, array()) === !$data["res"]);
	}

	/**
	* @dataProvider eq_str_provider
	*/
	public function test_NEQ_str($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->str($data["left"])->NEQ()->str($data["right"])->_NOT();
		$this->assertTrue($i->interpret($res, array()) === !$data["res"]);
	}

	/**
	* @dataProvider eq_date_provider
	*/
	public function test_NEQ_date($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->date(new \DateTime($data["left"]))->NEQ()->date(new \DateTime($data["right"]))->_NOT();
		$this->assertTrue($i->interpret($res, array()) === !$data["res"]);
	}

	/**
	* @dataProvider le_int_provider
	*/
	public function test_LE_int($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->int($data["left"])->LE()->int($data["right"]);
		$this->assertTrue($i->interpret($res, array()) === $data["res"]);
	}

	public function le_int_provider() {
		return array(
			array("left" => 1, "right" => 1, "res" => true)
			,array("left" => 2, "right" => 1, "res" => false)
			,array("left" => 1, "right" => 2, "res" => true)
			,array("left" => "2", "right" => 1, "res" => false)
			,array("left" => "2", "right" => "1", "res" => false)
			,array("left" => "1", "right" => "2", "res" => true)
			,array("left" => 2, "right" => "2", "res" => true)
			,array("left" => "0", "right" => 0, "res" => true));
	}

	/**
	* @dataProvider le_str_provider
	*/
	public function test_LE_str($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->str($data["left"])->LE()->str($data["right"]);
		$this->assertEquals($i->interpret($res, array()) === $data["res"]);
	}

	public function le_str_provider() {
		return array(
			array("left" => "a", "right" => "a", "res" => true)
			,array("left" => "b", "right" => "a", "res" => false)
			,array("left" => "1", "right" => "a", "res" => false)
			,array("left" => "a", "right" => "1", "res" => true)
			,array("left" => "aa", "right" => "a", "res" => false)
			,array("left" => "a", "right" => "aa", "res" => true)
			,array("left" => "", "right" => "a", "res" => true)
			,array("left" => " ", "right" => "a", "res" => true)
			,array("left" => "a", "right" => "", "res" => false));
	}

	/**
	* @dataProvider le_date_provider
	*/
	public function test_LE_date($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->date(new \DateTime($data["left"]))->LE()->date(new \DateTime($data["right"]));
		$this->assertTrue($i->interpret($res, array()) === $data["res"]);
	}

	public function le_date_provider() {
		return array(
			array("left" => "2016-01-01", "right" => "2016-01-01", "res" => true)
			,array("left" =>  "2016-01-02", "right" =>  "2016-01-01", "res" => false)
			,array("left" =>  "2016-01-01", "right" =>  "2016-01-02", "res" => true));
	}


	/**
	* @dataProvider ge_date_provider
	*/
	public function test_LT_int($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->int($data["left"])->LT()->int($data["right"]);
		$this->assertTrue($i->interpret($res, array()) === !$data["res"]);
	}

		/**
	* @dataProvider ge_str_provider
	*/
	public function test_LT_str($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->str($data["left"])->LT()->str($data["right"]);
		$this->assertEquals($i->interpret($res, array()) === !$data["res"]);
	}

	/**
	* @dataProvider ge_date_provider
	*/
	public function test_LT_date($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->date(new \DateTime($data["left"]))->LT()->date(new \DateTime($data["right"]));
		$this->assertTrue($i->interpret($res, array()) === !$data["res"]);
	}

	/**
	* @dataProvider ge_date_provider
	*/
	public function test_GE_int($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->int($data["left"])->GE()->int($data["right"]);
		$this->assertTrue($i->interpret($res, array()) === $data["res"]);
	}
	// We will have to aggree on string - int comparisons:
	// it seems, that depending on typecasting of int one gets different results
	// in orderings between ints and strings. 
	// For instance: '1'<'a', 1>'a' AND 1 = '1' are all TRUE(!?) on my local mysql (5.6.25)}:-[.
	public function ge_int_provider() {
		return array(
			array("left" => 1, "right" => 1, "res" => true)
			,array("left" => 2, "right" => 1, "res" => true)
			,array("left" => 1, "right" => 2, "res" => false)
			,array("left" => "2", "right" => 1, "res" => true)
			,array("left" => "2", "right" => "1", "res" => true)
			,array("left" => "1", "right" => "2", "res" => false)
			,array("left" => 2, "right" => "2", "res" => true)
			,array("left" => "0", "right" => 0, "res" => true));
	}

	/**
	* @dataProvider ge_str_provider
	*/
	public function test_GE_str($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->str($data["left"])->GE()->str($data["right"]);
		$this->assertEquals($i->interpret($res, array()) === $data["res"]);
	}

	public function ge_str_provider() {
		return array(
			array("left" => "a", "right" => "a", "res" => true)
			,array("left" => "b", "right" => "a", "res" => true)
			,array("left" => "1", "right" => "a", "res" => false)
			,array("left" => "a", "right" => "1", "res" => true)
			,array("left" => "aa", "right" => "a", "res" => true)
			,array("left" => "a", "right" => "aa", "res" => false)
			,array("left" => "", "right" => "a", "res" => false)
			,array("left" => " ", "right" => "a", "res" => false)
			,array("left" => "a", "right" => "", "res" => true));
	}

	/**
	* @dataProvider ge_date_provider
	*/
	public function test_GE_date($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->date(new \DateTime($data["left"]))->GE()->date(new \DateTime($data["right"]));
		$this->assertTrue($i->interpret($res, array()) === $data["res"]);
	}

	public function ge_date_provider() {
		return array(
			array("left" => "2016-01-01", "right" => "2016-01-01", "res" => true)
			,array("left" =>  "2016-01-02", "right" =>  "2016-01-01", "res" => true)
			,array("left" =>  "2016-01-01", "right" =>  "2016-01-02", "res" => false));
	}


	/**
	* @dataProvider le_int_provider
	*/
	public function test_GT_int($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->int($data["left"])->GT()->int($data["right"]);
		$this->assertTrue($i->interpret($res, array()) === !$data["res"]);
	}


	/**
	* @dataProvider le_str_provider
	*/
	public function test_GT_str($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->str($data["left"])->GT()->str($data["right"]);
		$this->assertEquals($i->interpret($res, array()) === !$data["res"]);
	}


	/**
	* @dataProvider le_date_provider
	*/
	public function test_GT_date($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->date(new \DateTime($data["left"]))->GT()->date(new \DateTime($data["right"]));
		$this->assertTrue($i->interpret($res, array()) === !$data["res"]);
	}

	/**
	* @dataProvider int_eq_field
	*/
	public function test_int_eq_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->int(1)->EQ()->field("one");

		$this->assertTrue($i->interpret($res, $data["field"]) === $data["res"]);
	}

	/**
	* @dataProvider int_eq_field
	*/
	public function test_field_eq_int($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->field("one")->EQ()->int(1);

		$this->assertTrue($i->interpret($res, $data["field"]) === $data["res"]);
	}


	/**
	* @dataProvider int_eq_field
	*/
	public function test_int_neq_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->_NOT($f->int(1)->EQ()->field("one"));

		$this->assertTrue($i->interpret($res, $data["field"]) === !$data["res"]);
	}
	
	/**
	* @dataProvider int_eq_field
	*/
	public function test_field_neq_int($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->_NOT($f->field("one")->EQ()->int(1));

		$this->assertTrue($i->interpret($res, $data["field"]) === !$data["res"]);
	}

	public function int_eq_field() {
		return array(
			array("field" => array() ,"res" => false)
			,array("field" => array("one" => 1) ,"res" => true)
			,array("field" => array("two" => "a", "one" => 1) ,"res" => true)
			,array("field" => array("two" => "a", "one" => "1") ,"res" => true)
			,array("field" => array("two" => 1) ,"res" => false));
	}

	/**
	* @dataProvider str_eq_field
	*/
	public function test_str_eq_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->str("a")->EQ()->field("one");

		$this->assertTrue($i->interpret($res, $data["field"]) === $data["res"]);
	}

	/**
	* @dataProvider str_eq_field
	*/
	public function test_field_eq_str($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->field("one")->EQ()->str("a");

		$this->assertTrue($i->interpret($res, $data["field"]) === $data["res"]);
	}

	/**
	* @dataProvider str_eq_field
	*/
	public function test_str_neq_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->_NOT($f->str("a")->EQ()->field("one"));

		$this->assertTrue($i->interpret($res, $data["field"]) === !$data["res"]);
	}
	
	/**
	* @dataProvider str_eq_field
	*/
	public function test_field_neq_str($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->field("one")->EQ()->str("a")->_NOT();

		$this->assertTrue($i->interpret($res, $data["field"]) === !$data["res"]);
	}

	public function str_eq_field() {
		return array(
			array("field" => array() ,"res" => false)
			,array("field" => array("one" => "a") ,"res" => true)
			,array("field" => array("two" => "a", "one" => "1") ,"res" => false)
			,array("field" => array("two" => "", "one" => "a") ,"res" => true)
			,array("field" => array("two" => "a") ,"res" => false));
	}


	/**
	* @dataProvider date_eq_field
	*/
	public function test_date_eq_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->date(new \DateTime("2016-01-01"))->EQ()->field("one");

		$this->assertTrue($i->interpret($res, $data["field"]) === $data["res"]);
	}

	/**
	* @dataProvider date_eq_field
	*/
	public function test_field_eq_date($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->field("one")->EQ()->date(new \DateTime("2016-01-01"));

		$this->assertTrue($i->interpret($res, $data["field"]) === $data["res"]);
	}

	/**
	* @dataProvider date_eq_field
	*/
	public function test_date_neq_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->date(new \DateTime("2016-01-01"))->EQ()->field("one")->_NOT();

		$this->assertTrue($i->interpret($res, $data["field"]) === !$data["res"]);
	}
	
	/**
	* @dataProvider date_eq_field
	*/
	public function test_field_neq_date($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->field("one")->EQ()->date(new \DateTime("2016-01-01"))->_NOT();

		$this->assertTrue($i->interpret($res, $data["field"]) === !$data["res"]);
	}

	public function date_eq_field() {
		return array(
			array("field" => array() ,"res" => false)
			,array("field" => array("one" => new \DateTime("2016-01-01")) ,"res" => true)
			,array("field" => array("two" => new \DateTime("2016-01-02"), "one" => new \DateTime("2016-01-01")) ,"res" => true)
			,array("field" => array("two" => new \DateTime("2016-01-03"), "one" => new \DateTime("2016-01-02")) ,"res" => false)
			,array("field" => array("two" => new \DateTime("2016-01-01")) ,"res" => false));
	}


	/**
	* @dataProvider int_ge_field
	*/
	public function test_int_ge_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->int(1)->GE()->field("one");

		$this->assertTrue($i->interpret($res, $data["field"]) === $data["res"]);
	}

	/**
	* @dataProvider int_ge_field
	*/
	public function test_field_le_int($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->field("one")->LE()->int(1);

		$this->assertTrue($i->interpret($res, $data["field"]) === $data["res"]);
	}


	/**
	* @dataProvider int_ge_field
	*/
	public function test_int_lt_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->int(1)->LT()->field("one")->_NOT();

		$this->assertTrue($i->interpret($res, $data["field"]) === !$data["res"]);
	}
	
	/**
	* @dataProvider int_ge_field
	*/
	public function test_field_gt_int($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->field("one")->GT()->int(1)->_NOT();

		$this->assertTrue($i->interpret($res, $data["field"]) === !$data["res"]);
	}

	public function int_ge_field() {
		return array(
			array("field" => array() ,"res" => false)
			,array("field" => array("one" => 1) ,"res" => true)
			,array("field" => array("two" => "a", "one" => 0) ,"res" => true)
			,array("field" => array("two" => "a", "one" => 2) ,"res" => false)
			,array("field" => array("two" => "a", "one" => 1) ,"res" => true)
			,array("field" => array("two" => 1) ,"res" => false)
			,array("field" => array("two" => 0) ,"res" => false));
	}

	/**
	* @dataProvider str_ge_field
	*/
	public function test_str_ge_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->str("b")->GE()->field("one");

		$this->assertTrue($i->interpret($res, $data["field"]) === $data["res"]);
	}

	/**
	* @dataProvider str_ge_field
	*/
	public function test_field_le_str($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->field("one")->LE()->str("b");

		$this->assertTrue($i->interpret($res, $data["field"]) === $data["res"]);
	}

	/**
	* @dataProvider str_ge_field
	*/
	public function test_str_lt_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->str("b")->LT()->field("one")->_NOT();

		$this->assertTrue($i->interpret($res, $data["field"]) === !$data["res"]);
	}
	
	/**
	* @dataProvider str_ge_field
	*/
	public function test_field_gt_str($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->field("one")->GT()->str("b")->_NOT();

		$this->assertTrue($i->interpret($res, $data["field"]) === !$data["res"]);
	}

	public function str_ge_field() {
		return array(
			array("field" => array() ,"res" => false)
			,array("field" => array("one" => "b") ,"res" => true)
			,array("field" => array("two" => "c", "one" => "a") ,"res" => true)
			,array("field" => array("two" => "a", "one" => "1") ,"res" => true)
			,array("field" => array("two" => "", "one" => "") ,"res" => true)
			,array("field" => array("two" => "", "one" => "b") ,"res" => true)
			,array("field" => array("two" => "a") ,"res" => false));
	}


	/**
	* @dataProvider date_ge_field
	*/
	public function test_date_ge_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->date(new \DateTime("2016-01-01"))->GE()->field("one");

		$this->assertTrue($i->interpret($res, $data["field"]) === $data["res"]);
	}

	/**
	* @dataProvider date_ge_field
	*/
	public function test_field_le_date($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->field("one")->LE()->date(new \DateTime("2016-01-01"));

		$this->assertTrue($i->interpret($res, $data["field"]) === $data["res"]);
	}

	/**
	* @dataProvider date_ge_field
	*/
	public function test_date_lt_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->date(new \DateTime("2016-01-01"))->LT()->field("one")->_NOT();

		$this->assertTrue($i->interpret($res, $data["field"]) === !$data["res"]);
	}
	
	/**
	* @dataProvider date_ge_field
	*/
	public function test_field_gt_date($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->field("one")->GT()->date(new \DateTime("2016-01-02"))->_NOT();

		$this->assertTrue($i->interpret($res, $data["field"]) === !$data["res"]);
	}

	public function date_ge_field() {
		return array(
			array("field" => array() ,"res" => false)
			,array("field" => array("one" => new \DateTime("2016-01-02")) ,"res" => true)
			,array("field" => array("two" => new \DateTime("2016-01-02"), "one" => new \DateTime("2016-01-01")) ,"res" => true)
			,array("field" => array("two" => new \DateTime("2016-01-03"), "one" => new \DateTime("2016-01-03")) ,"res" => false)
			,array("field" => array("two" => new \DateTime("2016-01-03"), "one" => new \DateTime("2016-01-02")) ,"res" => false)
			,array("field" => array("two" => new \DateTime("2016-01-01")) ,"res" => false));
	}

	/**
	 * @dataProvider eq_two_fields
	*/
	public function test_eq_two_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->EQ()->field("two");

		$this->assertTrue($i->interpret($p, $data["fields"]) === $data["res"]);
	}


	/**
	 * @dataProvider eq_two_fields
	*/
	public function test_neq_two_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->EQ()->field("two")->_NOT();

		$this->assertTrue($i->interpret($p, $data["fields"]) !== $data["res"]);
	}

	public function eq_two_fields() {
		return array(
			array("fields" => array("one" => 1, "two" => 2)
				, "res" => false)
			,array("fields" => array("two" => 2)
				, "res" => false)
			,array("fields" => array("one" => 1, "two" => 1)
				, "res" => true)
			,array("fields" => array("one" => "1", "two" => 1)
				, "res" => true)
			,array("fields" => array("one" => "a", "two" => 1)
				, "res" => false)
			,array("fields" => array("one" => "a", "two" => "a")
				, "res" => true)
			,array("fields" => array("one" => "a")
				, "res" => false)
			,array("fields" => array("one" => "a", "two" => "a")
				, "res" => true)	
			,array("fields" => array("one" => new \DateTime('2016-01-01'), "two" => new \DateTime('2016-01-01'))
				, "res" => false)
			,array("fields" => array("one" => new \DateTime('2016-01-01'))
				, "res" => false)
			,array("fields" => array("one" => new \DateTime('2016-01-01'), "two" => new \DateTime('2016-01-02'))
				, "res" => false)
			);
	}

	/**
	 * @dataProvider ge_two_fields
	*/
	public function test_ge_two_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->GE()->field("two");

		$this->assertTrue($i->interpret($p, $data["fields"]) === $data["res"]);
	}

	/**
	 * @dataProvider ge_two_fields
	*/
	public function test_lt_two_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->LT()->field("two");

		$this->assertTrue($i->interpret($p, $data["fields"]) === !$data["res"]);
	}

	/**
	 * @dataProvider ge_two_fields
	*/
	public function test_le_two_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("two")->LE()->field("one");

		$this->assertTrue($i->interpret($p, $data["fields"]) === $data["res"]);
	}

	/**
	 * @dataProvider ge_two_fields
	*/
	public function test_gt_two_field($data) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("two")->GT()->field("one");

		$this->assertTrue($i->interpret($p, $data["fields"]) === !$data["res"]);
	}




	public function ge_two_fields() {
		return array(
			array("fields" => array("one" => 1, "two" => 2)
				, "res" => false)
			,array("fields" => array("two" => 2)
				, "res" => false)
			,array("fields" => array("one" => 1, "two" => 1)
				, "res" => true)
			,array("fields" => array("one" => 2, "two" => 1)
				, "res" => true)
			,array("fields" => array("one" => "1", "two" => 1)
				, "res" => true)
			,array("fields" => array("one" => "a", "two" => 1)
				, "res" => false)
			,array("fields" => array("one" => "a", "two" => "a")
				, "res" => true)
			,array("fields" => array("one" => "a")
				, "res" => false)
			,array("fields" => array("one" => "a", "two" => "a")
				, "res" => true)	
			,array("fields" => array("one" => new \DateTime('2016-01-01'), "two" => new \DateTime('2016-01-01'))
				, "res" => false)
			,array("fields" => array("one" => new \DateTime('2016-01-01'), "two" => "a")
				, "res" => false)
			,array("fields" => array("one" => new \DateTime('2016-01-01'), "two" => new \DateTime('2016-01-02'))
				, "res" => false)
			);
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
	}
}