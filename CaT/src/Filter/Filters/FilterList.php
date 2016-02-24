<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class FilterList extends Filter {
	/**
	 * @var	Filter[]
	 */
	protected $subs;

	/**
	 * @inheritdocs
	 */
	public function content_type() {
	}

	/**
	 * @inheritdocs
	 */
	public function input_type() {
	}

	/**
	 * @inheritdocs
	 */
	public function content(/*...$inputs*/) {
	}

	/**
	* set the subs
	*
	* @param $subs 		array of Filter
	*/
	protected function setSubs($subs) {
		$this->subs = array_map(function(Filter $f) { return $f; }, $subs);
	}

	/**
	* get the subs
	*
	* @return $subs 	array of Filter
	*/
	public function subs() {
		return $this->subs;
	}
}
