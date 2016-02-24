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
		$res = array();
		$i = 0;

		foreach ($this->subs as $sub) {
			$amount_args = count($sub->input_type());
			$args = array_slice($inputs, $i, $amount_args);
			$i += $amount_args;
			$r = call_user_func_array(array($sub, "content"), $args);
			if (count($sub->content_type()) > 1) {
				$res = array_merge($res, $r);
			}
			else {
				$res[] = $r;
			}
		}

		return $res;
	}
}
