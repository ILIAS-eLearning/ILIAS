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

/**
* This class represents a property form user interface
*
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
*/
class ilPropertyFormGUI extends ilFormGUI
{
	private $buttons = array();
	
	/**
	* Constructor
	*
	* @param
	*/
	function ilPropertyFormGUI()
	{
		parent::ilFormGUI();
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
	* Add a text property.
	*
	* @param	string		Title
	* @param	string		_POST variable
	* @param	string		Current value.
	* @param	string		Info text.
	* @param	string		Alert text.
	* @param	boolean		Required field. (Default false)
	* @param	int			Maximum Length. (Defaul 200)
	* @param	int			Size. (Default 40)
	*/
	function addTextProperty($a_title, $a_post_var, $a_value = "", $a_info = "",
		$a_alert = "", $a_required = false, $a_maxlength = "200", $a_size = "40")
	{
		$this->properties[] = array ("type" => "text",
			"title" => $a_title,
			"postvar" => $a_post_var,
			"value" => $a_value,
			"info" => $a_info,
			"alert" => $a_alert,
			"required" => $a_required,
			"maxlength" => $a_maxlength,
			"size" => $a_size);
	}
	
	/**
	* Add a textarea property.
	*
	* @param	string		Title
	* @param	string		_POST variable
	* @param	string		Current value.
	* @param	string		Info text.
	* @param	string		Alert text.
	* @param	boolean		Required field. (Default false)
	* @param	int			Number of columns. (Default 40)
	* @param	int			Number of rows. (Default 4)
	* @param	boolean		Use rich text editing (default false)
	*/
	function addTextAreaProperty($a_title, $a_post_var, $a_value = "", $a_info = "",
		$a_alert = "", $a_required = false, $a_cols = "40", $a_rows = "4",
		$a_use_rt = false)
	{
		$this->properties[] = array ("type" => "textarea",
			"title" => $a_title,
			"postvar" => $a_post_var,
			"value" => $a_value,
			"info" => $a_info,
			"alert" => $a_alert,
			"required" => $a_required,
			"cols" => $a_cols,
			"rows" => $a_rows,
			"use_rt" => $a_use_rt);
	}

	/**
	* Add a radio property.
	*
	* @param	string		Title
	* @param	string		_POST variable
	* @param	array		Options. Array of array ("value" => ..., "text" => ...)
	* @param	string		Current value.
	* @param	string		Info text.
	* @param	string		Alert text.
	* @param	boolean		Required field. (Default false)
	*/
	function addRadioProperty($a_title, $a_post_var, $a_options, $a_value = "", $a_info = "",
		$a_alert = "", $a_required = false)
	{
		$this->properties[] = array ("type" => "radio",
			"title" => $a_title,
			"postvar" => $a_post_var,
			"options" => $a_options,
			"value" => $a_value,
			"info" => $a_info,
			"alert" => $a_alert,
			"required" => $a_required);
	}

	/**
	* Add a checkbox property.
	*
	* @param	string		Title
	* @param	string		_POST variable
	* @param	string		Value.
	* @param	boolean		Checked
	* @param	string		Info text.
	* @param	string		Alert text.
	* @param	boolean		Required field. (Default false)
	*/
	function addCheckboxProperty($a_title, $a_post_var, $a_value, $a_checked = false, $a_info = "",
		$a_alert = "", $a_required = false)
	{
		$this->properties[] = array ("type" => "checkbox",
			"title" => $a_title,
			"postvar" => $a_post_var,
			"checked" => $a_checked,
			"value" => $a_value,
			"info" => $a_info,
			"alert" => $a_alert,
			"required" => $a_required);
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
		
		// properties
		$required_text = false;
		foreach($this->properties as $property)
		{
			switch($property["type"])
			{
				case "text":
					$this->tpl->setCurrentBlock("prop_text");
					$this->tpl->setVariable("POST_VAR", $property["postvar"]);
					$this->tpl->setVariable("PROPERTY_VALUE",
						ilUtil::prepareFormOutput($property["value"]));
					$this->tpl->setVariable("SIZE", $property["size"]);
					$this->tpl->setVariable("MAXLENGTH", $property["maxlength"]);
					$this->tpl->parseCurrentBlock();
					break;
					
				case "textarea":
					if ($property["use_rt"])
					{
						include_once "./Services/RTE/classes/class.ilRTE.php";
						$rtestring = ilRTE::_getRTEClassname();
						include_once "./Services/RTE/classes/class.$rtestring.php";
						$rte = new $rtestring();
						
						// @todo: Check this.
						$rte->addCustomRTESupport(0, "", array("strong", "em", "u", "ol", "li", "ul", "a"));
						
						$this->tpl->touchBlock("prop_ta_w");
						$this->tpl->setCurrentBlock("prop_textarea");
						$this->tpl->setVariable("ROWS", $property["rows"]);
					}
					else
					{
						$this->tpl->touchBlock("no_rteditor");
						$this->tpl->setCurrentBlock("prop_ta_c");
						$this->tpl->setVariable("COLS", $property["cols"]);
						$this->tpl->parseCurrentBlock();
						
						$this->tpl->setCurrentBlock("prop_textarea");
						$this->tpl->setVariable("ROWS", $property["rows"]);
					}
					$this->tpl->setVariable("POST_VAR",
						ilUtil::prepareFormOutput($property["postvar"]));
					$this->tpl->setVariable("PROPERTY_VALUE", $property["value"]);
					$this->tpl->parseCurrentBlock();
					break;
					
				case "radio":
					$br = "";
					foreach($property["options"] as $option)
					{
						$this->tpl->setCurrentBlock("prop_radio_option");
						$this->tpl->setVariable("POST_VAR", $property["postvar"]);
						$this->tpl->setVariable("VAL_RADIO_OPTION", $option["value"]);
						if ($option["value"] == $property["value"])
						{
							$this->tpl->setVariable("CHK_RADIO_OPTION",
								'checked="checked"');
						}
						$this->tpl->setVariable("TXT_RADIO_OPTION", $option["text"]);
						$this->tpl->setVariable("BR", $br);
						$this->tpl->parseCurrentBlock();
						$br = "<br />";
					}
					$this->tpl->setCurrentBlock("prop_radio");
					$this->tpl->parseCurrentBlock();
					break;
					
				case "checkbox":
					$this->tpl->setCurrentBlock("prop_checkbox");
					$this->tpl->setVariable("POST_VAR", $property["postvar"]);
					$this->tpl->setVariable("PROPERTY_VALUE", $property["value"]);
					if ($property["checked"])
					{
						$this->tpl->setVariable("PROPERTY_CHECKED",
							'checked="checked"');
					}
					$this->tpl->parseCurrentBlock();
					break;

				case "custom":
					$this->tpl->setCurrentBlock("prop_custom");
					$this->tpl->setVariable("CUSTOM_CONTENT", $property["html"]);
					$this->tpl->parseCurrentBlock();
					break;
			}
			
			// info text
			if ($property["info"] != "")
			{
				$tpl->addJavaScript("Services/Form/js/ServiceForm.js");
				$this->tpl->setCurrentBlock("description");
				//$this->tpl->setVariable("IMG_INFO",
				//	ilUtil::getImagePath("icon_info_s.gif"));
				//$this->tpl->setVariable("ALT_INFO",
				//	$lng->txt("info_short"));
				$this->tpl->setVariable("PROPERTY_DESCRIPTION",
					$property["info"]);
				$this->tpl->parseCurrentBlock();
			}

			// required
			if ($property["required"])
			{
				$this->tpl->touchBlock("required");
				$required_text = true;
			}
			
			// alert
			if ($property["alert"] != "")
			{
				$this->tpl->setCurrentBlock("alert");
				$this->tpl->setVariable("IMG_ALERT",
					ilUtil::getImagePath("icon_alert_s.gif"));
				$this->tpl->setVariable("ALT_ALERT",
					$lng->txt("alert"));
				$this->tpl->setVariable("TXT_ALERT",
					$property["alert"]);
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("prop");
			$this->tpl->setVariable("PROPERTY_TITLE", $property["title"]);
			$this->tpl->parseCurrentBlock();
		}

		// command buttons
		if ($required_text)
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

		// title
		$this->tpl->setVariable("TXT_TITLE", $this->getTitle());
		
		return $this->tpl->get();
	}

}
