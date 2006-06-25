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
* Class ilCourseContentGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*
* @ilCtrl_Calls ilCourseContentGUI: ilCourseArchivesGUI, ilCourseObjectivePresentationGUI, ilCourseItemAdministrationGUI
* @ilCtrl_Calls ilCourseContentGUI: ilEventAdministrationGUI
*
*/

include_once './course/classes/Event/class.ilEvent.php';

class ilCourseContentGUI
{
	var $container_gui;
	var $container_obj;
	var $course_obj;

	var $tpl;
	var $ctrl;
	var $lng;
	var $tabs_gui;

	/**
	* Constructor
	* @access public
	*/
	function ilCourseContentGUI(&$container_gui_obj)
	{
		global $tpl,$ilCtrl,$lng,$ilObjDataCache,$ilTabs;

		$this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('crs');
		$this->tabs_gui =& $ilTabs;

		$this->container_gui =& $container_gui_obj;
		$this->container_obj =& $this->container_gui->object;

		// 
		$this->__initCourseObject();
	}		

	function &executeCommand()
	{
		global $ilAccess,$ilErr;

		if(!$ilAccess->checkAccess('read','',$this->container_obj->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->WARNING);
		}

		$this->__setSubTabs();
		$this->tabs_gui->setTabActive('view_content');
		$cmd = $this->ctrl->getCmd();

		switch($this->ctrl->getNextClass($this))
		{
			case 'ilcourseitemadministrationgui':
				include_once 'course/classes/class.ilCourseItemAdministrationGUI.php';

				$this->ctrl->setReturn($this,'');
				$item_adm_gui = new ilCourseItemAdministrationGUI($this->container_obj,(int) $_GET['item_id']);
				$this->ctrl->forwardCommand($item_adm_gui);
				break;

			case 'ilcoursearchivesgui':
				$this->__forwardToArchivesGUI();
				break;

			case 'ilcourseobjectivepresentationgui':
				$this->__forwardToObjectivePresentation();
				break;

			case 'ileventadministrationgui':
				include_once 'course/classes/Event/class.ilEventAdministrationGUI.php';

				$this->ctrl->setReturn($this,'');
				$event_gui = new ilEventAdministrationGUI($this->container_gui,(int) $_GET['event_id']);
				$this->ctrl->forwardCommand($event_gui);
				break;

			default:
				// forward if archives enabled and not tutor
				if(!$this->is_tutor = $ilAccess->checkAccess('write','',$this->course_obj->getRefId()) and
				   $this->course_obj->isArchived())
				{
					$this->__forwardToArchivesGUI();
					break;
				}
				// forward to objective presentation
				if(!$this->is_tutor and
				   $this->container_obj->getType() == 'crs' and
				   $this->container_obj->enabledObjectiveView())
				{
					$this->__forwardToObjectivePresentation();
					break;
				}
				

				if(!$cmd)
				{
					$cmd = 'view';
				}
				$this->$cmd();
				break;
		}
	}

	function __forwardToObjectivePresentation()
	{
		include_once 'course/classes/class.ilCourseObjectivePresentationGUI.php';

		$this->ctrl->setReturn($this,'');
		$objectives_gui = new ilCourseObjectivePresentationGUI($this->container_gui);
		$this->ctrl->forwardCommand($objectives_gui);

		$this->tabs_gui->setTabActive('view_content');
		$this->tabs_gui->setSubTabActive('learners_view');

		return true;
	}		

	function __forwardToArchivesGUI()
	{
		include_once 'course/classes/class.ilCourseArchivesGUI.php';
		
		$this->ctrl->setReturn($this,'');
		$archives_gui = new ilCourseArchivesGUI($this->container_gui);
		$this->ctrl->forwardCommand($archives_gui);

		$this->tabs_gui->setTabActive('view_content');
		$this->tabs_gui->setSubTabActive('crs_archives');

		return true;
	}

	function view()
	{
		global $rbacsystem;

		include_once './classes/class.ilObjectListGUIFactory.php';
		include_once './course/classes/Event/class.ilEvent.php';

		$this->tabs_gui->setSubTabActive('edit_content');

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.container_page.html");

		$this->container_gui->showPossibleSubObjects();
		
		// Feedback
		$this->__showFeedBack();
		
		// Event
		$this->__showEvents();

		// course materials
		$this->__showMaterials();
	}

