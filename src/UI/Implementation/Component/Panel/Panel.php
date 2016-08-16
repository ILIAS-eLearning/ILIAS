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
	protected  $title;

	/**
	 * @var \ILIAS\UI\Component\Component[] | \ILIAS\UI\Component\Component
	 */
	private  $content;


	/**
	 * @param string $title
	 * @param \ILIAS\UI\Component\Component[] | \ILIAS\UI\Component\Component $content
	 */
	public function __construct($title,$content) {
		$this->checkStringArg("title",$title);
		$content = $this->toArray($content);
		$types = [C\Component::class];
		$this->checkArgListElements("content", $content, $types);

		$this->title = $title;
		$this->content = $content;
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
	public function getContent() {
		return $this->content;
	}
}
?>