<?php
use ILIAS\TMS\TableRelations as TableRelations;
use ILIAS\TMS\Filter as Filters;
class TableTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->pf = new Filters\PredicateFactory();
		$this->tf = new TableRelations\TableFactory($this->pf, new  TableRelations\GraphFactory);
	}

	public function tableSample() {
		$tf = $this->tf;
		return $tf->Table("table","table_id")
			->addField($tf->Field("field1","table_id"))
			->addField($tf->Field("field2","table_id"));
	}

	public function test_table_create() {
		$tf = $this->tf;
		$t = $tf->Table("table","table_id",
				array($tf->Field("field1")
					, $tf->Field("field2"))
			)->addField($tf->Field("field3"));
		$field_names = array_map(function($field) {return $field->name();},$t->fields());
		$this->assertCount(0,array_diff($field_names,array("table_id.field1","table_id.field2","table_id.field3")));
		$this->assertCount(0,array_diff(array("table_id.field1","table_id.field2","table_id.field3"),$field_names));
		$field_namesS = array_map(function($field) {return $field->name_simple();},$t->fields());
		$this->assertCount(0,array_diff($field_namesS,array("field1","field2","field3")));
		$this->assertCount(0,array_diff(array("field1","field2","field3"),$field_namesS));
	}

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


	public function space() {
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
		return $ts;
	}

	public function test_table_constrain() {
		$t = $this->tableSample();
		$t->addConstraint($this->tf->Field("field1","table_id")->EQ()->int(1));
	}

	/**
	 * @expectedException ILIAS\TMS\TableRelations\Tables\TableException
	 */
	public function test_wrong_filed() {
		$t = $this->tableSample();
		$t->addField($this->tf->Field("field1","table_id1"));
	}
	/**
	 * @expectedException ILIAS\TMS\TableRelations\Tables\TableException
	 */
	public function test_wrong_field_in_constrain() {
		$t = $this->tableSample();
		$t->addConstraint($this->tf->Field("aafield12")->EQ()->int(1));
	}

	public function test_derived_table() {
		$table = $this->tf->DerivedTable($this->space(),"derived");
		$this->assertTrue($table->fieldInTable($this->tf->Field("foo","derived")));
		$this->assertFalse($table->fieldInTable($this->tf->Field("bar","derived")));
	}
}
