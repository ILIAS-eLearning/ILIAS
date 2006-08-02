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
* Class for generation of PDF documents
* 
*
*
* @ilCtrl_Calls ilPDFPresentation:
*
* 
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/
include_once 'Services/Tracking/classes/ItemList/class.ilLPItemListFactory.php';
include_once 'classes/class.ilXmlWriter.php';

class ilPDFPresentation extends ilLearningProgressBaseGUI
{
	var $type = '';


	function ilPDFPresentation($a_mode,$a_ref_id,$a_user_id = 0,$a_tracked_user = 0)
	{
		global $lng,$ilCtrl;

		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id,$a_user_id);

		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;

		$this->current_user_id = $a_tracked_user;

		$this->__init();
	}

	function &getCurrentUserId()
	{
		return $this->current_user_id;
	}

	

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$this->ctrl->setReturn($this, "");
		switch($this->ctrl->getNextClass())
		{
			default:
				$cmd = $this->ctrl->getCmd();
				$this->$cmd();

		}
		return true;
	}

	function setType($a_type)
	{
		$this->type = $a_type;
	}
	function getType()
	{
		return $this->type;
	}


	// PRIVATE
	function __init()
	{
		global $ilUser;

		include_once './classes/class.ilXmlWriter.php';
		$this->xmlWriter = new ilXmlWriter();

		include_once './Services/Tracking/classes/class.ilLPFilter.php';
		$this->filter = new ilLPFilter($ilUser->getId());

		include_once './Services/Tracking/classes/class.ilLPFilterGUI.php';
		$this->filter_gui = new ilLPFilterGUI($ilUser->getId());
	}

	function createList()
	{
		switch($this->getType())
		{
			case LP_ACTIVE_PROGRESS:
				return $this->__createPersonalProgressList();

			case LP_ACTIVE_OBJECTS:
				return $this->__createObjectList();

			default:
				sendInfo('trac_pdf_error'. ' '.$this->getMode(),true);
		}

		$this->ctrl->returnToParent($this);
	}

	function __createPersonalProgressList()
	{
		global $ilObjDataCache,$ilUser;

		// Load and fill xml template
		$this->tpl = new ilTemplate('tpl.lp_pdf_list.xml',true,true,'Services/Tracking');
		$this->__addMain();

		$this->tpl->setVariable("FILTER",$this->filter_gui->getFO());
		$this->filter->setRequiredPermission('read');
		$type = $this->filter->getFilterType();
		$this->tpl->setVariable("TXT_OBJS",$this->lng->txt('objs_'.$type));

		// Sort objects by title
		$objs = $this->filter->getObjects();
		$sorted_objs = $this->__sort(array_keys($objs),'object_data','title','obj_id');
		// Render item list
		$counter = 0;
		foreach($sorted_objs as $object_id)
		{
			$item_list =& ilLPItemListFactory::_getInstance(0,$object_id,$ilObjDataCache->lookupType($object_id));
			$item_list->setCurrentUser($this->getCurrentUserId());
			$item_list->readUserInfo();
			$item_list->renderSimpleProgressFO();
		}

		// Finally convert to fop
		$this->__convert();
	}

	function __createObjectList()
	{
		global $ilObjDataCache,$ilUser;

		// Load and fill xml template
		$this->tpl = new ilTemplate('tpl.lp_pdf_list.xml',true,true,'Services/Tracking');
		$this->__addMain();

		$this->tpl->setVariable("FILTER",$this->filter_gui->getFO());
		$this->filter->setRequiredPermission('read');
		$type = $this->filter->getFilterType();
		$this->tpl->setVariable("TXT_OBJS",$this->lng->txt('objs_'.$type));

		// Sort objects by title
		$objs = $this->filter->getObjects();
		$sorted_objs = $this->__sort(array_keys($objs),'object_data','title','obj_id');
		// Render item list
		$counter = 0;
		foreach($sorted_objs as $object_id)
		{
			$item_list =& ilLPItemListFactory::_getInstance(0,$object_id,$ilObjDataCache->lookupType($object_id));
			#$item_list->setCurrentUser($this->tracked_user->getId());
			$item_list->setCurrentUser($this->getCurrentUserId());
			$item_list->readUserInfo();
			$item_list->renderObjectListFO();
		}

		// Finally convert to fop
		$this->__convert();
	}		


	function __addMain()
	{
		global $ilUser,$ilObjDataCache;

		$this->tpl->setVariable("LEARNING_PROGRESS_OF",$this->lng->txt('learning_progress'));
		switch($this->getType())
		{
			case LP_ACTIVE_PROGRESS:
				$name = ilObjUser::_lookupName($this->getCurrentUserId());
				$this->tpl->setVariable("USER_FULLNAME",$name['lastname'].', '.$name['firstname']);
				break;
		}				
		$this->tpl->setVariable("DATE",ilFormat::formatUnixTime(time(),true));
		
		return true;
	}


	function __convert()
	{
		#include_once 'Services/Transformation/classes/class.ilContentObject2FO.php';

		#$co2fo = new ilContentObject2FO();
		#$co2fo->setXMLString($xml = $this->xml_tpl->get());

		#if(!$co2fo->transform())
		#{
		#	sendInfo($this->lng->txt('trac_error_pdf',true));
		#	$this->ctrl->returnToParent($this);
		#}

		include_once 'Services/Transformation/classes/class.ilFO2PDF.php';

		$fo2pdf = new ilFO2PDF();
		#echo htmlentities($this->tpl->get());
		$fo2pdf->setFOString($this->tpl->get());

		#var_dump("<pre>",htmlentities($fo2pdf->getFOString()),"<pre>");

		$pdf_base64 = $fo2pdf->send();

		if(!$pdf_base64)
		{
			sendInfo($this->lng->txt('trac_error_pdf',true));
			$this->ctrl->returnToParent($this);
		}
		ilUtil::deliverData($pdf_base64,'learning_progress.pdf','application/pdf');
	}
}	
?>