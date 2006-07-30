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

include_once "classes/class.ilObjUser.php";
include_once "classes/class.ilMail.php";
include_once "classes/class.ilPersonalDesktopGUI.php";


/**
* GUI class for personal desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPersonalDesktopGUI: ilPersonalProfileGUI, ilBookmarkAdministrationGUI
* @ilCtrl_Calls ilPersonalDesktopGUI: ilObjUserGUI, ilPDNotesGUI, ilLearningProgressGUI, ilFeedbackGUI, ilPaymentGUI, ilPaymentAdminGUI
*
* @package content
*/
class ilPersonalDesktopGUI
{
	var $tpl;
	var $lng;
	var $ilias;
	
	var $cmdClass = '';

	/**
	* constructor
	*/
	function ilPersonalDesktopGUI()
	{
		global $ilias, $tpl, $lng, $rbacsystem, $ilCtrl, $ilMainMenu;
		
		
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		
		$ilMainMenu->setActive("desktop");
		
		// catch hack attempts
		if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
		{
			$this->ilias->raiseError($this->lng->txt("msg_not_available_for_anon"),$this->ilias->error_obj->MESSAGE);
		}
		$this->cmdClass = $_GET['cmdClass'];
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilUser;
		
		$next_class = $this->ctrl->getNextClass();
		$this->ctrl->setReturn($this, "show");
		
		// check whether personal profile of user is incomplete
		if ($ilUser->getProfileIncomplete() && $next_class != "ilpersonalprofilegui")
		{
			$this->ctrl->redirectByClass("ilpersonalprofilegui");
		}
		
		// read last active subsection
		if($_GET['PDHistory'])
		{
			$next_class = $this->__loadNextClass();
		}
		$this->__storeLastClass($next_class);
		
		switch($next_class)
		{
			//Feedback
			case "ilfeedbackgui":
				include_once("Services/Feedback/classes/class.ilFeedbackGUI.php");
				$feedback_gui = new ilFeedbackGUI();
				$this->setTabs();
				$ret =& $this->ctrl->forwardCommand($feedback_gui);
				break;
				// bookmarks
			case "ilbookmarkadministrationgui":
				include_once("classes/class.ilBookmarkAdministrationGUI.php");
				$bookmark_gui = new ilBookmarkAdministrationGUI();
				if ($bookmark_gui->getMode() == 'tree') {
					$this->getTreeModeTemplates();
				} else {
					$this->getStandardTemplates();
				}
				$this->setTabs();
				$ret =& $this->ctrl->forwardCommand($bookmark_gui);
				break;
			
				// profile
			case "ilpersonalprofilegui":
				$this->getStandardTemplates();
				$this->setTabs();
				include_once("classes/class.ilPersonalProfileGUI.php");
				$profile_gui = new ilPersonalProfileGUI();
				$ret =& $this->ctrl->forwardCommand($profile_gui);
				break;
			
				// profile
			case "ilobjusergui":
				include_once("classes/class.ilObjUserGUI.php");
				$user_gui = new ilObjUserGUI("",$_GET["user"], false, false);
				$ret =& $this->ctrl->forwardCommand($user_gui);
				break;
			
				// pd notes
			case "ilpdnotesgui":
				$this->getStandardTemplates();
				$this->setTabs();
				include_once("classes/class.ilPDNotesGUI.php");
				$pd_notes_gui = new ilPDNotesGUI();
				$ret =& $this->ctrl->forwardCommand($pd_notes_gui);
				break;
			
			case "illearningprogressgui":
				$this->getStandardTemplates();
				$this->setTabs();
			
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
			
				$new_gui =& new ilLearningProgressGUI(LP_MODE_PERSONAL_DESKTOP,0);
				$ret =& $this->ctrl->forwardCommand($new_gui);
			
				break;
			
				// payment
			case "ilpaymentgui":
				$this->showShoppingCart();
				break;

			case "ilpaymentadmingui":
				$this->getStandardTemplates();
				$this->setTabs();
				include_once("./payment/classes/class.ilPaymentAdminGUI.php");
				$pa =& new ilPaymentAdminGUI($ilUser);
				$ret =& $this->ctrl->forwardCommand($pa);
				$this->tpl->show();
				break;

			default:
				$this->getStandardTemplates();
				$this->setTabs();
				$cmd = $this->ctrl->getCmd("show");
				$this->$cmd();
				break;
		}
		return true;
	}
	
	function showShoppingCart()
	{
		global $ilUser;
		$this->getStandardTemplates();
		$this->setTabs();
		include_once("./payment/classes/class.ilPaymentGUI.php");
		$pa =& new ilPaymentGUI($ilUser);
		$ret =& $this->ctrl->forwardCommand($pa);
		$this->tpl->show();
		return true;
	}

	/**
	* get standard templates
	*/
	function getStandardTemplates()
	{
		// add template for content
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
	}
	
	/**
	* get tree mode templates
	*/
	function getTreeModeTemplates()
	{
		// add template for content
		//$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_tree_content.html");
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");
	}
	
