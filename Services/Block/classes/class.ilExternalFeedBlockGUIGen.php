<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once("Services/Block/classes/class.ilBlockGUI.php");
define("IL_FORM_EDIT", 0);
define("IL_FORM_CREATE", 1);
define("IL_FORM_RE_EDIT", 2);
define("IL_FORM_RE_CREATE", 3);

/**
* GUI class for external news feed custom block.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilExternalFeedBlockGUIGen extends ilBlockGUI
{

	protected $form_edit_mode;

	/**
	* Constructor.
	*
	*/
	public function __construct()
	{
		global $ilCtrl;
		
		$this->ctrl = $ilCtrl;
		
		
		include_once("Services/Block/classes/class.ilExternalFeedBlock.php");
		if ($_GET["external_feed_block_id"] > 0)
		{
			$this->external_feed_block = new ilExternalFeedBlock($_GET["external_feed_block_id"]);
		}
		
		$this->ctrl->saveParameter($this, array("external_feed_block_id"));
		

	}

	/**
	* Execute command.
	*
	*/
	public function &executeCommand()
	{
		global $ilCtrl;
		
		// get next class and command
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		switch ($next_class)
		{
			default:
				$html = $this->$cmd();
				break;
		}
		
		return $html;

	}

	/**
	* Set FormEditMode.
	*
	* @param	int	$a_form_edit_mode	Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE | IL_FORM_RE_EDIT | IL_FORM_RE_CREATE)
	*/
	public function setFormEditMode($a_form_edit_mode)
	{
		$this->form_edit_mode = $a_form_edit_mode;
	}

	/**
	* Get FormEditMode.
	*
	* @return	int	Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE | IL_FORM_RE_EDIT | IL_FORM_RE_CREATE)
	*/
	public function getFormEditMode()
	{
		return $this->form_edit_mode;
	}

	/**
	* FORM FeedBlock: Output form.
	*
	*/
	public function outputFormFeedBlock()
	{
		global $lng;
		
		$lng->loadLanguageModule("block");
		
		include("Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$form_gui = new ilPropertyFormGUI();
		
		$values = $this->getValuesFeedBlock();
		
		// Property Title
		$alert = ($this->form_check["FeedBlock"]["Title"]["error"] != "")
			? $this->form_check["FeedBlock"]["Title"]["error"]
			: "";
		$form_gui->addTextProperty($lng->txt("block_feed_block_title"),
			"block_title",
			$values["Title"],
			"", $alert, true
			, "200");
		
		// Property FeedUrl
		$alert = ($this->form_check["FeedBlock"]["FeedUrl"]["error"] != "")
			? $this->form_check["FeedBlock"]["FeedUrl"]["error"]
			: "";
		$form_gui->addTextProperty($lng->txt("block_feed_block_feed_url"),
			"block_feed_url",
			$values["FeedUrl"],
			$lng->txt("block_feed_block_feed_url_info"), $alert, true
			, "250");
		
		// save and cancel commands
		if (in_array($this->getFormEditMode(), array(IL_FORM_CREATE,IL_FORM_RE_CREATE)))
		{
			$form_gui->addCommandButton("saveFeedBlock", $lng->txt("save"));
			$form_gui->addCommandButton("cancelSaveFeedBlock", $lng->txt("cancel"));
		}
		else
		{
			$form_gui->addCommandButton("updateFeedBlock", $lng->txt("save"));
			$form_gui->addCommandButton("cancelUpdateFeedBlock", $lng->txt("cancel"));
		}
		
		$form_gui->setTitle($lng->txt("block_feed_block_head"));
		$form_gui->setFormAction($this->ctrl->getFormAction($this));
		
		// individual preparation of form
		$this->prepareFormFeedBlock($form_gui);
		
		return $form_gui->getHTML();

	}

	/**
	* FORM FeedBlock: Edit form.
	*
	*/
	public function editFeedBlock()
	{
		$this->setFormEditMode(IL_FORM_EDIT);
		return $this->outputFormFeedBlock();

	}

	/**
	* FORM FeedBlock: Create ExternalFeedBlock.
	*
	*/
	public function createFeedBlock()
	{
		$this->setFormEditMode(IL_FORM_CREATE);
		return $this->outputFormFeedBlock();

	}

	/**
	* FORM FeedBlock: Save ExternalFeedBlock.
	*
	*/
	public function saveFeedBlock()
	{
		include_once("./classes/class.ilObjAdvancedEditing.php");
		if ($this->checkInputFeedBlock())
		{
			$this->external_feed_block = new ilExternalFeedBlock();
			$this->external_feed_block->setTitle(ilUtil::stripSlashes($_POST["block_title"]));
			$this->external_feed_block->setFeedUrl(ilUtil::stripSlashes($_POST["block_feed_url"]));
			$this->prepareSaveFeedBlock($this->external_feed_block);
			$this->external_feed_block->create();
		}
		else
		{
			$this->setFormEditMode(IL_FORM_RE_CREATE);
			return $this->outputFormFeedBlock();
		}

	}

	/**
	* FORM FeedBlock: Update ExternalFeedBlock.
	*
	*/
	public function updateFeedBlock()
	{
		include_once("./classes/class.ilObjAdvancedEditing.php");
		if ($this->checkInputFeedBlock())
		{
			
			$this->external_feed_block->setTitle(ilUtil::stripSlashes($_POST["block_title"]));
			$this->external_feed_block->setFeedUrl(ilUtil::stripSlashes($_POST["block_feed_url"]));
			$this->external_feed_block->update();
		}
		else
		{
			$this->setFormEditMode(IL_FORM_RE_EDIT);
			return $this->outputFormFeedBlock();
		}

	}

	/**
	* FORM FeedBlock: Get current values for ExternalFeedBlock form.
	*
	*/
	public function getValuesFeedBlock()
	{
		$values = array();
		
		switch ($this->getFormEditMode())
		{
			case IL_FORM_CREATE:
				$values["Title"] = "";
				$values["FeedUrl"] = "";
				break;
				
			case IL_FORM_EDIT:
				$values["Title"] = $this->external_feed_block->getTitle();
				$values["FeedUrl"] = $this->external_feed_block->getFeedUrl();
				break;
				
			case IL_FORM_RE_EDIT:
			case IL_FORM_RE_CREATE:
				$values["Title"] = ilUtil::stripSlashes($_POST["block_title"]);
				$values["FeedUrl"] = ilUtil::stripSlashes($_POST["block_feed_url"]);
				break;
		}
		
		return $values;

	}

	/**
	* FORM FeedBlock: Check input.
	*
	*/
	public function checkInputFeedBlock()
	{
		
		include_once("./Services/Utilities/classes/class.ilTypeCheck.php");
		$ilTypeCheck = new ilTypeCheck();
		
		$this->form_check["FeedBlock"] = array();
		$this->form_check["FeedBlock"]["Title"] =
			ilTypeCheck::check("varchar", $_POST["block_title"], true);
		$this->form_check["FeedBlock"]["FeedUrl"] =
			ilTypeCheck::check("varchar", $_POST["block_feed_url"], true);
		
		foreach($this->form_check["FeedBlock"] as $prop_check)
		{
			if (!$prop_check["ok"])
			{
				return false;
			}
		}
		return true;

	}

	/**
	* FORM FeedBlock: Prepare Saving of ExternalFeedBlock.
	*
	* @param	object	$a_external_feed_block	ExternalFeedBlock object.
	*/
	public function prepareSaveFeedBlock(&$a_external_feed_block)
	{

	}

	/**
	* FORM FeedBlock: Prepare form. (Can be overwritten in derived classes)
	*
	* @param	object	$a_form_gui	ilPropertyFormGUI instance.
	*/
	public function prepareFormFeedBlock(&$a_form_gui)
	{

	}


}
?>
