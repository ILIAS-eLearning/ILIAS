<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class OneOf extends Filter {
	/**
	 * @var	Filter[]
	 */
	protected $subs;

	public function __construct(\CaT\Filter\FilterFactory $factory, $label, $description, $subs) {
		$this->setFactory($factory);
		$this->setLabel($label);
		$this->setDescription($description);
		$this->subs = array_map(function(Filter $f) { return $f; }, $subs);
	}

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
		$inputs = func_get_args();
		assert('count($inputs) == 2');
		$choice = $inputs[0];
		$data = $inputs[1];
		assert('$choice < count($this->subs)');
		return call_user_func_array(array($this->subs[0], "content"), $data);
	}
}
