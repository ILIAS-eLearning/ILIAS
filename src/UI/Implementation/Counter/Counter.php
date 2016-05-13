<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Counter;

use ILIAS\UI\Component as C;

class Counter implements C\Counter {

	/**
	 * @var	string
	 */
	private $type;

	/**
	 * @var	int
	 */
	private $amount;

	/**
	 * @param string	$type
	 * @param int		$amount
	 */
	public function __construct($type, $amount) {
		assert('is_int($amount)');
		$this->type = $type;
		$this->amount = $amount;
	}

	/**
	 * @inheritdoc
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @inheritdoc
	 */
	public function withType($type) {
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @inheritdoc
	 */
	public function withAmount($amount) {
		return $this;
	}

	/**
	 * @throws \Exception
	 */
/*	public function to_html_string() {
		$type_class = '';
		switch (true) {
			case ($this->type instanceof E\NoveltyCounterType):
				$type_class = 'il-counter-novelty';
				break;
			case ($this->type instanceof E\StatusCounterType):
				$type_class = 'il-counter-status';
				break;
		}

		$tpl = new \ilTemplate('./src/UI/templates/default/Counter/tpl.counter.html', true, false);
		$tpl->setVariable('TYPE_CLASS', $type_class);
		$tpl->setVariable('AMOUNT', $this->amount());

		return $tpl->get();
	}*/
}
