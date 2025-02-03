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
 * class reading a glossary to transform it into a structure object
 * @author  Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 * @package ilias
 */
class ilSoapGLOStructureReader extends ilSoapStructureReader
{
    public function _parseStructure(): void
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

            $defStructureObject = ilSoapStructureObjectFactory::getInstance(
                (int) $term["id"],
                "term",
                $term["short_text"],
                "",
                $this->getObject()->getRefId()
            );

            $termStructureObject->addStructureObject($defStructureObject);
        }
    }
}
