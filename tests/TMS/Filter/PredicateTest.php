<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

class PredicateTest extends PHPUnit_Framework_TestCase {
	protected $backupGlobals = FALSE;

	protected function setUp() {
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		//include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		//ilUnitUtil::performInitialisation();

		$this->factory = new \ILIAS\TMS\Filter\PredicateFactory();
		$this->interpreter = new \ILIAS\TMS\Filter\DictionaryPredicateInterpreter();
		date_default_timezone_set('Europe/Berlin');
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

		$p = $f->_ALL($true, $true, $false);

		$this->assertFalse($i->interpret($p, array()));
		//$this->assertEquals($p->subs(), array($true, $true, $false));

		$p2 = $f->_ALL($true, $true, $true);

		$this->assertTrue($i->interpret($p2, array()));
		//$this->assertEquals($p->subs(), array($true, $true, $true));

		$p3 = $f->_ALL($false, $false, $false);

		$this->assertFalse($i->interpret($p3, array()));
		//$this->assertEquals($p->subs(), array($false, $false, $false));
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
		//$this->assertEquals($p->subs(), array($true, $false, $false));

		$p2 = $f->_ANY($false, $false, $false);

		$this->assertFalse($i->interpret($p2, array()));
		//$this->assertEquals($p->subs(), array($false, $false, $false));
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
	* @dataProvider seed_provider
	*/
	public function test_int($val,$int,$str,$date) {
		// Check if one could create integer values via $f->int
		// Check if $f->int throws if not passed an integer.
		$f = $this->factory;
		try {
			$res_t = 1;
			$p = $f->int($val);
		} catch (Exception $e) {
			$res_t = 0;
		}
		$this->assertTrue($res_t === $int);
	}
	
	/**
	* @dataProvider seed_provider
	*/
	public function test_string($val,$int,$str,$date) {
		// Similar to test_int
		$f = $this->factory;
		try {
			$res_t = 1;
			$p = $f->str($val);
		} catch (Exception $e) {
			$res_t = 0;
		}
		$this->assertTrue($res_t === $str);
	}

	/**
	* @dataProvider seed_provider
	*/	
	public function test_date($val,$int,$str,$date) {
				// Similar to test_int
		$f = $this->factory;
		try {
			$res_t = 1;
			$p = $f->date($val);
		} catch (Exception $e) {
			$res_t = 0;
		}
		$this->assertTrue($res_t === $date);
	}

	public function seed_provider() {
		date_default_timezone_set('Europe/Berlin');
		return array(
			array(1,1,0,0)
			,array("1",0,1,0)
			,array("a",0,1,0)
			,array("100a",0, 1,0)
			,array(new \DateTime("2016-01-01"),0,0,1)
			,array(null,0,0,0));
	}


	/**
	* @dataProvider eq_int_provider
	*/	
	public function test_EQ_int($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->int($left)->EQ()->int($right);
		$this->assertTrue($i->interpret($res_t, array()) === $res);
	}

	public function eq_int_provider() {
		return array(
			array(1, 1, true)
			,array(2, 1, false)
			,array(1, 2, false));
	}
	
	/**
	* @dataProvider eq_str_provider
	*/
	public function test_EQ_str($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->str($left)->EQ()->str($right);
		$this->assertTrue($i->interpret($res_t, array()) === $res);
	}

	public function eq_str_provider() {
		return array(
			array("aa", "aa", true)
			,array("a", "aa", false)
			,array("ab", "a", false)
			,array("1", "1", true)
			,array("a", "1", false)
			,array("a", "", false)
			,array("2", "1", false));
	}

	/**
	* @dataProvider eq_date_provider
	*/
	public function test_EQ_date($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime($left))->EQ()->date(new \DateTime($right));
		$this->assertTrue($i->interpret($res_t, array()) === $res);
	}

	public function eq_date_provider() {
		return array(
			array("2016-01-01", "2016-01-01", true)
			,array("2016-01-02", "2016-01-01", false)
			,array("2016-01-01", "2016-01-02", false));
	}

