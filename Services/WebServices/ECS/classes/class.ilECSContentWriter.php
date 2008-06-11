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
* Handles request like update delete addEContent 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS 
*/
class ilECSContentWriter
{
	const UPDATE_ALL = 1;
	const UPDATE_SETTINGS_ONLY = 2;
	
	private $mode = 0;
	
	protected $log;
	
	protected $content_obj = null;
	protected $export_settings = null;
	
	protected $exportable = true;
	protected $owner = 0;
	protected $mids = array();
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object content obj (e.g course_obj)
	 * 
	 */
	public function __construct($a_cont_obj)
	{
	 	global $ilLog;
	 	
	 	$this->log = $ilLog;
	 	
	 	$this->content_obj = $a_cont_obj;

	 	include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');
	 	$this->export_settings = new ilECSExport($this->content_obj->getId());
	}
	
	/**
	 * handle delete
	 * Objects that are moved to the trash call ECS-Remove
	 *
	 * @access public
	 * @param int obj_id
	 * @return bool
	 * @static
	 */
	public static function _handleDelete($a_subbtree_nodes)
	{
		include_once('./Services/WebServices/ECS/classes/class.ilECSSettings.php');
		if(!ilECSSettings::_getInstance()->isEnabled())
		{
			return false;
		}
		include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');
		$exported = ilECSExport::_getExportedIDs();
		foreach($a_subbtree_nodes as $node)
		{
			if(in_array($node['obj_id'],$exported))
			{
				if($content_obj = ilObjectFactory::getInstanceByRefId($node['child'],false))
				{
					try
					{
						$writer = new ilECSContentWriter($content_obj);
						$writer->deleteECSContent();
					}
					catch(ilECSContentWriterException $exc)
					{
						continue;
					}
				}
			}
		}
		
		
	}
	
	/**
	 * set exportable
	 *
	 * @access public
	 * @param bool status
	 * 
	 */
	public function setExportable($a_status)
	{
	 	$this->exportable = $a_status;
	}
	
	/**
	 * is exportable
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function isExportable()
	{
	 	return $this->exportable;
	}
	
	/**
	 * set owner mid
	 *
	 * @access public
	 * @param int owner mid
	 * 
	 */
	public function setOwnerId($a_id)
	{
	 	$this->owner = $a_id;
	}
	
	/**
	 * get owner id
	 *
	 * @access public
	 * 
	 */
	public function getOwnerId()
	{
	 	return $this->owner;
	}
	
	/**
	 * set participant ids
	 *
	 * @access public
	 * @param array array of participant mids
	 * 
	 */
	public function setParticipantIds($a_mids)
	{
	 	$this->mids = $a_mids;
	}
	
	/**
	 * get participant ids
	 *
	 * @access public
	 * 
	 */
	public function getParticipantIds()
	{
	 	return $this->mids;
	}
	
	/**
	 * Refresh (add- update- delete Econtent)
	 *
	 * @access public
	 * @throws ilConnectorException, ilECSContentWriterException 
	 */
	public function refresh()
	{
		$this->mode = self::UPDATE_ALL;
		try
		{
			if($this->export_settings->isExported())
			{
				if($this->isExportable())
				{
					// Update Econtent
					return $this->updateECSContent();
				}
				else
				{
					// Delete EContent
					return $this->deleteECSContent();
				}
			}
			else
			{
				if($this->isExportable())
				{
					// Add Econtent
					return $this->addECSContent();
				}
				else
				{
					// Nothing to do
				}
			}
			return true;
		}
		catch(ilECSConnectorException $exc)
		{
			$this->log->write(__METHOD__.': Error connecting to ECS server. '.$exc->getMessage());
			throw $exc;
		}
		catch(ilECSContentWriterException $exc)
		{
			$this->log->write(__METHOD__.': Cannot update ECS content. '.$exc->getMessage());
			throw $exc;
		}
	}
	
	/**
	 * Refresh Settings
	 *
	 * @access public
	 * @throws il 
	 */
	public function refreshSettings()
	{
	 	$this->mode = self::UPDATE_SETTINGS_ONLY;
	 	
		try
		{
			if($this->export_settings->isExported())
			{
				// Update Econtent
				return $this->updateECSContent();
			}
			else
			{
				// Nothing to do
				return true;
			}
		}
		catch(ilECSConnectorException $exc)
		{
			$this->log->write(__METHOD__.': Error connecting to ECS server. '.$exc->getMessage());
			throw $exc;
		}
		catch(ilECSContentWriterException $exc)
		{
			$this->log->write(__METHOD__.': Cannot update ECS content. '.$exc->getMessage());
			throw $exc;
		}
	}
	
