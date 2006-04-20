<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* Class ilInfoScreenGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilInfoScreenGUI: ilNoteGUI, ilFeedbackGUI
*
* @package ilias-core
*/
class ilInfoScreenGUI
{
	var $ilias;
	var $lng;
	var $ctrl;
	var $gui_object;
	var $top_buttons = array();
	var $top_formbuttons = array();
	/**
	* a form action parameter. if set a form is generated
	*/
	var $form_action;

	/**
	* Constructor
	*
	* @param	object	$a_gui_object	GUI instance of related object
	* 									(ilCouseGUI, ilTestGUI, ...)
	*/
	function ilInfoScreenGUI($a_gui_object)
	{
		global $ilias, $ilCtrl, $lng,$ilTabs;

		$this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->tabs_gui =& $ilTabs;
		$this->gui_object =& $a_gui_object;
		$this->sec_nr = 0;
		$this->private_notes_enabled = false;
		$this->feedback_enabled = false;
		$this->learning_progress_enabled = false;
		$this->form_action = "";
		$this->top_formbuttons = array();
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $rbacsystem;
		global $tpl; 

		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->ctrl->getCmd("showSummary");
		
		$this->setTabs();
		
		switch($next_class)
		{
			case "ilnotegui":
				$this->showSummary();	// forwards command
				break;

			case "ilfeedbackgui":
				include_once("Services/Feedback/classes/class.ilFeedbackGUI.php");
				$fb_gui = new ilFeedbackGUI();
				$this->ctrl->setParameterByClass("ilFeedbackGUI","obj_id",$this->gui_object->object->getId());
				$this->ctrl->setParameterByClass("ilFeedbackGUI","ref_id",$_GET['ref_id']);
				$html = $this->ctrl->forwardCommand($fb_gui);
				$tpl->setContent($html);
				break;
				
			default:
				return $this->$cmd();
				break;
		}
		return true;
	}

	/**
	* enable notes
	*/
	function enablePrivateNotes($a_enable = true)
	{
		$this->private_notes_enabled = $a_enable;
	}

	/**
	* enable learning progress
	*/
	function enableLearningProgress($a_enable = true)
	{
		$this->learning_progress_enabled = $a_enable;
	}
	
	
	/**
	* enable feedback
	*/
	function enableFeedback($a_enable = true)
	{
		$this->feedback_enabled = $a_enable;
	}
	
	/**
	* add a new section
	*/
	function addSection($a_title)
	{
		$this->sec_nr++;
		$this->section[$this->sec_nr]["title"] = $a_title;
	}
	
	/**
	* set a form action
	*/
	function setFormAction($a_form_action)
	{
		$this->form_action = $a_form_action;
	}
	
	/**
	* remove form action
	*/
	function removeFormAction()
	{
		$this->form_action = "";
	}
	
	/**
	* add a property to current section
	*/
	function addProperty($a_name, $a_value)
	{
		$this->section[$this->sec_nr]["properties"][] =
			array("name" => $a_name, "value" => $a_value);
	}

	/**
	* add a property to current section
	*/
	function addPropertyCheckbox($a_name, $a_checkbox_name, $a_checkbox_value, $a_checkbox_label = "", $a_checkbox_checked = false)
	{
		$checkbox = "<input type=\"checkbox\" name=\"$a_checkbox_name\" value=\"$a_checkbox_value\" id=\"$a_checkbox_name$a_checkbox_value\"";
		if ($a_checkbox_checked)
		{
			$checkbox .= " checked=\"checked\"";
		}
		$checkbox .= " />";
		if (strlen($a_checkbox_label))
		{
			$checkbox .= "&nbsp;<label for=\"$a_checkbox_name$a_checkbox_value\">$a_checkbox_label</label>"; 
		}
		$this->section[$this->sec_nr]["properties"][] =
			array("name" => $a_name, "value" => $checkbox);
	}

	/**
	* add a property to current section
	*/
	function addButton($a_title, $a_link, $a_frame = "", $a_position = "top")
	{
		if ($a_position == "top")
		{
			$this->top_buttons[] =
				array("title" => $a_title,"link" => $a_link,"target" => $a_frame);
		}
	}
	
