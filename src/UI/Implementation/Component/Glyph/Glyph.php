<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Glyph;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Counter\Counter;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;

class Glyph implements C\Glyph\Glyph {
	use ComponentHelper;
	use JavaScriptBindable;

	/**
	 * @var	string
	 */
	private $type;

	/**
	 * @var	string|null
	 */
	private $action;

	/**
	 * @var	string
	 */
	private $aria_label;

	/**
	 * @var	C\Counter[]
	 */
	private $counters;

	/**
	 * @var bool
	 */
	private $highlighted = false;

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
		, self::SORT_ASCENDING
		, self::SORT_DESCENDING
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
	 * @param string|null	$action
	 */
	public function __construct($type, $aria_label, $action = null) {
		$this->checkArgIsElement("type", $type, self::$types, "glyph type");
		$this->checkStringArg("string",$aria_label);

		if ($action !== null) {
			$this->checkStringArg("action", $action);
		}
		$this->type = $type;
		$this->aria_label = $aria_label;
		$this->action = $action;
		$this->counters = array();
		$this->highlighted = false;
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
	public function getAriaLabel() {
		return $this->aria_label;
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

	/**
	 * @return bool
	 */
	public function isHighlighted(){
		return $this->highlighted;
	}

	/**
	 * @inheritdoc
	 */
	public function withHighlight() {
		$clone = clone $this;
		$clone->highlighted = true;
		return $clone;
	}
}
