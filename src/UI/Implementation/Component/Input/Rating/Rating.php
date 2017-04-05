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
	 * @param 	string 		$topic
	 */
	public function __construct($topic) {
		$this->checkStringArg("string", $topic);
		$this->topic = $topic;
		$this->scale_captions = $this->fillCaptions(array());
		$this->byline = '';
	}

	/**
	 * @inheritdoc
	 */
	public function topic() {
		return $this->topic;
	}

	/**
	 * @inheritdoc
	 */
	public function withByline($byline) {
		$this->checkStringArg('string', $byline);
		$clone = clone $this;
		$clone->byline = $byline;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function byline() {
		return $this->byline;
	}

	/**
	 * @inheritdoc
	 */
	public function withCaptions($captions) {
		$captions = $this->toArray($captions);
		$types = array('string');
		$this->checkArgListElements('captions', $captions, $types);

		$clone = clone $this;
		$clone->scale_captions = $this->fillCaptions($captions);
		return $clone;
	}

	/**
	* Fill up (or truncate) captions to exactly five elements.
	*
	* @param 	string[] 	$captions
	*/
	private function fillCaptions($captions) {
		$scale_captions = array_fill(0, 5, '');
		$scale_captions =  array_replace($scale_captions, $captions);
		return array_slice($scale_captions, 0, 5);
	}

	/**
	 * @inheritdoc
	 */
	public function captions() {
		return $this->scale_captions;
	}



}
