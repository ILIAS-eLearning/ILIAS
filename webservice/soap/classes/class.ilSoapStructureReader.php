<?php
include_once './webservice/soap/classes/class.ilSoapStructureObject.php';

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

    public function getStructureObject() : ?ilSoapStructureObject
    {
        $this->_parseStructure();
        return $this->structureObject;
    }

    public function _parseStructure() : void
    {
    }

    public function isValid() : bool
    {
        return $this->structureObject instanceof \ilSoapStructureObject;
    }

    public function getObject() : ilObject
    {
        return $this->object;
    }
}
