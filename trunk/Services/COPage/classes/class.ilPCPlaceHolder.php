<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
* Class ilPlaceHolder
*
* List content object (see ILIAS DTD)
*
* @version $Id$
*
* @ingroup ServicesCOPage
*/

class ilPCPlaceHolder extends ilPageContent {
	
	//class of placeholder
	
	var $q_node;			// node of Paragraph element
	var $content_class;
	var $height;
	
	/**
	* Init page content component.
	*/
	function init()
	{
		$this->setType("plach");
	}
	
	
	/**
	* Set node
	*/
	function setNode(&$a_node)
	{
		parent::setNode($a_node);		// this is the PageContent node
		$this->q_node =& $a_node->first_child();		//... and this the PlaceHolder
	}
	
	/**
	* Create PlaceHolder Element
	*/
	function create(&$a_pg_obj, $a_hier_id)
	{
		$this->createPageContentNode();
		$a_pg_obj->insertContent($this, $a_hier_id, IL_INSERT_AFTER);
		$this->q_node = $this->dom->create_element("PlaceHolder");
		$this->q_node = $this->node->append_child($this->q_node);
	}

	/**
	* Set Content Class.
	*
	* @param	string	$a_class	Content Class
	*/
	function setContentClass($a_class)
	{
		if (is_object($this->q_node))
		{
			$this->q_node->set_attribute("ContentClass", $a_class);
		}
	}

	/**
	* Get Content Class.
	*
	* @return	string	Content Class
	*/
	function getContentClass()
	{
		if (is_object($this->q_node))
		{
			return $this->q_node->get_attribute("ContentClass", $a_class);
		}
		return false;
	}
	
	/**
	* Set Height
	*
	* @param	string	$a_height	Height
	*/
	function setHeight($a_height)
	{
		if (is_object($this->q_node))
		{
			$this->q_node->set_attribute("Height", $a_height);
		}
	}
	
	
	/**
	* Get Height
	*
	* @return	string	Content Class
	*/
	function getHeight()
	{
		if (is_object($this->q_node))
		{
			return $this->q_node->get_attribute("Height", $a_class);
		}
		return false;
	}
	
	/**
	* Get characteristic of PlaceHolder.
	*
	* @return	string		characteristic
	*/
	function getClass()
	{
		return "";
	}
	
	/**
	 * Get lang vars needed for editing
	 * @return array array of lang var keys
	 */
	static function getLangVars()
	{
		return array("question_placeh","media_placeh","text_placeh",
			"ed_insert_plach","question_placehl","media_placehl","text_placehl",
			"verification_placeh", "verification_placehl");
	}

	
	
}