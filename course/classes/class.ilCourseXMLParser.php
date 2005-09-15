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


include_once("Services/MetaData/classes/class.ilMDSaxParser.php");
include_once("Services/MetaData/classes/class.ilMD.php");
include_once('classes/class.ilObjUser.php');

/**
* Course XML Parser
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @extends ilMDSaxParser
* @package course
*/

include_once 'course/classes/class.ilCourseMembers.php';
include_once 'course/classes/class.ilCourseWaitingList.php';

class ilCourseXMLParser extends ilMDSaxParser
{
	var $lng;
	var $md_obj = null;			// current meta data object

	/**
	* Constructor
	*
	* @param	object		$a_content_object	must be of type ilObjContentObject
	*											ilObjTest or ilObjQuestionPool
	* @param	string		$a_xml_file			xml file
	* @param	string		$a_subdir			subdirectory in import directory
	* @access	public
	*/
	function ilCourseXMLParser(&$a_course_obj, $a_xml_file = '')
	{
		global $lng;

		parent::ilMDSaxParser($a_xml_file);

		$this->course_obj =& $a_course_obj;
		$this->course_members = new ilCourseMembers($this->course_obj);
		$this->course_waiting_list = new ilCourseWaitingList($this->course_obj->getId());

		$this->md_obj = new ilMD($this->course_obj->getId(),0,'crs');
		
		$this->setMDObject($this->md_obj);

		$this->lng =& $lng;
	}

	/**
	* set event handlers
	*
	* @param	resource	reference to the xml parser
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	


	/**
	* handler for begin of element
	*
	* @param	resource	$a_xml_parser		xml parser
	* @param	string		$a_name				element name
	* @param	array		$a_attribs			element attributes array
	*/
	function handlerBeginTag($a_xml_parser,$a_name,$a_attribs)
	{
		if($this->in_meta_data)
		{
			parent::handlerBeginTag($a_xml_parser,$a_name,$a_attribs);

			return;
		}

		switch($a_name)
		{
			case 'Admin':
				if($id_data = $this->__parseId($a_attribs['id']))
				{
					if($id_data['local'] or $id_data['imported'])
					{
						$this->course_members->add(new ilObjUser($id_data['usr_id']),
												   $this->course_members->ROLE_ADMIN,
												   $a_attribs['notification'] == 'Yes' ? 1 : 0,
												   $a_attribs['passed'] == 'Yes' ? 1 : 0);
					}
				}
				break;

			case 'Tutor':
				if($id_data = $this->__parseId($a_attribs['id']))
				{
					if($id_data['local'] or $id_data['imported'])
					{
						$this->course_members->add(new ilObjUser($id_data['usr_id']),
												   $this->course_members->ROLE_TUTOR,
												   $a_attribs['notification'] == 'Yes' ? 1 : 0,
												   $a_attribs['passed'] == 'Yes' ? 1 : 0);
					}
				}
				break;

			case 'Member':
				if($id_data = $this->__parseId($a_attribs['id']))
				{
					if($id_data['local'] or $id_data['imported'])
					{
						$this->course_members->add(new ilObjUser($id_data['usr_id']),
												   $this->course_members->ROLE_MEMBER,
												   $a_attribs['blocked'] == 'Yes' ? 
												   $this->course_members->STATUS_BLOCKED : 
												   $this->course_members->STATUS_UNBLOCKED,
												   $a_attribs['passed'] == 'Yes' ? 1 : 0);
					}
				}
				break;

			case 'Subscriber':
				if($id_data = $this->__parseId($a_attribs['id']))
				{
					if($id_data['local'] or $id_data['imported'])
					{
						$this->course_members->addSubscriber($id_data['usr_id']);
						$this->course_members->updateSubscriptionTime($id_data['usr_id'],$a_attribs['subscriptionTime']);
					}
				}
				break;

			case 'WaitingList':
				if($id_data = $this->__parseId($a_attribs['id']))
				{
					if($id_data['local'] or $id_data['imported'])
					{
						$this->course_waiting_list->addToList($id_data['usr_id']);
						$this->course_waiting_list->updateSubscriptionTime($id_data['usr_id'],$a_attribs['subscriptionTime']);
					}
				}
				break;
				

			case 'Settings':
				$this->in_settings = true;
				break;
			case 'Availability':
				$this->in_availability = true;
				break;

			case 'NotAvailable':
				$this->course_obj->setOfflineStatus(true);
				break;

			case 'Unlimited':
				if($this->in_availability)
				{
					$this->course_obj->setActivationUnlimitedStatus(true);
					$this->course_obj->setOfflineStatus(false);
				}
				break;
			case 'TemporarilyAvailable':
				if($this->in_availability)
				{
					$this->course_obj->setActivationUnlimitedStatus(false);
					$this->course_obj->setOfflineStatus(false);
				}
				break;

			case 'Start':
				break;
				
			case 'End':
				break;

			case 'Syllabus':
				break;

			case 'Contact':
				break;

			case 'Name':
			case 'Responsibility':
			case 'Phone':
			case 'Email':
			case 'Consultation':
				break;

			case 'Registration':
				$this->in_registration = true;
				
				switch($a_attribs['registrationType'])
				{
					case 'Confirmation':
						$this->course_obj->setSubscriptionType($this->course_obj->SUBSCRIPTION_CONFIRMATION);
						break;

					case 'Direct':
						$this->course_obj->setSubscriptionType($this->course_obj->SUBSCRIPTION_DIRECT);
						break;
						
					case 'Password':
						$this->course_obj->setSubscriptionType($this->course_obj->SUBSCRIPTION_PASSWORD);
						break;
				}
				$this->course_obj->setSubscriptionMaxMembers((int) $a_attribs['maxMembers']);
				$this->course_obj->setSubscriptionNotify($a_attribs['notification'] == 'Yes' ? true : false);
				break;

			case 'Sort':
				switch($a_attribs['type'])
				{
					case 'Manual':
						$this->course_obj->setOrderType($this->course_obj->SORT_MANUAL);
						break;

					case 'Title':
						$this->course_obj->setOrderType($this->course_obj->SORT_TITLE);
						break;

					case 'Activation':
						$this->course_obj->setOrderType($this->course_obj->SORT_ACTIVATION);
						break;
				}
				break;


			case 'Archive':
				$this->in_archive = true;
				switch($a_attribs['Access'])
				{
					case 'Disabled':
						$this->course_obj->setArchiveType($this->course_obj->ARCHIVE_DISABLED);
						break;

					case 'Read':
						$this->course_obj->setArchiveType($this->course_obj->ARCHIVE_READ);
						break;

					case 'Download':
						$this->course_obj->setArchiveType($this->course_obj->ARCHIVE_DOWNLOAD);
						break;
				}
				break;

			case 'Disabled':
				$this->course_obj->setSubscriptionType($this->course_obj->SUBSCRIPTION_DEACTIVATED);
				break;
				
			case "MetaData":
				$this->in_meta_data = true;
				parent::handlerBeginTag($a_xml_parser,$a_name,$a_attribs);
				break;

		}
	}



