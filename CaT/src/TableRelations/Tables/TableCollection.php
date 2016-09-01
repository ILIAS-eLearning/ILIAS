<?php
/**
 * A container for tables. It may be used to cluster
 * tables into logical units.
 */

namespace \CaT\TableRelations\Tables;

class TableCollection implements AbstractTableCollection{
	protected $tables = array();
	protected $dependencies = array();
	protected $id;

	public function __construct($id) {
		$this->id = (string)$id;
	}

	public function id() {
		return $this->id;
	}

	public function addTable(AbstractTable\Table $table) {
		$table_id = $table->id();
		if(isset($this->tables[$table_id])) {
			throw new TableException("$table_id allready in collection");
		}
		$this->tables[$table_id] = $table->setSubgraph($table_id);
		return $this;
	}

	public function addDependency(AbstractTableDependency $dep) {
		foreach ($dep->fields() as $field) {
			$table_id = $field->tableId();
			if(!isset($this->tables[$table_id])) {
				throw new TableException("$table_id used in dependenciy not in collection");
			}
		}
		$this->dependencies[] = $dep;
		return $this;
	}

	public function tables() {
		return $this->tables;
	}

	public function dependencies() {
		return $this->dependencies;
	}
}