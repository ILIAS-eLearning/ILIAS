<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* abstract class for the WBD-Data
* must be implemented for each WBD-Data
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
class gevWBDData implements WBDData{
	protected $WBDTagName;
	protected $WBDValue;

	public function __construct($WBDTagName,$WBDValue) {
		$this->WBDTagName = $WBDTagName;

		if(is_bool($WBDValue)) {
			$WBDValue = ($WBDValue) ? "ja" : "nein";
		}

		$this->WBDValue = $WBDValue;
	}

	/**
	* Get the WBD Tag Name
	*
	* @throws LogicException
	*
	* @return string $WBDTagName
	*/
	final public function WBDTagName() {
		if($this->WBDTagName === null) {
			throw new LogicException("gevWBDError::message:WBDTagName is null");
		}

		return $this->WBDTagName;
	}

	/**
	* Get the WBD Value
	*
	* @throws LogicException
	*
	* @return string $WBDValue
	*/
	final public function WBDValue() {
		if($this->WBDValue === null) {
			throw new LogicException("gevWBDError::message:WBDValue is null");
		}

		return $this->WBDValue;
	}

	final public function __toString() {
		return $this->WBDValue;
	}
}