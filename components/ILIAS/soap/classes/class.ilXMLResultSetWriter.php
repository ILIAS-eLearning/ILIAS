<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

/**
 * XML Writer for XMLResultSet
 * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 * @todo   remove dependency to ilXmlWriter and use SimpleXML
 */
class ilXMLResultSetWriter extends ilXmlWriter
{
    protected ilXMLResultSet $xmlResultSet;

    public function __construct(ilXMLResultSet $xmlResultSet)
    {
        parent::__construct();
        $this->xmlResultSet = $xmlResultSet;
    }

    public function start(): bool
    {
        $this->buildHeader();
        $this->buildColSpecs();
        $this->buildRows();
        $this->buildFooter();
        return true;
    }

    private function buildHeader(): void
    {
        $this->xmlSetDtdDef("<!DOCTYPE result PUBLIC \"-//ILIAS//DTD XMLResultSet//EN\" \"" . ILIAS_HTTP_PATH . "/components/ILIAS/Export/xml/ilias_xml_resultset_3_7.dtd\">");
        $this->xmlHeader();
        $this->xmlStartTag("result");
    }

    private function buildColSpecs(): void
    {
        $this->xmlStartTag("colspecs");
        foreach ($this->xmlResultSet->getColSpecs() as $colSpec) {
            $attr = array("idx" => $colSpec->getIndex(), "name" => $colSpec->getName());

            $this->xmlElement("colspec", $attr, null);
        }
        $this->xmlEndTag("colspecs");
    }

    private function buildRows(): void
    {
        $this->xmlStartTag("rows");
        foreach ($this->xmlResultSet->getRows() as $row) {
            $this->appendRow($row);
        }
        $this->xmlEndTag("rows");
    }

    private function appendRow(ilXMLResultSetRow $xmlResultSetRow): void
    {
        $this->xmlStartTag('row', null);
        foreach ($xmlResultSetRow->getColumns() as $value) {
            $this->xmlElement('column', null, $value);
        }
        $this->xmlEndTag('row');
    }

    private function buildFooter(): void
    {
        $this->xmlEndTag('result');
    }

    public function getXML(): string
    {
        return $this->xmlDumpMem(false);
    }
}
