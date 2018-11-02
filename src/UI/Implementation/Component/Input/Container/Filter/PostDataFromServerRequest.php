<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Implementation\Component\Input\PostData;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Implements interaction of input element with post data from
 * psr-7 server request.
 */
class PostDataFromServerRequest implements PostData {

	/**
	 * @var    array
	 */
	protected $query_params;


	public function __construct(ServerRequestInterface $request) {
		$this->query_params = $request->getQueryParams();
		$ar = array();
		foreach ($this->query_params as $name => $value) {
			if (strpos($name, "filter") !== false) {  // only the inputs from GET
				$ar[$name] = $value;
			}
		}
		$this->query_params = $ar;
	}


	/**
	 * @inheritdocs
	 */
	public function get($name) {
		if (!isset($this->query_params[$name])) {
			throw new \LogicException("'$name' is not contained in posted data.");
		}

		return $this->query_params[$name];
	}


	/**
	 * @inheritdocs
	 */
	public function getOr($name, $default) {
		if (!isset($this->query_params[$name])) {
			return $default;
		}

		return $this->query_params[$name];
	}
}
