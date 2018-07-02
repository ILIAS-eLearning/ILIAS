<?php
namespace ILIAS\TMS\TableRelations;

use ILIAS\TMS\Filter as Filters;
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
	 * Hist user orgu table representation
	 *
	 * @param	string	$id
	 * @return	AbstractTable
	 */
	public function histUserTestrun($id) {
		return $this->table('hist_usertestrun',$id)
			->addField($this->field('usr_id'))
			->addField($this->field('obj_id'))
			->addField($this->field('pass'))
			->addField($this->field('hist_historic'))
			->addField($this->field('max_points'))
			->addField($this->field('points_achieved'))
			->addField($this->field('test_passed'))
			->addField($this->field('testrun_finished_ts'))
			->addField($this->field('test_title'))
			->addField($this->field('percent_to_pass'))
		;
	}

	public function allOrgusOfUsers($id,array $usr_ids = array()) {

		$orgus = $this->histUserOrgu('orgus');
		$constraint = $orgus->field('hist_historic')->EQ()->int(0)->_AND($orgus->field('action')->GE()->int(0));
		if(count($usr_ids)>0) {
			$constraint = $constraint->_AND($this->predicate_factory->IN($orgus->field('usr_id'),$this->predicate_factory->list_int_by_array($usr_ids)));
		}
		$orgus->addConstraint($constraint);
		$all_orgus_space = $this->TableSpace()
					->addTablePrimary($orgus)
					->setRootTable($orgus)
					->request($orgus->field('usr_id'))
					->request($this->groupConcat('orgus',$orgus->field('orgu_title')))
					->groupBy($orgus->field('usr_id'));

		return $this->DerivedTable($all_orgus_space,$id);
	}

	/**
	 * Derived field equivalent of sql GROUP_CONCAT
	 *
	 * @param	string	$name
	 * @param	AbstractField	$field 
	 * @param	string	$separator
	 * @return	AbsractDerivedField
	 */
	public function groupConcat(
		$name,
		Filters\Predicates\Field $field,
		$separator = ', ',
		Filters\Predicates\Field $order_by = null,
		$order_direction = 'ASC'
	) {
		return new Tables\DerivedFields\GroupConcat(
			$this->predicate_factory, 
			$name,
			$field,
			$separator,
			$order_by,
			$order_direction
		);
	}

	public function concat($name, Filters\Predicates\Field $field_1, Filters\Predicates\Field $field_2, $inbetween = null) {
		return new Tables\DerivedFields\Concat($this->predicate_factory, $name, $field_1,  $field_2, $inbetween);
	}

	/**
	 * Derived field equivalent of sql FROM_UNIXTIME
	 *
	 * @param	string	$name
	 * @param	AbstractField	$timestamp
	 * @return	AbsractDerivedField
	 */
	public function fromUnixtime($name, Filters\Predicates\Field $timestamp) {
		return new Tables\DerivedFields\FromUnixtime($this->predicate_factory, $name, $field);
	}

	public function min($name, Filters\Predicates\Field $field) {
		return new Tables\DerivedFields\Min($this->predicate_factory, $name, $field);
	}

	public function max($name, Filters\Predicates\Field $field) {
		return new Tables\DerivedFields\Max($this->predicate_factory, $name, $field);
	}

	public function countAll($name) {
		return new Tables\DerivedFields\Count($this->predicate_factory, $name);
	}

	public function sum($name, Filters\Predicates\Field $field) {
		return new Tables\DerivedFields\Sum($this->predicate_factory, $name, $field);
	}

	public function avg($name, Filters\Predicates\Field $field) {
		return new Tables\DerivedFields\Avg($this->predicate_factory, $name, $field);
	}

	/**
	 * Derived field equivalent of sql /
	 *
	 * @param	string	$name
	 * @param	AbstractField
	 * @param	AbstractField
	 * @return	AbsractDerivedField
	 */
	public function quot($name, Filters\Predicates\Field $enum,Filters\Predicates\Field $denom) {
		return new Tables\DerivedFields\Quot($this->predicate_factory, $name, $enum,$denom);
	}

	/**
	 * Derived field equivalent of sql -
	 *
	 * @param	string	$name
	 * @param	AbstractField
	 * @param	AbstractField
	 * @return	AbsractDerivedField
	 */
	public function minus($name, Filters\Predicates\Field $minuend, Filters\Predicates\Field $subtrahend) {
		return new Tables\DerivedFields\Minus($this->predicate_factory, $name, $minuend, $subtrahend);
	}

	/**
	 * Derived field equivalent of sql +
	 *
	 * @param	string	$name
	 * @param	AbstractField
	 * @param	AbstractField
	 * @return	AbsractDerivedField
	 */
	public function plus($name, Filters\Predicates\Field $summand1, Filters\Predicates\Field $summand2) {
		return new Tables\DerivedFields\Plus($this->predicate_factory, $name, $summand1, $summand2);
	}

	/**
	 * Derived field equivalent of sql *
	 *
	 * @param	string	$name
	 * @param	AbstractField
	 * @param	AbstractField
	 * @return	AbsractDerivedField
	 */
	public function times($name, Filters\Predicates\Field $factor1, Filters\Predicates\Field $factor2) {
		return new Tables\DerivedFields\Times($this->predicate_factory, $name, $factor1, $factor2);
	}

	public function dateFormat(	$name, Filters\Predicates\Field $field, $format = '%d.%m.%Y')
	{
		return new Tables\DerivedFields\DateFormat($this->predicate_factory, $name, $field, $format);
	}

	public function ifThenElse($name, Filters\Predicates\Predicate $condition, Filters\Predicates\Field $then , Filters\Predicates\Field $else)
	{
		return new Tables\DerivedFields\IfThenElse($this->predicate_factory, $name, $condition, $then, $else);
	}

	/**
	 * Constant integer value.
	 *
	 * @param	string	name
	 * @param	int	$value
	 */
	public function constInt($name, $value)
	{
		return new Tables\DerivedFields\ConstInt($this->predicate_factory, $name, $value);
	}

	/**
	 * Constant string value.
	 *
	 * @param	string	name
	 * @param	string	$value
	 */
	public function constString($name, $value = '')
	{
		assert('is_string($name)');
		assert('is_string($value)');
		return new Tables\DerivedFields\ConstString($this->predicate_factory, $name, $value);
	}
}
