<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Item;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Common interface to all items.
 */
abstract class Item implements C\Item\Item {

	use ComponentHelper;

	/**
	 * @var int marker id 0-32 (0 no marker)
	 */
	protected $marker_id = 0;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $desc;

	/**
	 * @var array
	 */
	protected $props;

	/**
	 * @var array
	 */
	protected $actions;

	public function __construct($title) {
		$this->checkStringArg("title", $title);
		$this->title = $title;
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
	public function withDescription($desc) {
		$this->checkStringArg("description", $desc);
		$clone = clone $this;
		$clone->desc = $desc;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription(){
		return $this->desc;
	}

	/**
	 * @inheritdoc
	 */
	public function withProperties(array $props) {
		$clone = clone $this;
		$clone->props = $props;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getProperties(){
		return $this->props;
	}

	/**
	 * @inheritdoc
	 */
	public function withActions(array $actions) {
		$clone = clone $this;
		$clone->actions = $actions;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getActions(){
		return $this->actions;
	}

	/**
	 * @inheritdoc
	 */
	public function withMarkerId($marker_id){
		$this->checkIntArg("marker_id", $marker_id);

		$clone = clone $this;
		$clone->marker_id = $marker_id;

		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function getMarkerId(){
		return $this->marker_id;
	}
}
