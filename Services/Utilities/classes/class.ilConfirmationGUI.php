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

require_once("Services/Table/classes/class.ilTableGUI.php");

/**
* Confirmation screen class.
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
*
* @ingroup ServicesUtilities
*/
class ilConfirmationGUI
{
	private $hidden_item = array();
	private $item = array();
	private $use_images = false;
	private $buttons = array();
	
	/**
	* Constructor
	*
	*/
	public function __construct()
	{
	}

	final public function setFormAction($a_form_action)
	{
		$this->form_action = $a_form_action;
	}
	
	final public function getFormAction()
	{
		return $this->form_action;
	}

	/**
	* Set Set header text.
	*
	* @param	string	$a_headertext	Set header text
	*/
	function setHeaderText($a_headertext)
	{
		$this->headertext = $a_headertext;
	}

	/**
	* Get Set header text.
	*
	* @return	string	Set header text
	*/
	function getHeaderText()
	{
		return $this->headertext;
	}

	/**
	* Set cancel button command and text
	*
	* @param	string		cancel text
	* @param	string		cancel command
	*/
	final public function addButton($a_txt, $a_cmd)
	{
		$this->buttons[] = array(
			"txt" => $a_txt, "cmd" => $a_cmd);
	}

	/**
	* Set cancel button command and text
	*
	* @param	string		cancel text
	* @param	string		cancel command
	*/
	final public function setCancel($a_txt, $a_cmd)
	{
		$this->cancel_txt = $a_txt;
		$this->cancel_cmd = $a_cmd;
	}

	/**
	* Set confirmation button command and text
	*
	* @param	string		confirmation button text
	* @param	string		confirmation button command
	*/
	final public function setConfirm($a_txt, $a_cmd)
	{
		$this->confirm_txt = $a_txt;
		$this->confirm_cmd = $a_cmd;
	}

	/**
	* Add row item.
	*
	* @param	string	name of post variable used for id (e.g. "id[]")
	* @param	mixed	id value
	* @param	string	item text
	* @param	string	item image path
	*/
	public function addItem($a_post_var, $a_id, $a_text, $a_img = "",
		$a_alt = "")
	{
		$this->item[] = array("var" => $a_post_var, "id" => $a_id,
			"text" => $a_text, "img" => $a_img, "alt" => $a_alt);
		if ($a_img != "")
		{
			$this->use_images = true;
		}
	}
	
	/**
	* Add hidden item.
	*
	* @param	string	name of post variable used for id (e.g. "id[]")
	* @param	mixed	value
	*/
	public function addHiddenItem($a_post_var, $a_value)
	{
		$this->hidden_item[] = array("var" => $a_post_var, "value" => $a_value);
	}

	/**
	* Get confirmation screen HTML.
	*
	* @return	string		HTML code.
	*/
	final public function getHTML()
	{
		global $lng;
		
		ilUtil::sendQuestion($this->getHeaderText());
		
		include_once("./Services/Utilities/classes/class.ilConfirmationTableGUI.php");
		$ctab = new ilConfirmationTableGUI($this->use_images);
		$ctab->setData($this->item);

		// other buttons
		foreach ($this->buttons as $b)
		{
			$ctab->addCommandButton($b["cmd"], $b["txt"]);
		}
		$ctab->addCommandButton($this->confirm_cmd, $this->confirm_txt);
		$ctab->addCommandButton($this->cancel_cmd, $this->cancel_txt);
		$ctab->setFormAction($this->getFormAction());
		foreach ($this->hidden_item as $hidden_item)
		{
			$ctab->addHiddenInput($hidden_item["var"], $hidden_item["value"]);
		}
		
		return $ctab->getHTML();
	}
}
?>
