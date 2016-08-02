<?php
namespace CaT\TableRelations\Tables;
use CaT\TableRelations as TR;
use CaT\TableRelations\Graphs as Graphs;
use CaT\Filter\Predicates as Predicates;
use CaT\Filter as Filter;
/**
 * Table management takes place here.
 * Translates tables to graphs and operates them
 * to create and forward sorted graphs/query representatives
 * to graph interpreters.
 * Also it runs consistency-checks on Tables/fields provided.
 */
class TableSpace {

	const PRIMARY = 'primary_sg';
	const SECONDARY = 'secondary_sg';

	protected $graph;
	protected $having;
	protected $fields = array();
	protected $requested_fields = array();
	protected $derived_fields = array();
	protected $filter;
	protected $group_by = array();
	protected $relevant_table_ids = array();
	protected $root_table;
	protected $f;

	public function __construct(TR\TableFactory $f, TR\GraphFactory $graph_f, Filter\PredicateFactory $pf) {
		$this->graph = $graph_f->Graph();
		$this->f = $f;
		$this->pf = $pf;
	}

	/**
	 * Space setup.
	 */
	protected function addTable(AbstractTable $table) {
		$table_id = $table->id();
		if($this->graph->getNodeById($table_id)) {
			throw new TableException("$table_id allready in space");
		}
		if(count($table->fields()) === 0) {
			throw new TableException("Cant add empty table.");
		}
		$this->fields[$table_id] = array();
		foreach ($table->fields() as $field) {
			$this->fields[$table_id] = $field;
		}
		if($table->subgraph() !== null ) {
			$this->graph->addNode($table, $table->subgraph());
		} else {
			$this->graph->addNode($table);
		}
		return $this;
	}