	/**
	 * delete ecs content
	 *
	 * @access public
	 * @throws ilECSContentWriterException
	 */
	public function deleteECSContent()
	{
	 	include_once('./Services/WebServices/ECS/classes/class.ilECSConnector.php');
	 	include_once('./Services/WebServices/ECS/classes/class.ilECSConnectorException.php');

		try
		{
			$this->log->write(__METHOD__.': Start deleting ECS content...');
			
			$connector = new ilECSConnector();
			if(!$this->export_settings->getEContentId())
			{
				$this->log->write(__METHOD__.': Missing eid. Aborting.');
				throw new ilECSContentWriterException('Missing ECS content ID. Aborting.');
			}
			$connector->deleteResource($this->export_settings->getEContentId());
			$this->export_settings->setExported(false);
			$this->export_settings->save();
		}
	 	catch(ilECSConnectorException $exc)
	 	{
	 		throw $exc;
	 	}
	 	return true;
	}
	
	/**
	 * Add ECSContent
	 *
	 * @access public
	 * @throws ilConnectorException, ilECSContentWriterException 
	 */
	public function addECSContent()
	{
	 	include_once('./Services/WebServices/ECS/classes/class.ilECSConnector.php');
	 	include_once('./Services/WebServices/ECS/classes/class.ilECSConnectorException.php');
	 	
	 	try
	 	{
			$this->log->write(__METHOD__.': Starting course export...');
			
			// construct new json object and set settings from content obj
			$this->createJSON();
			$this->updateJSON();
			
	 		$connector = new ilECSConnector();
	 		$econtent_id = $connector->addResource(json_encode($this->json));
	
			$this->export_settings->setExported(true);
			$this->export_settings->setEContentId($econtent_id);
			$this->export_settings->save();
			
			// Send mail
			$this->sendNewContentNotification();
			
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
	 * update ECS content
	 *
	 * @access public
	 * @throws ilECSConnectorException, ilECSContentWriterException
	 */
	public function updateECSContent()
	{
	 	try
	 	{
	 		include_once('./Services/WebServices/ECS/classes/class.ilECSEContentReader.php');
	 		$reader = new ilECSEContentReader($this->export_settings->getEContentId());
	 		$reader->read();
	 		$content = $reader->getEContent();
	 		if(!is_array($content) or !is_object($content[0]))
	 		{
	 			$this->log->write(__METHOD__.': Error reading EContent with id: '.$this->export_settings->getEContentId());
	 			include_once('./Services/WebServices/ECS/classes/class.ilECSContentWriterException.php');
	 			throw new ilECSContentWriterException('Error reading EContent. Aborting');
	 		}
	 		$this->json = $content[0];
	 		$this->updateJSON();
	 		$connector = new ilECSConnector();
	 		#var_dump("<pre>",json_encode($this->json),"</pre>");
			
	 		$connector->updateResource($this->export_settings->getEContentId(),json_encode($this->json));
	 	}
	 	catch(ilECSConnectorException $exc)
	 	{
	 		throw $exc;
	 	}
	}
	
	/**
	 * send notifications about new EContent
	 *
	 * @access private
	 * @return bool
	 */
	private function sendNewContentNotification()
	{
		include_once('Services/WebServices/ECS/classes/class.ilECSSettings.php');
		$settings = ilECSSettings::_getInstance();
		if(!count($rcps = $settings->getApprovalRecipients()))
		{
			return true;
		}
		
		include_once('./Services/Mail/classes/class.ilMail.php');
		include_once('./Services/Language/classes/class.ilLanguageFactory.php');

		$lang = ilLanguageFactory::_getLanguage();
		$lang->loadLanguageModule('ecs');
		
		$mail = new ilMail(6);
		$message = $lang->txt('ecs_export_created_body_a')."\n\n";
		$message .= $lang->txt('title').': '.$this->content_obj->getTitle()."\n";
		if(strlen($desc = $this->content_obj->getDescription()))
		{
			$message .= $lang->txt('desc').': '.$desc."\n";
		}

		include_once('classes/class.ilLink.php');
		$href = ilLink::_getStaticLink($this->content_obj->getRefId(),'crs',true);
		$message .= $lang->txt("perma_link").': '.$href."\n\n";
		$message .= ilMail::_getAutoGeneratedMessageString();
		
		$error = $mail->sendMail($settings->getApprovalRecipientsAsString(),
			'','',
			$lang->txt('ecs_new_approval_subject'),
			$message,array(),array('normal'));
		
		return true;
	
	}
	
	/**
	 * Create new JSON object
	 *
	 * @access private
	 */
	private function createJSON()
	{
	 	include_once('./Services/WebServices/ECS/classes/class.ilECSEContent.php');
	 	$this->json = new ilECSEContent();
	 	return true;
	}
	
	/**
	 * update json object (read settings from content object)
	 *
	 * @access private
	 * @throws ilECSContentWriterException
	 */
	private function updateJSON()
	{
		// General fields
		######################################################
		include_once('./classes/class.ilLink.php');
		$this->json->setURL(ilLink::_getLink($this->content_obj->getRefId(),$this->content_obj->getType()));
		$this->json->setTitle($this->content_obj->getTitle());

		// Ownership EligibleMembers
		######################################################
		if($this->mode == self::UPDATE_ALL)
		{
			// Eligible members [0] is owner
			if(!$this->getOwnerId())
			{
				throw new ilECSContentWriterException('Missing ECS owner id.');
			}
			$this->json->setOwner($this->getOwnerId());
			$members = array_unique($this->getParticipantIds());
			$this->json->setEligibleMembers($members);
		}	

		// meta language
		include_once('./Services/MetaData/classes/class.ilMDLanguage.php');
		$this->json->setLanguage(ilMDLanguage::_lookupFirstLanguage($this->content_obj->getId(),$this->content_obj->getId(),$this->content_obj->getType()));
		$this->json->setStatus($this->content_obj->isActivated() ? 'online' : 'offline');
		$this->json->setInfo($this->content_obj->getDescription());

		// Optional fields
		######################################################
		include_once('./Services/WebServices/ECS/classes/class.ilECSUtils.php');
		include_once('./Services/WebServices/ECS/classes/class.ilECSDataMappingSettings.php');
		include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
		include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
		$mappings = ilECSDataMappingSettings::_getInstance();
		$values = ilAdvancedMDValues::_getValuesByObjId($this->content_obj->getId());

		// Study courses
		if($field = $mappings->getMappingByECSName('study_courses'))
		{
			$value = isset($values[$field]) ? $values[$field] : '';
			$this->json->setStudyCourses($value);
		}
		// Lecturer
		if($field = $mappings->getMappingByECSName('lecturer'))
		{
			$value = isset($values[$field]) ? $values[$field] : '';
			$this->json->setLecturers($value);
		}
		// CourseType
		if($field = $mappings->getMappingByECSName('courseType'))
		{
			$value = isset($values[$field]) ? $values[$field] : '';
			$this->json->setCourseType($value);
		}
		// Course ID
		if($field = $mappings->getMappingByECSName('courseID'))
		{
			$value = isset($values[$field]) ? $values[$field] : '';
			$this->json->setCourseID($value);
		}
		// Credits
		if($field = $mappings->getMappingByECSName('credits'))
		{
			$value = isset($values[$field]) ? $values[$field] : '';
			$this->json->setCredits($value);
		}
		// SWS
		if($field = $mappings->getMappingByECSName('semester_hours'))
		{
			$value = isset($values[$field]) ? $values[$field] : '';
			$this->json->setSemesterHours($value);
		}
		// Term
		if($field = $mappings->getMappingByECSName('term'))
		{
			$value = isset($values[$field]) ? $values[$field] : '';
			$this->json->setTerm($value);
		}
		// TIME PLACE OBJECT ########################
		if($field = $mappings->getMappingByECSName('begin'))
		{
			$value = isset($values[$field]) ? $values[$field] : '';
			$this->json->getTimePlace()->setBegin($value);
		}
		if($field = $mappings->getMappingByECSName('end'))
		{
			$value = isset($values[$field]) ? $values[$field] : '';
			$this->json->getTimePlace()->setEnd($value);
		}
		if($field = $mappings->getMappingByECSName('room'))
		{
			$value = isset($values[$field]) ? $values[$field] : '';
			$this->json->getTimePlace()->setRoom($value);
		}
		if($field = $mappings->getMappingByECSName('cycle'))
		{
			$value = isset($values[$field]) ? $values[$field] : '';
			$this->json->getTimePlace()->setCycle($value);
		}
		return true;
	}
}


?>