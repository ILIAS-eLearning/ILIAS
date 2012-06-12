<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilDataCollectionView
*
* @author Martin Studer <ms@studer-raimann.ch>
* @author Marcel Raimann <mr@studer-raimann.ch>
* @author Fabian Schmid <fs@studer-raimann.ch>
*
*/


class ilDataCollectionView
{
	public function __construct()
	{
		echo "!!!";
	}
	
	/**
	 * createView
	 * a_val = 
	 */
	public function createView($a_val)
	{
		global $x;
	
		
		return true;
	}

	
//
// Methoden View
//
	
	
	/**
	 * readView
	 * a_val = 
	 */
	public function readView($a_val)
	{
		global $x;
	
		
		return true;
	}

	/**
	 * updateView
	 * a_val = 
	 */
	public function updateView($a_val)
	{
		global $x;
	
		
		return true;
	}

	/**
	 * deleteView
	 * a_val = 
	 */
	public function deleteView($a_val)
	{
		global $x;
	
		
		return true;
	}


	/**
	 * setViewType
	 */
	public function setViewType($a_val)
	{
		$this->type = $a_val;
	}
	
	/**
	 * getViewType
	 */
	public function getViewType()
	{
		return $this->type;
	}
	
	/**
	 * setViewFormType
	 */
	public function setViewFormtype($a_val)
	{
		$this->formtype = $a_val;
	}
	
	/**
	 * getViewFormType
	 */
	public function getViewFormtype()
	{
		return $this->formtype;
	}
	
	/**
	 * setViewId
	 */
	public function setViewId($a_val)
	{
		$this->id = $a_val;
	}
	
	/**
	 * getViewId
	 */
	public function getViewId()
	{
		return $this->id;
	}
	

//
// Methoden Fieldorder
//

	/**
	 * createFieldorder
	 * a_val = 
	 */
	public function createFieldorder($a_val)
	{
		global $x;
	
		
		return true;
	}

	/**
	 * readFieldorder
	 * a_val = 
	 */
	public function readFieldorder($a_val)
	{
		global $x;
	
		
		return true;
	}

	/**
	 * updateFieldorder
	 * a_val = 
	 */
	public function updateFieldorder($a_val)
	{
		global $x;
	
		
		return true;
	}

	/**
	 * deleteFieldorder
	 * a_val = 
	 */
	public function deleteFieldorder($a_val)
	{
		global $x;
	
		
		return true;
	}

	
	/**
	 * setFieldorderId
	 */
	public function setFieldorderId($a_val)
	{
		$this->fieldorder_id = $a_val;
	}
	
	/**
	 * getFieldorderId
	 */
	public function getFieldorderId()
	{
		return $this->fieldorder_id;
	}
	
	/**
	 * setFielddefinitionId
	 */
	public function setFielddefinitionId($a_val)
	{
		$this->fielddefinition_id = $a_val;
	}
	
	/**
	 * getFielddefinitionId
	 */
	public function getFielddefinitionId()
	{
		return $this->fielddefinition_id;
	}
	
	/**
	 * setPosition
	 */
	public function setPosition($a_val)
	{
		$this->position = $a_val;
	}
	
	/**
	 * getPosition
	 */
	public function getPosition()
	{
		return $this->position;
	}
	
	/**
	 * setParentId
	 */
	public function setParentId($a_val)
	{
		$this->parent_id = $a_val;
	}
	
	/**
	 * getParentId
	 */
	public function getParentId()
	{
		return $this->parent_id;
	}

}

?>