	public function addDependency(AbstractTableDependency $dep) {
		$from = $dep->from();
		$to = $dep->to();
		foreach($dep->fields() as $field) {
			if(!$this->fieldInSpace($field)) {
				throw new TableException("field ".$field->name()." seem not to be in space");
			}
		}
		if(!$this->graph->getNodeById($from->id()) == $from || !$this->graph->getNodeById($to->id()) == $to) {
			throw new TableException("tables ".$from->id().", ".$to->id()." seem not to be in space");
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

	public function addTablePrimary(AbstractTable $table) {
		$table->setSubgraph(self::PRIMARY);
		$this->relevant_table_ids[] = $table->id();
		$this->addTable($table);
		return $this;
	}

	public function addTableSecondary(AbstractTable $table) {
		$table->setSubgraph(self::SECONDARY);
		$this->addTable($table);
		return $this;
	}

	public function setRootTable(AbstractTable $table) {
		$id = $table->id();
		if($this->graph->getNodeById($id) != $table) {
			throw new TableException("$id not in space");
		}
		$this->root_table = $id;
		return $this;
	}

	/**
	 * Query definition.
	 */
	public function request(Predicates\Field $field, $id = null) {

		if(!$field instanceof AbstractTableField && !$field instanceof AbstractDerivedField) {
			throw new TableException("invalid field");
		}
		$designated_id = $id !== null ? $id : $field->name_simple();
		if(isset($this->requested_fields[$designated_id])) {
			throw new TableException("id $designated_id allready requested");
		}
		if($field instanceof AbstractTableField) {
			if($this->fieldInSpace($field)) {
				$this->relevant_table_ids[] = $field->tableId();
				if($id === null) {
					if(!isset($this->requested[$field->name_simple()])) {
						$this->requested_field_ids[$field->name_simple()] = $field;
					} 
				}
			} else {
				throw new TableException("requested field $name not in space");
			}
		} elseif($field instanceof AbstractDerivedField) {
			foreach($field->derivedFrom() as $filed) {
				if(!$this->fieldInSpace($field)) {
					$name = $field->name_simple();
					throw new TableException("requested field $name not in space");
				}
				$this->relevant_table_ids[] = $field->tableId();
			}
		} else {
			throw new TableException("invalid field type");
		}
		$this->requested_fields[$designated_id] = $field;
		return $this;
	}

	public function addFilter(Predicates\Predicate $predicate) {
		foreach($predicate->fields() as $field) {
			if(!$this->fieldInSpace($field)) {
				throw new TableException("unknown field");
			}
			$this->relevant_table_ids[] = $field->tableId();
		}
		if($this->filter === null) {
			$this->filter = $predicate;
		} else {
			$this->filter = $this->filter->_AND($predicate);
		}
		return $this;
	}

	public function groupBy(Predicates\Field $field) {
		if($field instanceof AbstractTableField) {
			if(!$this->fieldInSpace($field)) {
				throw new TableException("requested field $name not in space");
			}
			$this->relevant_table_ids[$field->tableId()];
		} elseif($field instanceof AbstractDerivedField) {
			if(!isset($this->requested_fields[$field->name_simple()])) {
				throw new TableException("requested field $name not in space");
			}
		} else {
			throw new TableException("unknown field type");
		}
		$this->group_by[] = $field;
		return $this;
	}

	public function addHaving(Predicates\Predicate $predicate) {
		foreach($predicate->fields() as $field) {
			if(!isset($this->requested_fields[$field->name()])) {
				throw new TableExcepton("unknown field");
			}
		}
		if($this->having === null) {
			$this->having = $predicate;
		} else {
			$this->having = $this->having->_AND($predicate);
		}
		return $this;
	}

	public function query() {
		$paths = $this->getPathsFromGraph();
		$o_path = $this->getOrderedPathFromPaths($paths);
		$join_conditions = $this->getConditionsOnPaths($paths);
		$query = $this->f->query()
						->setRequested($this->requested_fields)
						->setRootTable($this->graph->getNodeById($this->root_table))
						->setJoins($o_path)
						->setJoinConditions($join_conditions)
						->setFilter($this->filter)
						->setHaving($this->having);
		foreach ($this->group_by as $field) {
			$query->setGroupBy($field);
		}
		return $query;
	}

	protected function getPathsFromGraph() {
		$paths = array();
		foreach (array_unique($this->relevant_table_ids) as $node_id) {
			if($node_id === $this->root_table) {
				continue;
			}
			$sg = $this->graph->getSubgraphOfNodeId($node_id) === self::PRIMARY ? self::PRIMARY : null;
			$paths = array_merge($this->graph->getPathsBetween($this->root_table, $node_id, $sg),$paths);
		}
		return $paths;
	}

	protected function getOrderedPathFromPaths(array $paths) {
		$o_path = clone current($paths);
		while($path = next($paths)) {
			$prev_table_id = null;
			foreach($path as $table_id => $table) {
				if($o_path->contains($table_id)) {
					if($o_path->positionOf($table_id) < $o_path->positionOf($prev_table_id)) {
						$o_path->insertAfter($prev_table_id,$table);
					}
				} else {
					$o_path->insertAfter($prev_table_id,$table);
				}
				$prev_table_id = $table_id;
			}
		}
		return $o_path;
	}

	protected function getConditionsOnPaths($paths) {
		$join_condition = array();
		foreach($paths as $path) {
			$prev_table_id = null;
			foreach ($path as $table_id => $table) {
				if($table_id !== $this->root_table) {
					if(!isset($join_conditions[$table_id])) {
						$join_conditions[$table_id] = array();
					}
					if(!isset($join_conditions[$table_id][$prev_table_id])) {
						$join_conditions[$table_id][$prev_table_id]
							= $this->graph->edgeBetween($prev_table_id,$table_id);
					}
				}
				$prev_table_id = $table_id;
			}
		}
		return $join_conditions;
	}

	/**
	 * misc.
	 */
	protected function fieldInSpace(AbstractTableField $field) {
		return $this->graph->getNodeById($field->tableId())->fieldInTable($field);
	}

	protected function fieldRequested(Predicates\Field $field) {
		if($field instanceof AbstractTableField) {
			return isset($this->requested_fields[$field->name_simple()]);
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
}