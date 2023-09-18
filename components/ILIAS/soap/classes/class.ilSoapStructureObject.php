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
