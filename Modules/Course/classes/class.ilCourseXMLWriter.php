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

/**
* XML writer class
*
* Class to simplify manual writing of xml documents.
* It only supports writing xml sequentially, because the xml document
* is saved in a string with no additional structure information.
* The author is responsible for well-formedness and validity
* of the xml document.
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*/

include_once "./classes/class.ilXmlWriter.php";

class ilCourseXMLWriter extends ilXmlWriter
{
	var $ilias;

	var $xml;
	var $course_obj;

	/**
	* constructor
	* @param	string	xml version
	* @param	string	output encoding
	* @param	string	input encoding
	* @access	public
	*/
	function ilCourseXMLWriter(&$course_obj)
	{
		global $ilias;

		parent::ilXmlWriter();

		$this->EXPORT_VERSION = "2";

		$this->ilias =& $ilias;
		$this->course_obj =& $course_obj;
	}

	function start()
	{
		$this->__buildHeader();
		$this->__buildMetaData();
		$this->__buildAdvancedMetaData();
		$this->__buildAdmin();
		$this->__buildTutor();
		$this->__buildMember();
		$this->__buildSubscriber();
		$this->__buildWaitingList();
		
		$this->__buildSetting();

		#$this->__buildObject($this->course_obj->getRefId());
		
		$this->__buildFooter();
	}

	function getXML()
	{
		#var_dump("<pre>", htmlentities($this->xmlDumpMem()),"<pre>");
		return $this->xmlDumpMem();
	}

	// Called from nested class
	function modifyExportIdentifier($a_tag, $a_param, $a_value)
	{
		if ($a_tag == "Identifier" && $a_param == "Entry")
		{
			$a_value = "il_".$this->ilias->getSetting('inst_id')."_crs_".$this->course_obj->getId();
		}

		return $a_value;
	}

