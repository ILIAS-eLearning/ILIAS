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
* @author		Helmut Schottmüller <ilias@aurealis.de>
* @version	$Id$
* @ingroup ModulesSurveyQuestionPool
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
	* ilSurveyPhrasesGUI constructor
	*
	*/
	function __construct($a_object)
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
	public function &executeCommand()
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
	*/
	public function getCommand($cmd)
	{
		return $cmd;
	}
	
	/**
	* Creates a confirmation form to delete personal phases from the database
	*/
	public function deletePhrase()
	{
		ilUtil::sendInfo();

		$checked_phrases = $_POST['phrase'];
		if (count($checked_phrases))
		{
			ilUtil::sendQuestion($this->lng->txt("qpl_confirm_delete_phrases"));
			$this->deletePhrasesForm($checked_phrases);
			return;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("qpl_delete_phrase_select_none"));
			$this->phrases();
			return;
		}
	}

	/**
	* Displays a form to manage the user created phrases
	*
	* @access	public
	*/
	public function phrases()
	{
		global $rbacsystem;
		
		if ($rbacsystem->checkAccess("write", $this->ref_id))
		{
			include_once "./Modules/SurveyQuestionPool/classes/class.ilSurveyPhrasesTableGUI.php";
			$table_gui = new ilSurveyPhrasesTableGUI($this, 'phrases');
			$phrases =& ilSurveyPhrases::_getAvailablePhrases(1);
			$data = array();
			foreach ($phrases as $phrase_id => $phrase_array)
			{
				$categories =& ilSurveyPhrases::_getCategoriesForPhrase($phrase_id);
				array_push($data, array('phrase_id' => $phrase_id, 'phrase' => $phrase_array["title"], 'answers' => join($categories, ", ")));
			}
			$table_gui->setData($data);
			$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("cannot_manage_phrases"));
		}
	}

	/**
	* cancel delete phrases
	*/
	public function cancelDeletePhrase()
	{
		$this->ctrl->redirect($this, "phrases");
	}
	
	/**
	* confirm delete phrases
	*/
	public function confirmDeletePhrase()
	{
		$phrases = $_POST['phrase'];
		$this->object->deletePhrases($phrases);
		ilUtil::sendSuccess($this->lng->txt("qpl_phrases_deleted"), true);
		$this->ctrl->redirect($this, "phrases");
	}
	
	/**
	* Creates a confirmation form to delete personal phases from the database
	*
	* @param array $checked_phrases An array with the id's of the phrases checked for deletion
	*/
	public function deletePhrasesForm($checked_phrases)
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.ilSurveyPhrasesTableGUI.php";
		$table_gui = new ilSurveyPhrasesTableGUI($this, 'phrases', true);
		$phrases =& ilSurveyPhrases::_getAvailablePhrases(1);
		$data = array();
		foreach ($checked_phrases as $phrase_id)
		{
			$phrase_array = $phrases[$phrase_id];
			$categories =& ilSurveyPhrases::_getCategoriesForPhrase($phrase_id);
			array_push($data, array('phrase_id' => $phrase_id, 'phrase' => $phrase_array["title"], 'answers' => join($categories, ", ")));
		}
		$table_gui->setData($data);
		$this->tpl->setVariable('ADM_CONTENT', $table_gui->getHTML());	
	}
}
?>
