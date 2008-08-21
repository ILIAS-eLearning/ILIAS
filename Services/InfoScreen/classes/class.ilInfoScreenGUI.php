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

/** @defgroup ServicesInfoScreen Services/InfoScreen
 */

/**
* Class ilInfoScreenGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilInfoScreenGUI: ilNoteGUI, ilFeedbackGUI, ilColumnGUI, ilPublicUserProfileGUI
*
* @ingroup ServicesInfoScreen
*/
class ilInfoScreenGUI
{
	var $ilias;
	var $lng;
	var $ctrl;
	var $gui_object;
	var $top_buttons = array();
	var $top_formbuttons = array();
	var $hiddenelements = array();
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
		$this->news_enabled = false;
		$this->feedback_enabled = false;
		$this->learning_progress_enabled = false;
		$this->form_action = "";
		$this->top_formbuttons = array();
		$this->hiddenelements = array();
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $rbacsystem;
		global $tpl;
		global $lng, $ilAccess, $ilCtrl;

		// load additional language modules
		$lng->loadLanguageModule("barometer");

		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->ctrl->getCmd("showSummary");
		$this->ctrl->setReturn($this, "showSummary");
		
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

			case "ilcolumngui":
				$this->showSummary();
				break;

			case "ilpublicuserprofilegui":
				include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
				$user_profile = new ilPublicUserProfileGUI($_GET["user_id"]);
				$user_profile->setBackUrl($ilCtrl->getLinkTarget($this, "showSummary"));
				$html = $ilCtrl->forwardCommand($user_profile);
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
	* enable news
	*/
	function enableNews($a_enable = true)
	{
		$this->news_enabled = $a_enable;
	}

	/**
	* enable news editing
	*/
	function enableNewsEditing($a_enable = true)
	{
		$this->news_editing = $a_enable;
	}

	/**
	* This function is supposed to be used for block type specific
	* properties, that should be passed to ilBlockGUI->setProperty
	*
	* @param	string	$a_property		property name
	* @param	string	$a_value		property value
	*/
	function setBlockProperty($a_block_type, $a_property, $a_value)
	{
		$this->block_property[$a_block_type][$a_property] = $a_value;
	}
	
