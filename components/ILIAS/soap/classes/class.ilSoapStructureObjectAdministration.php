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
 * administration for structure objects
 * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
 */
class ilSOAPStructureObjectAdministration extends ilSoapAdministration
{
    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function getStructureObjects(string $sid, int $ref_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        if (!$target_obj = ilObjectFactory::getInstanceByRefId($ref_id, false)) {
            return $this->raiseError('No valid reference id given.', 'Client');
        }

        $structureReaderClassname = "ilSoap" . strtoupper($target_obj->getType()) . "StructureReader";
        $filename = "./components/ILIAS/soap/classes/class." . $structureReaderClassname . ".php";

        if (!file_exists($filename)) {
            return $this->raiseError("Object type '" . $target_obj->getType() . "'is not supported.", 'Client');
        }

        include_once $filename;
        $structureReader = new $structureReaderClassname($target_obj);
        $xml_writer = new ilSoapStructureObjectXMLWriter();
        $structureObject = &$structureReader->getStructureObject();
        $xml_writer->setStructureObject($structureObject);
        if (!$xml_writer->start()) {
            return $this->raiseError('Cannot create object xml !', 'Server');
        }
        return $xml_writer->getXML();
    }
}
