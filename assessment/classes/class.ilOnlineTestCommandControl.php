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
* Class OnlineTestCommandControl
*
* @author	Roland Küstermann <rku@aifb.uni-karlsruhe.de>
* @version	$Id$
*
* @package assessment
*/



class OnlineTestCommandControl extends DefaultTestCommandControl {
	
	function OnlineTestCommandControl (&$gui, &$object) {
		parent::DefaultTestCommandControl($gui, $object);
	}

	/**
	 * prepare request variables
	 */
	function prepareRequestVariables (){
		if ($_GET["sort_summary"])
		//	sort summary: click on title to sort in summary
			$_POST["cmd"]["summary"]="1";

		if ($_POST["cmd"]["cancel_show_answers"]) {
		// cancel_show_answers: click on back in show_answer view
			if ($this->isTestAccessible()) 
			{	// everythings ok goto summary
				$_POST["cmd"]["summary"]="1";
			} 
				else 
			{
				$_POST["cmd"]["run"]="1";
				unset($_GET ["sequence"]);
			}			
		}
		
		if ($_POST["cmd"]["cancel_confirm_submit_answers"]) {
			if ($this->obj->isActiveTestSubmitted()) 
			{	// everythings ok goto summary
				$_POST["cmd"]["run"]="1";
			} 
				else 
			{
				$_POST["cmd"]["show_ansers"]="1";
				unset($_GET ["sequence"]);
			}			
			
		}
				
		if ($_POST["cmd"]["show_answers"] or $_POST["cmd"]["back"] or $_POST["cmd"]["submit_answers"] or $_POST["cmd"]["run"]) {
			unset($_GET ["sort_summary"]);			
			unset($_GET ["setsolved"]);
			if ($_POST["cmd"]["show_answers"]  or $_POST["cmd"]["submit_answers"] or $_POST["cmd"]["run"])					
				unset($_GET ["sequence"]);		
		}			
	}


	/**
	 * inherited behavior and checks access restrictions
	 */
	function onRunObjectEnter (){
		parent::onRunObjectEnter();	
		$this->checkOnlineTestAccess();					
	}	
	
	/**
	 * check access restrictions like client ip, partipating user etc. 
	 *
	 */
		
	function checkOnlineTestAccess () {
		global $ilUser;
		
		// check if user is invited to participate
		$user = $this->obj->getInvitedUsers($ilUser->getId());
		if (!is_array ($user) || count($user)!=1)
		{
				sendInfo($this->lng->txt("user_not_invited"), true);
				$path = $this->tree->getPathFull($this->obj->getRefID());
				ilUtil::redirect($this->gui->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
				exit();
		}
			
		$user = array_pop($user);
		// check if client ip is set and if current remote addr is equal to stored client-ip			
		if (strcmp($user->clientip,"")!=0 && strcmp($user->clientip,$_SERVER["REMOTE_ADDR"])!=0)
		{
			sendInfo($this->lng->txt("user_wrong_clientip"), true);
			$path = $this->tree->getPathFull($this->obj->getRefID());
			ilUtil::redirect($this->gui->getReturnLocation("cancel","../repository.php?cmd=frameset&ref_id=" . $path[count($path) - 2]["child"]));
			exit();
		}		
	}	
	
	/**
	 * handle start commands
	 */
	function handleStartCommands() {
		$val = parent::handleStartCommands();
			
		if (!$this->obj->isActiveTestSubmitted()) 
		{
			$_POST["cmd"]["summary"]="1";
		} 			
	}
	
	/**
	 * handle online test specific commands as well as standard commands
	 */
	
	function handleCommands () {
		global $ilUser;
		$return = parent::handleCommands ();				
		
		if ($return)
			return $return;
			
		if ($_POST["cmd"]["show_answers"]) {
			$this->gui->outShowAnswers(true);
			return true;
		}
		
		if ($_POST["cmd"]["submit_answers"]) {
			$this->gui->confirmSubmitAnswers();
			return true;
		}
				
		if ($_POST["cmd"]["confirm_submit_answers"]) {
			$this->obj->submit_answers($ilUser->id);
			$this->gui->outShowAnswers(false);			
			return true;
		}
		
		// set solved in summary
		if (is_numeric($_GET["set_solved"]) && is_numeric($_GET["question_id"]))		 
		{
			$this->obj->setQuestionSetSolved($_GET["set_solved"] , $_GET["question_id"], $ilUser->getId());
			$_POST["cmd"]["summary"]="summary";
		}
		
		// set solved in question
		if ($_POST["cmd"]["resetsolved"] or $_POST["cmd"]["setsolved"] && $_GET["sequence"] )		 
		{
			$value = ($_POST["cmd"]["resetsolved"])?0:1;			
			$q_id  = $this->obj->getQuestionIdFromActiveUserSequence($_GET["sequence"]);		
			$this->obj->setQuestionSetSolved($value , $q_id, $ilUser->getId());
		}
		
		
		if ($_POST["cmd"]["summary"] or isset($_GET["sort_summary"])) {
			$this->gui->outTestSummary();
			return true;
		}
		
		return false;
		
	}
		
	/*
	 * test is resumable if it is not submitted and matches inherited behaviour
	 */		
		
	function isTestResumable()  {
		if ($this->obj->isActiveTestSubmitted())
			return false;
		else return parent::isTestResumable();
	}


	/**
	 * test can show results if it is submmited and matches inherited behaviour
	 */
	function canShowTestResults () {
		return parent::canShowTestResults() && $this->obj->isActiveTestSubmitted();
	}
	
	
	/**
	 * get sequence delivers introduction page if test is submitted
	 */
	function getSequence () {
		if ($this->obj->isActiveTestSubmitted())
			return "";
		return parent::getSequence();
	} 
}
?>