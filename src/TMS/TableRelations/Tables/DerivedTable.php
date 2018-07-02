<?php

namespace ILIAS\TMS\TableRelations\Tables;
use ILIAS\TMS\TableRelations\Graphs as Graphs;
use ILIAS\TMS\Filter as Filters;

/**
 * Table derived from space (~subselect).
 */
class DerivedTable implements AbstractTable, Graphs\AbstractNode {
	protected $space;
	protected $fields;
	protected $id;
	protected $subgraph;
	protected $constraint = null;
	public function __construct(Filters\PredicateFactory $pf,TableSpace $space, $id) {
		if(count($space->requested()) === 0) {
			throw new TableException("$id:can't construct by space, no fields requested");
		}
		foreach ($space->requested() as $designated_id => $field) {
			$this->fields[$designated_id] = new TableField($pf, $designated_id, $id);
		}
		$this->space = $space;
		$this->id = $id;
	}

	/**
	 * @inheritdoc
	 */
	public function addConstraint(Filters\Predicates\Predicate $constraint) {
		foreach($constrain->fields() as $field) {
			if(!$this->fieldInTable($field)) {
				$name = $field->name_simple();
				throw new TableException("field $name not in table.");
			}
		}
		$this->constrain = $constraint;
	}
	/**
	 * Insert constraint directly into the subspace.
	 * This should hopefully esure better performance, since data will
	 * be filtered right where it is touched for the first time.
	 *
	 * @param	Filters\Predicates\Predicate	$constraint
	 * @return	void
	 */
	public function addConstraintSub(Filters\Predicates\Predicate $constraint) {
		$this->space->addFilter($constraint);
	}

	/**
	 * @inheritdoc
	 */
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
	public function field($id) {
		return $this->fields[$id];
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
	public function constraint() {
		return $this->constraint;
	}

	/**
	 * @inheritdoc
	 */
	public function subgraph() {
		return $this->subgraph;
	}

	/**
	 * @inheritdoc
	 */
	public function setSubgraph($subgraph) {
		$this->subgraph = $subgraph;
	}

	/**
	 * @inheritdoc
	 */
	public function space() {
		return $this->space;
	}
}
