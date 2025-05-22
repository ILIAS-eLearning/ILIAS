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
 * Abstract classs for soap structure objects
 * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 */
class ilSoapStructureObject
{
    public int $obj_id;
    public string $title;
    public string $type;
    public string $description;
    public ?int $parentRefId;

    public array $structureObjects = array();

    public function __construct(int $objId, string $type, string $title, string $description, ?int $parentRefId = null)
    {
        $this->setObjId($objId);
        $this->setType($type);
        $this->setTitle($title);
        $this->setDescription($description);
        $this->parentRefId = $parentRefId;
    }

    public function addStructureObject(ilSoapStructureObject $structureObject): void
    {
        $this->structureObjects [$structureObject->getObjId()] = $structureObject;
    }

    public function getStructureObjects(): array
    {
        return $this->structureObjects;
    }

    public function setObjId(int $value): void
    {
        $this->obj_id = $value;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setTitle(string $value): void
    {
        $this->title = $value;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $value): void
    {
        $this->description = $value;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setType(string $value): void
    {
        $this->type = $value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getGotoLink(): string
    {
        return ILIAS_HTTP_PATH . "/" . "goto.php?target=" . $this->getType() .
            "_" . $this->getObjId() .
            (is_numeric($this->getParentRefId()) ? "_" . $this->getParentRefId() : "") . "&client_id=" . CLIENT_ID;
    }

    public function getInternalLink(): string
    {
        return '';
    }

    /**
     * @return array{type: string, obj_id: int}
     */
    public function _getXMLAttributes(): array
    {
        return array(
            'type' => $this->getType(),
            'obj_id' => $this->getObjId()
        );
    }

    public function _getTagName(): string
    {
        return "StructureObject";
    }

    public function setParentRefId(int $parentRefId): void
    {
        $this->parentRefId = $parentRefId;
    }

    public function getParentRefId(): ?int
    {
        return $this->parentRefId;
    }

    public function exportXML(ilXmlWriter $xml_writer): void
    {
        $attrs = $this->_getXMLAttributes();

        $xml_writer->xmlStartTag($this->_getTagName(), $attrs);

        $xml_writer->xmlElement('Title', null, $this->getTitle());
        $xml_writer->xmlElement('Description', null, $this->getDescription());
        $xml_writer->xmlElement('InternalLink', null, $this->getInternalLink());
        $xml_writer->xmlElement('GotoLink', null, $this->getGotoLink());

        $xml_writer->xmlStartTag("StructureObjects");

        $structureObjects = $this->getStructureObjects();

        foreach ($structureObjects as $structureObject) {
            $structureObject->exportXML($xml_writer);
        }

        $xml_writer->xmlEndTag("StructureObjects");

        $xml_writer->xmlEndTag($this->_getTagName());
    }
}
