<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* GUI class for feedback services. Feedbacks are little polls that are embedded
* into the info screen and currently used by courses only.
*
* @author	Helmuth Antholzer <helmuth.antholzer@maguma.com>
* @version	$Id$
* @ingroup	ServicesFeedback
*
* @ilCtrl_Calls ilFeedbackGUI:
*/
class ilFeedbackGUI
{
	/**
	* Constructor
	*/
	function ilFeedbackGUI(){
		global $ilCtrl;
		global $lng;
		$this->update = 0;

		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule('barometer');

	}

	/**
	* Execute current command.
	*/
	function &executeCommand()
	{
		global $ilAccess,$ilErr;

		if(isset($_SESSION["message"]))	{
			if(isset($_SESSION['error_post_vars']['cmd']['update'])){
				//var_dump($_SESSION);
				$this->ctrl->setCmd('edit');
			}else
				$this->ctrl->setCmd('addBarometer');
		}
		$params = $this->ctrl->getParameterArray($this);

			$cmd = $this->ctrl->getCmd('fbList');
			//No write permissions, so this has to be a normal user..

			if((!$ilAccess->checkAccess('write','edit',$params['ref_id']))&&(in_array($cmd,array('fbList','save','delete','update','edit'))))
				$cmd = 'showBarometer';

			$next_class = $this->ctrl->getNextClass($this);
			switch($next_class){
				default:
					return($this->$cmd());
				break;
			}
	}

	function getFeedbackHTML(){
		global $lng;

		$tpl = new ilTemplate("tpl.feedbacklist.html", true, true, "Services/Feedback");
		$tpl->setVariable("TXT_FEEDBACK_TITLE", $lng->txt("stimmungsb"));
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TXT_STATUS", $lng->txt("status"));
		$tpl->setVariable("TXT_ACTIVE_TIME", $lng->txt("active_time"));
		$tpl->setVariable("TXT_OPTIONS", $lng->txt("options"));

		$tpl->setVariable("TXT_NEW_VOTE", $lng->txt("options"));
		$tpl->parseCurrentBlock();
		return($tpl->get());
	}

	/**
	* List all feedback items.
	*/
	function fbList(){

		include_once('Services/Feedback/classes/class.ilFeedback.php');
		$tbl =& $this->__initTableGUI();
		$cnt=0;
		$ilFeedback = new ilFeedback();
		$ilFeedback->setRefId($_GET['ref_id']);
		$barometers = $ilFeedback->getAllBarometer();

		if(is_Array($barometers)){
			foreach($barometers as $barometer){
				$rows[$cnt]['checkbox'] = ilUtil::formCheckbox(0,"barometer[]",$barometer->getId());;
				$rows[$cnt]['title'] = $barometer->getTitle();
				$rows[$cnt]['status'] = 'Aktive';
				if(($barometer->getStarttime()>0) && ($barometer->getStarttime()>0)){
					$rows[$cnt]['running'] = date('d.m.Y H:i',$barometer->getStarttime()).' - '.date('d.m.Y H:i',$barometer->getEndtime());
					if(($barometer->getStarttime()<=time())&&($barometer->getEndtime()>=time()))
						$rows[$cnt]['status'] = $this->lng->txt('active');
					else
						$rows[$cnt]['status'] = $this->lng->txt('inactive');

				}else{
					$rows[$cnt]['running'] = '';
					$rows[$cnt]['status'] = $this->lng->txt('active');
				}
				$rows[$cnt]['options'] = $this->getButtons($barometer->getId());
				$barometer_ids[]=$barometer->getId();
				$cnt++;
			}
		}

		$tbl->tpl->setCurrentBlock("tbl_form_header");
		$tbl->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tbl->tpl->parseCurrentBlock();
		$tbl->setTitle($this->lng->txt("feedb_feedback_list"),
			"icon_feedb.gif", $this->lng->txt("feedb_feedback_list"));
		$tbl->setHeaderNames(array("",$this->lng->txt("title"),$this->lng->txt("status"),$this->lng->txt("time"),$this->lng->txt("options")));
		$tbl->setHeaderVars(array("checkbox","title","stauts","running","options"));
		$tbl->setData($rows);
		$tbl->setOffset(0);
		$tbl->setLimit(0);
		$tbl->disable('sort');
		$tbl->setMaxCount(count($rows));
		$tbl->tpl->setCurrentBlock("plain_button");
		$tbl->tpl->setVariable("PBTN_NAME","addBarometer");
		$tbl->tpl->setVariable("PBTN_VALUE",$this->lng->txt("barometer_add"));
		$tbl->tpl->parseCurrentBlock();
		$tbl->tpl->setCurrentBlock("plain_buttons");
		$tbl->tpl->parseCurrentBlock();
		if (!empty($rows))
		{
			// set checkbox toggles
			$tbl->tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tbl->tpl->setVariable("JS_VARNAME","barometer");
			$tbl->tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($barometer_ids));
			$tbl->tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tbl->tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tbl->tpl->parseCurrentBlock();
		}


