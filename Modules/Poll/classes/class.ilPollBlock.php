<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Block/classes/class.ilCustomBlock.php");

/**
* Custom block for polls
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*/
class ilPollBlock extends ilCustomBlock
{
	protected $poll; // [ilObjPoll]
	protected $answers; // [array]
	protected $visible; // [bool]
	protected $active; // [bool]
	
	/**
	 * Set ref id (needed for poll access)
	 * 
	 * @param int $a_id 
	 */
	public function setRefId($a_id)
	{						
		include_once "Modules/Poll/classes/class.ilObjPoll.php";
		$this->poll = new ilObjPoll($a_id, true);
		$this->answers = $this->poll->getAnswers();
	}
	
	/**
	 * Get poll object
	 * 
	 * @return ilObjPoll 
	 */
	public function getPoll()
	{
		return $this->poll;
	}
	
	/**
	 * Check if user will see any content (vote/result)
	 * 
	 * @param int $a_user_id
	 * @return boolean 
	 */
	public function hasAnyContent($a_user_id, $a_ref_id)
	{		
		if(!sizeof($this->answers))
		{
			return false;
		}
		
		include_once "Modules/Poll/classes/class.ilObjPollAccess.php";
		$this->active = ilObjPollAccess::_isActivated($a_ref_id);
		if(!$this->active)
		{
			return false;
		}
		
		if(!$this->mayVote($a_user_id) &&
			!$this->maySeeResults($a_user_id))
		{
			return false;
		}
		
		return true;
	}
	
	public function mayVote($a_user_id)
	{
		if(!$this->active)
		{
			return false;
		}
		
		if($a_user_id == ANONYMOUS_USER_ID)
		{
			return false;
		}
		
		if($this->poll->hasUserVoted($a_user_id))
		{
			return false;						
		}
		
		if($this->poll->getVotingPeriod() && 
			($this->poll->getVotingPeriodBegin() > time() ||
			$this->poll->getVotingPeriodEnd() < time()))
		{
			return false;
		}
		
		return true;
	}
	
	public function mayNotResultsYet()
	{
		if($this->poll->getViewResults() == ilObjPoll::VIEW_RESULTS_AFTER_PERIOD &&
			$this->poll->getVotingPeriod() &&
			$this->poll->getVotingPeriodEnd() > time())
		{
			return true;
		}
		return false;	
	}
	
	public function maySeeResults($a_user_id)
	{
		if(!$this->active)
		{
			return false;
		}
		
		switch($this->poll->getViewResults())
		{
			case ilObjPoll::VIEW_RESULTS_NEVER:
				return false;
				
			case ilObjPoll::VIEW_RESULTS_ALWAYS:
				// fallthrough
				
			// #12023 - see mayNotResultsYet()
			case ilObjPoll::VIEW_RESULTS_AFTER_PERIOD:		
				return true;
				
			case ilObjPoll::VIEW_RESULTS_AFTER_VOTE:			
				if($this->poll->hasUserVoted($a_user_id))
				{
					return true;
				}
				return false;
		}						
	}
	
	public function getMessage($a_user_id)
	{
		global $lng;
		
		if(!sizeof($this->answers))
		{
			return $lng->txt("poll_block_message_no_answers");
		}
		
		if(!$this->active)
		{
			if(!$this->poll->isOnline())
			{
				return $lng->txt("poll_block_message_offline");
			}
			if($this->poll->getAccessBegin() > time())
			{
				$date = ilDatePresentation::formatDate(new ilDateTime($this->poll->getAccessBegin(), IL_CAL_UNIX));
				return sprintf($lng->txt("poll_block_message_inactive"), $date);
			}
		}
	}

	/**
	 * Show Results as (Barchart or Piechart)
	 * @return int
	 *
	 */
	public function showResultsAs()
	{
		return $this->poll->getShowResultsAs();
	}

	/**
	 * Are Comments enabled or disabled
	 * @return bool
	 */
	public function showComments()
	{
		return $this->poll->getShowComments();
	}
}

?>