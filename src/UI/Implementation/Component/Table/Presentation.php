<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Presentation implements T\Presentation {
	use ComponentHelper;

	/**
	 * @var	string
	 */
	private $title;

	/**
	 * @var ILIAS\UI\Component\ViewControl[]
	 */
	private $view_controls;

	/**
	 * @var T\PresentationRow[]
	 */
	private $rows;

	public function __construct($title, array $view_controls, array $rows) {
		$this->checkStringArg("string", $title);
		$this->title = $title;

		$this->view_controls = $view_controls;
		$this->rows = $rows;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @inheritdoc
	 */
	public function getRows() {
		return $this->rows;
	}

	/**
	 * @inheritdoc
	 */
	public function getViewControls() {
		return $this->view_controls;
	}

}
