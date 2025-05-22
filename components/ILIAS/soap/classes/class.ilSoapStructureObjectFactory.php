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
 * factory classs for structure objects
 * @author  Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 * @version $Id: class.ilSoapStructureReader.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
 * @package ilias
 */
class ilSoapStructureObjectFactory
{
    public function getInstanceForObject(ilObject $object): ?ilSoapStructureObject
    {
        $classname = $this->_getClassnameForType($object->getType());
        if ($classname !== null) {
            switch ($object->getType()) {
                case "lm":
                case "glo":
                    return new $classname(
                        $object->getId(),
                        $object->getType(),
                        $object->getTitle(),
                        $object->getLongDescription(),
                        $object->getRefId()
                    );
            }
        }

        return null;
    }

    public function getInstance(
        int $objId,
        string $type,
        string $title,
        string $description,
        int $parentRefId
    ): ?ilSoapStructureObject {
        $classname = $this->_getClassnameForType($type);
        if ($classname === null) {
            return null;
        }

        return new $classname($objId, $type, $title, $description, $parentRefId);
    }

    public function _getClassnameForType(string $type): ?string
    {
        switch ($type) {
            case "glo":
            case "lm":
                return "ilSoapRepositoryStructureObject";
            case "st":
                return "ilSoapLMChapterStructureObject";
            case "pg":
                return "ilSoapLMPageStructureObject";
            case "git":
                return "ilSoapGLOTermStructureObject";
            case "term":
                return "ilSoapGLOTermDefinitionStructureObject";
        }

        return null;
    }
}
