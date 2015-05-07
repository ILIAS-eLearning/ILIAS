<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Exercise peer review
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExPeerReview
{
	protected $assignment; // [$a_assignment]
	protected $assignment_id; // [int]
		
	public function __construct(ilExAssignment $a_assignment)
	{
		$this->assignment = $a_assignment;
		$this->assignment_id = $a_assignment->getId();
	}
	
	public function hasPeerReviewGroups()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT count(*) cnt".
			" FROM exc_assignment_peer".
			" WHERE ass_id = ".$ilDB->quote($this->assignment_id, "integer"));
		$cnt = $ilDB->fetchAssoc($set);
		return (bool)$cnt["cnt"];
	}
	
	protected function getValidPeerReviewUsers()
	{
		global $ilDB;
		
		$user_ids = array();
		
		// returned / assigned ?!
		$set = $ilDB->query("SELECT DISTINCT(user_id)".
			" FROM exc_returned".
			" WHERE ass_id = ".$ilDB->quote($this->assignment_id, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$user_ids[] = $row["user_id"];
		}
		
		return $user_ids;
	}
	
	protected function initPeerReviews()
	{
		global $ilDB;
				
		if(!$this->hasPeerReviewGroups())
		{
			$user_ids = $this->getValidPeerReviewUsers();
			
			// forever alone
			if(sizeof($user_ids) < 2)
			{
				return false;
			}
			
			$rater_ids = $user_ids;
			$matrix = array();

			$max = min(sizeof($user_ids)-1, $this->assignment->getPeerReviewMin());			
			for($loop = 0; $loop < $max; $loop++)
			{				
				$run_ids = array_combine($user_ids, $user_ids);
				
				foreach($rater_ids as $rater_id)
				{
					$possible_peer_ids = $run_ids;
					
					// may not rate himself
					unset($possible_peer_ids[$rater_id]);
					
					// already has linked peers
					if(array_key_exists($rater_id, $matrix))
					{
						$possible_peer_ids = array_diff($possible_peer_ids, $matrix[$rater_id]);
					}
					
					// #15665 / #15883 
					if(!sizeof($possible_peer_ids))
					{
						// no more possible peers left?  start over with all valid users
						$run_ids = array_combine($user_ids, $user_ids);
						
						// see above
						$possible_peer_ids = $run_ids;
						
						// may not rate himself
						unset($possible_peer_ids[$rater_id]);

						// already has linked peers
						if(array_key_exists($rater_id, $matrix))
						{
							$possible_peer_ids = array_diff($possible_peer_ids, $matrix[$rater_id]);
						}
					}
						
					// #14947 
					if(sizeof($possible_peer_ids))
					{
						$peer_id = array_rand($possible_peer_ids);
						if(!array_key_exists($rater_id, $matrix))
						{
							$matrix[$rater_id] = array();															
						}						
						$matrix[$rater_id][] = $peer_id;						
					}
					
					// remove peer_id from possible ids in this run
					unset($run_ids[$peer_id]);
				}
			}	
			
			foreach($matrix as $rater_id => $peer_ids)
			{
				foreach($peer_ids as $peer_id)
				{
					$ilDB->manipulate("INSERT INTO exc_assignment_peer".
						" (ass_id, giver_id, peer_id)".
						" VALUES (".$ilDB->quote($this->assignment_id, "integer").
						", ".$ilDB->quote($rater_id, "integer").
						", ".$ilDB->quote($peer_id, "integer").")");					
				}
			}
			
		}
		return true;
	}
	
	public function resetPeerReviewFileUploads()
	{		
		if($this->assignment->hasPeerReviewFileUpload())
		{
			include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
			$storage = new ilFSStorageExercise($this->assignment->getExerciseId(), $this->assignment_id);
			$storage->deletePeerReviewUploads();
		}
	}
	
	public function resetPeerReviews()
	{
		global $ilDB;
		
		$all = array();
		
		if($this->hasPeerReviewGroups())
		{
			// ratings					
			foreach($this->getAllPeerReviews(false) as $peer_id => $reviews)
			{
				foreach($reviews as $giver_id => $review)
				{					
					$all[] = $giver_id;
					
					ilRating::resetRatingForUserAndObject($this->assignment_id, "ass", 
						$peer_id, "peer", $giver_id);
				}
			}
			
			// files
			$this->resetPeerReviewFileUploads();
			
			// peer groups
			$ilDB->manipulate("DELETE FROM exc_assignment_peer".
				" WHERE ass_id = ".$ilDB->quote($this->assignment_id, "integer"));						
		}
		
		return $all;
	}
	
	public function validatePeerReviewGroups()
	{
		if($this->hasPeerReviewGroups())
		{			
			include_once "./Modules/Exercise/classes/class.ilExerciseMembers.php";
			$all_exc = ilExerciseMembers::_getMembers($this->assignment->getExerciseId());
			$all_valid = $this->getValidPeerReviewUsers(); // only returned
			
			$peer_ids = $invalid_peer_ids = $invalid_giver_ids = $all_reviews = array();
			foreach($this->getAllPeerReviews(false) as $peer_id => $reviews)
			{
				$peer_ids[] = $peer_id;
				
				if(!in_array($peer_id, $all_valid) ||
					!in_array($peer_id, $all_exc))
				{
					$invalid_peer_ids[] = $peer_id;
				}
				foreach($reviews as $giver_id => $review)
				{
					if(!in_array($giver_id, $all_valid) ||
						!in_array($peer_id, $all_exc))
					{
						$invalid_giver_ids[] = $giver_id;
					}
					else 
					{
						$valid = (trim($review[0]) || $review[1]);					
						$all_reviews[$peer_id][$giver_id] = $valid;						
					}
				}
			}			
			$invalid_giver_ids = array_unique($invalid_giver_ids);
			
			$missing_user_ids = array();
			foreach($all_valid as $user_id)
			{
				// a missing peer is also a missing giver
				if(!in_array($user_id, $peer_ids))
				{
					$missing_user_ids[] = $user_id;
				}
			}
			
			$not_returned_ids = array();
			foreach($all_exc as $user_id)
			{				
				if(!in_array($user_id, $all_valid))
				{
					$not_returned_ids[] = $user_id;
				}
			}
						
			return array(
				"invalid" => (sizeof($missing_user_ids) || 
					sizeof($invalid_peer_ids) || 
					sizeof($invalid_giver_ids)),
				"missing_user_ids" => $missing_user_ids, 
				"not_returned_ids" => $not_returned_ids,
				"invalid_peer_ids" => $invalid_peer_ids, 
				"invalid_giver_ids" => $invalid_giver_ids,
				"reviews" => $all_reviews);
		}
	}
	
	public function getPeerReviewsByGiver($a_user_id)
	{
		global $ilDB;
		
		$res = array();
		
		if($this->initPeerReviews())
		{			
			$idx = 0;
			$set = $ilDB->query("SELECT *".
				" FROM exc_assignment_peer".
				" WHERE giver_id = ".$ilDB->quote($a_user_id, "integer").
				" AND ass_id = ".$ilDB->quote($this->assignment_id, "integer").
				" ORDER BY peer_id");
			while($row = $ilDB->fetchAssoc($set))
			{
				$row["seq"] = ++$idx;
				$res[] = $row;
			}
		}				
		
		return $res;
	}
	
	public function getPeerMaskedId($a_giver_id, $a_peer_id)
	{
		foreach($this->getPeerReviewsByGiver($a_giver_id) as $idx => $peer)
		{
			if($peer["peer_id"] == $a_peer_id)
			{
				return $peer["seq"];
			}
		}
	}
	
	public function validatePeerReviewText($a_text)
	{
		$a_text = trim($a_text);
		$min_length = $this->assignment->getPeerReviewChars();
				
		if(!$min_length)
		{
			return (bool)strlen($a_text);
		}
		else 
		{
			include_once "Services/Utilities/classes/class.ilStr.php";
			return (ilStr::strLen($a_text) >= $min_length);
		}
	}
	
	protected function validatePeerReview(array $a_data, $a_rating = null)
	{				
		// comment
		$valid = $this->validatePeerReviewText($a_data["pcomment"]);		
		
		// if minimum chars given, review is always invalid without text (JF, 27 Apr 2015)
		if(!$valid &&
			$this->assignment->getPeerReviewChars())
		{
			return false;
		}
		
		// rating
		if(!$valid)
		{
			if($a_rating === null)
			{			
				include_once './Services/Rating/classes/class.ilRating.php';		
				$valid = (bool)round(ilRating::getRatingForUserAndObject($a_data["ass_id"], 
					"ass", $a_data["peer_id"], "peer", $a_data["giver_id"]));				
			}
			else if($a_rating)
			{
				$valid = true;
			}
		}

		// file(s) 
		if(!$valid) 
		{		
			$valid = (bool)sizeof($this->getPeerUploadFiles($a_data["peer_id"], $a_data["giver_id"]));
		}
		
		return $valid;
	}
	
	public function getPeerReviewsByPeerId($a_user_id, $a_only_valid = false)
	{
		global $ilDB;
		
		$res = array();
		
		$idx = 0;
		$set = $ilDB->query("SELECT *".
			" FROM exc_assignment_peer".
			" WHERE peer_id = ".$ilDB->quote($a_user_id, "integer").
			" AND ass_id = ".$ilDB->quote($this->assignment_id, "integer").
			" ORDER BY peer_id");
		while($row = $ilDB->fetchAssoc($set))
		{
			if(!$a_only_valid || 
				$this->validatePeerReview($row))
			{				
				// this would be correct but rather senseless
				// $row["seq"] = $this->getPeerMaskedId($row["giver_id"], $a_user_id);
				$row["seq"] = ++$idx;
				$res[] = $row;
			}
		}						
		
		return $res;
	}
	
	public function getAllPeerReviews($a_validate = true)
	{
		global $ilDB;
		
		$res = array();

		include_once './Services/Rating/classes/class.ilRating.php';
		
		$set = $ilDB->query("SELECT *".
			" FROM exc_assignment_peer".
			" WHERE ass_id = ".$ilDB->quote($this->assignment_id, "integer").
			" ORDER BY peer_id");
		while($row = $ilDB->fetchAssoc($set))
		{
			$rating = round(ilRating::getRatingForUserAndObject($this->assignment_id, 
					"ass", $row["peer_id"], "peer", $row["giver_id"]));		
			
			if(!$a_validate ||
				$this->validatePeerReview($row, $rating))
			{
				$res[$row["peer_id"]][$row["giver_id"]] = array($row["pcomment"], $rating);
			}
		}						
		
		return $res;		
	}
	
	public function hasPeerReviewAccess($a_peer_id)
	{
		global $ilDB, $ilUser;
		
		$set = $ilDB->query("SELECT ass_id".
			" FROM exc_assignment_peer".			
			" WHERE giver_id = ".$ilDB->quote($ilUser->getId(), "integer").
			" AND peer_id = ".$ilDB->quote($a_peer_id, "integer").
			" AND ass_id = ".$ilDB->quote($this->assignment_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		return (bool)$row["ass_id"];		
	}
	
	public function updatePeerReviewTimestamp($a_peer_id)
	{
		global $ilDB, $ilUser;
		
		$ilDB->manipulate("UPDATE exc_assignment_peer".
			" SET tstamp = ".$ilDB->quote(ilUtil::now(), "timestamp").
			" WHERE giver_id = ".$ilDB->quote($ilUser->getId(), "integer").
			" AND peer_id = ".$ilDB->quote($a_peer_id, "integer").
			" AND ass_id = ".$ilDB->quote($this->assignment_id, "integer"));
	}
	
	public function getPeerUploadFiles($a_peer_id, $a_giver_id)
	{
		if(!$this->assignment->hasPeerReviewFileUpload())
		{
			return array();
		}
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->assignment->getExerciseId(), $this->assignment_id);
		$path = $storage->getPeerReviewUploadPath($a_peer_id, $a_giver_id);			
		return glob($path."/*.*");			
	}
	
	public function updatePeerReviewComment($a_peer_id, $a_comment)
	{
		global $ilDB, $ilUser;
		
		$sql = "UPDATE exc_assignment_peer".
			" SET tstamp = ".$ilDB->quote(ilUtil::now(), "timestamp").
			",pcomment  = ".$ilDB->quote(trim($a_comment), "text").
			" WHERE giver_id = ".$ilDB->quote($ilUser->getId(), "integer").
			" AND peer_id = ".$ilDB->quote($a_peer_id, "integer").
			" AND ass_id = ".$ilDB->quote($this->assignment_id, "integer");
		
		$ilDB->manipulate($sql);
	}
	
	public function countGivenFeedback($a_validate = true, $a_user_id = null)
	{
		global $ilDB, $ilUser;
		
		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}
		
		$cnt = 0;
		
		include_once './Services/Rating/classes/class.ilRating.php';
		
		$set = $ilDB->query("SELECT *".
			" FROM exc_assignment_peer".
			" WHERE ass_id = ".$ilDB->quote($this->assignment_id, "integer").
			" AND giver_id = ".$ilDB->quote($a_user_id, "integer"));			
		while($row = $ilDB->fetchAssoc($set))
		{
			if(!(bool)$a_validate ||
				$this->validatePeerReview($row))
			{
				$cnt++;
			}			
		}
		
		return $cnt;
	}
	
	protected function getMaxPossibleFeedbacks()
	{
		global $ilDB;
		
		// check if number of returned assignments is lower than assignment peer min
		$set = $ilDB->query("SELECT COUNT(DISTINCT(user_id)) cnt".
			" FROM exc_returned".
			" WHERE ass_id = ".$ilDB->quote($this->assignment_id, "integer"));
		$cnt = $ilDB->fetchAssoc($set);
		$cnt = (int)$cnt["cnt"];
		return $cnt-1;
	}
	
	public function getNumberOfMissingFeedbacksForReceived()
	{		
		$max = $this->getMaxPossibleFeedbacks();
		
		// forever alone
		if($max < 2)
		{
			return;
		}
		
		// are all required or just 1?
		if(!$this->assignment->getPeerReviewSimpleUnlock())
		{
			$needed = $this->assignment->getPeerReviewMin();
		}
		else
		{
			$needed = 1;
		}
				
		// there could be less participants than stated in the min required setting
		$min = min($max, $needed);
				
		return max(0, $min-$this->countGivenFeedback());		
	}
		
	public function isFeedbackValidForPassed($a_user_id)
	{						
		switch($this->assignment->getPeerReviewValid())
		{
			case ilExAssignment::PEER_REVIEW_VALID_NONE:
				return true;
				
			case ilExAssignment::PEER_REVIEW_VALID_ONE:
				return (bool)$this->countGivenFeedback(true, $a_user_id);
				
			case ilExAssignment::PEER_REVIEW_VALID_ALL:
				$max = $this->getMaxPossibleFeedbacks();
		
				// forever alone - should be valid
				if($max < 2)
				{
					return true;
				}
		
				// there could be less participants than stated in the min required setting
				$min = min($max, $this->assignment->getPeerReviewMin());
				
				return (($min-$this->countGivenFeedback(true, $a_user_id)) < 1);				
		}			
	}
}
