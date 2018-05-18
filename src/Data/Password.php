<?php
/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

/**
 *
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Password {

	/**
	 * @var string
	 */
	private $pass;

	public function __construct($pass) {
		if(!is_string($pass)) {
			throw new \InvalidArgumentException('Invalid value for $pass');
		}
		$this->pass = $pass;
	}

	/**
	 * Get the password-string.
	 *
	 * @return  string
	 */
	public function getPassword() {
		return $this->pass;
	}
}