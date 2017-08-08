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

	/**
	 * @var	string
	 */
	private $subtitle_field;

	/**
	 * @var	array
	 */
	private $data;

	/**
	 * @var	array
	 */
	private $buttons;


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
	public function withSubtitleField($subtitle_field) {
		$this->checkStringArg("string", $subtitle_field);
		$clone = clone $this;
		$clone->subtitle_field = $subtitle_field;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getSubtitleField() {
		return $this->subtitle_field;
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
	public function withData($data) {
		$clone = clone $this;
		$clone->data = $data;
		return $clone;
	}
	/**
	 * @inheritdoc
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @inheritdoc
	 */
	public function withButtons(array $buttons) {
		$clone = clone $this;
		$clone->buttons = $buttons;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getButtons() {
		return $this->buttons;
	}



}
