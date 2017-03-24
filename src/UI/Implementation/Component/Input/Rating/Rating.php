<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Rating;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Rating. Implements a rating input.
 *
 * @author	Nils Haagen <nils.haagen@concepts-and-training.de>
 * @package ILIAS\UI\Implementation\Component\Input\Rating
 */
class Rating implements C\Input\Rating\Rating {
	use ComponentHelper;

	/**
	 * @var		string
	 */
	private $topic;

	/**
	 * @var		string
	 */
	private $byline;

	/**
	 * @var		string[]
	 */
	private $scale_captions;


	/**
	 * @param 	string 	$topic
	 */
	public function __construct($topic, $byline='') {
		$this->topic = $topic;
		$this->byline = $byline;
		$this->scale_captions = array_fill(0, 5, '');
	}


	/**
	 * @inheritdoc
	 */
	public function withTopic($topic) {
		$clone = clone $this;
		$clone->topic = $topic;
		return $clone;
	}

	/**
	 * get the topic
	 * @return string
	 */
	public function topic() {
		return $this->topic;
	}

	/**
	 * @inheritdoc
	 */
	public function withByline($byline) {
		$clone = clone $this;
		$clone->byline = $byline;
		return $clone;
	}

	/**
	 * get the byline
	 * @return string
	 */
	public function byline() {
		return $this->byline;
	}

	/**
	 * @inheritdoc
	 */
	public function withCaptions(array $scale_captions) {
		$clone = clone $this;
		$clone->scale_captions = $scale_captions;
		return $clone;
	}

	/**
	 * get all labels for the scale-positions
	 * @return string[]
	 */
	public function captions() {
		return $this->scale_captions;
	}


}



