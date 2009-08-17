<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesFeedback Services/Feedback
 */

/**
* FeedbackGUI Class
*
* @author	Helmuth Antholzer <helmuth.antholzer@maguma.com>
* @version	$Id$
* @ingroup	ServicesFeedback
*
*/
class ilFeedback 
{
	function ilFeedback($a_id = 0)
	{
		if($a_id > 0){
			$this->id = $a_id;
			$this->getBarometer();
		}

	}
	/* Set / Get Methods */
	function setId($a_id)
	{
		$this->id = $a_id;
	}
	function getId(){
		return($this->id);
	}
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	function getTitle(){
		return($this->title);
	}
	function setDescription($a_description){
		$this->description = $a_description;
	}
	function getDescription(){
		return($this->description);
	}
	function setAnonymous($a_anonymous){
		$this->anonymous = $a_anonymous;
	}

	function getAnonymous(){
		return($this->anonymous);
	}
	function setRequired($a_required){
		$this->required = $a_required;
	}

	function getRequired(){
		return($this->required);
	}
	function setShowOn($a_show_location){
		$this->show_on = $a_show_location;
	}

	function getShowOn(){
		return($this->show_on);
	}
	function setVotes($a_votes){
		$this->votes = $a_votes;
	}

	function getVotes(){
		return($this->votes);
	}
	function setStarttime($a_starttime){
		$this->starttime = $a_starttime;
	}

	function getStarttime(){
		return($this->starttime);
	}
	function setEndtime($a_endtime){
		$this->endtime = $a_endtime;
	}

	function getEndtime(){
		return($this->endtime);
	}
	function setInterval($a_interval){
		$this->interval = $a_interval;
	}

	function getInterval(){
		return($this->interval);
	}
	function setIntervalUnit($a_interval_unit){
		$this->interval_unit = $a_interval_unit;
	}

	function getIntervalUnit(){
		return($this->interval_unit);
	}
	function setFirstVoteBest($a_first_vote_best){
		$this->first_vote_best = $a_first_vote_best;
	}

	function getFirstVoteBest(){
		return($this->first_vote_best);
	}
	function setObjId($a_obj_id){
		$this->obj_id = $a_obj_id;
	}

	function getObjId(){
		return($this->obj_id);
	}
	function setRefId($a_ref_id){
		$this->ref_id = $a_ref_id;
	}
	function getRefId(){
		return($this->ref_id);
	}
	function setTextAnswer($a_text_answer){
		$this->text_answer = $a_text_answer;
	}

	function getTextAnswer(){
		return($this->text_answer);
	}
	function setIds($a_ids){
		$this->ids = $a_ids;
	}
	function setUserId($a_user_id){
		$this->user_id = $a_user_id;
	}
	function setVote($a_vote){
		$this->vote = $a_vote;
	}
	function setNote($a_note){
		$this->note = $a_note;
	}

	/**
	* set all data of a baromter
	*/
	function setAllData($a_barometer){
		$this->setId($a_barometer['fb_id']);
		$this->setTitle($a_barometer['title']);
		$this->setDescription($a_barometer['description']);
		$this->setAnonymous($a_barometer['anonymous']);
		$this->setRequired($a_barometer['required']);
		$this->setShowOn($a_barometer['show_on']);
		$this->setVotes($a_barometer['votes']);
		$this->setStarttime($a_barometer['starttime']);
		$this->setEndtime($a_barometer['endtime']);
		$this->setInterval($a_barometer['repeat_interval']);
		$this->setIntervalUnit($a_barometer['interval_unit']);
		$this->setFirstVoteBest($a_barometer['first_vote_best']);
		$this->setTextAnswer($a_barometer['text_answer']);
		$this->setObjId($a_barometer['obj_id']);
		$this->setRefId($a_barometer['ref_id']);
	}

