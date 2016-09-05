<?php
namespace CaT\TableRelations;

use CaT\Filter as Filters;
/**
 * This is a class to fullfill our our table construction needs,
 * e.g. shortcuts for tables and derived fields used in many reports.
 * Will be extended peu a peu according to demand.
 */
class TableFactory {
	public function __construct(Filters\PredicateFactory $predicate_factory, GraphFactory $gf) {
		$this->predicate_factory = $predicate_factory;
		$this->graph_factory = $gf;
	}

	/**
	 * @param	string	$name
	 * @param	string|null	$table_id
	 * @return	AbstractField
	 */
	public function Field($name, $table_id = null) {
		return new Tables\TableField($this->predicate_factory, $name, $table_id);
	}

	/**
	 * @param	string	$name
	 * @param	string	$table_id
	 * @param	AbstractField[]	$fields
	 * @return	AbstractTable
	 */
	public function Table($name, $table_id,array $fields = array()) {
		$table = new Tables\Table($name, $table_id);
		foreach ($fields as $field) {
			$table->addField($field);
		}
		return $table;
	}

	/**
	 * @param	AbstractTable	$from
	 * @param	AbstractTable	$to
	 * @param	Predicate	$predicate
	 * @param	AbstractField[]	$fields
	 * @return	AbstractTableDependency
	 */
	public function TableJoin(Tables\AbstractTable $from, Tables\AbstractTable $to, Filters\Predicates\Predicate $predicate) {
		$table = new Tables\TableJoin;
		$table->dependingTables($from, $to, $predicate);
		return $table;
	}

	/**
	 * @param	AbstractTable	$from
	 * @param	AbstractTable	$to
	 * @param	Predicate	$predicate
	 * @param	AbstractField[]	$fields
	 * @return	AbstractTableDependency
	 */
	public function TableLeftJoin(Tables\AbstractTable $from, Tables\AbstractTable $to, Filters\Predicates\Predicate $predicate) {
		$table = new Tables\TableLeftJoin;
		$table->dependingTables($from, $to, $predicate);
		return $table;
	}

	/**
	 * @param	string	$name
	 * @param	clousre	$postprocess
	 * @param	AbstractField[]	$fields
	 * @return	AbstractDerivedField
	 */
	public function DerivedField($name, \Closure $postprocess, array $fields) {
		return new Tables\DerivedField($this->predicate_factory,$name,$postprocess,$fields);
	}

	/**
	 * @return	TableSpace
	 */
	public function TableSpace() {
		return new Tables\TableSpace($this, $this->graph_factory,$this->predicate_factory);
	}

	/**
	 * @param	TableSpace	$space
	 * @param	string	$id
	 * @return	AbstractTable
	 */
	public function DerivedTable(Tables\TableSpace $space, $id) {
		return new Tables\DerivedTable($this->predicate_factory, $space, $id);
	}

	/**
	 * @return	Query
	 */
	public function query() {
		return new Tables\Query;
	}

	/**
	 * Hist user table representation
	 *
	 * @param	string	$id
	 * @return	AbstractTable
	 */
	public function histUser($id) {
		return $this->table('hist_user',$id)
			->addField($this->field('user_id'))
			->addField($this->field('hist_historic'))
			->addField($this->field('firstname'))
			->addField($this->field('lastname'))
		;
	}

	/**
	 * Hist user orgu table representation
	 *
	 * @param	string	$id
	 * @return	AbstractTable
	 */
	public function histUserOrgu($id) {
		return $this->table('hist_userorgu',$id)
			->addField($this->field('usr_id'))
			->addField($this->field('orgu_id'))
			->addField($this->field('orgu_title'))
			->addField($this->field('hist_historic'))
			->addField($this->field('action'))
			->addField($this->field('org_unit_above1'))
			->addField($this->field('org_unit_above2'))
		;
	}

	/**
	 * Derived field equivalent of sql GROUP_CONCAT
	 *
	 * @param	string	$name
	 * @param	AbstractField	$field 
	 * @param	string	$separator
	 * @return	AbsractDerivedField
	 */
	public function groupConcatFieldSql($name, Filters\Predicates\Field $field, $separator = ', ') {
		return $this->DerivedField(
			$name
			,function($field) use ($separator){
				return 'GROUP_CONCAT(DISTINCT '
					.$field
					.' ORDER BY '.$field
					.' DESC SEPARATOR \''.$separator.'\')';
			}
			,array($field));
	}

	/**
	 * Derived field equivalent of sql -
	 *
	 * @param	string	$name
	 * @param	AbstractField	$minuend
	 * @param	AbstractField	$subtrahend
	 * @return	AbsractDerivedField
	 */
	public function diffFieldsSql($name, Filters\Predicates\Field $minuend, Filters\Predicates\Field $subtrahend) {
		return $this->DerivedField(
			$name
			,function($minuend,$subtrahend) {
				return $minuend.' - '.$subtrahend;
			}
			,array($minuend,$subtrahend));
	}

	/**
	 * Derived field equivalent of sql FROM_UNIXTIME
	 *
	 * @param	string	$name
	 * @param	AbstractField	$timestamp
	 * @return	AbsractDerivedField
	 */
	public function fromUnixtimeSql($name, Filters\Predicates\Field $timestamp) {
		return $this->DerivedField(
			$name
			,function($timestamp) {
				return 'FROM_UNIXTIME('.$timestamp.',\'%d.%m.%Y\')';
			}
			,array($timestamp));
	}
}
