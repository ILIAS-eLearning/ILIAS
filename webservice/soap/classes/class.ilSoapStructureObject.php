<?php
  /*
   +-----------------------------------------------------------------------------+
   | ILIAS open source                                                           |
   +-----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
   |                                                                             |
   | This program is free software; you can redistribute it and/or               |
   | modify it under the terms of the GNU General Public License                 |
   | as published by the Free Software Foundation; either version 2              |
   | of the License, or (at your option) any later version.                      |
   |                                                                             |
   | This program is distributed in the hope that it will be useful,             |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
   | GNU General Public License for more details.                                |
   |                                                                             |
   | You should have received a copy of the GNU General Public License           |
   | along with this program; if not, write to the Free Software                 |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
   +-----------------------------------------------------------------------------+
  */


 /**
   * Abstract classs for soap structure objects
   *
   * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
   * @version $Id: class.ilSoapStructureObject.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
   *
   * @package ilias
   */

class ilSoapStructureObject
{
	var $obj_id;
	var $title;
	var $type;
	var $description;
	var $parentRefId;

	var $structureObjects = array ();


	function ilSoapStructureObject ($objId, $type, $title, $description, $parentRefId = null) {
		$this->setObjId ($objId);
		$this->setType ($type);
		$this->setTitle ($title);
		$this->setDescription ($description);
		$this->parentRefId = $parentRefId;
	}

	/**
	*	add structure object to its parent
	*
	*/
	function addStructureObject ($structureObject)
	{
		$this->structureObjects [$structureObject->getObjId()] =  $structureObject;
	}

	/**
	 * returns sub structure elements
	 *
	 */
	function getStructureObjects ()  {
		return $this->structureObjects;
	}


	/**
	*	set current ObjId
	*
	*/
	function setObjId ($value) {
		$this->obj_id= $value;
	}


	/**
	* return current object id
	*/
	function getObjId()
	{
		return $this->obj_id;
	}



	/**
	*	set current title
	*
	*/
	function setTitle ($value) {
		$this->title= $value;
	}


	/**
	*	return current title
	*
	*/
	function getTitle () {
		return $this->title;
	}

	/**
	*	set current description
	*
	*/
	function setDescription ($value) {
		$this->description = $value;
	}


	/**
	*	return current description
	*
	*/
	function getDescription () {
		return $this->description;
	}


	/**
	*	set current type
	*
	*/
	function setType ($value) 
	{
		$this->type = $value;
	}


	/**
	*	return current type
	*
	*/
	function getType () 
	{
		return $this->type;
	}


	/**
	*	return current goto_link
	*
	*/
	function getGotoLink () 
	{
		return ILIAS_HTTP_PATH."/". "goto.php?target=".$this->getType()."_".$this->getObjId().(is_numeric ($this->getParentRefId())?"_".$this->getParentRefId():"")."&client_id=".CLIENT_ID;
	}

	/**
	*	return current internal_link
	*
	*/
	function getInternalLink () 
	{
		die ("abstract");
	}

	/**
	 * get xml tag attributes
	 */

	function _getXMLAttributes () 
	{
		return array(	'type' => $this->getType(),
					   	'obj_id' => $this->getObjId()
		);
	}

	function _getTagName () 
	{
		return "StructureObject";
	}
	
	/**
	* set ref id for parent object (used for permanent link if set)
	*/
	function setParentRefId ($parentRefId) 
	{
		$this->parentRefId = $parentRefId;
	}
	
	
	/**
	* read access to parents ref id
	*/
	function getParentRefId() 
	{
		return $this->parentRefId;
	}
		

	/**
	 * export to xml writer
	 */
	 function exportXML ($xml_writer) {
	 	$attrs = $this->_getXMLAttributes();

		// open tag
 		$xml_writer->xmlStartTag($this->_getTagName(), $attrs);

		$xml_writer->xmlElement('Title',null,$this->getTitle());
		$xml_writer->xmlElement('Description',null,$this->getDescription());
		$xml_writer->xmlElement('InternalLink',null,$this->getInternalLink());
		$xml_writer->xmlElement('GotoLink',null,$this->getGotoLink());

		$xml_writer->xmlStartTag("StructureObjects");

		// handle sub elements
		$structureObjects = $this->getStructureObjects();

		foreach ($structureObjects as $structureObject)
		{
			$structureObject->exportXML ($xml_writer);
		}

		$xml_writer->xmlEndTag("StructureObjects");

		$xml_writer->xmlEndTag($this->_getTagName());

	 }


}

?>