<?php

/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* Private Notes on PD
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPDNotesGUI: ilNoteGUI, ilFeedbackGUI
*
*/


class ilPDNotesGUI
{

	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;
	var $tpl;
	var $lng;
	
	const PUBLIC_COMMENTS = "publiccomments";
	const PRIVATE_NOTES = "privatenotes";

	/**
	* Constructor
	*
	* @access	public
	*/
	function ilPDNotesGUI()
	{
		global $ilias, $tpl, $lng, $ilCtrl;

		// initiate variables
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ctrl =& $ilCtrl;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();

		switch($next_class)
		{
			case "ilnotegui":
				// scorm2004-start
				$this->setTabs();
				// scorm2004-end
				$this->displayHeader();
				$this->view();		// forwardCommand is invoked in view() method
				break;
				
			default:
				// scorm2004-start
				$this->setTabs();
				// scorm2004-end
				$cmd = $this->ctrl->getCmd("view");
				$this->displayHeader();
				$this->$cmd();
				break;
		}
		$this->tpl->show(true);
		return true;
	}

	/**
	* display header and locator
	*/
	function displayHeader()
	{
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			$this->lng->txt("personal_desktop"));
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));
				
		// catch feedback message
		// display infopanel if something happened
		ilUtil::infoPanel();

	}

	/*
	* display notes
	*/
	function view()
	{
		global $ilUser, $lng;

		//$this->tpl->addBlockFile("ADM_CONTENT", "objects", "tpl.table.html")
		include_once("Services/Notes/classes/class.ilNoteGUI.php");
		
		// output related item selection (if more than one)
		include_once("Services/Notes/classes/class.ilNote.php");
		$rel_objs = ilNote::_getRelatedObjectsOfUser();
		
		if ($ilUser->getPref("pd_notes_rel_obj") > 0)
		{
			$notes_gui = new ilNoteGUI($ilUser->getPref("pd_notes_rel_obj"), 0,
				ilObject::_lookupType($ilUser->getPref("pd_notes_rel_obj")),true);
		}
		else
		{
			$notes_gui = new ilNoteGUI(0, $ilUser->getId(), "pd");
		}
		
		if ($this->getMode() == ilPDNotesGUI::PRIVATE_NOTES)
		{
			$notes_gui->enablePrivateNotes(true);
			$notes_gui->enablePublicNotes(false);
		}
		else
		{
			$notes_gui->enablePrivateNotes(false);
			$notes_gui->enablePublicNotes(true);
		}
		$notes_gui->enableHiding(false);
		$notes_gui->enableTargets(true);
		$notes_gui->enableMultiSelection(true);

		$next_class = $this->ctrl->getNextClass($this);

		if ($next_class == "ilnotegui")
		{
			$html = $this->ctrl->forwardCommand($notes_gui);
		}
		else
		{
			$html = $notes_gui->getNotesHTML();
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.pd_notes.html", "Services/Notes");
		
		if (count($rel_objs) > 1 ||
			($rel_objs[0]["rep_obj_id"] > 0))
		{
			// prepend personal dektop, if first object 
			if ($rel_objs[0]["rep_obj_id"] > 0)
			{
				$rel_objs = array_merge(array(0), $rel_objs);
			}

			foreach($rel_objs as $obj)
			{
				$this->tpl->setCurrentBlock("related_option");
				$this->tpl->setVariable("VAL_RELATED",
					$obj["rep_obj_id"]);
//echo "-".$obj["rep_obj_id"]."-".$obj["obj_type"]."-";
				if ($obj["rep_obj_id"] > 0)
				{
					$type = ilObject::_lookupType($obj["rep_obj_id"]);
					$type_str = (in_array($type, array("lm", "htlm", "sahs", "dbk")))
						? $lng->txt("learning_resource")
						: $lng->txt("obj_".$type);
					$this->tpl->setVariable("TXT_RELATED", $type_str.": ".
						ilObject::_lookupTitle($obj["rep_obj_id"]));
				}
				else
				{
					$this->tpl->setVariable("TXT_RELATED",
						$lng->txt("personal_desktop"));
				}
				if ($obj["rep_obj_id"] == $ilUser->getPref("pd_notes_rel_obj"))
				{
					$this->tpl->setVariable("SEL", 'selected="selected"');
				}
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("related_selection");
			$this->tpl->setVariable("TXT_CHANGE", $lng->txt("change"));
			$this->tpl->setVariable("TXT_RELATED_TO", $lng->txt("related_to"));
			$this->tpl->setVariable("TXT_FILTER", $lng->txt("filter"));
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		// output notes
		$this->tpl->setVariable("NOTES", $html);
		$this->tpl->parseCurrentBlock();

	}
	
	/**
	* change related object
	*/
	function changeRelatedObject()
	{
		global $ilUser;
		
		$ilUser->writePref("pd_notes_rel_obj", $_POST["rel_obj"]);
		$this->ctrl->redirect($this);
	}

	// scorm2004-start
	/**
	* Show subtabs
	*/
	function setTabs()
	{
		global $ilTabs, $ilUser, $ilCtrl;
		
		$ilTabs->addSubTabTarget("private_notes",
			$ilCtrl->getLinkTarget($this, "showPrivateNotes"), "", "", "",
			($this->getMode() == ilPDNotesGUI::PRIVATE_NOTES));
		$ilTabs->addSubTabTarget("notes_public_comments",
			$ilCtrl->getLinkTarget($this, "showPublicComments"), "", "", "",
			($this->getMode() == ilPDNotesGUI::PUBLIC_COMMENTS));
	}
	
	/**
	* Show private notes
	*/
	function showPrivateNotes()
	{
		global $ilUser, $ilCtrl;
		
		$ilUser->writePref("pd_notes_mode", ilPDNotesGUI::PRIVATE_NOTES);
		$ilCtrl->redirect($this, "");
	}
	
	/**
	* Show public comments
	*/
	function showPublicComments()
	{
		global $ilUser, $ilCtrl;
		
		$ilUser->writePref("pd_notes_mode", ilPDNotesGUI::PUBLIC_COMMENTS);
		$ilCtrl->redirect($this, "");
	}

	/**
	* Get current mode
	*/
	function getMode()
	{
		global $ilUser;
		
		if ($ilUser->getPref("pd_notes_mode") == ilPDNotesGUI::PUBLIC_COMMENTS)
		{
			return ilPDNotesGUI::PUBLIC_COMMENTS;
		}
		else
		{
			return ilPDNotesGUI::PRIVATE_NOTES;
		}
	}
}
?>
