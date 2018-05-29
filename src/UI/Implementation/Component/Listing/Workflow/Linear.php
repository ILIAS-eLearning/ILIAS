<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\Workflow;

use ILIAS\UI\Component as C;

/**
 * Class Linear
 * @package ILIAS\UI\Implementation\Component\Listing\Workflow
 */
class Linear extends Workflow implements C\Listing\Workflow\Linear {

	/**
	 * @var mixed
	 */
	protected $orientation;

	/**
	 * Workflow constructor.
	 * @param 	string 	$title
	 * @param 	Step[] 	$steps
	 */
	public function __construct($title, array $steps) {
		$this->orientation = static::HORIZONTAL;
		parent::__construct($title, $steps);
	}

	/**
	 * @inheritdoc
	 */
	public function getOrientation() {
		return $this->orientation;
	}

	/**
	 * @inheritdoc
	 */
	public function withOrientation($orientation) {
		$this->checkArgIsElement('orientation', $orientation, [static::HORIZONTAL, static::VERTICAL], 'orientation');
		$clone = clone $this;
		$clone->orientation = $orientation;
		return $clone;
	}


}