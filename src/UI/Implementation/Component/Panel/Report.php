<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Panel
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Report implements C\Panel\Report {
	use ComponentHelper;

	/**
	 * @var string
	 */
	private  $title;

	/**
	 * @var \ILIAS\UI\Component\Panel\Sub[]
	 */
	private  $sub_panels;


	/**
	 * @param string $title
	 * @param \ILIAS\UI\Component\Panel\Sub[]
	 */
	public function __construct($title,$sub_panels) {
		$this->checkStringArg("title",$title);
		$sub_panels= $this->toArray($sub_panels);
		$types = [C\Panel\Sub::class];
		$this->checkArgListElements("content", $sub_panels, $types);

		$this->title = $title;
		$this->sub_panels = $sub_panels;
	}

	/**
	 * @inheritdoc
	 */
	public function withTitle($title){
		$this->checkStringArg("title", $title);

		$clone = clone $this;
		$clone->title = $title;
		return $clone;
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
	public function withSubPanels($sub_panels){
		$sub_panels = $this->toArray($sub_panels);
		$types = [C\Panel\Sub::class];
		$this->checkArgListElements("content", $sub_panels, $types);

		$clone = clone $this;
		$clone->sub_panels = $sub_panels;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getSubPanels() {
		return $this->sub_panels;
	}
}
?>