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

require_once("./content/classes/class.ilObjLearningModule.php");
require_once("./classes/class.ilMainMenuGUI.php");
require_once("./classes/class.ilObjStyleSheet.php");

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
	var $lng;
	var $layout_doc;

	function ilLMPresentationGUI()
	{
		global $ilias, $lng, $tpl;

		$this->ilias =& $ilias;
		$this->lng =& $lng;
		$this->tpl =& $tpl;

		$cmd = (!empty($_GET["cmd"])) ? $_GET["cmd"] : "layout";

		// Todo: check lm id
		$this->lm =& new ilObjLearningModule($_GET["ref_id"], true);
		//echo $this->lm->getTitle(); exit;

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
	function layout($a_xml = "main.xml")
	{
		global $tpl;

		$layout = $this->lm->getLayout();

		//$doc = xmldocfile("./layouts/lm/".$layout."/".$a_xml);

		// xmldocfile is deprecated! Use domxml_open_file instead.
		// But since using relative pathes with domxml under windows don't work,
		// we need another solution:
		$xmlfile = file_get_contents("./layouts/lm/".$layout."/".$a_xml);
		if (!$doc = domxml_open_mem($xmlfile)) { echo "ilLMPresentation: XML File invalid"; exit; }
		$this->layout_doc =& $doc;

		$xpc = xpath_new_context($doc);
		$path = (empty($_GET["frame"]) || ($_GET["frame"] == "_new"))
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
			if (empty($attributes["template"]) || !empty($_GET["obj_type"]))
			{
				// we got a variable content frame (can display different
				// object types (PageObject, MediaObject, GlossarItem)
				// and contains elements for them)

				// determine object type
				if(empty($_GET["obj_type"]))
				{
					$obj_type = "PageObject";
				}
				else
				{
					$obj_type = $_GET["obj_type"];
				}

				// get object specific node
				$childs = $node->child_nodes();
				$found = false;
				foreach($childs as $child)
				{
					if ($child->node_name() == $obj_type)
					{
						$found = true;
						$attributes = $this->attrib2arr($child->attributes());
						$node =& $child;
						break;
					}
				}
				if (!$found) { echo "ilLMPresentation: No template specified for frame '".
					$_GET["frame"]."' and object type '".$obj_type."'."; exit; }

			}
			// get template
			$in_module = ($attributes["template_location"] == "module")
				? true
				: false;
			if ($in_module)
			{
				$this->tpl = new ilTemplate($attributes["template"], true, true, $in_module);
			}
			else
			{
				$this->tpl =& $tpl;
			}

			// set style sheets
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
						$this->ilPage($child);
						break;

					case "ilLMNavigation":
						$this->ilLMNavigation();
						break;

					case "ilMedia":
						$this->ilMedia();
						break;

					case "ilLocator":
						$this->ilLocator();
						break;
				}
			}
		}
		$this->tpl->show();
	}

	function fullscreen()
	{
		$this->layout("fullscreen.xml");
	}

	/**
	* output main menu
	*/
	function ilMainMenu()
	{
		$menu = new ilMainMenuGUI("_top", true);
		$menu->setTemplate($this->tpl);
		$menu->addMenuBlock("CONTENT", "navigation");
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

		if ($_GET["lmexpand"] == "")
		{
			$mtree = new ilTree($this->lm->getId());
			$mtree->setTableNames('lm_tree','lm_data');
			$mtree->setTreeTablePK("lm_id");
			$expanded = $mtree->readRootId();
		}
		else
		{
			$expanded = $_GET["lmexpand"];
		}
		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_toc"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->setVariable("ACTION", "lm_presentation.php?cmd=".$_GET["cmd"]."&frame=".$_GET["frame"].
			"&ref_id=".$this->lm->getRefId()."&lmexpand=".$_GET["lmexpand"]);
		$this->tpl->parseCurrentBlock();
	}

	function ilLocator()
	{
		$this->tpl->setCurrentBlock("ilLocator");

		$lm_tree = new ilTree($this->lm->getId());
		$lm_tree->setTableNames('lm_tree','lm_data');
		$lm_tree->setTreeTablePK("lm_id");

		if (empty($_GET["obj_id"]))
		{
			$a_id = $lm_tree->getRootId();
		}
		else
		{
			$a_id = $_GET["obj_id"];
		}

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $lm_tree->getPathFull($a_id);

		// this is a stupid workaround for a bug in PEAR:IT
		$modifier = 1;

		//$modifier = 0;

		$i = 0;
		foreach ($path as $key => $row)
		{
			if ($row["type"] != "pg")
			{

				if ($path[$i + 1]["type"] == "st")
				{
					$this->tpl->touchBlock("locator_separator");
				}

				$this->tpl->setCurrentBlock("locator_item");

				if($row["child"] != $lm_tree->getRootId())
				{
					$this->tpl->setVariable("ITEM", $row["title"]);
					// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
					$this->tpl->setVariable("LINK_ITEM", "lm_presentation.php?frame=".$_GET["frame"]."&cmd=layout&ref_id=".
						$_GET["ref_id"]."&obj_id=".$row["child"]);
				}
				else
				{
					$this->tpl->setVariable("ITEM", $this->lm->getTitle());
					// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
					$this->tpl->setVariable("LINK_ITEM", "lm_presentation.php?frame=".$_GET["frame"]."&cmd=layout&ref_id=".
						$_GET["ref_id"]);
				}

				$this->tpl->parseCurrentBlock();
			}
			$i++;
		}

		/*
		if (isset($_GET["obj_id"]))
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);

			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $obj_data->getTitle());
			// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
			$this->tpl->setVariable("LINK_ITEM", "adm_object.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]);
			$this->tpl->parseCurrentBlock();
		}*/

		$this->tpl->setCurrentBlock("locator");

		if (DEBUG)
		{
			$debug = "DEBUG: <font color=\"red\">".$this->type."::".$this->id."::".$_GET["cmd"]."</font><br/>";
		}

		//$prop_name = $this->objDefinition->getPropertyName($_GET["cmd"],$this->type);


		$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));

		$this->tpl->parseCurrentBlock();
	}

	function getCurrentPageId()
	{
		$lmtree = new ilTree($this->lm->getId());
		$lmtree->setTableNames('lm_tree','lm_data');
		$lmtree->setTreeTablePK("lm_id");

		// determine object id
		if(empty($_GET["obj_id"]))
		{
			$obj_id = $lmtree->getRootId();
		}
		else
		{
			$obj_id = $_GET["obj_id"];
		}

		// obj_id not in tree -> it is a unassigned page -> return page id
		if (!$lmtree->isInTree($obj_id))
		{
			return $obj_id;
		}

		$curr_node = $lmtree->getNodeData($obj_id);
		if($curr_node["type"] == "pg")		// page in tree -> return page id
		{
			$page_id = $curr_node["obj_id"];
		}
		else				// no page -> search for next page and return its id
		{
			$succ_node = $lmtree->fetchSuccessorNode($obj_id, "pg");
			$page_id = $succ_node["obj_id"];

			if ($succ_node["type"] != "pg")
			{
				$this->tpl = new ilTemplate("tpl.main.html", true, true);
				$this->ilias->raiseError($this->lng->txt("cont_no_page"),$this->ilias->error_obj->FATAL);
				$this->tpl->show();
				exit;
//echo "2:".$succ_node["type"].":"; exit;
			}
		}
		return $page_id;
	}

	function ilPage(&$a_page_node)
	{
		// read link targets
		$childs =& $a_page_node->child_nodes();
		foreach($childs as $child)
		{
			if($child->node_name() == "LinkTarget")
			{
				$targets.= $this->layout_doc->dump_node($child);
			}
		}
		$targets = "<LinkTargets>$targets</LinkTargets>";

		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId()));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setCurrentBlock("ilPage");

		$page_id = $this->getCurrentPageId();

		require_once("content/classes/class.ilPageObject.php");
		$pg_obj =& new ilPageObject($page_id);
		$pg_obj->setLMId($this->lm->getId());
		$builded = $pg_obj->buildDom();
		$content = $pg_obj->getXMLFromDom(false, true, true, $targets);

		$pg_title = $pg_obj->getPresentationTitle($this->lm->getPageHeader());

		// convert bb code to xml
		$pg_obj->bbCode2XML($content);

		// todo: utf-header should be set globally
		//header('Content-type: text/html; charset=UTF-8');

		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
