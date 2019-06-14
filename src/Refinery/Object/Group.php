<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Object;

use ILIAS\Data\Factory;

/**
 * Class Group
 *
 * @package ILIAS\Refinery\Object
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class Group {

	/**
	 * @var Factory
	 */
	private $dataFactory;


	public function __construct(Factory $dataFactory) {
		$this->dataFactory = $dataFactory;
	}


	/**
	 * Creates a transformation to a Serialized Object
	 *
	 * Domain-Driven Design in PHP, 2014 - 2016 Carlos Buenosvinos, Christian Soronellas
	 * and Keyvan Akbary, Chapter: 3.7.1.4.1:
	 * <<...serialize/unserialize native PHP strategies have a problem when dealing
	 * with class and namespace refactoring. One alternative is use your own
	 * serialization mechanism for example, concatenating the amount,
	 * a one character separator such as “|” an the currency ISO code.>>
	 *
	 * @param $object $delimiter
	 *
	 * @return JsonSerializedObject
	 */
	public function JsonSerializedObject(): JsonSerializedObject {
		return new JsonSerializedObject($this->dataFactory);
	}
}
