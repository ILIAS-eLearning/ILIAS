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

	public function Field($name, $table_id = null) {
		return new Tables\TableField($this->predicate_factory, $name, $table_id);
	}

	public function Table($name, $table_id,array $fields = array()) {
		$table = new Tables\Table($name, $table_id);
		foreach ($fields as $field) {
			$table->addField($field);
		}
		return $table;
	}

	public function TableJoin(Tables\AbstractTable $from, Tables\AbstractTable $to, Filters\Predicates\Predicate $predicate) {
		$table = new Tables\TableJoin;
		$table->dependingTables($from, $to, $predicate);
		return $table;
	}

	public function TableLeftJoin(Tables\AbstractTable $from, Tables\AbstractTable $to, Filters\Predicates\Predicate $predicate) {
		$table = new Tables\TableLeftJoin;
		$table->dependingTables($from, $to, $predicate);
		return $table;
	}

	public function DerivedField($name, \Closure $postprocess, array $fields) {
		return new Tables\DerivedField($this->predicate_factory,$name,$postprocess,$fields);
	}

	public function TableSpace() {
		return new Tables\TableSpace($this, $this->graph_factory,$this->predicate_factory);
	}

	public function DerivedTable(Tables\TableSpace $space, $id) {
		return new Tables\DerivedTable($this->predicate_factory, $space, $id);
	}

	public function query() {
		return new Tables\Query;
	}

	public function histUser($id) {
		return $this->table('hist_user',$id)
			->addField($this->field('user_id'))
			->addField($this->field('hist_historic'))
			->addField($this->field('firstname'))
			->addField($this->field('lastname'))
		;
	}

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

	public function diffFieldsSql($name, Filters\Predicates\Field $minuend, Filters\Predicates\Field $subtrahend) {
		return $this->DerivedField(
			$name
			,function($minuend,$subtrahend) {
				return $minuend.' - '.$subtrahend;
			}
			,array($minuend,$subtrahend));
	}

	public function fromUnixtimeSql($name, Filters\Predicates\Field $timestamp) {
		return $this->DerivedField(
			$name
			,function($timestamp) {
				return 'FROM_UNIXTIME('.$timestamp.',\'%d.%m.%Y\')';
			}
			,array($timestamp));
	}
}
