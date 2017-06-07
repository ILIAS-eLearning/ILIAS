<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Icon;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Icon implements C\Icon\Icon {
	use ComponentHelper;

	/**
	 * @var	string
	 */
	private $css_class;

	/**
	 * @var	string
	 */
	private $aria_label;

	/**
	 * @var	string
	 */
	private $size;

	/**
	 * @var	string
	 */
	private $abbreviation;

	/**
	 * @var	string[]
	 */
	private static $possible_sizes = array(
		'small',
		'medium',
		'large'
	);

	public function __construct($class, $aria_label, $size, $abbreviation) {
		$this->checkStringArg("string", $class);
		$this->checkStringArg("string", $aria_label);
		$this->checkStringArg("string", $abbreviation);
		$this->checkArgIsElement(
			"size", $size,
			self::$possible_sizes,
			implode(self::$possible_sizes, '/')
		);
		$this->css_class = $class;
		$this->aria_label = $aria_label;
		$this->size = $size;
		$this->abbreviation = $abbreviation;
	}

	/**
	 * @inheritdoc
	 */
	public function cssclass(){
		return $this->css_class;
	}

	/**
	 * @inheritdoc
	 */
	public function aria(){
		return $this->aria_label;
	}

	/**
	 * @inheritdoc
	 */
	public function abbreviation(){
		return $this->abbreviation;
	}

	/**
	 * @inheritdoc
	 */
	public function withSize($size){
		$this->checkArgIsElement(
			"size", $size,
			self::$possible_sizes,
			implode(self::$possible_sizes, '/')
		);
		$clone = clone $this;
		$clone->size = $size;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function size(){
		return $this->size;
	}
}
