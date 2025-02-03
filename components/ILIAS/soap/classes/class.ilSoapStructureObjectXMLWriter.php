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

    public function setStructureObject(ilSoapStructureObject $structureObject): void
    {
        $this->structureObject = $structureObject;
    }

    public function start(): bool
    {
        if (!is_object($this->structureObject)) {
            return false;
        }

        $this->buildHeader();
        $this->structureObject->exportXML($this);
        $this->buildFooter();
        return true;
    }

    public function getXML(): string
    {
        return $this->xmlDumpMem(false);
    }

    private function buildHeader(): void
    {
        $this->xmlSetDtdDef("<!DOCTYPE RepositoryObject PUBLIC \"-//ILIAS//DTD UserImport//EN\" \"" . ILIAS_HTTP_PATH . "/components/ILIAS/Export/xml/ilias_soap_structure_object_3_7.dtd\">");
        $this->xmlSetGenCmt("Internal Structure Information of Repository Object");
        $this->xmlHeader();
    }

    private function buildFooter(): void
    {
    }
}
