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
	private $number;

	/**
	 * @param string	$type
	 * @param int		$number
	 */
	public function __construct($type, $number) {
		assert('is_int($number)');
		assert('self::is_valid_type($type)');
		$this->type = $type;
		$this->number = $number;
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
		assert('self::is_valid_type($type)');
		$clone = clone $this;
		$clone->type = $type;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getNumber() {
		return $this->number;
	}

	/**
	 * @inheritdoc
	 */
	public function withNumber($number) {
		assert('is_int($number)');
		$clone = clone $this;
		$clone->number = $number;
		return $clone;
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
		$tpl->setVariable('AMOUNT', $this->number());

		return $tpl->get();
	}*/

	// Helper
	static protected function is_valid_type($type) {
		static $types = array
			( self::NOVELTY
			, self::STATUS
			);
		return in_array($type, $types);
	}

}
