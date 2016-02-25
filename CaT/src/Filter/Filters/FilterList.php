<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

abstract class FilterList extends Filter {
	/**
	 * @var	Filter[]
	 */
	protected $subs;

	/**
	 * Set the sub filters.
	 *
	 * @param	Filter[]	$subs
	 */
	protected function setSubs($subs) {
		$this->subs = array_map(function(Filter $f) { return $f; }, $subs);
	}

	/**
	* Get the sub filters
	*
	* @return Filter[]
	*/
	public function subs() {
		return $this->subs;
	}

	/**
	 * Get the types of the sub filters inputs.
	 *
	 * @return	Type[]
	 */
	protected function subs_input_types() {
		return array_map(function ($s) { return $s->input_type(); }, $this->subs);
	}

	/**
	 * Get the types of the sub filters contents.
	 *
	 * @return	Type[]
	 */
	protected function subs_content_types() {
		return array_map(function ($s) { return $s->content_type(); }, $this->subs);
	}
}