	/**
	* add a form button to the info screen
	* the form buttons are only valid if a form action is set
	*/
	function addFormButton($a_command, $a_title, $a_position = "top")
	{
		if ($a_position == "top")
		{
			array_push($this->top_formbuttons,
				array("command" => $a_command, "title" => $a_title)
			);
		}
	}
	
	/**
	* add standard meta data sections
	*/
	function addMetaDataSections($a_rep_obj_id,$a_obj_id, $a_type)
	{
		global $lng;
		
		$lng->loadLanguageModule("meta");

		include_once("./Services/MetaData/classes/class.ilMD.php");
		$md = new ilMD($a_rep_obj_id,$a_obj_id, $a_type);
		
		if ($md_gen = $md->getGeneral())
		{
			// get first descrption
			foreach($md_gen->getDescriptionIds() as $id)
			{
				$md_des = $md_gen->getDescription($id);
				$description = $md_des->getDescription();
				break;
			}
			
			// get language(s)
			$langs = array();
			foreach($ids = $md_gen->getLanguageIds() as $id)
			{
				$md_lan = $md_gen->getLanguage($id);
				if ($md_lan->getLanguageCode() != "")
				{
					$langs[] = $lng->txt("meta_l_".$md_lan->getLanguageCode());
				}
			}
			$langs = implode($langs, ", ");
			
			// keywords
			$keywords = array();
			foreach($ids = $md_gen->getKeywordIds() as $id)
			{
				$md_key = $md_gen->getKeyword($id);
				$keywords[] = $md_key->getKeyword();
			}
			$keywords = implode($keywords, ", ");
		}
		
		// authors
		if(is_object($lifecycle = $md->getLifecycle()))
		{
			$sep = $author = "";
			foreach(($ids = $lifecycle->getContributeIds()) as $con_id)
			{
				$md_con = $lifecycle->getContribute($con_id);
				if ($md_con->getRole() == "Author")
				{
					foreach($ent_ids = $md_con->getEntityIds() as $ent_id)
					{
						$md_ent = $md_con->getEntity($ent_id);
						$author = $author.$sep.$md_ent->getEntity();
						$sep = ", ";
					}
				}
			}
		}
			
		// copyright
		$copyright = "";
		if(is_object($rights = $md->getRights()))
		{
			$copyright = $rights->getDescription();
		}

		// learning time
		#if(is_object($educational = $md->getEducational()))
		#{
		#	$learning_time = $educational->getTypicalLearningTime();
		#}
		$learning_time = "";
		if(is_object($educational = $md->getEducational()))
		{
			if($seconds = $educational->getTypicalLearningTimeSeconds())
			{
				$learning_time = ilFormat::_secondsToString($seconds);
			}
		}
		

		// output
		
		// description
		if ($description != "")
		{
			$this->addSection($lng->txt("description"));
			$this->addProperty("",  nl2br($description));
		}
		
		// general section
		$this->addSection($lng->txt("meta_general"));
		if ($langs != "")	// language
		{
			$this->addProperty($lng->txt("language"),
				$langs);
		}
		if ($keywords != "")	// keywords
		{
			$this->addProperty($lng->txt("keywords"),
				$keywords);
		}
		if ($author != "")		// author
		{
			$this->addProperty($lng->txt("author"),
				$author);
		}
		if ($copyright != "")		// copyright
		{
			$this->addProperty($lng->txt("meta_copyright"),
				$copyright);
		}
		if ($learning_time != "")		// typical learning time
		{
			$this->addProperty($lng->txt("meta_typical_learning_time"),
				$learning_time);
		}
	}
	
