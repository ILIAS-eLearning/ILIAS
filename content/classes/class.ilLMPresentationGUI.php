<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2004 ILIAS open source, University of Cologne            |
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
		global $ilias, $lng, $tpl, $rbacsystem;

		$this->ilias =& $ilias;
		$this->lng =& $lng;
		$this->tpl =& $tpl;

		$cmd = (!empty($_GET["cmd"]))
			? $_GET["cmd"]
			: "layout";

		$cmd = ($cmd == "edpost")
			? "ilCitation"
			: $cmd;

		// Todo: check lm id
		$type = $this->ilias->obj_factory->getTypeByRefId($_GET["ref_id"]);

		// TODO: WE NEED AN OBJECT FACTORY FOR GUI CLASSES
		switch($type)
		{
			case "dbk":
				include_once("./content/classes/class.ilObjDlBookGUI.php");

				$this->lm_gui = new ilObjDlBookGUI($data,$_GET["ref_id"],true,false);
				break;
			case "lm":
				include_once("./content/classes/class.ilObjLearningModuleGUI.php");

				$this->lm_gui = new ilObjLearningModuleGUI($data,$_GET["ref_id"],true,false);

				break;
		}
		$this->lm =& $this->lm_gui->object;
		
		// check, if learning module is online
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			if (!$this->lm->getOnline())
			{
				$ilias->raiseError($lng->txt("permission_denied"), $ilias->error_obj->WARNING);
			}
		}


		// ### AA 03.09.01 added page access logger ###
		$this->lmAccess($this->ilias->account->getId(),$_GET["ref_id"],$_GET["obj_id"]);

		if ($cmd == "post")
		{
			$cmd = key($_POST["cmd"]);
		}
		$this->$cmd();
	}

	// ### AA 03.09.01 added page access logger ###
	/**
	* logs access to lm objects to enable retrieval of a 'last viewed lm list' and 'return to last lm'
	* allows only ONE entry per user and lm object
	*
	* A.L. Ammerlaan / INGMEDIA FH-Aachen / 2003.09.08
	*/
	function lmAccess($usr_id,$lm_id,$obj_id)
	{
		// first check if an entry for this user and this lm already exist, when so, delete
		$q = "DELETE FROM lo_access ".
			"WHERE usr_id='".$usr_id."' ".
			"AND lm_id='".$lm_id."'";
		$this->ilias->db->query($q);
		$title = (is_object($this->lm))?$this->lm->getTitle():"- no title -";
		// insert new entry
		$pg_title = "";
		$q = "INSERT INTO lo_access ".
			"(timestamp,usr_id,lm_id,obj_id,lm_title) ".
			"VALUES ".
			"(now(),'".$usr_id."','".$lm_id."','".$obj_id."','".ilUtil::prepareDBString($title)."')";
		$this->ilias->db->query($q);
	}

    /**
    *   calls export of digilib-object
    *   at this point other lm-objects can be exported
    *
    *   @param
    *   @access public
    *   @return
    */
	function export()
	{
		switch($this->lm->getType())
		{
			case "dbk":
				$this->lm_gui->export();
				break;
		}
	}

    /**
    *   the different export types are processed here
    *
    *   @param
    *   @access public
    *   @return
    */
	function offlineexport() {

		if ($_POST["cmd"]["cancel"] != "")
		{
			ilUtil::redirect("lm_presentation.php?cmd=layout&frame=maincontent&ref_id=".$_GET["ref_id"]);
		}

		switch($this->lm->getType())
		{
			case "dbk":
				//$this->lm_gui->offlineexport();
				$_GET["frame"] = "maincontent";

				$query = "SELECT * FROM object_reference,object_data WHERE object_reference.ref_id='".
					$_GET["ref_id"]."' AND object_reference.obj_id=object_data.obj_id ";
				$result = $this->ilias->db->query($query);
				$objRow = $result->fetchRow(DB_FETCHMODE_ASSOC);
				$_GET["obj_id"] = $objRow["obj_id"];

				$query = "SELECT * FROM lm_data WHERE lm_id='".$objRow["obj_id"]."' AND type='pg' ";
				$result = $this->ilias->db->query($query);

				$page = 0;
				$showpage = 0;
				while (is_array($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) )
				{

					$page++;

					if ($_POST["pages"]=="all" || ($_POST["pages"]=="fromto" && $page>=$_POST["pagefrom"] && $page<=$_POST["pageto"] ))
                    {

						if ($showpage>0)
						{
							if($_POST["type"] == "pdf") $output .= "<hr BREAK >\n";
							if($_POST["type"] == "print") $output .= "<p style=\"page-break-after:always\" />";
							if($_POST["type"] == "html") $output .= "<br><br><br><br>";
						}
						$showpage++;

						$_GET["obj_id"] = $row["obj_id"];
						$o = $this->layout("main.xml",false);

                        $output .= "<div xmlns:xhtml=\"http://www.w3.org/1999/xhtml\" class=\"ilc_PageTitle\">".$this->lm->title."</div><p>";
						$output .= $o;

						$output .= "\n<table cellpadding=0 cellspacing=0 border=0 width=100%><tr><td valign=top align=center>- ".$page." -</td></tr></table>\n";

					}
				}

				$printTpl = new ilTemplate("tpl.print.html", true, true, true);

				if($_POST["type"] == "print")
				{
					$printTpl->touchBlock("printreq");
					$css1 = ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId());
					$css2 = ilUtil::getStyleSheetLocation();
				}
				else
				{
					$css1 = "./css/blueshadow.css";
					$css2 = "./css/content.css";
				}
				$printTpl->setVariable("LOCATION_CONTENT_STYLESHEET", $css1 );

				$printTpl->setVariable("LOCATION_STYLESHEET", $css2);
				$printTpl->setVariable("CONTENT",$output);

				// syntax style
				$printTpl->setCurrentBlock("SyntaxStyle");
				$printTpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
					ilObjStyleSheet::getSyntaxStylePath());
				$printTpl->parseCurrentBlock();


				$html = $printTpl->get();

				/**
				*	Check if export-directory exists
				*/
				$this->lm->createExportDirectory();
				$export_dir = $this->lm->getExportDirectory();

				/**
				*	create html-offline-directory
				*/
				$fileName = "offline";
				$fileName = str_replace(" ","_",$fileName);

				if (!file_exists($export_dir."/".$fileName))
				{
					@mkdir($export_dir."/".$fileName);
					@chmod($export_dir."/".$fileName, 0755);

					@mkdir($export_dir."/".$fileName."/css");
					@chmod($export_dir."/".$fileName."/css", 0755);

				}

				if($_POST["type"] == "xml")
				{
					//vd($_GET["ref_id"]);
					$tmp_obj =& $this->ilias->obj_factory->getInstanceByRefId($_GET["ref_id"]);

					if ($tmp_obj->getType() == "dbk" )
					{
						require_once "content/classes/class.ilObjDlBook.php";
						$dbk =& new ilObjDlBook($_GET["ref_id"]);
						$dbk->export();
					}

				}
				else if($_POST["type"] == "print")
				{
					echo $html;
				}
				else if ($_POST["type"]=="html")
				{

					/**
					*	copy data into dir
					*	zip all end deliver zip-file for download
					*/

					$css1 = file("./templates/default/blueshadow.css");
					$css1 = implode($css1,"");

					$fp = fopen($export_dir."/".$fileName."/css/blueshadow.css","wb");
					fwrite($fp,$css1);
					fclose($fp);

					$css2 = file("./content/content.css");
					$css2 = implode($css2,"");

					$fp = fopen($export_dir."/".$fileName."/css/content.css","wb");
					fwrite($fp,$css2);
					fclose($fp);


					$fp = fopen($export_dir."/".$fileName."/".$fileName.".html","wb");
					fwrite($fp,$html);
					fclose($fp);

					ilUtil::zip($export_dir."/".$fileName, $export_dir."/".$fileName.".zip");

                    ilUtil::deliverFile($export_dir."/".$fileName.".zip", $fileName.".zip");

				}
                else if ($_POST["type"]=="pdf")
				{

                    ilUtil::html2pdf($html, $export_dir."/".$fileName.".pdf");

                    ilUtil::deliverFile($export_dir."/".$fileName.".pdf", $fileName.".pdf");

				}

				exit;
		}

	}

    /**
    *   draws export-form on screen
    *
    *   @param
    *   @access public
    *   @return
    */
	function offlineexportform()
    {

		switch($this->lm->getType())
		{
			case "dbk":
				$this->lm_gui->offlineexportform();
				break;
		}

	}


    /**
    *   export bibinfo for download or copy/paste
    *
    *   @param
    *   @access public
    *   @return
    */
	function exportbibinfo()
	{
		$query = "SELECT * FROM object_reference,object_data WHERE object_reference.ref_id='".$_GET["ref_id"]."' AND object_reference.obj_id=object_data.obj_id ";
		$result = $this->ilias->db->query($query);

		$objRow = $result->fetchRow(DB_FETCHMODE_ASSOC);

		$filename = preg_replace('/[^a-z0-9_]/i', '_', $objRow["title"]);

		$C = $this->lm_gui->showAbstract(array(1));

		if ($_GET["print"]==1)
		{
			$printTpl = new ilTemplate("tpl.print.html", true, true, true);
			$printTpl->touchBlock("printreq");
			$css1 = ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId());
			$css2 = ilUtil::getStyleSheetLocation();
			$printTpl->setVariable("LOCATION_CONTENT_STYLESHEET", $css1 );

			$printTpl->setVariable("LOCATION_STYLESHEET", $css2);

			// syntax style
			$printTpl->setCurrentBlock("SyntaxStyle");
			$printTpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
				ilObjStyleSheet::getSyntaxStylePath());
			$printTpl->parseCurrentBlock();

			$printTpl->setVariable("CONTENT",$C);

			echo $printTpl->get();
			exit;
		}
		else
		{
			ilUtil::deliverData($C, $filename.".html");
			exit;
		}

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
	function layout($a_xml = "main.xml", $doShow = true)
	{
		global $tpl, $ilBench;

		$ilBench->start("ContentPresentation", "layout");

		$layout = $this->lm->getLayout();

		//$doc = xmldocfile("./layouts/lm/".$layout."/".$a_xml);

		// xmldocfile is deprecated! Use domxml_open_file instead.
		// But since using relative pathes with domxml under windows don't work,
		// we need another solution:
		$xmlfile = file_get_contents("./layouts/lm/".$layout."/".$a_xml);
		if (!$doc = domxml_open_mem($xmlfile)) { echo "ilLMPresentation: XML File invalid"; exit; }
		$this->layout_doc =& $doc;
//echo ":".htmlentities($xmlfile).":$layout:$a_xml:";

		// get current frame node
		$ilBench->start("ContentPresentation", "layout_getFrameNode");
		$xpc = xpath_new_context($doc);
		$path = (empty($_GET["frame"]) || ($_GET["frame"] == "_new"))
			? "/ilLayout/ilFrame[1]"
			: "//ilFrame[@name='".$_GET["frame"]."']";
		$result = xpath_eval($xpc, $path);
		$found = $result->nodeset;
		if (count($found) != 1) { echo "ilLMPresentation: XML File invalid"; exit; }
		$node = $found[0];

		$ilBench->stop("ContentPresentation", "layout_getFrameNode");

		// ProcessFrameset
		// node is frameset, if it has cols or rows attribute
		$attributes = $this->attrib2arr($node->attributes());
		if((!empty($attributes["rows"])) || (!empty($attributes["cols"])))
		{
			$ilBench->start("ContentPresentation", "layout_processFrameset");
			$content .= $this->buildTag("start", "frameset", $attributes);
			$this->processNodes($content, $node);
			$content .= $this->buildTag("end", "frameset");
			$this->tpl = new ilTemplate("tpl.frameset.html", true, true, true);
			$this->tpl->setVariable("PAGETITLE", "- ".$this->lm->getTitle());
			$this->tpl->setVariable("FS_CONTENT", $content);
			$ilBench->stop("ContentPresentation", "layout_processFrameset");
//echo nl2br(htmlentities($content));
		}
		else	// node is frame -> process the content tags
		{
//echo "<br>1node:".$node->node_name();
			// ProcessContentTag
			$ilBench->start("ContentPresentation", "layout_processContentTag");
			//if ((empty($attributes["template"]) || !empty($_GET["obj_type"])))
			if ((empty($attributes["template"]) || !empty($_GET["obj_type"]))
				&& ($_GET["frame"] != "_new" || $_GET["obj_type"] != "MediaObject"))
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
//echo "<br>2node:".$node->node_name();
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
						switch($this->lm->getType())
						{
							case "lm":
								unset($_SESSION["tr_id"]);
								unset($_SESSION["bib_id"]);
								unset($_SESSION["citation"]);
								$page_content = $this->ilPage($child);
								break;

							case "dbk":
								$this->setSessionVars();
								if((count($_POST["tr_id"]) > 1) or
								   (!$_POST["target"] and ($_POST["action"] == "show" or $_POST["action"] == "show_citation")))
								{
									$pageContent = $this->lm_gui->showAbstract($_POST["target"]);
								}
								else if($_GET["obj_id"] or ($_POST["action"] == "show") or ($_POST["action"] == "show_citation"))
								{
									// SHOW PAGE IF PAGE WAS SELECTED
									$pageContent = $this->ilPage($child);
									if($_SESSION["tr_id"])
									{
										$translation_content = $this->ilTranslation($child);
									}
								}
								else
								{
									// IF NO PAGE ID IS GIVEN SHOW BOOK/LE ABSTRACT
									$pageContent = $this->lm_gui->showAbstract($_POST["target"]);
								}

								break;
						}

						break;

					case "ilGlossary":
						$pageContent = $this->ilGlossary($child);
						break;

					case "ilLMNavigation":

						// NOT FOR ABSTRACT
						if($_GET["obj_id"] or
						   ((count($_POST["tr_id"]) < 2) and $_POST["target"] and
							($_POST["action"] == "show" or $_POST["action"] == "show_citation")) or
						   $this->lm->getType() == 'lm')
						{
							$this->ilLMNavigation();
						}
						break;

					case "ilMedia":
						$this->ilMedia();
						break;

					case "ilLocator":
						$this->ilLocator();
						break;

					case "ilLMMenu":
//echo "<br>LMPresentationGUI calls ilLMMenu";
						$this->ilLMMenu();
						break;
				}
			}
			$ilBench->stop("ContentPresentation", "layout_processContentTag");
		}

		if ($doShow)
		{
			// (horrible) workaround for preventing template engine
			// from hiding paragraph text that is enclosed
			// in curly brackets (e.g. "{a}", see ilPageObjectGUI::showPage())
			$output =  $this->tpl->get();
			$output = str_replace("&#123;", "{", $output);
			$output = str_replace("&#125;", "}", $output);
			header('Content-type: text/html; charset=UTF-8');
			echo $output;
		}

		$ilBench->stop("ContentPresentation", "layout");

		return($pageContent);
	}

	function fullscreen()
	{
		$this->layout("fullscreen.xml");
	}

	function media()
	{
		if ($_GET["frame"] != "_new")
		{
			$this->layout("main.xml");
		}
		else
		{
			$this->layout("fullscreen.xml");
		}
	}

	function glossary()
	{
		if ($_GET["frame"] != "_new")
		{
			$this->layout();
		}
		else
		{
			$this->tpl = new ilTemplate("tpl.glossary_term_output.html", true, true, true);
			$this->tpl->setVariable("PAGETITLE", " - ".$this->lm->getTitle());
			$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
			$this->ilGlossary($child);
			$this->tpl->show();
		}
	}

	/**
	* output main menu
	*/
	function ilMainMenu()
	{
		global $ilBench;

		// Determine whether the view of a learning resource should
		// be shown in the frameset of ilias, or in a separate window.
		$showViewInFrameset = $this->ilias->ini->readVariable("layout","view_target") == "frame";


		$ilBench->start("ContentPresentation", "ilMainMenu");
		if ($showViewInFrameset) {
			$menu = new ilMainMenuGUI("bottom", true);
		}
		else
		{
			$menu = new ilMainMenuGUI("_top", true);
		}
		$menu->setTemplate($this->tpl);
		$menu->addMenuBlock("CONTENT", "navigation");
		$menu->setTemplateVars();
		$ilBench->stop("ContentPresentation", "ilMainMenu");
	}

	function ilTOC($a_target)
	{
		global $ilBench;

		$ilBench->start("ContentPresentation", "ilTOC");
		require_once("./content/classes/class.ilLMTOCExplorer.php");
		$exp = new ilLMTOCExplorer("lm_presentation.php?cmd=layout&frame=$a_target&ref_id=".$this->lm->getRefId(),$this->lm);
		$exp->setTargetGet("obj_id");
		$exp->setFrameTarget($a_target);
		$exp->addFilter("du");
		$exp->addFilter("st");
		if ($this->lm->getTOCMode() == "pages")
		{
			$exp->addFilter("pg");
		}
		$exp->setFiltered(true);
		$exp->setFilterMode(IL_FM_POSITIVE);

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

		$this->tpl->setVariable("PAGETITLE", " - ".$this->lm->getTitle());
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_toc"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->setVariable("ACTION", "lm_presentation.php?cmd=".$_GET["cmd"]."&frame=".$_GET["frame"].
			"&ref_id=".$this->lm->getRefId()."&lmexpand=".$_GET["lmexpand"]);
		$this->tpl->parseCurrentBlock();
		$ilBench->stop("ContentPresentation", "ilTOC");
	}

	function ilLMMenu()
	{
		$this->tpl->setVariable("MENU", $this->lm_gui->setilLMMenu());
	}

	function ilLocator()
	{
		require_once("content/classes/class.ilStructureObject.php");

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

		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		if($lm_tree->isInTree($a_id))
		{
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
						$this->tpl->setVariable("ITEM", ilUtil::shortenText(
							ilStructureObject::_getPresentationTitle($row["child"],
							$this->lm->isActiveNumbering()),50,true));
						// TODO: SCRIPT NAME HAS TO BE VARIABLE!!!
						$this->tpl->setVariable("LINK_ITEM", "lm_presentation.php?frame=".$_GET["frame"]."&cmd=layout&ref_id=".
							$_GET["ref_id"]."&obj_id=".$row["child"]);
					}
					else
					{
						$this->tpl->setVariable("ITEM", ilUtil::shortenText($this->lm->getTitle(),50,true));
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
		}
		else		// lonely page
		{
			$this->tpl->touchBlock("locator_separator");

			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $this->lm->getTitle());
			$this->tpl->setVariable("LINK_ITEM", "lm_presentation.php?frame=".$_GET["frame"]."&cmd=layout&ref_id=".
				$_GET["ref_id"]);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("locator_item");
			require_once("content/classes/class.ilLMObjectFactory.php");
			$lm_obj =& ilLMObjectFactory::getInstance($this->lm, $a_id);
			$this->tpl->setVariable("ITEM", $lm_obj->getTitle());
			$this->tpl->setVariable("LINK_ITEM", "lm_presentation.php?frame=".$_GET["frame"]."&cmd=layout&ref_id=".
				$_GET["ref_id"]."&obj_id=".$a_id);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("locator_item");
		}


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

	function mapCurrentPageId($current_page_id)
	{
		$lmtree = new ilTree($this->lm->getId());
		$lmtree->setTableNames('lm_tree','lm_data');
		$lmtree->setTreeTablePK("lm_id");
		$subtree = $lmtree->getSubTree($lmtree->getNodeData(1));
		$node = $lmtree->getNodeData($current_page_id);
		$pos = array_search($node,$subtree);
		unset($lmtree);

		$this->tr_obj =& $this->ilias->obj_factory->getInstanceByRefId($_SESSION["tr_id"]);

		$lmtree = new ilTree($this->tr_obj->getId());
		$lmtree->setTableNames('lm_tree','lm_data');
		$lmtree->setTreeTablePK("lm_id");

		$subtree = $lmtree->getSubTree($lmtree->getNodeData(1));

		return $subtree[$pos]["child"];
	}

	function ilTranslation(&$a_page_node)
	{
		require_once("content/classes/Pages/class.ilPageObjectGUI.php");
		require_once("content/classes/class.ilLMPageObject.php");

		$page_id = $this->mapCurrentPageId($this->getCurrentPageId());

		if(!$page_id)
		{
			$this->tpl->setVariable("TRANSLATION_CONTENT","NO TRNSLATION FOUND");
			return false;
		}

		$page_object =& new ilPageObject($this->lm->getType(), $page_id);
		$page_object_gui =& new ilPageObjectGUI($page_object);

		$this->ilias->account->setDesktopItemParameters($_SESSION["tr_id"], $this->lm->getType(),$page_id);

		// read link targets
		$targets = $this->getLayoutLinkTargets();

		$lm_pg_obj =& new ilLMPageObject($this->lm, $page_id);
		$lm_pg_obj->setLMId($_SESSION["tr_id"]);
		//$pg_obj->setParentId($this->lm->getId());
		#$page_object_gui->setLayoutLinkTargets($targets);

		// USED FOR DBK PAGE TURNS
		$page_object_gui->setBibId($_SESSION["bib_id"]);

		// determine target frames for internal links
		//$pg_frame = $_GET["frame"];
		$page_object_gui->setLinkFrame($_GET["frame"]);
		$page_object_gui->setOutputMode("presentation");
		$page_object_gui->setOutputSubmode("translation");

		$page_object_gui->setPresentationTitle(
			ilLMPageObject::_getPresentationTitle($lm_pg_obj->getId(),
			$this->lm->getPageHeader(), $this->lm->isActiveNumbering()));
#		$page_object_gui->setLinkParams("ref_id=".$this->lm->getRefId());
		$page_object_gui->setLinkParams("ref_id=".$_SESSION["tr_id"]);
		$page_object_gui->setTemplateTargetVar("PAGE_CONTENT");
		$page_object_gui->setTemplateOutputVar("TRANSLATION_CONTENT");


		return $page_object_gui->presentation();

	}

	function ilCitation()
	{
		$page_id = $this->getCurrentPageId();
		$this->tpl = new ilTemplate("tpl.page.html",true,true,true);
		$this->ilLocator();
		$this->tpl->setVariable("MENU",$this->lm_gui->setilCitationMenu());

		include_once("content/classes/Pages/class.ilPageObject.php");

		$this->pg_obj =& new ilPageObject($this->lm->getType(),$page_id);
		$xml = $this->pg_obj->getXMLContent();
		$this->lm_gui->showCitation($xml);
		$this->tpl->show();
	}


	function getLayoutLinkTargets()
	{

		if (!is_object($this->layout_doc))
			return array ();

		$xpc = xpath_new_context($this->layout_doc);

		$path = "/ilLayout/ilLinkTargets/LinkTarget";
		$res = xpath_eval($xpc, $path);
		$targets = array();
		for ($i = 0; $i < count($res->nodeset); $i++)
		{
			$type = $res->nodeset[$i]->get_attribute("Type");
			$frame = $res->nodeset[$i]->get_attribute("Frame");
			$targets[$type] = array("Type" => $type, "Frame" => $frame);
		}
		return $targets;
	}

	/**
	* process <ilPage> content tag
	*/
	function ilPage(&$a_page_node)
	{
		global $ilBench;

		$ilBench->start("ContentPresentation", "ilPage");

		require_once("content/classes/Pages/class.ilPageObjectGUI.php");
		require_once("content/classes/class.ilLMPageObject.php");
		$page_id = $this->getCurrentPageId();
		$page_object =& new ilPageObject($this->lm->getType(), $page_id);
		$page_object->buildDom();
		$int_links = $page_object->getInternalLinks();
		$page_object_gui =& new ilPageObjectGUI($page_object);

		$this->ilias->account->setDesktopItemParameters($this->lm->getRefId(), $this->lm->getType(), $page_id);

		// read link targets
		$link_xml = $this->getLinkXML($int_links, $this->getLayoutLinkTargets());

		$lm_pg_obj =& new ilLMPageObject($this->lm, $page_id);
		$lm_pg_obj->setLMId($this->lm->getId());
		//$pg_obj->setParentId($this->lm->getId());
		$page_object_gui->setLinkXML($link_xml);

		// USED FOR DBK PAGE TURNS
		$page_object_gui->setBibId($_SESSION["bib_id"]);
		$page_object_gui->enableCitation((bool) $_SESSION["citation"]);

		// determine target frames for internal links
		//$pg_frame = $_GET["frame"];
		$page_object_gui->setLinkFrame($_GET["frame"]);
		$page_object_gui->setOutputMode("presentation");
		$page_object_gui->setFileDownloadLink("lm_presentation.php?cmd=downloadFile".
			"&amp;ref_id=".$this->lm->getRefId());
		$page_object_gui->setFullscreenLink("lm_presentation.php?cmd=fullscreen".
			"&amp;ref_id=".$this->lm->getRefId());
		$page_object_gui->setPresentationTitle(
			ilLMPageObject::_getPresentationTitle($lm_pg_obj->getId(),
			$this->lm->getPageHeader(), $this->lm->isActiveNumbering()));

		// ADDED FOR CITATION
		$page_object_gui->setLinkParams("ref_id=".$this->lm->getRefId());
		$page_object_gui->setTemplateTargetVar("PAGE_CONTENT");
		$page_object_gui->setSourcecodeDownloadScript("lm_presentation.php?".session_name()."=".session_id()."&ref_id=".$this->lm->getRefId());

		if($_SESSION["tr_id"])
		{
			$page_object_gui->setOutputSubmode("translation");
		}

		// content style
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId()));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		// track user access to page
		require_once "./tracking/classes/class.ilUserTracking.php";
		ilUserTracking::_trackAccess($this->lm->getId(), $this->lm->getType(),
			$page_id, "pg", "read");

		$ilBench->stop("ContentPresentation", "ilPage");

		return $page_object_gui->presentation();

	}

	/**
	* get xml for links
	*/
	function getLinkXML($a_int_links, $a_layoutframes)
	{
		// Determine whether the view of a learning resource should
		// be shown in the frameset of ilias, or in a separate window.
		$showViewInFrameset = $this->ilias->ini->readVariable("layout","view_target") == "frame";

		if ($a_layoutframes == "")
		{
			$a_layoutframes = array();
		}
		$link_info = "<IntLinkInfos>";
		foreach ($a_int_links as $int_link)
		{
			$target = $int_link["Target"];
			if (substr($target, 0, 4) == "il__")
			{
				$target_arr = explode("_", $target);
				$target_id = $target_arr[count($target_arr) - 1];
				$type = $int_link["Type"];
				$targetframe = ($int_link["TargetFrame"] != "")
					? $int_link["TargetFrame"]
					: "None";

				switch($type)
				{
					case "PageObject":
					case "StructureObject":
						$lm_id = ilLMObject::_lookupContObjID($target_id);
						if ($lm_id == $this->lm->getId() || $targetframe != "None")
						{
							$ltarget = $a_layoutframes[$targetframe]["Frame"];
							//$nframe = ($ltarget == "")
							//	? $_GET["frame"]
							//	: $ltarget;
							$nframe = ($ltarget == "")
								? ""
								: $ltarget;
							if ($ltarget == "")
							{
								if ($showViewInFrameset) {
									$ltarget="_parent";
								} else {
									$ltarget="_top";
								}
							}
							$href = "lm_presentation.php?obj_type=$type&amp;cmd=layout&amp;ref_id=".$_GET["ref_id"].
								"&amp;obj_id=".$target_id."&amp;frame=$nframe";
						}
						else
						{
							if ($type == "PageObject")
							{
								$href = "../goto.php?target=pg_".$target_id;
							}
							else
							{
								$href = "../goto.php?target=st_".$target_id;
							}
							$ltarget = "ilContObj".$lm_id;
						}
						break;

					case "GlossaryItem":
						if ($targetframe == "None")
						{
							$targetframe = "Glossary";
						}
						$ltarget = $a_layoutframes[$targetframe]["Frame"];
						$nframe = ($ltarget == "")
							? $_GET["frame"]
							: $ltarget;
						$href = "lm_presentation.php?obj_type=$type&amp;cmd=glossary&amp;ref_id=".$_GET["ref_id"].
							"&amp;obj_id=".$target_id."&amp;frame=$nframe";
						break;

					case "MediaObject":
						$ltarget = $a_layoutframes[$targetframe]["Frame"];
						$nframe = ($ltarget == "")
							? $_GET["frame"]
							: $ltarget;
						$href = "lm_presentation.php?obj_type=$type&amp;cmd=media&amp;ref_id=".$_GET["ref_id"].
							"&amp;mob_id=".$target_id."&amp;frame=$nframe";
						break;
				}
				$link_info.="<IntLinkInfo Target=\"$target\" Type=\"$type\" ".
					"TargetFrame=\"$targetframe\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" />";
					
				// set equal link info for glossary links of target "None" and "Glossary"
				/*
				if ($targetframe=="None" && $type=="GlossaryItem")
				{
					$link_info.="<IntLinkInfo Target=\"$target\" Type=\"$type\" ".
						"TargetFrame=\"Glossary\" LinkHref=\"$href\" LinkTarget=\"$ltarget\" />";
				}*/
			}
		}
		$link_info.= "</IntLinkInfos>";

		return $link_info;
	}


	/**
	* show glossary term
	*/
	function ilGlossary()
	{
		global $ilBench;

		$ilBench->start("ContentPresentation", "ilGlossary");

		//require_once("content/classes/Pages/class.ilPageObjectGUI.php");
		//require_once("content/classes/class.ilLMPageObject.php");

		require_once("content/classes/class.ilGlossaryTermGUI.php");
		$term_gui =& new ilGlossaryTermGUI($_GET["obj_id"]);

		// content style
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId()));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		$int_links = $term_gui->getInternalLinks();
		$link_xml = $this->getLinkXML($int_links, $this->getLayoutLinkTargets());
		$term_gui->setLinkXML($link_xml);

		$term_gui->output();

		$ilBench->stop("ContentPresentation", "ilGlossary");
	}

	/**
	* output media 
	*/
	function ilMedia()
	{
		global $ilBench;

		$ilBench->start("ContentPresentation", "ilMedia");

		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId()));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("PAGETITLE", " - ".$this->lm->getTitle());
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->setCurrentBlock("ilMedia");

		//$int_links = $page_object->getInternalLinks();
		$med_links = ilMediaItem::_getMapAreasIntLinks($_GET["mob_id"]);
		$link_xml = $this->getLinkXML($med_links, $this->getLayoutLinkTargets());
