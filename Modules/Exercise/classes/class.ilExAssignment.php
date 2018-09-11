<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Exercise assignment
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ModulesExercise
*/
class ilExAssignment
{
	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilAppEventHandler
	 */
	protected $app_event_handler;

	const TYPE_UPLOAD = 1;
	const TYPE_BLOG = 2;
	const TYPE_PORTFOLIO = 3;
	const TYPE_UPLOAD_TEAM = 4;
	const TYPE_TEXT = 5;
	
	const FEEDBACK_DATE_DEADLINE = 1;
	const FEEDBACK_DATE_SUBMISSION = 2;
	const FEEDBACK_DATE_CUSTOM = 3;
	
	const PEER_REVIEW_VALID_NONE = 1;
	const PEER_REVIEW_VALID_ONE = 2;
	const PEER_REVIEW_VALID_ALL = 3;
	
	protected $id;
	protected $exc_id;
	protected $type;
	protected $start_time;
	protected $deadline;
	protected $deadline2;
	protected $instruction;
	protected $title;
	protected $mandatory;
	protected $order_nr;
	protected $peer;
	protected $peer_min;
	protected $peer_unlock;
	protected $peer_dl;
	protected $peer_valid;
	protected $peer_file;
	protected $peer_personal;
	protected $peer_char;
	protected $peer_text;
	protected $peer_rating;
	protected $peer_crit_cat;
	protected $feedback_file;
	protected $feedback_cron;
	protected $feedback_date;
	protected $feedback_date_custom;
	protected $team_tutor = false;
	protected $max_file;
	protected $portfolio_template;
	protected $min_char_limit;
	protected $max_char_limit;
	
	protected $member_status = array(); // [array]

	protected $log;

	/**
	 * Constructor
	 */
	function __construct($a_id = 0)
	{
		global $DIC;

		$this->db = $DIC->database();
		$this->lng = $DIC->language();
		$this->user = $DIC->user();
		$this->app_event_handler = $DIC["ilAppEventHandler"];
		$this->setType(self::TYPE_UPLOAD);
		$this->setFeedbackDate(self::FEEDBACK_DATE_DEADLINE);

		$this->log = ilLoggerFactory::getLogger("exc");
		
		if ($a_id > 0)
		{
			$this->setId($a_id);
			$this->read();
		}
	}
			
	public static function getInstancesByExercise($a_exc_id)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$set = $ilDB->query("SELECT * FROM exc_assignment ".
			" WHERE exc_id = ".$ilDB->quote($a_exc_id, "integer").
			" ORDER BY order_nr ASC");
		$data = array();

		$order_val = 10;
		while ($rec = $ilDB->fetchAssoc($set))
		{
			// ???
			$rec["order_val"] = $order_val;
			
			$ass = new self();
			$ass->initFromDB($rec);
			$data[] = $ass;
			
			$order_val += 10;
		}
		
