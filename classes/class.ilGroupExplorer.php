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
	* @param    int user_id
	*/
	function ilGroupExplorer($a_target)
	{
		parent::ilExplorer($a_target);
		
	}
	/**
	* Creates output
	* recursive method
	* @access	public
	* @return	string
	*/
	function getOutput()
	{
		$this->format_options[0]["tab"] = array();
		$obj_factory =& new ilObjectFactory();
		
		$depth = $this->tree->getMaximumDepth();

		for ($i=0;$i<$depth;++$i)
		{
			$this->createLines($i);
		}

		foreach ($this->format_options as $key => $options)
		{
			if ($options["visible"] and $key != 0)
			{
				$node = $obj_factory->getInstanceByRefId($options["child"]); 
				$this->formatObject($options["child"],$options,$node->getType());
			}
		}

		return implode('',$this->output);
	}
	
	/**
	* Creates output
	* recursive method
	* overwritten method from class Explorer
	* @access	private
	* @param	integer
	* @param	array
	* @return	string
	*/
	function formatObject($a_node_id,$a_option,$a_type)
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

		$tpl->setCurrentBlock("row");
		$tpl->setVariable("ICON_IMAGE" ,ilUtil::getImagePath("icon_".$a_option["type"].".gif"));
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
		$target = (strpos($this->target, "?") === false) ?
			$this->target."?" : $this->target."&";
		
		if (!strcmp($a_type,"grp"))
		{
			$tpl->setVariable("LINK_TARGET", "group.php?cmd=show_content&grp_id=".$a_node_id);
		}
		else
		{
			$tpl->setVariable("LINK_TARGET", $target.$this->target_get."=".$a_node_id);
		}
		
		$tpl->setVariable("TITLE", $a_option["title"]);

		if ($this->frameTarget != "")
		{
			$tpl->setVariable("TARGET", " target=\"".$this->frameTarget."\"");
		}

		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}
	
	/**
	* Creates Get Parameter
	* @access	private
	* @param	string
	* @param	integer
	* @return	string
	*/
	function createTarget($a_type,$a_node_id)
	{
		if (!isset($a_type) or !is_string($a_type) or !isset($a_node_id))
		{
			$this->ilias->raiseError(get_class($this)."::createTarget(): Missing parameter or wrong datatype! ".
									"type: ".$a_type." node_id:".$a_node_id,$this->ilias->error_obj->WARNING);
		}

		// SET expand parameter:
		//     positive if object is expanded
		//     negative if object is compressed
		$a_node_id = $a_type == '+' ? $a_node_id : -(int) $a_node_id;

		$sep = (is_int(strpos($this->expand_target, "?")))
			? "&"
			: "?";
		return $this->expand_target.$sep."cmd=explorer&expand=".$a_node_id;
	}

}






?>
