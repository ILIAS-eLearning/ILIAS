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
	* Add a select property.
	*
	* @param	string		Title
	* @param	string		_POST variable
	* @param	array		Options. Array of array ("value" => ..., "text" => ...)
	* @param	string		Current value.
	* @param	string		Info text.
	* @param	string		Alert text.
	* @param	boolean		Required field. (Default false)
	*/
	function addSelectProperty($a_title, $a_post_var, $a_options, $a_value = "", $a_info = "",
		$a_alert = "", $a_required = false)
	{
		$this->properties[] = array ("type" => "select",
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
	* Add a location property.
	*
	* @param	string		Title
	* @param	string		_POST variable
	* @param	string		Latitude.
	* @param	string		Longitude.
	* @param	string		Value.
	* @param	string		Info text.
	* @param	string		Alert text.
	* @param	boolean		Required field. (Default false)
	*/
	function addLocationProperty($a_title, $a_post_var, $a_latitude, $a_longitude,
		$a_info = "", $a_alert = "", $a_required = false)
	{
		$this->properties[] = array ("type" => "location",
			"title" => $a_title,
			"postvar" => $a_post_var,
			"latitude" => $a_latitude,
			"longitude" => $a_longitude,
			"info" => $a_info,
			"alert" => $a_alert,
			"required" => $a_required);
	}

	/**
	* Add a file property.
	*
	* @param	string		Title
	* @param	string		_POST variable
	* @param	string		Info text.
	* @param	string		Alert text.
	* @param	boolean		Required field. (Default false)
	*/
	function addFileProperty($a_title, $a_post_var, $a_info = "",
		$a_alert = "", $a_required = false)
	{
		$this->properties[] = array ("type" => "file",
			"title" => $a_title,
			"postvar" => $a_post_var,
			"info" => $a_info,
			"alert" => $a_alert,
			"required" => $a_required);
	}

	/**
	* Add a date/time property.
	*
	* @param	string		Title
	* @param	string		_POST variable
	* @param	string		Date (yyyy-mm-dd)
	* @param	boolean		Display date (true/false), default true
	* @param	string		Time (hh:mm:ss)
	* @param	boolean		Display time (true/false), default false
	* @param	boolean		Display seconds, default false
	* @param	string		Info text.
	* @param	string		Alert text.
	* @param	boolean		Required field. (Default false)
	*/
	function addDateTimeProperty($a_title, $a_post_var, $a_date_val, $a_date = true,
		$a_time_val = "00:00:00", $a_time = false, $a_seconds = false,
		$a_info = "", $a_alert = "", $a_required = false)
	{
		$this->properties[] = array ("type" => "datetime",
			"title" => $a_title,
			"postvar" => $a_post_var,
			"date_val" => $a_date_val,
			"date" => $a_date,
			"time_val" => $a_time_val,
			"time" => $a_time,
			"seconds" => $a_seconds,
			"info" => $a_info,
			"alert" => $a_alert,
			"required" => $a_required);
	}

	/**
	* Add a section header.
	*
	* @param	string		Title
	*/
	function addSectionHeader($a_title, $a_info = "")
	{
		$this->properties[] = array ("type" => "section_header",
			"title" => $a_title,
			"info" => $a_info);
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
		
		$gm_set = new ilSetting("google_maps");
		
		$this->tpl = new ilTemplate("tpl.property_form.html", true, true, "Services/Form");

		// title icon
		if ($this->getTitleIcon() != "" && @is_file($this->getTitleIcon()))
		{
			$this->tpl->setCurrentBlock("title_icon");
			$this->tpl->setVariable("IMG_ICON", $this->getTitleIcon());
			$this->tpl->parseCurrentBlock();
		}

		// title
		$this->tpl->setCurrentBlock("header");
		$this->tpl->setVariable("TXT_TITLE", $this->getTitle());
		$this->tpl->parseCurrentBlock();
		$this->tpl->touchBlock("item");
		
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
					
				case "select":
					foreach($property["options"] as $option)
					{
						$this->tpl->setCurrentBlock("prop_select_option");
						$this->tpl->setVariable("VAL_SELECT_OPTION", $option["value"]);
						if ($option["value"] == $property["value"])
						{
							$this->tpl->setVariable("CHK_SEL_OPTION",
								'selected="selected"');
						}
						$this->tpl->setVariable("TXT_SELECT_OPTION", $option["text"]);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("prop_select");
					$this->tpl->setVariable("POST_VAR", $property["postvar"]);
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

				case "location":
					$tpl->addJavaScript("http://maps.google.com/maps?file=api&amp;v=2&amp;key=".
						$gm_set->get("api_key"));
					$tpl->addJavaScript("Services/JavaScript/js/Basic.js");
					$tpl->addJavaScript("Services/GoogleMaps/js/ServiceGoogleMaps.js");
					$this->tpl->setCurrentBlock("prop_location");
					$this->tpl->setVariable("POST_VAR", $property["postvar"]);
					$this->tpl->setVariable("MAP_ID", "map_".$property["postvar"]);
					$this->tpl->setVariable("PROPERTY_VALUE_LAT", $property["latitude"]);
					$this->tpl->setVariable("PROPERTY_VALUE_LONG", $property["longitude"]);
					$this->tpl->parseCurrentBlock();
					break;

				case "file":
					$this->setMultipart(true);
					$this->tpl->setCurrentBlock("prop_file");
					$this->tpl->setVariable("POST_VAR", $property["postvar"]);
					$this->tpl->parseCurrentBlock();
					break;
					
				case "datetime":
					//$tpl->addJavaScript("Services/Calendar/js/calendar.js");
					//$tpl->addJavaScript("Services/Calendar/js/calendar-setup.js");
					//$tpl->addCss("Services/Calendar/css/calendar.css");
					$lng->loadLanguageModule("jscalendar");
					require_once("./Services/Calendar/classes/class.ilCalendarUtil.php");
					ilCalendarUtil::initJSCalendar();
					$this->tpl->setCurrentBlock("prop_file");
					if ($property["date"])
					{
						$this->tpl->setVariable("IMG_DATE_CALENDAR", ilUtil::getImagePath("calendar.png"));
						$this->tpl->setVariable("TXT_DATE_CALENDAR", $lng->txt("open_calendar"));
						$this->tpl->setVariable("DATE_ID", $property["postvar"]);
						$this->tpl->setVariable("INPUT_FIELDS_DATE", $property["postvar"]."_date");
						$date = explode("-", $property["date_val"]);
						$this->tpl->setVariable("DATE_SELECT",
							ilUtil::makeDateSelect($property["postvar"]."_date", $date[0], $date[1], $date[2]));
					}
					if ($property["time"])
					{
						$time = explode(":", $property["time_val"]);
						$this->tpl->setVariable("TIME_SELECT",
							ilUtil::makeTimeSelect($property["postvar"]."_time", !$property["seconds"],
							$time[0], $time[1], $time[2]));
						$this->tpl->setVariable("TXT_TIME", $property["seconds"]
							? "(".$lng->txt("hh_mm_ss").")"
							: "(".$lng->txt("hh_mm").")");
					}
					if ($property["time"] && $property["date"])
					{
						$this->tpl->setVariable("DELIM", "<br />");
					}
					break;

				case "custom":
					$this->tpl->setCurrentBlock("prop_custom");
					$this->tpl->setVariable("CUSTOM_CONTENT", $property["html"]);
					$this->tpl->parseCurrentBlock();
					break;

				case "section_header":
					$this->tpl->setCurrentBlock("header");
					$this->tpl->setVariable("TXT_TITLE", $property["title"]);
					$this->tpl->parseCurrentBlock();
					break;
			}
			
			if ($property["type"] != "section_header")
			{
				// info text
				if ($property["info"] != "")
				{
					$tpl->addJavaScript("Services/JavaScript/js/Basic.js");
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
			
			$this->tpl->touchBlock("item");
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
		
		return $this->tpl->get();
	}

}
