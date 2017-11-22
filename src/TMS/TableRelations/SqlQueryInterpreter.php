<?php
namespace ILIAS\TMS\TableRelations;
use ILIAS\TMS\Filter as Filter;
use ILIAS\TMS\TableRelations\Tables\DerivedFields as Derived;

/**
 * Creates a sql-query or data from a query object.
 */
class SqlQueryInterpreter {

	public function __construct(Filter\SqlPredicateInterpreter $predicate_interpreter, Filter\PredicateFactory $pf, \ilDBInterface $ildb) {
		$this->predicate_interpreter = $predicate_interpreter;
		$this->gIldb = $ildb;
		$this->pf = $pf;
	}

	/**
	 * Get the data corresponding to query object.
	 *
	 * @param	Tables\AbstractQuery	$query
	 * @return	array[]
	 */
	public function interpret(Tables\AbstractQuery $query) {
		$res = $this->gIldb->query($this->getSql($query));
		$data = array();
		while($rec = $this->gIldb->fetchAssoc($res)) {
			$data[] = $rec;
		}
		return $data;
	}

	protected function requestedFields(Tables\AbstractQuery $query) {
		$sql_requested = array();
		foreach ($query->requested() as $id => $field) {
			$sql_requested[] = $this->interpretField($field)." AS ".$id;
		}
		return implode(", ", $sql_requested).PHP_EOL;
	}


	protected function interpretField(Filter\Predicates\Field $field) {
			if($field instanceof Tables\TableField) {
				return ' '.$field->name();
			} elseif($field instanceof Tables\DerivedField)  {
				//call self recursively as long derived field derive from derived fields...
				return $this->interpreteDerivedField($field);
			} else {
				throw new TableRelationsException("Unknown field ".$field->name());
			}
	}

	protected function interpreteDerivedField(Tables\DerivedField $field) {
		if($field instanceof Derived\Sum ) {
			return ' SUM('.$this->interpretField($field->argument()).')';
		} elseif( $field instanceof Derived\Count ) {
			return ' COUNT(*)';
		} elseif( $field instanceof Derived\GroupConcat ) {
			$order_by = '';
			$order_by_f = $field->orderBy();
			if($order_by_f !== null) {
				$order_by = 'ORDER BY '.$order_by_f->name().' '.$field->orderDirection();
			}
			return ' GROUP_CONCAT('.$this->interpretField($field->argument()).' '.$order_by.' SEPARATOR \''.$field->separator().'\' )';
		} elseif( $field instanceof Derived\FromUnixtime ) {
			return ' FROM_UNIXTIME('.$this->interpretField($field->argument()).')';
		} elseif( $field instanceof Derived\Avg ) {
			return ' AVG('.$this->interpretField($field->argument()).')';
		} elseif( $field instanceof Derived\Max ) {
			return ' MAX('.$this->interpretField($field->argument()).')';
		} elseif( $field instanceof Derived\Min ) {
			return ' MIN('.$this->interpretField($field->argument()).')';
		} elseif( $field instanceof Derived\Plus ) {
			return $this->interpretField($field->left()).' +'.$this->interpretField($field->right());
		} elseif( $field instanceof Derived\Minus ) {
			return $this->interpretField($field->left()).' -'.$this->interpretField($field->right());
		} elseif( $field instanceof Derived\Quot ) {
			return $this->interpretField($field->left()).' /'.$this->interpretField($field->right());
		} elseif( $field instanceof Derived\Times ) {
			return $this->interpretField($field->left()).' *'.$this->interpretField($field->right());
		} elseif( $field instanceof Derived\Concat ) {
			$inbetween = $field->inbetween();
			if($inbetween === null) {
				$inbetween = ',';
			} else {
				$inbetween = ','.$this->gIldb->quote($inbetween,'text').',';
			}
			return ' CONCAT('.$this->interpretField($field->fieldOne()).$inbetween.$this->interpretField($field->fieldTwo()).')';
		} elseif( $field instanceof Derived\DateFormat ) {
			return ' DATE_FORMAT('.$this->interpretField($field->argument()).','.$this->gIldb->quote($field->format(),'text').')';
		} elseif( $field instanceof Derived\IfThenElse ) {
			return ' IF('.$this->interpretPredicate($field->condition()).','.$this->interpretField($field->met()).','.$this->interpretField($field->notMet()).')';
		} elseif($field instanceof Derived\ConstInt) {
			return $this->gIldb->quote($field->value(), 'integer');
		} elseif($field instanceof Derived\ConstString) {
			return $this->gIldb->quote($field->value(), 'text');
		} else {
			throw new TableRelationsException("Unknown field type".$field->name());
		}
	}