	/**
	* show desktop
	*/
	function show()
	{
		// add template for content
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.usr_personaldesktop.html");
		
		// set locator
		/*
		$this->tpl->setVariable("TXT_LOCATOR", $this->lng->txt("locator"));
		$this->tpl->touchBlock("locator_separator");
		$this->tpl->touchBlock("locator_item");
		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("overview"));
		$this->tpl->setVariable("LINK_ITEM", $this->ctrl->getLinkTarget($this));
		$this->tpl->parseCurrentBlock();
		*/
		
		// catch feedback message
		sendInfo();
		
		// display infopanel if something happened
		infoPanel();
		
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
		$this->lng->txt("personal_desktop"));
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));
		
		$this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
		
		// output
		$this->displaySelectedItems();
		$this->displaySystemMessages();
		$this->displayMails();
		$this->displayNotes();
		$this->displayUsersOnline();
		$this->displayBookmarks();
		$this->displayFeedback();
		$this->tpl->show();
	}
	
	
	/**
	* show profile of other user
	*/
	function showUserProfile()
	{
		// add template for content
		//$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.usr_personaldesktop.html");
		
		// set locator
		/*
		$this->tpl->setVariable("TXT_LOCATOR", $this->lng->txt("locator"));
		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("ITEM", $this->lng->txt("personal_desktop"));
		$this->tpl->setVariable("LINK_ITEM", $this->ctrl->getLinkTarget($this));
		$this->tpl->parseCurrentBlock();
		*/
		
		// catch feedback message
		sendInfo();
		
		// display infopanel if something happened
		infoPanel();
		
		$this->tpl->setCurrentBlock("header_image");
		$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_pd_b.gif"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("HEADER", $this->lng->txt("personal_desktop"));
		
		include_once("classes/class.ilObjUserGUI.php");
		$user_gui = new ilObjUserGUI("",$_GET["user"], false, false);
		$this->tpl->setVariable("ADM_CONTENT", $user_gui->getPublicProfile());
		
		$this->tpl->show();
	}
	
	/**
	* drop item from desktop
	*/
	function dropItem()
	{
		global $ilUser;
		
		$ilUser->dropDesktopItem($_GET["item_ref_id"], $_GET["type"]);
		$this->show();
	}
	
	/**
	* copied from usr_personaldesktop.php
	*/
	function removeMember()
	{
		global $err_msg;
		if (strlen($err_msg) > 0)
		{
			$this->ilias->raiseError($this->lng->txt($err_msg),$this->ilias->error_obj->MESSAGE);
		}
		$this->show();
	}
	
	/**
	* show details for selected items
	*/
	function showSelectedItemsDetails()
	{
		global $ilUser;
		
		$ilUser->writePref("pd_selected_items_details", "y");
		$this->show();
	}
	
	/**
	* hide details for selected items
	*/
	function hideSelectedItemsDetails()
	{
		global $ilUser;
		
		$ilUser->writePref("pd_selected_items_details", "n");
		$this->show();
	}
	
	
	/**
	* show details for users online
	*/
	function showUsersOnlineDetails()
	{
		global $ilUser;
		
		$ilUser->writePref('show_users_online_details','y');
		$this->show();
	}
	
	/**
	* hide details for users online
	*/
	function hideUsersOnlineDetails()
	{
		global $ilUser;
		
		$ilUser->writePref('show_users_online_details','n');
		$this->show();
	}
	
	/**
	* show details for personal notes
	*/
	function showPDNotesDetails()
	{
		global $ilUser;
		
		$ilUser->writePref('show_pd_notes_details','y');
		$this->show();
	}
	
	/**
	* hide details for personal notes
	*/
	function hidePDNotesDetails()
	{
		global $ilUser;
		
		$ilUser->writePref('show_pd_notes_details','n');
		$this->show();
	}
	
	/**
	* order desktop items by location
	*/
	function orderPDItemsByLocation()
	{
		global $ilUser;
		
		$ilUser->writePref("pd_order_items", "location");
		$this->show();
	}
	
	/**
	* order desktop items by Type
	*/
	function orderPDItemsByType()
	{
		global $ilUser;
		
		$ilUser->writePref("pd_order_items", "type");
		$this->show();
	}
	
	
	/**
	* display selected items
	*/
	function displaySelectedItems()
	{
		
		$html = "";
		
		$html.= $this->getSelectedItemsBlockHTML();
		
		if ($html != "")
		{
			$this->tpl->setCurrentBlock("selected_items");
			$this->tpl->setVariable("SELECTED_ITEMS", $html);
			$this->tpl->parseCurrentBlock();
		}
		$this->ctrl->clearParameters($this);
	}
	
	
	/**
	* get selected item block
	*/
	function getSelectedItemsBlockHTML()
	{
		global $ilUser, $rbacsystem, $objDefinition, $ilBench;
		
		$tpl =& $this->newBlockTemplate();
		
		switch ($ilUser->getPref("pd_order_items"))
		{
			case "location":
			$ok = $this->getSelectedItemsPerLocation($tpl);
			break;
			
			default:
			$ok = $this->getSelectedItemsPerType($tpl);
			break;
		}
		
		
		if ($ok)
		{
			$tpl->setCurrentBlock("pd_header_row");
			$tpl->setVariable("PD_BLOCK_HEADER_CONTENT", $this->lng->txt("selected_items"));
			$tpl->setVariable("PD_BLOCK_HEADER_ID", "th_selected_items");
			if ($ilUser->getPref("pd_selected_items_details") == "y")
			{
				$tpl->setVariable("TXT_SEL_ITEMS_MODE", $this->lng->txt("hide_details"));
				$tpl->setVariable("LINK_SEL_ITEMS_MODE", $this->ctrl->getLinkTarget($this, "hideSelectedItemsDetails"));
			}
			else
			{
				$tpl->setVariable("TXT_SEL_ITEMS_MODE", $this->lng->txt("show_details"));
				$tpl->setVariable("LINK_SEL_ITEMS_MODE", $this->ctrl->getLinkTarget($this, "showSelectedItemsDetails"));
			}
			$tpl->parseCurrentBlock();
			
			// sort by type
			$tpl->setCurrentBlock("footer_link");
			$tpl->setVariable("HREF_FOOT_LINK", $this->ctrl->getLinkTarget($this, "orderPDItemsByType"));
			$tpl->setVariable("TXT_FOOT_LINK", $this->lng->txt("by_type"));
			$tpl->parseCurrentBlock();
			$tpl->touchBlock("footer_item");
			
			$tpl->touchBlock("footer_separator");
			$tpl->touchBlock("footer_item");
			
			// sort by location
			$tpl->setCurrentBlock("footer_link");
			$tpl->setVariable("HREF_FOOT_LINK", $this->ctrl->getLinkTarget($this, "orderPDItemsByLocation"));
			$tpl->setVariable("TXT_FOOT_LINK", $this->lng->txt("by_location"));
			$tpl->parseCurrentBlock();
			$tpl->touchBlock("footer_item");
			
			$tpl->setCurrentBlock("block_footer");
			$tpl->parseCurrentBlock();
		}
		
		return $tpl->get();
	}
	
	/**
	* get selected items per type
	*/
	function getSelectedItemsPerType(&$tpl)
	{
		global $ilUser, $rbacsystem, $objDefinition, $ilBench;
		
		$output = false;
		$types = array(
		array("title" => $this->lng->txt("objs_cat"), "types" => "cat"),
		array("title" => $this->lng->txt("objs_fold"), "types" => "fold"),
		array("title" => $this->lng->txt("objs_crs"), "types" => "crs"),
		array("title" => $this->lng->txt("objs_grp"), "types" => "grp"),
		array("title" => $this->lng->txt("objs_chat"), "types" => "chat"),
		array("title" => $this->lng->txt("objs_frm"), "types" => "frm"),
		array("title" => $this->lng->txt("learning_resources"),"types" => array("lm", "htlm", "sahs", "dbk")),
		array("title" => $this->lng->txt("objs_glo"), "types" => "glo"),
		array("title" => $this->lng->txt("objs_file"), "types" => "file"),
		array("title" => $this->lng->txt("objs_webr"), "types" => "webr"),
		array("title" => $this->lng->txt("objs_exc"), "types" => "exc"),
		array("title" => $this->lng->txt("objs_tst"), "types" => "tst"),
		array("title" => $this->lng->txt("objs_svy"), "types" => "svy"),
		array("title" => $this->lng->txt("objs_mep"), "types" => "mep"),
		array("title" => $this->lng->txt("objs_qpl"), "types" => "qpl"),
		array("title" => $this->lng->txt("objs_spl"), "types" => "spl"),
		array("title" => $this->lng->txt("objs_icrs"), "types" => "icrs"),
		array("title" => $this->lng->txt("objs_icla"), "types" => "icla")
		);
		
		foreach ($types as $type)
		{
			$type = $type["types"];
			$title = $type["title"];
			
			$items = $this->ilias->account->getDesktopItems($type);
			$item_html = array();
			
			if ($ilUser->getPref("pd_selected_items_details") != "n")
			{
				$rel_header = (is_array($type))
				? "th_lres"
				: "th_".$type;
			}
			
			if (count($items) > 0)
			{
				$tstCount = 0;
				$unsetCount = 0;
				$progressCount = 0;
				$unsetFlag = 0;
				$progressFlag = 0;
				$completedFlag = 0;
				if (strcmp($a_type, "tst") == 0) {
					$items = $this->multiarray_sort($items, "used_tries; title");
					foreach ($items as $tst_item) {
						if (!isset($tst_item["used_tries"])) {
							$unsetCount++;
						}
						elseif ($tst_item["used_tries"] == 0) {
							$progressCount++;
						}
					}
				}
				
				foreach($items as $item)
				{
					// get list gui class for each object type
					if ($cur_obj_type != $item["type"])
					{
						$class = $objDefinition->getClassName($item["type"]);
						$location = $objDefinition->getLocation($item["type"]);
						$full_class = "ilObj".$class."ListGUI";
						include_once($location."/class.".$full_class.".php");
						$item_list_gui = new $full_class();
						$item_list_gui->enableDelete(false);
						$item_list_gui->enableCut(false);
						$item_list_gui->enablePayment(false);
						$item_list_gui->enableLink(false);
						$item_list_gui->enableInfoScreen(false);
						if ($ilUser->getPref("pd_selected_items_details") != "y")
						{
							$item_list_gui->enableDescription(false);
							$item_list_gui->enableProperties(false);
							$item_list_gui->enablePreconditions(false);
						}
					}
					// render item row
					$ilBench->start("ilPersonalDesktopGUI", "getListHTML");
					
					$html = $item_list_gui->getListItemHTML($item["ref_id"],
					$item["obj_id"], $item["title"], $item["description"]);
					$ilBench->stop("ilPersonalDesktopGUI", "getListHTML");
					if ($html != "")
					{
						$item_html[] = array("html" => $html, "item_ref_id" => $item["ref_id"],
						"item_obj_id" => $item["obj_id"]);
					}
				}
				
				// output block for resource type
				if (count($item_html) > 0)
				{
					// add a header for each resource type
					if ($ilUser->getPref("pd_selected_items_details") == "y")
					{
						if ($this->ilias->getSetting("icon_position_in_lists") == "item_rows")
						{
							$this->addHeaderRow($tpl, $type, false);
						}
						else
						{
							$this->addHeaderRow($tpl, $type);
						}
						$this->resetRowType();
					}
					
					// content row
					foreach($item_html as $item)
					{
						if ($ilUser->getPref("pd_selected_items_details") != "y" ||
						$this->ilias->getSetting("icon_position_in_lists") == "item_rows")
						{
							$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"], $type, $rel_header);
						}
						else
						{
							$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"], "", $rel_header);
						}
						$output = true;
					}
				}
			}
		}
		
		return $output;
	}
	
	/**
	* get selected items per type
	*/
	function getSelectedItemsPerLocation(&$tpl)
	{
		global $ilUser, $rbacsystem, $objDefinition, $ilBench;
		
		$output = false;
		
		$items = $this->ilias->account->getDesktopItems();
		$item_html = array();
		
		if (count($items) > 0)
		{
			foreach($items as $item)
			{
				//echo "1";
				// get list gui class for each object type
				if ($cur_obj_type != $item["type"])
				{
					$item_list_gui =& $this->getItemListGUI($item["type"]);
					
					$item_list_gui->enableDelete(false);
					$item_list_gui->enableCut(false);
					$item_list_gui->enablePayment(false);
					$item_list_gui->enableLink(false);
					$item_list_gui->enableInfoScreen(false);
					if ($ilUser->getPref("pd_selected_items_details") != "y")
					{
						//echo "3";
						$item_list_gui->enableDescription(false);
						$item_list_gui->enableProperties(false);
						$item_list_gui->enablePreconditions(false);
					}
				}
				// render item row
				$ilBench->start("ilPersonalDesktopGUI", "getListHTML");
				
				$html = $item_list_gui->getListItemHTML($item["ref_id"],
				$item["obj_id"], $item["title"], $item["description"]);
				$ilBench->stop("ilPersonalDesktopGUI", "getListHTML");
				if ($html != "")
				{
					$item_html[] = array("html" => $html, "item_ref_id" => $item["ref_id"],
					"item_obj_id" => $item["obj_id"], "parent_ref" => $item["parent_ref"],
					"type" => $item["type"]);
				}
			}
			
			// output block for resource type
			if (count($item_html) > 0)
			{
				$cur_parent_ref = 0;
				
				// content row
				foreach($item_html as $item)
				{
					// add a parent header row for each new parent
					if ($cur_parent_ref != $item["parent_ref"])
					{
						if ($this->ilias->getSetting("icon_position_in_lists") == "item_rows")
						{
							$this->addParentRow($tpl, $item["parent_ref"], false);
						}
						else
						{
							$this->addParentRow($tpl, $item["parent_ref"]);
						}
						$this->resetRowType();
						$cur_parent_ref = $item["parent_ref"];
					}
					
					//if ($ilUser->getPref("pd_selected_items_details") != "y" ||
					//	$this->ilias->getSetting("icon_position_in_lists") == "item_rows")
					//{
						$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"], $item["type"],
						"th_".$cur_parent_ref);
					//}
					//else
					//{
						//	$this->addStandardRow($tpl, $item["html"], $item["item_ref_id"], $item["item_obj_id"]);
					//}
					$output = true;
				}
			}
		}
		
		return $output;
	}
	
	/**
	* get item list gui class for type
	*/
	function &getItemListGUI($a_type)
	{
		global $objDefinition;
		//echo "<br>+$a_type+";
		if (!is_object($this->item_list_guis[$a_type]))
		{
			$class = $objDefinition->getClassName($a_type);
			$location = $objDefinition->getLocation($a_type);
			$full_class = "ilObj".$class."ListGUI";
			//echo "<br>-".$location."/class.".$full_class.".php"."-";
			include_once($location."/class.".$full_class.".php");
			$item_list_gui = new $full_class();
			$this->item_list_guis[$a_type] =& $item_list_gui;
		}
		else
		{
			$item_list_gui =& $this->item_list_guis[$a_type];
		}
		return $item_list_gui;
	}
	
	/**
	* adds a header row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_type		object type
	* @access	private
	*/
	function addHeaderRow(&$a_tpl, $a_type, $a_show_image = true)
	{
		if (!is_array($a_type))
		{
			$icon = ilUtil::getImagePath("icon_".$a_type.".gif");
			$title = $this->lng->txt("objs_".$a_type);
			$header_id = "th_".$a_type;
		}
		else
		{
			$icon = ilUtil::getImagePath("icon_lm.gif");
			$title = $this->lng->txt("learning_resources");
			$header_id = "th_lres";
		}
		if ($a_show_image)
		{
			$a_tpl->setCurrentBlock("container_header_row_image");
			$a_tpl->setVariable("HEADER_IMG", $icon);
			$a_tpl->setVariable("HEADER_ALT", $title);
		}
		else
		{
			$a_tpl->setCurrentBlock("container_header_row");
		}
		
		$a_tpl->setVariable("BLOCK_HEADER_CONTENT", $title);
		$a_tpl->setVariable("BLOCK_HEADER_ID", $header_id);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}
	
	/**
	* adds a header row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_type		object type
	* @access	private
	*/
	function addParentRow(&$a_tpl, $a_ref_id, $a_show_image = true)
	{
		global $tree;
		
		$par_id = ilObject::_lookupObjId($a_ref_id);
		$type = ilObject::_lookupType($par_id);
		if (!in_array($type, array("lm", "dbk", "sahs", "htlm")))
		{
			$icon = ilUtil::getImagePath("icon_".$type.".gif");
		}
		else
		{
			$icon = ilUtil::getImagePath("icon_lm.gif");
		}
		
		// custom icon
		if ($this->ilias->getSetting("custom_icons") &&
		in_array($type, array("cat","grp","crs")))
		{
			require_once("classes/class.ilContainer.php");
			if (($path = ilContainer::_lookupIconPath($par_id, "small")) != "")
			{
				$icon = $path;
			}
		}
		
		if ($tree->getRootId() != $par_id)
		{
			$title = ilObject::_lookupTitle($par_id);
		}
		else
		{
			$title = $this->lng->txt("repository");
		}
		
		$item_list_gui =& $this->getItemListGUI($type);
		
		$item_list_gui->enableDelete(false);
		$item_list_gui->enableCut(false);
		$item_list_gui->enablePayment(false);
		$item_list_gui->enableLink(false);
		$item_list_gui->enableDescription(false);
		$item_list_gui->enableProperties(false);
		$item_list_gui->enablePreconditions(false);
		$item_list_gui->enablePath(true);
		$item_list_gui->enableCommands(false);
		$html = $item_list_gui->getListItemHTML($a_ref_id,
		$par_id, $title, "");
		
		if ($a_show_image)
		{
			$a_tpl->setCurrentBlock("container_header_row_image");
			$a_tpl->setVariable("HEADER_IMG", $icon);
			$a_tpl->setVariable("HEADER_ALT", $title);
		}
		else
		{
			$a_tpl->setCurrentBlock("container_header_row");
		}
		
		$a_tpl->setVariable("BLOCK_HEADER_CONTENT", $html);
		$a_tpl->setVariable("BLOCK_HEADER_ID", "th_".$a_ref_id);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}
	
	/**
	* adds a standard row to a block template
	*
	* @param	object		$a_tpl		block template
	* @param	string		$a_html		html code
	* @access	private
	*/
	function addStandardRow(&$a_tpl, $a_html, $a_item_ref_id = "", $a_item_obj_id = "",
	$a_image_type = "", $a_related_header = "")
	{
		$this->cur_row_type = ($this->cur_row_type == "row_type_1")
		? "row_type_2"
		: "row_type_1";
		$a_tpl->touchBlock($this->cur_row_type);
		
		if ($a_image_type != "")
		{
			if (!is_array($a_image_type) && !in_array($a_image_type, array("lm", "dbk", "htlm", "sahs")))
			{
				$icon = ilUtil::getImagePath("icon_".$a_image_type.".gif");
				$title = $this->lng->txt("obj_".$a_image_type);
			}
			else
			{
				$icon = ilUtil::getImagePath("icon_lm.gif");
				$title = $this->lng->txt("learning_resource");
			}
			
			// custom icon
			if ($this->ilias->getSetting("custom_icons") &&
			in_array($a_image_type, array("cat","grp","crs")))
			{
				require_once("classes/class.ilContainer.php");
				if (($path = ilContainer::_lookupIconPath($a_item_obj_id, "small")) != "")
				{
					$icon = $path;
				}
			}
			
			$a_tpl->setCurrentBlock("block_row_image");
			$a_tpl->setVariable("ROW_IMG", $icon);
			$a_tpl->setVariable("ROW_ALT", $title);
			$a_tpl->parseCurrentBlock();
		}
		else
		{
			$a_tpl->setVariable("ROW_NBSP", "&nbsp;");
		}
		$a_tpl->setCurrentBlock("container_standard_row");
		$a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
		$rel_headers = ($a_related_header != "")
		? "th_selected_items ".$a_related_header
		: "th_selected_items";
		$a_tpl->setVariable("BLOCK_ROW_HEADERS", $rel_headers);
		$a_tpl->parseCurrentBlock();
		$a_tpl->touchBlock("container_row");
	}
	
	function resetRowType()
	{
		$this->cur_row_type = "";
	}
	
	/**
	* returns a new list block template
	*
	* @access	private
	* @return	object		block template
	*/
	function &newBlockTemplate()
	{
		$tpl = new ilTemplate ("tpl.pd_list_block.html", true, true);
		$this->cur_row_type = "";
		return $tpl;
	}
	
	
	function displaySystemMessages()
	{
		// SYSTEM MAILS
		$umail = new ilMail($_SESSION["AccountId"]);
		$smails = $umail->getMailsOfFolder(0);
		
		if(count($smails))
		{
			// output mails
			$counter = 1;
			foreach ($smails as $mail)
			{
				// GET INBOX FOLDER FOR LINK_READ
				require_once "classes/class.ilMailbox.php";
				
				$mbox = new ilMailbox($_SESSION["AccountId"]);
				$inbox = $mbox->getInboxFolder();
				
				$this->tpl->setCurrentBlock("tbl_system_msg_row");
				$this->tpl->setVariable("ROWCOL",++$counter%2 ? 'tblrow2' : 'tblrow1');
				
				// GET SENDER NAME
				$user = new ilObjUser($mail["sender_id"]);
				
				if(!($fullname = $user->getFullname()))
				{
					$fullname = $this->lng->txt("unknown");
				}
				
				//new mail or read mail?
				$this->tpl->setVariable("MAILCLASS", $mail["m_status"] == 'read' ? 'mailread' : 'mailunread');
				$this->tpl->setVariable("MAIL_FROM", $fullname);
				$this->tpl->setVariable("MAIL_SUBJ", $mail["m_subject"]);
				$this->tpl->setVariable("MAIL_DATE", ilFormat::formatDate($mail["send_time"]));
				$target_name = htmlentities(urlencode("mail_read.php?mobj_id=".$inbox."&mail_id=".$mail["mail_id"]));
				$this->tpl->setVariable("MAIL_LINK_READ", "mail_frameset.php?target=".$target_name);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("tbl_system_msg");
			//headline
			$this->tpl->setVariable("SYSTEM_MAILS",$this->lng->txt("mail_system"));
			//columns headlines
			$this->tpl->setVariable("TXT_SENDER", $this->lng->txt("sender"));
			$this->tpl->setVariable("TXT_SUBJECT", $this->lng->txt("subject"));
			$this->tpl->setVariable("TXT_DATETIME",$this->lng->txt("date")."/".$this->lng->txt("time"));
			$this->tpl->parseCurrentBlock();
		}
	}
	
	
	
	/**
	* display New Mails
	*/
	
	
	function displayMails()
	{
		
		// MAILS
		// GET INBOX FOLDER FOR LINK_READ
		include_once "./include/inc.header.php";
		include_once "./include/inc.mail.php";
		include_once "classes/class.ilObjUser.php";
		include_once "classes/class.ilMailbox.php";
		include_once "classes/class.ilMail.php";
		
		
		// BEGIN MAILS
		$umail = new ilMail($_SESSION["AccountId"]);
		$mbox = new ilMailBox($_SESSION["AccountId"]);
		$inbox = $mbox->getInboxFolder();
		
		//SHOW MAILS FOR EVERY USER
		$mail_data = $umail->getMailsOfFolder($inbox);
		$mail_counter = $umail->getMailCounterData();
		$unreadmails = 0;
		
		
		foreach ($mail_data as $mail)
		{
			//ONLY NEW MAILS WOULD BE ON THE PERONAL DESKTOP
			if($mail["m_status"]== 'unread')
			{
				//echo $mail["m_status"];
				
				$this->tpl->setCurrentBlock("tbl_mails");
				$this->tpl->setVariable("ROWCOL",++$counter%2 ? 'tblrow1' : 'tblrow2');
				$this->tpl->setVariable("NEW_MAIL",$this->lng->txt("email"));
				
				// GET SENDER NAME
				$user = new ilObjUser($mail["sender_id"]);
				
				if(!($fullname = $user->getFullname()))
				{
					$fullname = $this->lng->txt("unknown");
				}
				
				
				$this->tpl->setCurrentBlock("tbl_mails");
				//columns headlines
				$this->tpl->setVariable("NEW_TXT_SENDER", $this->lng->txt("sender"));
				$this->tpl->setVariable("NEW_TXT_SUBJECT", $this->lng->txt("subject"));
				$this->tpl->setVariable("NEW_TXT_DATE",$this->lng->txt("date")."/".$this->lng->txt("time"));
				
				
				$this->tpl->setCurrentBlock("tbl_mails_row");
				$this->tpl->setVariable("NEW_MAIL_FROM", $fullname);
				$this->tpl->setVariable("NEW_MAIL_FROM_LOGIN", $user->getLogin());
				//$this->tpl->setVariable("NEW_MAILCLASS", $mail["status"] == 'read' ? 'mailread' : 'mailunread');
				$this->tpl->setVariable("NEW_MAIL_SUBJ", $mail["m_subject"]);
				$this->tpl->setVariable("NEW_MAIL_DATE", ilFormat::formatDate($mail["send_time"]));
				$target_name = htmlentities(urlencode("mail_read.php?mobj_id=".$inbox."&mail_id=".$mail["mail_id"]));
				$this->tpl->setVariable("NEW_MAIL_LINK_READ", "mail_frameset.php?target=".$target_name);
				$this->tpl->setVariable("IMG_SENDER", $user->getPersonalPicturePath("xxsmall"));
				$this->tpl->setVariable("ALT_SENDER", $user->getLogin());
				$this->tpl->parseCurrentBlock();
				
			}
		}
	}
	
	
	/**
	* display private notes
	*/
	function displayNotes()
	{
		global $ilias;
		
		if ($ilias->account->getPref("show_notes") == "n")
		{
			return;
		}
		//$users_notes = $ilias->account->getPref("show_notes");
		include_once("Services/Notes/classes/class.ilNoteGUI.php");
		$note_gui = new ilNoteGUI(0,0,"");
		$note_gui->enableTargets();
		$html = $note_gui->getPDOverviewNoteListHTML();
		$this->tpl->setVariable("NOTES", $html);
	}
	
	/**
	* display users online
	*/
	function displayUsersOnline()
	{
		global $ilias, $ilUser;
		
		$users_online_pref = $ilias->account->getPref("show_users_online");
		if ($users_online_pref != "y" && $users_online_pref != "associated")
		{
			
			return;
		}
		
		$this->tpl->setVariable("TXT_USERS_ONLINE",$this->lng->txt("users_online"));
		
		if ($users_online_pref == "associated")
		{
			$users = ilUtil::getAssociatedUsersOnline($ilias->account->getId());
		} else {
			$users = ilUtil::getUsersOnline();
		}
		$num = 0;
		
		$users[$ilUser->getId()] =
			array("user_id" => $ilUser->getId(),
				"firstname" => $ilUser->getFirstname(),
				"lastname" => $ilUser->getLastname(),
				"title" => $ilUser->getUTitle(),
				"login" => $ilUser->getLogin());

		foreach ($users as $user_id => $user)
		{
			if ($user_id != ANONYMOUS_USER_ID)
			{
				$num++;
			}
			else
			{
				$visitors = $user["num"];
			}
		}
		
		// parse visitors text
		if (empty($visitors) || $users_online_pref == "associated")
		{
			$visitor_text = "";
		}
		elseif ($visitors == "1")
		{
			$visitor_text = "1 ".$this->lng->txt("visitor");
		}
		else
		{
			$visitor_text = $visitors." ".$this->lng->txt("visitors");
		}
		
		// determine whether the user want's to see details of the active users
		// and remember user preferences, in case the user has changed them.
		$showdetails = $ilias->account->getPref('show_users_online_details') == 'y';
		
		// parse registered users text
		if ($num > 0)
		{
			$user_kind = ($users_online_pref == "associated") ? "associated_user" : "registered_user";
			if ($num == 1)
			{
				$user_list = $num." ".$this->lng->txt($user_kind);
			}
			
			else
			{
				$user_list = $num." ".$this->lng->txt($user_kind."s");
			}
			
			// add details link
			if ($showdetails)
			{
				$text = $this->lng->txt("hide_details");
				$cmd = "hideUsersOnlineDetails";
			}
			else
			{
				$text = $this->lng->txt("show_details");
				$cmd = "showUsersOnlineDetails";
			}
			
			//$user_details_link = "&nbsp;&nbsp;<span style=\"font-weight:lighter\">[</span><a class=\"std\" href=\"usr_personaldesktop.php?cmd=".$cmd."\">".$text."</a><span style=\"font-weight:lighter\">]</span>";
			
			if (!empty($visitor_text))
			{
				$user_list .= " ".$this->lng->txt("and")." ".$visitor_text;
			}
			
			//$user_list .= $user_details_link;
		}
		else
		{
			$user_list = $visitor_text;
		}
		
		$this->tpl->setVariable("USER_LIST",$user_list);
		$this->tpl->setVariable("LINK_USER_DETAILS",
		$this->ctrl->getLinkTarget($this, $cmd));
		$this->tpl->setVariable("TXT_USER_DETAILS", $text);
		
		// display details of users online
		if ($showdetails)
		{
			$z = 0;
			
			foreach ($users as $user_id => $user)
			{
				if ($user_id != ANONYMOUS_USER_ID)
				{
					$rowCol = ilUtil::switchColor($z,"tblrow1","tblrow2");
					//$login_time = ilFormat::dateDiff(ilFormat::datetime2unixTS($user["last_login"]),time());
					
					// hide mail-to icon for anonymous users
					if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID and $_SESSION["AccountId"] != $user_id)
					{
						$this->tpl->setCurrentBlock("mailto_link");
						//$this->tpl->setVariable("IMG_MAIL", ilUtil::getImagePath("icon_pencil_b.gif", false));
						$this->tpl->setVariable("TXT_MAIL",$this->lng->txt("mail"));
						$this->tpl->setVariable("MAIL_USR_LOGIN",$user["login"]);
						$this->tpl->parseCurrentBlock();
					}
					
					// check for profile
					// todo: use user class!
					$user_obj = new ilObjUser($user_id);
					$q = "SELECT value FROM usr_pref WHERE usr_id='".$user_id."' AND keyword='public_profile' AND value='y'";
					$r = $this->ilias->db->query($q);
					
					
					include_once './chat/classes/class.ilChatServerConfig.php';
					if(ilChatServerConfig::_isActive())
					{
						if(!$this->__showActiveChatsOfUser($user_id))
						{
							// Show invite to chat
							$this->__showChatInvitation($user_id);
						}
					}
					
					if ($r->numRows())
					{
						$this->tpl->setCurrentBlock("profile_link");
						//$this->tpl->setVariable("IMG_VIEW", ilUtil::getImagePath("enlarge.gif", false));
						$this->tpl->setVariable("TXT_VIEW",$this->lng->txt("profile"));
						$this->ctrl->setParameter($this, "user", $user_id);
						$this->tpl->setVariable("LINK_PROFILE",
						$this->ctrl->getLinkTarget($this, "showUserProfile"));
						$this->tpl->setVariable("USR_ID",$user_id);
						$this->tpl->parseCurrentBlock();
					}
					
					// user image
					$this->tpl->setCurrentBlock("usr_image");
					$this->tpl->setVariable("USR_IMAGE",
					$user_obj->getPersonalPicturePath("xxsmall"));
					$this->tpl->setVariable("USR_ALT", $this->lng->txt("personal_picture"));
					$this->tpl->parseCurrentBlock();
					
					$this->tpl->setCurrentBlock("tbl_users_row");
					$this->tpl->setVariable("ROWCOL",$rowCol);
					$this->tpl->setVariable("USR_LOGIN",$user["login"]);
					$this->tpl->setVariable("USR_FULLNAME",ilObjUser::setFullname($user["title"],$user["firstname"],$user["lastname"]));
					//$this->tpl->setVariable("USR_LOGIN_TIME",$login_time);
					
					$this->tpl->parseCurrentBlock();
					
					$z++;
				}
			}
			
/*
			if ($z > 0)
			{
				$this->tpl->setCurrentBlock("tbl_users_header");
				$this->tpl->setVariable("TXT_USR",ucfirst($this->lng->txt("user")));
				$this->tpl->setVariable("TXT_USR_LOGIN_TIME",ucfirst($this->lng->txt("login_time")));
				$this->tpl->parseCurrentBlock();
			}
*/
		}
		
		$this->ctrl->clearParameters($this);
	}
	
	
	/**
	* display bookmarks
	*/
	function displayBookmarks()
	{
		include_once("classes/class.ilBookmarkAdministrationGUI.php");
		$bookmark_gui = new ilBookmarkAdministrationGUI();
		$html = $bookmark_gui->getPDBookmarkListHTML();
		$this->tpl->setVariable("BOOKMARKS", $html);
	}
	/**
	* Display Links for Feedback
	*/
	function displayFeedback(){
		include_once('Services/Feedback/classes/class.ilFeedbackGUI.php');
		$feedback_gui = new ilFeedbackGUI();
		$html = $feedback_gui->getPDFeedbackListHTML();
		$this->tpl->setVariable('FEEDBACK', $html);
	}
	
	/**
	* Returns the multidimenstional sorted array
	*
	* Returns the multidimenstional sorted array
	*
	* @author       Muzaffar Altaf <maltaf@tzi.de>
	* @param array $arrays The array to be sorted
	* @param string $key_sort The keys on which array must be sorted
	* @access public
	*/
	function multiarray_sort ($array, $key_sort)
	{
		if ($array) {
			$key_sorta = explode(";", $key_sort);
			
			$multikeys = array_keys($array);
			$keys = array_keys($array[$multikeys[0]]);
			
			for($m=0; $m < count($key_sorta); $m++) {
				$nkeys[$m] = trim($key_sorta[$m]);
			}
			$n += count($key_sorta);
			
			for($i=0; $i < count($keys); $i++){
				if(!in_array($keys[$i], $key_sorta)) {
					$nkeys[$n] = $keys[$i];
					$n += "1";
				}
			}
			
			for($u=0;$u<count($array); $u++) {
				$arr = $array[$multikeys[$u]];
				for($s=0; $s<count($nkeys); $s++) {
					$k = $nkeys[$s];
					$output[$multikeys[$u]][$k] = $array[$multikeys[$u]][$k];
				}
			}
			sort($output);
			return $output;
		}
	}
	
	/**
	* set personal desktop tabs
	*/
	function setTabs()
	{
		$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");
		
		$script_name = basename($_SERVER["SCRIPT_NAME"]);
		
		$command = $_GET["cmd"] ? $_GET["cmd"] : "";
		
		if (ereg("whois",$command) or $script_name == "profile.php")
		{
			$who_is_online = true;
		}
		
		// to do: use ilTabsGUI here!
		
		// personal desktop home
		$inc_type = (strtolower($_GET["baseClass"]) == "ilpersonaldesktopgui" &&
		(strtolower($this->cmdClass) == "ilpersonaldesktopgui" ||
		$this->cmdClass == ""))
		? "tabactive"
		: "tabinactive";
		$inhalt1[] = array($inc_type, $this->ctrl->getLinkTarget($this), $this->lng->txt("overview"));
		
		// user profile
		$inc_type = (strtolower($this->cmdClass) == "ilpersonalprofilegui")
		? "tabactive"
		: "tabinactive";
		$inhalt1[] = array($inc_type, $this->ctrl->getLinkTargetByClass("ilPersonalProfileGUI"),
		$this->lng->txt("personal_profile"));
		
		if ($_SESSION["AccountId"] != ANONYMOUS_USER_ID)
		{
			// user calendar
			if ($this->ilias->getSetting("enable_calendar"))
			{
				$inc_type = ($script_name == "dateplaner.php")
				? "tabactive"
				: "tabinactive";
				$inhalt1[] = array($inc_type,"dateplaner.php",$this->lng->txt("calendar"));
			}
			
			// private notes
			$inc_type = (strtolower($this->cmdClass) == "ilpdnotesgui" ||
			strtolower($this->cmdClass) == "ilnotegui")
			? "tabactive"
			: "tabinactive";
			$inhalt1[] = array($inc_type,
			$this->ctrl->getLinkTargetByClass("ilpdnotesgui"),
			$this->lng->txt("private_notes"));
			
			// user bookmarks
			$inc_type = (strtolower($this->cmdClass) == "ilbookmarkadministrationgui")
			? "tabactive"
			: "tabinactive";
			$inhalt1[] = array($inc_type,
			$this->ctrl->getLinkTargetByClass("ilbookmarkadministrationgui"),
			$this->lng->txt("bookmarks"));
			
		}
		
		// Tracking
		
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if (ilObjUserTracking::_enabledLearningProgress())
		{
			$cmd_classes = array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui');
			$inc_type = in_array(strtolower($this->cmdClass),$cmd_classes) ? 'tabactive' : 'tabinactive';
			
			$inhalt1[] = array($inc_type, $this->ctrl->getLinkTargetByClass("ilLearningProgressGUI"),
			$this->lng->txt("learning_progress"));
		}
		
		include_once "./payment/classes/class.ilPaymentVendors.php";
		include_once "./payment/classes/class.ilPaymentTrustees.php";
		include_once "./payment/classes/class.ilPaymentShoppingCart.php";
		include_once "./payment/classes/class.ilPaymentBookings.php";
		
		if(ilPaymentShoppingCart::_hasEntries($this->ilias->account->getId()) or
		ilPaymentBookings::_getCountBookingsByCustomer($this->ilias->account->getId()))
		{
			$this->lng->loadLanguageModule('payment');

			$cmd_classes = array('ilpaymentgui','ilpaymentshoppingcartgui','ilpaymentbuyedobjectsgui');
			$inc_type = in_array(strtolower($this->cmdClass),$cmd_classes) ? 'tabactive' : 'tabinactive';

			$inhalt1[] = array($inc_type, $this->ctrl->getLinkTargetByClass("ilPaymentGUI"),
			$this->lng->txt("paya_shopping_cart"));
		}
		if(ilPaymentVendors::_isVendor($this->ilias->account->getId()) or
		ilPaymentTrustees::_hasAccess($this->ilias->account->getId()))
		{
			$this->lng->loadLanguageModule('payment');

			$cmd_classes = array('ilpaymentstatisticgui','ilpaymentobjectgui','ilpaymenttrusteegui','ilpaymentadmingui');
			$inc_type = in_array(strtolower($this->cmdClass),$cmd_classes) ? 'tabactive' : 'tabinactive';

			$inhalt1[] = array($inc_type, $this->ctrl->getLinkTargetByClass("ilPaymentAdminGUI"),
			$this->lng->txt("paya_header"));
		}
		
		for ( $i=0; $i<sizeof($inhalt1); $i++)
		{
			if ($inhalt1[$i][1] != "")
			{	$this->tpl->setCurrentBlock("tab");
				$this->tpl->setVariable("TAB_TYPE",$inhalt1[$i][0]);
				$this->tpl->setVariable("TAB_LINK",$inhalt1[$i][1]);
				$this->tpl->setVariable("TAB_TEXT",$inhalt1[$i][2]);
				$this->tpl->setVariable("TAB_TARGET",$inhalt1[$i][3]);
				$this->tpl->parseCurrentBlock();
			}
		}
		
		$this->tpl->setCurrentBlock("tabs");
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* workaround for menu in calendar only
	*/
	function jumpToProfile()
	{
		$this->ctrl->redirectByClass("ilpersonalprofilegui");
	}
	
	/**
	* workaround for menu in calendar only
	*/
	function jumpToBookmarks()
	{
		$this->ctrl->redirectByClass("ilbookmarkadministrationgui");
	}
	
	/**
	* workaround for menu in calendar only
	*/
	function jumpToNotes()
	{
		$this->ctrl->redirectByClass("ilpdnotesgui");
	}
	
	/**
	* workaround for menu in calendar only
	*/
	function jumpToLP()
	{
		$this->ctrl->redirectByClass("illearningprogressgui");
	}
	
	
	function __showActiveChatsOfUser($a_usr_id)
	{
		global $rbacsystem;
		
		// show chat info
		include_once './chat/classes/class.ilChatRoom.php';
		
		$chat_id = ilChatRoom::_isActive($a_usr_id);
		foreach(ilObject::_getAllReferences($chat_id) as $ref_id)
		{
			if($rbacsystem->checkAccess('read',$ref_id))
			{
				$this->tpl->setCurrentBlock("chat_info");
				$this->tpl->setVariable("CHAT_ACTIVE_IN",$this->lng->txt('chat_active_in'));
				$this->tpl->setVariable("CHAT_LINK","chat/chat.php?ref_id=".$ref_id."&room_id=0");
				$this->tpl->setVariable("CHAT_TITLE",ilObject::_lookupTitle($chat_id));
				$this->tpl->parseCurrentBlock();
				
				return true;
			}
		}
		return false;
	}
	
	function __showChatInvitation($a_usr_id)
	{
		global $rbacsystem,$ilUser;
		
		include_once './chat/classes/class.ilObjChat.php';
		
		if($a_usr_id == $ilUser->getId())
		{
			return false;
		}
		
		if($rbacsystem->checkAccess('read',ilObjChat::_getPublicChatRefId())
		and $rbacsystem->checkAccessOfUser($a_usr_id,'read',ilObjChat::_getPublicChatRefId()))
		{
			$this->tpl->setCurrentBlock("chat_link");
			$this->tpl->setVariable("TXT_CHAT_INVITE",$this->lng->txt('chat_invite'));
			$this->tpl->setVariable("CHAT_LINK",'chat/chat.php?ref_id='.ilObjChat::_getPublicChatRefId().
			'&usr_id='.$a_usr_id.'&cmd=invitePD');
			$this->tpl->parseCurrentBlock();
			
			return true;
		}
		return false;
	}

	function __loadNextClass()
	{
		if(isset($_SESSION['il_pd_history']))
		{
			return $_SESSION['il_pd_history'];
		}
		else
		{
			return '';
		}
	}
	function __storeLastClass($a_class)
	{
		$_SESSION['il_pd_history'] = $a_class;
		$this->cmdClass = $a_class;
	}
}
?>