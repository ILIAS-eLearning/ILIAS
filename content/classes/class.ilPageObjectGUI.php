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
	}

	/*
	* display content of page
	*/
	function view()
	{
		global $tree;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.page_edit_wysiwyg.html", true);
		$num = 0;

		$this->tpl->setVariable("TXT_PG_CONTENT", $this->lng->txt("cont_pg_content"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?lm_id=".
			$this->lm_obj->getId()."&obj_id=".$this->obj->getId()."&cmd=edpost");

		// setting to utf-8 here
		$this->obj->buildDom();
		$this->obj->addHierIDs();
		$content = $this->obj->getXMLFromDom();

		header('Content-type: text/html; charset=UTF-8');

		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
//echo "<b>XML</b>:".htmlentities($content).":<br>";
//echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
		$params = array ('mode' => 'edit');
		$output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", NULL, $args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		$this->tpl->setVariable("PAGE_CONTENT", $output);

	}


	/*
	* display content of page (wysiwyg test)
	*/
	function viewWysiwyg()
	{
		global $tree;

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.page_edit_wysiwyg.html", true);
		$num = 0;

		$this->tpl->setVariable("TXT_PG_CONTENT", $this->lng->txt("cont_pg_content"));
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?lm_id=".
			$this->lm_obj->getId()."&obj_id=".$this->obj->getId()."&cmd=edpost");


		$this->obj->buildDom();
		$this->obj->addHierIDs();
		$content = $this->obj->getXMLFromDom();

		// convert bb code to xml
		$content = eregi_replace("\[com\]","<Comment>",$content);
		$content = eregi_replace("\[\/com\]","</Comment>",$content);
		$content = eregi_replace("\[emp]","<Emph>",$content);
		$content = eregi_replace("\[\/emp\]","</Emph>",$content);
		$content = eregi_replace("\[str]","<Strong>",$content);
		$content = eregi_replace("\[\/str\]","</Strong>",$content);

		header('Content-type: text/html; charset=UTF-8');

		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
//echo "<b>XML</b>:".htmlentities($content).":<br>";
//echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
		$params = array ('mode' => 'edit');
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
		$this->tpl->setVariable("FORMACTION", "lm_edit.php?lm_id=".
			$this->lm_obj->getId()."&obj_id=".$this->obj->getId()."&cmd=edpost");

		// output
		$content = $this->obj->getXMLContent();

		// convert bb code to xml
		$this->bbCode2XML($content);

		// todo: utf-header should be set globally
		header('Content-type: text/html; charset=UTF-8');

		$xsl = file_get_contents("./content/page.xsl");
		$args = array( '/_xml' => $content, '/_xsl' => $xsl );
		$xh = xslt_create();
//echo "<b>XML</b>:".htmlentities($content).":<br>";
//echo "<b>XSLT</b>:".htmlentities($xsl).":<br>";
		$params = array ('mode' => 'preview');
		$output = xslt_process($xh,"arg:/_xml","arg:/_xsl",NULL,$args, $params);
		echo xslt_error($xh);
		xslt_free($xh);

		// unmask user html
		$output = str_replace("&lt;","<",$output);
		$output = str_replace("&gt;",">",$output);

		$this->tpl->setVariable("PAGE_CONTENT", $output);
	}

	function bbCode2XML(&$a_content)
	{
		$a_content = eregi_replace("\[com\]","<Comment>",$a_content);
		$a_content = eregi_replace("\[\/com\]","</Comment>",$a_content);
		$a_content = eregi_replace("\[emp]","<Emph>",$a_content);
		$a_content = eregi_replace("\[\/emp\]","</Emph>",$a_content);
		$a_content = eregi_replace("\[str]","<Strong>",$a_content);
		$a_content = eregi_replace("\[\/str\]","</Strong>",$a_content);
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
		$this->obj->setLMId($_GET["lm_id"]);
		$this->obj->create();

		// obj_id is empty, if page is created from "all pages" screen
		// -> a free page is created (not in the tree)
		if (empty($_GET["obj_id"]))
		{
			header("location: lm_edit.php?cmd=pages&lm_id=".$this->lm_obj->getId());
		}
		else
		{
			$this->putInTree();
			header("location: lm_edit.php?cmd=view&lm_id=".$this->lm_obj->getId()."&obj_id=".
				$_GET["obj_id"]);
		}
	}
}
?>