//echo "<br><br>".htmlentities($link_xml);
		require_once("content/classes/Media/class.ilObjMediaObject.php");
		$media_obj =& new ilObjMediaObject($_GET["mob_id"]);
		if (!empty ($_GET["pg_id"]))
		{
			require_once("content/classes/Pages/class.ilPageObject.php");
			$pg_obj =& new ilPageObject($this->lm->getType(), $_GET["pg_id"]);
			$pg_obj->buildDom();

			$xml = "<dummy>";
			// todo: we get always the first alias now (problem if mob is used multiple
			// times in page)
			$xml.= $pg_obj->getMediaAliasElement($_GET["mob_id"]);
			$xml.= $media_obj->getXML(IL_MODE_OUTPUT);
			$xml.= $link_xml;
			$xml.="</dummy>";
		}
		else
		{
			$xml = "<dummy>";
			// todo: we get always the first alias now (problem if mob is used multiple
			// times in page)
			$xml.= $media_obj->getXML(IL_MODE_ALIAS);
			$xml.= $media_obj->getXML(IL_MODE_OUTPUT);
			$xml.= $link_xml;
			$xml.="</dummy>";
		}

//echo htmlentities($xml); exit;

		// todo: utf-header should be set globally
		//header('Content-type: text/html; charset=UTF-8');

		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		$xh = xslt_create();

