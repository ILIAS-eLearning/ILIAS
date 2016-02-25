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
	public function test_int($val,$res) {
		// Check if one could create integer values via $f->int
		// Check if $f->int throws if not passed an integer.
		$f = $this->factory;
		try {
			$res_t = 1;
			$p = $f->int($val);
		} catch (Exception $e) {
			$res_t = 0;
		}
		$this->assertEquals($res_t,$res["int"]);
	}
	
	/**
	* @dataProvider seed_provider
	*/
	public function test_string($val,$res) {
		// Similar to test_int
		$f = $this->factory;
		try {
			$res_t = 1;
			$p = $f->str($val);
		} catch (Exception $e) {
			$res_t = 0;
		}
		$this->assertEquals($res_t,$res["str"]);
	}

	/**
	* @dataProvider seed_provider
	*/	
	public function test_date($val,$res) {
				// Similar to test_int
		$f = $this->factory;
		try {
			$res_t = 1;
			$p = $f->date($val);
		} catch (Exception $e) {
			$res_t = 0;
		}
		$this->assertEquals($res_t,$res["date"]);
	}

	public function seed_provider() {
		return array(
			array(1,
				array("int" => 1,"str" => 0,"date" => 0))
			,array("1",
				array("int" => 1,"str" => 1,"date" => 0))
			,array("a",
				array("int" => 0,"str" => 1,"date" => 0))
			,array("100a",
				array("int" => 0,"str" => 1,"date" => 0))
			,array(new \DateTime("2016-01-01"),
				array("int" => 0,"str" => 0,"date" => 1))
			,array(null,
				array("int" => 0,"str" => 0,"date" => 0)));
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
			array("left" => 1, "right" => 1, "res" => true)
			,array("left" => 2, "right" => 1, "res" => false)
			,array("left" => 1, "right" => 2, "res" => false));
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
			array("left" => "aa", "right" => "aa", "res" => true)
			,array("left" => "a", "right" => "aa", "res" => false)
			,array("left" => "ab", "right" => "a", "res" => false)
			,array("left" => "1", "right" => "1", "res" => true)
			,array("left" => "a", "right" => "1", "res" => false)
			,array("left" => "a", "right" => "", "res" => false)
			,array("left" => "2", "right" => "1", "res" => false));
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
			array("left" => "2016-01-01", "right" => "2016-01-01", "res" => true)
			,array("left" => "2016-01-02", "right" => "2016-01-01", "res" => false)
			,array("left" => "2016-01-01", "right" => "2016-01-02", "res" => false));
	}

	/**
	* @dataProvider eq_int_provider
	*/	
	public function test_NEQ_int($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->int($left)->EQ()->int($right)->_NOT();
		$this->assertTrue($i->interpret($res_t, array()) === !$res);
	}

	/**
	* @dataProvider eq_str_provider
	*/
	public function test_NEQ_str($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->str($left)->EQ()->str($right)->_NOT();
		$this->assertTrue($i->interpret($res_t, array()) === !$res);
	}

	/**
	* @dataProvider eq_date_provider
	*/
	public function test_NEQ_date($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime($left))->EQ()->date(new \DateTime($right))->_NOT();
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
			array("left" => 1, "right" => 1, "res" => true)
			,array("left" => 2, "right" => 1, "res" => false)
			,array("left" => 1, "right" => 2, "res" => true));
	}

	public function test_IN() {
		$this->assertFalse("Implement me!");
	}

	public function test_LIKE() {
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

		$res_t = $f->str($left)->LE()->str($right);
		$this->assertTrue($i->interpret($res_t, array()) === $res);
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
	public function test_LE_date($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime($left))->LE()->date(new \DateTime($right));
		$this->assertTrue($i->interpret($res_t, array()) === $res);
	}

	public function le_date_provider() {
		return array(
			array("left" => "2016-01-01", "right" => "2016-01-01", "res" => true)
			,array("left" =>  "2016-01-02", "right" =>  "2016-01-01", "res" => false)
			,array("left" =>  "2016-01-01", "right" =>  "2016-01-02", "res" => true));
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
			array("left" => 1, "right" => 1, "res" => true)
			,array("left" => 2, "right" => 1, "res" => true)
			,array("left" => 1, "right" => 2, "res" => false));
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
	public function test_GE_date($left,$right,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime($left))->GE()->date(new \DateTime($right));
		$this->assertTrue($i->interpret($res_t, array()) === $res);
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
			array("field" => array() ,"res" => false)
			,array("field" => array("one" => 1) ,"res" => true)
			,array("field" => array("two" => "a", "one" => 1) ,"res" => true)
			,array("field" => array("two" => "a", "one" => "1") ,"res" => false)
			,array("field" => array("two" => 1) ,"res" => false));
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
			array("field" => array() ,"res" => false)
			,array("field" => array("one" => "a") ,"res" => true)
			,array("field" => array("two" => "a", "one" => "1") ,"res" => false)
			,array("field" => array("two" => "", "one" => "a") ,"res" => true)
			,array("field" => array("two" => "a") ,"res" => false));
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
	public function test_int_ge_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_r = $f->int(1)->GE()->field("one");

		$this->assertTrue($i->interpret($res_r, $field) === $res);
	}

	/**
	* @dataProvider int_ge_field
	*/
	public function test_field_le_int($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->LE()->int(1);

		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}


	/**
	* @dataProvider int_ge_field
	*/
	public function test_int_lt_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->int(1)->LT()->field("one")->_NOT();

		$this->assertTrue($i->interpret($res_t, $field) === !$res);
	}
	
	/**
	* @dataProvider int_ge_field
	*/
	public function test_field_gt_int($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->GT()->int(1)->_NOT();

		$this->assertTrue($i->interpret($res_t, $field) === !$res);
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
	public function test_str_ge_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->str("b")->GE()->field("one");

		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider str_ge_field
	*/
	public function test_field_le_str($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->LE()->str("b");

		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider str_ge_field
	*/
	public function test_str_lt_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->str("b")->LT()->field("one")->_NOT();

		$this->assertTrue($i->interpret($res_t, $field) === !$res);
	}
	
	/**
	* @dataProvider str_ge_field
	*/
	public function test_field_gt_str($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->GT()->str("b")->_NOT();

		$this->assertTrue($i->interpret($res_t, $field) === !$res);
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
	public function x_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime("2016-01-01"))->GE()->field("one");

		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider date_ge_field
	*/
	public function test_field_le_date($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->LE()->date(new \DateTime("2016-01-01"));

		$this->assertTrue($i->interpret($res_t, $field) === $res);
	}

	/**
	* @dataProvider date_ge_field
	*/
	public function test_date_lt_field($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->date(new \DateTime("2016-01-01"))->LT()->field("one")->_NOT();

		$this->assertTrue($i->interpret($res_t, $field) === !$res);
	}
	
	/**
	* @dataProvider date_ge_field
	*/
	public function test_field_gt_date($field,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$res_t = $f->field("one")->GT()->date(new \DateTime("2016-01-02"))->_NOT();

		$this->assertTrue($i->interpret($res_t, $field) === !$res);
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
	public function test_eq_two_field($fields,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->EQ()->field("two");

		$this->assertTrue($i->interpret($p, $fields) === $res);
	}


	/**
	 * @dataProvider eq_two_fields
	*/
	public function test_neq_two_field($fields,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->EQ()->field("two")->_NOT();

		$this->assertTrue($i->interpret($p, $fields) !== $res);
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
	public function test_ge_two_field($fields,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->GE()->field("two");

		$this->assertTrue($i->interpret($p, $fields) === $res);
	}

	/**
	 * @dataProvider ge_two_fields
	*/
	public function test_lt_two_field($fields,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one")->LT()->field("two");

		$this->assertTrue($i->interpret($p, $fields) === !$res);
	}

	/**
	 * @dataProvider ge_two_fields
	*/
	public function test_le_two_field($fields,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("two")->LE()->field("one");

		$this->assertTrue($i->interpret($p, $fields) === $res);
	}

	/**
	 * @dataProvider ge_two_fields
	*/
	public function test_gt_two_field($fields,$res) {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("two")->GT()->field("one");

		$this->assertTrue($i->interpret($p, $fields) === !$res);
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
	
	public function test_IN() {
		$this->assertFalse("Implement me!");
	}

	public function test_LIKE() {
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

}