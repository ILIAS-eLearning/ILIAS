<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class PresentationRow implements T\PresentationRow {
	use ComponentHelper;

	/**
	 * @var	string
	 */
	private $title_field;


	public function __construct($title_field) {
		$this->checkStringArg("string", $title_field);
		$this->title_field = $title_field;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitleField() {
		return $this->title_field;
	}

	/**
	 * @inheritdoc
	 */
	public function withSubtitleField() {
		$clone = clone $this;
		//$clone->abbreviation = $abbreviation;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withImportantFields() {
		$clone = clone $this;
		//$clone->abbreviation = $abbreviation;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withDescriptionFields() {
		$clone = clone $this;
		//$clone->abbreviation = $abbreviation;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withFurtherFields() {
		$clone = clone $this;
		//$clone->abbreviation = $abbreviation;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withData() {
		$clone = clone $this;
		//$clone->abbreviation = $abbreviation;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function withButtons() {
		$clone = clone $this;
		//$clone->abbreviation = $abbreviation;
		return $clone;
	}



}
