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
   *
   * @author Roland Küstermann <roland@kuestermann.com>
   * @version $Id: class.ilSoapExerciseAdministration.php 12992 2007-01-25 10:04:26Z rkuester $
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapSCORMAdministration extends ilSoapAdministration
{
	function ilSoapExerciseAdministration()
	{
		parent::ilSoapAdministration();
	}

  	/**
	 * get ims manifest xml
	 *
	 * @param string $sid
	 * @param int $ref_id
	 *
	 * @return xml following scorm.dtd
	 */

	function getIMSManifestXML ($sid, $ref_id) {
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if(!strlen($ref_id))
		{
			return $this->__raiseError('No ref id given. Aborting!',
									   'Client');
		}
		include_once './include/inc.header.php';
		global $rbacsystem, $tree, $ilLog;

		// get obj_id
		if(!$obj_id = ilObject::_lookupObjectId($ref_id))
		{
			return $this->__raiseError('No exercise found for id: '.$ref_id,
									   'Client');
		}

		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError("Parent with ID $ref_id has been deleted.", 'Client');
		}

		// Check access
		$permission_ok = false;
		foreach($ref_ids = ilObject::_getAllReferences($obj_id) as $ref_id)
		{
			if($rbacsystem->checkAccess('read',$ref_id))
			{
				$permission_ok = true;
				break;
			}
		}

		if(!$permission_ok)
		{
			return $this->__raiseError('No permission to read the object with id: '.$ref_id,
									   'Server');
		}

		$lm_obj = ilObjectFactory::getInstanceByObjId($obj_id, false);
		if (!is_object($lm_obj) || $lm_obj->getType()!= "sahs")
		{
			return $this->__raiseError('Wrong obj id or type for scorm object with id '.$ref_id,
									   'Server');
		}
		// get scorm xml
		require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMObject.php");
		require_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMResource.php");

		$imsFilename = $lm_obj->getDataDirectory().DIRECTORY_SEPARATOR."imsmanifest.xml";

		if (!file_exists($imsFilename)) {
			return $this->__raiseError('Could not find manifest file for object with ref id '.$ref_id,
									   'Server');
			
		}
		return file_get_contents($imsFilename);
	}
	
	public function hasSCORMCertificate($sid, $ref_id, $usr_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}
		if(!strlen($ref_id))
		{
			return $this->__raiseError('No ref id given. Aborting!',
									   'Client');
		}
		include_once './include/inc.header.php';
		global $rbacsystem, $tree, $ilLog;

		// get obj_id
		if(!$obj_id = ilObject::_lookupObjectId($ref_id))
		{
			return $this->__raiseError('No exercise found for id: '.$ref_id,
									   'Client');
		}

		if(ilObject::_isInTrash($ref_id))
		{
			return $this->__raiseError("Parent with ID $ref_id has been deleted.", 'Client');
		}

		$result = false;
		include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModuleAccess.php");
		if (ilObjSAHSLearningModuleAccess::_lookupCertificate($obj_id))
		{
			$lpdata = false;
			$completed = false;
			include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
			$type = ilObjSAHSLearningModule::_lookupSubType($obj_id);

			include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
			if (ilObjUserTracking::_enabledLearningProgress())
			{
				$path = $tree->getPathFull($_GET['ref_id']);
				$course = 0;
				foreach ($path as $item) if (strcmp($item["type"], "crs") == 0) $course = $item["obj_id"];
				if ($course > 0)
				{
					include_once "./Services/tracking/classes/class.ilLPCollections.php";
					$items = ilLPCollections::_getItems($course);
					if (in_array($ref_id, $items))
					{
						include_once "./Services/tracking/classes/class.ilLPStatusWrapper.php";
						$completed_user_ids_array = ilLPStatusWrapper::_getCompleted($course);
						if (in_array($usr_id, $completed_user_ids_array))
						{
							$completed = true;
						}
						$lpdata = true;
					}
				}
			}
			switch ($type)
			{
				case "scorm":
					if (!$lpdata)
					{
						include_once "./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php";
						$completed = ilObjSCORMLearningModule::_getCourseCompletionForUser($obj_id, $usr_id);
					}
					if ($completed)
					{
						$result = true;
					}
					break;
				case "scorm2004":
					if (!$lpdata)
					{
						include_once "./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php";
						$completed = ilObjSCORM2004LearningModule::_getCourseCompletionForUser($obj_id, $usr_id);
					}
					if ($completed)
					{
						$result = true;
					}
					break;
				default:
					break;
			}
		}

		return $result;
	}
}
?>