		return $data;
	}

	/**
	 * @param array $a_file_data
	 * @param integer $a_ass_id assignment id.
	 * @return array
	 */
	public static function instructionFileGetFileOrderData($a_file_data, $a_ass_id)
	{
		global $DIC;

		$db = $DIC->database();
		$db->setLimit(1,0);

		$result_order_val = $db->query("
				SELECT id, order_nr
				FROM exc_ass_file_order
				WHERE assignment_id = {$db->quote($a_ass_id, 'integer')}
				AND filename = {$db->quote($a_file_data['entry'], 'string')}
			");

		$order_val = 0;
		$order_id = 0;
		while ($row = $db->fetchAssoc($result_order_val)) {
			$order_val = (int)$row['order_nr'];
			$order_id = (int)$row['id'];
		}
		return array($order_val, $order_id);
	}

	public function hasTeam()
	{
		return $this->type == self::TYPE_UPLOAD_TEAM;
	}
	
	/**
	 * Set assignment id
	 *
	 * @param	int		assignment id
	 */
	function setId($a_val)
	{
		$this->id = $a_val;
	}
	
	/**
	 * Get assignment id
	 *
	 * @return	int	assignment id
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set exercise id
	 *
	 * @param	int		exercise id
	 */
	function setExerciseId($a_val)
	{
		$this->exc_id = $a_val;
	}
	
	/**
	 * Get exercise id
	 *
	 * @return	int	exercise id
	 */
	function getExerciseId()
	{
		return $this->exc_id;
	}
	
	/**
	 * Set start time (timestamp)
	 *
	 * @param	int		start time (timestamp)
	 */
	function setStartTime($a_val)
	{
		$this->start_time = $a_val;
	}
	
	/**
	 * Get start time (timestamp)
	 *
	 * @return	int		start time (timestamp)
	 */
	function getStartTime()
	{
		return $this->start_time;
	}

	/**
	 * Set deadline (timestamp)
	 *
	 * @param	int		deadline (timestamp)
	 */
	function setDeadline($a_val)
	{
		$this->deadline = $a_val;
	}
	
	/**
	 * Get deadline (timestamp)
	 *
	 * @return	int		deadline (timestamp)
	 */
	function getDeadline()
	{
		return $this->deadline;
	}
	
	/**
	 * Get individual deadline
	 * @param int $a_user_id
	 * @return int
	 */
	function getPersonalDeadline($a_user_id)
	{
		$ilDB = $this->db;
		
		$is_team = false;
		if($this->getType() == self::TYPE_UPLOAD_TEAM)
		{
			include_once("./Modules/Exercise/classes/class.ilExAssignmentTeam.php");
			$team_id = ilExAssignmentTeam::getTeamId($this->getId(), $a_user_id);
			if(!$team_id)
			{
				// #0021043
				$this->getDeadline();
			}
			$a_user_id = $team_id;
			$is_team = true;
		}
		
		$set = $ilDB->query("SELECT tstamp FROM exc_idl".
			" WHERE ass_id = ".$ilDB->quote($this->getId(), "integer").
			" AND member_id = ".$ilDB->quote($a_user_id, "integer").
			" AND is_team = ".$ilDB->quote($is_team, "integer"));
		$row = $ilDB->fetchAssoc($set);
		
		// use assignment deadline if no direct personal
		return max($row["tstamp"], $this->getDeadline());
	}
	
	/**
	 * Get last/final personal deadline (of assignment)
	 * 
	 * @return int
	 */
	protected function getLastPersonalDeadline()
	{
		$ilDB = $this->db;
		
		$set = $ilDB->query("SELECT MAX(tstamp) FROM exc_idl".
			" WHERE ass_id = ".$ilDB->quote($this->getId(), "integer"));
		$row = $ilDB->fetchAssoc($set);
		return $row["tstamp"];
	}
	
	/**
	 * Set extended deadline (timestamp)
	 *
	 * @param int	
	 */
	function setExtendedDeadline($a_val)
	{
		if($a_val !== null)
		{
			$a_val = (int)$a_val;
		}
		$this->deadline2 = $a_val;
	}
	
	/**
	 * Get extended deadline (timestamp)
	 *
	 * @return	int		
	 */
	function getExtendedDeadline()
	{
		return $this->deadline2;
	}

	/**
	 * Set instruction
	 *
	 * @param	string		instruction
	 */
	function setInstruction($a_val)
	{
		$this->instruction = $a_val;
	}
	
	/**
	 * Get instruction
	 *
	 * @return	string		instruction
	 */
	function getInstruction()
	{
		return $this->instruction;
	}

	/**
	 * Set title
	 *
	 * @param	string		title
	 */
	function setTitle($a_val)
	{
		$this->title = $a_val;
	}
	
	/**
	 * Get title
	 *
	 * @return	string	title
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set mandatory
	 *
	 * @param	int		mandatory
	 */
	function setMandatory($a_val)
	{
		$this->mandatory = $a_val;
	}
	
	/**
	 * Get mandatory
	 *
	 * @return	int	mandatory
	 */
	function getMandatory()
	{
		return $this->mandatory;
	}

	/**
	 * Set order nr
	 *
	 * @param	int		order nr
	 */
	function setOrderNr($a_val)
	{
		$this->order_nr = $a_val;
	}
	
	/**
	 * Get order nr
	 *
	 * @return	int	order nr
	 */
	function getOrderNr()
	{
		return $this->order_nr;
	}
	
	/**
	 * Set type
	 * 
	 * @param int $a_value 
	 */
	function setType($a_value)
	{
		if($this->isValidType($a_value))
		{
			$this->type = (int)$a_value;
			
			if($this->type == self::TYPE_UPLOAD_TEAM)
			{
				$this->setPeerReview(false);
			}
		}
	}
	
	/**
	 * Get type
	 * 
	 * @return int
	 */
	function getType()
	{
		return $this->type;
	}
	
	/**
	 * Is given type valid?
	 * 
	 * @param int $a_value
	 * @return bool
	 */
	function isValidType($a_value)
	{
		if(in_array((int)$a_value, array(self::TYPE_UPLOAD, self::TYPE_BLOG, 
			self::TYPE_PORTFOLIO, self::TYPE_UPLOAD_TEAM, self::TYPE_TEXT)))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * Toggle peer review
	 * 
	 * @param bool $a_value
	 */
	function setPeerReview($a_value)
	{
		$this->peer = (bool)$a_value;
	}
	
	/**
	 * Get peer review status
	 * 
	 * @return bool 
	 */
	function getPeerReview()
	{
		return (bool)$this->peer;
	}
	
	/**
	 * Set peer review minimum
	 * 
	 * @param int $a_value
	 */
	function setPeerReviewMin($a_value)
	{
		$this->peer_min = (int)$a_value;
	}
	
	/**
	 * Get peer review minimum
	 * 
	 * @return int 
	 */
	function getPeerReviewMin()
	{
		return (int)$this->peer_min;
	}
	
	/**
	 * Set peer review simple unlock
	 * 
	 * @param bool $a_value
	 */
	function setPeerReviewSimpleUnlock($a_value)
	{
		$this->peer_unlock = (bool)$a_value;
	}
	
	/**
	 * Get peer review simple unlock
	 * 
	 * @return bool 
	 */
	function getPeerReviewSimpleUnlock()
	{
		return (bool)$this->peer_unlock;
	}
	
	/**
	 * Set peer review deadline (timestamp)
	 *
	 * @param	int		deadline (timestamp)
	 */
	function setPeerReviewDeadline($a_val)
	{
		$this->peer_dl = $a_val;
	}
	
	/**
	 * Get peer review deadline (timestamp)
	 *
	 * @return	int		deadline (timestamp)
	 */
	function getPeerReviewDeadline()
	{
		return $this->peer_dl;
	}
	
	/**
	 * Set peer review validation
	 * 
	 * @param int $a_value
	 */
	function setPeerReviewValid($a_value)
	{
		$this->peer_valid = (int)$a_value;
	}
	
	/**
	 * Get peer review validatiob
	 * 
	 * @return int 
	 */
	function getPeerReviewValid()
	{
		return (int)$this->peer_valid;
	}
	
	/**
	 * Set peer review rating
	 *
	 * @param	bool
	 */
	function setPeerReviewRating($a_val)
	{
		$this->peer_rating = (bool)$a_val;
	}
	
	/**
	 * Get peer review rating status
	 *
	 * @return	bool
	 */
	function hasPeerReviewRating()
	{
		return $this->peer_rating;
	}
	
	/**
	 * Set peer review text
	 *
	 * @param	bool
	 */
	function setPeerReviewText($a_val)
	{
		$this->peer_text = (bool)$a_val;
	}
	
	/**
	 * Get peer review text status
	 *
	 * @return	bool
	 */
	function hasPeerReviewText()
	{
		return $this->peer_text;
	}
	
	/**
	 * Set peer review file upload
	 *
	 * @param	bool
	 */
	function setPeerReviewFileUpload($a_val)
	{
		$this->peer_file = (bool)$a_val;
	}
	
	/**
	 * Get peer review file upload status
	 *
	 * @return	bool
	 */
	function hasPeerReviewFileUpload()
	{
		return $this->peer_file;
	}
	
	/**
	 * Set peer review personalized
	 *
	 * @param	bool
	 */
	function setPeerReviewPersonalized($a_val)
	{
		$this->peer_personal = (bool)$a_val;
	}
	
	/**
	 * Get peer review personalized status
	 *
	 * @return	bool
	 */
	function hasPeerReviewPersonalized()
	{
		return $this->peer_personal;
	}	
	
	/**
	 * Set peer review minimum characters
	 * 
	 * @param int $a_value
	 */
	function setPeerReviewChars($a_value)
	{
		$a_value = (is_numeric($a_value) && (int)$a_value > 0)
			? (int)$a_value
			: null;		
		$this->peer_char = $a_value;
	}
	
	/**
	 * Get peer review minimum characters
	 * 
	 * @return int 
	 */
	function getPeerReviewChars()
	{
		return $this->peer_char;
	}
	
	/**
	 * Set peer review criteria catalogue id
	 * 
	 * @param int $a_value
	 */
	function setPeerReviewCriteriaCatalogue($a_value)
	{
		$a_value = is_numeric($a_value)
			? (int)$a_value
			: null;		
		$this->crit_cat = $a_value;
	}
	
	/**
	 * Get peer review criteria catalogue id
	 * 
	 * @return int 
	 */
	function getPeerReviewCriteriaCatalogue()
	{
		return $this->crit_cat;
	}
	
	function getPeerReviewCriteriaCatalogueItems()
	{
		include_once "Modules/Exercise/classes/class.ilExcCriteria.php";
		
		if($this->crit_cat)
		{			
			return ilExcCriteria::getInstancesByParentId($this->crit_cat);
		}
		else
		{
			$res = array();
			
			if($this->peer_rating)
			{
				$res[] = ilExcCriteria::getInstanceByType("rating");
			}
			
			if($this->peer_text)
			{
				$crit = ilExcCriteria::getInstanceByType("text");				
				if($this->peer_char)
				{
					$crit->setMinChars($this->peer_char);
				}
				$res[] = $crit;
			}
			
			if($this->peer_file)
			{
				$res[] = ilExcCriteria::getInstanceByType("file");
			}
			
			return $res;
		}
	}
	
	/**
	 * Set (global) feedback file
	 * 
	 * @param string $a_value
	 */
	function setFeedbackFile($a_value)
	{
		$this->feedback_file = (string)$a_value;
	}
	
	/**
	 * Get (global) feedback file
	 * 
	 * @return int 
	 */
	function getFeedbackFile()
	{
		return (string)$this->feedback_file;
	}
	
	/**
	 * Toggle (global) feedback file cron
	 * 
	 * @param bool $a_value
	 */
	function setFeedbackCron($a_value)
	{
		$this->feedback_cron = (string)$a_value;
	}
	
	/**
	 * Get (global) feedback file cron status
	 * 
	 * @return int 
	 */
	function hasFeedbackCron()
	{
		return (bool)$this->feedback_cron;
	}
	
	/**
	 * Set (global) feedback file availability date
	 * 
	 * @param int $a_value
	 */
	function setFeedbackDate($a_value)
	{
		$this->feedback_date = (int)$a_value;
	}
	
	/**
	 * Get (global) feedback file availability date
	 * 
	 * @return int 
	 */
	function getFeedbackDate()
	{
		return (int)$this->feedback_date;
	}

	/**
	 * Set (global) feedback file availability using a custom date.
	 * @param int $a_value timestamp
	 */
	function setFeedbackDateCustom($a_value)
	{
		$this->feedback_date_custom = $a_value;
	}

	/**
	 * Get feedback file availability using custom date.
	 * @return string timestamp
	 */
	function getFeedbackDateCustom()
	{
		return $this->feedback_date_custom;
	}

	/**
	 * Set team management by tutor
	 * 
	 * @param bool $a_value
	 */
	function setTeamTutor($a_value)
	{
		$this->team_tutor = (bool)$a_value;
	}
	
	/**
	 * Get team management by tutor
	 * 
	 * @return bool 
	 */
	function getTeamTutor()
	{
		return $this->team_tutor;
	}
	
	/**
	 * Set max number of uploads
	 * 
	 * @param int $a_value
	 */
	function setMaxFile($a_value)
	{
		if($a_value !== null)
		{
			$a_value = (int)$a_value;
		}
		$this->max_file = $a_value;
	}
	
	/**
	 * Get max number of uploads
	 * 
	 * @return bool 
	 */
	function getMaxFile()
	{
		return $this->max_file;
	}

	/**
	 * Set portfolio template id
	 *
	 * @param int $a_val
	 */
	function setPortfolioTemplateId($a_val)
	{
		$this->portfolio_template = $a_val;
	}

	/**
	 * Get portfolio template id
	 *
	 * @return	int	portfolio template id
	 */
	function getPortfolioTemplateId()
	{
		return $this->portfolio_template;
	}


	/**
	 * Read from db
	 */
	function read()
	{
		$ilDB = $this->db;
		
		$set = $ilDB->query("SELECT * FROM exc_assignment ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		
		// #16172 - might be deleted
		if(is_array($rec))
		{
			$this->initFromDB($rec);		
		}
	}
	
	/**
	 * Import DB record
	 * 	 
	 * @see getInstancesByExercise()
	 * @param array $a_set
	 */
	protected function initFromDB(array $a_set)
	{
		$this->setId($a_set["id"]);
		$this->setExerciseId($a_set["exc_id"]);
		$this->setDeadline($a_set["time_stamp"]);
		$this->setExtendedDeadline($a_set["deadline2"]);
		$this->setInstruction($a_set["instruction"]);
		$this->setTitle($a_set["title"]);
		$this->setStartTime($a_set["start_time"]);
		$this->setOrderNr($a_set["order_nr"]);
		$this->setMandatory($a_set["mandatory"]);
		$this->setType($a_set["type"]);
		$this->setPeerReview($a_set["peer"]);
		$this->setPeerReviewMin($a_set["peer_min"]);
		$this->setPeerReviewSimpleUnlock($a_set["peer_unlock"]);
		$this->setPeerReviewDeadline($a_set["peer_dl"]);
		$this->setPeerReviewValid($a_set["peer_valid"]);
		$this->setPeerReviewFileUpload($a_set["peer_file"]);
		$this->setPeerReviewPersonalized($a_set["peer_prsl"]);
		$this->setPeerReviewChars($a_set["peer_char"]);
		$this->setPeerReviewText($a_set["peer_text"]);
		$this->setPeerReviewRating($a_set["peer_rating"]);
		$this->setPeerReviewCriteriaCatalogue($a_set["peer_crit_cat"]);
		$this->setFeedbackFile($a_set["fb_file"]);
		$this->setFeedbackDate($a_set["fb_date"]);
		$this->setFeedbackDateCustom($a_set["fb_date_custom"]);
		$this->setFeedbackCron($a_set["fb_cron"]);
		$this->setTeamTutor($a_set["team_tutor"]);
		$this->setMaxFile($a_set["max_file"]);
		$this->setPortfolioTemplateId($a_set["portfolio_template"]);
		$this->setMinCharLimit($a_set["min_char_limit"]);
		$this->setMaxCharLimit($a_set["max_char_limit"]);
	}
	
	/**
	 * Save assignment
	 */
	function save()
	{
		$ilDB = $this->db;
		
		if ($this->getOrderNr() == 0)
		{
			$this->setOrderNr(
				self::lookupMaxOrderNrForEx($this->getExerciseId())
				+ 10);
		}
		
		$next_id = $ilDB->nextId("exc_assignment");
		$ilDB->insert("exc_assignment", array(
			"id" => array("integer", $next_id),
			"exc_id" => array("integer", $this->getExerciseId()),
			"time_stamp" => array("integer", $this->getDeadline()),
			"deadline2" => array("integer", $this->getExtendedDeadline()),
			"instruction" => array("clob", $this->getInstruction()),
			"title" => array("text", $this->getTitle()),
			"start_time" => array("integer", $this->getStartTime()),
			"order_nr" => array("integer", $this->getOrderNr()),
			"mandatory" => array("integer", $this->getMandatory()),
			"type" => array("integer", $this->getType()),
			"peer" => array("integer", $this->getPeerReview()),
			"peer_min" => array("integer", $this->getPeerReviewMin()),
			"peer_unlock" => array("integer", $this->getPeerReviewSimpleUnlock()),
			"peer_dl" => array("integer", $this->getPeerReviewDeadline()),
			"peer_valid" => array("integer", $this->getPeerReviewValid()),
			"peer_file" => array("integer", $this->hasPeerReviewFileUpload()),
			"peer_prsl" => array("integer", $this->hasPeerReviewPersonalized()),
			"peer_char" => array("integer", $this->getPeerReviewChars()),
			"peer_text" => array("integer", (int) $this->hasPeerReviewText()),
			"peer_rating" => array("integer", (int) $this->hasPeerReviewRating()),
			"peer_crit_cat" => array("integer", $this->getPeerReviewCriteriaCatalogue()),
			"fb_file" => array("text", $this->getFeedbackFile()),
			"fb_date" => array("integer", $this->getFeedbackDate()),
			"fb_date_custom" => array("integer", $this->getFeedbackDateCustom()),
			"fb_cron" => array("integer", $this->hasFeedbackCron()),
			"team_tutor" => array("integer", $this->getTeamTutor()),
			"max_file" => array("integer", $this->getMaxFile()),
			"portfolio_template" => array("integer", $this->getPortFolioTemplateId()),
			"min_char_limit" => array("integer", $this->getMinCharLimit()),
			"max_char_limit" => array("integer", $this->getMaxCharLimit())
		));
		$this->setId($next_id);
		$exc = new ilObjExercise($this->getExerciseId(), false);
		$exc->updateAllUsersStatus();
		self::createNewAssignmentRecords($next_id, $exc);
		
		$this->handleCalendarEntries("create");
	}
	
	/**
	 * Update
	 */
	function update()
	{		
		$ilDB = $this->db;

		$ilDB->update("exc_assignment",
			array(
			"exc_id" => array("integer", $this->getExerciseId()),
			"time_stamp" => array("integer", $this->getDeadline()),
			"deadline2" => array("integer", $this->getExtendedDeadline()),
			"instruction" => array("clob", $this->getInstruction()),
			"title" => array("text", $this->getTitle()),
			"start_time" => array("integer", $this->getStartTime()),
			"order_nr" => array("integer", $this->getOrderNr()),
			"mandatory" => array("integer", $this->getMandatory()),
			"type" => array("integer", $this->getType()),
			"peer" => array("integer", $this->getPeerReview()),
			"peer_min" => array("integer", $this->getPeerReviewMin()),
			"peer_unlock" => array("integer", $this->getPeerReviewSimpleUnlock()),
			"peer_dl" => array("integer", $this->getPeerReviewDeadline()),
			"peer_valid" => array("integer", $this->getPeerReviewValid()),
			"peer_file" => array("integer", $this->hasPeerReviewFileUpload()),
			"peer_prsl" => array("integer", $this->hasPeerReviewPersonalized()),
			"peer_char" => array("integer", $this->getPeerReviewChars()),
			"peer_text" => array("integer", (int) $this->hasPeerReviewText()),
			"peer_rating" => array("integer", (int) $this->hasPeerReviewRating()),
			"peer_crit_cat" => array("integer", $this->getPeerReviewCriteriaCatalogue()),
			"fb_file" => array("text", $this->getFeedbackFile()),
			"fb_date" => array("integer", $this->getFeedbackDate()),
			"fb_date_custom" => array("integer", $this->getFeedbackDateCustom()),
			"fb_cron" => array("integer", $this->hasFeedbackCron()),
			"team_tutor" => array("integer", $this->getTeamTutor()),
			"max_file" => array("integer", $this->getMaxFile()),
			"portfolio_template" => array("integer", $this->getPortFolioTemplateId()),
			"min_char_limit" => array("integer", $this->getMinCharLimit()),
			"max_char_limit" => array("integer", $this->getMaxCharLimit())
			),
			array(
			"id" => array("integer", $this->getId()),
			));
		$exc = new ilObjExercise($this->getExerciseId(), false);
		$exc->updateAllUsersStatus();
		
		$this->handleCalendarEntries("update");
	}
	
	/**
	 * Delete assignment
	 */
	function delete()
	{
		$ilDB = $this->db;
		
		$this->deleteGlobalFeedbackFile();
		
		$ilDB->manipulate("DELETE FROM exc_assignment WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
			);
		$exc = new ilObjExercise($this->getExerciseId(), false);
		$exc->updateAllUsersStatus();
		
		$this->handleCalendarEntries("delete");

		$reminder = new ilExAssignmentReminder();
		$reminder->deleteReminders($this->getId());
	}
	
	
	/**
	 * Get assignments data of an exercise in an array
	 */
	static function getAssignmentDataOfExercise($a_exc_id)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		// should be changed to self::getInstancesByExerciseId()
		
		$set = $ilDB->query("SELECT * FROM exc_assignment ".
			" WHERE exc_id = ".$ilDB->quote($a_exc_id, "integer").
			" ORDER BY order_nr ASC");
		$data = array();

		$order_val = 10;
		while ($rec = $ilDB->fetchAssoc($set))
		{
			
			$data[] = array(
				"id" => $rec["id"],
				"exc_id" => $rec["exc_id"],
				"deadline" => $rec["time_stamp"],
				"deadline2" => $rec["deadline2"],
				"instruction" => $rec["instruction"],
				"title" => $rec["title"],
				"start_time" => $rec["start_time"],
				"order_val" => $order_val,
				"mandatory" => $rec["mandatory"],
				"type" => $rec["type"],
				"peer" => $rec["peer"],
				"peer_min" => $rec["peer_min"],
				"peer_dl" => $rec["peer_dl"],
				"peer_file" => $rec["peer_file"],
				"peer_prsl" => $rec["peer_prsl"],
				"fb_file" => $rec["fb_file"],
				"fb_date" => $rec["fb_date"],
				"fb_cron" => $rec["fb_cron"],
				);
			$order_val += 10;
		}
		return $data;
	}
	
	/**
	 * Clone assignments of exercise
	 * @param
	 * @return
	 */
	static function cloneAssignmentsOfExercise($a_old_exc_id, $a_new_exc_id, array $a_crit_cat_map)
	{
		$ass_data = self::getInstancesByExercise($a_old_exc_id);
		foreach ($ass_data as $d)
		{			
			// clone assignment
			$new_ass = new ilExAssignment();
			$new_ass->setExerciseId($a_new_exc_id);
			$new_ass->setTitle($d->getTitle());
			$new_ass->setDeadline($d->getDeadline());
			$new_ass->setExtendedDeadline($d->getExtendedDeadline());
			$new_ass->setInstruction($d->getInstruction());
			$new_ass->setMandatory($d->getMandatory());
			$new_ass->setOrderNr($d->getOrderNr());
			$new_ass->setStartTime($d->getStartTime());
			$new_ass->setType($d->getType());
			$new_ass->setPeerReview($d->getPeerReview());
			$new_ass->setPeerReviewMin($d->getPeerReviewMin());
			$new_ass->setPeerReviewDeadline($d->getPeerReviewDeadline());
			$new_ass->setPeerReviewFileUpload($d->hasPeerReviewFileUpload());
			$new_ass->setPeerReviewPersonalized($d->hasPeerReviewPersonalized());
			$new_ass->setPeerReviewValid($d->getPeerReviewValid());
			$new_ass->setPeerReviewChars($d->getPeerReviewChars());
			$new_ass->setPeerReviewText($d->hasPeerReviewText());
			$new_ass->setPeerReviewRating($d->hasPeerReviewRating());
			$new_ass->setPeerReviewCriteriaCatalogue($d->getPeerReviewCriteriaCatalogue());
			$new_ass->setPeerReviewSimpleUnlock($d->getPeerReviewSimpleUnlock());
			$new_ass->setFeedbackFile($d->getFeedbackFile());
			$new_ass->setFeedbackDate($d->getFeedbackDate());
			$new_ass->setFeedbackDateCustom($d->getFeedbackDateCustom());
			$new_ass->setFeedbackCron($d->hasFeedbackCron()); // #16295
			$new_ass->setTeamTutor($d->getTeamTutor());
			$new_ass->setMaxFile($d->getMaxFile());
			$new_ass->setMinCharLimit($d->getMinCharLimit());
			$new_ass->setMaxCharLimit($d->getMaxCharLimit());
			$new_ass->setPortfolioTemplateId($d->getPortfolioTemplateId());

			// criteria catalogue(s)
			if($d->getPeerReviewCriteriaCatalogue() &&
				array_key_exists($d->getPeerReviewCriteriaCatalogue(), $a_crit_cat_map))
			{
				$new_ass->setPeerReviewCriteriaCatalogue($a_crit_cat_map[$d->getPeerReviewCriteriaCatalogue()]);
			}			
			
			$new_ass->save();
			

			// clone assignment files		
			include_once("./Modules/Exercise/classes/class.ilFSWebStorageExercise.php");
			$old_web_storage = new ilFSWebStorageExercise($a_old_exc_id, (int) $d->getId());
			$new_web_storage = new ilFSWebStorageExercise($a_new_exc_id, (int) $new_ass->getId());
			$new_web_storage->create();
			if (is_dir($old_web_storage->getPath()))
			{
				ilUtil::rCopy($old_web_storage->getPath(), $new_web_storage->getPath());
			}
			
			// clone global feedback file			
			include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
			$old_storage = new ilFSStorageExercise($a_old_exc_id, (int) $d->getId());
			$new_storage = new ilFSStorageExercise($a_new_exc_id, (int) $new_ass->getId());
			$new_storage->create();
			if (is_dir($old_storage->getGlobalFeedbackPath()))
			{
				ilUtil::rCopy($old_storage->getGlobalFeedbackPath(), $new_storage->getGlobalFeedbackPath());
			}
		}
	}
	
	/**
	 * Get files
	 */
	public function getFiles()
	{
		$this->log->debug("getting files from class.ilExAssignment using ilFSWebStorageExercise");
		include_once("./Modules/Exercise/classes/class.ilFSWebStorageExercise.php");
		$storage = new ilFSWebStorageExercise($this->getExerciseId(), $this->getId());
		return $storage->getFiles();
	}

	/**
	 * @param $a_ass_id
	 * @return array
	 */
	public function getInstructionFilesOrder()
	{
		$ilDB = $this->db;

		$set = $ilDB->query("SELECT filename, order_nr, id FROM exc_ass_file_order ".
			" WHERE assignment_id  = ".$ilDB->quote($this->getId(), "integer")
		);

		$data = array();
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$data[$rec['filename']] = $rec;
		}

		return $data;
	}
	
	/**
	 * Select the maximum order nr for an exercise
	 */
	static function lookupMaxOrderNrForEx($a_exc_id)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$set = $ilDB->query("SELECT MAX(order_nr) mnr FROM exc_assignment ".
			" WHERE exc_id = ".$ilDB->quote($a_exc_id, "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			return (int) $rec["mnr"];
		}
		return 0;
	}
	
	/**
	 * Check if assignment is online
	 * @param int $a_ass_id
	 * @return bool
	 */
	public static function lookupAssignmentOnline($a_ass_id)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$query = "SELECT id FROM exc_assignment ".
			"WHERE start_time <= ".$ilDB->quote(time(),'integer').' '.
			"AND time_stamp >= ".$ilDB->quote(time(),'integer').' '.
			"AND id = ".$ilDB->quote($a_ass_id,'integer');
		$res = $ilDB->query($query);
		
		return $res->numRows() ? true : false;
	}
	
	
	/**
	 * Private lookup
	 */
	private static function lookup($a_id, $a_field)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$set = $ilDB->query("SELECT ".$a_field." FROM exc_assignment ".
			" WHERE id = ".$ilDB->quote($a_id, "integer")
			);

		$rec = $ilDB->fetchAssoc($set);

		return $rec[$a_field];
	}
	
	/**
	 * Lookup title
	 */
	static function lookupTitle($a_id)
	{
		return self::lookup($a_id, "title");
	}
	
	/**
	 * Lookup type
	 */
	static function lookupType($a_id)
	{
		return self::lookup($a_id, "type");
	}
	
	/**
	 * Save ordering of all assignments of an exercise
	 */
	static function saveAssOrderOfExercise($a_ex_id, $a_order)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$result_order = array();
		asort($a_order);
		$nr = 10;
		foreach ($a_order as $k => $v)
		{
			// the check for exc_id is for security reasons. ass ids are unique.
			$ilDB->manipulate($t = "UPDATE exc_assignment SET ".
				" order_nr = ".$ilDB->quote($nr, "integer").
				" WHERE id = ".$ilDB->quote((int) $k, "integer").
				" AND exc_id = ".$ilDB->quote((int) $a_ex_id, "integer")
				);
			$nr+=10;
		}
	}

	/**
	 * Order assignments by deadline date
	 */
	function orderAssByDeadline($a_ex_id)
	{
		$ilDB = $this->db;
		
		$set = $ilDB->query("SELECT id FROM exc_assignment ".
			" WHERE exc_id = ".$ilDB->quote($a_ex_id, "integer").
			" ORDER BY time_stamp ASC"
			);
		$nr = 10;
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$ilDB->manipulate("UPDATE exc_assignment SET ".
				" order_nr = ".$ilDB->quote($nr, "integer").
				" WHERE id = ".$ilDB->quote($rec["id"], "integer")
				);
			$nr += 10;
		}
	}

	/**
	 * Order assignments by deadline date
	 */
	static function countMandatory($a_ex_id)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$set = $ilDB->query("SELECT count(*) cntm FROM exc_assignment ".
			" WHERE exc_id = ".$ilDB->quote($a_ex_id, "integer").
			" AND mandatory = ".$ilDB->quote(1, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		return $rec["cntm"];
	}

///
	/**
	 * Check whether student has upload new files after tutor has
	 * set the exercise to another than notgraded.
	 */
	static function lookupUpdatedSubmission($ass_id, $member_id)
	{
		global $DIC;

		$ilDB = $DIC->database();
		$lng = $DIC->language();
		
		// team upload?
		$user_ids = self::getTeamMembersByAssignmentId($ass_id, $member_id);
		if(!$user_ids)
		{
			$user_ids = array($member_id);
		}

  		$q="SELECT exc_mem_ass_status.status_time, exc_returned.ts ".
			"FROM exc_mem_ass_status, exc_returned ".
			"WHERE exc_mem_ass_status.status_time < exc_returned.ts ".
			"AND NOT exc_mem_ass_status.status_time IS NULL ".
			"AND exc_returned.ass_id = exc_mem_ass_status.ass_id ".
			"AND exc_returned.user_id = exc_mem_ass_status.usr_id ".
			"AND exc_returned.ass_id=".$ilDB->quote($ass_id, "integer").
			" AND ".$ilDB->in("exc_returned.user_id", $user_ids, "", "integer");

  		$usr_set = $ilDB->query($q);

  		$array = $ilDB->fetchAssoc($usr_set);

		if (count($array)==0)
		{
			return 0;
  		}
		else
		{
			return 1;
		}

	}

	/**
	 * get member list data
	 */
	function getMemberListData()
	{
		$ilDB = $this->db;

		$mem = array();
		
		// first get list of members from member table
		$set = $ilDB->query("SELECT ud.usr_id, ud.lastname, ud.firstname, ud.login".
			" FROM exc_members excm".
			" JOIN usr_data ud ON (ud.usr_id = excm.usr_id)".
			" WHERE excm.obj_id = ".$ilDB->quote($this->getExerciseId(), "integer"));
		while($rec = $ilDB->fetchAssoc($set))
		{			
			$mem[$rec["usr_id"]] =
				array(
				"name" => $rec["lastname"].", ".$rec["firstname"],
				"login" => $rec["login"],
				"usr_id" => $rec["usr_id"],
				"lastname" => $rec["lastname"],
				"firstname" => $rec["firstname"]
				);			
		}
		
		include_once "Modules/Exercise/classes/class.ilExSubmission.php";

		$q = "SELECT * FROM exc_mem_ass_status ".
			"WHERE ass_id = ".$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($q);
		while($rec = $ilDB->fetchAssoc($set))
		{
			if (isset($mem[$rec["usr_id"]]))
			{
				$sub = new ilExSubmission($this, $rec["usr_id"]);
				
				$mem[$rec["usr_id"]]["sent_time"] = $rec["sent_time"];
				$mem[$rec["usr_id"]]["submission"] = $sub->getLastSubmission();
				$mem[$rec["usr_id"]]["status_time"] = $rec["status_time"];
				$mem[$rec["usr_id"]]["feedback_time"] = $rec["feedback_time"];
				$mem[$rec["usr_id"]]["notice"] = $rec["notice"];
				$mem[$rec["usr_id"]]["status"] = $rec["status"];
				$mem[$rec["usr_id"]]["mark"] = $rec["mark"];
				$mem[$rec["usr_id"]]["comment"] = $rec["u_comment"];
			}
		}
		return $mem;
	}

	/**
	 * Get submission data for an specific user,exercise and assignment.
	 * todo we can refactor a bit the method getMemberListData to use this and remove duplicate code.
	 * @param $a_user_id
	 * @param $a_grade
	 * @return array
	 */
	public function getExerciseMemberAssignmentData($a_user_id, $a_grade = "")
	{
		global $DIC;
		$ilDB = $DIC->database();

		include_once "Modules/Exercise/classes/class.ilExSubmission.php";

		if(in_array($a_grade, array("notgraded", "passed", "failed")))
		{
			$and_grade = " AND status = ".$ilDB->quote($a_grade, "text");
		}

		$q = "SELECT * FROM exc_mem_ass_status ".
			"WHERE ass_id = ".$ilDB->quote($this->getId(), "integer").
			" AND usr_id = ".$ilDB->quote($a_user_id, "integer").
			$and_grade;

		$set = $ilDB->query($q);

		while($rec = $ilDB->fetchAssoc($set))
		{
			$sub = new ilExSubmission($this, $a_user_id);

			$data["sent_time"] = $rec["sent_time"];
			$data["submission"] = $sub->getLastSubmission();
			$data["status_time"] = $rec["status_time"];
			$data["feedback_time"] = $rec["feedback_time"];
			$data["notice"] = $rec["notice"];
			$data["status"] = $rec["status"];
			$data["mark"] = $rec["mark"];
			$data["comment"] = $rec["u_comment"];
		}

		return $data;

	}

	/**
	 * Create member status record for a new participant for all assignments
	 */
	static function createNewUserRecords($a_user_id, $a_exc_id)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$ass_data = self::getAssignmentDataOfExercise($a_exc_id);
		foreach ($ass_data as $ass)
		{
//echo "-".$ass["id"]."-".$a_user_id."-";
			$ilDB->replace("exc_mem_ass_status", array(
				"ass_id" => array("integer", $ass["id"]),
				"usr_id" => array("integer", $a_user_id)
				), array(
				"status" => array("text", "notgraded")
				));
		}
	}
	
	/**
	 * Create member status record for a new assignment for all participants
	 */
	static function createNewAssignmentRecords($a_ass_id, $a_exc)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		include_once("./Modules/Exercise/classes/class.ilExerciseMembers.php");
		$exmem = new ilExerciseMembers($a_exc);
		$mems = $exmem->getMembers();

		foreach ($mems as $mem)
		{
			$ilDB->replace("exc_mem_ass_status", array(
				"ass_id" => array("integer", $a_ass_id),
				"usr_id" => array("integer", $mem)
				), array(
				"status" => array("text", "notgraded")
				));
		}
	}

	/**
	 * Upload assignment files
	 * (from creation form)
	 */
	function uploadAssignmentFiles($a_files)
	{
		ilLoggerFactory::getLogger("exc")->debug("upload assignment files files = ",$a_files);
		include_once("./Modules/Exercise/classes/class.ilFSWebStorageExercise.php");
		$storage = new ilFSWebStorageExercise($this->getExerciseId(), $this->getId());
		$storage->create();
		$storage->uploadAssignmentFiles($a_files);
	}
	
	
	////
	//// Multi-Feedback
	////

	/**
	 * Create member status record for a new assignment for all participants
	 */
	function sendMultiFeedbackStructureFile(ilObjExercise $exercise)
	{
		global $DIC;
		
		
		// send and delete the zip file
		$deliverFilename = trim(str_replace(" ", "_", $this->getTitle()."_".$this->getId()));
		$deliverFilename = ilUtil::getASCIIFilename($deliverFilename);
		$deliverFilename = "multi_feedback_".$deliverFilename;

		$exc = new ilObjExercise($this->getExerciseId(), false);
		
		$cdir = getcwd();
		
		// create temporary directoy
		$tmpdir = ilUtil::ilTempnam();
		ilUtil::makeDir($tmpdir);
		$mfdir = $tmpdir."/".$deliverFilename;
		ilUtil::makeDir($mfdir);
		
		// create subfolders <lastname>_<firstname>_<id> for each participant
		include_once("./Modules/Exercise/classes/class.ilExerciseMembers.php");
		$exmem = new ilExerciseMembers($exc);
		$mems = $exmem->getMembers();
		
		$mems = $DIC->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
			'edit_submissions_grades',
			'edit_submissions_grades',
			$exercise->getRefId(),
			$mems
		);
		foreach ($mems as $mem)
		{
			$name = ilObjUser::_lookupName($mem);
			$subdir = $name["lastname"]."_".$name["firstname"]."_".$name["login"]."_".$name["user_id"];
			$subdir = ilUtil::getASCIIFilename($subdir);
			ilUtil::makeDir($mfdir."/".$subdir);
		}
		
		// create the zip file
		chdir($tmpdir);
		$tmpzipfile = $tmpdir."/multi_feedback.zip";
		ilUtil::zip($tmpdir, $tmpzipfile, true);
		chdir($cdir);
		

		ilUtil::deliverFile($tmpzipfile, $deliverFilename.".zip", "", false, true);
	}
	
	/**
	 * Upload multi feedback file
	 *
	 * @param array 
	 * @return
	 */
	function uploadMultiFeedbackFile($a_file)
	{
		$lng = $this->lng;
		$ilUser = $this->user;
		
		include_once("./Modules/Exercise/exceptions/class.ilExerciseException.php");
		if (!is_file($a_file["tmp_name"]))
		{
			throw new ilExerciseException($lng->txt("exc_feedback_file_could_not_be_uploaded"));
		}
		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		$mfu = $storage->getMultiFeedbackUploadPath($ilUser->getId());
		ilUtil::delDir($mfu, true);
		ilUtil::moveUploadedFile($a_file["tmp_name"], "multi_feedback.zip", $mfu."/"."multi_feedback.zip");
		ilUtil::unzip($mfu."/multi_feedback.zip", true);
		$subdirs = ilUtil::getDir($mfu);
		$subdir = "notfound";
		foreach ($subdirs as $s => $j)
		{
			if ($j["type"] == "dir" && substr($s, 0, 14) == "multi_feedback")
			{
				$subdir = $s;
			}
		}

		if (!is_dir($mfu."/".$subdir))
		{
			throw new ilExerciseException($lng->txt("exc_no_feedback_dir_found_in_zip"));
		}

		return true;
	}
	
	/**
	 * Get multi feedback files (of uploader)
	 *
	 * @param int $a_user_id user id of uploader
	 * @return array array of user files (keys: lastname, firstname, user_id, login, file)
	 */
	function getMultiFeedbackFiles($a_user_id = 0)
	{
		$ilUser = $this->user;
		
		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}
		
		$mf_files = array();
		
		// get members
		$exc = new ilObjExercise($this->getExerciseId(), false);
		include_once("./Modules/Exercise/classes/class.ilExerciseMembers.php");
		$exmem = new ilExerciseMembers($exc);
		$mems = $exmem->getMembers();

		// read mf directory
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		$mfu = $storage->getMultiFeedbackUploadPath($ilUser->getId());

		// get subdir that starts with multi_feedback
		$subdirs = ilUtil::getDir($mfu);
		$subdir = "notfound";
		foreach ($subdirs as $s => $j)
		{
			if ($j["type"] == "dir" && substr($s, 0, 14) == "multi_feedback")
			{
				$subdir = $s;
			}
		}
		
		$items = ilUtil::getDir($mfu."/".$subdir);
		foreach ($items as $k => $i)
		{
			// check directory
			if ($i["type"] == "dir" && !in_array($k, array(".", "..")))
			{
				// check if valid member id is given
				$parts = explode("_", $i["entry"]);
				$user_id = (int) $parts[count($parts) - 1];
				if (in_array($user_id, $mems))
				{
					// read dir of user
					$name = ilObjUser::_lookupName($user_id);
					$files = ilUtil::getDir($mfu."/".$subdir."/".$k);
					foreach ($files as $k2 => $f)
					{
						// append files to array
						if ($f["type"] == "file" && substr($k2, 0, 1) != ".")
						{
							$mf_files[] = array(
								"lastname" => $name["lastname"],
								"firstname" => $name["firstname"],
								"login" => $name["login"],
								"user_id" => $name["user_id"],
								"full_path" => $mfu."/".$subdir."/".$k."/".$k2,
								"file" => $k2);
						}
					}
				}
			}
		}
		return $mf_files;
	}
	
	/**
	 * Clear multi feedback directory
	 *
	 * @param array 
	 * @return
	 */
	function clearMultiFeedbackDirectory()
	{
		$lng = $this->lng;
		$ilUser = $this->user;
		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		$mfu = $storage->getMultiFeedbackUploadPath($ilUser->getId());
		ilUtil::delDir($mfu);
	}
	
	/**
	 * Save multi feedback files
	 *
	 * @param
	 * @return
	 */
	function saveMultiFeedbackFiles($a_files, ilObjExercise $a_exc)
	{					
		if($this->getExerciseId() != $a_exc->getId())
		{
			return;
		}
		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$fstorage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		$fstorage->create();
		
		$team_map = array();
		
		$mf_files = $this->getMultiFeedbackFiles();
		foreach ($mf_files as $f)
		{			
			$user_id = $f["user_id"];
			$file_path = $f["full_path"];				
			$file_name = $f["file"];
			
			// if checked in confirmation gui
			if ($a_files[$user_id][md5($file_name)] != "")
			{			
				$submission = new ilExSubmission($this, $user_id);
				$feedback_id = $submission->getFeedbackId();
				$noti_rec_ids = $submission->getUserIds();
				
				if ($feedback_id)
				{
					$fb_path = $fstorage->getFeedbackPath($feedback_id);
					$target = $fb_path."/".$file_name;
					if (is_file($target))
					{
						unlink($target);
					}
					// rename file
					rename($file_path, $target);
										
					if ($noti_rec_ids)
					{						
						foreach($noti_rec_ids as $user_id)
						{
							$member_status = $this->getMemberStatus($user_id);
							$member_status->setFeedback(true);
							$member_status->update();
						}	
						
						$a_exc->sendFeedbackFileNotification($file_name, $noti_rec_ids,
							(int) $this->getId());
					}
				}				
			}
		}
		
		$this->clearMultiFeedbackDirectory();
	}
	
	
	
	
	/**
	 * Handle calendar entries for deadline(s)
	 * 
	 * @param string $a_event
	 */
	protected function handleCalendarEntries($a_event)
	{		
		$ilAppEventHandler = $this->app_event_handler;
		
		$dl_id = $this->getId()."0";
		$fbdl_id = $this->getId()."1";
		
		$context_ids = array($dl_id, $fbdl_id);		
		$apps = array();
		
		if($a_event != "delete")
		{										
			include_once "Services/Calendar/classes/class.ilCalendarAppointmentTemplate.php";
			
			if($this->getDeadline())
			{					
				$app = new ilCalendarAppointmentTemplate($dl_id);
				$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
				$app->setSubtitle("cal_exc_deadline");
				$app->setTitle($this->getTitle());				
				$app->setFullday(false);
				$app->setStart(new ilDateTime($this->getDeadline(), IL_CAL_UNIX));			
				
				$apps[] = $app;
			}

			if($this->getPeerReview() &&
				$this->getPeerReviewDeadline())
			{
				$app = new ilCalendarAppointmentTemplate($fbdl_id);
				$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
				$app->setSubtitle("cal_exc_peer_review_deadline");
				$app->setTitle($this->getTitle());				
				$app->setFullday(false);
				$app->setStart(new ilDateTime($this->getPeerReviewDeadline(), IL_CAL_UNIX));
				
				$apps[] = $app;
			}		
			
		}			
				
		include_once "Modules/Exercise/classes/class.ilObjExercise.php";
		$exc = new ilObjExercise($this->getExerciseId(), false);
		
		$ilAppEventHandler->raise('Modules/Exercise',
			$a_event.'Assignment',
			array(
			'object' => $exc,
			'obj_id' => $exc->getId(),			
			'context_ids' => $context_ids,
			'appointments' => $apps));		
	}
	
	
	public static function getPendingFeedbackNotifications()
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$res = array();

		$set = $ilDB->query("SELECT id,fb_file,time_stamp,deadline2,fb_date FROM exc_assignment".
			" WHERE fb_cron = ".$ilDB->quote(1, "integer").
			" AND (fb_date = ".$ilDB->quote(self::FEEDBACK_DATE_DEADLINE, "integer").
				" AND time_stamp IS NOT NULL".
				" AND time_stamp > ".$ilDB->quote(0, "integer").
				" AND time_stamp < ".$ilDB->quote(time(), "integer").
				" AND fb_cron_done = ".$ilDB->quote(0, "integer").
			") OR (fb_date = ".$ilDB->quote(self::FEEDBACK_DATE_CUSTOM, "integer").
				" AND fb_date_custom IS NOT NULL".
				" AND fb_date_custom > ".$ilDB->quote(0, "integer").
				" AND fb_date_custom < ".$ilDB->quote(time(), "integer").
				" AND fb_cron_done = ".$ilDB->quote(0, "integer").")");



		while($row = $ilDB->fetchAssoc($set))
		{
			if($row['fb_date'] == self::FEEDBACK_DATE_DEADLINE)
			{
				$max = max($row['time_stamp'], $row['deadline2']);
				if (trim($row["fb_file"]) && $max <= time())
				{
					$res[] = $row["id"];
				}
			}
			elseif($row['fb_date'] == self::FEEDBACK_DATE_CUSTOM)
			{
				if(trim($row["fb_file"]) && $row['fb_date_custom'] <= time())
				{
					$res[] = $row["id"];
				}
			}
		}

		return $res;
	}
	
	public static function sendFeedbackNotifications($a_ass_id, $a_user_id = null)
	{
		global $DIC;

		$ilDB = $DIC->database();
		
		$ass = new self($a_ass_id);
		
		// valid assignment?
		if(!$ass->hasFeedbackCron() || !$ass->getFeedbackFile())
		{
			return false;
		}		
		
		if(!$a_user_id)
		{
			// already done?
			$set = $ilDB->query("SELECT fb_cron_done".
				" FROM exc_assignment".
				" WHERE id = ".$ilDB->quote($a_ass_id, "integer"));
			$row = $ilDB->fetchAssoc($set);
			if($row["fb_cron_done"])
			{
				return false;
			}
		}
		
		include_once "./Services/Notification/classes/class.ilSystemNotification.php";
		$ntf = new ilSystemNotification();
		$ntf->setLangModules(array("exc"));
		$ntf->setObjId($ass->getExerciseId());
		$ntf->setSubjectLangId("exc_feedback_notification_subject");
		$ntf->setIntroductionLangId("exc_feedback_notification_body");
		$ntf->addAdditionalInfo("exc_assignment", $ass->getTitle());
		$ntf->setGotoLangId("exc_feedback_notification_link");		
		$ntf->setReasonLangId("exc_feedback_notification_reason");	
		
		if(!$a_user_id)
		{
			include_once "./Modules/Exercise/classes/class.ilExerciseMembers.php";
			$ntf->sendMail(ilExerciseMembers::_getMembers($ass->getExerciseId()));
						
			$ilDB->manipulate("UPDATE exc_assignment".
				" SET fb_cron_done = ".$ilDB->quote(1, "integer").
				" WHERE id = ".$ilDB->quote($a_ass_id, "integer"));
		}
		else
		{		
			$ntf->sendMail(array($a_user_id));
		}
		
		return true;		
	}
	
	
	// status
	
	public function afterDeadline()
	{
		$ilUser = $this->user;
				
		// :TODO: always current user?
		$idl = $this->getPersonalDeadline($ilUser->getId());
		
		// no deadline === true
		$deadline = max($this->deadline, $this->deadline2, $idl);
		return ($deadline - time() <= 0);
	}
	
	public function afterDeadlineStrict($a_include_personal = true)
	{
		// :TODO: this means that peer feedback, global feedback is available 
		// after LAST personal deadline
		// team management is currently ignoring personal deadlines
		$idl = (bool)$a_include_personal
			? $this->getLastPersonalDeadline()
			: null;
		
		// no deadline === false
		$deadline = max($this->deadline, $this->deadline2, $idl);		
		
		// #18271 - afterDeadline() does not handle last personal deadline
		if($idl && $deadline == $idl)
		{
			return ($deadline - time() <= 0);
		}
		
		return ($deadline > 0 && 
			$this->afterDeadline());	
	}

	/**
	 * @return bool return if sample solution is available using a custom date.
	 */
	public function afterCustomDate()
	{
		$date_custom = $this->getFeedbackDateCustom();

		//if the solution will be displayed only after reach all the deadlines.
		//$final_deadline = $this->afterDeadlineStrict();
		//$dl = max($final_deadline, time());
		//return ($date_custom - $dl <= 0);
		return ($date_custom - time() <= 0);
	}
	
	public function beforeDeadline()
	{
		// no deadline === true
		return !$this->afterDeadlineStrict();
	}
	
	public function notStartedYet()
	{
		return (time() - $this->start_time <= 0);
	}
	
	
	// 
	// FEEDBACK FILES
	// 
	
	public function getGlobalFeedbackFileStoragePath()
	{
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->getExerciseId(), $this->getId());
		return $storage->getGlobalFeedbackPath();
	}
	
	function deleteGlobalFeedbackFile()
	{	
		ilUtil::delDir($this->getGlobalFeedbackFileStoragePath());				
	}	
	
	function handleGlobalFeedbackFileUpload(array $a_file)
	{		
		$path = $this->getGlobalFeedbackFileStoragePath();
		ilUtil::delDir($path, true);
		if (ilUtil::moveUploadedFile($a_file["tmp_name"], $a_file["name"], $path."/".$a_file["name"]))
		{
			$this->setFeedbackFile($a_file["name"]);		
			return true;
		}
		return false;
	}
	
	function getGlobalFeedbackFilePath()
	{
		$file = $this->getFeedbackFile();
		if($file)
		{			
			$path = $this->getGlobalFeedbackFileStoragePath();
			return $path."/".$file;
		}
	}

	/**
	 * @param int|null $a_user_id
	 * @return \ilExAssignmentMemberStatus
	 */
	public function getMemberStatus($a_user_id = null)
	{
		$ilUser = $this->user;
		
		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}
		if(!array_key_exists($a_user_id, $this->member_status))
		{
			include_once "Modules/Exercise/classes/class.ilExAssignmentMemberStatus.php";
			$this->member_status[$a_user_id] = new ilExAssignmentMemberStatus($this->getId(), $a_user_id);
		}
		return $this->member_status[$a_user_id];
	}
	
	public function recalculateLateSubmissions()
	{
		$ilDB = $this->db;
		
		// see JF, 2015-05-11 
				
		$ext_deadline = $this->getExtendedDeadline();
		
		include_once "Modules/Exercise/classes/class.ilExSubmission.php";
		foreach(ilExSubmission::getAllAssignmentFiles($this->exc_id, $this->getId()) as $file)
		{
			$id = $file["returned_id"];
			$uploaded = new ilDateTime($file["ts"], IL_CAL_DATETIME);
			$uploaded = $uploaded->get(IL_CAL_UNIX);
			
			$deadline = $this->getPersonalDeadline($file["user_id"]);
			$last_deadline = max($deadline, $this->getExtendedDeadline());
			
			$late = null;			
			
			// upload is not late anymore 
			if($file["late"] && 
				(!$last_deadline ||
				!$ext_deadline ||
				$uploaded < $deadline))
			{
				$late = false;
			}
			// upload is now late 
			else if(!$file["late"] &&
				$ext_deadline &&
				$deadline && 
				$uploaded > $deadline)
			{
				$late = true;
			}
			else if($last_deadline && $uploaded > $last_deadline)
			{
				// do nothing, we do not remove submissions?
			}
			
			if($late !== null)
			{				
				$ilDB->manipulate("UPDATE exc_returned".
					" SET late = ".$ilDB->quote($late, "integer").
					" WHERE returned_id = ".$ilDB->quote($id, "integer"));
			}
		}	
	}
	
	
	//
	// individual deadlines
	//
	
	public function setIndividualDeadline($id, ilDateTime $date)
	{
		$ilDB = $this->db;
		
		$is_team = false;
		if(!is_numeric($id))
		{
			$id = substr($id, 1);
			$is_team = true;
		}
		
		$ilDB->replace("exc_idl",
			array(
				"ass_id" => array("integer", $this->getId()),
				"member_id" => array("integer", $id),
				"is_team" => array("integer", $is_team)
			),
			array(
				"tstamp" => array("integer", $date->get(IL_CAL_UNIX))
			)
		);
	}
	
	public function getIndividualDeadlines()
	{
		$ilDB = $this->db;
		
		$res = array();
		
		$set = $ilDB->query("SELECT * FROM exc_idl".
			" WHERE ass_id = ".$ilDB->quote($this->getId(), "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			if($row["is_team"])
			{
				$row["member_id"] = "t".$row["member_id"];
			}
			
			$res[$row["member_id"]] = $row["tstamp"];
		}
		
		return $res;
	}
	
	public function hasActiveIDl()
	{		
		return (bool)$this->getDeadline();
	}
	
	public function hasReadOnlyIDl()
	{
		if($this->getType() != ilExAssignment::TYPE_UPLOAD_TEAM &&
			$this->getPeerReview())
		{		
			// all deadlines are read-only if we have peer feedback
			include_once "Modules/Exercise/classes/class.ilExPeerReview.php";
			$peer_review = new ilExPeerReview($this);	
			if($peer_review->hasPeerReviewGroups())
			{
				return true;
			}
		}
		
		return false;		
	}

	/**
	 * Save ordering of instruction files for an assignment
	 * @param int $a_ass_id assignment id
	 * @param int $a_order order
	 */
	static function saveInstructionFilesOrderOfAssignment($a_ass_id, $a_order)
	{
		global $DIC;

		$db = $DIC->database();

		asort($a_order, SORT_NUMERIC);

		$nr = 10;
		foreach ($a_order as $k => $v)
		{
			// the check for exc_id is for security reasons. ass ids are unique.
			$db->manipulate($t = "UPDATE exc_ass_file_order SET ".
				" order_nr = ".$db->quote($nr, "integer").
				" WHERE id = ".$db->quote((int) $k, "integer").
				" AND assignment_id = ".$db->quote((int) $a_ass_id, "integer")
			);
			$nr+=10;
		}
	}

	/**
	 * Store the file order in the database
	 * @param string $a_filename  previously sanitized.
	 * @param int $a_ass_id assignment id.
	 */
	static function instructionFileInsertOrder($a_filename, $a_ass_id, $a_order_nr = 0)
	{
		global $DIC;

		$db = $DIC->database();

		$order = 0;
		$order_val = 0;

		if($a_ass_id)
		{
			//first of all check the suffix and change if necessary
			$filename = ilUtil::getSafeFilename($a_filename);

			if(!self::instructionFileExistsInDb($filename, $a_ass_id))
			{
				if ($a_order_nr == 0)
				{
					$order_val = self::instructionFileOrderGetMax($a_ass_id);
					$order = $order_val + 10;
				}
				else
				{
					$order = $a_order_nr;
				}

				$id = $db->nextID('exc_ass_file_order');
				$db->manipulate("INSERT INTO exc_ass_file_order " .
					"(id, assignment_id, filename, order_nr) VALUES (" .
					$db->quote($id, "integer") . "," .
					$db->quote($a_ass_id, "integer") . "," .
					$db->quote($filename, "text") . "," .
					$db->quote($order, "integer") .
					")");
			}
		}
	}

	static function instructionFileDeleteOrder($a_ass_id, $a_file)
	{
		global $DIC;

		$db = $DIC->database();

		//now its done by filename. We need to figure how to get the order id in the confirmdelete method
		foreach ($a_file as $k => $v)
		{
			$db->manipulate("DELETE FROM exc_ass_file_order " .
				//"WHERE id = " . $ilDB->quote((int)$k, "integer") .
				"WHERE filename = " . $db->quote($v, "string") .
				" AND assignment_id = " . $db->quote($a_ass_id, 'integer')
			);
		}
	}

	/**
	 * @param string $a_old_name
	 * @param string $a_new_name
	 * @param int $a_ass_id assignment id
	 */
	static function renameInstructionFile($a_old_name, $a_new_name, $a_ass_id)
	{
		global $DIC;

		$db = $DIC->database();

		if($a_ass_id)
		{
			$db->manipulate("DELETE FROM exc_ass_file_order".
				" WHERE assignment_id = ".$db->quote((int)$a_ass_id, 'integer').
				" AND filename = ".$db->quote($a_new_name, 'string')
			);

			$db->manipulate("UPDATE exc_ass_file_order SET".
				" filename = ".$db->quote($a_new_name, 'string').
				" WHERE assignment_id = ".$db->quote((int)$a_ass_id, 'integer').
				" AND filename = ".$db->quote($a_old_name, 'string')
			);
		}
	}

	/**
	 * @param $a_filename
	 * @param $a_ass_id assignment id
	 * @return int if the file exists or not in the DB
	 */
	static function instructionFileExistsInDb($a_filename, $a_ass_id)
	{
		global $DIC;

		$db = $DIC->database();

		if($a_ass_id)
		{
			$result = $db->query("SELECT id FROM exc_ass_file_order" .
				" WHERE assignment_id = " . $db->quote((int)$a_ass_id, 'integer') .
				" AND filename = " . $db->quote($a_filename, 'string')
			);

			return $db->numRows($result);
		}
	}

	function fixInstructionFileOrdering()
	{
		global $DIC;

		$db = $DIC->database();

		$files = array_map(function ($v) {
			return $v["name"];
		}, $this->getFiles());

		$set = $db->query("SELECT * FROM exc_ass_file_order ".
			" WHERE assignment_id = ".$db->quote($this->getId(), "integer").
			" ORDER BY order_nr");
		$order_nr = 10;
		$numbered_files = array();
		while ($rec = $db->fetchAssoc($set))
		{
			// file exists, set correct order nr
			if (in_array($rec["filename"], $files))
			{
				$db->manipulate("UPDATE exc_ass_file_order SET ".
					" order_nr = ".$db->quote($order_nr, "integer").
					" WHERE assignment_id = ".$db->quote($this->getId(), "integer").
					" AND id = ".$db->quote($rec["id"], "integer")
					);
				$order_nr+=10;
				$numbered_files[] = $rec["filename"];
			}
			else	// file does not exist, delete entry
			{
				$db->manipulate("DELETE FROM exc_ass_file_order ".
					" WHERE assignment_id = ".$db->quote($this->getId(), "integer").
					" AND id = ".$db->quote($rec["id"], "integer")
				);
			}
		}
		foreach ($files as $f)
		{
			if (!in_array($f, $numbered_files))
			{
				self::instructionFileInsertOrder($f, $this->getId());
			}
		}
	}

	/**
	 * @param array $a_entries
	 * @param integer $a_ass_id assignment id
	 * @return array data items
	 */
	function fileAddOrder($a_entries = array())
	{
		$this->fixInstructionFileOrdering();

		$order = $this->getInstructionFilesOrder();
		foreach ($a_entries as $k => $e)
		{
			$a_entries[$k]["order_val"] = $order[$e["file"]]["order_nr"];
			$a_entries[$k]["order_id"] = $order[$e["file"]]["id"];
		}

		return $a_entries;
	}

	/**
	 * @param int $a_ass_id assignment id
	 * @return int
	 */
	public static function instructionFileOrderGetMax($a_ass_id)
	{
		global $DIC;

		$db = $DIC->database();

		//get max order number
		$result = $db->queryF("SELECT max(order_nr) as max_order FROM exc_ass_file_order WHERE assignment_id = %s",
			array('integer'),
			array($db->quote($a_ass_id, 'integer'))
		);

		while ($row = $db->fetchAssoc($result)) {
			$order_val = (int)$row['max_order'];
		}
		return $order_val;
	}


	/**
	 * Set limit minimum characters
	 *
	 * @param	int	minim limit
	 */
	function setMinCharLimit($a_val)
	{
		$this->min_char_limit = $a_val;
	}

	/**
	 * Get limit minimum characters
	 *
	 * @return	int minimum limit
	 */
	function getMinCharLimit()
	{
		return $this->min_char_limit;
	}

	/**
	 * Set limit maximum characters
	 * @param int max limit
	 */
	function setMaxCharLimit($a_val)
	{
		$this->max_char_limit = $a_val;
	}

	/**
	 * get limit maximum characters
	 * return int max limit
	 */
	function getMaxCharLimit()
	{
		return $this->max_char_limit;
	}

}

?>