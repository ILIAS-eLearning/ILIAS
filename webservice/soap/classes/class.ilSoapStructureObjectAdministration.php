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

include_once './webservice/soap/classes/class.ilSoapAdministration.php';

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
        $filename = "./webservice/soap/classes/class." . $structureReaderClassname . ".php";

        if (!file_exists($filename)) {
            return $this->raiseError("Object type '" . $target_obj->getType() . "'is not supported.", 'Client');
        }

        include_once $filename;
        $structureReader = new $structureReaderClassname($target_obj);
        include_once './webservice/soap/classes/class.ilSoapStructureObjectXMLWriter.php';
        $xml_writer = new ilSoapStructureObjectXMLWriter();
        $structureObject = &$structureReader->getStructureObject();
        $xml_writer->setStructureObject($structureObject);
        if (!$xml_writer->start()) {
            return $this->raiseError('Cannot create object xml !', 'Server');
        }
        return $xml_writer->getXML();
    }
}
