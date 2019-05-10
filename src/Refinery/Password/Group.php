<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Password;

use ILIAS\Data\Factory;
use ILIAS\Refinery\Password\Constraint\HasLowerChars;
use ILIAS\Refinery\Password\Constraint\HasMinLength;
use ILIAS\Refinery\Password\Constraint\HasNumbers;
use ILIAS\Refinery\Password\Constraint\HasSpecialChars;
use ILIAS\Refinery\Password\Constraint\HasUpperChars;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class Group
{
	/**
	 * @var ILIAS\Data\Factory
	 */
	protected $data_factory;

	/**
	 * @var \ilLanguage
	 */
	protected $lng;

	public function __construct(Factory $data_factory, \ilLanguage $lng) {
		$this->data_factory = $data_factory;
		$this->lng = $lng;
	}

	/**
	 * Get the constraint that a password has a minimum length.
	 *
	 * @param	int	$min_length
	 * @return HasMinLength
	 */
	public function hasMinLength($min_length) {
		return new HasMinLength($min_length, $this->data_factory, $this->lng);
	}

	/**
	 * Get the constraint that a password has upper case chars.
	 *
	 * @return HasUpperChars
	 */
	public function hasUpperChars() {
		return new HasUpperChars($this->data_factory, $this->lng);
	}

	/**
	 * Get the constraint that a password has lower case chars.
	 *
	 * @return HasLowerChars
	 */
	public function hasLowerChars() {
		return new HasLowerChars($this->data_factory, $this->lng);
	}

	/**
	 * Get the constraint that a password has numbers.
	 *
	 * @return HasNumbers
	 */
	public function hasNumbers() {
		return new HasNumbers($this->data_factory, $this->lng);
	}

	/**
	 * Get the constraint that a password has special chars.
	 *
	 * @return HasSpecialChars
	 */
	public function hasSpecialChars() {
		return new HasSpecialChars($this->data_factory, $this->lng);
	}
}
