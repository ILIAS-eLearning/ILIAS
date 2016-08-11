<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Generic;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Generic
 * @package ILIAS\UI\Implementation\Component\Generic
 */
class Generic implements C\Generic\Generic {
	use ComponentHelper;

	/**
	 * @var	string
	 */
	private $content;


	/**
	 * Generic constructor.
	 * @param string $content
	 */
	public function __construct($content) {
		$this->checkStringArg("content", $content);

		$this->content = $content;
	}

	/**
	 * @inheritdoc
	 */
	public function getContent(){
		return $this->content;
	}

	/**
	 * @inheritdoc
	 */
	public function withContent($content){
		$this->checkStringArg("content", $content);

		$clone = clone $this;
		$clone->content = $content;
		return $clone;
	}

}
?>