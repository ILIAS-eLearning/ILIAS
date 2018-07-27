<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Component as C;

/**
 * This implements a Standard Filter.
 */
class Standard extends Filter implements C\Input\Container\Filter\Standard {

	/**
	 * @var string
	 */
	protected $post_url;


	public function __construct($post_url, array $inputs) {
		parent::__construct($inputs);
		$this->checkStringArg("post_url", $post_url);
		$this->post_url = $post_url;
	}


	/**
	 * @inheritdoc
	 */
	public function getPostURL() {
		return $this->post_url;
	}
}
