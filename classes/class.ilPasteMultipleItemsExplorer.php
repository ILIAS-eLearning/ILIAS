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

require_once('./classes/class.ilRepositoryExplorer.php');

/*
* ilPasteMultipleItemsExplorer Explorer
*
* @author Michael Jansen <mjansen@databay.de>
*
*/
class ilPasteMultipleItemsExplorer extends ilRepositoryExplorer
{
	public $root_id;
	public $output;
	public $ctrl;
	
	private $checked_items = array();
	private $checkbox_post_var = 'nodes[]';
	private $checkbox_types = array();
	
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	*/
	public function __construct($a_target)
	{
		global $tree, $ilCtrl;

		$this->ctrl = $ilCtrl;

		parent::ilRepositoryExplorer($a_target);
		$this->tree = $tree;
		$this->root_id = $this->tree->readRootId();
		$this->order_column = 'title';
		$this->setSessionExpandVariable('repexpand');

		$this->setFiltered(true);
		$this->setFilterMode(IL_FM_POSITIVE);
	}
	
	public function isClickable($a_type, $a_ref_id, $a_obj_id = 0)
	{
		return false;
	}	
	
	public function addCheckboxForType($type)
	{
		$this->checkbox_types[$type] = true;
	}
	public function rempveCheckboxForType($type)
	{
		$this->checkbox_types[$type] = false;
	}
	public function setCheckedItems(Array $a_checked_items = array())
	{
		$this->checked_items = $a_checked_items;
	}	
	public function isItemChecked($a_id)
	{
		return in_array($a_id, $this->checked_items) ? true : false;
	}
	public function setCheckboxPostVar($a_post_var)
	{
		$this->checkbox_post_var = $a_post_var;
	}
	public function getCheckboxPostVar()
	{
		return $this->checkbox_post_var;
	}
	
	public function buildCheckbox($a_node_id, $a_type)
	{
		if(!array_key_exists($a_type, $this->checkbox_types) || !$this->checkbox_types[$a_type]) return '';
				
		return ilUtil::formCheckbox((int)$this->isItemChecked($a_node_id), $this->checkbox_post_var, $a_node_id);
	}
	
