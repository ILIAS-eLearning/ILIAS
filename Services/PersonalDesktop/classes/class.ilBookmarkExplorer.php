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
* Explorer View for Bookmarks
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Manfred Thaler <manfred.thaler@endo7.com>
* @version $Id$
*
*/

require_once("classes/class.ilExplorer.php");

class ilBookmarkExplorer extends ilExplorer
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
	function ilBookmarkExplorer($a_target,$a_user_id)
	{
		parent::ilExplorer($a_target);
		$this->tree = new ilTree($a_user_id);
		$this->tree->setTableNames('bookmark_tree','bookmark_data');
		$this->root_id = $this->tree->readRootId();
		$this->user_id = $a_user_id;
		$this->allowed_types= array ('bmf','dum');
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
	* Creates output
	* overwritten method from class Explorer
	* @access	public
	* @return	string
	*/
	function getOutput()
	{
		global $ilBench, $tpl;
		
		$this->format_options[0]["tab"] = array();

		$depth = $this->tree->getMaximumDepth();

		for ($i=0;$i<$depth;++$i)
		{
			$this->createLines($i);
		}
		
		$tpl_tree = new ilTemplate("tpl.tree_tooltip.html", true, true);

		$cur_depth = -1;
		foreach ($this->format_options as $key => $options)
		{
			if (!$options["visible"])
			{
				continue;
			}

			
			// end tags
			$this->handleListEndTags($tpl_tree, $cur_depth, $options["depth"]);
			
			// start tags
			$this->handleListStartTags($tpl_tree, $cur_depth, $options["depth"]);
			
			$cur_depth = $options["depth"];
			
			if ($options["visible"] and $key != 0)
			{
				$this->formatObject($tpl_tree, $options["child"],$options,$options['obj_id']);
			}
			if ($key == 0)
			{
				//$this->formatHeader($tpl_tree, $options["child"],$options);
			}
		}
		
		$this->handleListEndTags($tpl_tree, $cur_depth, -1);

		return $tpl_tree->get();
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
//			var_dump("<pre>",$objects,"</pre");
			$tab = ++$a_depth - 2;

			if($a_depth < 4)
			{
				for($i=0;$i<count($objects);++$i)
				{
					//$objects[$i]["title"] = $lng->txt("mail_".$objects[$i]["title"]);
					//$objects[$i]["title"] = "TEEST";
				}
			}

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
				// Store targets for Bookmarks
				if ($object["type"]=='bm') {
					$this->bm_targets[$object["child"]]=$object["target"];
				};
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
	* Overwritten method from class.Explorer.php to use Tooltips
	* recursive method
	* Creates output
	* recursive method
	* @access	private
	* @param	integer
	* @param	array
	* @return	string
	*/
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
				//$tpl->touchBlock("checkbox");
				//$tpl->parseCurrentBlock();

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

			if ($picture == 'minus')
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
			$small = ($this->getEnableSmallMode())
				? "_s"
				: "";
			$small = "_s";
			$tpl->setCurrentBlock("icon");
			$tpl->setVariable("ICON_IMAGE" , $this->getImage("icon_".$a_option["type"].$small.".gif", $a_option["type"], $a_obj_id));
			$tpl->setVariable("TARGET_ID" , "iconid_".$a_node_id);
			$this->iconList[] = "iconid_".$a_node_id;
			$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["type"]));
			$tpl->parseCurrentBlock();
		}

		if ($this->isClickable($a_option["type"], $a_node_id,$a_obj_id))	// output link
		{
			$tpl->setCurrentBlock("link");
			//$target = (strpos($this->target, "?") === false) ?
			//	$this->target."?" : $this->target."&";
			//$tpl->setVariable("LINK_TARGET", $target.$this->target_get."=".$a_node_id.$this->params_get);
			$tpl->setVariable("LINK_TARGET", $this->buildLinkTarget($a_node_id, $a_option["type"]));
			if (($onclick = $this->buildOnClick($a_node_id, $a_option["type"], $a_option["title"])) != "")
			{
				$tpl->setVariable("ONCLICK", "onClick=\"$onclick\"");
			}
			if (($tooltip = $this->buildToolTip($a_node_id, $a_option["type"],$a_option["description"])) != "")
			{
				$tpl->setVariable("TOOLTIP", 'title="'.ilUtil::prepareFormOutput($tooltip).'"');
			}
			$tpl->setVariable("LINK_NAME", $a_node_id);
			$tpl->setVariable("TITLE", ilUtil::prepareFormOutput(ilUtil::shortenText(
				$this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]),
				$this->textwidth, true)));
			$tpl->setVariable("DESC",
				$this->buildDescription($a_option["description"], $a_node_id, $a_option["type"]));
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
			$tpl->setVariable("OBJ_TITLE",ilUtil::prepareFormOutput(ilUtil::shortenText(
				$this->buildTitle($a_option["title"], $a_node_id, $a_option["type"]), $this->textwidth, true)));
			$tpl->setVariable("OBJ_DESC",
				$this->buildDescription($a_option["description"], $a_node_id, $a_option["type"]));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("list_item");
		if ($this->getEnableSmallMode())
		{
			$tpl->setVariable("DIVCLASS", ' class="small" ');
		}
		$tpl->parseCurrentBlock();
		$tpl->touchBlock("element");
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

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TYPE", $a_option["type"]);
		$tpl->setVariable("TITLE", $lng->txt("bookmarks_of")." ".$ilias->account->getFullname());
		$sep = (is_int(strpos($this->target, "?")))
			? "&"
			: "?";
		$tpl->setVariable("LINK_TARGET", $this->target.$sep.$this->target_get."=1");
		$tpl->setVariable("TARGET", " target=\"content\"");
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("element");
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
		// IF ISN'T SET CREATE SESSION VARIABLE
		if(!is_array($_SESSION["mexpand"]))
		{
			$_SESSION["mexpand"] = array();
		}
		// IF $_GET["expand"] is positive => expand this node
		if($a_node_id > 0 && !in_array($a_node_id,$_SESSION["mexpand"]))
		{
			array_push($_SESSION["mexpand"],$a_node_id);
		}
		// IF $_GET["expand"] is negative => compress this node
		if($a_node_id < 0)
		{
			$key = array_keys($_SESSION["mexpand"],-(int) $a_node_id);
			unset($_SESSION["mexpand"][$key[0]]);
		}
		$this->expanded = $_SESSION["mexpand"];
	}
	/**
	* overwritten method from base class
	* get link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		switch ($a_type) {
			case 'bm':
				// return stored Bookmark target;
				return $this->bm_targets[$a_node_id];
				break;
			default:
				$target = (strpos($this->target, "?") === false)
					? $this->target."?"
					: $this->target."&";
				return $target.$this->target_get."=".$a_node_id.$this->params_get;
		}
	}
	/**
	* overwritten method from base class
	* buid link target
	*/
	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		switch ($a_type) {
			case 'bm':
				// return _blank for Bookmarks;
				return '_blank';
				break;
			default:
				return '';
		}
	}

	/**
	* buid tooltip
	*/
	function buildToolTip($a_node_id, $a_type, $a_desc)
	{
		if ($this->show_details!='y' && !empty($a_desc))
		{
			return $a_desc;
		}
		else
		{
			return "";
		}
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
			return '<br />'.ilUtil::prepareFormOutput($a_desc);

		}
		else
		{
			return "";
		}

	}
}
?>
