<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Filters;

class Sequence extends FilterList {
	/**
	 * @var	Filter[]
	 */
	protected $subs;

	public function __construct(\ILIAS\TMS\Filter\FilterFactory $factory, $subs,
								array $mappings = array(), array $mapping_result_types = array()) {
		$this->setFactory($factory);
		$this->setSubs($subs);
		$this->setMappings($mappings, $mapping_result_types);
	}

	/**
	 * @inheritdocs
	 */
	public function original_content_type() {
		$tup_types = $this->subs_content_types();
		$tf = $this->factory->type_factory();
		return call_user_func_array(array($tf, "tuple"), $tup_types);
	}

	/**
	 * @inheritdocs
	 */
	public function input_type() {
		$tup_types = $this->subs_input_types();
		$tf = $this->factory->type_factory();
		return call_user_func_array(array($tf, "tuple"), $tup_types);
	}

	/**
	 * @inheritdocs
	 */
	protected function raw_content($input) {
		$res = array();
		$len = count($this->subs);

		for ($i = 0; $i < $len; $i++) {
			$sub = $this->subs[$i];
			$inp = $input[$i];
			$res[] = $sub->mapped_content($inp);
		}

		return $res;
	}

	/**
	 * @inheritdocs
	 */
	protected function clone_with_new_mappings($mappings, $mapping_result_types) {
		return new Sequence($this->factory, $this->subs(), $mappings, $mapping_result_types);
	}
}
