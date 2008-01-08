<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
class ilFeedback {
	function ilFeedback($a_id = 0){
		if($a_id > 0){
			$this->id = $a_id;
			$this->getBarometer();
		}

	}
	/* Set / Get Methods */
	function setId($a_id){
		$this->id = $a_id;
	}
	function getId(){
		return($this->id);
	}
	function setTitle($a_title){
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
	function create(){
		global $ilDB;
		$q = "INSERT INTO feedback_items (title, description, anonymous,".
			"required, show_on, text_answer, votes, starttime, endtime, ".
			"repeat_interval, interval_unit, first_vote_best, ref_id,obj_id) VALUES(".
			$ilDB->quote($this->title).", ".
			$ilDB->quote($this->description).", ".
			$ilDB->quote($this->anonymous).", ".
			$ilDB->quote($this->required).", ".
			$ilDB->quote($this->show_on).", ".
			$ilDB->quote($this->text_answer).", ".
			$ilDB->quote($this->votes).", ".
			$ilDB->quote($this->starttime).", ".
			$ilDB->quote($this->endtime).", ".
			$ilDB->quote($this->interval).", ".
			$ilDB->quote($this->interval_unit).", ".
			$ilDB->quote($this->first_vote_best).", ".
			$ilDB->quote($this->ref_id).", ".
			$ilDB->quote($this->obj_id).")";
		$ilDB->query($q);
		$this->id = $ilDB->getLastInsertId();


	}

	/**
	* update a barometer
	*/
	function update(){
		global $ilDB;
		$q = "UPDATE feedback_items set ".
			"title=".$ilDB->quote($this->title).", ".
			"description=".$ilDB->quote($this->description).", ".
			"anonymous=".$ilDB->quote($this->anonymous).", ".
			"required=".$ilDB->quote($this->required).", ".
			"show_on=".$ilDB->quote($this->show_on).", ".
			"text_answer=".$ilDB->quote($this->text_answer).", ".
			"votes=".$ilDB->quote($this->votes).", ".
			"starttime=".$ilDB->quote($this->starttime).", ".
			"endtime=".$ilDB->quote($this->endtime).", ".
			"repeat_interval=".$ilDB->quote($this->interval).", ".
			"interval_unit=".$ilDB->quote($this->interval_unit).", ".
			"first_vote_best=".$ilDB->quote($this->first_vote_best)." WHERE fb_id=".$ilDB->quote($this->id);

		$ilDB->query($q);
		$this->id = $ilDB->getLastInsertId();


	}

	/**
	* get a baromter by id
	*/
	function getBarometer(){
		global $ilDB;
		$q = "SELECT * FROM feedback_items WHERE fb_id=".$ilDB->quote($this->id);
		$res = $ilDB->query($q);
		if($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
			$this->setAllData($row);


	}
	/**
	* get a barometer by obj_id
	*/
	function getBarometerByObjId(){
		global $ilDB;
		$q = "SELECT * FROM feedback_items WHERE obj_id=".$ilDB->quote($this->obj_id);
		$res = $ilDB->query($q);
		if($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
			$this->setAllData($row);

	}

	/**
	* get a baromter by ref_id
	*/
	function getBarometerByRefId(){
		global $ilDB;
		$q = "SELECT * FROM feedback_items WHERE ref_id=".$ilDB->quote($this->ref_id);
		$res = $ilDB->query($q);
		if($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
			$this->setAllData($row);

	}

	/**
	* get a required baromter for a certain ref_id
	*/
	function getFeedback($required=0){
		global $ilDB;
		$filter_req = ($required) ? ' required=1 AND ' : '';
		$q = "SELECT * FROM feedback_items WHERE ".
		 	$filter_req.
			" ((starttime<=UNIX_TIMESTAMP() AND".
			" endtime>=UNIX_TIMESTAMP()) OR(starttime<=0 AND endtime<=0))";
		$res = $ilDB->query($q);
		if($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC))
			$this->setAllData($row);
	}

	/**
	* get all barometers for a certain ref_id
	* if no ref_id is set we get all barometers,
	* this is needed for the personal desktop box.
	*/
	function getAllBarometer($a_show_inactive=1,$a_only_req=0){
		global $ilDB;

		if($this->ref_id)
			 $where.=" ref_id=".$ilDB->quote($this->ref_id);
		if($a_only_req==1)
			if($where!='')
				$where .= ' AND required=1 ';
			else
				$where = ' required = 1 ';
		$q = "SELECT * FROM feedback_items WHERE ".$where;
		
		if($a_show_inactive==0){
			if($where!='')
				$where = ' AND'.$where;
			$q = "SELECT * FROM feedback_items WHERE ".
			" ((starttime<=UNIX_TIMESTAMP() AND".
			" endtime>=UNIX_TIMESTAMP()) OR(starttime<=0 AND endtime<=0))".$where;
		}
		$res = $ilDB->query($q);
		$i = 0;
		while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
			$barometers[$i] = new ilFeedback();
			$barometers[$i]->setAllData($row);
			$i++;
		}

		return $barometers ? $barometers : array();
	}
	/**
	* delete a barometer and its results
	*/
	function delete(){
		global $ilDB;
		foreach ($this->ids as $k => $v)
			$this->ids[$k] = $ilDB->quote($v);
		$q = "DELETE FROM feedback_items WHERE ".
			"fb_id IN (".implode(',',$this->ids).")";
		$ilDB->query($q);
		$q = "DELETE FROM feedback_results WHERE ".
			"fb_id IN (".implode(',',$this->ids).")";
		$ilDB->query($q);
	}

	/**
	* save a feedback result
	*/
	function saveResult(){
		global $ilDB;
		//Save Only if there is not already a result from this user for this barometer

		if($this->canVote($this->user_id,$this->id)==1){
			$q = "INSERT INTO feedback_results (".
				"fb_id,user_id,vote,note,votetime) VALUES (".
				$ilDB->quote($this->id).", ".
				$ilDB->quote($this->user_id).", ".
				$ilDB->quote($this->vote).", ".
				$ilDB->quote($this->note).", UNIX_TIMESTAMP())";
			$ilDB->query($q);
		}
	}

	/**
	* check if a certain user has already answerd a certain barometer
	*/
	function canVote($a_user_id,$a_fb_id){
		global $ilDB, $ilUser;
		include_once('Modules/Course/classes/class.ilCourseParticipants.php');
		
		$q = "SELECT * FROM feedback_results WHERE ".
			"fb_id=".$ilDB->quote($a_fb_id)." AND ".
			"user_id=".$ilDB->quote($a_user_id)." ORDER BY votetime DESC";;
		$res = $ilDB->query($q);

		$row_results = $res->fetchRow(MDB2_FETCHMODE_ASSOC);
		$q = "SELECT * FROM feedback_items WHERE ".
			"fb_id = ".$ilDB->quote($a_fb_id);
		$res1 = $ilDB->query($q);
		$row_items = $res1->fetchRow(MDB2_FETCHMODE_ASSOC);
		
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
	function getChartData(){
		global $ilDB;
		if($this->user_id!='')
			$user_filter = ' feedback_results.user_id='.$ilDB->quote($this->user_id).' AND ';
		$q='SELECT usr_data.login, feedback_results.user_id,feedback_results.vote, feedback_results.votetime, FROM_UNIXTIME(feedback_results.votetime,"%d.%m.%Y %H:%i") as timelabel FROM'.
			' feedback_results LEFT JOIN usr_data ON usr_data.usr_id = feedback_results.user_id WHERE '.
			' '.$user_filter.' feedback_results.fb_id='.$ilDB->quote($this->id).
			' ORDER BY feedback_results.votetime,usr_data.login';

		$res = $ilDB->query($q);
		$i=0;
		$j=1;
		$k=1;
		$n=0;
		$pvt='';
		$datapie[0][0] = 'Vote';
		while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){

			if(!isset($tmp[$row['user_id']]))
				$tmp[$row['user_id']]=$j++;
			if(!isset($tmpv[$row['vote']]))
				$tmpv[$row['vote']] = $k++;
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
		if(is_array($data)){
			foreach($data as $k => $v){
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
	function getNotes(){
	global $ilDB;
		if($this->user_id!='')
			$user_filter = ' AND feedback_results.user_id='.$ilDB->quote($this->user_id);
		$q='SELECT usr_data.login, feedback_results.user_id,feedback_results.note,feedback_results.vote, feedback_results.votetime, FROM_UNIXTIME(feedback_results.votetime,"%d.%m.%Y %H:%i") as timelabel FROM'.
			' feedback_results LEFT JOIN usr_data ON usr_data.usr_id = feedback_results.user_id'.
			' WHERE  feedback_results.note<>""'.
			' '.$user_filter.' AND feedback_results.fb_id='.$ilDB->quote($this->id).
			' ORDER BY feedback_results.votetime,usr_data.login';

		$res = $ilDB->query($q);
		$i=0;
		while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
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
	function getResultUsers(){

		global $ilDB;
		$q='SELECT distinct(usr_data.login), feedback_results.user_id  FROM'.
			' feedback_results LEFT JOIN usr_data ON usr_data.usr_id = feedback_results.user_id'.
			' WHERE feedback_results.fb_id='.$ilDB->quote($this->id).
			' ORDER BY feedback_results.votetime,usr_data.login';

		$res = $ilDB->query($q);

		while($row = $res->fetchRow(MDB2_FETCHMODE_ASSOC)){
 			$users[$row['user_id']] = $row['login'];
		}
		return($users);
	}

	/**
	* convert a interval with unit to seconds
	* ex. 4 days to seconds
	*/
	function interval2seconds($a_interval,$a_interval_unit){
		switch($a_interval_unit){
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