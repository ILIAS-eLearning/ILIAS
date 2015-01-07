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
* Explorer View for Workspace Folders
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilBookmarkExplorer.php 23354 2010-03-24 13:25:09Z akill $
*
*/

include_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");
include_once("Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php");

class ilWorkspaceFolderExplorer extends ilExplorer
{
	/**
	 * user_id
	 * @var int uid
	 * @access private
	 */
	var $user_id;

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;

	/**
	 * allowed object types
	 * @var array object types
	 * @access private
	 */
	var $allowed_types;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function __construct($a_target,$a_user_id)
	{
		parent::__construct($a_target);
		$this->tree = new ilWorkspaceTree($a_user_id);
		$this->root_id = $this->tree->readRootId();
		$this->user_id = $a_user_id;
		$this->allowed_types= array ('wfld', 'wsrt');
		$this->enablesmallmode = false;
	}

		/**
	* Set Enable Small Mode.
	*
	* @param	boolean	$a_enablesmallmode	Enable Small Mode
	*/
	function setEnableSmallMode($a_enablesmallmode)
	{
		$this->enablesmallmode = $a_enablesmallmode;
	}

	/**
	* Get Enable Small Mode.
	*
	* @return	boolean	Enable Small Mode
	*/
	function getEnableSmallMode()
	{
		return $this->enablesmallmode;
	}


	/**
	* Overwritten method from class.Explorer.php to avoid checkAccess selects
	* recursive method
	* @access	public
	* @param	integer		parent_node_id where to start from (default=0, 'root')
	* @param	integer		depth level where to start (default=1)
	* @return	string
	*/
	function setOutput($a_parent, $a_depth = 1)
	{
		global $lng;
		static $counter = 0;

		if ($objects =  $this->tree->getChilds($a_parent,"type DESC,title"))
		{
			$tab = ++$a_depth - 2;

			foreach ($objects as $key => $object)
			{
				if (!in_array($object["type"],$this->allowed_types))
				{
					continue;
				}

				//ask for FILTER
				if ($object["child"] != $this->root_id)
				{
					//$data = $this->tree->getParentNodeData($object["child"]);
					$parent_index = $this->getIndex($object);
				}
				
				$this->format_options["$counter"]["parent"] = $object["parent"];
				$this->format_options["$counter"]["child"] = $object["child"];
				$this->format_options["$counter"]["title"] = $object["title"];
				$this->format_options["$counter"]["description"] = $object["description"];
				$this->format_options["$counter"]["type"] = $object["type"];
				$this->format_options["$counter"]["depth"] = $tab;
				$this->format_options["$counter"]["container"] = false;
				$this->format_options["$counter"]["visible"]	  = true;

				// Create prefix array
				for ($i = 0; $i < $tab; ++$i)
				{
					$this->format_options["$counter"]["tab"][] = 'blank';
				}
				// only if parent is expanded and visible, object is visible
				if ($object["child"] != $this->root_id  and (!in_array($object["parent"],$this->expanded)
														  or !$this->format_options["$parent_index"]["visible"]))
				{
					$this->format_options["$counter"]["visible"] = false;
				}

				// if object exists parent is container
				if ($object["child"] != $this->root_id)
				{
					$this->format_options["$parent_index"]["container"] = true;

					if (in_array($object["parent"],$this->expanded))
					{
						$this->format_options["$parent_index"]["tab"][($tab-2)] = 'minus';
					}
					else
					{
						$this->format_options["$parent_index"]["tab"][($tab-2)] = 'plus';
					}
				}

				++$counter;

				// Recursive
				$this->setOutput($object["child"],$a_depth);
			} //foreach
		} //if
	} //function

	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader($tpl, $a_obj_id, $a_option)
	{
		global $lng, $ilCtrl;
	
		$title = $lng->txt("wsp_personal_workspace");
		
		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE", ilUtil::getImagePath("icon_wsrt.svg"));
		$tpl->setVariable("TXT_ALT_IMG", $title);
		$tpl->parseCurrentBlock();				
		
		$tpl->setCurrentBlock("link");
		$tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($this->root_id, "wsrt"));
		$tpl->setVariable("TITLE", $title);
		
		// highlighting 
		$style_class = $this->getNodeStyleClass($this->root_id, "wsrt");			
		if ($style_class != "")
		{
			$tpl->setVariable("A_CLASS", ' class="'.$style_class.'" ' );
		}
		
		$tpl->parseCurrentBlock();		
	}

	/**
	* set the expand option
	* this value is stored in a SESSION variable to save it different view (lo view, frm view,...)
	* @access	private
	* @param	string		pipe-separated integer
	*/
	function setExpand($a_node_id)
	{
		if ($a_node_id == "")
		{
			$a_node_id = $this->root_id;
		}

		// IF ISN'T SET CREATE SESSION VARIABLE
		if(!is_array($_SESSION[$this->expand_variable]))
		{
			$_SESSION[$this->expand_variable] = array();
		}
		// IF $_GET["expand"] is positive => expand this node
		if($a_node_id > 0 && !in_array($a_node_id,$_SESSION[$this->expand_variable]))
		{
			array_push($_SESSION[$this->expand_variable],$a_node_id);
		}
		// IF $_GET["expand"] is negative => compress this node
		if($a_node_id < 0)
		{
			$key = array_keys($_SESSION[$this->expand_variable],-(int) $a_node_id);
			unset($_SESSION[$this->expand_variable][$key[0]]);
		}
		$this->expanded = $_SESSION[$this->expand_variable];
	}
	/**
	* overwritten method from base class
	* get link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl;
		
		switch ($a_type) 
		{
			case "wsrt":
				$ilCtrl->setParameterByClass("ilobjworkspacerootfoldergui", "wsp_id", $a_node_id);
				return $ilCtrl->getLinkTargetByClass("ilobjworkspacerootfoldergui", "");
				
			case "wfld":
				$ilCtrl->setParameterByClass("ilobjworkspacefoldergui", "wsp_id", $a_node_id);
				return $ilCtrl->getLinkTargetByClass("ilobjworkspacefoldergui", "");
			
			default:
				return;
		}
	}
	/**
	* overwritten method from base class
	* buid link target
	*/
	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		return '';
	}

	/**
	* set the alowed object types
	* @access	private
	* @param	array		arraye of object types
	*/
	function setAllowedTypes($a_types)
	{
		$this->allowed_types = $a_types;
	}
	/**
	* set details mode
	* @access	public
	* @param	string		y or n
	*/
	function setShowDetails($s_details)
	{
		$this->show_details = $s_details;
	}

	/**
	* overwritten method from base class
	* buid decription
	*/
	function buildDescription($a_desc, $a_id, $a_type)
	{
		if ($this->show_details=='y' && !empty($a_desc))
		{
			return $a_desc;

		}
		else
		{
			return "";
		}
	}
	
	function getImageAlt($a_def, $a_type, $a_obj_id)
	{
		global $lng;
		
		return $lng->txt("icon")." ".$lng->txt($a_type);
	}

	function hasFolders($a_node_id)
	{
		return sizeof($this->tree->getChildsByType($a_node_id, "wfld"));
	}

	function getParentNode($a_node_id)
	{
		return $this->tree->getParentId($a_node_id);
	}
	
	function getOutput()
	{
		global $tpl;
		
		$html = parent::getOutput();	
		$tpl->setBodyClass("std");
		return $html;
	}
}

?>