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

require_once("./content/classes/class.ilLearningModule.php");
require_once("./classes/class.ilMainMenuGUI.php");

/**
* Class ilLMPresentationGUI
*
* GUI class for learning module presentation
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilLMPresentationGUI
{
	var $ilias;
	var $lm;
	var $tpl;

	function ilLMPresentationGUI()
	{
		global $ilias;

		$this->ilias =& $ilias;

		$cmd = (!empty($_GET["cmd"])) ? $_GET["cmd"] : "layout";

		// Todo: check lm id

		$this->lm =& new ilLearningModule($_GET["lm_id"]);

		$this->$cmd();
	}

	function attrib2arr(&$a_attributes)
	{
		$attr = array();
		if(!is_array($a_attributes))
		{
			return $attr;
		}
		foreach ($a_attributes as $attribute)
		{
			$attr[$attribute->name()] = $attribute->value();
		}
		return $attr;
	}

	/**
	* generates frame layout
	*/
	function layout()
	{
		$layout = $this->lm->getLayout();

		$doc = xmldocfile("./layouts/lm/".$layout."/main.xml");
		$xpc = xpath_new_context($doc);
		$path = (empty($_GET["frame"]))
			? "/ilFrame[1]"
			: "//ilFrame[@name='".$_GET["frame"]."']";
		$result = xpath_eval($xpc, $path);
		$found = $result->nodeset;
		if (count($found) != 1) { echo "ilLMPresentation: XML File invalid"; exit; }
		$node = $found[0];

		// node is frameset, if it has cols or rows attribute
		$attributes = $this->attrib2arr($node->attributes());
		if((!empty($attributes["rows"])) || (!empty($attributes["cols"])))
		{
			$content .= $this->buildTag("start", "frameset", $attributes);
			$this->processNodes($content, $node);
			$content .= $this->buildTag("end", "frameset");
			$this->tpl = new ilTemplate("tpl.frameset.html", true, true, true);
			$this->tpl->setVariable("FS_CONTENT", $content);
//echo nl2br(htmlentities($content));
		}
		else	// node is frame -> process the content tags
		{
			if (empty($attributes["template"]))
			{ echo "ilLMPresentation: No template specified for ilFrame"; exit; }

			// get template
			$in_module = ($attributes["template_location"] == "module")
				? true
				: false;
			$this->tpl = new ilTemplate($attributes["template"], true, true, $in_module);
			$childs = $node->child_nodes();
			foreach($childs as $child)
			{
				$child_attr = $this->attrib2arr($child->attributes());
				switch ($child->node_name())
				{
					case "ilMainMenu":
						$this->ilMainMenu();
						break;

					case "ilTOC":
						$this->ilTOC($child_attr["target_frame"]);
						break;
				}
			}
		}
		$this->tpl->show();
	}

	/**
	* output main menu
	*/
	function ilMainMenu()
	{
		$menu = new ilMainMenuGUI("_top", true);
		$menu->setTemplate($this->tpl);
		$menu->setTemplateVars();
	}

	/**
	* output table of content
	*/
	function ilTOC($a_target)
	{
		require_once("./content/classes/class.ilLMTOCExplorer.php");
		$exp = new ilLMTOCExplorer("lm_presentation.php?cmd=layout&frame=$a_target&lm_id=".$this->lm->getId(),$this->lm);
		$exp->setTargetGet("obj_id");
		$exp->setFrameTarget($a_target);
		$exp->addFilter("du");
		$exp->addFilter("st");
		$exp->setFiltered(true);

		if ($_GET["mexpand"] == "")
		{
			$mtree = new ilTree($this->lm->getId());
			$mtree->setTableNames('lm_tree','lm_data');
			$mtree->setTreeTablePK("lm_id");
			$expanded = $mtree->readRootId();
		}
		else
		{
			$expanded = $_GET["mexpand"];
		}
		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->setVariable("ACTION", "lm_presentation.php?cmd=".$_GET["cmd"]."&frame=".$_GET["frame"].
			"&lm_id=".$this->lm->getId()."&mexpand=".$_GET["mexpand"]);
		$this->tpl->parseCurrentBlock();
	}

	function processNodes(&$a_content, &$a_node)
	{
		$child_nodes = $a_node->child_nodes();
		foreach ($child_nodes as $child)
		{
			if($child->node_name() == "ilFrame")
			{
				$attributes = $this->attrib2arr($child->attributes());
				// node is frameset, if it has cols or rows attribute
				if ((!empty($attributes["rows"])) || (!empty($attrubtes["cols"])))
				{
					// if framset has name, another http request is necessary
					// (html framesets don't have names, so we need a wrapper frame)
					if(!empty($attributes["name"]))
					{
						$a_content .= "<frame name=\"".$attributes["name"]."\" ".
							"src=\"lm_presentation.php?lm_id=".$this->lm->getId()."&cmd=layout&frame=".$attributes["name"]."\" />\n";
					}
					else	// ok, no name means that we can easily output the frameset tag
					{
						$a_content .= $this->buildTag("start", "frameset", $attributes);
						$this->processNodes($a_content, $child);
						$a_content .= $this->buildTag("end", "frameset");
					}
				}
				else	// frame with
				{
					$a_content .= "<frame name=\"".$attributes["name"]."\" ".
						"src=\"lm_presentation.php?lm_id=".$this->lm->getId()."&cmd=layout&frame=".$attributes["name"]."\" />\n";
				}
			}
		}
	}

	/**
	* generate a tag with given name and attributes
	*
	* @param	string		"start" | "end" for starting or ending tag
	* @param	string		element/tag name
	* @param	array		array of attributes
	*/
	function buildTag ($type, $name, $attr="")
	{
		$tag = "<";

		if ($type == "end")
			$tag.= "/";

		$tag.= $name;

		if (is_array($attr))
		{
			while (list($k,$v) = each($attr))
				$tag.= " ".$k."=\"$v\"";
		}

		$tag.= ">\n";

		return $tag;
	}

}
?>
