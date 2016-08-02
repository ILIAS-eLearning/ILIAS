<?php

namespace CaT\TableRelations\Tables;


class DerivedTable implements AbstractTable, Graphs\AbstractNode{
	protected $space;
	protected $fields;
	protected $id;
	protected $subgraph;
	protected $constrain = null;
	public function __construct(TableSpace $space, $id) {
		if(count($space->requested()) === 0) {
			throw new TableException("$id:can't construct by space, no fields requested");
		}
		foreach ($space->requested as $field) {
			$this->fields[$field->name_simple()] = new TableField($field->name_simple(), $id);
		}
		$this->space = $space;
		$this->id = $id;
	}

	public function addConstrain(Predicates\Predicate $constrain) {
		foreach($constrain->fields() as $field) {
			if(!$this->fieldInTable($field)) {
				$name = $field->name_simple();
				throw new TableException("field $name not in table.");
			}
		}
		$this->constrain = $constrain;
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
	public function fields() {
		return $this->fields;
	}

	/**
	 * @inheritdoc
	 */
	public function title() {
		return $this->id;
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
	public function constrain() {
		return $this->constrain;
	}

	public function subgraph() {
		return $this->subgraph;
	}

	public function setSubgraph($subgraph) {
		$this->subgraph = $subgraph;
	}

	public function space() {
		return $this->space;
	}
}