<?php

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
		
		// only if assignment is through
		if(!$this->assignment->getDeadline() || $this->getDeadline() > time())
		{
			return false;
		}
		
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

			$max = min(sizeof($user_ids)-1, $this->getPeerReviewMin());			
			for($loop = 0; $loop < $max; $loop++)
			{				
				$run_ids = array_combine($user_ids, $user_ids);
				
				foreach($rater_ids as $rater_id)
				{
					$possible_peer_ids = $run_ids;
					
					// may not rate himself
					unset($possible_peer_ids[$rater_id]);
					
					// already has linked peers
					if(isset($matrix[$rater_id]))
					{
						$possible_peer_ids = array_diff($possible_peer_ids, $matrix[$rater_id]);
						if(sizeof($possible_peer_ids))
						{
							$peer_id = array_rand($possible_peer_ids);
							$matrix[$rater_id][] = $peer_id;	
						}
					}
					// 1st peer
					else
					{
						if(sizeof($possible_peer_ids)) // #14947
						{
							$peer_id = array_rand($possible_peer_ids);
							$matrix[$rater_id] = array($peer_id);	
						}
					}
					
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
		
		if($this->hasPeerReviewGroups())
		{
			// ratings					
			foreach($this->getAllPeerReviews(false) as $peer_id => $reviews)
			{
				foreach($reviews as $giver_id => $review)
				{					
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
			$set = $ilDB->query("SELECT *".
				" FROM exc_assignment_peer".
				" WHERE giver_id = ".$ilDB->quote($a_user_id, "integer").
				" AND ass_id = ".$ilDB->quote($this->assignment_id, "integer").
				" ORDER BY peer_id");
			while($row = $ilDB->fetchAssoc($set))
			{
				$res[] = $row;
			}
		}				
		
		return $res;
	}
	
	protected static function validatePeerReview(array $a_data, $a_rating = null)
	{							
		$valid = false;		
		
		// comment
		if(trim($a_data["pcomment"]))
		{
			$valid = true;
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
			$ass = new ilExAssignment($a_data["ass_id"]);	
			$peer = new self($ass);
			$valid = (bool)sizeof($peer->getPeerUploadFiles($a_data["peer_id"], $a_data["giver_id"]));
		}
		
		return $valid;
	}
	
	public function getPeerReviewsByPeerId($a_user_id, $a_only_valid = false)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT *".
			" FROM exc_assignment_peer".
			" WHERE peer_id = ".$ilDB->quote($a_user_id, "integer").
			" AND ass_id = ".$ilDB->quote($this->assignment_id, "integer").
			" ORDER BY peer_id");
		while($row = $ilDB->fetchAssoc($set))
		{
			if(!$a_only_valid || 
				self::validatePeerReview($row))
			{				
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
				self::validatePeerReview($row, $rating))
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
		if(!$this->hasPeerReviewFileUpload())
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
	
	public static function countGivenFeedback($a_ass_id)
	{
		global $ilDB, $ilUser;
		
		$cnt = 0;
		
		include_once './Services/Rating/classes/class.ilRating.php';
		
		$set = $ilDB->query("SELECT *".
			" FROM exc_assignment_peer".
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer").
			" AND giver_id = ".$ilDB->quote($ilUser->getId(), "integer"));			
		while($row = $ilDB->fetchAssoc($set))
		{
			if(self::validatePeerReview($row))
			{
				$cnt++;
			}			
		}
		
		return $cnt;
	}
	
	public static function getNumberOfMissingFeedbacks($a_ass_id, $a_min)
	{
		global $ilDB;
		
		// check if number of returned assignments is lower than assignment peer min
		$set = $ilDB->query("SELECT COUNT(DISTINCT(user_id)) cnt".
			" FROM exc_returned".
			" WHERE ass_id = ".$ilDB->quote($a_ass_id, "integer"));
		$cnt = $ilDB->fetchAssoc($set);
		$cnt = (int)$cnt["cnt"];
		
		// forever alone
		if($cnt < 2)
		{
			return;
		}
				
		$a_min = min($cnt-1, $a_min);
				
		return max(0, $a_min-self::countGivenFeedback($a_ass_id));		
	}
}