	/**
	 * Get the sql query corresponding to query object.
	 *
	 * @param	Tables\AbstractQuery	$query
	 * @return	string
	 */
	public function getSql($query) {
		return 
			"SELECT ".$this->requestedFields($query)
				.$this->from($query)
				.$this->join($query)
				.$this->where($query)
				.$this->groupBy($query)
				.$this->having($query)
				.$this->orderBy($query);
	}

	protected function orderBy($query) {
		$fields = $query->orderByFields();
		return count($fields) > 0 ? PHP_EOL.' ORDER BY '.implode(' '.strtoupper($query->orderByMode()).', ',$query->orderByFields()).' '.strtoupper($query->orderByMode()) : '';
	}

	protected function interpretTable(Tables\AbstractTable $table) {
		if($table instanceof Tables\Table) {
			return $table->title()." AS ".$table->id();
		} elseif($table instanceof Tables\DerivedTable) {
			return $this->interpretDerivedTable($table);
		} else {
			throw new TableRelationsException();
		}
	}

	protected function from(Tables\AbstractQuery $query) {
		return PHP_EOL." FROM ".$this->interpretTable($query->rootTable());
	}

	protected function interpretDerivedTable(Tables\DerivedTable $table) {
		return PHP_EOL."(".$this->getSql($table->space()->query()).") AS ".$table->id();
	}

	protected function interpretPredicate(Filter\Predicates\Predicate $predicate) {
		return $this->predicate_interpreter->interpret($predicate);
	}

	protected function join(Tables\AbstractQuery $query) {
		$joins = array();
		foreach($query as $table_id => $table) {
			$join = $this->interpretTable($table);
			$join_conditions = $query->currentJoinCondition();
			if(current($join_conditions) instanceof Tables\TableLeftJoin) {
				$join = " LEFT JOIN ".$join;
			} elseif(current($join_conditions) instanceof Tables\TableJoin) {
				$join = " JOIN ".$join;
			} else {
				throw new TableRelationsException("dunno condition");
			}
			$condition_aggregate = call_user_func_array(array($this->pf,"_ALL"),
				array_map(function ($condition) {return $condition->dependencyCondition();},$join_conditions));
			if($table->constraint()) {
				$condition_aggregate = $condition_aggregate->_AND($table->constraint());
			}
			$joins[] = $join." ON ".$this->interpretPredicate($condition_aggregate);
		}
		return count($joins) > 0 ? PHP_EOL.implode(PHP_EOL,$joins) : "";
	}

	protected function where(Tables\AbstractQuery $query) {
		$predicate = null;
		$root_constraint = $query->rootTable()->constraint();
		if($query->filter()) {
			$predicate = $query->filter();
			if($root_constraint) {
				$predicate = $predicate->_AND($root_constraint);
			}
			return PHP_EOL." WHERE ".$this->interpretPredicate($predicate);
		} elseif( $root_constraint) {
			return PHP_EOL." WHERE ".$this->interpretPredicate($root_constraint);
		}
		return "";
	}

	protected function having(Tables\AbstractQuery $query) {
		if($query->having()) {
			return PHP_EOL." HAVING ".$this->interpretPredicate($query->having());
		}
		return "";
	}


	protected function groupBy(Tables\AbstractQuery $query) {
		$group_by = array();
		foreach($query->groupBy() as $field) {
			$group_by[] = $field->name();
		}
		return count($group_by) > 0 ?  PHP_EOL." GROUP BY ".implode(", ",$group_by) : '';
	}
};