	/**
	* create an new barometer
	*/
	function create()
	{
		global $ilDB;

		$this->id = $ilDB->nextId("feedback_items");
		/*$q = "INSERT INTO feedback_items (fb_id,title, description, anonymous,".
			"required, show_on, text_answer, votes, starttime, endtime, ".
			"repeat_interval, interval_unit, first_vote_best, ref_id,obj_id) VALUES(".
			$ilDB->quote($this->id, "integer").", ".
			$ilDB->quote($this->title, "text").", ".
			$ilDB->quote($this->description, "clob").", ".
			$ilDB->quote((int) $this->anonymous, "integer").", ".
			$ilDB->quote((int) $this->required, "integer").", ".
			$ilDB->quote($this->show_on, "text").", ".
			$ilDB->quote((int) $this->text_answer, "integer").", ".
			$ilDB->quote($this->votes, "clob").", ".
			$ilDB->quote((int) $this->starttime, "integer").", ".
			$ilDB->quote((int) $this->endtime, "integer").", ".
			$ilDB->quote((int) $this->interval, "integer").", ".
			$ilDB->quote((int) $this->interval_unit, "integer").", ".
			$ilDB->quote((int) $this->first_vote_best, "integer").", ".
			$ilDB->quote((int) $this->ref_id, "integer").", ".
			$ilDB->quote((int) $this->obj_id, "integer").")";*/
		$ilDB->insert("feedback_items", array(
			"fb_id" => array("integer", $this->id),
			"title" => array("text", $this->title),
			"description" => array("clob", $this->description),
			"anonymous" => array("integer", (int) $this->anonymous),
			"required" => array("integer", (int) $this->required),
			"show_on" => array("text", $this->show_on),
			"text_answer" => array("integer", (int) $this->text_answer),
			"votes" => array("clob", $this->votes),
			"starttime" => array("integer", (int) $this->starttime),
			"endtime" => array("integer", (int) $this->endtime),
			"repeat_interval" => array("integer", (int) $this->interval),
			"interval_unit" => array("integer", (int) $this->interval_unit),
			"first_vote_best" => array("integer", (int) $this->first_vote_best),
			"ref_id" => array("integer", (int) $this->ref_id),
			"obj_id" => array("integer", (int) $this->obj_id)
			));

//echo "-$q-";
		//$ilDB->manipulate($q);
	}

	/**
	* update a barometer
	*/
	function update()
	{
		global $ilDB;
		
		/*$q = "UPDATE feedback_items set ".
			"title=".$ilDB->quote($this->title, "text").", ".
			"description=".$ilDB->quote($this->description, "clob").", ".
			"anonymous=".$ilDB->quote((int) $this->anonymous, "integer").", ".
			"required=".$ilDB->quote((int) $this->required, "integer").", ".
			"show_on=".$ilDB->quote($this->show_on, "text").", ".
			"text_answer=".$ilDB->quote((int) $this->text_answer, "integer").", ".
			"votes=".$ilDB->quote($this->votes, "clob").", ".
			"starttime=".$ilDB->quote((int) $this->starttime, "integer").", ".
			"endtime=".$ilDB->quote((int) $this->endtime, "integer").", ".
			"repeat_interval=".$ilDB->quote((int) $this->interval, "integer").", ".
			"interval_unit=".$ilDB->quote((int) $this->interval_unit, "integer").", ".
			"first_vote_best=".$ilDB->quote((int) $this->first_vote_best, "integer").
			" WHERE fb_id=".$ilDB->quote($this->id, "integer");
		$ilDB->manipulate($q);*/
		
		$ilDB->update("feedback_items", array(
			"title" => array("text", $this->title),
			"description" => array("clob", $this->description),
			"anonymous" => array("integer", (int) $this->anonymous),
			"required" => array("integer", (int) $this->required),
			"show_on" => array("text", $this->show_on),
			"text_answer" => array("integer", (int) $this->text_answer),
			"votes" => array("clob", $this->votes),
			"starttime" => array("integer", (int) $this->starttime),
			"endtime" => array("integer", (int) $this->endtime),
			"repeat_interval" => array("integer", (int) $this->interval),
			"interval_unit" => array("integer", (int) $this->interval_unit),
			"first_vote_best" => array("integer", (int) $this->first_vote_best)
			),array(
			"fb_id" => array("integer", $this->id)
			));

	}

