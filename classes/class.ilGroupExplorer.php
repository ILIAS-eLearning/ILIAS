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
require_once("classes/class.ilExplorer.php");
require_once("classes/class.ilObjectFactory.php");

class ilGroupExplorer extends ilExplorer
{
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int group ref_id
	*/
	function ilGroupExplorer($a_target, $a_ref_id)
	{
		parent::ilExplorer($a_target);
		
		$this->grp_id = $a_ref_id;
		$this->tree = new ilTree($a_ref_id,$a_ref_id);
		$this->tree->setTableNames("grp_tree","object_data","object_reference");
		$this->setSessionExpandVariable("grp_expand");				
		$this->setExpandTarget("group.php?cmd=view&ref_id=".$a_ref_id."&viewmode=tree");
		// temp. disabled for folders
		$this->rbac_check = false;
	}

	/**
	* Creates output for explorer view in admin menue
	* recursive method
	* @access	public
	* @param	integer		parent_node_id where to start from (default=0, 'root')
	* @param	integer		depth level where to start (default=1)
	* @return	string
	*/
	function setOutput($a_parent_id, $a_depth = 1)
	{
		global $rbacadmin, $rbacsystem;
		static $counter = 0;

		if (!isset($a_parent_id))
		{
			$this->ilias->raiseError(get_class($this)."::setOutput(): No node_id given!",$this->ilias->error_obj->WARNING);
		}

		$objects = $this->tree->getChilds($a_parent_id, $this->order_column);
		//echo $a_parent_id;var_dump($objects);
		if (count($objects) > 0)
		{
			$tab = ++$a_depth - 2;
			// Maybe call a lexical sort function for the child objects
			$objects = $this->sortNodes($objects);

			foreach ($objects as $key => $object)
			{
				//ask for FILTER
				if ($this->filtered == false or $this->checkFilter($object["type"]) == false)
//				if ($this->filtered == false || $this->checkFilter($object["type"])==true)
				{
					if ($rbacsystem->checkAccess("visible",$object["child"]) || (!$this->rbac_check))
					{//echo "child".$object["child"]."--root".$this->tree->getRootId();
						if ($object["child"] != $this->tree->getRootId())
						{
							$parent_index = $this->getIndex($object);
						}

						$this->format_options["$counter"]["parent"]		= $object["parent"];
						$this->format_options["$counter"]["child"]		= $object["child"];
						$this->format_options["$counter"]["title"]		= $object["title"];
						$this->format_options["$counter"]["type"]		= $object["type"];
						$this->format_options["$counter"]["desc"] 		= "obj_".$object["type"];
						$this->format_options["$counter"]["depth"]		= $tab;
						$this->format_options["$counter"]["container"]	= false;
						$this->format_options["$counter"]["visible"]	= true;

						// Create prefix array
						for ($i = 0; $i < $tab; ++$i)
						{
							 $this->format_options["$counter"]["tab"][] = 'blank';
						}

						// only if parent is expanded and visible, object is visible
						if ($object["child"] != $this->tree->getRootId() and (!in_array($object["parent"],$this->expanded)
						   or !$this->format_options["$parent_index"]["visible"]))
						{
							$this->format_options["$counter"]["visible"] = false;
						}

						// if object exists parent is container
						if ($object["child"] != $this->tree->getRootId())
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

						// stop recursion if 2. level beyond expanded nodes is reached 
						if (in_array($object["parent"],$this->expanded) or ($object["parent"] == 0))
						{
							// recursive
							$this->setOutput($object["child"],$a_depth);
						}
					} //if
				} //if FILTER
			} //foreach
		} //if
	} //function

	
	
