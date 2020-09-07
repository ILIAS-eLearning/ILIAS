<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Xml/classes/class.ilXmlWriter.php";

/**
  * XML Writer for XMLResultSet
  *
  * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
  * @version $Id: class.ilXMLResultSet.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
  */
class ilXMLResultSetWriter extends ilXmlWriter
{
    public $xmlResultSet;

    public function __construct(&$xmlResultSet)
    {
        parent::__construct();
        $this->xmlResultSet = $xmlResultSet;
    }


    public function start()
    {
        if (!is_object($this->xmlResultSet)) {
            return false;
        }

        $this->__buildHeader();

        $this->__buildColSpecs();

        $this->__buildRows();

        $this->__buildFooter();

        return true;
    }

    public function getXML()
    {
        return $this->xmlDumpMem(false);
    }


    // PRIVATE
    public function __appendRow(&$xmlResultSetRow)
    {
        $this->xmlStartTag('row', null);

        foreach ($xmlResultSetRow->getColumns() as $value) {
            $this->xmlElement('column', null, $value);
        }

        $this->xmlEndTag('row');
    }


    public function __buildHeader()
    {
        $this->xmlSetDtdDef("<!DOCTYPE result PUBLIC \"-//ILIAS//DTD XMLResultSet//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_xml_resultset_3_7.dtd\">");
        $this->xmlHeader();

        $this->xmlStartTag("result");

        return true;
    }

    public function __buildColSpecs()
    {
        $this->xmlStartTag("colspecs");

        foreach ($this->xmlResultSet->getColSpecs() as $colSpec) {
            $attr  = array("idx" => $colSpec->getIndex(), "name" => $colSpec->getName());

            $this->xmlElement("colspec", $attr, null);
        }

        $this->xmlEndTag("colspecs");
    }

    public function __buildRows()
    {
        $this->xmlStartTag("rows");

        foreach ($this->xmlResultSet->getRows() as $row) {
            $this->__appendRow($row);
        }

        $this->xmlEndTag("rows");
    }

    public function __buildFooter()
    {
        $this->xmlEndTag('result');
    }
}
