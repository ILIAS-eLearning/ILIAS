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
	protected $constrain = null;

	public function __construct($title, $id) {
		$this->title = $title;
		$this->id = $id;
	}

	/**
	 * @inheritdoc
	 */
	public function addField(AbstractTableField $field) {
		if($this->fieldInTable($field)) {
			$name = $field->name_simple;
			$id = $this->id;
			throw new TableException("field $name in table $id allready");
		}
		$f_table = $field->tableId();
		if($f_table === $this->id) {
			$this->fields[$field->name_simple()] = $field;
		} elseif($f_table === null) {
			$field->setTableId($this->id);
			$this->fields[$field->name_simple()] = $field;
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
		foreach ($predicate->fields() as $field) {
			if(!$this->fieldInTable($field)) {
				throw new TableException("unknown fields in predicate");
			}
		}
		$this->constrain = $predicate;
	}

	public function fieldInTable(AbstractTableField $field) {
		if(!isset($this->fields[$field->name_simple()])) {
			return false;
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