	/**
	* show summary page
	*/
	function showSummary()
	{
		global $tpl, $ilAccess;
		
		$tpl->setContent($this->getHTML());
	}

	
	/**
	* get html
	*/
	function getHTML()
	{
		$tpl = new ilTemplate("tpl.infoscreen.html" ,true, true);
		
		// add top buttons
		if (count($this->top_buttons) > 0)
		{
			$tpl->addBlockfile("TOP_BUTTONS", "top_buttons", "tpl.buttons.html");

			foreach($this->top_buttons as $button)
			{
				// view button
				$tpl->setCurrentBlock("btn_cell");
				$tpl->setVariable("BTN_LINK", $button["link"]);
				$tpl->setVariable("BTN_TARGET", $button["target"]);
				$tpl->setVariable("BTN_TXT", $button["title"]);
				$tpl->parseCurrentBlock();
			}
		}
		
		// add top formbuttons
		if ((count($this->top_formbuttons) > 0) && (strlen($this->form_action) > 0))
		{
			$tpl->addBlockfile("TOP_FORMBUTTONS", "top_submitbuttons", "tpl.submitbuttons.html");

			foreach($this->top_formbuttons as $button)
			{
				// view button
				$tpl->setCurrentBlock("btn_submit_cell");
				$tpl->setVariable("BTN_COMMAND", $button["command"]);
				$tpl->setVariable("BTN_NAME", $button["title"]);
				$tpl->parseCurrentBlock();
			}
		}

		// add form action
		if (strlen($this->form_action) > 0)
		{
			$tpl->setCurrentBlock("formtop");
			$tpl->setVariable("FORMACTION", $this->form_action);
			$tpl->parseCurrentBlock();
			$tpl->touchBlock("formbottom");
		}

		for($i = 1; $i <= $this->sec_nr; $i++)
		{
			if (is_array($this->section[$i]["properties"]))
			{
				// section header
				$tpl->setCurrentBlock("header_row");
				$tpl->setVariable("TXT_SECTION",
					$this->section[$i]["title"]);
				$tpl->parseCurrentBlock();
				$tpl->touchBlock("row");
				
				// section properties
				foreach($this->section[$i]["properties"] as $property)
				{
					if ($property["name"] != "")
					{
						$tpl->setCurrentBlock("property_row");
						$tpl->setVariable("TXT_PROPERTY", $property["name"]);
						$tpl->setVariable("TXT_PROPERTY_VALUE", $property["value"]);
						$tpl->parseCurrentBlock();
						$tpl->touchBlock("row");
					}
					else
					{
						$tpl->setCurrentBlock("property_full_row");
						$tpl->setVariable("TXT_PROPERTY_FULL_VALUE", $property["value"]);
						$tpl->parseCurrentBlock();
						$tpl->touchBlock("row");
					}
				}
			}
		}
		// learning progress
		if($this->learning_progress_enabled and $html = $this->showLearningProgress())
		{
			$tpl->setCurrentBlock("learning_progress");
			$tpl->setVariable("LP_TABLE",$html);
			$tpl->parseCurrentBlock();
		}
			
		
		// notes section
		if ($this->private_notes_enabled)
		{
			$html = $this->showNotesSection();
			$tpl->setCurrentBlock("notes");
			$tpl->setVariable("NOTES", $html);
			$tpl->parseCurrentBlock();
		}
		
		return $tpl->get();
	}

