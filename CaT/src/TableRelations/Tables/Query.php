<?php

namespace CaT\TableRelations\Tables;

use CaT\Filter\Predicates as Predicates;
/**
 * @inheritdoc
 */
class Query {

	public function valid() {
		return $this->path->valid();
	}

	public function key() {
		return $this->path->key();
	}

	public function current() {
		return $this->path->current();
	}

	public function next() {
		$this->current++;
		return $this->path->next();
	}

	/**
	 * We ignore root table in $path.
	 */
	public function rewind() {
		$this->path->rewind();
		$this->path->next();
	}

	public function currentJoinCondition() {
		
	}

	/**
	 * @inheritdoc
	 */
	public function requested() {

	}

	/**
	 * @inheritdoc
	 */
	public function rootTable() {

	}

	/**
	 * @inheritdoc
	 */
	public function having() {}


	/**
	 * @inheritdoc
	 */
	public function groupedBy() {}	
}