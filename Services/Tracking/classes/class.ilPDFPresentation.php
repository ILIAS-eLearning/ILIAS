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
				ilUtil::sendInfo('trac_pdf_error'. ' '.$this->getMode(),true);
		}

		$this->ctrl->returnToParent($this);
	}

	function createDetails()
	{
		switch($this->getType())
		{
			case LP_ACTIVE_OBJECTS:
			case LP_ACTIVE_PROGRESS:
				return $this->__createObjectDetails();
				
			default:
				ilUtil::sendInfo('trac_pdf_error'. ' '.$this->getMode(),true);
		}
	}

	function __createObjectDetails()
	{
		global $ilObjDataCache;

		include_once 'classes/class.ilXmlWriter.php';
		$this->writer = new ilXmlWriter();
		$this->writer->xmlHeader();
		$this->writer->xmlStartTag('LearningProgress');
		$this->writer->xmlElement('Title',null,$this->lng->txt('learning_progress'));

		// Header
		$header = $this->lng->txt('learning_progress').': '.ilFormat::formatUnixTime(time(),true);
		$this->writer->xmlElement('Header',null,$header);
		
		// Footer
		$this->writer->xmlElement('Footer',null,'powered by ILIAS');

		// Info
		include_once 'Services/Tracking/classes/ItemList/class.ilLPItemListFactory.php';
		$this->details_obj_id = $ilObjDataCache->lookupObjId($this->getRefId());
		$item_list =& ilLPItemListFactory::_getInstance(0,$this->details_obj_id,$ilObjDataCache->lookupType($this->details_obj_id));

		if($this->getType() == LP_ACTIVE_PROGRESS)
		{
			$item_list->setCurrentUser($this->current_user_id);
			$item_list->readUserInfo();
			$item_list->renderObjectInfoXML($this->writer,true,true);
		}
		else
		{
			$item_list->renderObjectInfoXML($this->writer,false,false);
		}
		// Items
		include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
		if(ilLPObjSettings::_isContainer(ilLPObjSettings::_lookupMode($this->details_obj_id)) or $this->getType() == LP_ACTIVE_OBJECTS)
		{
			$this->__showItems();
		}
		
		$this->writer->xmlEndTag('LearningProgress');

		$this->__toFO();
		$this->__toPDF();
	}

	function __showItems()
	{
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';

		global $ilObjDataCache,$ilUser;

		$not_attempted = ilLPStatusWrapper::_getNotAttempted($this->details_obj_id);
		$in_progress = ilLPStatusWrapper::_getInProgress($this->details_obj_id);
		$completed = ilLPStatusWrapper::_getCompleted($this->details_obj_id);
		$failed = ilLPStatusWrapper::_getFailed($this->details_obj_id);

		switch($this->getType())
		{
			case LP_ACTIVE_OBJECTS:
				$all_users = array_merge($completed,$in_progress,$not_attempted,$failed);
				$all_users = $this->__sort($all_users,'usr_data','lastname','usr_id');
				break;

			case LP_ACTIVE_PROGRESS:
				$all_users = array($this->current_user_id);
				break;
		}

		// Header
		$this->writer->xmlStartTag('Items');
		$this->writer->xmlStartTag('ItemHeader');
		$this->writer->xmlElement('HeaderTitle',null,$this->lng->txt('trac_objects'));
		
		// Show timings header
		include_once 'Modules/Course/classes/class.ilCourseItems.php';
		if($this->has_timings = ilCourseItems::_hasCollectionTimings($this->getRefId()))
		{
			$this->writer->xmlElement('HeaderInfo',null,$this->lng->txt('trac_head_timing'));
		}
		$this->writer->xmlEndTag('ItemHeader');

		// Render item list
		$this->container_row_counter = 0;
		foreach($all_users as $user)
		{
			$this->writer->xmlStartTag('Item');
			$this->__renderContainerRow(0,$this->getRefId(),$user,'usr',0);
			$this->writer->xmlEndTag('Item');
		}
		$this->writer->xmlEndTag('Items');
	}
	function __renderContainerRow($a_parent_id,$a_item_id,$a_usr_id,$type,$level)
	{
		global $ilObjDataCache,$ilUser,$ilAccess;

		include_once 'Services/Tracking/classes/ItemList/class.ilLPItemListFactory.php';

		$item_list =& ilLPItemListFactory::_getInstanceByRefId($a_parent_id,$a_item_id,$type);
		if($this->has_timings)
		{
			$item_list->readTimings();
			$item_list->enable('timings');
		}
		$item_list->setCurrentUser($a_usr_id);
		$item_list->readUserInfo();
		$item_list->setIndentLevel($level);


		$item_list->renderObjectDetailsXML($this->writer);

		if($type == 'sahs_item' or
		   $type == 'objective' or
		   $type == 'event')
		{
			return true;
		}
		
		include_once './Services/Tracking/classes/class.ilLPCollectionCache.php';
		foreach(ilLPCollectionCache::_getItems($ilObjDataCache->lookupObjId($a_item_id)) as $child_id)
		{
			switch($item_list->getMode())
			{
				case LP_MODE_OBJECTIVES:
					$this->writer->xmlStartTag('Item');
					$this->__renderContainerRow($a_item_id,$child_id,$a_usr_id,'objective',$level + 2);
					$this->writer->xmlEndTag('Item');
					break;

				case LP_MODE_SCORM:
					$this->writer->xmlStartTag('Item');
					$this->__renderContainerRow($a_item_id,$child_id,$a_usr_id,'sahs_item',$level + 2);
					$this->writer->xmlEndTag('Item');
					break;

				default:
					if(!$ilAccess->checkAccess('read','',$child_id))
					{
						break;
					}				
					$this->writer->xmlStartTag('Item');
					$this->__renderContainerRow($a_item_id,$child_id,$a_usr_id,
												$ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($child_id)),$level + 2);
					$this->writer->xmlEndTag('Item');
					break;
			}
		}
	}

	function __toFO()
	{
		include_once 'Services/Transformation/classes/class.ilXML2FO.php';
		
		$xml2FO = new ilXML2FO();
		$xml2FO->setXSLTLocation($this->tpl->getTemplatePath('learning_progress_fo.xsl','Services/Tracking'));
		$xml2FO->setXMLString($this->writer->xmlDumpMem());
		$xml2FO->transform();

		$this->fo_string = $xml2FO->getFOString();
		#var_dump("<pre>",htmlentities($this->fo_string),"<pre>");
		return true;
	}

	function __toPDF()
	{
		global $ilLog;
		
		include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
		try
		{
			$pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')->ilFO2PDF($this->fo_string);
			ilUtil::deliverData($pdf_base64->scalar,'learning_progress.pdf','application/pdf');
		}
		catch(XML_RPC2_FaultException $e)
		{
			ilUtil::sendFailure('trac_error_pdf',true);
			$ilLog->write(__METHOD__.': '.$e->getMessage());
			$this->ctrl->returnToParent($this);
			return false;
		}
		catch(Exception $e)
		{
			ilUtil::sendFailure('trac_error_pdf',true);
			$ilLog->write(__METHOD__.': '.$e->getMessage());
			$this->ctrl->returnToParent($this);
			return false;
		}
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
		global $ilLog;
		
		include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
		try
		{
			$pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')->ilFO2PDF($this->tpl->get());
			ilUtil::deliverData($pdf_base64->scalar,'learning_progress.pdf','application/pdf');
			return true;
		}
		catch(XML_RPC2_FaultException $e)
		{
			ilUtil::sendFailure('trac_error_pdf',true);
			$ilLog->write(__METHOD__.': '.$e->getMessage());
			$this->ctrl->returnToParent($this);
			return false;
		}
		catch(Exception $e)
		{
			ilUtil::sendFailure('trac_error_pdf',true);
			$ilLog->write(__METHOD__.': '.$e->getMessage());
			$this->ctrl->returnToParent($this);
			return false;
		}
	}
}	
?>