	/**
	* get a baromter by id
	*/
	function getBarometer()
	{
		global $ilDB;
		
		$q = "SELECT * FROM feedback_items WHERE fb_id = ".
			$ilDB->quote($this->id, "integer");
		$res = $ilDB->query($q);
		if ($row = $ilDB->fetchAssoc($res))
		{
			$this->setAllData($row);
		}
	}

	/**
	* Get a barometer by obj_id
	*/
	function getBarometerByObjId()
	{
		global $ilDB;

		$q = "SELECT * FROM feedback_items WHERE obj_id = ".
			$ilDB->quote($this->obj_id, "integer");
		$res = $ilDB->query($q);
		if($row = $ilDB->fetchAssoc($res))
		{
			$this->setAllData($row);
		}
	}

	/**
	* get a baromter by ref_id
	*/
	function getBarometerByRefId()
	{
		global $ilDB;
		$q = "SELECT * FROM feedback_items WHERE ref_id = ".
			$ilDB->quote($this->ref_id, "integer");
		$res = $ilDB->query($q);
		if($row = $ilDB->fetchAssoc($res))
		{
			$this->setAllData($row);
		}
	}

	/**
	* get a required baromter for a certain ref_id
	*/
	function getFeedback($required = 0)
	{
		global $ilDB;

		$filter_req = ($required)
			? ' required = 1 AND '
			: '';
		$q = "SELECT * FROM feedback_items WHERE ".
		 	$filter_req.
			" ((starttime <= ".$ilDB->quote(time(), "integer")." AND".
			" endtime >= ".$ilDB->quote(time(), "integer").
			") OR (starttime <= 0 AND endtime <= 0))";

		$res = $ilDB->query($q);
		if($row = $ilDB->fetchAssoc($res))
		{
			$this->setAllData($row);
		}
	}

	/**
	* get all barometers for a certain ref_id
	* if no ref_id is set we get all barometers,
	* this is needed for the personal desktop box.
	*/
	function getAllBarometer($a_show_inactive = 1,$a_only_req = 0)
	{
		global $ilDB;

		if ($this->ref_id)
		{
			 $where.=" ref_id = ".$ilDB->quote($this->ref_id, "integer");
		}
		if ($a_only_req == 1)
		{
			if ($where != '')
			{
				$where .= ' AND required = 1 ';
			}
			else
			{
				$where = ' required = 1 ';
			}
		}
		$q = "SELECT * FROM feedback_items WHERE ".$where;
		
		if ($a_show_inactive == 0)
		{
			if ($where != '')
			{
				$where = ' AND '.$where;
			}
			$q = "SELECT * FROM feedback_items WHERE ".
			" ((starttime <= ".$ilDB->quote(time(), "integer")." AND".
			" endtime >= ".$ilDB->quote(time(), "integer").
			") OR (starttime <= 0 AND endtime <=0 ))".$where;
		}
		$res = $ilDB->query($q);
		$i = 0;
		while($row = $ilDB->fetchAssoc($res))
		{
			$barometers[$i] = new ilFeedback();
			$barometers[$i]->setAllData($row);
			$i++;
		}

		return $barometers ? $barometers : array();
	}

	/**
	* delete a barometer and its results
	*/
	function delete()
	{
		global $ilDB;

		/*
		foreach ($this->ids as $k => $v)
		{
			$this->ids[$k] = $ilDB->quote($v,'integer');
		}
		*/

		$q = "DELETE FROM feedback_items WHERE ".
			$ilDB->in("fb_id", $this->ids, false, "integer");

			//"fb_id IN (".implode(',',$this->ids).")";
		$ilDB->manipulate($q);
		$q = "DELETE FROM feedback_results WHERE ".
			$ilDB->in("fb_id", $this->ids, false, "integer");
			//"fb_id IN (".implode(',',$this->ids).")";
		$ilDB->manipulate($q);
	}

