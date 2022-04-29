<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";

/**
 * XML writer class
 * Class to simplify manual writing of xml documents.
 * It only supports writing xml sequentially, because the xml document
 * is saved in a string with no additional structure information.
 * The author is responsible for well-formedness and validity
 * of the xml document.
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.ilObjectXMLWriter.php,v 1.3 2005/11/04 12:50:24 smeyer Exp $
 */
class ilSoapStructureObjectXMLWriter extends ilXmlWriter
{
    public string $xml;
    public ?ilSoapStructureObject $structureObject = null;

    public function __construct()
    {
        global $DIC;

        $ilUser = $DIC->user();
        parent::__construct();
    }

    public function setStructureObject(ilSoapStructureObject $structureObject) : void
    {
        $this->structureObject = $structureObject;
    }

    public function start() : bool
    {
        if (!is_object($this->structureObject)) {
            return false;
        }

        $this->buildHeader();
        $this->structureObject->exportXML($this);
        $this->buildFooter();
        return true;
    }

    public function getXML() : string
    {
        return $this->xmlDumpMem(false);
    }

    private function buildHeader() : void
    {
        $this->xmlSetDtdDef("<!DOCTYPE RepositoryObject PUBLIC \"-//ILIAS//DTD UserImport//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_soap_structure_object_3_7.dtd\">");
        $this->xmlSetGenCmt("Internal Structure Information of Repository Object");
        $this->xmlHeader();
    }

    private function buildFooter() : void
    {
    }
}
