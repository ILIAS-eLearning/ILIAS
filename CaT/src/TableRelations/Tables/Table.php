<?php
namespace \CaT\TableRelations\Tables;
use \CaT\TableRelations\Graphs as Graphs;
use CaT\Filter\Predicates as Predicates;

class Table implements abstractTable, \Graphs\abstractNode {

	protected $id;
	protected $title;
	protected $fields = array();
	protected $field_names = array();

	public function __construct($title, $id) {

	}


	/**
	 * @inheritdoc
	 */
	public function addField(Predicates\Field $field) {
		$this->fields[] = $field;
		$this->field_names[] = $field->name();
	}

	/**
	 * @inheritdoc
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * @inheritdoc
	 */
	public function addConstrain(Predicates\Predicate $predicate) {
		if(count(array_diff($predicate->fields(),$this->field_names))) {
			$this->constrain = $predicate;
		} else {
			throw new TableException("unknown fields in predicate");
		}
	}

	/**
	 * @inheritdoc
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * @inheritdoc
	 */
	public function title() {
		return $this->title;
	}
}