	/**
	* save a feedback result
	*/
	function saveResult(){
		global $ilDB;
		//Save Only if there is not already a result from this user for this barometer

		if($this->canVote($this->user_id,$this->id)==1 || $this->user_id == 0)
		{
/*			$q = "INSERT INTO feedback_results (".
				"fb_id,user_id,vote,note,votetime) VALUES (".
				$ilDB->quote($this->id, "integer").", ".
				$ilDB->quote($this->user_id, "integer").", ".
				$ilDB->quote($this->vote, "integer").", ".
				$ilDB->quote($this->note, "clob").", ".
				$ilDB->quote(time(), "integer").")";
			$ilDB->manipulate($q);*/
			$ilDB->insert("feedback_results", array(
				"fb_id" => array("integer", $this->id),
				"user_id" => array("integer", $this->user_id),
				"vote" => array("integer", $this->vote),
				"note" => array("clob", $this->note),
				"votetime" => array("integer", time())
				));
		}
	}

	/**
	* check if a certain user has already answerd a certain barometer
	*/
	function canVote($a_user_id,$a_fb_id){
		global $ilDB, $ilUser;
		include_once('Modules/Course/classes/class.ilCourseParticipants.php');
		
		$q = "SELECT * FROM feedback_results WHERE ".
			"fb_id = ".$ilDB->quote($a_fb_id, "integer")." AND ".
			"user_id = ".$ilDB->quote($a_user_id, "integer").
			" ORDER BY votetime DESC";;
		$res = $ilDB->query($q);

		$row_results = $ilDB->fetchAssoc($res);
		$q = "SELECT * FROM feedback_items WHERE ".
			"fb_id = ".$ilDB->quote($a_fb_id, "integer");
		$res1 = $ilDB->query($q);
		$row_items = $ilDB->fetchAssoc($res1);
		
		// check end time
		if (!($row_items["starttime"]<=time() && $row_items["endtime"]>=time()))
		{
			return (0);
		}
		
		$members_obj = ilCourseParticipants::_getInstanceByObjId($row_items['obj_id']);
		
		//Check if the user is Member of that course, otherwise its not necessary that he votes
		if(($res->numRows()==0) && $members_obj->isAssigned($ilUser->getId()))
			return(1);

		if($members_obj->isAssigned($ilUser->getId()))
		{
			if($row_items['repeat_interval'] > 0){
				$interval = $this->interval2seconds($row_items['repeat_interval'], $row_items['interval_unit']);
				if((time() - $row_results['votetime']) >= $interval){
					return(1);
				}
			}
		}


		return(0);
	}