//echo "<b>XML</b>:".htmlentities($content).":<br>";
//echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";

		// determine target frames for internal links
		$pg_frame = $_GET["frame"];
		$wb_path = "../".$this->ilias->ini->readVariable("server","webspace_dir");
		$params = array ('mode' => 'presentation', 'pg_title' => $pg_title, 'pg_id' => $page_id,
			'ref_id' => $this->lm->getRefId(), 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$output = str_replace("&lt;","<",$output);
		$output = str_replace("&gt;",">",$output);
		$output = str_replace("&amp;", "&", $output);
//echo "<b>HTML</b>".htmlentities($output);
		$this->tpl->setVariable("PAGE_CONTENT", $output);
	}

	function ilMedia()
	{
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId()));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setCurrentBlock("ilMedia");

		require_once("content/classes/class.ilMediaObject.php");
		$media_obj =& new ilMediaObject($_GET["mob_id"]);

		require_once("content/classes/class.ilPageObject.php");
		$pg_obj =& new ilPageObject($_GET["pg_id"]);
		$pg_obj->buildDom();

		$xml = "<dummy>";
		// todo: we get always the first alias now (problem if mob is used multiple
		// times in page)
		$xml.= $pg_obj->getMediaAliasElement($_GET["mob_id"]);
		$xml.= $media_obj->getXML(IL_MODE_OUTPUT);
		$xml.="</dummy>";


		// todo: utf-header should be set globally
		//header('Content-type: text/html; charset=UTF-8');

		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();

//echo "<b>XML:</b>".htmlentities($xml);
		// determine target frames for internal links
		//$pg_frame = $_GET["frame"];
		$wb_path = "../".$this->ilias->ini->readVariable("server","webspace_dir");
		$params = array ('mode' => 'fullscreen',
			'ref_id' => $this->lm->getRefId(), 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$this->tpl->setVariable("MEDIA_CONTENT", $output);
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

		if(!$lmtree->isInTree($page_id))
		{
			return;
		}

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
			$pre_title = $pre_page->getPresentationTitle($this->lm->getPageHeader());
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
			$succ_title = $succ_page->getPresentationTitle($this->lm->getPageHeader());
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
							"src=\"lm_presentation.php?ref_id=".$this->lm->getRefId()."&cmd=layout&frame=".$attributes["name"]."&obj_id=".$_GET["obj_id"]."\" />\n";
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
						"src=\"lm_presentation.php?ref_id=".$this->lm->getRefId()."&cmd=layout&frame=".$attributes["name"]."&obj_id=".$_GET["obj_id"]."\" />\n";
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
