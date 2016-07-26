<?php
namespace \CaT\TableRelations\Tables;

/**
 * Table management takes place here.
 * Translates tables to graphs and operates them
 * to create and forward sorted graphs/query representatives
 * to graph interpreters.
 * Also it runs consistency-checks on Tables/fields provided.
 */
class TableSpace {
	protected $graph;
	protected $fields = array();
	protected $tables = array();
	public function __construct(Graphs\AbstractGraph $graph) {
		$this->graph = $graph;
	}

	public function addTable(AbstractTable\Table $table) {
		$table_id = $table->id();
		if(isset($this->tables[$table_id])) {
			throw new TableException("$table_id allready in table");
		}
		$this->fields[$table_id] = array();
		foreach ($table->fields() as $field) {
			$this->fields[$table_id] = $field;
		}
		$this->graph->addNode($table);
		return $this;
	}

	public function addDependency(AbstractTableDependency $dep) {
		
		return $this;
	}
}