	// PRIVATE
	function __showEvents()
	{
		include_once 'course/classes/Event/class.ilEventItems.php';

		$this->course_obj->initCourseItemObject($this->container_obj->getRefId());

		if(!count($event_objs = ilEvent::_getEvents($this->container_obj->getId())))
		{
			return true;
		}
		
		$tpl =& new ilTemplate("tpl.table.html", true, true);
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.event_row.html","course");

		$counter = 0;
		foreach($event_objs as $event_obj)
		{
			$appointment_obj =& $event_obj->getFirstAppointment();

			// Links
			if($this->is_tutor)
			{
				// Edit
				$tpl->setCurrentBlock("event_commands");
				$this->ctrl->setParameterByClass('ileventadministrationgui','event_id',$event_obj->getEventId());
				$tpl->setVariable("EVENT_LINK",$this->ctrl->getLinkTargetByClass('ileventadministrationgui','edit'));
				$tpl->setVariable("EVENT_LINK_TXT",$this->lng->txt('edit'));
				$tpl->parseCurrentBlock();

				// Edit assignments
				$tpl->setCurrentBlock("event_commands");
				$tpl->setVariable("EVENT_LINK",$this->ctrl->getLinkTargetByClass('ileventadministrationgui','materials'));
				$tpl->setVariable("EVENT_LINK_TXT",$this->lng->txt('event_assign_materials'));
				$tpl->parseCurrentBlock();

				// Delete
				$tpl->setCurrentBlock("event_commands");
				$tpl->setVariable("EVENT_LINK",$this->ctrl->getLinkTargetByClass('ileventadministrationgui','confirmDelete'));
				$tpl->setVariable("EVENT_LINK_TXT",$this->lng->txt('delete'));
				$tpl->parseCurrentBlock();
			}


			$event_items = $this->course_obj->items_obj->getItemsByEvent($event_obj->getEventId());
			foreach ($event_items as $cont_data)
			{
				if(strlen($html = $this->__getItemHTML($cont_data)))
				{
					 /* Disabled: no manual sort 
					 foreach($this->__getOptions($cont_data,$num) as $key => $image)
					 {
						 $tpl->setCurrentBlock("img");
						 $tpl->setVariable("IMG_TYPE",$image["gif"]);
						 $tpl->setVariable("IMG_ALT",$image["lng"]);
						 $tpl->setVariable("IMG_LINK",$image["lnk"]);
						 $tpl->setVariable("IMG_TARGET",$image["tar"]);
						 $tpl->parseCurrentBlock();
					 }

					 $tpl->setCurrentBlock("options");
					 $tpl->setVariable("OPT_ROWCOL", ilUtil::switchColor($num,"tblrow1","tblrow2"));
					 $tpl->parseCurrentBlock();
					 */

					 if ($this->container_gui->isActiveAdministrationPanel())
					 {
						 $tpl->setCurrentBlock("block_row_check");
						 $tpl->setVariable("ITEM_ID", $cont_data['ref_id']);
						 $tpl->parseCurrentBlock();
					 }

					 // change row color
					 $tpl->setVariable("ITEM_HTML",$html);
					 $tpl->setVariable("MATERIAL_ROWCOL", ilUtil::switchColor($counter,"tblrow1","tblrow2"));
					 $tpl->setVariable("TYPE_IMG", ilUtil::getImagePath("icon_".$cont_data["type"].".gif"));
					 $tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
				 }

				 $tpl->setCurrentBlock("materials");
				 $tpl->setVariable("ITEM_HTML",$html);
				 $tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("EVENT_ROWCOL",ilUtil::switchColor($counter,'tblrow1','tblrow2'));
			$tpl->setVariable("EVENT_IMG",ilUtil::getImagePath('icon_event.gif'));
			$tpl->setVariable("EVENT_ALT",$this->lng->txt('events'));
			$tpl->setVariable("EVENT_TITLE",$event_obj->getTitle());
			
			$this->ctrl->setParameterByClass('ileventadministrationgui','event_id',$event_obj->getEventId());
			$tpl->setVariable("HREF_EVENT_TITLE",$this->ctrl->getLinkTargetByClass('ileventadministrationgui',
																				   'info'));
			if(strlen($desc = $event_obj->getDescription()))
			{
				$tpl->setVariable("EVENT_DESCRIPTION",$desc);
			}
			$tpl->setVariable("EVENT_TXT_DATE",$this->lng->txt('event_date'));
			$tpl->setVariable("EVENT_DATE",ilFormat::formatUnixTime($appointment_obj->getStartingTime(),false));
			$tpl->setVariable("EVENT_TIME",$appointment_obj->formatTime());
			$tpl->parseCurrentBlock();

			$counter++;
		}

		// create table
		include_once './classes/class.ilTableGUI.php';
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("events"),"icon_crs.gif",$this->lng->txt("events"));

		if($this->is_tutor)
		{
			$tbl->setHeaderNames(array($this->lng->txt("type"),$this->lng->txt("title"),
									   ""));
			$tbl->setHeaderVars(array("type","title","options"), 
								array("ref_id" => $this->course_obj->getRefId(),
									  "cmdClass" => "ilobjcoursecontentgui",
									  "cmdNode" => $_GET["cmdNode"]));
			$tbl->setColumnWidth(array("1px","100%","24px"));
			$tbl->disable("header");
		}
		else
		{
			$tbl->setHeaderNames(array($this->lng->txt("type"),$this->lng->txt("title")));
			$tbl->setHeaderVars(array("type","title"), 
								array("ref_id" => $this->course_obj->getRefId(),
									  "cmdClass" => "ilobjcoursecontentgui",
									  "cmdNode" => $_GET["cmdNode"]));
			$tbl->setColumnWidth(array("1px",""));
			$tbl->disable("header");
		}

		// footer
		$tbl->disable("footer");
		$tbl->disable('sort');
		$tbl->disable("form");

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setCurrentBlock("cont_page_content");
		$this->tpl->setVariable("CONTAINER_PAGE_CONTENT",$tpl->get());
		$this->tpl->parseCurrentBlock();
	}

