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
abstract class ilExternalFeedBlockGUIGen extends ilBlockGUI
{

	protected $gui_object;
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
		if (isset($_GET["external_feed_block_id"]) && $_GET["external_feed_block_id"] > 0)
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
	* Set GuiObject.
	*
	* @param	object	$a_gui_object	GUI object
	*/
	public function setGuiObject(&$a_gui_object)
	{
		$this->gui_object = $a_gui_object;
	}

	/**
	* Get GuiObject.
	*
	* @return	object	GUI object
	*/
	public function getGuiObject()
	{
		return $this->gui_object;
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
	* FORM FeedBlock: Create ExternalFeedBlock.
	*
	*/
	public function createFeedBlock()
	{
		$this->initFormFeedBlock(IL_FORM_CREATE);
		return $this->form_gui->getHtml();

	}

	/**
	* FORM FeedBlock: Edit form.
	*
	*/
	public function editFeedBlock()
	{
		$this->initFormFeedBlock(IL_FORM_EDIT);
		$this->getValuesFeedBlock();
		return $this->form_gui->getHtml();

	}

	/**
	* FORM FeedBlock: Save ExternalFeedBlock.
	*
	*/
	public function saveFeedBlock()
	{
		$this->initFormFeedBlock(IL_FORM_CREATE);
		if ($this->form_gui->checkInput())
		{
			$this->external_feed_block = new ilExternalFeedBlock();
			$this->external_feed_block->setTitle($this->form_gui->getInput("block_title"));
			$this->external_feed_block->setFeedUrl($this->form_gui->getInput("block_feed_url"));
			$this->prepareSaveFeedBlock($this->external_feed_block);
			$this->external_feed_block->create();
			$this->exitSaveFeedBlock();
		}
		else
		{
			$this->form_gui->setValuesByPost();
			return $this->form_gui->getHtml();
		}

	}

	/**
	* FORM FeedBlock: Update ExternalFeedBlock.
	*
	*/
	public function updateFeedBlock()
	{
		$this->initFormFeedBlock(IL_FORM_EDIT);
		if ($this->form_gui->checkInput())
		{
			
			$this->external_feed_block->setTitle($this->form_gui->getInput("block_title"));
			$this->external_feed_block->setFeedUrl($this->form_gui->getInput("block_feed_url"));
			$this->external_feed_block->update();
			$this->exitUpdateFeedBlock();
		}
		else
		{
			$this->form_gui->setValuesByPost();
			return $this->form_gui->getHtml();
		}

	}

	/**
	* FORM FeedBlock: Init form.
	*
	* @param	int	$a_mode	Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE)
	*/
	public function initFormFeedBlock($a_mode)
	{
		global $lng;
		
		$lng->loadLanguageModule("block");
		
		include("Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$this->form_gui = new ilPropertyFormGUI();
		
		
		// Property Title
		$text_input = new ilTextInputGUI($lng->txt("block_feed_block_title"), "block_title");
		$text_input->setInfo("");
		$text_input->setRequired(true);
		$text_input->setMaxLength(200);
		$this->form_gui->addItem($text_input);
		
		// Property FeedUrl
		$text_input = new ilTextInputGUI($lng->txt("block_feed_block_feed_url"), "block_feed_url");
		$text_input->setInfo($lng->txt("block_feed_block_feed_url_info"));
		$text_input->setRequired(true);
		$text_input->setMaxLength(250);
		$this->form_gui->addItem($text_input);
		
		
		// save and cancel commands
		if (in_array($a_mode, array(IL_FORM_CREATE,IL_FORM_RE_CREATE)))
		{
			$this->form_gui->addCommandButton("saveFeedBlock", $lng->txt("save"));
			$this->form_gui->addCommandButton("cancelSaveFeedBlock", $lng->txt("cancel"));
		}
		else
		{
			$this->form_gui->addCommandButton("updateFeedBlock", $lng->txt("save"));
			$this->form_gui->addCommandButton("cancelUpdateFeedBlock", $lng->txt("cancel"));
		}
		
		$this->form_gui->setTitle($lng->txt("block_feed_block_head"));
		$this->form_gui->setFormAction($this->ctrl->getFormAction($this));
		
		$this->prepareFormFeedBlock($this->form_gui);

	}

	/**
	* FORM FeedBlock: Get current values for ExternalFeedBlock form.
	*
	*/
	public function getValuesFeedBlock()
	{
		$values = array();
		
		$values["block_title"] = $this->external_feed_block->getTitle();
		$values["block_feed_url"] = $this->external_feed_block->getFeedUrl();
		
		$this->form_gui->setValuesByArray($values);

	}

	/**
	* FORM FeedBlock: Cancel save. (Can be overwritten in derived classes)
	*
	*/
	public function cancelSaveFeedBlock()
	{
		global $ilCtrl;

		$ilCtrl->returnToParent($this);
	}

	/**
	* FORM FeedBlock: Cancel update. (Can be overwritten in derived classes)
	*
	*/
	public function cancelUpdateFeedBlock()
	{
		global $ilCtrl;

		$ilCtrl->returnToParent($this);
	}

	/**
	* FORM FeedBlock: Exit save. (Can be overwritten in derived classes)
	*
	*/
	public function exitSaveFeedBlock()
	{
		global $ilCtrl;

		$ilCtrl->returnToParent($this);
	}

	/**
	* FORM FeedBlock: Exit update. (Can be overwritten in derived classes)
	*
	*/
	public function exitUpdateFeedBlock()
	{
		global $ilCtrl;

		$ilCtrl->returnToParent($this);
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
