<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilECSObjectSettings
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
* $Id: class.ilObjCourseGUI.php 31646 2011-11-14 11:39:37Z jluetzen $
*
* @ingroup ServicesWebServicesECS
*/
class ilECSObjectSettings
{
	/**
	 * Is ECS active?
	 * 
	 * @param int $a_object_id
	 * @return boolean 
	 */
	public static function isActive($a_object_id)
	{				
		include_once('./Services/WebServices/ECS/classes/class.ilECSServerSettings.php');
		if(ilECSServerSettings::getInstance()->activeServerExists())
		{
			// :TODO: check object type for activation		
			
			// imported objects cannot be exported
			include_once('./Services/WebServices/ECS/classes/class.ilECSImport.php');
			if(!ilECSImport::lookupServerId($a_object_id))
			{
				return true;			
			}
		}
						
		return false;
	}

	/**
	 * Fill ECS export settings "multiple servers"
	 * 
	 * @param int $a_obj_id
	 * @param ilPropertyFormGUI $a_form
	 */
	public static function fillECSExportSettings($a_obj_id, ilPropertyFormGUI $a_form)
	{
		global $ilLog, $lng;

		// Return if no participant is enabled for export and the current object is not released
		include_once './Services/WebServices/ECS/classes/class.ilECSExport.php';
		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';

		$exportablePart = ilECSParticipantSettings::getExportableParticipants();
		if(!$exportablePart and !ilECSExport::_isExported($a_obj_id))
		{
			return true;
		}

		$lng->loadLanguageModule('ecs');

		// show ecs property form section
		$ecs = new ilFormSectionHeaderGUI();
		$ecs->setTitle($lng->txt('ecs_export'));
		$a_form->addItem($ecs);


		// release or not
		$exp = new ilRadioGroupInputGUI($lng->txt('ecs_export_obj_settings'),'ecs_export');
		$exp->setRequired(true);
		$exp->setValue(ilECSExport::_isExported($a_obj_id) ? 1 : 0);
		$off = new ilRadioOption($lng->txt('ecs_export_disabled'),0);
		$exp->addOption($off);
		$on = new ilRadioOption($lng->txt('ecs_export_enabled'),1);
		$exp->addOption($on);
		$a_form->addItem($exp);

		// Show all exportable participants
		$publish_for = new ilCheckboxGroupInputGUI($lng->txt('ecs_publish_for'),'ecs_sid');

		// @TODO: Active checkboxes for recipients
		//$publish_for->setValue((array) $members);

		// Read receivers
		$receivers = array();
		foreach(ilECSExport::getExportServerIds($a_obj_id) as $sid)
		{
			$exp = new ilECSExport($sid, $a_obj_id);
			$eid = $exp->getEContentId();
			try
			{
				include_once './Services/WebServices/ECS/classes/class.ilECSEContentReader.php';
				$econtent_reader = new ilECSEContentReader($sid,$eid);
				$econtent_reader->read(true);
				$details = $econtent_reader->getEContentDetails();
				if($details instanceof ilECSEContentDetails)
				{
					foreach($details->getReceivers() as $mid)
					{
						$receivers[] = $sid.'_'.$mid;
					}
					#$owner = $details->getFirstSender();
				}
			}
			catch(ilECSConnectorException $exc)
			{
				$ilLog->write(__METHOD__.': Error connecting to ECS server. '.$exc->getMessage());
			}
			catch(ilECSReaderException $exc)
			{
				$ilLog->write(__METHOD__.': Error parsing ECS query: '.$exc->getMessage());
			}
		}

		$publish_for->setValue($receivers);

		foreach($exportablePart as $pInfo)
		{
			include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
			$partSetting = new ilECSParticipantSetting($pInfo['sid'], $pInfo['mid']);

			$com = new ilCheckboxInputGUI(
				$partSetting->getCommunityName().': '.$partSetting->getTitle(),
				'sid_mid'
			);
			$com->setValue($pInfo['sid'].'_'.$pInfo['mid']);
			$publish_for->addOption($com);
		}
		$on->addSubItem($publish_for);
		return true;
	}
	
