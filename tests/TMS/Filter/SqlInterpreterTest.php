<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

class  SqlInterpreterTest 
/**
 * skipped for now to avoid ilias-dependency in test.
 */
//extends PHPUnit_Framework_TestCase 
{
	protected $backupGlobals = FALSE;

	protected function setUp() {
		PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;

		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		global $ilDB;
		$this->db = $ilDB;
		$this->factory = new \ILIAS\TMS\Filter\PredicateFactory();
		$this->interpreter = new \ILIAS\TMS\Filter\SqlPredicateInterpreter($ilDB);
		date_default_timezone_set('Europe/Berlin');
	}

	public function test_TRUE() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->_TRUE();

		$this->assertEquals($i->interpret($p) , 'TRUE ');
	}
	
	public function test_FALSE() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->_FALSE();

		$this->assertEquals($i->interpret($p) , 'NOT (TRUE ) ');
	}

	public function test_ALL() {
		$f = $this->factory;
		$i = $this->interpreter;

		$true = $f->_TRUE();
		$false = $f->_FALSE();

		$p = $f->_ALL($true, $true, $false);

		$this->assertEquals($i->interpret($p) , '(TRUE ) AND (TRUE ) AND (NOT (TRUE ) ) ');
		//$this->assertEquals($p->subs(), array($true, $true, $false));

		$p2 = $f->_ALL($true, $true, $true);

		$this->assertEquals($i->interpret($p2),  '(TRUE ) AND (TRUE ) AND (TRUE ) ');
		//$this->assertEquals($p->subs(), array($true, $true, $true));

		$p3 = $f->_ALL($false, $false, $false);

		$this->assertEquals($i->interpret($p3),  '(NOT (TRUE ) ) AND (NOT (TRUE ) ) AND (NOT (TRUE ) ) ');
		//$this->assertEquals($p->subs(), array($false, $false, $false));
	}

	public function test_AND() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->_TRUE()->_AND()->_FALSE();

		$this->assertEquals($i->interpret($p),  '(TRUE ) AND (NOT (TRUE ) ) ');
	}

	public function test_ANY() {
		$f = $this->factory;
		$i = $this->interpreter;

		$true = $f->_TRUE();
		$false = $f->_FALSE();

		$p = $f->_ANY($true, $true, $false);

		$this->assertEquals($i->interpret($p) , '(TRUE ) OR (TRUE ) OR (NOT (TRUE ) ) ');
		//$this->assertEquals($p->subs(), array($true, $true, $false));

		$p2 = $f->_ANY($true, $true, $true);

		$this->assertEquals($i->interpret($p2),  '(TRUE ) OR (TRUE ) OR (TRUE ) ');
		//$this->assertEquals($p->subs(), array($true, $true, $true));

		$p3 = $f->_ANY($false, $false, $false);

		$this->assertEquals($i->interpret($p3),  '(NOT (TRUE ) ) OR (NOT (TRUE ) ) OR (NOT (TRUE ) ) ');
		//$this->assertEquals($p->subs(), array($false, $false, $false));
	}

	public function test_OR() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->_TRUE()->_OR()->_FALSE();

		$this->assertEquals($i->interpret($p),  '(TRUE ) OR (NOT (TRUE ) ) ');
	}

	public function test_NOT() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->_NOT()->_TRUE();
		// $f->_NOT($f->TRUE());

		$this->assertEquals($i->interpret($p), 'NOT (TRUE ) ');
	}


	public function test_EQ_int() {
		$left = 1;
		$right = 2;
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;

		$res_t = $f->int($left)->EQ()->int($right);
		$this->assertEquals($i->interpret($res_t) , $db->quote($left,'integer').' = '.$db->quote($right,'integer').' ' );
	}

	public function test_EQ_str() {
		$left = "a";
		$right = "b";
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;

		$res_t = $f->str($left)->EQ()->str($right);
		$this->assertEquals($i->interpret($res_t) , $db->quote($left,'text').' = '.$db->quote($right,'text').' ' );
	}


	public function test_EQ_date() {
		$left = '2016-01-01';
		$right = '2016-01-01';
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->date(new \DateTime($left))->EQ()->date(new \DateTime($right));
		$this->assertEquals($i->interpret($res_t) , $db->quote($left,'date').' = '.$db->quote($right,'date').' ' );
	}

	public function test_NEQ_int() {
		$left = 1;
		$right = 2;
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->int($left)->NEQ()->int($right);
		$this->assertEquals($i->interpret($res_t) , $db->quote($left,'integer').' != '.$db->quote($right,'integer').' ' );	
	}

	public function test_NEQ_str() {
		$left = "a";
		$right = "b";
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->str($left)->NEQ()->str($right);
		$this->assertEquals($i->interpret($res_t) , $db->quote($left,'text').' != '.$db->quote($right,'text').' ' );
	}

	public function test_NEQ_date() {
		$left = '2016-01-01';
		$right = '2016-01-01';
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->date(new \DateTime($left))->NEQ()->date(new \DateTime($right));
		$this->assertEquals($i->interpret($res_t) , $db->quote($left,'date').' != '.$db->quote($right,'date').' ' );
	}


	public function test_LT_int() {
		$left = 1;
		$right = 2;
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->int($left)->LT()->int($right);
		$this->assertEquals($i->interpret($res_t) , $db->quote($left,'integer').' < '.$db->quote($right,'integer').' ' );
	}

	public function test_LT_str() {
		$left = "a";
		$right = "b";
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->str($left)->LT()->str($right);
		$this->assertEquals($i->interpret($res_t) , $db->quote($left,'text').' < '.$db->quote($right,'text').' ' );
	}

	public function test_LT_date() {
		$left = '2016-01-01';
		$right = '2016-01-01';
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->date(new \DateTime($left))->LT()->date(new \DateTime($right));
		$this->assertEquals($i->interpret($res_t) , $db->quote($left,'date').' < '.$db->quote($right,'date').' ' );
	}

	public function test_int_eq_field() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->int(1)->EQ()->field("one.two");

		$this->assertEquals($i->interpret($res_t) , $db->quote(1,'integer')." = `one`.`two` ");
	}

	public function test_field_eq_int() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;

		$res_t = $f->field("one.two")->EQ()->int(1);

		$this->assertEquals($i->interpret($res_t) , "`one`.`two` = ".$db->quote(1,'integer')." ");
	}

	public function test_int_neq_field() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->int(1)->NEQ()->field("one.two");
		$this->assertEquals($i->interpret($res_t) , $db->quote(1,'integer')." != `one`.`two` ");
	}

	public function test_field_neq_int() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->field("one.two")->NEQ()->int(1);
		$this->assertEquals($i->interpret($res_t) , "`one`.`two` != ".$db->quote(1,'integer')." ");
	}

	public function test_str_eq_field() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->str("a")->EQ()->field("one.two");
		$this->assertEquals($i->interpret($res_t) , $db->quote('a','text')." = `one`.`two` ");
	}

	public function test_field_eq_str() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->field("one.two")->EQ()->str("a");
		$this->assertEquals($i->interpret($res_t) , "`one`.`two` = ".$this->db->quote('a','text')." ");
	}

	public function test_str_neq_field() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->str("a")->NEQ()->field("one.two");
		$this->assertEquals($i->interpret($res_t) , $db->quote('a','text')." != `one`.`two` ");
	}
	
	public function test_field_neq_str() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->field("one.two")->NEQ()->str("a");
		$this->assertEquals($i->interpret($res_t) , "`one`.`two` != ".$this->db->quote('a','text')." ");
	}

	public function test_date_eq_field() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->date(new \DateTime("2016-01-01 10:00"))->EQ()->field("one.two");
		$this->assertEquals($i->interpret($res_t) , $db->quote("2016-01-01",'date')." = `one`.`two` ");
	}

	public function test_field_eq_date() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->field("one.two")->EQ()->date(new \DateTime("2016-01-01 10:00"));
		$this->assertEquals($i->interpret($res_t) , "`one`.`two` = ".$this->db->quote("2016-01-01",'date')." ");
	}

	public function test_date_neq_field() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->date(new \DateTime("2016-01-01 10:00"))->NEQ()->field("one.two");
		$this->assertEquals($i->interpret($res_t) , $db->quote("2016-01-01",'date')." != `one`.`two` ");
	}
	
	public function test_field_neq_date() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->field("one.two")->NEQ()->date(new \DateTime("2016-01-01 10:00"));
		$this->assertEquals($i->interpret($res_t) , "`one`.`two` != ".$this->db->quote("2016-01-01",'date')." ");
	}


	public function test_int_lt_field() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->int(1)->LT()->field("one.two");
		$this->assertEquals($i->interpret($res_t) , $db->quote(1,'integer')." < `one`.`two` ");
	}
	

	public function test_str_lt_field() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->str("b")->LT()->field("one.two");
		$this->assertEquals($i->interpret($res_t) , $db->quote("b",'text')." < `one`.`two` ");
	}
	
	public function test_ex_two_vars() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$p = $f->str("1")->LT()->int(1);

		try {
			$i->interpret($p);
			$this->assertFalse("should have thrown Exception");
		}
		catch (\InvalidArgumentException $ex) {
		}

	}

	public function test_date_lt_field() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;
		$res_t = $f->date(new \DateTime("2016-01-01 10:00"))->LT()->field("one.two");
		$this->assertEquals($i->interpret($res_t) , $db->quote("2016-01-01",'date')." < `one`.`two` ");
	}

	public function test_eq_two_field() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one.two")->EQ()->field("ONE.TWO");
		$this->assertEquals($i->interpret($p) , "`one`.`two` = `ONE`.`TWO` ");
	}

	public function test_neq_two_field() {
		$f = $this->factory;
		$i = $this->interpreter;
		$p = $f->field("one.two")->NEQ()->field("ONE.TWO");
		$this->assertEquals($i->interpret($p) , "`one`.`two` != `ONE`.`TWO` ");
	}

	public function test_lt_two_field() {
		$f = $this->factory;
		$i = $this->interpreter;

		$p = $f->field("one.two")->LT()->field("ONE.TWO");
		$this->assertEquals($i->interpret($p) , "`one`.`two` < `ONE`.`TWO` ");
	}

	public function test_IN() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;

		$int_list = $f->list_int(1,2,3);		
		$str_list = $f->list_str("a","b","c");		
		
		$int_1 = $f->int(1);
		$str_1 = $f->str("a");

		$field_1 = $f->field("foo.bar");
		$res = $int_1->IN($int_list);
		$this->assertEquals($i->interpret($res), $db->quote(1,'integer').' IN('
					.$db->quote(1,'integer')
					.','.$db->quote(2,'integer')
					.','.$db->quote(3,'integer').') ');

		$res = $str_1->IN($str_list);
		$this->assertEquals($i->interpret($res), $db->quote('a','text').' IN('
					.$db->quote('a','text')
					.','.$db->quote('b','text')
					.','.$db->quote('c','text').') ');

		$field_1 = $f->field("foo.bar");
		$res = $field_1->IN($int_list);
		$this->assertEquals($i->interpret($res), '`foo`.`bar` IN('
					.$db->quote(1,'integer')
					.','.$db->quote(2,'integer')
					.','.$db->quote(3,'integer').') ');
	}


	public function test_LIKE() {
		$f = $this->factory;
		$i = $this->interpreter;
		$res = $f->field('foo.bar')->LIKE($f->str('blubb%'));
		$this->assertEquals($i->interpret($res), "`foo`.`bar` LIKE 'blubb%'");
		$res = $f->field('foo.bar')->LIKE()->str('blubb%');
		$this->assertEquals($i->interpret($res), "`foo`.`bar` LIKE 'blubb%'");
	}

    /**
     * @expectedException InvalidArgumentException 
     */
	public function test_LIKE_fail() {
		$f = $this->factory;
		$i = $this->interpreter;
		$res = $f->int(1)->LIKE($f->str('blubb%'));
		$i->interpret($res);
	}

	public function test_IsNull() {
		$f = $this->factory;
		$i = $this->interpreter;
		$db = $this->db;

		$res = $f->int(1)->IS_NULL();
		$this->assertEquals($i->interpret($res), "FALSE ");
		$res = $f->str("a")->IS_NULL();
		$this->assertEquals($i->interpret($res), "FALSE ");
		$res = $f->date(new \DateTime("2016-01-01"))->IS_NULL();
		$this->assertEquals($i->interpret($res), "FALSE ");

		$res = $f->field("foo.bar")->IS_NULL();
		$this->assertEquals($i->interpret($res), "`foo`.`bar` IS NULL ");
	}


}
