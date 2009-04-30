<?php
 /*
   +----------------------------------------------------------------------------+
   | ILIAS open source                                                          |
   +----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne           |
   |                                                                            |
   | This program is free software; you can redistribute it and/or              |
   | modify it under the terms of the GNU General Public License                |
   | as published by the Free Software Foundation; either version 2             |
   | of the License, or (at your option) any later version.                     |
   |                                                                            |
   | This program is distributed in the hope that it will be useful,            |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of             |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the              |
   | GNU General Public License for more details.                               |
   |                                                                            |
   | You should have received a copy of the GNU General Public License          |
   | along with this program; if not, write to the Free Software                |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. |
   +----------------------------------------------------------------------------+
*/

/**
* Survey phrases GUI class
*
* The ilSurveyPhrases GUI class creates the GUI output for 
* survey phrases (collections of survey categories)
* of ordinal survey question types.
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version	$Id$
* @ingroup ModulesSurvey
*/
class ilSurveyPhrasesGUI
{
	var $object;
	var $gui_object;
	var $lng;
	var $tpl;
	var $ctrl;
	var $ilias;
	var $tree;
	var $ref_id;
	
/**
* ilSurveyPhrases constructor
*
* The constructor takes possible arguments an creates an instance of the ilSurveyPhrases object.
*
* @access public
*/
  function ilSurveyPhrasesGUI($a_object)
  {
		global $lng, $tpl, $ilCtrl, $ilias, $tree;

		include_once "./Modules/SurveyQuestionPool/classes/class.ilSurveyPhrases.php";
    $this->lng =& $lng;
    $this->tpl =& $tpl;
		$this->ctrl =& $ilCtrl;
		$this->ilias =& $ilias;
		$this->gui_object =& $a_object;
		$this->object = new ilSurveyPhrases();
		$this->tree =& $tree;
		$this->ref_id = $a_object->ref_id;
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd();
		$next_class = $this->ctrl->getNextClass($this);

		$cmd = $this->getCommand($cmd);
		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}
		return $ret;
	}

/**
* Retrieves the ilCtrl command
*
* Retrieves the ilCtrl command
*
* @access public
*/
	function getCommand($cmd)
	{
		return $cmd;
	}
	
/**
* Creates a confirmation form to delete personal phases from the database
*
* Creates a confirmation form to delete personal phases from the database
*
* @access public
*/
	function deletePhrase()
	{
		ilUtil::sendInfo();

		$checked_phrases = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/phrase_(\d+)/", $key, $matches))
			{
				array_push($checked_phrases, $matches[1]);
			}
		}
		if (count($checked_phrases))
		{
			ilUtil::sendInfo($this->lng->txt("qpl_confirm_delete_phrases"));
			$this->deletePhrasesForm($checked_phrases);
			return;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_delete_phrase_select_none"));
			$this->phrases();
			return;
		}
		
		$this->tpl->setCurrentBlock("obligatory");
		$this->tpl->setVariable("TEXT_OBLIGATORY", $this->lng->txt("obligatory"));
		$this->tpl->setVariable("CHECKED_OBLIGATORY", " checked=\"checked\"");
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("DEFINE_QUESTIONBLOCK_HEADING", $this->lng->txt("define_questionblock"));
		$this->tpl->setVariable("TEXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Displays a form to manage the user created phrases
	*
	* @access	public
	*/
  function phrases()
	{
		global $rbacsystem;
		
		if ($rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_phrases.html", "Modules/SurveyQuestionPool");
			$phrases =& $this->object->_getAvailablePhrases(1);
			if (count($phrases))
			{
				include_once "./Services/Utilities/classes/class.ilUtil.php";
				$colors = array("tblrow1", "tblrow2");
				$counter = 0;
				foreach ($phrases as $phrase_id => $phrase_array)
				{
					$this->tpl->setCurrentBlock("phraserow");
					$this->tpl->setVariable("PHRASE_ID", $phrase_id);
					$this->tpl->setVariable("COLOR_CLASS", $colors[$counter++ % 2]);
					$this->tpl->setVariable("PHRASE_TITLE", $phrase_array["title"]);
					$categories =& $this->object->_getCategoriesForPhrase($phrase_id);
					$this->tpl->setVariable("PHRASE_CONTENT", join($categories, ", "));
					$this->tpl->parseCurrentBlock();
				}
				$counter++;
				$this->tpl->setCurrentBlock("selectall");
				$this->tpl->setVariable("SELECT_ALL", $this->lng->txt("select_all"));
				$this->tpl->setVariable("COLOR_CLASS", $colors[$counter++ % 2]);
				$this->tpl->parseCurrentBlock();
				$this->tpl->setCurrentBlock("Footer");
				$this->tpl->setVariable("ARROW", "<img src=\"" . ilUtil::getImagePath("arrow_downright.gif") . "\" alt=\"".$this->lng->txt("arrow_downright")."\">");
				$this->tpl->setVariable("TEXT_DELETE", $this->lng->txt("delete"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("Emptytable");
				$this->tpl->setVariable("TEXT_EMPTYTABLE", $this->lng->txt("no_user_phrases_defined"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("INTRODUCTION_MANAGE_PHRASES", $this->lng->txt("introduction_manage_phrases"));
			$this->tpl->setVariable("TEXT_PHRASE_TITLE", $this->lng->txt("phrase"));
			$this->tpl->setVariable("TEXT_PHRASE_CONTENT", $this->lng->txt("categories"));
			$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("cannot_manage_phrases"));
		}
	}

	/**
	* cancel delete phrases
	*/
	function cancelDeletePhrase()
	{
		$this->ctrl->redirect($this, "phrases");
	}
	
	/**
	* confirm delete phrases
	*/
	function confirmDeletePhrase()
	{
		$phrases = array();
		foreach ($_POST as $key => $value)
		{
			if (preg_match("/phrase_(\d+)/", $key, $matches))
			{
				array_push($phrases, $matches[1]);
			}
		}
		$this->object->deletePhrases($phrases);
		ilUtil::sendSuccess($this->lng->txt("qpl_phrases_deleted"), true);
		$this->ctrl->redirect($this, "phrases");
	}
	
/**
* Creates a confirmation form to delete personal phases from the database
*
* Creates a confirmation form to delete personal phases from the database
*
* @param array $checked_phrases An array with the id's of the phrases checked for deletion
* @access public
*/
	function deletePhrasesForm($checked_phrases)
	{
		ilUtil::sendInfo();
		$phrases =& $this->object->_getAvailablePhrases(1);
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_svy_qpl_confirm_delete_phrases.html", "Modules/SurveyQuestionPool");
		$colors = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($checked_phrases as $id)
		{
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("COLOR_CLASS", $colors[$counter++ % 2]);
			$this->tpl->setVariable("PHRASE_TITLE", $phrases[$id]["title"]);
			$categories =& $this->object->_getCategoriesForPhrase($id);
			$this->tpl->setVariable("PHRASE_CONTENT", join($categories, ", "));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("hidden");
			$this->tpl->setVariable("HIDDEN_NAME", "phrase_$id");
			$this->tpl->setVariable("HIDDEN_VALUE", "1");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TEXT_PHRASE_TITLE", $this->lng->txt("phrase"));
		$this->tpl->setVariable("TEXT_PHRASE_CONTENT", $this->lng->txt("categories"));
		$this->tpl->setVariable("BTN_CONFIRM", $this->lng->txt("confirm"));
		$this->tpl->setVariable("BTN_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("FORM_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->parseCurrentBlock();
	}
}
?>