	/**
	* get the information to display on the charts
	*/
	function getChartData()
	{
		global $ilDB;

		if($this->user_id!='')
		{
			$user_filter = ' feedback_results.user_id = '.$ilDB->quote($this->user_id, "integer").' AND ';
		}
		$q='SELECT usr_data.login, feedback_results.user_id,feedback_results.vote, feedback_results.votetime FROM'.
			' feedback_results LEFT JOIN usr_data ON usr_data.usr_id = feedback_results.user_id WHERE '.
			' '.$user_filter.' feedback_results.fb_id='.$ilDB->quote($this->id, "integer").
			' ORDER BY feedback_results.votetime,usr_data.login';

		$res = $ilDB->query($q);
		$i=0;
		$j=1;
		$k=1;
		$n=0;
		$pvt='';
		$datapie[0][0] = 'Vote';
		while($row = $ilDB->fetchAssoc($res))
		{
			$row["timelabel"] = date("d.m.Y H:i", $row["votetime"]);
			if(!isset($tmp[$row['user_id']]))
			{
				$tmp[$row['user_id']]=$j++;
			}
			if(!isset($tmpv[$row['vote']]))
			{
				$tmpv[$row['vote']] = $k++;
			}
			$data[$i][0] = $row['timelabel'];
			$data[$i][$tmp[$row['user_id']]] = $row['vote'];
			$legend[$row['login']] = $row['login'];
			$legendpie[$row['vote']] = $row['vote'];


			$datapie[0][$tmpv[$row['vote']]]++;
			if($row['votetime']!=$pvt){
				$i++;

			}
			$pvt=$row['votetime'];

			$table[$n]['votetime'] = $row['timelabel'];
			$table[$n]['user'] = $row['login'];
			$table[$n]['vote'] = $row['vote'];
			$n++;
		}
		if(is_array($data))
		{
			foreach($data as $k => $v)
			{
				/* Look if there are set all Y-values. If a user has no Y value for a certain date, the Y value has to be set to something otherwise PHPlot will not work correctly.
				The array keys have also to be sorted for PHPlot */
				if(count($v)<=count($tmp)){
					for($i=1;$i<=count($tmp);$i++)
						if(!isset($v[$i]))
							$data[$k][$i]='';
				}
				ksort($data[$k]);
			}
		}
		return(array('data' => $data,'legend' => $legend,'legendpie' => $legendpie, 'datapie' => $datapie, 'table' => $table));

	}
	/**
	* get the comments of an user or all users
	*/
	function getNotes()
	{
		global $ilDB;
		
		if($this->user_id!='')
		{
			$user_filter = ' AND feedback_results.user_id='.$ilDB->quote($this->user_id, "integer");
		}
		$q='SELECT usr_data.login, feedback_results.user_id,feedback_results.note,feedback_results.vote, feedback_results.votetime FROM'.
			' feedback_results LEFT JOIN usr_data ON usr_data.usr_id = feedback_results.user_id'.
			' WHERE  feedback_results.note<>""'.
			' '.$user_filter.' AND feedback_results.fb_id='.$ilDB->quote($this->id, "integer").
			' ORDER BY feedback_results.votetime,usr_data.login';

		$res = $ilDB->query($q);
		$i=0;
		while($row = $ilDB->fetchAssoc($res))
		{
			$row["timelabel"] = date("d.m.Y H:i", $row["votetime"]);
			$data[$i]['user'] = $row['login'];
			$data[$i]['votetime'] = $row['timelabel'];
			$data[$i]['note'] = $row['note'];
			$i++;
		}
		return($data);
	}
	/**
	* get all users that have answerd a certain barometer
	*/
	function getResultUsers()
	{
		global $ilDB;

		$q='SELECT distinct(usr_data.login), feedback_results.user_id  FROM'.
			' feedback_results LEFT JOIN usr_data ON usr_data.usr_id = feedback_results.user_id'.
			' WHERE feedback_results.fb_id='.$ilDB->quote($this->id, "integer").
			' ORDER BY feedback_results.votetime,usr_data.login';

		$res = $ilDB->query($q);

		while($row = $ilDB->fetchAssoc($res))
		{
 			$users[$row['user_id']] = $row['login'];
		}
		return($users);
	}

	/**
	* convert a interval with unit to seconds
	* ex. 4 days to seconds
	*/
	function interval2seconds($a_interval,$a_interval_unit)
	{
		switch($a_interval_unit)
		{
			case 1:
				//Days
				$multi_by = 24 * 60 * 60;
				break;
			case 2:
				//Weeks
				$mult_by = 7 * 24 * 60 * 60;
				break;
			case 3:
				// Months
				$mult_by = 30 *  24 * 60 *60;
				break;

			default:
				//Hours
				$mult_by = 60 * 60;
				break;
		}
		$seconds = $a_interval * $mult_by;
		return($seconds);
	}
}
?>