	/**
	 * Update ECS Export Settings
	 *
	 * @param ilObject $a_content_object	 
	 * @return bool
	 */
	public function updateECSExportSettings(ilObject $a_content_object)
	{	
		// Parse post data
		$mids = array();
		foreach((array) $_POST['ecs_sid'] as $sid_mid)
		{
			$tmp = explode('_',$sid_mid);
			$mids[$tmp[0]][] = $tmp[1];
		}

		try
		{
			include_once './Services/WebServices/ECS/classes/class.ilECSCommunitiesCache.php';
			include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';

			// Update for each server
			foreach(ilECSParticipantSettings::getExportServers() as $server_id)
			{
				// Export
				$export = true;
				if(!$_POST['ecs_export'])
				{
					$export = false;
				}
				if(!count($mids[$server_id]))
				{
					$export = false;
				}
				self::handleECSSettings(
					$a_content_object,
					$server_id,
					$export,
					ilECSCommunitiesCache::getInstance()->lookupOwnId($server_id,$mids[$server_id][0]),
					$mids[$server_id]
				);
			}
			return true;
		}
		catch(ilECSConnectorException $exc)
		{
			ilUtil::sendFailure('Error connecting to ECS server: '.$exc->getMessage());
			return false;
		}
		catch(ilECSContentWriterException $exc)
		{
			ilUtil::sendFailure('Course export failed with message: '.$exc->getMessage());
			return false;
		}
		return true;

		/*
		global $rbacadmin, $lng;	
		  
		if($_POST['ecs_export'] and !$_POST['ecs_owner'])
		{
			ilUtil::sendFailure($lng->txt('ecs_no_owner'));
			return false;
		}
		try
		{
		    // deprecated (see below)
			self::handleECSSettings((bool)$_POST['ecs_export'],(int) $_POST['ecs_owner'],(array) $_POST['ecs_mids']);
			
			// update performed now grant/revoke ecs user permissions
			include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');
			$export = new ilECSExport($a_content_obj->getId());
			if($export->isExported())
			{
				// Grant permission
				$rbacadmin->grantPermission($ecs_settings->getGlobalRole(),
					ilRbacReview::_getOperationIdsByName(array('join','visible')),
					$a_content_obj->getRefId());
				
			}
			else
			{
				$rbacadmin->revokePermission($a_content_obj->getRefId(),
					$ecs_settings->getGlobalRole());
			}
		}
		catch(ilECSConnectorException $exc)
		{
			ilUtil::sendFailure('Error connecting to ECS server: '.$exc->getMessage());
			return false;
		}
		catch(ilECSContentWriterException $exc)
		{
			ilUtil::sendFailure('Course export failed with message: '.$exc->getMessage());
			return false;
		}
		return true;		 
		*/
	}
	
	/**
	 * Save ECS settings (add- update- deleteResource)
	 *
	 * @param ilObject $a_content_object flag
	 * @param int $a_server_id
	 * @param bool $a_export
	 * @param int $a_owner
	 * @param array array of participant mids
	 * @throws ilECSConnectorException, ilECSContentWriterException
	 */
	protected static function handleECSSettings(ilObject $a_content_object,$a_server_id,$a_export,$a_owner,$a_mids)
	{
		try
		{
			include_once('./Services/WebServices/ECS/classes/class.ilECSContentWriter.php');
			$writer = new ilECSContentWriter($a_content_object,$a_server_id);
			$writer->setExportable($a_export);
			$writer->setOwnerId($a_owner);
			$writer->setParticipantIds((array) $a_mids);
			$writer->refresh();
		}
		catch(ilECSConnectorException $exc)
		{
			throw $exc;
		}
		catch(ilECSContentWriterException $exc)
		{
			throw $exc;
		}
	}	
		
	/**
	 * Update ECS Content
	 *
	 * @param ilObject $a_content_object
	 * @return bool
	 */
	public function updateECSContent(ilObject $a_content_object)
	{
		global $ilLog;

		include_once './Services/WebServices/ECS/classes/class.ilECSExport.php';

		$export_servers = ilECSExport::getExportServerIds($a_content_object->getId());
		foreach($export_servers as $server_id)
		{
			include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php';
			if(ilECSSetting::getInstanceByServerId($server_id)->isEnabled())
			{
				try 
				{
					include_once('./Services/WebServices/ECS/classes/class.ilECSContentWriter.php');
					$writer = new ilECSContentWriter($a_content_object,$server_id);
					$writer->refreshSettings();
					return true;
			 	}
				catch(ilException $exc)
				{
					$ilLog->write(__METHOD__.': Cannot save ECS settings. '.$exc->getMessage());
					return false;
				}
			}
		}
	}
}

?>