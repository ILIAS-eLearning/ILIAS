<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class Mapped extends Filter {
	/**
	 * @var	Filter
	 */
	protected $mapped;

	/**
	 * @var	\Closure
	 */
	protected $mapper;

	/**
	 * @var	array
	 */
	protected $result_types;

	public function __construct(\CaT\Filter\FilterFactory $factory, Filter $mapped, \Closure $mapper, $result_types) {
		$this->setFactory($factory);
		$this->mapped = $mapped;
		$this->mapper = $mapper;
		$this->result_types = $result_types;
	}

	/**
	 * @inheritdocs
	 */
	public function content_type() {
		return $this->result_types;
	}

	/**
	 * @inheritdocs
	 */
	public function input_type() {
		return $this->mapped->input_type();
	}

	/**
	 * @inheritdocs
	 */
	public function content(/*...$inputs*/) {
		$inputs = func_get_args();
		$_res = call_user_func_array(array($this->mapped, "content"), $inputs);
		if (count($this->mapped->content_type()) == 1) {
			$_res = array($_res);
		}
		$res = call_user_func_array($this->mapper, $_res);
		assert('$this->check_result($res)');
		return $res;
	}

	private function check_result($res) {
		// TODO: implement this properly
		return true;
	}
}
