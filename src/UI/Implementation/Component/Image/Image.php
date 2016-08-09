<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Image;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Image
 * @package ILIAS\UI\Implementation\Component\Image
 */
class Image implements C\Image\Image {
	use ComponentHelper;

	/**
	 * @var	string
	 */
	private $type;

	/**
	 * @var	string
	 */
	private  $src;

	/**
	 * @var	string
	 */
	private  $alt;

	/**
	 * @var []
	 */
	private static $types = [
			self::STANDARD,
			self::RESPONSIVE
	];

	/**
	 * @inheritdoc
	 */
	public function __construct($type, $source, $alt) {
		$this->checkStringArg("src", $source);
		$this->checkStringArg("alt", $alt);
		$this->checkArgIsElement("type", $type, self::$types, "image type");

		$this->type = $type;
		$this->src = $source;
		$this->alt = $alt;

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
	public function withSource($source){
		$this->checkStringArg("src", $source);

		$clone = clone $this;
		$clone->src = $source;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getSource() {
		return $this->src;
	}

	/**
	 * @inheritdoc
	 */
	public function withAlt($alt){
		$this->checkStringArg("alt", $alt);

		$clone = clone $this;
		$clone->alt = $alt;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getAlt() {
		return $this->alt;
	}
}
?>