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


/**
* Class ilInfoScreenGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias-core
*/
class ilInfoScreenGUI
{
	var $ilias;
	var $lng;
	var $ctrl;
	var $gui_object;
	var $top_buttons = array();

	/**
	* Constructor
	*
	* @param	object	$a_gui_object	GUI instance of related object
	* 									(ilCouseGUI, ilTestGUI, ...)
	*/
	function ilInfoScreenGUI($a_gui_object)
	{
		global $ilias, $ilCtrl, $lng;

		$this->ilias =& $ilias;
		$this->ctrl =& $ilCtrl;
		$this->gui_object =& $a_gui_object;
		$this->sec_nr = 0;
		$this->private_notes_enabled = false;
	}
	
	/**
	* enable notes
	*/
	function enablePrivateNotes($a_enable = true)
	{
		$this->private_notes_enabled = $a_enable;
	}
	
	/**
	* add a new section
	*/
	function addSection($a_title)
	{
		$this->sec_nr++;
		$this->section[$this->sec_nr]["title"] = $a_title;
	}
	
	/**
	* add a property to current section
	*/
	function addProperty($a_name, $a_value)
	{
		$this->section[$this->sec_nr]["properties"][] =
			array("name" => $a_name, "value" => $a_value);
	}

	/**
	* add a property to current section
	*/
	function addButton($a_title, $a_link, $a_frame = "", $a_position = "top")
	{
		if ($a_position == "top")
		{
			$this->top_buttons[] =
				array("title" => $a_title,"link" => $a_link,"target" => $a_frame);
		}
	}
	
	/**
	* add standard meta data sections
	*/
	function addMetaDataSections($a_rep_obj_id,$a_obj_id, $a_type)
	{
		global $lng;
		
		$lng->loadLanguageModule("meta");

		include_once("./Services/MetaData/classes/class.ilMD.php");
		$md = new ilMD($a_rep_obj_id,$a_obj_id, $a_type);
		
		if ($md_gen = $md->getGeneral())
		{
			// get first descrption
			foreach($md_gen->getDescriptionIds() as $id)
			{
				$md_des = $md_gen->getDescription($id);
				$description = $md_des->getDescription();
				break;
			}
			
			// get language(s)
			$langs = array();
			foreach($ids = $md_gen->getLanguageIds() as $id)
			{
				$md_lan = $md_gen->getLanguage($id);
				if ($md_lan->getLanguageCode() != "")
				{
					$langs[] = $lng->txt("meta_l_".$md_lan->getLanguageCode());
				}
			}
			$langs = implode($langs, ", ");
			
			// keywords
			$keywords = array();
			foreach($ids = $md_gen->getKeywordIds() as $id)
			{
				$md_key = $md_gen->getKeyword($id);
				$keywords[] = $md_key->getKeyword();
			}
			$keywords = implode($keywords, ", ");
		}
		
		// authors
		if(is_object($lifecycle = $md->getLifecycle()))
		{
			$sep = $author = "";
			foreach(($ids = $lifecycle->getContributeIds()) as $con_id)
			{
				$md_con = $lifecycle->getContribute($con_id);
				if ($md_con->getRole() == "Author")
				{
					foreach($ent_ids = $md_con->getEntityIds() as $ent_id)
					{
						$md_ent = $md_con->getEntity($ent_id);
						$author = $author.$sep.$md_ent->getEntity();
						$sep = ", ";
					}
				}
			}
		}
			
		// copyright
		$copyright = "";
		if(is_object($rights = $md->getRights()))
		{
			$copyright = $rights->getDescription();
		}

		// learning time
		$learning_time = "";
		if(is_object($educational = $md->getEducational()))
		{
			$learning_time = $educational->getTypicalLearningTime();
		}

		// output
		
		// description
		if ($description != "")
		{
			$this->addSection($lng->txt("description"));
			$this->addProperty("",  nl2br($description));
		}
		
		// general section
		$this->addSection($lng->txt("meta_general"));
		if ($langs != "")	// language
		{
			$this->addProperty($lng->txt("language"),
				$langs);
		}
		if ($keywords != "")	// keywords
		{
			$this->addProperty($lng->txt("keywords"),
				$keywords);
		}
		if ($author != "")		// author
		{
			$this->addProperty($lng->txt("author"),
				$author);
		}
		if ($copyright != "")		// copyright
		{
			$this->addProperty($lng->txt("meta_copyright"),
				$copyright);
		}
		if ($learning_time != "")		// typical learning time
		{
			$this->addProperty($lng->txt("meta_typical_learning_time"),
				$learning_time);
		}
	}
	
	/**
	* get html
	*/
	function getHTML()
	{
		$tpl = new ilTemplate("tpl.infoscreen.html" ,true, true);
		
		// add top buttons
		if (count($this->top_buttons) > 0)
		{
			$tpl->addBlockfile("TOP_BUTTONS", "top_buttons", "tpl.buttons.html");

			foreach($this->top_buttons as $button)
			{
				// view button
				$tpl->setCurrentBlock("btn_cell");
				$tpl->setVariable("BTN_LINK", $button["link"]);
				$tpl->setVariable("BTN_TARGET", $button["target"]);
				$tpl->setVariable("BTN_TXT", $button["title"]);
				$tpl->parseCurrentBlock();
			}
		}
		
		for($i = 1; $i <= $this->sec_nr; $i++)
		{
			if (is_array($this->section[$i]["properties"]))
			{
				// section header
				$tpl->setCurrentBlock("header_row");
				$tpl->setVariable("TXT_SECTION",
					$this->section[$i]["title"]);
				$tpl->parseCurrentBlock();
				$tpl->touchBlock("row");
				
				// section properties
				foreach($this->section[$i]["properties"] as $property)
				{
					if ($property["name"] != "")
					{
						$tpl->setCurrentBlock("property_row");
						$tpl->setVariable("TXT_PROPERTY", $property["name"]);
						$tpl->setVariable("TXT_PROPERTY_VALUE", $property["value"]);
						$tpl->parseCurrentBlock();
						$tpl->touchBlock("row");
					}
					else
					{
						$tpl->setCurrentBlock("property_full_row");
						$tpl->setVariable("TXT_PROPERTY_FULL_VALUE", $property["value"]);
						$tpl->parseCurrentBlock();
						$tpl->touchBlock("row");
					}
				}
			}
		}
		
		// notes section
		if ($this->private_notes_enabled)
		{
			$html = $this->showNotesSection();
			$tpl->setCurrentBlock("notes");
			$tpl->setVariable("NOTES", $html);
			$tpl->parseCurrentBlock();
		}
		
		return $tpl->get();
	}
	
	
	/**
	* show notes section
	*/
	function showNotesSection()
	{
		$next_class = $this->ctrl->getNextClass($this);

		include_once("Services/Notes/classes/class.ilNoteGUI.php");
		$notes_gui = new ilNoteGUI($this->gui_object->object->getId(), 0,
			$this->gui_object->object->getType());
		
		$notes_gui->enablePrivateNotes();
		//$notes_gui->enablePublicNotes();

		if ($next_class == "ilnotegui")
		{
			$html = $this->ctrl->forwardCommand($notes_gui);
		}
		else
		{	
			$html = $notes_gui->getNotesHTML();
		}
		
		return $html;
	}

}

?>
