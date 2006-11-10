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

include_once "./Modules/Test/classes/inc.AssessmentConstants.php";
include_once "./Modules/Test/classes/class.ilTestServiceGUI.php";

/**
* Scoring class for tests
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesTest
* @extends ilTestServiceGUI
*/
class ilTestScoringGUI extends ilTestServiceGUI
{
	
/**
* ilTestScoringGUI constructor
*
* The constructor takes the test object reference as parameter 
*
* @param object $a_object Associated ilObjTest class
* @access public
*/
  function ilTestScoringGUI($a_object)
  {
		parent::ilTestServiceGUI($a_object);
	}
	
	/**
	* Selects a participant for manual scoring
	*/
	function selectParticipant()
	{
		$this->manscoring($_POST["participants"]);
	}

	/**
	* Shows the test scoring GUI
	*
	* @param integer $active_id The acitve ID of the participant to score
	*/
	function manscoring($active_id = 0)
	{
		global $rbacsystem;

		if ((!$rbacsystem->checkAccess("read", $this->ref_id)) && (!$rbacsystem->checkAccess("write", $this->ref_id))) 
		{
			// allow only read and write access
			sendInfo($this->lng->txt("cannot_edit_test"));
			return;
		}
		if (array_key_exists("active_id", $_GET))
		{
			$active_id = $_GET["active_id"];
		}
		$pass = 0;
		if (array_key_exists("pass", $_GET))
		{
			$pass = $_GET["pass"];
		}
		
		$participants =& $this->object->getTestParticipants();
		if (count($participants) == 0)	
		{
			sendInfo($this->lng->txt("tst_participants_no"));
			return;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.il_as_tst_manual_scoring.html", "Modules/Test");
		$counter = 1;
		foreach ($participants as $user_id => $data)
		{
			$this->tpl->setCurrentBlock("participants");
			$this->tpl->setVariable("ID_PARTICIPANT", $data->active_id);
			$suffix = "";
			if ($this->object->getAnonymity())
			{
				$suffix = " " . $counter++;
			}
			if ($active_id > 0)
			{
				if ($active_id == $data->active_id)
				{
					$this->tpl->setVariable("SELECTED_PARTICIPANT", " selected=\"selected\""); 
				}
			}
			$this->tpl->setVariable("TEXT_PARTICIPANT", $this->object->userLookupFullName($user_id, FALSE, TRUE, $suffix)); 
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setVariable("PLEASE_SELECT", $this->lng->txt("please_select"));
		$this->tpl->setVariable("BUTTON_SELECT", $this->lng->txt("select"));
		$this->tpl->setVariable("TEXT_SELECT_USER", $this->lng->txt("manscoring_select_user"));
		
		if ($active_id > 0)
		{
			if ($this->object->getNrOfTries() != 1)
			{
				$overview = $this->getPassOverview($active_id, "iltestscoringgui", "manscoring");
				$this->tpl->setVariable("PASS_OVERVIEW", $overview);
			}
		}
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
	}

}

?>
