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
		$this->lng->loadLanguageModule("pdesk");
		
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
				$this->getStandardTemplates();
				$this->setTabs();
				$this->tpl->setTitle($this->lng->txt("personal_desktop"));
				$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
					$this->lng->txt("personal_desktop"));

				include_once("Services/Feedback/classes/class.ilFeedbackGUI.php");
				$feedback_gui = new ilFeedbackGUI();
				$ret =& $this->ctrl->forwardCommand($feedback_gui);
				break;
				// bookmarks
			case "ilbookmarkadministrationgui":
				include_once("./Services/PersonalDesktop/classes/class.ilBookmarkAdministrationGUI.php");
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
	* Activate hidden block
	*/
	function activateBlock()
	{
		global $ilUser;
		
		if ($_POST["block"] != "")
		{
			include_once("Services/Block/classes/class.ilBlockSetting.php");
			ilBlockSetting::_writeDetailLevel($_POST["block"], 1, $ilUser->getId());
		}

		$this->show();
	}
	
	/**
	* show desktop
	*/
	function show()
	{
		// add template for content
		$this->pd_tpl = new ilTemplate("tpl.usr_personaldesktop.html", true, true);
		$this->tpl->getStandardTemplate();
		
		// catch feedback message
		sendInfo();
		
		// display infopanel if something happened
		infoPanel();
		
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			$this->lng->txt("personal_desktop"));
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));
		$this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
		
		// output
		$this->pd_tpl->setCurrentBlock("selected_items");
		$this->pd_tpl->setVariable("SELECTED_ITEMS",
			$this->displaySelectedItems());
		$this->pd_tpl->parseCurrentBlock();

		$this->tpl->setContent($this->pd_tpl->get());
		$this->tpl->setRightContent($this->getRightColumnHTML());
		$this->tpl->setLeftContent($this->getLeftColumnHTML());
		$this->tpl->show();
	}
	
	/**
	* Display right column
	*/
	function getRightColumnHTML()
	{
		global $ilUser, $lng, $ilCtrl;
		
		// add template for content
		$tpl = new ilTemplate("tpl.usr_pd_right_column.html", true, true,
			"Services/PersonalDesktop");
		
		$tpl->setVariable("MAILS", $this->displayMails());
		$tpl->setVariable("NOTES", $this->displayNotes());
		$tpl->setVariable("USERS_ONLINE", $this->displayUsersOnline());
		$tpl->setVariable("BOOKMARKS", $this->displayBookmarks());

		// show selector for hidden blocks
		include_once("Services/Block/classes/class.ilBlockSetting.php");
		$hidden_blocks = array();
		$blocks = array("pdmail" => $lng->txt("mail"),
			"pdnote" => $lng->txt("notes"),
			"pdusers" => $lng->txt("users_online"),
			"pdbookm" => $lng->txt("my_bms"));

		foreach($blocks as $block => $txt)
		{
			if (ilBlockSetting::_lookupDetailLevel($block, $ilUser->getId()) == 0)
			{
				$hidden_blocks[$block] = $txt;
			}
		}
		if (count($hidden_blocks) > 0)
		{
			$tpl->setCurrentBlock("hidden_block_selector");
			$tpl->setVariable("HB_ACTION", $ilCtrl->getFormAction($this));
			$tpl->setVariable("BLOCK_SEL", ilUtil::formSelect("", "block", $hidden_blocks,
				false, true, 0, "ilEditSelect"));
			$tpl->setVariable("TXT_ACTIVATE", $lng->txt("pdesk_activate_block"));
			$tpl->parseCurrentBlock();
		}
		return $tpl->get();
	}

	/**
	* Display left column
	*/
	function getLeftColumnHTML()
	{
		global $ilUser, $lng, $ilCtrl;
		
		// add template for content
		$tpl = new ilTemplate("tpl.usr_pd_left_column.html", true, true,
			"Services/PersonalDesktop");
		
		$tpl->setVariable("SYS_MESSAGES", $this->displaySystemMessages());
		$tpl->setVariable("FEEDBACK", $this->displayFeedback());
		
		return $tpl->get();
	}

	function prepareContentView()
	{
		// add template for content
		$this->pd_tpl = new ilTemplate("tpl.usr_personaldesktop.html", true, true);
		$this->tpl->getStandardTemplate();
		
		// catch feedback message
		sendInfo();
		
		// display infopanel if something happened
		infoPanel();
		
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			$this->lng->txt("personal_desktop"));
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));
		$this->tpl->setVariable("IMG_SPACE", ilUtil::getImagePath("spacer.gif", false));
	}
	
	/**
	* show profile of other user
	*/
	function showUserProfile()
	{
		global $lng, $ilCtrl;
		
		$this->prepareContentView();
		
		include_once("classes/class.ilObjUserGUI.php");
		$user_gui = new ilObjUserGUI("",$_GET["user"], false, false);
		
		include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
		$content_block = new ilPDContentBlockGUI("ilpersonaldesktopgui", "show");
		$content_block->setContent($user_gui->getPublicProfile("", false, true));
		$content_block->setTitle($lng->txt("profile_of")." ".
			$user_gui->object->getLogin());
		$content_block->setColSpan(2);
		$content_block->setImage(ilUtil::getImagePath("icon_usr.gif"));
		$content_block->addHeaderCommand($ilCtrl->getLinkTarget($this, "show"),
			$lng->txt("close"));
		
		$this->tpl->setContent($content_block->getHTML());
		$this->tpl->setRightContent($this->getRightColumnHTML());
		$this->tpl->setLeftContent($this->getLeftColumnHTML());

		$this->tpl->show();
	}

	/**
	* show single note
	*/
	function showNote()
	{
		global $lng, $ilCtrl;
		
		$this->prepareContentView();
		
		include_once("./Services/Notes/classes/class.ilNoteGUI.php");
		$note_gui = new ilNoteGUI();
		$note_gui->enableTargets();
		include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
		$content_block = new ilPDContentBlockGUI("ilpersonaldesktopgui", "show");
		$content_block->setContent($note_gui->getPDNoteHTML($_GET["note_id"]));
		$content_block->setTitle($lng->txt("note"));
		$content_block->setColSpan(2);
		$content_block->setImage(ilUtil::getImagePath("icon_note.gif"));
		$content_block->addHeaderCommand($ilCtrl->getLinkTarget($this, "show"),
			$lng->txt("close"));
		
		$this->tpl->setContent($content_block->getHTML());
		$this->tpl->setRightContent($this->getRightColumnHTML());
		$this->tpl->setLeftContent($this->getLeftColumnHTML());

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
	* order desktop items by location
	*/
	function orderPDItemsByLocation()
	{
		global $ilUser, $ilCtrl;
		
		$ilUser->writePref("pd_order_items", "location");
		
		if ($ilCtrl->isAsynch())
		{
			echo $this->displaySelectedItems();
			exit;
		}
		else
		{
			$this->show();
		}
	}
	
	/**
	* order desktop items by Type
	*/
	function orderPDItemsByType()
	{
		global $ilUser, $ilCtrl;
		
		$ilUser->writePref("pd_order_items", "type");
		if ($ilCtrl->isAsynch())
		{
			echo $this->displaySelectedItems();
			exit;
		}
		else
		{
			$this->show();
		}
	}
	
	
	/**
	* display selected items
	*/
	function displaySelectedItems()
	{
		global $ilUser;
		
		include_once("./Services/Block/classes/class.ilBlockSetting.php");
		$this->detail_level = (isset($_GET["pditems_block_detail"]))
			? $_GET["pditems_block_detail"]
			: ilBlockSetting::_lookupDetailLevel("pditems", $ilUser->getId());
		$html = "";
		
		$html.= $this->getSelectedItemsBlockHTML();
		
		if ($html != "")
		{
			include_once("./Services/PersonalDesktop/classes/class.ilPDSelectedItemsBlockGUI.php");
			$pd_block = new ilPDSelectedItemsBlockGUI("ilpersonaldesktopgui", "show");
			$pd_block->setContent($html);
			$this->ctrl->clearParameters($this);
			
			return $pd_block->getHTML();
		}
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
		
		/*
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
		}*/
		
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
			
			if ($this->detail_level == 3)
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
						if ($this->detail_level < 3)
						{
							$item_list_gui->enableDescription(false);
							$item_list_gui->enableProperties(false);
							$item_list_gui->enablePreconditions(false);
						}
						if ($this->detail_level < 2)
						{
							$item_list_gui->enableCommands(true, true);
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
					if ($this->detail_level == 3)
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
						if ($this->detail_level < 3 ||
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
					if ($this->detail_level < 3)
					{
						//echo "3";
						$item_list_gui->enableDescription(false);
						$item_list_gui->enableProperties(false);
						$item_list_gui->enablePreconditions(false);
					}
					if ($this->detail_level < 2)
					{
						$item_list_gui->enableCommands(true, true);
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
		in_array($type, array("cat","grp","crs", "root")))
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
			$nd = $tree->getNodeData(ROOT_FOLDER_ID);
			$title = $nd["title"];
			if ($title == "ILIAS")
			{
				$title = $this->lng->txt("repository");
			}
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
	
	/**
	* Display system messages.
	*/
	function displaySystemMessages()
	{
		include_once("Services/Mail/classes/class.ilPDSysMessageBlockGUI.php");
		$sys_block = new ilPDSysMessageBlockGUI("ilpersonaldesktopgui", "show");
		return $sys_block->getHTML();
	}
	
	/**
	* display New Mails
	*/
	function displayMails()
	{
		include_once("Services/Mail/classes/class.ilPDMailBlockGUI.php");
		$mail_block = new ilPDMailBlockGUI("ilpersonaldesktopgui", "show");
		return $mail_block->getHTML();
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

		include_once("Services/Notes/classes/class.ilPDNotesBlockGUI.php");
		$notes_block = new ilPDNotesBlockGUI("ilpersonaldesktopgui", "show");
		
		return $notes_block->getHTML();
	}
	
	/**
	* display users online
	*/
	function displayUsersOnline()
	{
		global $ilias, $ilUser, $rbacsystem, $ilSetting;
		
		include_once("./Services/PersonalDesktop/classes/class.ilUsersOnlineBlockGUI.php");
		$users_block = new ilUsersOnlineBlockGUI("ilpersonaldesktopgui", "show");
		return $users_block->getHTML();
	}

	/**
	* display bookmarks
	*/
	function displayBookmarks()
	{
		global $ilCtrl;
		
		include_once("./Services/PersonalDesktop/classes/class.ilBookmarkAdministrationGUI.php");
		$bookmark_gui = new ilBookmarkAdministrationGUI();
		return $ilCtrl->getHTML($bookmark_gui);
	}
	
	/**
	* Display Links for Feedback
	*/
	function displayFeedback()
	{
		include_once("./Services/Feedback/classes/class.ilPDFeedbackBlockGUI.php");
		$fb_block = new ilPDFeedbackBlockGUI("ilpersonaldesktopgui", "show");
		return $fb_block->getHTML();

		include_once('Services/Feedback/classes/class.ilFeedbackGUI.php');
		$feedback_gui = new ilFeedbackGUI();
		return $feedback_gui->getPDFeedbackListHTML();
	}
	
	
	/**
	* Update Block (asynchronous)
	*/
	function updateBlock()
	{
		switch($_GET["block_id"])
		{
			// bookmarks
			case "block_pdbookm_0":
				echo $this->displayBookmarks();
				break;
				
			// users
			case "block_pdusers_0":
				echo $this->displayUsersOnline();
				break;

			// notes
			case "block_pdnote_0":
				echo $this->displayNotes();
				break;
			
			// mails
			case "block_pdmail_0":
				echo $this->displayMails();
				break;
				
			// system messages
			case "block_pdsysmess_0":
				echo $this->displaySystemMessages();
				break;
				
			// personal desktop items
			case "block_pditems_0":
				echo $this->displaySelectedItems();
				break;

			// personal desktop items
			case "block_pdfeedb_0":
				echo $this->displayFeedback();
				break;
				
			default:
				echo "Error: ilPersonalDesktopGUI::updateBlock: Block '".
					$_GET["block_id"]."' unknown.";
				break;
		}
		
		exit;
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
		$this->cmdClass == "" || (strtolower($this->cmdClass)) == "ilfeedbackgui"))
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

	function __loadNextClass()
	{
		$stored_classes = array('ilpersonaldesktopgui',
								'ilpersonalprofilegui',
								'ilpdnotesgui',
								'ilbookmarkadministrationgui',
								'illearningprogressgui',
								'ilpaymentadmingui');

		if(isset($_SESSION['il_pd_history']) and in_array($_SESSION['il_pd_history'],$stored_classes))
		{
			return $_SESSION['il_pd_history'];
		}
		else
		{
			$this->ctrl->getNextClass($this);
		}
	}
	function __storeLastClass($a_class)
	{
		$_SESSION['il_pd_history'] = $a_class;
		$this->cmdClass = $a_class;
	}
}
?>