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
		$this->lm =& new ilLearningModule($_GET["ref_id"]);

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
			$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
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

					case "ilPage":
						$this->ilPage();
						break;

					case "ilLMNavigation":
						$this->ilLMNavigation();
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
		$exp = new ilLMTOCExplorer("lm_presentation.php?cmd=layout&frame=$a_target&ref_id=".$this->lm->getRefId(),$this->lm);
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
			"&ref_id=".$this->lm->getRefId()."&mexpand=".$_GET["mexpand"]);
		$this->tpl->parseCurrentBlock();
	}

	function getCurrentPageId()
	{
		$lmtree = new ilTree($this->lm->getId());
		$lmtree->setTableNames('lm_tree','lm_data');
		$lmtree->setTreeTablePK("lm_id");
		if(empty($_GET["obj_id"]))
		{
			$obj_id = $lmtree->getRootId();
		}
		else
		{
			$obj_id = $_GET["obj_id"];
		}
		$curr_node = $lmtree->getNodeData($obj_id);
		if($curr_node["type"] == "pg")
		{
			$page_id = $curr_node["obj_id"];
		}
		else
		{
			$succ_node = $lmtree->fetchSuccessorNode($obj_id, "pg");
			$page_id = $succ_node["obj_id"];
		}
		return $page_id;
	}

	function ilPage()
	{
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setCurrentBlock("ilPage");

		$page_id = $this->getCurrentPageId();

		require_once("content/classes/class.ilPageObject.php");
		$pg_obj =& new ilPageObject($page_id);
		$pg_obj->setLMId($this->lm->getId());
		$content = $pg_obj->getXMLContent();
		$pg_title = $pg_obj->getPresentationTitle();

		// convert bb code to xml
		$pg_obj->bbCode2XML($content);

		// todo: utf-header should be set globally
		header('Content-type: text/html; charset=UTF-8');

		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
//echo "<b>XML</b>:".htmlentities($content).":<br>";
//echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
		$params = array ('mode' => 'preview', 'pg_title' => $pg_title);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$output = str_replace("&lt;","<",$output);
		$output = str_replace("&gt;",">",$output);
//echo "<b>HTML</b>".htmlentities($output);
		$this->tpl->setVariable("PAGE_CONTENT", $output);
	}


	/**
	* inserts sequential learning module navigation
	* at template variable LMNAVIGATION_CONTENT
	*/
	function ilLMNavigation()
	{
		$page_id = $this->getCurrentPageId();

		if(empty($page_id))
		{
			return;
		}

		$lmtree = new ilTree($this->lm->getId());
		$lmtree->setTableNames('lm_tree','lm_data');
		$lmtree->setTreeTablePK("lm_id");
		$succ_node = $lmtree->fetchSuccessorNode($page_id, "pg");
		$succ_str = ($succ_node !== false)
			? " -> ".$succ_node["obj_id"]."_".$succ_node["type"]
			: "";
		$pre_node = $lmtree->fetchPredecessorNode($page_id, "pg");
		$pre_str = ($pre_node !== false)
			? $pre_node["obj_id"]."_".$pre_node["type"]." -> "
			: "";

		// determine target frame
		$framestr = (!empty($_GET["frame"]))
			? "frame=".$_GET["frame"]."&"
			: "";

		if($pre_node != "")
		{
			$this->tpl->setCurrentBlock("ilLMNavigation_Prev");
			$pre_page =& new ilPageObject($pre_node["obj_id"]);
			$pre_page->setLMId($this->lm->getId());
			$pre_title = $pre_page->getPresentationTitle();
			$output = "<a href=\"lm_presentation.php?".$framestr."cmd=layout&obj_id=".
				$pre_node["obj_id"]."&ref_id=".$this->lm->getRefId().
				"\">&lt; ".$pre_title."</a>";
			$this->tpl->setVariable("LMNAVIGATION_PREV", $output);
			$this->tpl->parseCurrentBlock();
		}
		if($succ_node != "")
		{
			$this->tpl->setCurrentBlock("ilLMNavigation_Next");
			$succ_page =& new ilPageObject($succ_node["obj_id"]);
			$succ_page->setLMId($this->lm->getId());
			$succ_title = $succ_page->getPresentationTitle();
			$output = " <a href=\"lm_presentation.php?".$framestr."cmd=layout&obj_id=".
				$succ_node["obj_id"]."&ref_id=".$this->lm->getRefId().
				"\">".$succ_title." &gt;</a>";
			$this->tpl->setVariable("LMNAVIGATION_NEXT", $output);
			$this->tpl->parseCurrentBlock();

		}


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
							"src=\"lm_presentation.php?ref_id=".$this->lm->getRefId()."&cmd=layout&frame=".$attributes["name"]."\" />\n";
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
						"src=\"lm_presentation.php?ref_id=".$this->lm->getRefId()."&cmd=layout&frame=".$attributes["name"]."\" />\n";
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