	/**
	* handler for end of element
	*
	* @param	resource	$a_xml_parser		xml parser
	* @param	string		$a_name				element name
	*/
	function handlerEndTag($a_xml_parser,$a_name)
	{
		if($this->in_meta_data)
		{
			parent::handlerEndTag($a_xml_parser,$a_name);
		}

		switch($a_name)
		{
			case 'Course':
				$this->course_obj->updateSettings();
				break;

			case 'Settings':
				$this->in_settings = false;
				break;

			case 'Availability':
				$this->in_availability = false;
				break;

			case 'Registration':
				$this->in_registration = false;
				break;

			case 'Archive':
				$this->in_archive = false;
				break;

			case 'Start':
				if($this->in_availability)
				{
					$this->course_obj->setActivationStart(trim($this->cdata));
				}
				if($this->in_registration)
				{
					$this->course_obj->setSubscriptionStart(trim($this->cdata));
				}
				if($this->in_archive)
				{
					$this->course_obj->setArchiveStart(trim($this->cdata));
				}
				break;

			case 'End':
				if($this->in_availability)
				{
					$this->course_obj->setActivationEnd(trim($this->cdata));
				}
				if($this->in_registration)
				{
					$this->course_obj->setSubscriptionEnd(trim($this->cdata));
				}
				if($this->in_archive)
				{
					$this->course_obj->setArchiveEnd(trim($this->cdata));
				}
				break;

			case 'Syllabus':
				$this->course_obj->setSyllabus(trim($this->cdata));
				break;

			case 'Name':
				$this->course_obj->setContactName(trim($this->cdata));
				break;

			case 'Responsibility':
				$this->course_obj->setContactResponsibility(trim($this->cdata));
				break;

			case 'Phone':
				$this->course_obj->setContactPhone(trim($this->cdata));
				break;

			case 'Email':
				$this->course_obj->setContactEmail(trim($this->cdata));
				break;

			case 'Consultation':
				$this->course_obj->setContactConsultation(trim($this->cdata));
				break;

			case 'Password':
				$this->course_obj->setSubscriptionPassword(trim($this->cdata));
				break;

			case 'MetaData':
				$this->in_meta_data = false;
				parent::handlerEndTag($a_xml_parser,$a_name);
				break;
		}
		$this->cdata = '';

		return;
	}

	/**
	* handler for character data
	*
	* @param	resource	$a_xml_parser		xml parser
	* @param	string		$a_data				character data
	*/
	function handlerCharacterData($a_xml_parser,$a_data)
	{
		// call meta data handler
		if($this->in_meta_data)
		{
			parent::handlerCharacterData($a_xml_parser,$a_data);
		}
		if($a_data != "\n")
		{
			// Replace multiple tabs with one space
			$a_data = preg_replace("/\t+/"," ",$a_data);

			$this->cdata .= $a_data;
		}


	}

	// PRIVATE
	function __parseId($a_id)
	{
		global $ilias;

		$fields = explode('_',$a_id);

		if(!is_array($fields) or
		   $fields[0] != 'il' or
		   !is_numeric($fields[1]) or
		   $fields[2] != 'usr' or
		   !is_numeric($fields[3]))
		{
			return false;
		}
		if($id = ilObjUser::_getImportedUserId($a_id))
		{
			return array('imported' => true,
						 'local' => false,
						 'usr_id' => $id);
		}
		if(($fields[1] == $ilias->getSetting('inst_id',0)) and strlen(ilObjUser::_lookupName($fields[3])))
		{
			return array('imported' => false,
						 'local' => true,
						 'usr_id' => $fields[3]);
		}
		return false;
	}

}
?>