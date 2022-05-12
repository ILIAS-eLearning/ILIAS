<?php
/*
 +-----------------------------------------------------------------------------+
 | ILIAS open source                                                           |
 +-----------------------------------------------------------------------------+
 | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
 * Soap exercise administration methods
 * @author  Roland Küstermann <roland@kuestermann.com>
 * @version $Id: class.ilSoapExerciseAdministration.php 12992 2007-01-25 10:04:26Z rkuester $
 * @package ilias
 */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapSCORMAdministration extends ilSoapAdministration
{
    /**
     * @return false|soap_fault|SoapFault|string|null
     */
    public function getIMSManifestXML(string $sid, int $requested_ref_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        if (!($requested_ref_id > 0)) {
            return $this->raiseError(
                'No ref id given. Aborting!',
                'Client'
            );
        }
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];

        if (!$obj_id = ilObject::_lookupObjectId($requested_ref_id)) {
            return $this->raiseError(
                'No exercise found for id: ' . $requested_ref_id,
                'Client'
            );
        }

        if (ilObject::_isInTrash($requested_ref_id)) {
            return $this->raiseError("Parent with ID $requested_ref_id has been deleted.", 'Client');
        }

        $permission_ok = false;
        foreach ($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id) {
            if ($rbacsystem->checkAccess('read', $ref_id)) {
                $permission_ok = true;
                break;
            }
        }

        if (!$permission_ok) {
            return $this->raiseError(
                'No permission to read the object with id: ' . $requested_ref_id,
                'Server'
            );
        }

        $lm_obj = ilObjectFactory::getInstanceByObjId($obj_id, false);
        if (!is_object($lm_obj) || $lm_obj->getType() !== "sahs") {
            return $this->raiseError(
                'Wrong obj id or type for scorm object with id ' . $requested_ref_id,
                'Server'
            );
        }

        require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMObject.php");
        require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResource.php");

        $imsFilename = $lm_obj->getDataDirectory() . DIRECTORY_SEPARATOR . "imsmanifest.xml";

        if (!file_exists($imsFilename)) {
            return $this->raiseError(
                'Could not find manifest file for object with ref id ' . $requested_ref_id,
                'Server'
            );
        }
        return file_get_contents($imsFilename);
    }

    /**
     * @return bool|soap_fault|SoapFault|null
     */
    public function hasSCORMCertificate(string $sid, int $ref_id, int $usr_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }
        if (!($ref_id > 0)) {
            return $this->raiseError(
                'No ref id given. Aborting!',
                'Client'
            );
        }
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];

        if (!$obj_id = ilObject::_lookupObjectId($ref_id)) {
            return $this->raiseError(
                'No exercise found for id: ' . $ref_id,
                'Client'
            );
        }

        if (ilObject::_isInTrash($ref_id)) {
            return $this->raiseError("Parent with ID $ref_id has been deleted.", 'Client');
        }

        $certValidator = new ilCertificateUserCertificateAccessValidator();

        return $certValidator->validate($usr_id, $obj_id);
    }

    /**
     * @return soap_fault|SoapFault|string|null
     */
    public function getSCORMCompletionStatus(string $sid, int $a_usr_id, int $a_ref_id)
    {
        $this->initAuth($sid);
        $this->initIlias();

        if (!$this->checkSession($sid)) {
            return $this->raiseError($this->getMessage(), $this->getMessageCode());
        }

        if (!($a_ref_id > 0)) {
            return $this->raiseError('No ref_id given. Aborting!', 'Client');
        }

        include_once 'include/inc.header.php';

        if (!$obj_id = ilObject::_lookupObjectId($a_ref_id)) {
            return $this->raiseError(
                'No scorm module found for id: ' . $a_ref_id,
                'Client'
            );
        }

        include_once 'Services/Tracking/classes/class.ilLPStatus.php';
        include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';

        if (!ilObjUserTracking::_enabledLearningProgress()) {
            return $this->raiseError('Learning progress not enabled in this installation. Aborting!', 'Server');
        }

        $status = ilLPStatus::_lookupStatus($obj_id, $a_usr_id);
        if ($status === ilLPStatus::LP_STATUS_COMPLETED_NUM) {
            return 'completed';
        } elseif ($status === ilLPStatus::LP_STATUS_FAILED_NUM) {
            return 'failed';
        } elseif ($status === ilLPStatus::LP_STATUS_IN_PROGRESS_NUM) {
            return 'in_progress';
        } else {
            return 'not_attempted';
        }
    }
}
