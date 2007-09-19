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

	final public function setCancel($a_txt, $a_cmd)
	{
		$this->cancel_txt = $a_txt;
		$this->cancel_cmd = $a_cmd;
	}

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
	public function addItem($a_post_var, $a_id, $a_text, $a_img = "")
	{
		$this->item[] = array("var" => $a_post_var, "id" => $a_id,
			"text" => $a_text, "img" => $a_img);
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
		
		$tpl = new ilTemplate("tpl.confirmation.html", true, true, "Services/Utilities");

		// cancel/confirm buttons
		$tpl->setCurrentBlock("cmd");
		$tpl->setVariable("TXT_CMD", $this->confirm_txt);
		$tpl->setVariable("CMD", $this->confirm_cmd);
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("cmd");
		$tpl->setVariable("TXT_CMD", $this->cancel_txt);
		$tpl->setVariable("CMD", $this->cancel_cmd);
		$tpl->parseCurrentBlock();
		
		// output items
		foreach ($this->item as $item)
		{
			if ($this->use_images)
			{
				if ($item["img"] != "")
				{
					$tpl->setCurrentBlock("img_cell");
					$tpl->setVariable("IMG_ITEM", $item["img"]);
					$tpl->parseCurrentBlock();
				}
				else
				{
					$tpl->touchBlock("blank_cell");
				}
			}
			$tpl->setCurrentBlock("item_row");
			$this->fillRowColor($tpl);
			$tpl->setVariable("TXT_ITEM", $item["text"]);
			$tpl->setVariable("VAR_ITEM", $item["var"]);
			$tpl->setVariable("ID", $item["id"]);
			$tpl->parseCurrentBlock();
		}
		
		foreach ($this->hidden_item as $hidden_item)
		{
			$tpl->setCurrentBlock("hidden_item_row");
			$tpl->setVariable("VAR_HIDDEN_VAR", $hidden_item["var"]);
			$tpl->setVariable("VAR_HIDDEN_VALUE", $hidden_item["value"]);
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable("FORMACTION", $this->getFormAction());
		$tpl->setVariable("TXT_HEADER", $this->getHeaderText());
		
		if ($this->use_images)
		{
			$tpl->setVariable("ROW_SPAN", 2);
		}
		else
		{
			$tpl->setVariable("ROW_SPAN", 1);
		}
	
		return $tpl->get();
	}

	final protected function fillRowColor(&$a_tpl, $a_placeholder = "CSS_ROW")
	{
		$this->css_row = ($this->css_row != "tblrow1")
			? "tblrow1"
			: "tblrow2";
		$a_tpl->setVariable($a_placeholder, $this->css_row);
	}

}
?>
