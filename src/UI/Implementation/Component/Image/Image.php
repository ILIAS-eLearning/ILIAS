<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Image;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * Class Image
 * @package ILIAS\UI\Implementation\Component\Image
 */
class Image implements C\Image\Image {
	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

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
	 * @var string
	 */
	protected $action = '';

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

	/**
	 * @inheritdoc
	 */
	public function withAction($action) {
		$this->checkStringOrSignalArg("action", $action);
		$clone = clone $this;
		if (is_string($action)) {
			$clone->action = $action;
		}
		else {
			$clone->action = null;
			$clone->setTriggeredSignal($action, "click");
		}

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getAction() {
		if ($this->action !== null) {
			return $this->action;
		}
		return $this->getTriggeredSignalsFor("click");
	}

	/**
	 * @inheritdoc
	 */
	public function withOnClick(Signal $signal) {
		return $this->withTriggeredSignal($signal, 'click');
	}

	/**
	 * @inheritdoc
	 */
	public function appendOnClick(Signal $signal) {
		return $this->appendTriggeredSignal($signal, 'click');
	}
}