		$tbl->tpl->setVariable("COLUMN_COUNTS",5);
		$tbl->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tbl->tpl->setCurrentBlock("tbl_action_select");
		//$tbl->tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",array('delete'),false,true));
		$tbl->tpl->setVariable("BTN_NAME","delete");
		$tbl->tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$tbl->tpl->parseCurrentBlock();
		//$tbl->setOrderColumn('title');
		$tbl->setOrderDirection('asc');
		$tbl->setColumnWidth(array("","25%","25%","25%",""));
		#$tbl->disable('sort');
		$tbl->setFooter("tblfooter");
		$tbl->render();

		return($tbl->tpl->get());

	}
	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";
		return new ilTableGUI(0,false);

	}
	function delete(){
		if(is_Array($_POST['barometer'])){
			include_once('Services/Feedback/classes/class.ilFeedback.php');
			$ilFB = new ilFeedback();
			$ilFB->setIds(ilUtil::stripSlashes($_POST['barometer']));
			$ilFB->delete();
		}
		ilUtil::redirect($this->ctrl->getLinkTarget($this, 'fbList'));
	}
	function stats(){

		include_once('Services/Feedback/classes/class.ilFeedback.php');


		$tpl = new ilTemplate("tpl.feedback_stats.html", true, true, "Services/Feedback");
		$feedback = new ilFeedback();
		$feedback->setUserId(ilUtil::stripSlashes($_POST['chart_user']));
		$feedback->setId($_GET['barometer_id']);
		$chartdata = $feedback->getChartData();
		$data = $chartdata['data'];
		$legend = $chartdata['legend'];
		$legendpie = $chartdata['legendpie'];
		$datapie = $chartdata['datapie'];
		$datatable = $chartdata['table'];

		$chartlines = '<img src="Services/Feedback/showchart.php?chart_type=lines&title='.base64_encode($this->lng->txt('chart_users')).'&data='.base64_encode(serialize($data)).'&legend='.base64_encode(serialize($legend)).'">';
		$chartpie = '<img src="Services/Feedback/showchart.php?chart_type=pie&title='.base64_encode($this->lng->txt('chart_votes')).'&data='.base64_encode(serialize($datapie)).'&legend='.base64_encode(serialize($legendpie)).'">';

		$chart_type['lines'] = $this->lng->txt('lines');
		$chart_type['pie'] = $this->lng->txt('pie');
		$chart_type['table'] = $this->lng->txt('table');

		$chart_user[0] = $this->lng->txt('all_users');
		$chart_user = $feedback->getResultUsers();

		$tpl->setVariable("TXT_USER",$this->lng->txt('user'));
		$tpl->setVariable("TXT_CHART_TYPE",$this->lng->txt('chart_type'));
		switch($_POST['chart_type']){
			case 'pie':
				$tpl->setVariable("CHART_PIE", $chartpie);
				break;
			case 'table':
				if(is_array($datatable)){
					$tpl->setCurrentBlock('tablerow');
					$tpl->setVariable('TXT_TABLE_USERNAME',$this->lng->txt('username'));
					$tpl->setVariable('TXT_TABLE_VOTE',$this->lng->txt('vote'));
					$tpl->setVariable('TXT_TABLE_DATE', $this->lng->txt('date'));
					$tpl->parseCurrentBlock();
					$i=0;
					foreach($datatable as $tablerow){
						$tpl->setVariable('VALUE_NUM',(($i++ % 2) ? 1 : 2));
						$tpl->setVariable('VALUE_VOTETIME', $tablerow['votetime']);
						$tpl->setVariable('VALUE_USER', $tablerow['user']);
						$tpl->setVariable('VALUE_VOTE', $tablerow['vote']);
						$tpl->parseCurrentBlock();
					}
				}
				break;
			default:
				$tpl->setVariable("CHART_LINES", $chartlines);
				break;
		}
		$tpl->setVariable("SELECTBOX_CHART_TYPE", $this->selectbox(ilUtil::stripSlashes($_POST['chart_type']),'chart_type',$chart_type,'onChange="document.forms[0].submit()"'));
		$tpl->setVariable("SELECTBOX_USER", $this->selectbox(ilUtil::stripSlashes($_POST['chart_user']),'chart_user',$chart_user,'onChange="document.forms[0].submit()"',$this->lng->txt('all_users')));




		$comments = $feedback->getNotes();
		if(is_Array($comments)){
			$tpl->setCurrentBlock('comment_row');
			$tpl->setVariable('TXT_USERNAME',$this->lng->txt('username'));
			$tpl->setVariable('TXT_COMMENT',$this->lng->txt('comment'));
			$tpl->setVariable('TXT_DATE', $this->lng->txt('date'));
			$tpl->parseCurrentBlock();
			$i=0;
			foreach ($comments as $comment){
				$tpl->setVariable('VALUE_NUM',(($i++ % 2) ? 1 : 2));
				$tpl->setVariable('VALUE_LOGIN',$comment['user']);
				$tpl->setVariable('VALUE_DATE',$comment['votetime']);
				$tpl->setVariable('VALUE_NOTE',$comment['note']);
				$tpl->parseCurrentBlock();
			}
		}
		$tpl->parseCurrentBlock();
		return($tpl->get());
	}

	/**
	* Edit screen for single feedback item.
	*/
	function edit(){
		include_once('Services/Feedback/classes/class.ilFeedback.php');

		$ilFB = new ilFeedback($_GET['barometer_id']);
		$tpl = new ilTemplate("tpl.feedback_edit.html", true, true, "Services/Feedback");

		$data['title'] = $_POST['title'] ? $_POST['title'] : $ilFB->getTitle();
		$data['description'] = $_POST['text'] ? $_POST['text'] : $ilFB->getDescription();
		$data['anonymous'] = ($_POST['anonymous']!='') ? $_POST['anonymous'] : $ilFB->getAnonymous();
		$data['required'] = ($_POST['required']!='') ? $_POST['required'] : $ilFB->getRequired();
		$data['show_on'] = $_POST['show_on'] ? $_POST['show_on'] : $ilFB->getShowOn();
		$data['vote'] = $_POST['vote'] ? $_POST['vote'] : unserialize($ilFB->getVotes());
		if($_POST['extra_votes']=='')
			$_POST['extra_votes'] = count($data['votes']);
		$data['text_answer'] = ($_POST['text_answer']!='') ? $_POST['text_answer'] : $ilFB->getTextAnswer();
		if($ilFB->getStarttime()>=0||isset($_POST['start_day'])){
			$data['start_day'] = $_POST['start_day'] ? $_POST['start_day'] : date('d',$ilFB->getStarttime());
			$data['start_month'] =$_POST['start_month'] ? $_POST['start_month'] : date('m',$ilFB->getStarttime());
			$data['start_year'] = $_POST['start_year'] ? $_POST['start_year'] :date('Y',$ilFB->getStarttime());
			$data['start_hour'] = $_POST['start_hour'] ? $_POST['start_hour'] :date('h',$ilFB->getStarttime());
			$data['start_minute'] = $_POST['start_minute'] ? $_POST['start_minute'] :date('i',$ilFB->getStarttime());
		}
		if($ilFB->getEndtime()>=0||isset($_POST['end_day'])){
			$data['end_day'] = $_POST['end_day'] ? $_POST['end_day'] :date('d',$ilFB->getEndtime());
			$data['end_month'] = $_POST['end_month'] ? $_POST['end_month'] :date('m',$ilFB->getEndtime());
			$data['end_year'] = $_POST['end_year'] ? $_POST['end_year'] :date('Y',$ilFB->getEndtime());
			$data['end_hour'] = $_POST['end_hour'] ? $_POST['end_hour'] :date('h',$ilFB->getEndtime());
			$data['end_minute'] = $_POST['end_minute'] ? $_POST['end_minute'] :date('i',$ilFB->getEndtime());
		}

		$data['interval'] = $_POST['interval'] ? $_POST['interval'] : $ilFB->getInterval();
		$data['interval_unit'] = $_POST['interval_unit'] ? $_POST['interval_unit'] : $ilFB->getIntervalUnit();
		$data['first_vote_best'] = ($_POST['first_vote_best']!='') ? $_POST['first_vote_best'] : $ilFB->getFirstVoteBest();

		$this->ctrl->setParameter($this,"barometer_id",$_GET['barometer_id']);
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_HEADER", $this->lng->txt("feedb_edit_feedback"));
		$tpl->setVariable("IMG_FEEDB", ilUtil::getImagePath("icon_feedb.gif"));
		$tpl->setVariable("ALT_FEEDB", $this->lng->txt("feedb_edit_feedback"));
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$tpl->setVariable("VALUE_TITLE",$data['title']);
		//$tpl->setVariable("TXT_TEXT", $this->lng->txt("text"));
		$tpl->setVariable("TXT_TIME", $this->lng->txt("feedb_time"));
		$tpl->setVariable("VALUE_TEXT", $data['description']);
		$tpl->setVariable("TXT_ANONYMOUS", $this->lng->txt("anonymous"));

		if($data["anonymous"]=="0")
			$tpl->setVariable("ANONYMOUS_NO", " checked");
		else
			$tpl->setVariable("ANONYMOUS_YES", " checked");
		$tpl->setVariable("TXT_YES", $this->lng->txt("yes"));
		$tpl->setVariable("TXT_NO", $this->lng->txt("no"));
		$tpl->setVariable("TXT_TYPE", $this->lng->txt("type"));
		$typeSB[0] = $this->lng->txt('optional');
		$typeSB[1] = $this->lng->txt('required');

		$tpl->setVariable("SELECT_TYPE",
			ilUtil::formSelect($data['required'],'type',$typeSB,false,true));

		$tpl->setVariable("TXT_REQUIRED", $this->lng->txt("required"));
		if($data["show_on"]=="course")
			$tpl->setVariable("SHOW_CHANGE_COURSE_SELECTED", "selected");
		else
			$tpl->setVariable("SHOW_LOGIN_SELECTED", "selected");
 		$tpl->setVariable("TXT_LOGIN", $this->lng->txt("login"));
		$tpl->setVariable("TXT_CHANGE_COURSE", $this->lng->txt("change_course"));
		if($data["text_answer"]=="1")
			$tpl->setVariable("TEXT_ANSWER_YES", " checked");
		else
			$tpl->setVariable("TEXT_ANSWER_NO", " checked");
		$tpl->setVariable("TXT_TEXT_ANSWER", $this->lng->txt("text_answer"));
		$tpl->setVariable("TXT_VOTES", $this->lng->txt("votes"));
		$extra_votes = $_POST["extra_votes"] ? $_POST["extra_votes"]+1 : (count($data["vote"])-2);
		if ($extra_votes<1) $extra_votes=1;
		$tpl->setVariable("VALUE_EXTRA_VOTES", $extra_votes);

		for($i=1;$i < 3+$extra_votes ;$i++){
			$tpl->setCurrentBlock("vote");
			$tpl->setVariable("TXT_TEXT",$this->lng->txt("text"));
			$tpl->setVariable("VALUE_VOTE_TEXT",$data["vote"][$i]);
			$tpl->setVariable("VOTE_NUM",$i);
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("TXT_DAY",$this->lng->txt("day"));
		$tpl->setVariable("SELECT_ACTIVATION_START_DAY",$this->getDateSelect('day','start_day',$data['start_day']));
		$tpl->setVariable("SELECT_ACTIVATION_START_MONTH",$this->getDateSelect('month','start_month',$data['start_month']));
		$tpl->setVariable("SELECT_ACTIVATION_START_YEAR",$this->getDateSelect('year','start_year',$data['start_year']));
		$tpl->setVariable("SELECT_ACTIVATION_START_HOUR",$this->getDateSelect('hour','start_hour',$data['start_hour']));
		$tpl->setVariable("SELECT_ACTIVATION_START_MINUTE",$this->getDateSelect('minute','start_minute',$data['start_minute']));
		$tpl->setVariable("SELECT_ACTIVATION_END_DAY",$this->getDateSelect('day','end_day',$data['end_day']));
		$tpl->setVariable("SELECT_ACTIVATION_END_MONTH",$this->getDateSelect('month','end_month',$data['end_month']));
		$tpl->setVariable("SELECT_ACTIVATION_END_YEAR",$this->getDateSelect('year','end_year',$data['end_year']));
		$tpl->setVariable("SELECT_ACTIVATION_END_HOUR",$this->getDateSelect('hour','end_hour',$data['end_hour']));
		$tpl->setVariable("SELECT_ACTIVATION_END_MINUTE",$this->getDateSelect('minute','end_minute',$data['end_minute']));

		$tpl->setVariable("TXT_MONTH",$this->lng->txt("month"));
		$tpl->setVariable("TXT_YEAR",$this->lng->txt("year"));
		$tpl->setVariable("TXT_FROM",$this->lng->txt("from"));
		$tpl->setVariable("TXT_UNTIL",$this->lng->txt("until"));

		$tpl->setVariable("TXT_DURATION",$this->lng->txt("duration"));
		$tpl->setVariable("TXT_HOURS",$this->lng->txt("hours"));
		$tpl->setVariable("TXT_DAYS",$this->lng->txt("days"));
		$tpl->setVariable("TXT_WEEKS",$this->lng->txt("weeks"));
		$tpl->setVariable("TXT_MONTHS",$this->lng->txt("months"));
		$tpl->setVariable("TXT_REPEAT",$this->lng->txt("repeat"));
		$tpl->setVariable("TXT_FTEXT",$this->lng->txt("text"));

		for($i=0;$i < 25;$i++){
			$interval[$i]=$i;
		}
		$tpl->setVariable("SELECT_INTERVAL",ilUtil::formSelect($data['interval'],'interval',$interval,false,true));

		$interval_unitSB[0] = $this->lng->txt('hours');
		$interval_unitSB[1] = $this->lng->txt('days');
		$interval_unitSB[2] = $this->lng->txt('weeks');
		$interval_unitSB[3] = $this->lng->txt('months');
		$tpl->setVariable("SELECT_INTERVAL_UNIT",ilUtil::formSelect($data['interval_unit'],'interval_unit',$interval_unitSB,false,true));

		$tpl->setVariable("TXT_FIRST_VOTE",$this->lng->txt("first_vote"));
		$tpl->setVariable("TXT_BEST",$this->lng->txt("best"));
		$tpl->setVariable("TXT_WORST",$this->lng->txt("worst"));
		if($data["first_vote_best"]==1)
			$tpl->setVariable("BEST_CHECKED", "checked");
		else
			$tpl->setVariable("WORST_CHECKED", "checked");
		$tpl->setVariable("TXT_VOTE", $this->lng->txt("vote"));
		$tpl->setVariable("TXT_NEW_VOTE", $this->lng->txt("new_vote"));
		$tpl->setVariable("CMD_ADDVOTE", 'edit');
		$tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$tpl->setVariable("CMD_SUBMIT", "update");
		$tpl->parseCurrentBlock();
		return($tpl->get());

	}
	function update(){
		$this->update=1;

		$this->save();
	}
	function save(){
	//print_r($_POST);
		global $ilias;
		include_once('Services/Feedback/classes/class.ilFeedback.php');
		$params = $this->ctrl->getParameterArray($this);
		$tmp_votes=array();
		$vote_cnt=1;
		foreach ($_POST['vote'] as $k=>$v)
		{
			if(trim($v))
			{
				$tmp_votes[$vote_cnt]=$v;
				$vote_cnt++;
			}
		}
		if(($_POST['title']=='')||($_POST['text']=='')|| $vote_cnt<3){
			$this->ctrl->setParameter($this,'a','32');
			$ilias->raiseError($this->lng->txt('missing_fields'),$ilias->error_obj->MESSAGE);
		}
		$ilFeedback = new  ilFeedback();
		$ilFeedback->setTitle(ilUtil::stripSlashes($_POST['title']));
		$ilFeedback->setDescription(ilUtil::stripSlashes($_POST['text']));
		$ilFeedback->setAnonymous(ilUtil::stripSlashes($_POST['anonymous']));
		$ilFeedback->setRequired(ilUtil::stripSlashes($_POST['type']));
		$ilFeedback->setShowOn(ilUtil::stripSlashes($_POST['show_on']));
		$ilFeedback->setTextAnswer(ilUtil::stripSlashes($_POST['text_answer']));
		$ilFeedback->setVotes(serialize($tmp_votes));
		$ilFeedback->setStarttime(mktime(ilUtil::stripSlashes($_POST['start_hour']),
			ilUtil::stripSlashes($_POST['start_minute']),0,
			ilUtil::stripSlashes($_POST['start_month']),
			ilUtil::stripSlashes($_POST['start_day']),
			ilUtil::stripSlashes($_POST['start_year'])));
		$ilFeedback->setEndtime(mktime(
			ilUtil::stripSlashes($_POST['end_hour']),
			ilUtil::stripSlashes($_POST['end_minute']),0,
			ilUtil::stripSlashes($_POST['end_month']),
			ilUtil::stripSlashes($_POST['end_day']),
			ilUtil::stripSlashes($_POST['end_year'])));
		$ilFeedback->setInterval(
			ilUtil::stripSlashes($_POST['interval']));
		$ilFeedback->setIntervalUnit(
			ilUtil::stripSlashes($_POST['interval_unit']));
		$ilFeedback->setFirstVoteBest(
			ilUtil::stripSlashes($_POST['first_vote_best']));
		$ilFeedback->setObjId($params['obj_id']);
		$ilFeedback->setRefId($params['ref_id']);
		if($this->update==1){
			$ilFeedback->setId($_GET['barometer_id']);
			$ilFeedback->update();
		}else
			$ilFeedback->create();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, 'fbList'));

	}

	/**
	* get stored post var in case of an error/warning otherwise return passed value
	*/
	function get_last($a_var, $a_value='',$pForm=true)
	{
		if (!empty($_POST[$a_var])) $result=$_POST[$a_var];
		elseif (!empty($_SESSION["error_post_vars"][$a_var])) $result=$_SESSION["error_post_vars"][$a_var];
		else {
			$result=$a_value;
			$pForm=false;
		}

		return $pForm?ilUtil::prepareFormOutput($result):$result;
	}
	/**
	* Display the new feedback creation screen.
	*/
	function addBarometer(){
		$tpl = new ilTemplate("tpl.feedback_edit.html", true, true, "Services/Feedback");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->setVariable("TXT_HEADER", $this->lng->txt("bar_create"));
		$tpl->setVariable("IMG_FEEDB", ilUtil::getImagePath("icon_feedb.gif"));
		$tpl->setVariable("ALT_FEEDB", $this->lng->txt("bar_create"));
		$tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$tpl->setVariable("TXT_TIME", $this->lng->txt("feedb_time"));
		$tpl->setVariable("VALUE_TITLE",$this->get_last("title"));
		$tpl->setVariable("TXT_TOP_TEXT", $this->lng->txt("text"));
		$tpl->setVariable("VALUE_TEXT", $this->get_last("text"));
		$tpl->setVariable("TXT_ANONYMOUS", $this->lng->txt("anonymous"));
		if($this->get_last("anonymous")=="0")
			$tpl->setVariable("ANONYMOUS_NO", " checked");
		else
			$tpl->setVariable("ANONYMOUS_YES", " checked");
		$tpl->setVariable("TXT_YES", $this->lng->txt("yes"));
		$tpl->setVariable("TXT_NO", $this->lng->txt("no"));
		$tpl->setVariable("TXT_TYPE", $this->lng->txt("type"));
		$typeSB[0] = $this->lng->txt('optional');
		$typeSB[1] = $this->lng->txt('required');
		$tpl->setVariable("SELECT_TYPE", ilUtil::formSelect($this->get_last('type'),'type',$typeSB,false,true));

		$tpl->setVariable("TXT_REQUIRED", $this->lng->txt("required"));
		if($this->get_last("show_on")=="course")
			$tpl->setVariable("SHOW_CHANGE_COURSE_SELECTED", "selected");
		else
			$tpl->setVariable("SHOW_LOGIN_SELECTED", "selected");
		$tpl->setVariable("TXT_LOGIN", $this->lng->txt("login"));
		$tpl->setVariable("TXT_CHANGE_COURSE", $this->lng->txt("change_course"));
		if($this->get_last("text_answer")=="1")
			$tpl->setVariable("TEXT_ANSWER_YES", " checked");
		else
			$tpl->setVariable("TEXT_ANSWER_NO", " checked");
		$tpl->setVariable("TXT_TEXT_ANSWER", $this->lng->txt("text_answer"));
		$tpl->setVariable("TXT_VOTES", $this->lng->txt("votes"));
		$extra_votes = $this->get_last("extra_votes",0);
		if ($_POST['cmd']['addBarometer']) $extra_votes+=1;
		$tpl->setVariable("VALUE_EXTRA_VOTES", $extra_votes);
		$vote_txt=$this->get_last('vote',array(),false);
		for($i=1;$i < 3+$extra_votes ;$i++){
			$tpl->setCurrentBlock("vote");
			$tpl->setVariable("TXT_TEXT",$this->lng->txt("text"));
			$tpl->setVariable("VALUE_VOTE_TEXT",ilUtil::prepareFormOutput($vote_txt[$i]));
			$tpl->setVariable("VOTE_NUM",$i);
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("TXT_DAY",$this->lng->txt("day"));
		$tpl->setVariable("SELECT_ACTIVATION_START_DAY",$this->getDateSelect('day','start_day',$this->get_last('start_day')));
		$tpl->setVariable("SELECT_ACTIVATION_START_MONTH",$this->getDateSelect('month','start_month',$this->get_last('start_month')));
		$tpl->setVariable("SELECT_ACTIVATION_START_YEAR",$this->getDateSelect('year','start_year',$this->get_last('start_year')));
		$tpl->setVariable("SELECT_ACTIVATION_START_HOUR",$this->getDateSelect('hour','start_hour',$this->get_last('start_hour')));
		$tpl->setVariable("SELECT_ACTIVATION_START_MINUTE",$this->getDateSelect('minute','start_minute',$this->get_last('start_minute')));
		$tpl->setVariable("SELECT_ACTIVATION_END_DAY",$this->getDateSelect('day','end_day',$this->get_last('end_day')));
		$tpl->setVariable("SELECT_ACTIVATION_END_MONTH",$this->getDateSelect('month','end_month',$this->get_last('end_month')));
		$tpl->setVariable("SELECT_ACTIVATION_END_YEAR",$this->getDateSelect('year','end_year',$this->get_last('end_year')));
		$tpl->setVariable("SELECT_ACTIVATION_END_HOUR",$this->getDateSelect('hour','end_hour',$this->get_last('end_hour')));
		$tpl->setVariable("SELECT_ACTIVATION_END_MINUTE",$this->getDateSelect('minute','end_minute',$this->get_last('end_minute')));

		$tpl->setVariable("TXT_MONTH",$this->lng->txt("month"));
		$tpl->setVariable("TXT_YEAR",$this->lng->txt("year"));
		$tpl->setVariable("TXT_FROM",$this->lng->txt("from"));
		$tpl->setVariable("TXT_UNTIL",$this->lng->txt("until"));

		$tpl->setVariable("TXT_DURATION",$this->lng->txt("duration"));
		$tpl->setVariable("TXT_HOURS",$this->lng->txt("hours"));
		$tpl->setVariable("TXT_DAYS",$this->lng->txt("days"));
		$tpl->setVariable("TXT_WEEKS",$this->lng->txt("weeks"));
		$tpl->setVariable("TXT_MONTHS",$this->lng->txt("months"));
		$tpl->setVariable("TXT_REPEAT",$this->lng->txt("repeat"));

		for($i=0;$i < 25;$i++){
			$interval[$i]=$i;
		}
		$tpl->setVariable("SELECT_INTERVAL",ilUtil::formSelect($this->get_last('interval'),'interval',$interval,false,true));
		$tpl->setVariable("TXT_EVERY",$this->lng->txt("every"));

		$interval_unitSB[0] = $this->lng->txt('hours');
		$interval_unitSB[1] = $this->lng->txt('days');
		$interval_unitSB[2] = $this->lng->txt('weeks');
		$interval_unitSB[3] = $this->lng->txt('months');
		$tpl->setVariable("SELECT_INTERVAL_UNIT",ilUtil::formSelect($this->get_last('interval_unit'),'interval_unit',$interval_unitSB,false,true));

		$tpl->setVariable("TXT_FIRST_VOTE",$this->lng->txt("first_vote"));
		$tpl->setVariable("TXT_BEST",$this->lng->txt("best"));
		$tpl->setVariable("TXT_WORST",$this->lng->txt("worst"));
		if($this->get_last("first_vote_best")==1)
			$tpl->setVariable("BEST_CHECKED", "checked");
		else
			$tpl->setVariable("WORST_CHECKED", "checked");

		$tpl->setVariable("TXT_NEW_VOTE", $this->lng->txt("new_vote"));
		$tpl->setVariable("CMD_ADDVOTE", 'addBarometer');
		$tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$tpl->setVariable("CMD_SUBMIT", "save");
		$tpl->parseCurrentBlock();
		return($tpl->get());

	}
	function getButtons($a_barometer_id){

		$tpl = new ilTemplate("tpl.buttons.html", true, true,"Services/Feedback");

		$tpl->setCurrentBlock("btn_cell");
		$this->ctrl->setParameter($this,"barometer_id",$a_barometer_id);
		$this->ctrl->setParameter($this,"ref_id",$_GET['ref_id']);
		$tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this,'stats'));
		$tpl->setVariable("BTN_TXT", $this->lng->txt("statistics"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("btn_cell");
		$tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this,'edit'));
		$tpl->setVariable("BTN_TXT", $this->lng->txt("edit"));
		$tpl->parseCurrentBlock();
		return($tpl->get());
	}
        function getDateSelect($a_type,$a_varname,$a_selected)
        {
                switch($a_type)
                {
                        case "minute":
                                for($i=0;$i<60;$i++)
                                {
                                        $days[$i] = $i < 10 ? "0".$i : $i;
                                }
                                return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

                        case "hour":
                                for($i=0;$i<24;$i++)
                                {
                                        $days[$i] = $i < 10 ? "0".$i : $i;
                                }
                                return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

                        case "day":
                                for($i=1;$i<32;$i++)
                                {
                                        $days[$i] = $i < 10 ? "0".$i : $i;
                                }
				$days[0] = '';
                                return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

                        case "month":
                                for($i=1;$i<13;$i++)
                                {
                                        $month[$i] = $i < 10 ? "0".$i : $i;
                                }
				$month[0] = '';
                                return ilUtil::formSelect($a_selected,$a_varname,$month,false,true);

                        case "year":
                                for($i = date("Y",time());$i < date("Y",time()) + 3;++$i)
                                {
                                        $year[$i] = $i;
                                }
				$year[0] = '';
                                return ilUtil::formSelect($a_selected,$a_varname,$year,false,true);
                }
        }

	function showBarometerById($a_id=0, $a_header = false){
		global $ilAccess,$ilUser;
		include_once('Services/Feedback/classes/class.ilFeedback.php');
		$feedback = new ilFeedback();
		$bid = $a_id ? $a_id :$_GET['barometer_id'];
		$feedback->setId($bid);
		$feedback->getBarometer();
		if($feedback->getId()&& ($feedback->canVote($ilUser->getId(),$feedback->getId())==1)){
			$tpl = new ilTemplate("tpl.feedback_vote.html", true,true, "Services/Feedback");
			
			if ($a_header)
			{
				$tpl->setCurrentBlock("vote_head");
				$tpl->setVariable("IMG_VHEAD", ilUtil::getImagePath("icon_crs.gif"));
				$tpl->setVariable("TXT_CRS_TITLE", ilObject::_lookupTitle($feedback->getObjId()));
				$tpl->parseCurrentBlock();
			}

			$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this).'&fb_id='.$feedback->getId());
			$tpl->setVariable("TXT_TITLE", $feedback->getTitle());
			$tpl->setVariable("TXT_DESCRIPTION", $feedback->getDescription());
			$votes = unserialize($feedback->getVotes());
			$checked = 1;
			foreach($votes as $vote => $votetext){
				$radios.=ilUtil::formRadioButton($checked,'vote',$vote).$votetext.'<br>';
				$checked = 0;
			}
			$tpl->setVariable("TXT_SAVE",$this->lng->txt('save_vote'));

			$tpl->setVariable("RADIO_VOTES",$radios);
			if($feedback->getTextAnswer()){
				$tpl->setCurrentBlock("text_answer");
				$tpl->setVariable("TXT_NOTE",$this->lng->txt('note'));

			}
			$tpl->parseCurrentBlock();

			return($tpl->get());
		}

	}
	function _isRequiredFeedbackOnLogin(){
		global $ilUser;
		include_once('Services/Feedback/classes/class.ilFeedback.php');
		include_once('Modules/Course/classes/class.ilCourseParticipants.php');
		

		$feedback = new ilFeedback();
		$feedback->getFeedback();

		$members_obj = ilCourseParticipants::_getInstanceByObjId($feedback->getObjId());
		if(($feedback->getId()) && ($members_obj->isAssigned($ilUser->getId())))

			return($feedback->getRefId());
		else
			return(0);

	}

	function showBarometer(){
		global $ilUser;
		include_once('Services/Feedback/classes/class.ilFeedback.php');

		$ilfeedback = new ilFeedback();
		$ilfeedback->setRefId($_GET['ref_id']);
		$feedbacks = $ilfeedback->getAllBarometer();

		//There can be more then 1 Barometer for a ref_id so we show show always only the first_vote_best
		//Barometer a user can vote, the next time he acceses this page, he will get the next barometer a.s.o.
		foreach($feedbacks as $feedback){
			//Show only if there is no vote yet or enough time has passed since the last vote
			if($feedback->getId()&& ($feedback->canVote($ilUser->getId(),$feedback->getId())==1)){
				$tpl = new ilTemplate("tpl.feedback_vote.html", true,true, "Services/Feedback");
				$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this).'&fb_id='.$feedback->getId());
				$tpl->setVariable("TXT_TITLE", $feedback->getTitle());
				$tpl->setVariable("TXT_DESCRIPTION", $feedback->getDescription());
				$votes = unserialize($feedback->getVotes());
				$checked = 1;
				foreach($votes as $vote => $votetext){
					$radios.=ilUtil::formRadioButton($checked,'vote',$vote).$votetext.'<br>';
					$checked = 0;
				}
				$tpl->setVariable("TXT_SAVE",$this->lng->txt('save_vote'));
				$tpl->setVariable("RADIO_VOTES",$radios);
				if($feedback->getTextAnswer()){
					$tpl->setCurrentBlock("text_answer");
					$tpl->setVariable("TXT_NOTE",$this->lng->txt('note'));

				}
				$tpl->parseCurrentBlock();

				return($tpl->get());
			}
		}
	}
	function saveVote(){
		global $ilUser;
		include_once('Services/Feedback/classes/class.ilFeedback.php');

		$feedback = new ilFeedback();
		$feedback->setId($_GET['fb_id']);
		$feedback->getBarometer()
;
		$feedback->setVote(ilUtil::stripSlashes($_POST['vote']));
		$feedback->setNote(ilUtil::stripSlashes($_POST['text_answer']));
		if($feedback->getAnonymous())
			$feedback->setUserId(0);
		else
			$feedback->setUserId($ilUser->getId());
		$feedback->saveResult();
		$this->ctrl->returnToParent($this);
	}
	function selectbox($selected_itm, $name, $items, $params='',$first=''){
		$selected_ = '';
		$options = $first ? '<option value="">'.$first.'</option>'.chr(10) : '';
		if(is_Array($items)){
			foreach($items as $key => $item){
				$selected = ($key == $selected_itm) ? ' selected' : '';
				$options.='<option value="'.$key.'"'.$selected.'>'.$item.'</option>'.chr(10);

			}
		}
		$content = '<select name="'.$name.'" '.$params.'>
				'.$options.'
				</select>';
		return($content);
	}

	function getPDFeedbackListHTML(){
		global $ilUser, $ilAccess;

		include_once('Services/Feedback/classes/class.ilFeedback.php');
		$feedback = new ilFeedback();
		$barometers = $feedback->getAllBarometer(0);

		$tpl = new ilTemplate("tpl.feedback_pdbox.html", true,true, "Services/Feedback");
		$tpl->setVariable('TXT_TITLE',$this->lng->txt('feedback'));
		$rownum = 0;
		if(count($barometers)){
			foreach ($barometers as $barometer)
			{
				// do not show feedback for tutors/admins
				if ($ilAccess->checkAccess("write", "", $barometer->getRefId()))
				{
					continue;
				}
				if($barometer->canVote($ilUser->getId(),$barometer->getId())==1)
				{
					$tpl->setCurrentBlock('tbl_row');
					$tpl->setVariable('ROWCOL', 'tblrow'.(($rownum++ % 2)+1));
					$this->ctrl->setParameter($this,"barometer_id",$barometer->getId());
					$link ='<a href="'.$this->ctrl->getLinkTargetByClass('ilfeedbackgui','voteform').'">'.$barometer->getTitle().'</a>';
					//$link ='<a href="repository.php?jumpToFeedback=1&barometer_id='.$barometer->getId().'&ref_id='.$barometer->getRefId().'&rep_frame=1">'.$barometer->getTitle().'</a>';
					$tpl->setVariable('BAROMETER_LINK',$link);
					$tpl->parseCurrentBlock();
				}
			}
		}
		$output = $rownum ? $tpl->get() : '' ;
		return($output);
	}

	function getCRSFeedbackListHTML(){
		global $ilUser;

		include_once('Services/Feedback/classes/class.ilFeedback.php');
		$feedback = new ilFeedback();
		$feedback->setRefId($_GET['ref_id']);
		$barometers = $feedback->getAllBarometer(0);

		$tpl = new ilTemplate("tpl.feedback_pdbox.html", true,true, "Services/Feedback");
		$tpl->setVariable('TXT_TITLE',$this->lng->txt('feedback'));
		$rownum = 0;
		if(count($barometers)){
			foreach ($barometers as $barometer){
				if($barometer->canVote($ilUser->getId(),$barometer->getId())==1){
					$tpl->setCurrentBlock('tbl_row');
					$tpl->setVariable('ROWCOL', 'tblrow'.(($rownum++ % 2)+1));
					$this->ctrl->setParameter($this,"barometer_id",$barometer->getId());
					$link ='<a href="'.$this->ctrl->getLinkTargetByClass(array('ilinfoscreengui','ilfeedbackgui'),'fbList').'">'.$barometer->getTitle().'</a>';

					$tpl->setVariable('BAROMETER_LINK',$link);
					$tpl->parseCurrentBlock();
				}
			}
		}
		$output = $rownum ? $tpl->get() : '' ;
		return($output);
	}
	
	function handleRequiredFeedback($a_ref_id = 0)
	{
		global $ilUser;

		include_once('Services/Feedback/classes/class.ilFeedback.php');
		$feedback = new ilFeedback();
		if ($a_ref_id > 0)
		{
			$feedback->setRefId($a_ref_id);
		}
		$barometers = $feedback->getAllBarometer(0,1);

		$rownum = 0;
		if(count($barometers))
		{
			foreach ($barometers as $barometer)
			{
				if($barometer->canVote($ilUser->getId(),$barometer->getId())==1){
					$this->ctrl->setParameter($this,"barometer_id",$barometer->getId());
					$this->ctrl->redirectByClass(array("ilinfoscreengui", "ilfeedbackgui"), "showBarometerById");
				}
			}
		}
	}
	
	function voteform($a_barometer_id=0){
		global $tpl;

		$barometer_id = $a_barometer_id ? $a_barometer_id :$_GET['barometer_id'];
		//$tpl->setVariable('CONTENT',$this->showBarometerById($a_barometer_id));
		$tpl->setContent($this->showBarometerById($a_barometer_id, true));
		//$tpl->parseCurrentBlock();
		$tpl->show();

	}
}
?>