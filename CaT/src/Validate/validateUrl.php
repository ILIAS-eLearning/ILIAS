<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

namespace CaT\Validate;

/**
 * Vlaidator class for every possible validation in an url
 */
class validateUrl {
	/**
	 * checks given url string for legal prefix
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public function validUrlPrefix($url) {
		assert('is_string($url)');
		assert('$url !== ""');
		$reg_exp = "/^(https:\/\/)|(http:\/\/)[\w]+/";

		return preg_match("/^(https:\/\/)|(http:\/\/)[\w]+/", $url) === 1;
	}
}