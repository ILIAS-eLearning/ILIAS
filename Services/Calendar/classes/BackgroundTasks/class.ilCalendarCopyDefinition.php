<?php

use ILIAS\BackgroundTasks\Implementation\Values\AbstractValue;
use ILIAS\BackgroundTasks\Value;

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilCalendarCopyDefinition extends AbstractValue
{
	const COPY_SOURCE_DIR = 'source';
	const COPY_TARGET_DIR = 'target';
	
	/**
	 * Copy Jobs: source file => relative target file in zip directory.
	 * @param string[] 
	 */
	private $copy_definitions = [];
	
	
	/**
	 * Get copy definitions
	 * @return string[]
	 */
	public function getCopyDefinitions()
	{
		return $this->copy_definitions;
	}
	
	/**
	 * Set copy definitions
	 * @param string[] $a_definitions
	 */
	public function setCopyDefinitions($a_definitions)
	{
		$this->copy_definitions = $a_definitions;
	}
	
	/**
	 * Add copy definition
	 * @param string $a_source
	 * @param string $a_target
	 */
	public function addCopyDefinition($a_source, $a_target)
	{
		$this->copy_definitions[] = 
			[
				self::COPY_SOURCE_DIR => $a_source,
				self::COPY_TARGET_DIR => $a_target
			];
	}
	

	/**
	 * Check equality
	 * @param Value $other
	 * @return bool
	 */
	public function equals(Value $other)
	{
		return strcmp($this->getHash(), $other->getHash());
	}

	
	/**
	 * Get hash
	 * @return string
	 */
	public function getHash()
	{
		return md5($this->serialize());
	}

	/**
	 * Serialize content
	 */
	public function serialize()
	{
		return serialize($this->getCopyDefinitions());
	}

	/**
	 * Set value
	 * @param string[] $value
	 */
	public function setValue($value)
	{
		$this->copy_definitions = $value;
	}

	/**
	 * Unserialize definitions
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		$this->setCopyDefinitions(unserialize($serialized));
	}

}
?>