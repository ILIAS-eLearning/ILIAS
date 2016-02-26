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
	 * @var	\CaT\Filter\Types\Type
	 */
	protected $result_type;

	public function __construct(\CaT\Filter\FilterFactory $factory, Filter $mapped, \Closure $mapper, \CaT\Filter\Types\Type $result_type) {
		$this->setFactory($factory);
		$this->mapped = $mapped;
		$this->mapper = $mapper;
		$this->result_type = $result_type;
	}

	public function mapped() {
		return $this->mapped;
	}

	/**
	 * @inheritdocs
	 */
	public function content_type() {
		return $this->result_type;
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
	protected function _content($input) {
		$res1 = $this->mapped->_content($input);
		$args = $this->mapped->content_type()->flatten($res1);
		$res2 = call_user_func_array($this->mapper, $args);
		assert('$this->check_result($res2)');
		return $res2;
	}

	private function check_result($res) {
		// TODO: implement this properly
		return true;
	}
}
