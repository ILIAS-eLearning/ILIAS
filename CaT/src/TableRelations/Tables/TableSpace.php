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
	public function __construct(Graphs\AbstractGraph $graph) {
		$this->graph = $graph;
	}

	public function addTable(AbstractTable\Table $table) {
		$table_id = $table->id();
		if($this->graph->getNodeById($from->id())) {
			throw new TableException("$table_id allready in space");
		}
		$this->fields[$table_id] = array();
		foreach ($table->fields() as $field) {
			$this->fields[$table_id] = $field;
		}
		$this->graph->addNode($table, $subgraph);
		return $this;
	}

	public function addDependency(AbstractTableDependency $dep) {
		$from = $dep->from();
		$to = $dep->to();
		if(!$from == $this->graph->getNodeById($from->id()) || !$to == $this->graph->getNodeById($to->id())) {
			throw new TableException("tables ".$from->id.", ".$to->id." seem not to be in space");
		}
		if($dep instanceof TableJoin) {
			$this->graph->connectNodesSymmetric($dep);
		} elseif($dep instanceof TableLeftJoin) {
			$this->graph->connectNodesDirected($dep);
		} else {
			throw new TableException("unnkown dependency type");
		}
		return $this;
	}
}
