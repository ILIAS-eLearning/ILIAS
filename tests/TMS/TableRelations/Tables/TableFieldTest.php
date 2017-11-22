<?php
use ILIAS\TMS\TableRelations as TableRelations;
use ILIAS\TMS\Filter as Filters;
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
		$f = $this->tf;
		$fa = $f->field('a');
		$fb = $f->field('b');
		$fc = $f->field('c');
		$min = $f->min('min',$fc);
		$quot = $f->quot('quot',$fa,$fb);
		$aux = $f->plus('sum',$min,$quot);
		$plus1 = $f->plus('plus1',$fa,$fb);
		$plus2 = $f->plus('plus2',$plus1,$min);
		$group_concat = $f->groupConcat('plus2',$plus1, '///' );
		$this->assertEquals($group_concat->separator(),'///');
		$this->assertTrue($this->fieldListsEqual(array($quot->left,$quot->right),array($fa,$fb)));
		$this->assertTrue($this->fieldListsEqual(array($min->argument()),array($fc)));
		$this->assertTrue($this->fieldListsEqual(array($aux->left(),$aux->right()),array($min,$quot)));
		$this->assertTrue($this->fieldListsEqual($aux->derivedFromRecursive(),array($fa,$fb,$fc)));
		$this->assertTrue($this->fieldListsEqual($plus2->derivedFromRecursive(),array($fa,$fb,$fc)));
		$this->assertFalse($this->fieldListsEqual($plus2->derivedFromRecursive(),array($fa,$fb,$fc,$min)));
	}

	protected function fieldListsEqual($list1,$list2) {
		$field_ids1 = array_unique(array_map(function($field) {return $field->name();},$list1));
		$field_ids2 = array_unique(array_map(function($field) {return $field->name();},$list2));
		return count(array_intersect($field_ids1, $field_ids2)) === count($list1) && count(array_intersect($field_ids1, $field_ids2)) === count($list2);
	}
}
