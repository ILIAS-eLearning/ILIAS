<?php

use ILIAS\TMS\TableRelations as TR;
use ILIAS\TMS\Filter as Filters;
use ILIAS\TMS\TableRelations\TestFixtures\SqlPredicateInterpreterWrap as SqlPredicateInterpreterWrap;
class TestTableSpace extends PHPUnit_Framework_TestCase {

	public function table($i,$N = 3) {
		$tf = $this->tf;
		$table = $tf->Table("table$i","table".$i."_id");
		for($n = 0; $n < $N; $n++) {
			$table->addField($tf->Field("field1$n","table".$i."_id"));
		}
		return $table;
	}

	public function join($table_1,$table_2) {
		return $this->tf->TableJoin($table_1,$table_2,current($table_1->fields())->EQ(current($table_2->fields())));
	}

	public function leftJoin($table_1,$table_2) {
		return $this->tf->TableLeftJoin($table_1,$table_2,current($table_1->fields())->EQ(current($table_2->fields())));
	}

	public function interpreter() {
		return new TR\SqlQueryInterpreter(new SqlPredicateInterpreterWrap($this->db),$this->pf,$this->db);
	}

	public function setUp() {
		/*PHPUnit_Framework_Error_Deprecated::$enabled = FALSE;
		include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
		ilUnitUtil::performInitialisation();
		global $ilDB;
		$this->db = $ilDB;*/
		$this->pf = new Filters\PredicateFactory();
		$this->tf = new TR\TableFactory( $this->pf,  new TR\GraphFactory);
	}

	public function test_create() {
		$tf = $this->tf;
		$ts = $tf->TableSpace();
		$t1 = $this->table(1)->addConstraint($tf->Field("field12","table1_id")->EQ()->int(2));
		$t2 = $this->table(2)->addConstraint($tf->Field("field11","table2_id")->EQ()->str("bah"));;
		$t3 = $this->table(3);
		$ts->addTablePrimary($t1);
		$ts->setRootTable($t1);
		$ts->addTablePrimary($t2);
		$ts->addTableSecondary($t3);
		$ts->addDependency($this->join($t1,$t2));
		$ts->addDependency($this->leftJoin($t2,$t3));
		$ts->addDependency($this->leftJoin($t1,$t3));
		$ts->request(current($t3->fields()),"foo");
		$ts->addFilter(current($t1->fields())->EQ()->int(1));
		$ts->addHaving($this->pf->field("foo")->EQ()->str("a"));
		$ts->groupBy($tf->Field("field11","table2_id"));
		$query = $ts->query();
	}
}
