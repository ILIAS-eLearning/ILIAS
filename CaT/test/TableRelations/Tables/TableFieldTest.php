<?php
use CaT\TableRelations as TableRelations;
use CaT\Filter as Filters;
class TableFieldTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->tf = new TableRelations\TableFactory(new Filters\PredicateFactory(), new TableRelations\GraphFactory);
	}

	public function test_field() {
		$field = $this->tf->Field("name", "a_table_id");
		$this->assertEquals($field->name(),"a_table_id.name");
		$this->assertEquals($field->name_simple(),"name");
		$this->assertEquals($field->table_id,"a_table_id");
	}

	public function test_derived_field() {
		$tf = $this->tf;
		$field = $tf->DerivedField("name",function($field_a,$field_b) {return $field_a->name().'+'.$field_b->name();}
			,array($tf->field("name_a","table_a"),$tf->field("name_b","table_b")));
		$this->assertEquals(call_user_func_array($field->postprocess, $field->derivedFrom()),
							"table_a.name_a+table_b.name_b");
	}
}
