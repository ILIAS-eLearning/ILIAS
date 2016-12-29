<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Trigger\TriggerAction;

/**
 * Base class for modals
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
abstract class Modal implements Component\Modal\Modal {

	use ComponentHelper;
	use JavaScriptBindable;


//	/**
//	 * @param string              $title Title of the modal
//	 * @param Component\Component $content
//	 */
//	public function __construct($title, Component\Component $content) {
//		$this->checkStringArg('title', $title);
//		$this->checkArgInstanceOf('content', $content, Component\Component::class);
//		$this->title = $title;
//		$this->content = $content;
//	}
//
//
//	/**
//	 * @inheritdoc
//	 */
//	public function getTitle() {
//		return $this->title;
//	}
//
//
//	/**
//	 * @inheritdoc
//	 */
//	public function getContent() {
//		return $this->content;
//	}
//
//
//	/**
//	 * @inheritdoc
//	 */
//	public function withTitle($title) {
//		$this->checkStringArg('title', $title);
//		$clone = clone $this;
//		$clone->title = $title;
//
//		return $clone;
//	}
//
//
//	/**
//	 * @inheritdoc
//	 */
//	public function withContent(Component\Component $content) {
//		$this->checkArgInstanceOf('content', $content, Component\Component::class);
//		$clone = clone $this;
//		$clone->content = $content;
//
//		return $clone;
//	}


	/**
	 * @inheritdoc
	 */
	public function getShowAction() {
		return new ShowAction($this);
	}


	/**
	 * @inheritdoc
	 */
	public function getCloseAction() {
		return new CloseAction($this);
	}


	/**
	 * @inheritdoc
	 */
	public function getButtons() {
		return $this->buttons;
	}
}
