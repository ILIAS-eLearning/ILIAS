<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Panel
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Panel implements C\Panel\Panel {
	use ComponentHelper;

	/**
	 * @var string
	 */
	private  $title;

	/**
	 * @var mixed content \ILIAS\UI\Component\Component[] | \ILIAS\UI\Component\Component
	 */
	private  $content;


	/**
	 * @param string $title
	 * @param mixed $content \ILIAS\UI\Component\Component[] | \ILIAS\UI\Component\Component
	 */
	public function __construct($title,$content) {
		$this->checkStringArg("title",$title);
		$content = $this->toArray($content);
		$types = [C\Component::class];
		$this->checkArgListElements("content", $content, $types);

		$this->title = $title;
		$this->content = $content;

		return $this;
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
	public function withContent($content){
		$content = $this->toArray($content);
		$types = [C\Component::class];
		$this->checkArgListElements("content", $content, $types);

		$clone = clone $this;
		$clone->content = $content;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getContent() {
		return $this->content;
	}
}
?>