<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* abstract class for the WBD-Error
* must be implemented for each WBD-Error
*
* @author	Stefan Hecken <shecken@concepts-and-training.de>
* @version	$Id$
*
*/
class gevWBDError extends WBDError{
	protected $errMessage;
	protected $usr_id;
	protected $crs_id;
	protected $row_id;

	public function __construct($errMessage,$usr_id,$row_id,$crs_id = 0) {
		$this->errMessage = $errMessage;
		$this->usr_id = $usr_id;
		$this->row_id = $row_id;
		$this->crs_id = $crs_id;
	}

	/**
	* Get the internal user id of the WBD Error
	*
	* @throws LogicException
	*
	* @return string $errMessage
	*/
	final function userId() {
		if($this->usr_id === null) {
			throw new LogicException("gevWBDError::userId:user_id is null");
		}

		return $this->usr_id;
	}

	/**
	* Get the inernal row id of the WBD Error
	*
	* @throws LogicException
	*
	* @return string $errMessage
	*/
	final function rowId() {
		if($this->row_id === null) {
			throw new LogicException("gevWBDError::rowId:row_id is null");
		}

		return $this->row_id;
	}

	/**
	* Get the internal crs id of the WBD Error
	*
	* @throws LogicException
	*
	* @return string $errMessage
	*/
	final function crsId() {
		if($this->crs_id === null) {
			throw new LogicException("gevWBDError::crsId:crs_id is null");
		}

		return $this->crs_id;
	}
}