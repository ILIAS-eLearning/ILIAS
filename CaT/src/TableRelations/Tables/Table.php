<?php
namespace CaT\TableRelations\Tables;
use CaT\TableRelations\Graphs as Graphs;
use CaT\Filter\Predicates as Predicates;

class Table implements abstractTable, Graphs\abstractNode {

	protected $id;
	protected $title;
	protected $fields = array();
	protected $field_names = array();

	public function __construct($title, $id) {
		$this->title = $title;
		$this->id = $id;
	}


	/**
	 * @inheritdoc
	 */
	public function addField(abstractTableField $field) {
		$f_table = $field->tableId();
		if($f_table === $this->id) {
			$this->fields[$field->name()] = $field;
		} elseif($_table = null) {
			$field->setTableId($this->id);
			$this->fields[$field->name()] = $field;
		} elseif($f_table !== $this->id) {
			throw new TableException("inproper table bound to field");
		}
		return $this;
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