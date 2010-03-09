<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";

/**
* XML writer class
*
* Class to simplify manual writing of xml documents.
* It only supports writing xml sequentially, because the xml document
* is saved in a string with no additional structure information.
* The author is responsible for well-formedness and validity
* of the xml document.
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilObjectXMLWriter.php,v 1.3 2005/11/04 12:50:24 smeyer Exp $
*/
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

		$this->structureObject->exportXML ($this);

		$this->__buildFooter();

		return true;
	}

	function getXML()
	{
		return $this->xmlDumpMem(FALSE);
	}


	function __buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE RepositoryObject PUBLIC \"-//ILIAS//DTD UserImport//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_soap_structure_object_3_7.dtd\">");
		$this->xmlSetGenCmt("Internal Structure Information of Repository Object");
		$this->xmlHeader();


		return true;
	}

	function __buildFooter()
	{

	}

}


?>
