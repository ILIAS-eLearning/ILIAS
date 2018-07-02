<?php
namespace ILIAS\TMS\TableRelations\Tables;
use ILIAS\TMS\TableRelations\Graphs as Graphs;
use ILIAS\TMS\Filter\Predicates as Predicates;

/**
 * This is to be seen as a container carrying
 * metadata information about tables, i.e.
 * the identification and contained columns/fields.
 * Also constraints may be added, to select only subsets
 * of information contained in tables.
 */
class Table implements AbstractTable, Graphs\AbstractNode {

	protected $id;
	protected $title;
	protected $fields = array();
	protected $field_names = array();
	protected $subgraph;
	protected $constraint = null;

	public function __construct($title, $id) {
		$this->title = $title;
		$this->id = $id;
	}

	/**
	 * @inheritdoc
	 */
	public function addField(AbstractTableField $field) {
		if($this->fieldInTable($field)) {
			$name = $field->name_simple();
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
		return $this->fields;
	}

	/**
	 * @inheritdoc
	 */
	public function addConstraint(Predicates\Predicate $predicate) {
		foreach ($predicate->fields() as $field) {
			if(!$this->fieldInTable($field)) {
				throw new TableException("unknown fields in predicate");
			}
		}
		if($this->constraint) {
			$this->constraint = $this->constraint->_AND($predicate);
		} else {
			$this->constraint = $predicate;
		}
		return $this;
	}

	/**
	 * Check wether a field is in this table.
	 *
	 * @param	AbstractTableField	$field
	 * @return	bool
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
	public function id() {
		return $this->id;
	}

	/**
	 * @inheritdoc
	 */
	public function title() {
		return $this->title;
	}

	/**
	 * @inheritdoc
	 */
	public function constraint() {
		return $this->constraint;
	}

	/**
	 * which subgraph does this table belong to?
	 */
	public function subgraph() {
		return $this->subgraph;
	}

	/**
	 * set the subgraph corresponding to which this table belongs.
	 *
	 * @param	sting|int	$subgraph
	 */
	public function setSubgraph($subgraph) {
		$this->subgraph = $subgraph;
	}

	/**
	 * @inheritdoc
	 */
	public function field($name) {
		return $this->fields[$name];
	}
}
