<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class OneOf extends FilterList {
	/**
	 * @var	Filter[]
	 */
	protected $subs;

	public function __construct(\CaT\Filter\FilterFactory $factory, $label, $description, $subs) {
		$this->setFactory($factory);
		$this->setLabel($label);
		$this->setDescription($description);
		$this->setSubs($subs);
	}

	/**
	 * @inheritdocs
	 */
	public function content_type() {
		$opt_types = $this->subs_content_types();
		$tf = $this->factory->type_factory();
		return call_user_func_array(array($tf, "option"), $opt_types);
	}

	/**
	 * @inheritdocs
	 */
	public function input_type() {
		$opt_types = $this->subs_input_types();
		$tf = $this->factory->type_factory();
		return call_user_func_array(array($tf, "option"), $opt_types);
	}

	/**
	 * @inheritdocs
	 */
	protected function _content($input) {
		$choice = $input[0];
		return array($choice, $this->subs[$choice]->_content($input[1]));
	}
}
