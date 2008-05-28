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

include_once("./Services/Form/classes/class.ilFormGUI.php");

// we currently include all property types (autoload may prevent this in the future)
include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");
include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
include_once("./Services/Form/classes/class.ilCustomInputGUI.php");
include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");
include_once("./Services/Form/classes/class.ilFileInputGUI.php");
include_once("./Services/Form/classes/class.ilImageFileInputGUI.php");
include_once("./Services/Form/classes/class.ilLocationInputGUI.php");
include_once("./Services/Form/classes/class.ilRadioGroupInputGUI.php");
include_once("./Services/Form/classes/class.ilFormSectionHeaderGUI.php");
include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
include_once("./Services/Form/classes/class.ilTextAreaInputGUI.php");
include_once("./Services/Form/classes/class.ilTextInputGUI.php");
include_once("./Services/Form/classes/class.ilDurationInputGUI.php");
include_once("./Services/Form/classes/class.ilFeedUrlInputGUI.php");
include_once("./Services/Form/classes/class.ilNonEditableValueGUI.php");
include_once("./Services/Form/classes/class.ilRegExpInputGUI.php");
include_once('./Services/Form/classes/class.ilColorPickerInputGUI.php');

/**
* This class represents a property form user interface
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
* @ingroup	ServicesForm
*/
class ilPropertyFormGUI extends ilFormGUI
{
	private $buttons = array();
	private $items = array();
	protected $mode = "std";
	protected $check_input_called = false;
	protected $subformmode = "bottom";
	
	/**
	* Constructor
	*
	* @param
	*/
	function ilPropertyFormGUI()
	{
		global $lng;
		
		$lng->loadLanguageModule("form");
		parent::ilFormGUI();
	}

	/**
	 * Set table width
	 *
	 * @access public
	 * @param string table width
	 * 
	 */
	final public function setTableWidth($a_width)
	{
	 	$this->tbl_width = $a_width;
	}
	
	/**
	 * get table width
	 *
	 * @access public
	 * 
	 */
	final public function getTableWidth()
	{
	 	return $this->tbl_width;
	}

	/**
	* Set Mode ('std', 'subform').
	*
	* @param	string	$a_mode	Mode ('std', 'subform')
	*/
	function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	/**
	* Get Mode ('std', 'subform').
	*
	* @return	string	Mode ('std', 'subform')
	*/
	function getMode()
	{
		return $this->mode;
	}

	/**
	* Set Title.
	*
	* @param	string	$a_title	Title
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	* Get Title.
	*
	* @return	string	Title
	*/
	function getTitle()
	{
		return $this->title;
	}

	/**
	* Set Subform Mode. ("bottom" | "right")
	*
	* @param	string	$a_subformmode	Subform Mode
	*/
	function setSubformMode($a_subformmode)
	{
		$this->subformmode = $a_subformmode;
	}

	/**
	* Get Subform Mode. ("bottom" | "right")
	*
	* @return	string	Subform Mode
	*/
	function getSubformMode()
	{
		return $this->subformmode;
	}

	/**
	* Set TitleIcon.
	*
	* @param	string	$a_titleicon	TitleIcon
	*/
	function setTitleIcon($a_titleicon)
	{
		$this->titleicon = $a_titleicon;
	}

	/**
	* Get TitleIcon.
	*
	* @return	string	TitleIcon
	*/
	function getTitleIcon()
	{
		return $this->titleicon;
	}

	/**
	* Add Item (Property, SectionHeader).
	*
	* @param	object	$a_property		Item object
	*/
	function addItem($a_item)
	{
		return $this->items[] = $a_item;
	}

	/**
	* Remove Item.
	*
	* @param	string	$a_postvar		Post Var
	*/
	function removeItemByPostVar($a_post_var)
	{
		foreach ($this->items as $key => $item)
		{
			if ($item->getPostVar() == $a_post_var)
			{
				unset($this->items[$key]);
			}
		}
	}

