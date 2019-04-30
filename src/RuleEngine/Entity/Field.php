<?php

namespace ILIAS\RuleEngine\Entity;

class Field {

	const FIELD_TYPE_TEXT = 'text'; // MySQL varchar, char
	const FIELD_TYPE_INTEGER = 'integer'; // MySQL tinyint, smallint, mediumint, int, bigint
	const FIELD_TYPE_FLOAT = 'float'; // MySQL double
	const FIELD_TYPE_DATE = 'date'; // MySQL date
	const FIELD_TYPE_TIME = 'time'; // MySQL time
	const FIELD_TYPE_TIMESTAMP = 'timestamp'; // MySQL datetime
	const FIELD_TYPE_CLOB = 'clob'; // MySQL longtext

	/**
	 * @var string
	 */
	protected $name;
	/**
	 * @var string
	 */
	protected $type;


	/**
	 * @param string $name
	 * @param string $type
	 */
	public function __construct($name, $type)	{
		$this->setName($name);
		$this->setType($type);
	}


	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}


	/**
	 * @param string $name
	 */
	public function setName(string $name) {
		$this->name = $name;
	}


	/**
	 * @return int
	 */
	public function getType(): string {
		return $this->type;
	}


	/**
	 * @param int $type
	 */
	public function setType(string $type) {
		$this->type = $type;
	}
}