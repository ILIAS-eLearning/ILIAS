<?php
use CaT\TableRelations\Tables as Tables;
use CaT\Filter as Filters;
class TableFieldTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->tf = new Tables\TableFactory(new Filters\PredicateFactory());
	}

	public function test_field() {
		$field = $this->tf->Field("name", "a_table_id");
		$this->assertEquals($field->name(),"a_table_id.name");
		$this->assertEquals($field->table_id,"a_table_id");
	}
}