	/**
	* Get Item by POST variable.
	*
	* @param	string	$a_postvar		Post Var
	*/
	function getItemByPostVar($a_post_var)
	{
		foreach ($this->items as $key => $item)
		{
			if ($item->getPostVar() == $a_post_var)
			{
				return $this->items[$key];
			}
		}
		
		return false;
	}

	/**
	* Set Items
	*
	* @param	array	$a_items	array of item objects
	*/
	function setItems($a_items)
	{
		$this->items = $a_items;
	}

	/**
	* Get Items
	*
	* @return	array	array of item objects
	*/
	function getItems()
	{
		return $this->items;
	}

	/**
	* Set form values from an array
	*
	* @param	array	$a_values	Value array (key is post variable name, value is value)
	*/
	function setValuesByArray($a_values)
	{
		foreach($this->items as $item)
		{
			$item->setValueByArray($a_values);
		}
	}

	/**
	* Set form values from POST values
	*
	*/
	function setValuesByPost()
	{
	    foreach($this->items as $item)
		{
			$item->setValueByArray($_POST);
		}
	}
	
	/**
	* Check Post Input. This method also strips slashes and html from
	* input and sets the alert texts for the items, if the input was not ok.
	*
	* @return	boolean		ok true/false
	*/
	function checkInput()
	{
		if ($this->check_input_called)
		{
			die ("Error: ilPropertyFormGUI->checkInput() called twice.");
		}
		
		$ok = true;
		foreach($this->items as $item)
		{
			$item_ok = $item->checkInput();
			if(!$item_ok)
			{
				$ok = false;
			}
		}
		
		$this->check_input_called = true;
		
		return $ok;
	}

	function getInput($a_post_var)
	{
		// this check ensures, that checkInput has been called (incl. stripSlashes())
		if (!$this->check_input_called)
		{
			die ("Error: ilPropertyFormGUI->getInput() called without calling checkInput() first.");
		}
		
		return $_POST[$a_post_var];
	}
	
	/**
	* Add a custom property.
	*
	* @param	string		Title
	* @param	string		HTML.
	* @param	string		Info text.
	* @param	string		Alert text.
	* @param	boolean		Required field. (Default false)
	*/
	function addCustomProperty($a_title, $a_html, $a_info = "",
		$a_alert = "", $a_required = false)
	{
		$this->properties[] = array ("type" => "custom",
			"title" => $a_title,
			"html" => $a_html,
			"info" => $a_info);
	}

	/**
	* Add Command button
	*
	* @param	string	Command
	* @param	string	Text
	*/
	function addCommandButton($a_cmd, $a_text)
	{
		$this->buttons[] = array("cmd" => $a_cmd, "text" => $a_text);
	}