	function getAllBlockProperties()
	{
		return $this->block_property;
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
	*
	* @param	string	$a_name		property name string
	* @param	string	$a_value	property value
	* @param	string	$a_link		link (will link the property value string)
	*/
	function addProperty($a_name, $a_value, $a_link = "")
	{
		$this->section[$this->sec_nr]["properties"][] =
			array("name" => $a_name, "value" => $a_value,
				"link" => $a_link);
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
	function addPropertyTextinput($a_name, $a_input_name, $a_input_value = "", $a_input_size = "", $direct_button_command = "", $direct_button_label = "")
	{
		$input = "<input type=\"text\" name=\"$a_input_name\" id=\"$a_input_name\"";
		if (strlen($a_input_value))
		{
			$input .= " value=\"" . ilUtil::prepareFormOutput($a_input_value) . "\"";
		}
		if (strlen($a_input_size))
		{
			$input .= " size=\"" . $a_input_size . "\"";
		}
		$input .= " />";
		if (strlen($direct_button_command) && strlen($direct_button_label))
		{
			$input .= " <input type=\"submit\" class=\"submit\" name=\"cmd[$direct_button_command]\" value=\"$direct_button_label\" />";
		}
		$this->section[$this->sec_nr]["properties"][] =
			array("name" => "<label for=\"$a_input_name\">$a_name</label>", "value" => $input);
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

	function addHiddenElement($a_name, $a_value)
	{
		array_push($this->hiddenelements, array("name" => $a_name, "value" => $a_value));
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
			include_once('Services/MetaData/classes/class.ilMDUtils.php');
			$copyright = ilMDUtils::_parseCopyright($rights->getDescription());
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
	* add standard object section
	*/
	function addObjectSections()
	{
		global $lng, $ilCtrl, $ilUser, $ilAccess, $tree;
		
		$this->addSection($lng->txt("additional_info"));
		$a_obj = $this->gui_object->object;
                
		// links to the object
		if (is_object($a_obj))
		{
			// permanent link
			$type = $a_obj->getType();
			$ref_id = $a_obj->getRefId();
			
			include_once('classes/class.ilLink.php');
			$href = ilLink::_getStaticLink($ref_id,$type,true);
				
			// delicous link
			$d_set = new ilSetting("delicious");
			if ($d_set->get("add_info_links") == "1")
			{
				$lng->loadLanguageModule("delic");
				$del_link = '<br/><a class="small" href="http://del.icio.us/post?desc=nn&url='.
					urlencode($href).'"><img border="0" src="'.ilUtil::getImagePath("icon_delicious_s.gif").
					'" /> '.$lng->txt("delic_add_to_delicious").
					'</a>';
			}
			
			include_once('Services/WebServices/ECS/classes/class.ilECSSettings.php');
			$settings = ilECSSettings::_getInstance();
			if($settings->isEnabled())
			{
				$this->addProperty($lng->txt("object_id"),
					$a_obj->getId()
					);
			}
				
			$this->addProperty($lng->txt("perma_link"),
				$href,
				$href
				);
			
			// links to resource
			if ($ilAccess->checkAccess("write", "", $ref_id) ||
				$ilAccess->checkAccess("edit_permissions", "", $ref_id))
			{
				$obj_id = $a_obj->getId();
				$rs = ilObject::_getAllReferences($obj_id);
				$refs = array();
				foreach($rs as $r)
				{
					if ($tree->isInTree($r))
					{
						$refs[] = $r;
					}
				}
				if (count($refs) > 1)
				{
					$links = $sep = "";
					foreach($refs as $r)
					{
						$cont_loc = new ilLocatorGUI();
						$cont_loc->addContextItems($r, true);
						$links.= $sep.$cont_loc->getHTML();
						$sep = "<br />";
					}
					
					$this->addProperty($lng->txt("res_links"),
						'<div class="small">'.$links.'</div>'
						);
				}
			}
		}
                
                
		// creation date
		$this->addProperty(
			$lng->txt("create_date"),
			ilDatePresentation::formatDate(new ilDateTime($a_obj->getCreateDate(),IL_CAL_DATETIME)));

		// owner
		if ($ilUser->getId() != ANONYMOUS_USER_ID)
		{
			if (ilObjUser::_lookupEmail($a_obj->getOwner()) === false)
			{
				$this->addProperty($lng->txt("owner"),$lng->txt('deleted_user_account'));
			}
			else
			{
				$ownerObj = new ilObjUser($a_obj->getOwner());
				$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "user_id", $ownerObj->getId());
				$this->addProperty($lng->txt("owner"),
					$ownerObj->getFirstname().' '.
					$ownerObj->getLastname().' '.
					$ownerObj->getLogin(),
						$ilCtrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML")
					);
			}
		}

		// change event
		require_once 'Services/Tracking/classes/class.ilChangeEvent.php';
		if (ilChangeEvent::_isActive())
		{
			if ($ilUser->getId() != ANONYMOUS_USER_ID)
			{
				$readEvents = ilChangeEvent::_lookupReadEvents($a_obj->getId());
				$count_users = 0;
				$count_members = 0;
				$count_user_reads = 0;
				$count_anonymous_reads = 0;
				foreach ($readEvents as $evt)
				{
					if ($evt['usr_id'] == ANONYMOUS_USER_ID)
					{
						$count_anonymous_reads += $evt['read_count'];
					}
					else
					{
						$count_user_reads += $evt['read_count'];
						$count_users++;
						/* to do: if ($evt['user_id'] is member of $this->getRefId())
						{
							$count_members++;
						}*/
					}
				}
				if ($count_anonymous_reads > 0)
				{
					$this->addProperty($this->lng->txt("readcount_anonymous_users"),$count_anonymous_reads);
				}
				if ($count_user_reads > 0)
				{
					$this->addProperty($this->lng->txt("readcount_users"),$count_user_reads);
                                }
				if ($count_users > 0)
				{
					$this->addProperty($this->lng->txt("accesscount_registered_users"),$count_users);
                                }
			}
		}
		// END ChangeEvent: Display change event info

		// BEGIN WebDAV: Display locking information
		require_once('Services/WebDAV/classes/class.ilDAVServer.php');
		if (ilDAVServer::_isActive())
		{
			global $ilias, $ilUser;
			if ($ilUser->getId() != ANONYMOUS_USER_ID)
			{
				$davLocks = new ilDAVLocks();

				// Show lock info
				if ($ilias->account->getId() != ANONYMOUS_USER_ID)
				{
					$locks =& $davLocks->getLocksOnObjectObj($a_obj->getId());
					if (count($locks) > 0)
					{
						$lockUser = new ilObjUser($locks[0]['ilias_owner']);

						$this->addProperty($this->lng->txt("in_use_by"),
							$lockUser->getFirstname().' '.$lockUser->getLastname().' '.$lockUser->getLogin(),
							"./ilias.php?user=".$locks[0]['ilias_owner'].'&cmd=showUserProfile&cmdClass=ilpersonaldesktopgui&cmdNode=1&baseClass=ilPersonalDesktopGUI'
						);
					}
				}
			}
		}
		// END WebDAV: Display locking information


	}
	// END ChangeEvent: Display standard object info
	/**
	* show summary page
	*/
	function showSummary()
	{
		global $tpl, $ilAccess;

		$tpl->setContent($this->getCenterColumnHTML());
		$tpl->setRightContent($this->getRightColumnHTML());
	}


	/**
	* Display center column
	*/
	function getCenterColumnHTML()
	{
		global $ilCtrl;
		
		include_once("Services/Block/classes/class.ilColumnGUI.php");
		$column_gui = new ilColumnGUI("info", IL_COL_CENTER);
		$this->setColumnSettings($column_gui);

		if (!$ilCtrl->isAsynch())
		{
			if ($column_gui->getScreenMode() != IL_SCREEN_SIDE)
			{
				// right column wants center
				if ($column_gui->getCmdSide() == IL_COL_RIGHT)
				{
					$column_gui = new ilColumnGUI("info", IL_COL_RIGHT);
					$this->setColumnSettings($column_gui);
					$html = $ilCtrl->forwardCommand($column_gui);
				}
				// left column wants center
				if ($column_gui->getCmdSide() == IL_COL_LEFT)
				{
					$column_gui = new ilColumnGUI("info", IL_COL_LEFT);
					$this->setColumnSettings($column_gui);
					$html = $ilCtrl->forwardCommand($column_gui);
				}
			}
			else
			{
				$html = $this->getHTML();
			}
		}
		
		return $html;
	}

	/**
	* Display right column
	*/
	function getRightColumnHTML()
	{
		global $ilUser, $lng, $ilCtrl;
		
		include_once("Services/Block/classes/class.ilColumnGUI.php");
		$column_gui = new ilColumnGUI("info", IL_COL_RIGHT);
		$this->setColumnSettings($column_gui);

		if ($ilCtrl->getNextClass() == "ilcolumngui" &&
			$column_gui->getCmdSide() == IL_COL_RIGHT &&
			$column_gui->getScreenMode() == IL_SCREEN_SIDE)
		{
			$html = $ilCtrl->forwardCommand($column_gui);
		}
		else
		{
			if (!$ilCtrl->isAsynch())
			{
				if ($this->news_enabled)
				{
					$html = $ilCtrl->getHTML($column_gui);
				}
			}
		}

		return $html;
	}

	/**
	* Set column settings.
	*/
	function setColumnSettings($column_gui)
	{
		global $lng, $ilAccess;

		$column_gui->setEnableEdit($this->news_editing);
		$column_gui->setRepositoryMode(true);
		$column_gui->setAllBlockProperties($this->getAllBlockProperties());
	}

	/**
	* get html
	*/
	function getHTML()
	{
		global $lng, $ilSetting, $tree, $ilAccess, $ilCtrl;
		
		$tpl = new ilTemplate("tpl.infoscreen.html" ,true, true, "Services/InfoScreen");

		// other class handles form action (@todo: this is not implemented/tested)
		if ($this->form_action == "")
		{
			$this->setFormAction($ilCtrl->getFormAction($this));
		}

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

		if (count($this->hiddenelements))
		{
			foreach ($this->hiddenelements as $hidden)
			{
				$tpl->setCurrentBlock("hidden_element");
				$tpl->setVariable("HIDDEN_NAME", $hidden["name"]);
				$tpl->setVariable("HIDDEN_VALUE", $hidden["value"]);
				$tpl->parseCurrentBlock();
			}
		}

		// tagging
		if (is_object($this->gui_object->object))
		{
			$this->addTagging($tpl);
		}
		

		// learning progress
		if($this->learning_progress_enabled and $html = $this->showLearningProgress())
		{
			$tpl->setCurrentBlock("learning_progress");
			$tpl->setVariable("LP_TABLE",$html);
			$tpl->parseCurrentBlock();
		}


		// notes section
		if ($this->private_notes_enabled && !$ilSetting->get('disable_notes'))
		{
			$html = $this->showNotesSection();
			$tpl->setCurrentBlock("notes");
			$tpl->setVariable("NOTES", $html);
			$tpl->parseCurrentBlock();
		}

		// add object sections
		$this->addObjectSections($this->object);

                // render all sections
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
						if ($property["link"] == "")
						{
							$tpl->setCurrentBlock("pv");
							$tpl->setVariable("TXT_PROPERTY_VALUE", $property["value"]);
							$tpl->parseCurrentBlock();
						}
						else
						{
							$tpl->setCurrentBlock("lpv");
							$tpl->setVariable("TXT_PROPERTY_LVALUE", $property["value"]);
							$tpl->setVariable("LINK_PROPERTY_VALUE", $property["link"]);
							$tpl->parseCurrentBlock();
						}
						$tpl->setCurrentBlock("property_row");
						$tpl->setVariable("TXT_PROPERTY", $property["name"]);
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
		if (!ilObjUserTracking::_enabledLearningProgress() and $ilUser->getId() != ANONYMOUS_USER_ID)
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
				$tpl->setVariable('LAST_ACCESS',ilDatePresentation::formatDate(new ilDateTime($progress['access_time'],IL_CAL_DATETIME)));
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
				$tpl->setVariable("DURATION",ilFormat::_secondsToString($progress['spent_seconds']));
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
		ilUtil::sendInfo($this->lng->txt('trac_updated_status'));

		$this->showSummary();
	}


	/**
	* show notes section
	*/
	function showNotesSection()
	{
		global $ilAccess;
		
		$next_class = $this->ctrl->getNextClass($this);
		include_once("Services/Notes/classes/class.ilNoteGUI.php");
		$notes_gui = new ilNoteGUI($this->gui_object->object->getId(), 0,
			$this->gui_object->object->getType());

		$ref_id = $this->gui_object->object->getRefId();
		if ($ilAccess->checkAccess("write", "", $ref_id) ||
			$ilAccess->checkAccess("edit_permissions", "", $ref_id))
		{
			$notes_gui->enableCommentsSettings();
		}

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

	/**
	 * show LDAP role group mapping info
	 *
	 * @access public
	 * @param string section name. Leave empty to place this info string inside a section
	 * 
	 */
	public function showLDAPRoleGroupMappingInfo($a_section = '')
	{
	 	if(strlen($a_section))
	 	{
	 		$this->addSection($a_section);
	 	}
		include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMapping.php');
		$ldap_mapping = ilLDAPRoleGroupMapping::_getInstance();
		if($infos = $ldap_mapping->getInfoStrings($this->gui_object->object->getId()))
		{
			$info_combined = '<div style="color:green;">';
			$counter = 0;
			foreach($infos as $info_string)
			{
				if($counter++)
				{
					$info_combined .= '<br />';
				}
				$info_combined .= $info_string; 
			}
			$info_combined .= '</div>';
			$this->addProperty($this->lng->txt('applications'),$info_combined);
		}
	 	return true;
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
		global $rbacsystem,$ilUser,$ilAccess;

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
			$show_feedback_tab=false;
			if($ilAccess->checkAccess('write','edit',$_GET['ref_id']))
			{
				$show_feedback_tab=true;
			}
			else
			{
				// this should work with feedback class available
				// maybe a line... "@ ilCtrl_Calls ilFeedbackGUI:"
				// in the header of feedbackgui is necessary
				include_once('Services/Feedback/classes/class.ilFeedback.php');
				$feedback = new ilFeedback();
				$feedback->setRefId($_GET['ref_id']);
				$barometers = $feedback->getAllBarometer(0);
				if(count($barometers))
				{
					foreach ($barometers as $barometer)
					{
						if($barometer->canVote($ilUser->getId(),$barometer->getId())==1)
						{
							$show_feedback_tab=true;
							break;
						}
					}
				}
			}
			if ($show_feedback_tab)
			{
				$tabs_gui->addSubTabTarget("feedback",
					$this->ctrl->getLinkTargetByClass("ilfeedbackgui", "fbList"),
					"", "ilfeedbackgui");
			}

			/*
			$tabs_gui->addSubTabTarget("feedb_feedback_settings",
				$this->ctrl->getLinkTargetByClass("ilfeedbackgui", "fbList"),
				"", "ilfeedbackgui");*/
		}
	}

	
	/**
	* Add tagging
	*/
	function addTagging($tpl)
	{
		global $lng, $ilCtrl;
		
		$lng->loadLanguageModule("tagging");
		
		include_once("Services/Tagging/classes/class.ilTaggingGUI.php");
		$tagging_gui = new ilTaggingGUI();
		$tagging_gui->setObject($this->gui_object->object->getId(),
			$this->gui_object->object->getType());
		
		// section header
		$tpl->setCurrentBlock("header_row");
		$tpl->setVariable("TXT_SECTION",
			$this->lng->txt("tagging_tags"));
		$tpl->parseCurrentBlock();
		$tpl->touchBlock("row");
		
		// tags of all users
		$tpl->setCurrentBlock("pv");
		$tpl->setVariable("TXT_PROPERTY_VALUE",
			$tagging_gui->getAllUserTagsForObjectHTML());
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("property_row");
		$tpl->setVariable("TXT_PROPERTY", $lng->txt("tagging_all_users"));
		$tpl->parseCurrentBlock();
		$tpl->touchBlock("row");

		// user tags
		$tpl->setCurrentBlock("pv");
		$tpl->setVariable("TXT_PROPERTY_VALUE", $tagging_gui->getTaggingInputHTML());
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("property_row");
		$tpl->setVariable("TXT_PROPERTY", $lng->txt("tagging_my_tags"));
		$tpl->parseCurrentBlock();
		$tpl->touchBlock("row");
	}
	
	function saveTags()
	{
		global $ilCtrl;
		
		include_once("Services/Tagging/classes/class.ilTaggingGUI.php");
		$tagging_gui = new ilTaggingGUI();
		$tagging_gui->setObject($this->gui_object->object->getId(),
			$this->gui_object->object->getType());
		$tagging_gui->saveInput();
		
		return $this->showSummary();
	}
}

?>
