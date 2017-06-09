<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data\Link;

/**
 * A link consists of a label and an URL.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Link {

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $url;


	public function __construct($label, $url) {
		assert('is_string($label)');
		assert('is_string($url)');
		if(trim($url) === '') {
			throw new \InvalidArgumentException("You MUST provide an URL.", 1);
		}
		$this->label = $label;
		$this->url = $url;
	}

	/**
	 * Return the label
	 *
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * Return the url
	 *
	 * @return string
	 */
	public function getURL() {
		return $this->url;
	}

}
