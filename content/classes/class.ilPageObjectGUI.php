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

require_once("./content/classes/class.ilLMObjectGUI.php");

/**
* Class ilPageObjectGUI
*
* User Interface for Page Objects Editing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilPageObjectGUI extends ilLMObjectGUI
{
	var $obj;
	var $lm_obj;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageObjectGUI(&$a_lm_object)
	{
		global $ilias, $tpl, $lng;

		parent::ilLMObjectGUI();
		$this->lm_obj =& $a_lm_object;

	}

	function setPageObject(&$a_pg_obj)
	{
		$this->obj =& $a_pg_obj;
		$this->obj->setLMId($this->lm_obj->getId());
	}

	/*
	* display content of page (edit view)
	*/
	function view()
	{
		global $tree;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.page_edit_wysiwyg.html", true);
		$num = 0;

		$this->tpl->setVariable("TXT_PG_CONTENT", $this->lng->txt("cont_pg_content"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->lm_obj->getRefId()."&obj_id=".$this->obj->getId()."&cmd=edpost");


		$builded = $this->obj->buildDom();
		$this->obj->addHierIDs();
		$content = $this->obj->getXMLFromDom(false, true);

		// convert bb code to xml
		/*
		$content = eregi_replace("\[com\]","<Comment>",$content);
		$content = eregi_replace("\[\/com\]","</Comment>",$content);
		$content = eregi_replace("\[emp]","<Emph>",$content);
		$content = eregi_replace("\[\/emp\]","</Emph>",$content);
		$content = eregi_replace("\[str]","<Strong>",$content);
		$content = eregi_replace("\[\/str\]","</Strong>",$content);*/

		if($builded !== true)
		{
			$this->displayValidationError($builded);
		}
		else
		{
			$this->displayValidationError($_SESSION["il_pg_error"]);
		}
		unset($_SESSION["il_pg_error"]);

		header('Content-type: text/html; charset=UTF-8');

		$pg_title = $this->obj->getPresentationTitle($this->lm_obj->getPageHeader());

		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
//echo "<b>XML</b>:".htmlentities($content).":<br>";
//echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
		$wb_path = "../".$this->ilias->ini->readVariable("server","webspace_dir");
		$params = array ('mode' => 'edit', 'pg_title' => $pg_title,
			'ref_id' => $this->lm_obj->getRefId(), 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$output = str_replace("&lt;","<",$output);
		$output = str_replace("&gt;",">",$output);

		$this->tpl->setVariable("PAGE_CONTENT", $output);
	}

	/*
	* preview
	*/
	function preview()
	{
		global $tree;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.page_edit_wysiwyg.html", true);
		$num = 0;

		$this->tpl->setVariable("TXT_PG_CONTENT", $this->lng->txt("cont_pg_content"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?ref_id=".
			$this->lm_obj->getRefId()."&obj_id=".$this->obj->getId()."&cmd=edpost");

		// output
		$builded = $this->obj->buildDom();
		//$this->obj->addHierIDs();
		$content = $this->obj->getXMLFromDom(false, true);

		//$content = $this->obj->getXMLContent();

		// convert bb code to xml
		//$this->obj->bbCode2XML($content);

		// todo: utf-header should be set globally
		header('Content-type: text/html; charset=UTF-8');

		$pg_title = $this->obj->getPresentationTitle($this->lm_obj->getPageHeader());

		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
//echo "<b>XML</b>:".htmlentities($content).":<br>";
//echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
		$wb_path = "../".$this->ilias->ini->readVariable("server","webspace_dir");
		$params = array ('mode' => 'preview', 'pg_title' => $pg_title,
			'ref_id' => $this->lm_obj->getRefId(), 'webspace_path' => $wb_path);
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$output = str_replace("&lt;","<",$output);
		$output = str_replace("&gt;",">",$output);
//echo "<b>HTML</b>".htmlentities($output);
		$this->tpl->setVariable("PAGE_CONTENT", $output);
	}


	function edit()
	{
		//
	}

	/**
	* output a cell in object list
	*/
	function add_cell($val, $link = "")
	{
		if(!empty($link))
		{
			$this->tpl->setCurrentBlock("begin_link");
			$this->tpl->setVariable("LINK_TARGET", $link);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("end_link");
		}

		$this->tpl->setCurrentBlock("text");
		$this->tpl->setVariable("TEXT_CONTENT", $val);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("table_cell");
		$this->tpl->parseCurrentBlock();
	}


	function save()
	{
		// create new object
		$meta_gui =& new ilMetaDataGUI();
		$meta_data =& $meta_gui->create();
		$this->obj =& new ilPageObject();
		$this->obj->assignMetaData($meta_data);
		$this->obj->setType($_GET["new_type"]);
		$this->obj->setLMId($this->lm_obj->getId());
		$this->obj->create();

		// obj_id is empty, if page is created from "all pages" screen
		// -> a free page is created (not in the tree)
		if (empty($_GET["obj_id"]))
		{
			header("location: lm_edit.php?cmd=pages&ref_id=".$this->lm_obj->getRefId());
		}
		else
		{
			$this->putInTree();
			header("location: lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId()."&obj_id=".
				$_GET["obj_id"]);
		}
	}

	function displayValidationError($a_error)
	{
		if(is_array($a_error))
		{
			$error_str = "<b>Validation Error(s):</b><br>";
			foreach ($a_error as $error)
			{
				$err_mess = implode($error, " - ");
				if (!is_int(strpos($err_mess, ":0:")))
				{
					$error_str .= htmlentities($err_mess)."<br />";
				}
			}
			$this->tpl->setVariable("MESSAGE", $error_str);
		}
	}
}
?>