	// PRIVATE
	function __buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE Course PUBLIC \"-//ILIAS//DTD Course//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_course_3_8.dtd\">"); 
		$this->xmlSetGenCmt("Export of ILIAS course ". $this->course_obj->getId()." of installation ".$this->ilias->getSetting('inst_id').".");
		$this->xmlHeader();

		$attrs["exportVersion"] = $this->EXPORT_VERSION;
		$attrs["id"] = "il_".$this->ilias->getSetting('inst_id').'_crs_'.$this->course_obj->getId();
		$this->xmlStartTag("Course", $attrs);

		return true;
	}
	function __buildMetaData()
	{
		include_once 'Services/MetaData/classes/class.ilMD2XML.php';

		$md2xml = new ilMD2XML($this->course_obj->getId(),$this->course_obj->getId(),'crs');
		$md2xml->startExport();
		$this->appendXML($md2xml->getXML());

		return true;
	}
	
	/**
	 * Build advanced meta data
	 *
	 * @access private
	 * 
	 */
	private function __buildAdvancedMetaData()
	{
	 	include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
	 	ilAdvancedMDValues::_appendXMLByObjId($this,$this->course_obj->getId());
	}
	
	function __buildAdmin()
	{
		$this->course_obj->initCourseMemberObject();

		foreach($this->course_obj->members_obj->getAdmins() as $id)
		{
			$attr['id'] = 'il_'.$this->ilias->getSetting('inst_id').'_usr_'.$id;
			$attr['notification'] = ($this->course_obj->members_obj->isNotificationEnabled($id)) ? 'Yes' : 'No';
			$attr['passed'] = $this->course_obj->members_obj->hasPassed($id) ? 'Yes' : 'No';

			$this->xmlStartTag('Admin',$attr);
			$this->xmlEndTag('Admin');
		}
		return true;
	}

	function __buildTutor()
	{
		$this->course_obj->initCourseMemberObject();

		foreach($this->course_obj->members_obj->getTutors() as $id)
		{
			$attr['id'] = 'il_'.$this->ilias->getSetting('inst_id').'_usr_'.$id;
			$attr['notification'] = ($this->course_obj->members_obj->isNotificationEnabled($id)) ? 'Yes' : 'No';
			$attr['passed'] = $this->course_obj->members_obj->hasPassed($id) ? 'Yes' : 'No';

			$this->xmlStartTag('Tutor',$attr);
			$this->xmlEndTag('Tutor');
		}
		return true;
	}
	function __buildMember()
	{
		$this->course_obj->initCourseMemberObject();

		foreach($this->course_obj->members_obj->getMembers() as $id)
		{
			$attr['id'] = 'il_'.$this->ilias->getSetting('inst_id').'_usr_'.$id;
			$attr['blocked'] = ($this->course_obj->members_obj->isBlocked($id)) ? 'Yes' : 'No';
			$attr['passed'] = $this->course_obj->members_obj->hasPassed($id) ? 'Yes' : 'No';

			$this->xmlStartTag('Member',$attr);
			$this->xmlEndTag('Member');
		}
		return true;
	}

	function __buildSubscriber()
	{
		$this->course_obj->initCourseMemberObject();

		foreach($this->course_obj->members_obj->getSubscribers() as $id)
		{
			$data = $this->course_obj->members_obj->getSubscriberData($id);

			$attr['id'] = 'il_'.$this->ilias->getSetting('inst_id').'_usr_'.$id;
			$attr['subscriptionTime'] = $data['time'];

			$this->xmlStartTag('Subscriber',$attr);
			$this->xmlEndTag('Subscriber');
		}
		return true;
	}

	function __buildWaitingList()
	{
		include_once 'Modules/Course/classes/class.ilCourseWaitingList.php';

		$waiting_list = new ilCourseWaitingList($this->course_obj->getId());
		
		foreach($waiting_list->getAllUsers() as $data)
		{
			$attr['id'] = 'il_'.$this->ilias->getSetting('inst_id').'_usr_'.$data['usr_id'];
			$attr['position'] = $data['position'];
			$attr['subscriptionTime'] = $data['time'];
			
			$this->xmlStartTag('WaitingList',$attr);
			$this->xmlEndTag('WaitingList');
		}
		return true;
	}


	function __buildSetting()
	{
		$this->xmlStartTag('Settings');

		// Availability
		$this->xmlStartTag('Availability');
		if($this->course_obj->getOfflineStatus())
		{
			$this->xmlElement('NotAvailable');
		}
		elseif($this->course_obj->getActivationUnlimitedStatus())
		{
			$this->xmlElement('Unlimited');
		}
		else
		{
			$this->xmlStartTag('TemporarilyAvailable');
			$this->xmlElement('Start',null,$this->course_obj->getActivationStart());
			$this->xmlElement('End',null,$this->course_obj->getActivationEnd());
			$this->xmlEndTag('TemporarilyAvailable');
		}
		$this->xmlEndTag('Availability');

		// Syllabus
		$this->xmlElement('Syllabus',null,$this->course_obj->getSyllabus());
		$this->xmlElement('ImportantInformation',null,$this->course_obj->getImportantInformation());
		
		
		// Contact
		$this->xmlStartTag('Contact');
		$this->xmlElement('Name',null,$this->course_obj->getContactName());
		$this->xmlElement('Responsibility',null,$this->course_obj->getContactResponsibility());
		$this->xmlElement('Phone',null,$this->course_obj->getContactPhone());
		$this->xmlElement('Email',null,$this->course_obj->getContactEmail());
		$this->xmlElement('Consultation',null,$this->course_obj->getContactConsultation());
		$this->xmlEndTag('Contact');

		// Registration
		$attr = array();

		if($this->course_obj->getSubscriptionType() == IL_CRS_SUBSCRIPTION_CONFIRMATION)
		{
			$attr['registrationType'] = 'Confirmation';
		}
		elseif($this->course_obj->getSubscriptionType() == IL_CRS_SUBSCRIPTION_DIRECT)
		{
			$attr['registrationType'] = 'Direct';
		}
		else
		{
			$attr['registrationType'] = 'Password';
		}

		$attr['maxMembers'] = $this->course_obj->getSubscriptionMaxMembers();
		$attr['notification'] = $this->course_obj->getSubscriptionNotify() ? 'Yes' : 'No';
		$attr['waitinList'] = $this->course_obj->enabledWaitingList() ? 'Yes' : 'No';

		$this->xmlStartTag('Registration',$attr);
		
		if($this->course_obj->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_DEACTIVATED)
		{
			$this->xmlElement('Disabled');
		}
		elseif($this->course_obj->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_UNLIMITED)
		{
			$this->xmlElement('Unlimited');
		}
		else
		{
			$this->xmlStartTag('TemporarilyAvailable');
			$this->xmlElement('Start',null,$this->course_obj->getSubscriptionStart());
			$this->xmlElement('End',null,$this->course_obj->getSubscriptionEnd());
			$this->xmlEndTag('TemporarilyAvailable');
		}
		if(strlen($pwd = $this->course_obj->getSubscriptionPassword()))
		{
			$this->xmlElement('Password',null,$pwd);
		}
		$this->xmlEndTag('Registration');

		// Sort
		$attr = array();
		if($this->course_obj->getOrderType() == IL_CRS_SORT_MANUAL)
		{
			$attr['type'] = 'Manual';
		}
		elseif($this->course_obj->getOrderType() == IL_CRS_SORT_TITLE)
		{
			$attr['type'] = 'Title';
		}
		else
		{
			$attr['type'] = 'Activation';
		}
		$this->xmlElement('Sort',$attr);

		// Archives
		$attr = array();
		if($this->course_obj->getViewMode() != IL_CRS_VIEW_ARCHIVE)
		{
			$attr['Access'] = 'Disabled';
		}
		elseif($this->course_obj->getViewMode() == IL_CRS_VIEW_ARCHIVE)
		{
			$attr['Access'] = 'Read';
		}
		if($this->course_obj->getArchiveType() == IL_CRS_ARCHIVE_DOWNLOAD)
		{
			$attr['Access'] = 'Download';
		}
		$this->xmlStartTag('Archive',$attr);

		$this->xmlElement('Start',null,$this->course_obj->getArchiveStart());
		$this->xmlElement('End',null,$this->course_obj->getArchiveEnd());

		$this->xmlEndTag('Archive');

		$this->xmlEndTag('Settings');

		return true;
	}

	
	// recursive
	function __buildObject($a_parent_id)
	{
		$this->course_obj->initCourseItemObject();
		$this->course_obj->items_obj->setParentId($a_parent_id);

		foreach($this->course_obj->items_obj->getAllItems() as $item)
		{
			
			if(!$tmp_obj =& ilObjectFactory::getInstanceByRefId($item['child'],false))
			{
				continue;
			}
		
			$attr = array();
			$attr['id'] = 'il_'.$this->ilias->getSetting('inst_id').'_'.$tmp_obj->getType().'_'.$item['child'];
			$attr['type'] = $tmp_obj->getType();
			$attr['Unlimited'] = $item['activation_unlimited'] ? 1 : 0;
			$attr['Position'] = $item['position'];

			$this->xmlStartTag('Object',$attr);
			$this->xmlElement('Title',null,$item['title']);
			$this->xmlElement('Description',null,$item['description']);
			$this->xmlElement('Start',null,$item['activation_start']);
			$this->xmlElement('End',null,$item['activation_end']);

			if($item['type'] == 'file')
			{
				$this->xmlElement('FileType',null,$tmp_obj->getFileType());
			}

			$this->__buildObject($item['child']);
			
			$this->xmlEndTag('Object');

			unset($tmp_obj);
		}
	}

	function __buildFooter()
	{
		$this->xmlEndTag('Course');
	}
}


?>
