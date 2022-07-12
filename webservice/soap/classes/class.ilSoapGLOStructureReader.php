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

include_once "./webservice/soap/classes/class.ilSoapStructureReader.php";
include_once "./webservice/soap/classes/class.ilSoapStructureObjectFactory.php";

/**
 * class reading a glossary to transform it into a structure object
 * @author  Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 * @package ilias
 */
class ilSoapGLOStructureReader extends ilSoapStructureReader
{
    public function _parseStructure() : void
    {
        /* @var $object ilObjGlossary */
        $object = $this->object;

        $terms = $this->object->getTermList();
        foreach ($terms as $term) {
            $termStructureObject = ilSoapStructureObjectFactory::getInstance(
                (int) $term["id"],
                "git",
                $term["term"],
                "",
                $this->getObject()->getRefId()
            );

            $this->structureObject->addStructureObject($termStructureObject);

            $defs = ilGlossaryDefinition::getDefinitionList((int) $term["id"]);
            foreach ($defs as $def) {
                $defStructureObject = ilSoapStructureObjectFactory::getInstance(
                    (int) $def["id"],
                    "gdf",
                    $def["short_text"],
                    "",
                    $this->getObject()->getRefId()
                );

                $termStructureObject->addStructureObject($defStructureObject);
            }
        }
    }
}
