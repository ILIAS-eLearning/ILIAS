<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;

class Tag extends Button implements C\Button\Tag {

	private static $relevance_levels = array (
		 self::RELLOW,
		 self::RELVERYLOW,
		 self::RELMID,
		 self::RELHIGH,
		 self::RELVERYHIGH
	);

	/**
	 * @var int
	 */
	protected $relevance = 5;

	/**
	 * @var Color
	 */
	protected $bgcol;

	/**
	 * @var Color
	 */
	protected $forecol;

	/**
	 * @var string[]
	 */
	protected $css_classes;

	/**
	 * @inheritdoc
	 */
	public function withRelevance($relevance) {
		$this->checkIntArg("relevance", $relevance);
		if($relevance < 1 || $relevance > 5) {
			throw new \InvalidArgumentException("Relevance must be between 1 and 5", 1);
		}
		$clone = clone $this;
		$clone->relevance = $relevance;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getRelevance() {
		return $this->relevance;
	}

	/**
	 * @inheritdoc
	 */
	public function getRelevanceClass() {
		return self::$relevance_levels[$this->relevance - 1];
	}

	/**
	 * @inheritdoc
	 */
	public function withBackgroundColor($col) {
		$this->checkArgInstanceOf('Color', $col, \ILIAS\Data\Color::class);
		$clone = clone $this;
		$clone->bgcol = $col;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getBackgroundColor() {
		return $this->bgcol;
	}

	/**
	 * @inheritdoc
	 */
	public function withForegroundColor($col) {
		$this->checkArgInstanceOf('Color', $col, \ILIAS\Data\Color::class);
		$clone = clone $this;
		$clone->forecol = $col;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getForegroundColor() {
		return $this->forecol;
	}

	/**
	 * @inheritdoc
	 */
	public function withClasses($classes) {
		$classes = $this->toArray($classes);
		foreach ($classes as $class) {
			$this->checkStringArg('classes', $class);
		}
		$clone = clone $this;
		$clone->css_classes = $classes;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getAdditionalClasses() {
		if(!$this->css_classes) {
			return array();
		}
		return $this->css_classes;
	}

	/**
	 * @inheritdoc
	 */
	public function getCSSClasses() {
		$classes = array_merge(
			array($this->getRelevanceClass()),
			$this->getAdditionalClasses()
		);
		return trim(join(' ', $classes));
	}

}
