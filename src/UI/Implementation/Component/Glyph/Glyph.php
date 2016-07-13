<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Glyph;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Counter\Counter;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Glyph implements C\Glyph\Glyph {
	use ComponentHelper;

	/**
	 * @var	string
	 */
	private $type;

	/**
	 * @var	string
	 */
	private $action;

	/**
	 * @var	C\Counter[]
	 */
	private $counters;

	private static $types = array
		(self::SETTINGS
		, self::COLLAPSE
		, self::EXPAND
		, self::ADD
		, self::REMOVE
		, self::UP
		, self::DOWN
		, self::BACK
		, self::NEXT
		, self::SORT
		, self::USER
		, self::MAIL
		, self::NOTIFICATION
		, self::TAG
		, self::NOTE
		, self::COMMENT
		);


	/**
	 * @param string		$type
	 * @param string		$action
	 */
	public function __construct($type, $action) {
		$this->checkArgIsElement("type", $type, self::$types, "glyph type");
		$this->checkStringArg("action", $action);
		$this->type = $type;
		$this->action = $action;
		$this->counters = array();
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
	public function getAction() {
		return $this->action;
	}

	/**
	 * @inheritdoc
	 */
	public function getCounters() {
		return array_values($this->counters);
	}

	/**
	 * @inheritdoc
	 */
	public function withCounter(Counter $counter) {
		$clone = clone $this;
		$clone->counters[$counter->getType()] = $counter;
		return $clone;
	}
}
