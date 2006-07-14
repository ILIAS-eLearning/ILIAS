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
* XML writer class
*
* Class to simplify manual writing of xml documents.
* It only supports writing xml sequentially, because the xml document
* is saved in a string with no additional structure information.
* The author is responsible for well-formedness and validity
* of the xml document.
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id: class.ilObjectXMLWriter.php,v 1.3 2005/11/04 12:50:24 smeyer Exp $
*/

include_once "./classes/class.ilXmlWriter.php";

class ilSoapStructureObjectXMLWriter extends ilXmlWriter
{
	var $ilias;
	var $xml;
	var $structureObject;
	var $user_id = 0;

	/**
	* constructor
	* @param	string	xml version
	* @param	string	output encoding
	* @param	string	input encoding
	* @access	public
	*/
	function ilSoapStructureObjectXMLWriter()
	{
		global $ilias,$ilUser;

		parent::ilXmlWriter();

		$this->ilias =& $ilias;
		$this->user_id = $ilUser->getId();
	}


	function setStructureObject(&  $structureObject)
	{
		$this->structureObject = & $structureObject;
	}


	function start()
	{
		if (!is_object($this->structureObject))
			return false;

		$this->__buildHeader();
//
//		$this->xmlElement('Title',null,$this->structureObject->getTitle());
//		$this->xmlElement('Description',null,$this->structureObject->getDescription());
//		$this->xmlElement('InternalLink',null,$this->structureObject->getInternalLink());
//		$this->xmlElement('GotoLink',null,$this->structureObject->getGotoLink());

		$this->structureObject->exportXML ($this);


//
//		// first level sub structure objects
//		$structureObjects = $this->structureObject->getStructureObjects();
//
//		$this->__handleObject ($structureObjects);


		$this->__buildFooter();

		return true;
	}

	function getXML()
	{
		return $this->xmlDumpMem(FALSE);
	}


//	// PRIVATE
//
//	function __handleObject (& $subStructureObjects) {
//
//		$this->xmlStartTag("StructureObjects");
//
//		foreach($subStructureObjects as $subObject)
//		{
//			$attrs = array(	'type' => $subObject->getType(),
//					   	'obj_id' => $subObject->getObjId(),
//						'ref_id' => $subObject->getRefId(),
//					   	'parent_id' => $subObject->getParentId()
//			);
//
//			// open tag
//			$this->xmlStartTag("StructureObject", $attrs);
//
//			$this->xmlElement('Title',null,$subObject->getTitle());
//			$this->xmlElement('Description',null,$subObject->getDescription());
//			$this->xmlElement('InternalLink',null,$subObject->getInternalLink());
//			$this->xmlElement('GotoLink',null,$subObject->getGotoLink());
//
//			// handle sub elements
//			$structureObjects = $subObject->getStructureObjects();
//
//			$this->__handleObject ($structureObjects);
//
//			// close tag
//			$this->xmlEndTag("StructureObject");
//
//		}
//
//
//		$this->xmlEndTag("StructureObjects");
//
//	}
//
	function __buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE RepositoryObject PUBLIC \"-//ILIAS//DTD UserImport//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_soap_structure_object_3_7.dtd\">");
		$this->xmlSetGenCmt("Internal Structure Information of Content Objects");
		$this->xmlHeader();
//
//		$attrs = array('type' => $this->structureObject->getType(),
//					   'obj_id' => $this->structureObject->getObjId(),
//					   'ref_id' => $this->structureObject->getRefId()
//					   );
//
//
//		$this->xmlStartTag('Object', $attrs);


		return true;
	}

	function __buildFooter()
	{
		//$this->xmlEndTag('Object');
	}

}


?>