	/**
	* Get Content.
	*/
	function getContent()
	{
		global $lng, $tpl;
		
		$this->tpl = new ilTemplate("tpl.property_form.html", true, true, "Services/Form");

		// title icon
		if ($this->getTitleIcon() != "" && @is_file($this->getTitleIcon()))
		{
			$this->tpl->setCurrentBlock("title_icon");
			$this->tpl->setVariable("IMG_ICON", $this->getTitleIcon());
			$this->tpl->parseCurrentBlock();
		}

		// title
		if ($this->getTitle() != "")
		{
			$this->tpl->setCurrentBlock("header");
			$this->tpl->setVariable("TXT_TITLE", $this->getTitle());
			if ($this->getSubformMode() == "right")
			{
				$this->tpl->setVariable("HEAD_COLSPAN", "3");
			}
			else
			{
				$this->tpl->setVariable("HEAD_COLSPAN", "2");
			}		
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->touchBlock("item");
		
		// properties
		$this->required_text = false;
		foreach($this->items as $item)
		{
			$this->insertItem($item);
		}

		// required
		if ($this->required_text)
		{
			$this->tpl->setCurrentBlock("required_text");
			$this->tpl->setVariable("TXT_REQUIRED", $lng->txt("required_field"));
			$this->tpl->parseCurrentBlock();			
		}
		
		// command buttons
		foreach($this->buttons as $button)
		{
			$this->tpl->setCurrentBlock("cmd");
			$this->tpl->setVariable("CMD", $button["cmd"]);
			$this->tpl->setVariable("CMD_TXT", $button["text"]);
			$this->tpl->parseCurrentBlock();
		}
		
		if ($required_text || count($this->buttons) > 0)
		{
			$this->tpl->setCurrentBlock("commands");
			$this->tpl->parseCurrentBlock();
		}
		
		if ($this->getMode() == "subform")
		{
			$this->tpl->touchBlock("sub_table");
		}
		else
		{
			$this->tpl->touchBlock("std_table");
			$this->tpl->setVariable('STD_TABLE_WIDTH',$this->getTableWidth());
		}
		
		return $this->tpl->get();
	}

	function insertItem($item, $a_sub_item = false)
	{
		global $tpl, $lng;
		
		$item->insert($this->tpl);
		if ($item->getType() == "file" || $item->getType() == "image_file")
		{
			$this->setMultipart(true);
		}
		
		if ($item->getType() != "section_header")
		{
			// info text
			if ($item->getInfo() != "")
			{
				$tpl->addJavaScript("Services/JavaScript/js/Basic.js");
				$tpl->addJavaScript("Services/Form/js/ServiceForm.js");
				$this->tpl->setCurrentBlock("description");
				//$this->tpl->setVariable("IMG_INFO",
				//	ilUtil::getImagePath("icon_info_s.gif"));
				//$this->tpl->setVariable("ALT_INFO",
				//	$lng->txt("info_short"));
				$this->tpl->setVariable("PROPERTY_DESCRIPTION",
					$item->getInfo());
				$this->tpl->parseCurrentBlock();
			}

			if ($this->getMode() == "subform")
			{
				// required
				if ($item->getType() != "non_editable_value")
				{
					if ($item->getRequired())
					{
						$this->tpl->touchBlock("sub_required");
						$this->required_text = true;
					}
				}
				$this->tpl->setCurrentBlock("sub_prop_start");
				$this->tpl->setVariable("PROPERTY_TITLE", $item->getTitle());
				if ($item->getType() != "non_editable_value")
				{
					$this->tpl->setVariable("LAB_ID", $item->getFieldId());
				}
				if ($this->getSubformMode() != "right")
				{
					$this->tpl->setVariable("PS_STYLE", "padding-left:40px; vertical-align:top;");
				}
				else
				{
					$this->tpl->setVariable("PS_STYLE", "vertical-align:top;");
				}
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				// required
				if ($item->getType() != "non_editable_value")
				{
					if ($item->getRequired())
					{
						$this->tpl->touchBlock("required");
						$this->required_text = true;
					}
				}
				$this->tpl->setCurrentBlock("std_prop_start");
				$this->tpl->setVariable("PROPERTY_TITLE", $item->getTitle());
				if ($item->getType() != "non_editable_value")
				{
					$this->tpl->setVariable("LAB_ID", $item->getFieldId());
				}
				$this->tpl->parseCurrentBlock();
			}
			
			// alert
			if ($item->getType() != "non_editable_value" && $item->getAlert() != "")
			{
				$this->tpl->setCurrentBlock("alert");
				$this->tpl->setVariable("IMG_ALERT",
					ilUtil::getImagePath("icon_alert_s.gif"));
				$this->tpl->setVariable("ALT_ALERT",
					$lng->txt("alert"));
				$this->tpl->setVariable("TXT_ALERT",
					$item->getAlert());
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("prop");
			
			// subitems
			$sf = "";
			if ($item->getType() != "non_editable_value")
			{
				$sf = $item->getSubForm();
			}

			if ($this->getSubformMode() == "right")
			{
				if ($sf != "")
				{
					$this->tpl->setVariable("PROP_SUB_FORM",
						'</td><td class="option_value">'.$sf);
				}
				else
				{
					if ($this->getMode() != "subform")
					{
						$this->tpl->setVariable("PROP_SUB_FORM",
							'</td><td class="option_value">&nbsp;');
					}
				}
			}
			else
			{
				$this->tpl->setVariable("PROP_SUB_FORM", $item->getSubForm());
			}

			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->touchBlock("item");
	}
}
