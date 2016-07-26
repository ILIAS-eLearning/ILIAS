<?php
namespace CaT\TableRelations\Tables;
use CaT\TableRelations\Graphs as Graphs;
use CaT\Filter\Predicates as Predicates;

class Table implements AbstractTable, Graphs\AbstractNode {

	protected $id;
	protected $title;
	protected $fields = array();
	protected $field_names = array();
	protected $subgraph;

	public function __construct($title, $id) {
		$this->title = $title;
		$this->id = $id;
	}


	/**
	 * @inheritdoc
	 */
	public function addField(AbstractTableField $field) {
		$f_table = $field->tableId();
		if($f_table === $this->id) {
			$this->fields[$field->name()] = $field;
		} elseif($f_table === null) {
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
	public function fields() {
		return array_values($this->fields);
	}

	/**
	 * @inheritdoc
	 */
	public function addConstrain(Predicates\Predicate $predicate) {
		if($this->fieldsExistsInTable(array_map(function($field) {return $field->name();},$predicate->fields()))) {
			$this->constrain = $predicate;
		} else {
			throw new TableException("unknown fields in predicate");
		}
	}

	protected function fieldsExistsInTable($full_field_names) {
		foreach ($full_field_names as $full_field_name) {
			if(!isset($this->fields[$full_field_name])) {
				return false;
			}
		}
		return true;
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

	public function constrain() {
		return $this->constrain;
	}

	public function subgraph() {
		return $this->subgraph;
	}

	public function setSubgraph($subgraph) {
		$this->subgraph = $subgraph;
	}
}
