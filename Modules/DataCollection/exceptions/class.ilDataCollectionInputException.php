<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Exceptions/classes/class.ilException.php';

/**
 * Class ilDataCollectionField
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionInputException extends ilException {

	const TYPE_EXCEPTION = 0;
	const LENGTH_EXCEPTION = 1;
	const REGEX_EXCEPTION = 2;
	const UNIQUE_EXCEPTION = 3;
	const NOT_URL = 4;
	const NOT_IMAGE = 5;
	/**
	 * @var int
	 */
	protected $exception_type;


	/**
	 * @param string $exception_type
	 */
	public function __construct($exception_type) {
		parent::__construct($exception_type);
		$this->exception_type = $exception_type;
	}


	/**
	 * @return string
	 */
	public function getExceptionType() {
		return $this->exception_type;
	}


	/**
	 * @return string
	 */
	public function __toString() {
		global $lng;

		switch ($this->exception_type) {
			case self::TYPE_EXCEPTION:
				return $lng->txt('dcl_wrong_input_type');
			case self::LENGTH_EXCEPTION:
				return $lng->txt('dcl_wrong_length');
			case self::REGEX_EXCEPTION:
				return $lng->txt('dcl_wrong_regex');
			case self::UNIQUE_EXCEPTION:
				return $lng->txt('dcl_unique_exception');
			case self::NOT_URL:
				return $lng->txt('dcl_noturl_exception');
			case self::NOT_IMAGE:
				return $lng->txt('dcl_notimage_exception');
			default:
				return $lng->txt('dcl_unknown_exception');
		}
	}
}
