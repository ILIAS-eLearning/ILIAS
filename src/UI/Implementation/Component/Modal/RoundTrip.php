<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Modal;

use ILIAS\UI\Component as Component;
use ILIAS\UI\Component\Button;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class RoundTrip extends Modal implements Component\Modal\RoundTrip {

	use ModalHelper;
	/**
	 * @var Button\Button[]
	 */
	protected $action_buttons;
	/**
	 * @var string
	 */
	protected $title;
	/**
	 * @var Component\Component[]
	 */
	protected $content;
	/**
	 * @var string
	 */
	protected $cancel_button_label = 'cancel';


	/**
	 * @param string                                    $title
	 * @param Component\Component|Component\Component[] $content
	 * @param string                                    $cancel_button_label
	 */
	public function __construct($title, $content, $cancel_button_label = 'cancel') {
		$this->checkStringArg('title', $title);
		$content = $this->toArray($content);
		$types = array( Component\Component::class );
		$this->checkArgListElements('content', $content, $types);
		$this->title = $title;
		$this->content = $content;
		$this->cancel_button_label = $cancel_button_label;
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
		return $this->getContent();
	}


	/**
	 * @inheritdoc
	 */
	public function getActionButtons() {
		return $this->action_buttons;
	}


	/**
	 * @inheritdoc
	 */
	public function withTitle($title) {
		$this->checkStringArg('title', $title);
		$clone = clone $this;
		$clone->title = $title;

		return $clone;
	}


	/**
	 * @inheritdoc
	 */
	public function withContent($content) {
		$content = $this->toArray($content);
		$types = array( Component\Component::class );
		$this->checkArgListElements('content', $content, $types);
		$clone = clone $this;
		$clone->content = $content;

		return $clone;
	}


	/**
	 * @inheritdoc
	 */
	public function withActionButtons(array $buttons) {
		$types = array( Button\Button::class );
		$this->checkArgListElements('buttons', $buttons, $types);
		$clone = clone $this;
		$clone->action_buttons = $buttons;

		return $clone;
	}
}
