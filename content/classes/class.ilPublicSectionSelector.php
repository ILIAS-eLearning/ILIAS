<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

/*
* Public Section Explorer
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @package core
*/

require_once("content/classes/class.ilLMExplorer.php");
require_once("content/classes/class.ilLMObject.php");

class ilPublicSectionSelector extends ilLMExplorer
{
	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $output;
	var $ctrl;

	var $selectable_type;
	var $ref_id;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    object  lm object
	* @param	string	gui class name
	*/
	function ilPublicSectionSelector($a_target,&$a_lm_obj,$a_gui_class)
	{
		global $ilCtrl;

		$this->ctrl =& $ilCtrl;
		$this->gui_class = $a_gui_class;
		
		parent::ilLMExplorer($a_target, $a_lm_obj);
		$this->forceExpandAll(true);
		$this->setSessionExpandVariable("lmpublicselectorexpand");
	}

	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader($a_obj_id,$a_option)
	{
		global $lng, $ilias;

		$tpl = new ilTemplate("tpl.tree_form.html", true, true);

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", ilUtil::shortenText($this->lm_obj->getTitle(), $this->textwidth, true));
		$tpl->setVariable("LINK_TARGET", $this->target);
		$tpl->setVariable("TARGET", " target=\"".$this->frame_target."\"");
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}

	/**
	* Creates output
	* recursive method
	* @access	private
	* @param	integer
	* @param	array
	* @return	string
	*/
	function formatObject($a_node_id,$a_option,$a_obj_id = 0)
	{
		global $lng;
		
		if (!isset($a_node_id) or !is_array($a_option))
		{
			$this->ilias->raiseError(get_class($this)."::formatObject(): Missing parameter or wrong datatype! ".
									"node_id: ".$a_node_id." options:".var_dump($a_option),$this->ilias->error_obj->WARNING);
		}

		$tpl = new ilTemplate("tpl.tree_form.html", true, true);

		// build structurs without any icons or lines
		foreach ($a_option["tab"] as $picture)
		{
			$picture = "blank";
			$tpl->setCurrentBlock("lines");
			$tpl->setVariable("IMGPATH_LINES", ilUtil::getImagePath("browser/".$picture.".gif"));
			$tpl->parseCurrentBlock();
		}

		if ($this->output_icons)
		{
			$tpl->setCurrentBlock("icon");
			$tpl->setVariable("ICON_IMAGE" ,ilUtil::getImagePath("icon_".$a_option["type"].".gif"));
			$tpl->setVariable("PAGE_ID" , $a_node_id);
			
			//$this->iconList[] = "iconid_".$a_node_id;
			
			$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
			$tpl->parseCurrentBlock();
		}
		
		if (!$a_option["container"])
		{
			$tpl->setCurrentBlock("checkbox");
			$tpl->setVariable("PAGE_ID", $a_node_id);
			
			if (ilLMObject::_isPagePublic($a_node_id))
			{
				$tpl->setVariable("CHECKED","checked=\"checked\"");
			}
			
			$tpl->parseCurrentBlock();
		}
		else
		{
			$childs = $this->tree->getChilds($a_node_id);
			
			foreach ($childs as $node)
			{
				if ($node["type"] == "pg")
				{
					$pages[] = $node["child"];
				}
			}
			
			$js_pages = ilUtil::array_php2js($pages);
			$tpl->setVariable("ONCLICK", " onclick=\"alterCheckboxes('PublicSelector','page_',$js_pages); return false;\"");
		}

		$tpl->setCurrentBlock("text");
		$tpl->setVariable("PAGE_ID", $a_node_id);
		$tpl->setVariable("OBJ_TITLE", ilUtil::shortenText($a_option["title"], $this->textwidth, true));
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}
} // END class ilPublicSectionSelector
?>