	function showLearningProgress()
	{
		global $ilUser,$rbacsystem;

		if(!$rbacsystem->checkAccess('read',$this->gui_object->object->getRefId()))
		{
			return false;
		}
		if($ilUser->getId() == ANONYMOUS_USER_ID)
		{
			return false;
		}

		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if (!ilObjUserTracking::_enabledLearningProgress())
		{
			return false;
		}

		include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';

		if(ilLPObjSettings::_lookupMode($this->gui_object->object->getId()) != LP_MODE_MANUAL)
		{
			return false;
		}

		$this->lng->loadLanguageModule('trac');
		$tpl = new ilTemplate("tpl.lp_edit_manual.html",true,true,'Services/Tracking');
		
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_trac.gif'));
		$tpl->setVariable("ALT_IMG",$this->lng->txt('learning_progress'));
		$tpl->setVariable("TXT_EDIT_PROGRESS",$this->lng->txt('trac_edit_progress'));

		$tpl->setVariable("TXT_STATUS",$this->lng->txt('trac_status'));


		$tpl->setVariable("CHECK_EDITED",ilUtil::formSelect((int) ilLPMarks::_hasCompleted($ilUser->getId(),
																						   $this->gui_object->object->getId()),
															'lp_edit',
															array(0 => $this->lng->txt('trac_not_completed'),
																  1 => $this->lng->txt('trac_completed')),
															false,
															true));
															
															
		$tpl->setVariable("INFO_EDITED",$this->lng->txt('trac_info_edited'));

		// More infos for lm's
		if($this->gui_object->object->getType() == 'lm' ||
		   $this->gui_object->object->getType() == 'htlm')
		{
			$tpl->setCurrentBlock("lm_infos");
			$tpl->setVariable("TXT_LAST_ACCESS",$this->lng->txt('trac_last_access'));

			include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
			$progress = ilLearningProgress::_getProgress($ilUser->getId(),$this->gui_object->object->getId());
			if($progress['access_time'])
			{
				$tpl->setVariable("LAST_ACCESS",date('Y-m-d H:i:s',$progress['access_time']));
			}
			else
			{
				$tpl->setVariable("LAST_ACCESS",$this->lng->txt('trac_not_accessed'));
			}
			
			$tpl->setVariable("TXT_VISITS",$this->lng->txt('trac_visits'));
			$tpl->setVariable("VISITS",(int) $progress['visits']);

			if($this->gui_object->object->getType() == 'lm')
			{
				$tpl->setVariable("TXT_DURATION",$this->lng->txt('trac_spent_time'));
				$tpl->setVariable("DURATION",ilFormat::_secondsToString($progress['spent_time']));
			}

			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("TXT_SAVE",$this->lng->txt('save'));

		return $tpl->get();
	}

	function saveProgress()
	{
		global $ilUser;

		include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';
		
		$lp_marks = new ilLPMarks($this->gui_object->object->getId(),$ilUser->getId());
		$lp_marks->setCompleted((bool) $_POST['lp_edit']);
		$lp_marks->update();

		$this->lng->loadLanguageModule('trac');
		sendInfo($this->lng->txt('trac_updated_status'));

		$this->showSummary();
	}
	
	
	/**
	* show notes section
	*/
	function showNotesSection()
	{
		$next_class = $this->ctrl->getNextClass($this);
		include_once("Services/Notes/classes/class.ilNoteGUI.php");
		$notes_gui = new ilNoteGUI($this->gui_object->object->getId(), 0,
			$this->gui_object->object->getType());
		
		$notes_gui->enablePrivateNotes();
		//$notes_gui->enablePublicNotes();

		if ($next_class == "ilnotegui")
		{
			$html = $this->ctrl->forwardCommand($notes_gui);
		}
		else
		{	
			$html = $notes_gui->getNotesHTML();
		}
		
		return $html;
	}

	function setTabs()
	{
		global $tpl;
		
		$this->getTabs($this->tabs_gui);
	}

	/**
	* get tabs
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;
		
		$next_class = $this->ctrl->getNextClass($this);
		$force_active = ($next_class == "ilnotegui")
			? true
			: false;

		$tabs_gui->addSubTabTarget('summary',
			 $this->ctrl->getLinkTarget($this, "showSummary"),
			 array("showSummary", ""),
			 get_class($this), "", $force_active);
			 
		if ($this->feedback_enabled)
		{
			// dummy
			/*$tabs_gui->addSubTabTarget("feedback",
				$this->ctrl->getLinkTarget($this, "dummy"),
				"", "ilfeedbackgui");
*/
			// this should work with feedback class available
			// maybe a line... "@ ilCtrl_Calls ilFeedbackGUI:"
			// in the header of feedbackgui is necessary
			
			$tabs_gui->addSubTabTarget("feedback",
				$this->ctrl->getLinkTargetByClass('ilfeedbackgui','fbList'),
				"", "ilfeedbackgui");
			
		}
	}

}

?>
