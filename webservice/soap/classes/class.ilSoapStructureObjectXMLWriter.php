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
    public $ilias;
    public $xml;
    public $structureObject;
    public $user_id = 0;

    /**
    * constructor
    * @param	string	xml version
    * @param	string	output encoding
    * @param	string	input encoding
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $ilUser = $DIC['ilUser'];

        parent::__construct();

        $this->ilias =&$ilias;
        $this->user_id = $ilUser->getId();
    }


    public function setStructureObject(&$structureObject)
    {
        $this->structureObject = &$structureObject;
    }


    public function start()
    {
        if (!is_object($this->structureObject)) {
            return false;
        }

        $this->__buildHeader();

        $this->structureObject->exportXML($this);

        $this->__buildFooter();

        return true;
    }

    public function getXML()
    {
        return $this->xmlDumpMem(false);
    }


    public function __buildHeader()
    {
        $this->xmlSetDtdDef("<!DOCTYPE RepositoryObject PUBLIC \"-//ILIAS//DTD UserImport//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_soap_structure_object_3_7.dtd\">");
        $this->xmlSetGenCmt("Internal Structure Information of Repository Object");
        $this->xmlHeader();


        return true;
    }

    public function __buildFooter()
    {
    }
}