//echo "<b>XML:</b>".htmlentities($xml);
		// determine target frames for internal links
		//$pg_frame = $_GET["frame"];
		$wb_path = ilUtil::getWebspaceDir("output");
//		$wb_path = "../".$this->ilias->ini->readVariable("server","webspace_dir");
		$mode = ($_GET["cmd"] == "fullscreen")
			? "fullscreen"
			: "media";
		$enlarge_path = ilUtil::getImagePath("enlarge.gif");
		$fullscreen_link = "lm_presentation.php?cmd=fullscreen".
			"&ref_id=".$this->lm->getRefId();

		$params = array ('mode' => $mode, 'enlarge_path' => $enlarge_path,
			'link_params' => "ref_id=".$this->lm->getRefId(),'fullscreen_link' => $fullscreen_link,
			'ref_id' => $this->lm->getRefId(), 'pg_frame' => $pg_frame, 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$this->tpl->setVariable("MEDIA_CONTENT", $output);

		$ilBench->stop("ContentPresentation", "ilMedia");
	}


	/**
	* inserts sequential learning module navigation
	* at template variable LMNAVIGATION_CONTENT
	*/
	function ilLMNavigation()
	{
		global $ilBench;

		$ilBench->start("ContentPresentation", "ilLMNavigation");

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

		$ilBench->start("ContentPresentation", "ilLMNavigation_fetchSuccessor");
		$succ_node = $lmtree->fetchSuccessorNode($page_id, "pg");
		$ilBench->stop("ContentPresentation", "ilLMNavigation_fetchSuccessor");

		$succ_str = ($succ_node !== false)
			? " -> ".$succ_node["obj_id"]."_".$succ_node["type"]
			: "";

		$ilBench->start("ContentPresentation", "ilLMNavigation_fetchPredecessor");
		$pre_node = $lmtree->fetchPredecessorNode($page_id, "pg");
		$ilBench->stop("ContentPresentation", "ilLMNavigation_fetchPredecessor");

		$pre_str = ($pre_node !== false)
			? $pre_node["obj_id"]."_".$pre_node["type"]." -> "
			: "";

		// determine target frame
		$framestr = (!empty($_GET["frame"]))
			? "frame=".$_GET["frame"]."&"
			: "";


                // Determine whether the view of a learning resource should
                // be shown in the frameset of ilias, or in a separate window.
                $showViewInFrameset = $this->ilias->ini->readVariable("layout","view_target") == "frame";

		if($pre_node != "")
		{
			$ilBench->start("ContentPresentation", "ilLMNavigation_outputPredecessor");
			$this->tpl->setCurrentBlock("ilLMNavigation_Prev");

			// get page object
			//$ilBench->start("ContentPresentation", "ilLMNavigation_getPageObject");
			//$pre_page =& new ilLMPageObject($this->lm, $pre_node["obj_id"]);
			//$pre_page->setLMId($this->lm->getId());
			//$ilBench->stop("ContentPresentation", "ilLMNavigation_getPageObject");

			// get presentation title
			$ilBench->start("ContentPresentation", "ilLMNavigation_getPresentationTitle");
			$pre_title = ilLMPageObject::_getPresentationTitle($pre_node["obj_id"],
				$this->lm->getPageHeader(), $this->lm->isActiveNumbering());
			$prev_img = "<img src=\"".ilUtil::getImagePath("nav_arr_L.gif")."\" border=\"0\"/>";
			if (!$this->lm->cleanFrames())
			{
				$output = "<a href=\"lm_presentation.php?".$framestr."cmd=layout&obj_id=".
					$pre_node["obj_id"]."&ref_id=".$this->lm->getRefId().
					"\">$prev_img ".ilUtil::shortenText($pre_title, 50, true)."</a>";
			}
			else if ($showViewInFrameset)
			{
				$output = "<a href=\"lm_presentation.php?cmd=layout&obj_id=".
					$pre_node["obj_id"]."&ref_id=".$this->lm->getRefId().
					"\" target=\"bottom\">$prev_img ".ilUtil::shortenText($pre_title, 50, true)."</a>";
			}
			else
			{
				$output = "<a href=\"lm_presentation.php?cmd=layout&obj_id=".
					$pre_node["obj_id"]."&ref_id=".$this->lm->getRefId().
					"\" target=\"_top\">$prev_img ".ilUtil::shortenText($pre_title, 50, true)."</a>";
			}
			$ilBench->stop("ContentPresentation", "ilLMNavigation_getPresentationTitle");

			$this->tpl->setVariable("LMNAVIGATION_PREV", $output);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("ilLMNavigation_Prev2");
			$this->tpl->setVariable("LMNAVIGATION_PREV2", $output);
			$this->tpl->parseCurrentBlock();
			$ilBench->stop("ContentPresentation", "ilLMNavigation_outputPredecessor");
		}
		if($succ_node != "")
		{
			$ilBench->start("ContentPresentation", "ilLMNavigation_outputSuccessor");
			$this->tpl->setCurrentBlock("ilLMNavigation_Next");

			// get page object
			//$ilBench->start("ContentPresentation", "ilLMNavigation_getPageObject");
			//$succ_page =& new ilLMPageObject($this->lm, $succ_node["obj_id"]);
			//$ilBench->stop("ContentPresentation", "ilLMNavigation_getPageObject");
			//$succ_page->setLMId($this->lm->getId());

			// get presentation title
			$ilBench->start("ContentPresentation", "ilLMNavigation_getPresentationTitle");
			$succ_title = ilLMPageObject::_getPresentationTitle($succ_node["obj_id"],
			$this->lm->getPageHeader(), $this->lm->isActiveNumbering());
			$succ_img = "<img src=\"".ilUtil::getImagePath("nav_arr_R.gif")."\" border=\"0\"/>";
			if (!$this->lm->cleanFrames())
			{
				$output = " <a href=\"lm_presentation.php?".$framestr."cmd=layout&obj_id=".
					$succ_node["obj_id"]."&ref_id=".$this->lm->getRefId().
					"\">".ilUtil::shortenText($succ_title,50,true)." $succ_img</a>";
			}
			else if ($showViewInFrameset)
			{
				$output = " <a href=\"lm_presentation.php?cmd=layout&obj_id=".
					$succ_node["obj_id"]."&ref_id=".$this->lm->getRefId().
					"\" target=\"bottom\">".ilUtil::shortenText($succ_title,50,true)." $succ_img</a>";
			}
			else
			{
				$output = " <a href=\"lm_presentation.php?cmd=layout&obj_id=".
					$succ_node["obj_id"]."&ref_id=".$this->lm->getRefId().
					"\" target=\"_top\">".ilUtil::shortenText($succ_title,50,true)." $succ_img</a>";
			}
			$ilBench->stop("ContentPresentation", "ilLMNavigation_getPresentationTitle");

			$this->tpl->setVariable("LMNAVIGATION_NEXT", $output);
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("ilLMNavigation_Next2");
			$this->tpl->setVariable("LMNAVIGATION_NEXT2", $output);
			$this->tpl->parseCurrentBlock();
			$ilBench->stop("ContentPresentation", "ilLMNavigation_outputSuccessor");
		}

		$ilBench->stop("ContentPresentation", "ilLMNavigation");
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
						unset($attributes["template"]);
						unset($attributes["template_location"]);
						$attributes["src"] = "lm_presentation.php?ref_id=".$this->lm->getRefId().
							"&cmd=layout&frame=".$attributes["name"]."&obj_id=".$_GET["obj_id"];
						$a_content .= $this->buildTag("", "frame", $attributes);
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
					unset($attributes["template"]);
					unset($attributes["template_location"]);
					$attributes["src"] = "lm_presentation.php?ref_id=".$this->lm->getRefId().
						"&cmd=layout&frame=".$attributes["name"]."&obj_id=".$_GET["obj_id"];
					$a_content .= $this->buildTag("", "frame", $attributes);
					//$a_content .= "<frame name=\"".$attributes["name"]."\" ".
					//	"src=\"lm_presentation.php?ref_id=".$this->lm->getRefId()."&cmd=layout&frame=".$attributes["name"]."&obj_id=".$_GET["obj_id"]."\" />\n";
				}
			}
		}
	}

	/**
	* generate a tag with given name and attributes
	*
	* @param	string		"start" | "end" | "" for starting or ending tag or complete tag
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

		if ($type == "")
			$tag.= "/";

		$tag.= ">\n";

		return $tag;
	}


	/**
	* table of contents
	*/
	function showTableOfContents()
	{
		global $ilBench;

		$ilBench->start("ContentPresentation", "TableOfContents");

		//$this->tpl = new ilTemplate("tpl.lm_toc.html", true, true, true);
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId()));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("PAGETITLE", " - ".$this->lm->getTitle());
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.lm_toc.html", true);

		// set title header
		$this->tpl->setVariable("HEADER", $this->lm->getTitle());

		include_once ("content/classes/class.ilLMTableOfContentsExplorer.php");
		$exp = new ilTableOfContentsExplorer("lm_presentation.php?ref_id=".$_GET["ref_id"]
			, $this->lm);
		$exp->setTargetGet("obj_id");

		$tree =& $this->lm->getTree();
		if ($_GET["lmtocexpand"] == "")
		{
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET["lmtocexpand"];
		}

		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setVariable("EXPLORER", $output);
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();

		$ilBench->stop("ContentPresentation", "TableOfContents");
	}

	/**
	* table of contents
	*/
	function showPrintViewSelection()
	{
		global $ilBench;

		require_once("content/classes/class.ilStructureObject.php");

		$ilBench->start("ContentPresentation", "PrintViewSelection");

		//$this->tpl = new ilTemplate("tpl.lm_toc.html", true, true, true);
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId()));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("PAGETITLE", " - ".$this->lm->getTitle());
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.lm_print_selection.html", true);

		// set title header
		$this->tpl->setVariable("HEADER", $this->lm->getTitle());
		$this->tpl->setVariable("TXT_SHOW_PRINT", $this->lng->txt("cont_show_print_view"));
		$this->tpl->setVariable("TXT_BACK", $this->lng->txt("back"));
		$this->tpl->setVariable("LINK_BACK",
			"lm_presentation.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]);

		$this->tpl->setVariable("FORMACTION", "lm_presentation.php?ref_id=".$_GET["ref_id"]
			."&obj_id=".$_GET["obj_id"]."&cmd=post");

		$tree = $this->lm->getLMTree();
		$nodes = $tree->getSubtree($tree->getNodeData($tree->getRootId()));

		if (!is_array($_POST["item"]))
		{
			if ($_GET["obj_id"] != "")
			{
				$_POST["item"][$_GET["obj_id"]] = "y";
			}
			else
			{
				$_POST["item"][1] = "y";
			}
		}

		foreach ($nodes as $node)
		{

			// indentation
			for ($i=0; $i<$node["depth"]; $i++)
			{
				$this->tpl->setCurrentBlock("indent");
				$this->tpl->setVariable("IMG_BLANK", ilUtil::getImagePath("browser/blank.gif"));
				$this->tpl->parseCurrentBlock();
			}

			// output title
			$this->tpl->setCurrentBlock("lm_item");

			switch ($node["type"])
			{
				// page
				case "pg":
					$this->tpl->setVariable("TXT_TITLE",
						ilLMPageObject::_getPresentationTitle($node["obj_id"],
						$this->lm->getPageHeader(), $this->lm->isActiveNumbering()));
					$this->tpl->setVariable("IMG_TYPE", ilUtil::getImagePath("icon_pg.gif"));
					break;

				// learning module
				case "du":
					$this->tpl->setVariable("TXT_TITLE", "<b>".$this->lm->getTitle()."</b>");
					$this->tpl->setVariable("IMG_TYPE", ilUtil::getImagePath("icon_lm.gif"));
					break;

				// chapter
				case "st":
					/*
					$this->tpl->setVariable("TXT_TITLE", "<b>".
						ilStructureObject::_getPresentationTitle($node["obj_id"],
						$this->lm->getPageHeader(), $this->lm->isActiveNumbering())
						."</b>");*/
					$this->tpl->setVariable("TXT_TITLE", "<b>".
						ilStructureObject::_getPresentationTitle($node["obj_id"],
						$this->lm->isActiveNumbering())
						."</b>");
					$this->tpl->setVariable("IMG_TYPE", ilUtil::getImagePath("icon_st.gif"));
					break;
			}

			$this->tpl->setVariable("ITEM_ID", $node["obj_id"]);
			if ($_POST["item"][$node["obj_id"]] == "y")
			{
				$this->tpl->setVariable("CHECKED", "checked=\"1\"");
			}

			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->show();

		$ilBench->stop("ContentPresentation", "PrintViewSelection");
	}

	/**
	* show print view
	*/
	function showPrintView()
	{
		global $ilBench;

		$ilBench->start("ContentPresentation", "PrintView");

		$this->tpl->setVariable("PAGETITLE", " - ".$this->lm->getTitle());
		$this->tpl->setVariable("LOCATION_STYLESHEET",ilObjStyleSheet::getContentPrintStyle());

		// content style
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->lm->getStyleSheetId()));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

		//$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.lm_print_view.html", true);

		// set title header
		$this->tpl->setVariable("HEADER", $this->lm->getTitle());

		$tree = $this->lm->getLMTree();
		$nodes = $tree->getSubtree($tree->getNodeData($tree->getRootId()));

		require_once("content/classes/Pages/class.ilPageObjectGUI.php");
		require_once("content/classes/class.ilLMPageObject.php");
		require_once("content/classes/class.ilStructureObject.php");

		$act_level = 99999;
		$activated = false;

		$glossary_links = array();
		$output_header = false;
		foreach ($nodes as $node)
		{

			// print all subchapters/subpages if higher chapter
			// has been selected
			if ($node["depth"] <= $act_level)
			{
				if ($_POST["item"][$node["obj_id"]] == "y")
				{
					$act_level = $node["depth"];
					$activated = true;
				}
				else
				{
					$act_level = 99999;
					$activated = false;
				}
			}

			if ($activated)
			{
				// output learning module header
				if ($node["type"] == "du")
				{
					$output_header = true;
				}

				// output chapter title
				if ($node["type"] == "st")
				{
					$chap =& new ilStructureObject($this->lm, $node["obj_id"]);
					$this->tpl->setCurrentBlock("print_chapter");

					$chapter_title = $chap->_getPresentationTitle($node["obj_id"],
						$this->lm->isActiveNumbering());
					$this->tpl->setVariable("CHAP_TITLE",
						$chapter_title);

					/*
					if ($chap->getDescription() != "none" &&
						$chap->getDescription() != "")
					{
						$chap->initMeta();
						$meta =& $chap->getMetaData();
						$this->tpl->setVariable("CHAP_DESCRIPTION", $meta->getDescription());
					}*/
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("print_block");
					$this->tpl->parseCurrentBlock();
				}

				// output page
				if ($node["type"] == "pg")
				{
					$this->tpl->setCurrentBlock("print_item");
					$page_id = $node["obj_id"];

					$page_object =& new ilPageObject($this->lm->getType(), $page_id);
					//$page_object->buildDom();
					$page_object_gui =& new ilPageObjectGUI($page_object);

					$lm_pg_obj =& new ilLMPageObject($this->lm, $page_id);
					$lm_pg_obj->setLMId($this->lm->getId());

					// determine target frames for internal links
					$page_object_gui->setLinkFrame($_GET["frame"]);
					$page_object_gui->setOutputMode("print");

					$page_object_gui->setPresentationTitle("");
					if ($this->lm->getPageHeader() == IL_PAGE_TITLE)
					{
						$page_title = ilLMPageObject::_getPresentationTitle($lm_pg_obj->getId(),
								$this->lm->getPageHeader(), $this->lm->isActiveNumbering());

						// prevent page title after chapter title
						// that have the same content
						if ($this->lm->isActiveNumbering())
						{
							$chapter_title = trim(substr($chapter_title,
								strpos($chapter_title, " ")));
						}

						if ($page_title != $chapter_title)
						{
							$page_object_gui->setPresentationTitle($page_title);
						}
					}

					$page_content = $page_object_gui->showPage();
					if ($this->lm->getPageHeader() != IL_PAGE_TITLE)
					{
						$this->tpl->setVariable("CONTENT", $page_content);
					}
					else
					{
						$this->tpl->setVariable("CONTENT", $page_content."<br />");
					}
					$chapter_title = "";
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("print_block");
					$this->tpl->parseCurrentBlock();

					// get internal links
					$int_links = ilInternalLink::_getTargetsOfSource($this->lm->getType().":pg", $node["obj_id"]);

					$got_mobs = false;

					foreach ($int_links as $key => $link)
					{
						if ($link["type"] == "git" &&
							($link["inst"] == IL_INST_ID || $link["inst"] == 0))
						{
							$glossary_links[$key] = $link;
						}
						if ($link["type"] == "mob" &&
							($link["inst"] == IL_INST_ID || $link["inst"] == 0))
						{
							$got_mobs = true;
							$mob_links[$key] = $link;
						}
					}

					// this is not cool because of performance reasons
					// unfortunately the int link table does not
					// store the target frame (we want to append all linked
					// images but not inline images (i.e. mobs with no target
					// frame))
					if ($got_mobs)
					{
						$page_object->buildDom();
					}
				}
			}
		}

		$annex_cnt = 0;
		$annexes = array();

		// glossary
		if (count($glossary_links) > 0)
		{
			require_once("content/classes/class.ilGlossaryTerm.php");
			require_once("content/classes/class.ilGlossaryDefinition.php");

			// sort terms
			$terms = array();
			foreach($glossary_links as $key => $link)
			{
				$term = ilGlossaryTerm::_lookGlossaryTerm($link["id"]);
				$terms[$term.":".$key] = $link;
			}
			ksort($terms);

			foreach($terms as $key => $link)
			{
				$defs = ilGlossaryDefinition::getDefinitionList($link["id"]);
				$def_cnt = 1;

				// output all definitions of term
				foreach($defs as $def)
				{
					// definition + number, if more than 1 definition
					if (count($defs) > 1)
					{
						$this->tpl->setCurrentBlock("def_title");
						$this->tpl->setVariable("TXT_DEFINITION",
							$this->lng->txt("cont_definition")." ".($def_cnt++));
						$this->tpl->parseCurrentBlock();
					}
					$page =& new ilPageObject("gdf", $def["id"]);
					$page_gui =& new ilPageObjectGUI($page);
					$page_gui->setTemplateOutput(false);
					$page_gui->setOutputMode("print");

					$this->tpl->setCurrentBlock("definition");
					$output = $page_gui->showPage();
					$this->tpl->setVariable("VAL_DEFINITION", $output);
					$this->tpl->parseCurrentBlock();
				}

				// output term
				$this->tpl->setCurrentBlock("term");
				$this->tpl->setVariable("VAL_TERM",
					$term = ilGlossaryTerm::_lookGlossaryTerm($link["id"]));
				$this->tpl->parseCurrentBlock();
			}

			// output glossary header
			$annex_cnt++;
			$this->tpl->setCurrentBlock("glossary");
			$annex_title = $this->lng->txt("cont_annex")." ".
				chr(64+$annex_cnt).": ".$this->lng->txt("glo");
			$this->tpl->setVariable("TXT_GLOSSARY", $annex_title);
			$this->tpl->parseCurrentBlock();

			$annexes[] = $annex_title;
		}

		// output learning module title and toc
		if ($output_header)
		{
			$this->tpl->setCurrentBlock("print_header");
			$this->tpl->setVariable("LM_TITLE", $this->lm->getTitle());
			if ($this->lm->getDescription() != "none")
			{
				$this->lm->initMeta();
				$meta =& $this->lm->getMetaData();
				$this->tpl->setVariable("LM_DESCRIPTION", $meta->getDescription());
			}
			$this->tpl->parseCurrentBlock();

			// output toc
			$nodes2 = $nodes;
			foreach ($nodes2 as $node2)
			{
				if ($node2["type"] == "st")
				{
					for ($j=1; $j < $node2["depth"]; $j++)
					{
						$this->tpl->setCurrentBlock("indent");
						$this->tpl->setVariable("IMG_BLANK", ilUtil::getImagePath("browser/blank.gif"));
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("toc_entry");
					$this->tpl->setVariable("TXT_TOC_TITLE",
						ilStructureObject::_getPresentationTitle($node2["obj_id"],
						$this->lm->isActiveNumbering()));
					$this->tpl->parseCurrentBlock();
				}
			}

			// annexes
			foreach ($annexes as $annex)
			{
				$this->tpl->setCurrentBlock("indent");
				$this->tpl->setVariable("IMG_BLANK", ilUtil::getImagePath("browser/blank.gif"));
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("toc_entry");
				$this->tpl->setVariable("TXT_TOC_TITLE", $annex);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("toc");
			$this->tpl->setVariable("TXT_TOC", $this->lng->txt("cont_toc"));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("print_start_block");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->show(false);

		$ilBench->stop("ContentPresentation", "PrintView");
	}

	// PRIVATE METHODS
	function setSessionVars()
	{
		if($_POST["action"] == "show" or $_POST["action"] == "show_citation")
		{
			if($_POST["action"] == "show_citation")
			{
				// ONLY ONE EDITION
				if(count($_POST["target"]) != 1)
				{
					sendInfo($this->lng->txt("cont_citation_err_one"));
					$_POST["action"] = "";
					$_POST["target"] = 0;
					return false;
				}
				$_SESSION["citation"] = 1;
			}
			else
			{
				unset($_SESSION["citation"]);
			}
			if(isset($_POST["tr_id"]))
			{
				$_SESSION["tr_id"] = $_POST["tr_id"][0];
			}
			else
			{
				unset($_SESSION["tr_id"]);
			}
			if(is_array($_POST["target"]))
			{
				$_SESSION["bib_id"] = ",".implode(',',$_POST["target"]).",";
			}
			else
			{
				$_SESSION["bib_id"] = ",0,";
			}
		}
		return true;
	}

	/**
	* download file of file lists
	*/
	function downloadFile()
	{
		$file = explode("_", $_GET["file_id"]);
		require_once("classes/class.ilObjFile.php");
		$fileObj =& new ilObjFile($file[count($file) - 1], false);
		$fileObj->sendFile();
		exit;
	}

	/**
	* download source code paragraph
	*/
	function download_paragraph ()
	{
		require_once("content/classes/Pages/class.ilPageObject.php");
		$pg_obj =& new ilPageObject($this->lm->getType(), $_GET["pg_id"]);
		$pg_obj->send_paragraph ($_GET["par_id"], $_GET["downloadtitle"]);
	}
}
?>
