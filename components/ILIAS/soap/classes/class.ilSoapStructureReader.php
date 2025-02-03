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
 * Abstract classs for reading structure objects
 * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 */
class ilSoapStructureReader
{
    protected ilObject $object;
    public ?ilSoapStructureObject $structureObject = null;

    public function __construct(ilObject $object)
    {
        $this->object = $object;
        $this->structureObject = ilSoapStructureObjectFactory::getInstanceForObject($object);
    }

    public function getStructureObject(): ?ilSoapStructureObject
    {
        $this->_parseStructure();
        return $this->structureObject;
    }

    public function _parseStructure(): void
    {
    }

    public function isValid(): bool
    {
        return $this->structureObject instanceof \ilSoapStructureObject;
    }

    public function getObject(): ilObject
    {
        return $this->object;
    }
}
