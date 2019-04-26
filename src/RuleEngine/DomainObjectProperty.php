<?php

namespace ILIAS\RuleEngine;

/**
 * Interface DomainObjectProperty
 *
 * @author Martin Studer ms@studer-raimann.ch
 */
interface DomainObjectProperty {

	/**
	 * DtoPropertyInterface constructor.
	 *
	 * @param string $classname ILIAS\DomainObject::class
	 * @param string $property_key
	 */
	public function __construct(string $classname, string $property_key);


	/**
	 * @return int|string
	 */
	public function getValue();
}