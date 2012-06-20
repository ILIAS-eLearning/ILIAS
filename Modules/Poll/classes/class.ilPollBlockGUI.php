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
		
		
		// vote
		
		if($this->poll_block->mayVote($ilUser->getId()))
		{		
			$this->tpl->setCurrentBlock("answer");
			foreach($a_poll->getAnswers() as $item)
			{			
				$this->tpl->setVariable("VALUE_ANSWER", $item["id"]);
				$this->tpl->setVariable("TXT_ANSWER", nl2br($item["answer"]));
				$this->tpl->parseCurrentBlock();
			}		

			$this->tpl->setVariable("TXT_QUESTION", nl2br($a_poll->getQuestion()));

			$img = $a_poll->getImageFullPath();
			if($img)
			{
				$this->tpl->setVariable("URL_IMAGE", $img);
			}

			$ilCtrl->setParameterByClass("ilobjpollgui",
					"ref_id", $this->getRefId());		
			$url = $ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjpollgui"),
						"vote");
			$ilCtrl->clearParametersByClass("ilobjpollgui");

			$this->tpl->setVariable("URL_FORM", $url);
			$this->tpl->setVariable("CMD_FORM", "vote");
			$this->tpl->setVariable("TXT_SUBMIT", $lng->txt("poll_vote"));		
		}
		
		
		// result
		
		if($this->poll_block->maySeeResults($ilUser->getId()))
		{	
			
			
		}
	}

	/**
	* Get block HTML code.
	*/
	function getHTML()
	{
		global $ilCtrl, $lng, $ilAccess, $ilUser;
		
		$this->poll_block->setRefId($this->getRefId());		
		$this->may_write = $ilAccess->checkAccess("write", "", $this->getRefId());
		$this->has_content = $this->poll_block->hasAnyContent($ilUser->getId(), $this->getRefId());
		
		if(!$this->may_write && !$this->has_content)
		{
			return "";
		}
		
		$this->setTitle($lng->txt("obj_poll"));
		$this->setData(array($this->poll_block->getPoll()));	
		
		if ($this->may_write)
		{
			$ilCtrl->setParameterByClass("ilobjpollgui",
				"ref_id", $this->getRefId());		
			$this->addBlockCommand(
				$ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjpollgui"),
					"render"),
				$lng->txt("edit"));
			$ilCtrl->clearParametersByClass("ilobjpollgui");
		}
		
		return parent::getHTML();
	}
}

?>