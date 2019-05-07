<?php

/* Copyright (c) 2016 Amstutz Timon <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Card;

use ILIAS\UI\Component\Card as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

class Card implements C\Card {
	use ComponentHelper;
	use JavaScriptBindable;
	use Triggerer;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var \ILIAS\UI\Component\Component
	 */
	protected $header_section;

	/**
	 * @var \ILIAS\UI\Component\Component[]
	 */
	protected $content_sections;

	/**
	 * @var \ILIAS\UI\Component\Image\Image
	 */
	protected $image;

	/**
	 * @var string|Signal[]
	 */
	protected $title_action = '';

	/**
	 * @var boolean
	 */
	protected $highlight = false;

	/**
	 * @param $title
	 * @param \ILIAS\UI\Component\Image\Image|null $image
	 */
	public function __construct($title, \ILIAS\UI\Component\Image\Image $image = null){
		$this->checkStringArg("title", $title);

		$this->title = $title;
		$this->image = $image;
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
	public function getTitle(){
		return $this->title;
	}

	/**
	 * @inheritdoc
	 */
	public function withImage(\ILIAS\UI\Component\Image\Image $image){
		$clone = clone $this;
		$clone->image = $image;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getImage(){
		return $this->image;
	}

	/**
	 * @inheritdoc
	 */
	public function withSections(array $sections){
		$classes = [\ILIAS\UI\Component\Component::class];
		$this->checkArgListElements("sections",$sections,$classes);

		$clone = clone $this;
		$clone->content_sections = $sections;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getSections(){
		return $this->content_sections;
	}

	/**
	 * @inheritdoc
	 */
	public function withTitleAction($action) {
		$this->checkStringOrSignalArg("title_action", $action);

		$clone = clone $this;
		if (is_string($action)) {
			$clone->title_action = $action;
		}
		else {
			$clone->title_action = null;
			$clone->setTriggeredSignal($action, "click");
		}

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getTitleAction() {
		if ($this->title_action !== null) {
			return $this->title_action;
		}
		return $this->getTriggeredSignalsFor("click");
	}

	/**
	 * @inheritdoc
	 */
	public function withHighlight($status) {
		$clone = clone $this;
		$clone->highlight = $status;

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function isHighlighted() {
		return $this->highlight;
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
