<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* Editor Explorer for SCORM 2004 Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
require_once("./classes/class.ilExplorer.php");
class ilScorm2004EditorExplorer extends ilExplorer
{
	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $slm_obj;
	var $output;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilScorm2004EditorExplorer($a_target, $a_slm_obj)
	{
		parent::ilExplorer($a_target);
		$this->tree = new ilTree($a_slm_obj->getId());
		$this->tree->setTableNames('sahs_sc13_tree','sahs_sc13_tree_node');
		$this->tree->setTreeTablePK("slm_id");
		$this->root_id = $this->tree->readRootId();
		$this->slm_obj = $a_slm_obj;
		$this->order_column = "";
		$this->setSessionExpandVariable("scexpand");
		$this->checkPermissions(false);
		$this->setPostSort(false);
		$this->textwidth = 200;

		$this->force_open_path = array();
	}

	/**
	* set force open path
	*/
	function setForceOpenPath($a_path)
	{
		$this->force_open_path = $a_path;
	}

	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader(&$tpl, $a_obj_id,$a_option)
	{
		global $lng, $ilias, $ilCtrl;
		
		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_sahs_s.gif"));
		$tpl->setVariable("TXT_ALT_IMG", $this->slm_obj->getTitle());
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", ilUtil::shortenText($this->slm_obj->getTitle()."",
			$this->textwidth, true));
		$tpl->setVariable("LINK_TARGET",
			$ilCtrl->getLinkTargetByClass("ilobjscorm2004learningmodulegui", "showOrganization"));
		$tpl->setVariable("TARGET", " target=\"".$this->frame_target."\"");
		$tpl->parseCurrentBlock();
		
		$tpl->touchBlock("element");
	}

	/**
	* overwritten method from base class
	* @access	private
	* @param	integer
	* @param	array
	* @return	string
	*/
	function formatObject(&$tpl, $a_node_id,$a_option,$a_obj_id = 0)
	{
		global $lng;
		
		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , $this->getImage("icon_".$a_option["type"]."_s.gif", $a_option["type"], $a_obj_id));
		$tpl->setVariable("TARGET_ID" , "iconid_".$a_node_id);
		$this->iconList[] = "iconid_".$a_node_id;
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
		$tpl->parseCurrentBlock();
		
		$this->outputIcons(false);
		parent::formatObject(&$tpl, $a_node_id,$a_option,$a_obj_id);
	}
	
	/**
	* check if links for certain object type are activated
	*
	* @param	string		$a_type			object type
	*
	* @return	boolean		true if linking is activated
	*/
	function isClickable($a_type, $a_obj_id = 0)
	{
		global $ilUser;
		return true;
	}
	
	/**
	* build link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl;
		
		switch($a_type)
		{
			case "chap":
				$ilCtrl->setParameterByClass("ilScorm2004ChapterGUI", "obj_id", $a_node_id);
				return $ilCtrl->getLinkTargetByClass("ilScorm2004ChapterGUI", "showOrganization");
				break;
				
			case "seqc":
				$ilCtrl->setParameterByClass("ilScorm2004SeqChapterGUI", "obj_id", $a_node_id);
				return $ilCtrl->getLinkTargetByClass("ilScorm2004SeqChapterGUI", "showOrganization");
				break;

			case "page":
				$ilCtrl->setParameterByClass("ilScorm2004PageNodeGUI", "obj_id", $a_node_id);
				return $ilCtrl->getLinkTargetByClass("ilScorm2004PageNodeGUI", "edit");
				break;

			case "sco":
				$ilCtrl->setParameterByClass("ilScorm2004ScoGUI", "obj_id", $a_node_id);
				return $ilCtrl->getLinkTargetByClass("ilScorm2004ScoGUI", "showOrganization");
				break;
		}
	}

	/**
	* force expansion of node
	*/
	function forceExpanded($a_obj_id)
	{
		if (in_array($a_obj_id, $this->force_open_path))
		{
			return true;
		}
		return false;
	}

} // END class ilScorm2004EditorExplorer
?>