	/**
	* Creates output
	* recursive method
	* @access	private
	* @param	integer
	* @param	array
	* @return	string
	*/
	function formatObject($a_node_id,$a_option)
	{
		global $lng;
		
		if (!isset($a_node_id) or !is_array($a_option))
		{
			$this->ilias->raiseError(get_class($this)."::formatObject(): Missing parameter or wrong datatype! ".
									"node_id: ".$a_node_id." options:".var_dump($a_option),$this->ilias->error_obj->WARNING);
		}

		$tpl = new ilTemplate("tpl.tree.html", true, true);

		foreach ($a_option["tab"] as $picture)
		{
			if ($picture == 'plus')
			{
				$target = $this->createTarget('+',$a_node_id);
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("LINK_TARGET", $target);
				$tpl->setVariable("IMGPATH", ilUtil::getImagePath("browser/plus.gif"));
				$tpl->parseCurrentBlock();
			}

			if ($picture == 'minus')
			{
				$target = $this->createTarget('-',$a_node_id);
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("LINK_TARGET", $target);
				$tpl->setVariable("IMGPATH", ilUtil::getImagePath("browser/minus.gif"));
				$tpl->parseCurrentBlock();
			}

			if ($picture == 'blank' or $picture == 'winkel'
			   or $picture == 'hoch' or $picture == 'quer' or $picture == 'ecke')
			{
				$tpl->setCurrentBlock("expander");
				$tpl->setVariable("IMGPATH", ilUtil::getImagePath("browser/".$picture.".gif"));
				$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
				$tpl->parseCurrentBlock();
			}
		}

		if ($this->output_icons)
		{
			$tpl->setCurrentBlock("icon");
			$tpl->setVariable("ICON_IMAGE" ,ilUtil::getImagePath("icon_".$a_option["type"].".gif"));
			$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
			$tpl->parseCurrentBlock();
		}

		if($this->isClickable($a_option["type"]))	// output link
		{
			$tpl->setCurrentBlock("link");
			/*$target = (strpos($this->target, "?") === false) ?
				$this->target."?" : $this->target."&";
			$tpl->setVariable("LINK_TARGET", $target.$this->target_get."=".$a_node_id.$this->params_get);
			*/
			
			$tpl->setVariable("LINK_TARGET", $this->getURLbyType($a_option));
			$tpl->setVariable("TITLE", ilUtil::shortenText($a_option["title"], $this->textwidth, true));
			
			$tpl->setVariable("TARGET", " target=\"bottom\"");
			
			/*if ($this->frame_target != "")
			{
				$tpl->setVariable("TARGET", " target=\"".$this->frame_target."\"");
			}*/
			$tpl->parseCurrentBlock();
		}
		else			// output text only
		{
			$tpl->setCurrentBlock("text");
			$tpl->setVariable("OBJ_TITLE", ilUtil::shortenText($a_option["title"], $this->textwidth, true));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}
	
	/*
	* function returns specific link-url depending on object-type
	*
	*
	* access public
	*/
	function getURLbyType($cont_data)
	{	
		global $ilias;
		
		switch ($cont_data["type"])
		{
	  		case "frm":
			
				$obj_frm = & $this->ilias->obj_factory->getInstanceByRefId($cont_data["child"]);
				
				require_once "classes/class.ilForum.php";
				
				$frm = new ilForum();
				$frm->setWhereCondition("top_frm_fk = ".$obj_frm->getId());
				$topicData = $frm->getOneTopic();		
			
				if ($topicData["top_num_threads"] > 0)
				{
					$thr_page = "liste";
				}
				else
				{
					$thr_page = "new";
				}
				
				$URL = "forums_threads_".$thr_page.".php?ref_id=".$cont_data["child"];
				break;
	
			case "crs":
				$URL = "lo_list.php?cmd=displayList&ref_id=".$cont_data["child"];
				break;
	
			case "lm":
				$URL = "content/lm_presentation.php?ref_id=".$cont_data["child"];
				break;
	
			case "fold":
				$URL = "group.php?cmd=view&ref_id=".$cont_data["child"]."&viewmode=flat&type=fold";
				break;

			case "file":
				$URL = "group.php?cmd=get_file&ref_id=".$cont_data["child"];
				break;
		}

		return $URL;
	}
}






?>
