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

/**
* Class ilObjDlBookGUI
*
* @author Databay AG <ay@databay.de>
* @version $Id$
*
* @package content
*/

require_once "classes/class.ilObjectGUI.php";
require_once "content/classes/class.ilObjLearningModuleGUI.php";
require_once "content/classes/class.ilObjDlBook.php";

class ilObjDlBookGUI extends ilObjLearningModuleGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjDlBookGUI($a_data,$a_id = 0,$a_call_by_reference = true, $a_prepare_output = true)
	{
        $this->type = "dbk";
		parent::ilObjContentObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);
		if($a_id != 0)
		{
			$this->lm_tree =& $this->object->getLMTree();
		}

		
	}
	
	
	/**
	*	exports the digi-lib-object into a xml structure
	*/
	function export() 
	{
		
		$this->object =& new ilObjDlBook($this->id, true);
		$this->object->export($_GET["ref_id"]);
		
		
	}
	
	function offlineexport() {
		
	}
	
	function offlineexportform() {
		
		//$tpl_offline =& new ilTemplate("tpl.");
		//vd($this->tpl);
		$this->tpl->addBlockfile("CONTENT", "offline_content", "tpl.offline_export.html", true);
		$this->tpl->touchBlock("offline_content");
		$this->tpl->setVariable("TXT_ACTION","Digilib-Book - read offline");
		
		$this->tpl->setVariable("TXT_TYPE","Export-Type");
		$this->tpl->setVariable("TXT_HTMLEXPORT","HTML export");
		$this->tpl->setVariable("TXT_PDFEXPORT","PDF export");
		
		$this->tpl->setVariable("TXT_PAGES","Pages");
		$this->tpl->setVariable("TXT_PAGESALL","all");
		$this->tpl->setVariable("TXT_PAGESCHAPTER","chapter");
		if ($_GET["obj_id"] != "") $this->tpl->setVariable("TXT_PAGESPAGE","this page");
		$this->tpl->setVariable("TXT_PAGESFROM","pages from");
		$this->tpl->setVariable("TXT_PAGESTO","to");
		
		$this->tpl->setVariable("BTN_VALUE","start export");
		
		$this->tpl->setVariable("EXPORT_ACTION","lm_presentation.php?cmd=offlineexport&ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]);
		
		$this->tpl->show();
		
	}

    function setilLMMenu()
	{
		
		include_once("./classes/class.ilTemplate.php");

		$tpl_menu =& new ilTemplate("tpl.buttons.html",true,true);
		
		$tpl_menu->setCurrentBlock("btn_cell");
		$tpl_menu->setVariable("BTN_LINK","./lm_presentation.php?cmd=export&ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]);
		$tpl_menu->setVariable("BTN_TXT","Export");
		// $tpl_menu->setVariable("BTN_TARGET","...");
		$tpl_menu->parseCurrentBlock();


		$tpl_menu->setVariable("BTN_LINK","./lm_presentation.php?cmd=offlineexportform&ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]);
		$tpl_menu->setVariable("BTN_TXT","read offline");
		// $tpl_menu->setVariable("BTN_TARGET","...");
		$tpl_menu->parseCurrentBlock();
		
		$tpl_menu->setCurrentBlock("btn_row");
		$tpl_menu->parseCurrentBlock();

		return $tpl_menu->get();
		
	}
}
?>