	/**
	* @dataProvider eq_int_provider
	*/	
	public function test_NEQ_int($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->int($left)->NEQ()->int($right);
		$this->assertTrue($i->interpret($res_t, array()) === !$res);
	}

	/**
	* @dataProvider eq_str_provider
	*/
	public function test_NEQ_str($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->str($left)->NEQ()->str($right);
		$this->assertTrue($i->interpret($res_t, array()) === !$res);
	}

	/**
	* @dataProvider eq_date_provider
	*/
	public function test_NEQ_date($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime($left))->NEQ()->date(new \DateTime($right));
		$this->assertTrue($i->interpret($res_t, array()) === !$res);
	}

	/**
	* @dataProvider le_int_provider
	*/
	public function test_LE_int($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->int($left)->LE()->int($right);
		$this->assertTrue($i->interpret($res_t, array()) === $res);
	}

	public function le_int_provider() {
		return array(
			array(1, 1, true)
			,array(2, 1, false)
			,array(1, 2, true));
	}

	/**
	* @dataProvider le_str_provider
	*/
	public function test_LE_str($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->str($left)->LE()->str($right);
		$this->assertTrue($i->interpret($res_t, array()) === $res);
	}

	public function le_str_provider() {
		return array(
			array("a", "a", true)
			,array("b",  "a", false)
			,array("1",  "a", true)
			,array("a",  "1", false)
			,array("aa", "a", false)
			,array("a",  "aa", true)
			,array("", "a", true)
			,array(" ",  "a", true)
			,array("a",  "", false));
	}

	/**
	* @dataProvider le_date_provider
	*/
	public function test_LE_date($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime($left))->LE()->date(new \DateTime($right));
		$this->assertTrue($i->interpret($res_t, array()) === $res);
	}

	public function le_date_provider() {
		return array(
			array("2016-01-01", "2016-01-01", true)
			,array("2016-01-02",  "2016-01-01", false)
			,array("2016-01-01",  "2016-01-02", true));
	}


	/**
	* @dataProvider ge_int_provider
	*/
	public function test_LT_int($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->int($left)->LT()->int($right);
		$this->assertTrue($i->interpret($res_t, array()) === !$res);
	}

		/**
	* @dataProvider ge_str_provider
	*/
	public function test_LT_str($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->str($left)->LT()->str($right);
		$this->assertTrue($i->interpret($res_t, array()) === !$res);
	}

	/**
	* @dataProvider ge_date_provider
	*/
	public function test_LT_date($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime($left))->LT()->date(new \DateTime($right));
		$this->assertTrue($i->interpret($res_t, array()) === !$res);
	}

	/**
	* @dataProvider ge_int_provider
	*/
	public function test_GE_int($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->int($left)->GE()->int($right);
		$this->assertTrue($i->interpret($res_t, array()) === $res);
	}
	// We will have to aggree on string - int comparisons:
	// it seems, that depending on typecasting of int one gets different results
	// in orderings between ints and strings. 
	// For instance: '1'<'a', 1>'a' AND 1 = '1' are all TRUE(!?) on my local mysql (5.6.25)}:-[.
	public function ge_int_provider() {
		return array(
			array(1, 1, true)
			,array(2, 1, true)
			,array(1, 2, false));
	}

	/**
	* @dataProvider ge_str_provider
	*/
	public function test_GE_str($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->str($left)->GE()->str($right);
		$this->assertTrue($i->interpret($res_t, array()) === $res);
	}

	public function ge_str_provider() {
		return array(
			array("a", "a", true)
			,array("b", "a",true)
			,array("1", "a",false)
			,array("a", "1",true)
			,array("aa", "a", true)
			,array("a", "aa", false)
			,array("", "a", false)
			,array(" ","a", false)
			,array("a","", true));
	}

	/**
	* @dataProvider ge_date_provider
	*/
	public function test_GE_date($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime($left))->GE()->date(new \DateTime($right));
		$this->assertTrue($i->interpret($res_t, array()) === $res);
	}

	public function ge_date_provider() {
		return array(
			array("2016-01-01", "2016-01-01", true)
			,array("2016-01-02", "2016-01-01", true)
			,array("2016-01-01", "2016-01-02", false));
	}


	/**
	* @dataProvider le_int_provider
	*/
	public function test_GT_int($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->int($left)->GT()->int($right);
		$this->assertTrue($i->interpret($res_t, array()) === !$res);
	}


	/**
	* @dataProvider le_str_provider
	*/
	public function test_GT_str($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->str($left)->GT()->str($right);
		$this->assertTrue($i->interpret($res_t, array()) === !$res);
	}


	/**
	* @dataProvider le_date_provider
	*/
	public function test_GT_date($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime($left))->GT()->date(new \DateTime($right));
		$this->assertTrue($i->interpret($res_t, array()) === !$res);
	}

	/**
	* @dataProvider int_eq_field
	*/
	public function test_int_eq_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->int(1)->EQ()->field("one");

		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider int_eq_field
	*/
	public function test_field_eq_int($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->EQ()->int(1);

		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}


	/**
	* @dataProvider int_eq_field
	*/
	public function test_int_neq_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->_NOT($f->int(1)->EQ()->field("one"));

		$this->assertTrue($i->interpret($res_t, $field) === !$res);
	}
	
	/**
	* @dataProvider int_eq_field
	*/
	public function test_field_neq_int($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->_NOT($f->field("one")->EQ()->int(1));

		$this->assertTrue($i->interpret($res_t, $field) === !$res);
	}

	public function int_eq_field() {
		return array(
			array( array("one" => 1) , true)
			,array( array("two" => "a", "one" => 1) , true)
			,array( array("two" => "a", "one" => 2) , false));
	}

	/**
	* @dataProvider str_eq_field
	*/
	public function test_str_eq_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->str("a")->EQ()->field("one");

		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider str_eq_field
	*/
	public function test_field_eq_str($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->EQ()->str("a");

		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider str_eq_field
	*/
	public function test_str_neq_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->_NOT($f->str("a")->EQ()->field("one"));

		$this->assertTrue($i->interpret($res_t, $field) === !$res);
	}
	
	/**
	* @dataProvider str_eq_field
	*/
	public function test_field_neq_str($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->EQ()->str("a")->_NOT();

		$this->assertTrue($i->interpret($res_t, $field) === !$res);
	}

	public function str_eq_field() {
		return array(
			array(array("one" => "a") , true)
			,array(array("two" => "a", "one" => "1") , false)
			,array(array("two" => "", "one" => "a") , true));
	}


	/**
	* @dataProvider date_eq_field
	*/
	public function test_date_eq_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime("2016-01-01"))->EQ()->field("one");

		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider date_eq_field
	*/
	public function test_field_eq_date($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->EQ()->date(new \DateTime("2016-01-01"));

		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider date_eq_field
	*/
	public function test_date_neq_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime("2016-01-01"))->EQ()->field("one")->_NOT();

		$this->assertTrue($i->interpret($res_t, $field) === !$res);
	}
	
	/**
	* @dataProvider date_eq_field
	*/
	public function test_field_neq_date($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->EQ()->date(new \DateTime("2016-01-01"))->_NOT();

		$this->assertTrue($i->interpret($res_t, $field) === !$res);
	}

	public function date_eq_field() {
		date_default_timezone_set('Europe/Berlin');
		return array(
			array( array("one" => new \DateTime("2016-01-01")) ,true)
			,array( array("two" => new \DateTime("2016-01-02"), "one" => new \DateTime("2016-01-01")) , true)
			,array( array("two" => new \DateTime("2016-01-03"), "one" => new \DateTime("2016-01-02")) , false));
	}


	/**
	* @dataProvider int_ge_field
	*/
	public function test_int_ge_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_r = $f->int(1)->GE()->field("one");
		$res = isset($field["one"]) ? $res : false;
		$this->assertTrue($i->interpret($res_r, $field) === $res);
	}

	/**
	* @dataProvider int_ge_field
	*/
	public function test_field_le_int($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->LE()->int(1);
		$res = isset($field["one"]) ? $res : false;
		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}


	/**
	* @dataProvider int_ge_field
	*/
	public function test_int_lt_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->int(1)->LT()->field("one");

		$res = isset($field["one"]) ? !$res : false;

		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}
	
	/**
	* @dataProvider int_ge_field
	*/
	public function test_field_gt_int($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->GT()->int(1);
		$res = isset($field["one"]) ? !$res : false;
		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	public function int_ge_field() {
		return array(
			array( array("one" => 1) , true)
			,array( array("two" => "a", "one" => 0) , true)
			,array( array("two" => "a", "one" => 2) ,false)
			,array( array("two" => "a", "one" => 1) , true));
	}

	/**
	* @dataProvider str_ge_field
	*/
	public function test_str_ge_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->str("b")->GE()->field("one");
		$res = isset($field["one"]) ? $res : false;
		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider str_ge_field
	*/
	public function test_field_le_str($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->LE()->str("b");
		$res = isset($field["one"]) ? $res : false;
		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider str_ge_field
	*/
	public function test_str_lt_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->str("b")->LT()->field("one");
		$res = isset($field["one"]) ? !$res : false;
		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}
	
	public function test_ex_two_vars() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->str("1")->GT()->int(1);

		try {
			$i->interpret($p, array("one" => 1,"two" => "1"));
			$this->assertFalse("should have thrown Exception");
		}
		catch (\InvalidArgumentException $ex) {
		}

	}


	/**
	* @dataProvider str_ge_field
	*/
	public function test_field_gt_str($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->GT()->str("b");
		$res = isset($field["one"]) ? !$res : false;
		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	public function str_ge_field() {
		return array(
			array( array("one" => "b") , true)
			,array( array("two" => "c", "one" => "a") , true)
			,array( array("two" => "a", "one" => "1") , true)
			,array( array("two" => "", "one" => "") , true)
			,array( array("two" => "", "one" => "b") , true));
	}


	/**
	* @dataProvider date_ge_field
	*/
	public function x_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime("2016-01-01"))->GE()->field("one");
		$res = isset($field["one"]) ? $res : false;
		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider date_ge_field
	*/
	public function test_field_le_date($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->LE()->date(new \DateTime("2016-01-01"));
		$res = isset($field["one"]) ? $res : false;
		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider date_ge_field
	*/
	public function test_date_lt_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime("2016-01-01"))->LT()->field("one");
		$res = isset($field["one"]) ? !$res : false;
		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}
	
	/**
	* @dataProvider date_ge_field
	*/
	public function test_field_gt_date($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->GT()->date(new \DateTime("2016-01-01"));
		$res = isset($field["one"]) ? !$res : false;
		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	public function date_ge_field() {
		date_default_timezone_set('Europe/Berlin');
		return array(
			array(array("one" => new \DateTime("2016-01-02")) , false)
			,array(array("two" => new \DateTime("2016-01-02"), "one" => new \DateTime("2016-01-01")) , true)
			,array(array("two" => new \DateTime("2016-01-02"), "one" => new \DateTime("2015-12-31")) , true)
			,array(array("two" => new \DateTime("2016-01-03"), "one" => new \DateTime("2016-01-03")) ,false)
			,array(array("two" => new \DateTime("2016-01-03"), "one" => new \DateTime("2016-01-02")) ,false));
	}

	/**
	 * @dataProvider eq_two_fields
	*/
	public function test_eq_two_field($fields,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->EQ()->field("two");
		$res = isset($fields["one"]) &&  isset($fields["two"]) ? $res : false;
		$this->assertTrue($i->interpret($p, $fields) === $res);
	}


	/**
	 * @dataProvider eq_two_fields
	*/
	public function test_neq_two_field($fields,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->EQ()->field("two")->_NOT();
		$res = isset($fields["one"]) &&  isset($fields["two"]) ? !$res : true;
		$this->assertTrue($i->interpret($p, $fields) === $res);
	}

	public function eq_two_fields() {
		date_default_timezone_set('Europe/Berlin');		
		return array(
			array(array("one" => 1, "two" => 2)
				,  false)
			,array( array("one" => 1, "two" => 1)
				,  true)
			,array( array("one" => "1", "two" => "1")
				, true)
			,array( array("one" => "a", "two" => "1")
				,  false)
			,array( array("one" => "a", "two" => "a")
				, true)
			,array( array("one" => "a", "two" => "a")
				,  true)	
			,array( array("one" => new \DateTime('2016-01-01'), "two" => new \DateTime('2016-01-01'))
				,  true)
			,array( array("one" => new \DateTime('2016-01-01'), "two" => new \DateTime('2016-01-02'))
				,  false)
			);
	}

	/**
	 * @dataProvider ge_two_fields
	*/
	public function test_ge_two_field($fields,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->GE()->field("two");
		$res = isset($fields["one"]) &&  isset($fields["two"]) ? $res : false;
		$this->assertTrue($i->interpret($p, $fields) === $res);
	}

	/**
	 * @dataProvider ge_two_fields
	*/
	public function test_lt_two_field($fields,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->LT()->field("two");
		$res = isset($fields["one"]) &&  isset($fields["two"]) ? !$res : false;
		$this->assertTrue($i->interpret($p, $fields) === $res);
	}

	/**
	 * @dataProvider ge_two_fields
	*/
	public function test_le_two_field($fields,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("two")->LE()->field("one");
		$res = isset($fields["one"]) &&  isset($fields["two"]) ? $res : false;
		$this->assertTrue($i->interpret($p, $fields) === $res);
	}

	/**
	 * @dataProvider ge_two_fields
	*/
	public function test_gt_two_field($fields,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("two")->GT()->field("one");
		$res = isset($fields["one"]) &&  isset($fields["two"]) ? !$res : false;
		$this->assertTrue($i->interpret($p, $fields) === $res);
	}

	public function test_ex_two_field() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("two")->GT()->field("one");

		try {
			$i->interpret($p, array("one" => 1,"two" => "1"));
			$this->assertFalse("should have thrown Exception");
		}
		catch (\InvalidArgumentException $ex) {
		}

		try {
			$i->interpret($p, array("one" => 1));
			$this->assertFalse("should have thrown Exception");
		}
		catch (\InvalidArgumentException $ex) {
		}

	}

	public function ge_two_fields() {
		return array(
			array(array("one" => 1, "two" => 2)	, false)
			,array(array("one" => 1, "two" => 1), true)
			,array(array("one" => 2, "two" => 1),true)
			,array(array("one" => "a", "two" => "a"), true)
			,array(array("one" => "a", "two" => "a"),  true)	
			,array(array("one" => new \DateTime('2016-01-01'), "two" => new \DateTime('2016-01-01'))
				, true)
			,array(array("one" => new \DateTime('2016-01-01'), "two" => new \DateTime('2016-01-02'))
				, false)
			,array(array("one" => new \DateTime('2016-01-02'), "two" => new \DateTime('2016-01-01'))
				, true)
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
		$this->assertEquals(array($f1, $f2, $f3), $f1->EQ($f2)->_OR($f2->LE($f3))->fields());
		$fa = $f->field('a');
		$this->assertTrue($this->fieldListsEqual($fa->IN($f->list_field_by_array(array($f1,$f2,$f3)))->fields(),array($fa,$f1,$f2,$f3)));
		$st = $f->str('a');
		$this->assertTrue($this->fieldListsEqual($st->IN($f->list_field_by_array(array($f1,$f2,$f3)))->fields(),array($f1,$f2,$f3)));
		//just to check fieldListEqual
		$this->assertFalse($this->fieldListsEqual($st->IN($f->list_field_by_array(array($f1,$f2,$f3)))->fields(),array($fa,$f1,$f2,$f3)));
	}
	

	protected function fieldListsEqual($list1,$list2) {
		$field_ids1 = array_map(function($field) {return $field->name();},$list1);
		$field_ids2 = array_map(function($field) {return $field->name();},$list2);
		return count(array_intersect($field_ids1, $field_ids2)) === count($list1) && count(array_intersect($field_ids1, $field_ids2)) === count($list2);
	}

	public function test_IN() {
		$f = $this->factory;
		$i = $this->interpreter;

		$int_list = $f->list_int(1,2,3);
		$int_list_v = $f->list_int();

		$str_list = $f->list_str("a","b","c");
		$str_list_v = $f->list_str();
		
		$int_1 = $f->int(1);
		$int_2 = $f->int(5);

		$str_1 = $f->str("a");
		$str_2 = $f->str("z");

		$field_1 = $f->field("foo");

		$this->assertTrue($i->interpret($int_1->IN($int_list),array()));
		$this->assertFalse($i->interpret($int_1->IN($int_list_v),array()));
		$this->assertFalse($i->interpret($int_2->IN($int_list),array()));

		$this->assertTrue($i->interpret($str_1->IN($str_list),array()));
		$this->assertFalse($i->interpret($str_1->IN($str_list_v),array()));
		$this->assertFalse($i->interpret($str_2->IN($str_list),array()));

		$this->assertTrue($i->interpret($field_1->IN($int_list),array("foo" => 1)));
		$this->assertTrue($i->interpret($field_1->IN($str_list),array("foo" => "a")));

		try{
			$i->interpret($field_1->IN($int_list),array("bar" => "foo"));
			$this->assertFalse("should have thrown");
		} catch (\InvalidArgumentException $ex) {
		}

		try{
			$this->assertFalse($i->interpret($str_1->IN($int_list),array()));
			$this->assertFalse("should have thrown");
		} catch (\InvalidArgumentException $ex) {}

		try{
			$this->assertFalse($i->interpret($int_1->IN($str_list),array()));
			$this->assertFalse("should have thrown");
		} catch (\InvalidArgumentException $ex) {}
	}

	/**
	* not needed atm
	*/
	public function test_LIKE() {
		$this->assertTrue(true);
	}


	public function test_ValueList() {
		$ls = $this->factory->list_int(1,2,3,4);
		$this->assertInstanceOf("\\ILIAS\\TMS\\Filter\\Predicates\\ValueList", $ls);
		$vals = array_map(function (\ILIAS\TMS\Filter\Predicates\ValueInt $v) {
					return $v->value();
				}, $ls->values());

		$ls = $this->factory->list_str("one","two","three");
		$this->assertInstanceOf("\\ILIAS\\TMS\\Filter\\Predicates\\ValueList", $ls);
		$vals = array_map(function (\ILIAS\TMS\Filter\Predicates\ValueStr $v) {
					return $v->value();
				}, $ls->values());
		$this->assertEquals(array("one","two","three"), $vals);

		try {
			$ls = $this->factory->list_int(1,"one");
			$this->assertFalse("should have thrown Exception");
		}
		catch (\InvalidArgumentException $ex) {
		}

		try {
			$ls = $this->factory->list_str(1,"one");
			$this->assertFalse("should have thrown Exception");
		}
		catch (\InvalidArgumentException $ex) {
		}

	}

	public function test_IsNull() {
		$f = $this->factory;
		$i = $this->interpreter;

		$res = $f->int(1)->IS_NULL();
		$this->assertFalse($i->interpret($res,array()));
		$res = $f->str("a")->IS_NULL();
		$this->assertFalse($i->interpret($res,array()));
		$res = $f->date(new \DateTime("2016-01-01"))->IS_NULL();
		$this->assertFalse($i->interpret($res,array()));

		$res = $f->field("foo")->IS_NULL();
		$this->assertFalse($i->interpret($res,array("foo" => "2")));
		$this->assertTrue($i->interpret($res,array("foo" => null)));

		try {
			$i->interpret($res,array("bar" => null));
			$this->assertFalse("should have thrown Exception");
		}
		catch (\InvalidArgumentException $ex) {
		}
	}
}
