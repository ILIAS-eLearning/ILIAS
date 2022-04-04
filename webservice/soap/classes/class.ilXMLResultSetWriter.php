<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * XML Writer for XMLResultSet
 * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 * @todo   remove dependency to ilXmlWriter and use SimpleXML
 */
class ilXMLResultSetWriter extends ilXmlWriter
{
    protected $xmlResultSet;

    public function __construct(ilXMLResultSet $xmlResultSet)
    {
        parent::__construct();
        $this->xmlResultSet = $xmlResultSet;
    }

    public function start() : bool
    {
        $this->__buildHeader();
        $this->__buildColSpecs();
        $this->__buildRows();
        $this->__buildFooter();
        return true;
    }

    protected function __buildHeader() : void
    {
        $this->xmlSetDtdDef("<!DOCTYPE result PUBLIC \"-//ILIAS//DTD XMLResultSet//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_xml_resultset_3_7.dtd\">");
        $this->xmlHeader();
        $this->xmlStartTag("result");
    }

    protected function __buildColSpecs() : void
    {
        $this->xmlStartTag("colspecs");
        foreach ($this->xmlResultSet->getColSpecs() as $colSpec) {
            $attr = array("idx" => $colSpec->getIndex(), "name" => $colSpec->getName());

            $this->xmlElement("colspec", $attr, null);
        }
        $this->xmlEndTag("colspecs");
    }

    protected function __buildRows() : void
    {
        $this->xmlStartTag("rows");
        foreach ($this->xmlResultSet->getRows() as $row) {
            $this->__appendRow($row);
        }
        $this->xmlEndTag("rows");
    }

    protected function __appendRow(ilXMLResultSetRow $xmlResultSetRow) : void
    {
        $this->xmlStartTag('row', null);
        foreach ($xmlResultSetRow->getColumns() as $value) {
            $this->xmlElement('column', null, $value);
        }
        $this->xmlEndTag('row');
    }

    public function __buildFooter()
    {
        $this->xmlEndTag('result');
    }

    public function getXML() : string
    {
        return $this->xmlDumpMem(false);
    }
}
