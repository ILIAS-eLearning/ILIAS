<?php
use CaT\TableRelations\Tables as Tables;
use CaT\Filter as Filters;
class TableTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->tf = new Tables\TableFactory(new Filters\PredicateFactory());
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

	public function test_table_constrain() {
		$t = $this->tableSample();
		$t->addConstrain($this->tf->Field("field1","table_id")->EQ()->int(1));
	}

	/**
	 * @expectedException CaT\TableRelations\Tables\TableException
	 */
	public function test_wrong_filed() {
		$t = $this->tableSample();
		$t->addField($this->tf->Field("field1","table_id1"));
	}
	/**
	 * @expectedException CaT\TableRelations\Tables\TableException
	 */
	public function test_wrong_field_in_constrain() {
		$t = $this->tableSample();
		$t->addConstrain($this->tf->Field("aafield12")->EQ()->int(1));
	}
}