	function formatObject(&$tpl, $a_node_id,$a_option,$a_obj_id = 0)
	{
		global $lng;
		if (!isset($a_node_id) or !is_array($a_option))
		{
			$this->ilias->raiseError(get_class($this)."::formatObject(): Missing parameter or wrong datatype! ".
									"node_id: ".$a_node_id." options:".var_dump($a_option),$this->ilias->error_obj->WARNING);
		}

		$pic = false;
		foreach ($a_option["tab"] as $picture)
		{
			if ($picture == 'plus')
			{
				$tpl->setCurrentBlock("exp_desc");
				$tpl->setVariable("EXP_DESC", $lng->txt("expand"));
				$tpl->parseCurrentBlock();
				$target = $this->createTarget('+',$a_node_id);
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("LINK_NAME", $a_node_id);
				$tpl->setVariable("LINK_TARGET_EXPANDER", $target);
				$tpl->setVariable("IMGPATH", $this->getImage("browser/plus.gif"));
				$tpl->parseCurrentBlock();
				$pic = true;
			}

			if ($picture == 'minus' && $this->show_minus)
			{
				$tpl->setCurrentBlock("exp_desc");
				$tpl->setVariable("EXP_DESC", $lng->txt("collapse"));
				$tpl->parseCurrentBlock();
				$target = $this->createTarget('-',$a_node_id);
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("LINK_NAME", $a_node_id);
				$tpl->setVariable("LINK_TARGET_EXPANDER", $target);
				$tpl->setVariable("IMGPATH", $this->getImage("browser/minus.gif"));
				$tpl->parseCurrentBlock();
				$pic = true;
			}

			/*
			if ($picture == 'blank' or $picture == 'winkel'
			   or $picture == 'hoch' or $picture == 'quer' or $picture == 'ecke')
			{
				$picture = "blank";
				$tpl->setCurrentBlock("lines");
				$tpl->setVariable("IMGPATH_LINES", $this->getImage("browser/".$picture.".gif"));
				$tpl->parseCurrentBlock();
			}
			*/
		}
		
		if (!$pic)
		{
			$tpl->setCurrentBlock("blank");
			$tpl->setVariable("BLANK_PATH", $this->getImage("browser/blank.gif"));
			$tpl->parseCurrentBlock();
		}

		if ($this->output_icons)
		{
			$tpl->setCurrentBlock("icon");
			$tpl->setVariable("ICON_IMAGE" , $this->getImage("icon_".$a_option["type"].".gif", $a_option["type"], $a_obj_id));
			
			$tpl->setVariable("TARGET_ID" , "iconid_".$a_node_id);
			$this->iconList[] = "iconid_".$a_node_id;
			$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
			$tpl->parseCurrentBlock();
		}
		
		if(strlen($sel = $this->buildSelect($a_node_id,$a_option['type'])))
		{
			$tpl->setCurrentBlock('select');
			$tpl->setVariable('OBJ_SEL',$sel);
			$tpl->parseCurrentBlock();
		}
		
		if(strlen($check = $this->buildCheckbox($a_node_id, $a_option['type'])))
		{
			$tpl->setCurrentBlock('check');
			$tpl->setVariable('OBJ_CHECK', $check);
			$tpl->parseCurrentBlock();
		}

		if ($this->isClickable($a_option["type"], $a_node_id,$a_obj_id))	// output link
		{
			$tpl->setCurrentBlock("link");
			//$target = (strpos($this->target, "?") === false) ?
			//	$this->target."?" : $this->target."&";
			//$tpl->setVariable("LINK_TARGET", $target.$this->target_get."=".$a_node_id.$this->params_get);
			$tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($a_node_id, $a_option["type"]));
				
			$style_class = $this->getNodeStyleClass($a_node_id, $a_option["type"]);
			
			if ($style_class != "")
			{
				$tpl->setVariable("A_CLASS", ' class="'.$style_class.'" ' );
			}

			if (($onclick = $this->buildOnClick($a_node_id, $a_option["type"], $a_option["title"])) != "")
			{
				$tpl->setVariable("ONCLICK", "onClick=\"$onclick\"");
			}

			$tpl->setVariable("LINK_NAME", $a_node_id);
			$tpl->setVariable("TITLE", ilUtil::shortenText(
				$this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]),
				$this->textwidth, true));
			$tpl->setVariable("DESC", ilUtil::shortenText(
				$this->buildDescription($a_option["description"], $a_node_id, $a_option["type"]), $this->textwidth, true));
			$frame_target = $this->buildFrameTarget($a_option["type"], $a_node_id, $a_option["obj_id"]);
			if ($frame_target != "")
			{
				$tpl->setVariable("TARGET", " target=\"".$frame_target."\"");
			}
			$tpl->parseCurrentBlock();
		}
		else			// output text only
		{
			$tpl->setCurrentBlock("text");
			$tpl->setVariable("OBJ_TITLE", ilUtil::shortenText(
				$this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]), $this->textwidth, true));
			$tpl->setVariable("OBJ_DESC", ilUtil::shortenText(
				$this->buildDescription($a_option["desc"], $a_node_id, $a_option["type"]), $this->textwidth, true));			
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("list_item");
		$tpl->parseCurrentBlock();
		$tpl->touchBlock("element");
	}
	
	/*
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader(&$tpl, $a_obj_id,$a_option)
	{
		global $lng, $ilias, $tree;

		// custom icons
		$path = ilObject::_getIcon($a_obj_id, "small", "root");
		

		$tpl->setCurrentBlock("icon");
		$nd = $tree->getNodeData(ROOT_FOLDER_ID);
		$title = $nd["title"];
		if ($title == "ILIAS")
		{
			$title = $lng->txt("repository");
		}

		$tpl->setVariable("ICON_IMAGE", $path);
		$tpl->setVariable("TXT_ALT_IMG", $title);
		$tpl->parseCurrentBlock();
		
		$tpl->setVariable('OBJ_TITLE', $title);
	}
}
?>