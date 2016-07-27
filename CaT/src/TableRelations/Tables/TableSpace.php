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
	protected $requested = array();
	protected $filter = array();
	protected $group_by;
	public function __construct(Graphs\AbstractGraph $graph) {
		$this->graph = $graph;
	}

	public function addTable(AbstractTable\Table $table) {
		$table_id = $table->id();
		if($this->graph->getNodeById($from->id())) {
			throw new TableException("$table_id allready in space");
		}
		if(count($table->fields) === 0) {
			throw new TableException("Cant add empty table.");
		}
		$this->fields[$table_id] = array();
		foreach ($table->fields() as $field) {
			$this->fields[$table_id] = $field;
		}
		if($table->subgraph !== null ) {
			$this->graph->addNode($table, $subgraph);
		} else {
			$this->graph->addNode($table);
		}
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

	public function request(Predicate\Field $field, $id = null) {
		if($field instanceof AbstractTableField) {
			if($this->fieldInSpace($field)) {
				if($id === null) {
					if(!isset($this->requested[$field->name_simple()])) {
						$this->requested[$field->name_simple()] = $field;
						return $this;
					} 
				} elseif( !isset($this->requested[$id])) {
					$this->requested[$id] = $field;
					return $this;
				}
				$name = $id ? $id : $field->name();
				throw new TableExcepton("$name allready requested");
			}
			$name = $field->name();
			throw new TableExcepton("requested field $name not in space");
		} elseif($field instanceof AbstractDerivedField) {
			foreach($derived_field->derivedFrom() as $filed) {
				if(!$this->fieldInSpace($field)) {
					$name = $field->name_simple();
					throw new TableExcepton("requested field $name not in space");
				}
			}
			$this->requested[$derived_field->name()] = $derived_field;
			return $this;
		} else {
			throw new TableExcepton("invalid field type");
		}
	}

	protected function fieldInSpace(AbstractTableField $field) {
		return $this->graph->getNodeById($field->tableId())->fieldInTable($field);
	}

	protected function fieldRequested(Predicates\Field $field) {
		if($field instanceof AbstractTableField) {
			return isset($this->requested[$field->name_simple()]);
		} elseif($field instanceof AbstractDerivedField) {
			foreach($derived_field->derivedFrom() as $filed) {
				if(!$this->fieldInSpace($field)) {
					$name = $field->name_simple();
					throw new TableExcepton("requested field $name not in space");
				}
			}
			$this->requested[$derived_field->name()] = $derived_field;
			return $this;
		}
	}

	public function groupBy(Predicates\Field $field) {
		$this->group_by = $field;
	}

	public function having(Predicate\Predicate $predicate) {
		$this->having = $field;
	}

	protected function getRelevantTables() {

	}

	public function addRootTable() {

	}

	public function addTablePrimary() {
		
	}

	public function addTableSecondary() {

	}
}