	function __showMaterials()
	{
		include_once 'course/classes/Event/class.ilEventItems.php';
		
		// Todo get all items that are not assigned to an event
		$this->course_obj->initCourseItemObject($this->container_obj->getRefId());
		$this->cont_arr = $this->course_obj->items_obj->getFilteredItems($this->container_obj->getId());

		// NO ITEMS FOUND
		if(!count($this->cont_arr))
		{	
			#sendInfo($this->lng->txt("crs_no_items_found"));
			$this->tpl->addBlockFile("CONTENT_TABLE", "content_tab", "tpl.container_page.html");
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
			$this->tpl->setVariable("CONTAINER_PAGE_CONTENT", "");
			$this->container_gui->showAdministrationPanel($this->tpl);
			return true;
		}

		// show course materials
		$tpl =& new ilTemplate("tpl.table.html", true, true);
		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.crs_content_row.html","course");
		$cont_num = count($this->cont_arr);
		
		$this->container_gui->clearAdminCommandsDetermination();

		// render table content data
		// counter for rowcolor change

		$num = 0;
		foreach ($this->cont_arr as $cont_data)
		{
			if(strlen($html = $this->__getItemHTML($cont_data)))
			{
				foreach($this->__getOptions($cont_data,$num) as $key => $image)
				{
					$tpl->setCurrentBlock("img");
					$tpl->setVariable("IMG_TYPE",$image["gif"]);
					$tpl->setVariable("IMG_ALT",$image["lng"]);
					$tpl->setVariable("IMG_LINK",$image["lnk"]);
					$tpl->setVariable("IMG_TARGET",$image["tar"]);
					$tpl->parseCurrentBlock();
				}

				$tpl->setCurrentBlock("options");
				$tpl->setVariable("OPT_ROWCOL", ilUtil::switchColor($num,"tblrow1","tblrow2"));
				$tpl->parseCurrentBlock();

				if ($this->container_gui->isActiveAdministrationPanel())
				{
					$tpl->setCurrentBlock("block_row_check");
					$tpl->setVariable("ITEM_ID", $cont_data['ref_id']);
					$tpl->parseCurrentBlock();
				}

				// change row color
				$tpl->setVariable("ITEM_HTML",$html);
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow1","tblrow2"));
				$tpl->setVariable("TYPE_IMG", ilUtil::getImagePath("icon_".$cont_data["type"].".gif"));
				$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
				$tpl->setCurrentBlock("tbl_content");
				$tpl->parseCurrentBlock();
				// increment counter
			}
			$num++;
		}

		// create table
		include_once './classes/class.ilTableGUI.php';
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("crs_content"),"icon_crs.gif",$this->lng->txt("courses"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		if($this->is_tutor)
		{
			$tbl->setHeaderNames(array($this->lng->txt("type"),$this->lng->txt("title"),
									   ""));
			$tbl->setHeaderVars(array("type","title","options"), 
								array("ref_id" => $this->course_obj->getRefId(),
									  "cmdClass" => "ilobjcoursecontentgui",
									  "cmdNode" => $_GET["cmdNode"]));
			$tbl->setColumnWidth(array("1px","100%","24px"));
			$tbl->disable("header");
		}
		else
		{
			$tbl->setHeaderNames(array($this->lng->txt("type"),$this->lng->txt("title")));
			$tbl->setHeaderVars(array("type","title"), 
								array("ref_id" => $this->course_obj->getRefId(),
									  "cmdClass" => "ilobjcoursecontentgui",
									  "cmdNode" => $_GET["cmdNode"]));
			$tbl->setColumnWidth(array("1px",""));
			$tbl->disable("header");
		}

		// footer
		$tbl->disable("footer");
		$tbl->disable('sort');
		$tbl->disable("form");

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setCurrentBlock("cont_page_content");
		$this->tpl->setVariable("CONTAINER_PAGE_CONTENT", $tpl->get());
		$this->tpl->parseCurrentBlock();
		$this->container_gui->showAdministrationPanel($this->tpl);
		
		return true;

	}

	function __getItemHTML($cont_data)
	{
		include_once './classes/class.ilObjectListGUIFactory.php';

		// ACTIVATION
		$activation = '';
		if($cont_data['timing_type'] == IL_CRS_TIMINGS_ACTIVATION)
		{
			#$activation = $this->lng->txt("crs_from").' '.ilFormat::formatUnixTime($cont_data['timing_start'],true).' '.
			#	$this->lng->txt("crs_to").' '.ilFormat::formatUnixTime($cont_data['timing_end'],true);
			$activation = ilFormat::formatUnixTime($cont_data['timing_start'],true).' - '.
				ilFormat::formatUnixTime($cont_data['timing_end'],true);
		}
				
		// get item list gui object
		if (!is_object ($this->list_gui[$cont_data["type"]]))
		{
			$item_list_gui =& ilObjectListGUIFactory::_getListGUIByType($cont_data["type"]);

			$item_list_gui->setContainerObject($this->container_gui);
			// Enable/disable subscription depending on course settings
			$item_list_gui->enableSubscribe($this->course_obj->getAboStatus());

			$this->list_gui[$cont_data["type"]] =& $item_list_gui;
		}
		else
		{
			$item_list_gui =& $this->list_gui[$cont_data["type"]];
		}
				
		// show administration command buttons (or not)
		if (!$this->container_gui->isActiveAdministrationPanel())
		{
			$item_list_gui->enableDelete(false);
			$item_list_gui->enableLink(false);
			$item_list_gui->enableCut(false);
		}
				
		// add activation custom property
		if ($activation != "")
		{
			$item_list_gui->addCustomProperty($this->lng->txt("activation"), $activation,
											  false, true);
		}
				
		if($this->is_tutor)
		{
			$this->ctrl->setParameterByClass('ilcourseitemadministrationgui',"ref_id",
											 $this->container_obj->getRefId());
			$this->ctrl->setParameterByClass('ilcourseitemadministrationgui',"item_id",
											 $cont_data['child']);

			$item_list_gui->addCustomCommand($this->ctrl->getLinkTargetByClass('ilCourseItemAdministrationGUI',
																			   'edit'),
											 'activation');
		}
				
		$html = $item_list_gui->getListItemHTML($cont_data['ref_id'],
												$cont_data['obj_id'], $cont_data['title'], $cont_data['description']);
					
		$this->container_gui->determineAdminCommands($cont_data['ref_id'],
													 $item_list_gui->adminCommandsIncluded());

		return $html;
	}

	function __getOptions($cont_data,$num)
	{
		if($this->is_tutor)
		{
			$images = array();
			if($this->course_obj->getOrderType() == $this->course_obj->SORT_MANUAL)
			{
				if($num != 0)
				{
					$tmp_array["gif"] = ilUtil::getImagePath("a_up.gif");
					$tmp_array["lng"] = $this->lng->txt("crs_move_up");

					$this->ctrl->setParameterByClass('ilcourseitemadministrationgui',"ref_id",
													 $this->container_obj->getRefId());
					$this->ctrl->setParameterByClass('ilcourseitemadministrationgui',"item_id",
													 $cont_data['child']);
					$tmp_array['lnk'] = $this->ctrl->getLinkTargetByClass('ilcourseitemadministrationgui','moveUp');
					$tmp_array["tar"] = "";

					$images[] = $tmp_array;
				}
				if($num != count($this->cont_arr) - 1)
				{
					$tmp_array["gif"] = ilUtil::getImagePath("a_down.gif");
					$tmp_array["lng"] = $this->lng->txt("crs_move_down");
					$this->ctrl->setParameterByClass('ilcourseitemadministrationgui',"ref_id",
													 $this->container_obj->getRefId());
					$this->ctrl->setParameterByClass('ilcourseitemadministrationgui',"item_id",
													 $cont_data['child']);
					$tmp_array['lnk'] = $this->ctrl->getLinkTargetByClass('ilcourseitemadministrationgui','moveDown');
							
					$images[] = $tmp_array;
				}
			}
			
		}
		return $images ? $images : array();
	}		

		
	function __showMaterial(&$tpl,$cont_data,$num)
	{
		include_once './classes/class.ilObjectListGUIFactory.php';

		// ACTIVATION
		$activation = '';
		if($cont_data['timing_type'] == IL_CRS_TIMINGS_ACTIVATION)
		{
			#$activation = $this->lng->txt("crs_from").' '.ilFormat::formatUnixTime($cont_data['timing_start'],true).' '.
			#	$this->lng->txt("crs_to").' '.ilFormat::formatUnixTime($cont_data['timing_end'],true);
			$activation = ilFormat::formatUnixTime($cont_data['timing_start'],true).' - '.
				ilFormat::formatUnixTime($cont_data['timing_end'],true);
		}
				
		// get item list gui object
		if (!is_object ($this->list_gui[$cont_data["type"]]))
		{
			$item_list_gui =& ilObjectListGUIFactory::_getListGUIByType($cont_data["type"]);

			$item_list_gui->setContainerObject($this->container_gui);
			// Enable/disable subscription depending on course settings
			$item_list_gui->enableSubscribe($this->course_obj->getAboStatus());

			$this->list_gui[$cont_data["type"]] =& $item_list_gui;
		}
		else
		{
			$item_list_gui =& $this->list_gui[$cont_data["type"]];
		}
				
		// show administration command buttons (or not)
		if (!$this->container_gui->isActiveAdministrationPanel())
		{
			$item_list_gui->enableDelete(false);
			$item_list_gui->enableLink(false);
			$item_list_gui->enableCut(false);
		}
				
		// add activation custom property
		if ($activation != "")
		{
			$item_list_gui->addCustomProperty($this->lng->txt("activation"), $activation,
											  false, true);
		}
				
		if($this->is_tutor)
		{
			$this->ctrl->setParameterByClass('ilcourseitemadministrationgui',"ref_id",
											 $this->container_obj->getRefId());
			$this->ctrl->setParameterByClass('ilcourseitemadministrationgui',"item_id",
											 $cont_data['child']);

			$item_list_gui->addCustomCommand($this->ctrl->getLinkTargetByClass('ilCourseItemAdministrationGUI',
																			   'edit'),
											 'activation');
		}
				
		$html = $item_list_gui->getListItemHTML($cont_data['ref_id'],
												$cont_data['obj_id'], $cont_data['title'], $cont_data['description']);
					
		$this->container_gui->determineAdminCommands($cont_data['ref_id'],
													 $item_list_gui->adminCommandsIncluded());

		if(strlen($html))
		{
			$tpl->setVariable("ITEM_HTML", $html);
		}

		// OPTIONS
		if($this->is_tutor)
		{
			$images = array();
			if($this->course_obj->getOrderType() == $this->course_obj->SORT_MANUAL)
			{
				if($num != 0)
				{
					$tmp_array["gif"] = ilUtil::getImagePath("a_up.gif");
					$tmp_array["lng"] = $this->lng->txt("crs_move_up");

					$this->ctrl->setParameterByClass('ilcourseitemadministrationgui',"ref_id",
													 $this->container_obj->getRefId());
					$this->ctrl->setParameterByClass('ilcourseitemadministrationgui',"item_id",
													 $cont_data['child']);
					$tmp_array['lnk'] = $this->ctrl->getLinkTargetByClass('ilcourseitemadministrationgui','moveUp');
					$tmp_array["tar"] = "";

					$images[] = $tmp_array;
				}
				if($num != count($this->cont_arr) - 1)
				{
					$tmp_array["gif"] = ilUtil::getImagePath("a_down.gif");
					$tmp_array["lng"] = $this->lng->txt("crs_move_down");
					$this->ctrl->setParameterByClass('ilcourseitemadministrationgui',"ref_id",
													 $this->container_obj->getRefId());
					$this->ctrl->setParameterByClass('ilcourseitemadministrationgui',"item_id",
													 $cont_data['child']);
					$tmp_array['lnk'] = $this->ctrl->getLinkTargetByClass('ilcourseitemadministrationgui','moveDown');
							
					$images[] = $tmp_array;
				}
			}
										
			foreach($images as $key => $image)
			{
				$tpl->setCurrentBlock("img");
				$tpl->setVariable("IMG_TYPE",$image["gif"]);
				$tpl->setVariable("IMG_ALT",$image["lng"]);
				$tpl->setVariable("IMG_LINK",$image["lnk"]);
				$tpl->setVariable("IMG_TARGET",$image["tar"]);
				$tpl->parseCurrentBlock();
			}
			unset($images);
					
			$tpl->setCurrentBlock("options");
			$tpl->setVariable("OPT_ROWCOL", ilUtil::switchColor($num,"tblrow1","tblrow2"));
			$tpl->parseCurrentBlock();
		} // END write perm

		if(strlen($html))
		{
			if ($this->container_gui->isActiveAdministrationPanel())
			{
				$tpl->setCurrentBlock("block_row_check");
				$tpl->setVariable("ITEM_ID", $cont_data['ref_id']);
				$tpl->parseCurrentBlock();
				//$nbsp = false;
			}

			// change row color
			$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow1","tblrow2"));
			$tpl->setVariable("TYPE_IMG", ilUtil::getImagePath("icon_".$cont_data["type"].".gif"));
			$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
			$tpl->setCurrentBlock("tbl_content");
			$tpl->parseCurrentBlock();
			// increment counter
			return ++$num;
		}
		return $num;
	}

	function __showFeedback()
	{
		if($this->container_obj->getType() == 'crs')
		{
			include_once('Services/Feedback/classes/class.ilFeedbackGUI.php');
			$feedbackGUI = new ilFeedbackGUI();
			$feedbackHTML = $feedbackGUI->getCRSFeedbackListHTML();

			if(strlen($feedbackHTML))
			{
				$this->tpl->setCurrentBlock("cont_page_content");
				$this->tpl->setVariable("CONTAINER_PAGE_CONTENT",$feedbackHTML);
				$this->tpl->parseCurrentBlock();
			}
		}
		return true;
	}

	function __setSubTabs()
	{
		global $ilAccess;

		if($this->container_obj->getType() != 'crs')
		{
			return true;
		}
		if(!$ilAccess->checkAccess('write','',
								   $this->course_obj->getRefId(),'crs',$this->course_obj->getId()))
		{
			$this->is_tutor = false;
			return true;
		}
		
		// Write perm given
		$this->is_tutor = true;
		if($this->course_obj->enabledObjectiveView())
		{
			// Objective gui
			$this->tabs_gui->addSubTabTarget('learners_view',
											 $this->ctrl->getLinkTargetByClass('ilcourseobjectivepresentationgui','view'));
		}
		$this->tabs_gui->addSubTabTarget('edit_content',
										 $this->ctrl->getLinkTarget($this,'view'));
		$this->tabs_gui->addSubTabTarget('crs_archives',
										 $this->ctrl->getLinkTargetByClass('ilcoursearchivesgui','view'));

		return true;
	}

	function __initCourseObject()
	{
		global $tree;

		if($this->container_obj->getType() == 'crs')
		{
			// Container is course
			$this->course_obj =& $this->container_obj;
		}
		else
		{
			$course_ref_id = $tree->checkForParentType($this->container_obj->getRefId(),'crs');
			$this->course_obj =& ilObjectFactory::getInstanceByRefId($course_ref_id);
		}
		return true;
	}
} // END class.ilCourseContentGUI
?>
