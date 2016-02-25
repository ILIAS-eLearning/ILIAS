<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class Sequence extends FilterList {
	/**
	 * @var	Filter[]
	 */
	protected $subs;

	public function __construct(\CaT\Filter\FilterFactory $factory, $subs) {
		$this->setFactory($factory);
		$this->setSubs($subs);
	}

	/**
	 * @inheritdocs
	 */
	public function content_type() {
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
	protected function _content($input) {
		$res = array();
		$len = count($this->subs);

		for ($i = 0; $i < $len; $i++) {
			$sub = $this->subs[$i];
			$inp = $input[$i];
			$res[] = $sub->_content($inp);
		}

		return $res;
	}
}
