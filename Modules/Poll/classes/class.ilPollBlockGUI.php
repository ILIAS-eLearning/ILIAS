<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Block/classes/class.ilBlockGUI.php");
include_once("./Modules/Poll/classes/class.ilObjPoll.php");

/**
* BlockGUI class for polls. 
*
* @author Jörg Lützenkirchen
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPollBlockGUI: ilColumnGUI
* @ingroup ModulesPoll
*/
class ilPollBlockGUI extends ilBlockGUI
{
	static $block_type = "poll";
	
	protected $poll_block; // [ilPollBlock]
	
	/**
	* Constructor
	*/
	function __construct()
	{
		global $lng;
		
		parent::__construct();
			
		$lng->loadLanguageModule("poll");		
		$this->setRowTemplate("tpl.block.html", "Modules/Poll");
	}
		
	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}

	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function isRepositoryObject()
	{
		return true;
	}
	
	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{		
		return IL_SCREEN_SIDE;		
	}

	/**
	* Do most of the initialisation.
	*/
	function setBlock($a_block)
	{
		$this->setBlockId($a_block->getId());
		$this->poll_block = $a_block;				
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		switch ($next_class)
		{
			default:
				return $this->$cmd();
		}
	}
	
	function fillRow($a_poll)
	{		
		global $ilCtrl, $lng, $ilUser;
		
		
		// handle messages
		
		$mess = $this->poll_block->getMessage($ilUser->getId());
		if($mess)
		{
			$this->tpl->setVariable("TXT_QUESTION", $mess);
			return;
		}		
		
		
		// nested form problem
		if(!$_SESSION["il_cont_admin_panel"])
		{
			// vote

			if($this->poll_block->mayVote($ilUser->getId()))
			{		
				$this->tpl->setCurrentBlock("answer");
				foreach($a_poll->getAnswers() as $item)
				{			
					$this->tpl->setVariable("VALUE_ANSWER", $item["id"]);
					$this->tpl->setVariable("TXT_ANSWER_VOTE", nl2br($item["answer"]));
					$this->tpl->parseCurrentBlock();
				}		

				$ilCtrl->setParameterByClass("ilobjpollgui",
						"ref_id", $this->getRefId());		
				$url = $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjpollgui"),
							"vote");
				$ilCtrl->clearParametersByClass("ilobjpollgui");
				
				$url .= "#poll".$a_poll->getID();

				$this->tpl->setVariable("URL_FORM", $url);
				$this->tpl->setVariable("CMD_FORM", "vote");
				$this->tpl->setVariable("TXT_SUBMIT", $lng->txt("poll_vote"));		
			}


			// result		
			if($this->poll_block->maySeeResults($ilUser->getId()))
			{	
				if(!$this->poll_block->mayNotResultsYet($ilUser->getId()))
				{				
					$perc = $this->poll_block->getPoll()->getVotePercentages();
					$total = $perc["total"];
					$perc = $perc["perc"];

					$this->tpl->setVariable("TOTAL_ANSWERS", sprintf($lng->txt("poll_population"), $total));

					$this->tpl->setCurrentBlock("answer_result");
					foreach($a_poll->getAnswers() as $item)
					{			
						$this->tpl->setVariable("TXT_ANSWER_RESULT", nl2br($item["answer"]));
						$this->tpl->setVariable("PERC_ANSWER_RESULT", round($perc[$item["id"]]["perc"]));
						$this->tpl->parseCurrentBlock();
					}		
				}
				else 
				{							
					$rel =  ilDatePresentation::useRelativeDates();
					ilDatePresentation::setUseRelativeDates(false);
					$end = $this->poll_block->getPoll()->getVotingPeriodEnd();
					$end = ilDatePresentation::formatDate(new ilDateTime($end, IL_CAL_UNIX));
					ilDatePresentation::setUseRelativeDates($rel);
					
					$this->tpl->setVariable("TOTAL_ANSWERS", $lng->txt("poll_block_message_already_voted").
						' '.sprintf($lng->txt("poll_block_results_available_on"), $end));					
				}
			}
			else if($this->poll_block->getPoll()->hasUserVoted($ilUser->getId()))
			{
				$this->tpl->setVariable("TOTAL_ANSWERS", $lng->txt("poll_block_message_already_voted"));
			}
		}
		
				
		$this->tpl->setVariable("ANCHOR_ID", $a_poll->getID());
		$this->tpl->setVariable("TXT_QUESTION", nl2br(trim($a_poll->getQuestion())));
		
		$desc = trim($a_poll->getDescription());
		if($desc)
		{
			$this->tpl->setVariable("TXT_DESC", nl2br($desc));
		}

		$img = $a_poll->getImageFullPath();
		if($img)
		{
			$this->tpl->setVariable("URL_IMAGE", $img);
		}
	}

	/**
	* Get block HTML code.
	*/
	function getHTML()
	{
		global $ilCtrl, $lng, $ilAccess, $ilUser, $tree, $objDefinition;
		
		$this->poll_block->setRefId($this->getRefId());		
		$this->may_write = $ilAccess->checkAccess("write", "", $this->getRefId());
		$this->has_content = $this->poll_block->hasAnyContent($ilUser->getId(), $this->getRefId());
		
		if(!$this->may_write && !$this->has_content)
		{
			return "";
		}
		
		$poll_obj = $this->poll_block->getPoll();
		$this->setTitle($poll_obj->getTitle());
		$this->setData(array($poll_obj));	
	
		if ($this->may_write)
		{
			// edit
			$ilCtrl->setParameterByClass("ilobjpollgui",
				"ref_id", $this->getRefId());		
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjpollgui"),
					"render"),
				$lng->txt("edit_content"));
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjpollgui"),
					"edit"),
				$lng->txt("settings"));
			
			/* delete (#10993 - see ilBlockGUI)			
			$parent_id = $tree->getParentId($this->getRefId());			
			$type = ilObject::_lookupType($parent_id, true);
			$class = $objDefinition->getClassName($type);
			if($class)
			{
				$class = "ilobj".strtolower($class)."gui";
				$ilCtrl->setParameterByClass($class, "ref_id", $parent_id);		
				$ilCtrl->setParameterByClass($class, "item_ref_id", $this->getRefId());	
				$this->addBlockCommand(
					$ilCtrl->getLinkTargetByClass($class, "delete"),
					$lng->txt("delete"));	
			}			 
			*/
			
			$ilCtrl->clearParametersByClass("ilobjpollgui");
		}
		
		return parent::getHTML();
	}
}

?>