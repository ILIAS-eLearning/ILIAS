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

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLMStatistics.php';

class ilLMStatisticsGUI extends ilLearningProgressBaseGUI {

	var $lm_statistics;

	function ilLMStatisticsGUI($a_mode, $a_ref_id) {
		parent :: ilLearningProgressBaseGUI($a_mode, $a_ref_id);

		$this->lm_statistics = new ilLMStatistics($this->obj_id);
	}

	/**
	Anzeige der Stats-Auswahlm�glichkeiten
	*/

	function show() {
		global $tpl, $lng, $ilias;

		$q = "SELECT obj_id, type,title FROM object_data WHERE type ='lm' and obj_id=".$this->obj_id;
		$result = $ilias->db->query($q);
		while ($row = $result->fetchRow()) {
			$Lehrmodulanz ++;
			$LehrmodulID[$Lehrmodulanz] = $row[0];
			$rLehrmodulID[$row[0]] = $Lehrmodulanz;
			$LehrmodulName[$Lehrmodulanz] = $row[2];
		}

		$_SESSION["il_track_rlm_id"] = $rLehrmodulID;
		$_SESSION["il_track_lm_name"] = $LehrmodulName;

		$q = "SELECT obj_id,title,type,lm_id FROM lm_data WHERE type='pg'";
		$result = $ilias->db->query($q);
		while ($row = $result->fetchRow()) {
			$LMSeitenanz[$rLehrmodulID[$row[3]]]++;
		}

		$year = array (2004, 2005, 2006, 2007);
		$month = array (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
		$day = array (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31);

		$tpl->addBlockFile("ADM_CONTENT", "adm_content", "Services/Tracking/templates/default/tpl.lm_statistics_settings.html");

//		$tpl->setVariable("SEARCH_ACTION", ".php?ref_id=".$_GET["ref_id"]."&cmd=gateway");
		$tpl->setVariable("SEARCH_ACTION", $this->ctrl->getLinkTargetByClass('illmstatisticsgui',''));
		$tpl->setVariable("TXT_TRACKING_DATA", $lng->txt("tracking_data"));
		$tpl->setVariable("TXT_TIME_SEGMENT", $lng->txt("time_segment"));
		$tpl->setVariable("TXT_STATISTIC", $lng->txt("statistic"));
		$tpl->setVariable("TXT_STATISTIC_H", $lng->txt("stats_pages_statisics"));
		$tpl->setVariable("TXT_STATISTIC_D", $lng->txt("stats_chapter_statisics"));
		$tpl->setVariable("TXT_STATISTIC_O", $lng->txt("stats_sessions_statisics"));
		$tpl->setVariable("TXT_STATISTIC_U", $lng->txt("stats_navigation"));
		$tpl->setVariable("TXT_USER_LANGUAGE", $lng->txt("user_language"));
		$tpl->setVariable("TXT_LM", $lng->txt("lm"));
		$tpl->setVariable("TXT_SHOW_TR_DATA", $lng->txt("query_data"));
		$tpl->setVariable("TXT_SHOW_TR_DATA2", $lng->txt("stats_new_selection"));
		$tpl->setVariable("TXT_TRACKED_OBJECTS", $lng->txt("tracked_objects"));
		$tpl->setVariable("TXT_TRACKED_USER", $lng->txt("stats_tracked_user"));


		$tpl->setVariable("TXT_ALLE", $lng->txt("stats_all"));
		$tpl->setVariable("TXT_AUSWAHL", $lng->txt("stats_choice"));

		if ($_SESSION["il_track_yearf"] == "") {
			$_SESSION["il_track_yearf"] = Date("Y");
			$_SESSION["il_track_yeart"] = Date("Y");
			$_SESSION["il_track_monthf"] = "1";
			$_SESSION["il_track_montht"] = Date("n");
			$_SESSION["il_track_dayf"] = "1";
			$_SESSION["il_track_dayt"] = Date("j");
		}
		if ($_SESSION["il_track_stat"] == "") {
			$_SESSION["il_track_stat"] = 'h';
		}
		$languages = $lng->getInstalledLanguages();

		$tpl->setCurrentBlock("lm_selection");
		$_SESSION["il_track_lm"] = $this->obj_id;
		$tpl->setVariable("LM_ID", $LehrmodulID[1]);
		$tpl->setVariable("LM_DESC", substr($LehrmodulName[1], 0, 40)." (S:".$LMSeitenanz[1].")");
		$tpl->parseCurrentBlock();

		if ($_SESSION["il_track_stat2"] == "choice") {
			$tpl->setVariable("CHC_CHK", " checked=\"1\" ");

		} else {
			$tpl->setVariable("ALL_CHK", " checked=\"1\" ");
		}

		$user_IDs = $_SESSION["userSelected_stat"];


	    $tpl->setCurrentBlock("user_selection");

	    if (count($user_IDs) > 0) {
			foreach ($_SESSION["userSelected_stat"] as $result_id) {
				$tpl->setVariable("USER1", $result_id);
				$tpl->setVariable("USER2", $result_id);
			 }
		} else {
			$tpl->setCurrentBlock("user_selection");
			$tpl->setVariable("USER1", "Alle");
			$tpl->setVariable("USER2", "Alle");
		}

        include_once("Services/Tracking/classes/class.ilObjUserTracking.php");

		if (!ilObjUserTracking::_enabledUserRelatedData()) {
		    $tpl->setVariable ("DISABLED", "disabled");
		    $tpl->setVariable("ALL_CHK", " checked=\"1\" ");
		    $tpl->setVariable("CHC_CHK", "");
        } else {
            $tpl->setVariable("INFO1", $lng->txt(stats_current).": ".count($_SESSION["userSelected_stat"])." ".$lng->txt("stats_user_selected"));
        }

		$tpl->parseCurrentBlock();

		//Datum von:
		foreach ($year as $key) {
			$tpl->setCurrentBlock("fromyear_selection");
			$tpl->setVariable("YEARFR", $key);
			$tpl->setVariable("YEARF", $key);
			if ($_SESSION["il_track_yearf"] == $key) {
				$tpl->setVariable("YEARF_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach ($month as $key) {
			$tpl->setCurrentBlock("frommonth_selection");
			$tpl->setVariable("MONTHFR", $key);
			$tpl->setVariable("MONTHF", $key);
			if ($_SESSION["il_track_monthf"] == $key) {
				$tpl->setVariable("MONTHF_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach ($day as $key) {
			$tpl->setCurrentBlock("fromday_selection");
			$tpl->setVariable("DAYFR", $key);
			$tpl->setVariable("DAYF", $key);
			if ($_SESSION["il_track_dayf"] == $key) {
				$tpl->setVariable("DAYF_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		//Datum nach:
		foreach ($day as $key) {
			$tpl->setCurrentBlock("today_selection");
			$tpl->setVariable("DAYTO", $key);
			$tpl->setVariable("DAYT", $key);
			if ($_SESSION["il_track_dayt"] == $key) {
				$tpl->setVariable("DAYT_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach ($month as $key) {
			$tpl->setCurrentBlock("tomonth_selection");
			$tpl->setVariable("MONTHTO", $key);
			$tpl->setVariable("MONTHT", $key);
			if ($_SESSION["il_track_montht"] == $key) {
				$tpl->setVariable("MONTHT_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		foreach ($year as $key) {
			$tpl->setCurrentBlock("toyear_selection");
			$tpl->setVariable("YEARTO", $key);
			$tpl->setVariable("YEART", $key);
			if ($_SESSION["il_track_yeart"] == $key) {
				$tpl->setVariable("YEART_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		// language selection
		$tpl->setCurrentBlock("language_selection");
		$tpl->setVariable("LANG", $lng->txt("any_language"));
		$tpl->setVariable("LANGSHORT", "0");
		$tpl->parseCurrentBlock();
		foreach ($languages as $lang_key) {
			$tpl->setCurrentBlock("language_selection");
			$tpl->setVariable("LANG", $lng->txt("lang_".$lang_key));
			$tpl->setVariable("LANGSHORT", $lang_key);
			if ($_SESSION["il_track_language"] == $lang_key) {
				$tpl->setVariable("LANG_SEL", " selected=\"1\" ");
			}
			$tpl->parseCurrentBlock();
		}
		// statistic type
		if ($_SESSION["il_track_stat"] == "d") {
			$tpl->setVariable("D_CHK", " checked=\"1\" ");
		}
		elseif ($_SESSION["il_track_stat"] == "h") {
			$tpl->setVariable("H_CHK", " checked=\"1\" ");
		}
		elseif ($_SESSION["il_track_stat"] == "o") {
			$tpl->setVariable("O_CHK", " checked=\"1\" ");
		}
		elseif ($_SESSION["il_track_stat"] == "u") {
			$tpl->setVariable("U_CHK", " checked=\"1\" ");
		}
	}

	/**
	output statistics der Stats
	*/

	function outputStatistics() {
		require_once "./Services/Tracking/classes/class.ilLMStatistics.php";

		if ($_POST["stat"] == 'u') { //Beobachtungsmodell starten
			$this->lm_statistics->outputApplet();
		} else { //Session-,Seiten-,Kapitelstats starten
			$this->lm_statistics->outputHTML();
		}
	}

	/**
	add users
	*/
	function searchUserForm() {
		global $rbacsystem;

		$this->lng->loadLanguageModule('search');

		// MINIMUM ACCESS LEVEL = 'administrate'
		if (!$rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_members_search.html");

		$this->tpl->setVariable("F_ACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SEARCH_ASSIGN_USR", $this->lng->txt("grp_search_members"));
		$this->tpl->setVariable("SEARCH_SEARCH_TERM", $this->lng->txt("search_search_term"));
		$this->tpl->setVariable("SEARCH_VALUE", $_SESSION["grp_search_str"] ? $_SESSION["grp_search_str"] : "");
		$this->tpl->setVariable("SEARCH_FOR", $this->lng->txt("exc_search_for"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_USER", $this->lng->txt("exc_users"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_ROLE", $this->lng->txt("exc_roles"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_GROUP", $this->lng->txt("exc_groups"));
		$this->tpl->setVariable("BTN2_VALUE", $this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN1_VALUE", $this->lng->txt("search"));

		$usr = ($_POST["search_for"] == "usr" || $_POST["search_for"] == "") ? 1 : 0;
		$grp = ($_POST["search_for"] == "grp") ? 1 : 0;
		$role = ($_POST["search_for"] == "role") ? 1 : 0;

		$this->tpl->setVariable("SEARCH_ROW_CHECK_USER", ilUtil :: formRadioButton($usr, "search_for", "usr"));
		$this->tpl->setVariable("SEARCH_ROW_CHECK_ROLE", ilUtil :: formRadioButton($role, "search_for", "role"));
		$this->tpl->setVariable("SEARCH_ROW_CHECK_GROUP", ilUtil :: formRadioButton($grp, "search_for", "grp"));

	}

	function cancelMember() {
		$return_location = "members";

		ilUtil::sendInfo($this->lng->txt("action_aborted"), true);
		ilUtil :: redirect($this->ctrl->getLinkTarget($this, $return_location));
	}

	function members() {
		$this->show();
	}

	function search() {
		global $rbacsystem, $tree;

		$_SESSION["grp_search_str"] = $_POST["search_str"] = $_POST["search_str"] ? $_POST["search_str"] : $_SESSION["grp_search_str"];
		$_SESSION["grp_search_for"] = $_POST["search_for"] = $_POST["search_for"] ? $_POST["search_for"] : $_SESSION["grp_search_for"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if (!$rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilias->error_obj->MESSAGE);
		}

		if (!isset ($_POST["search_for"]) or !isset ($_POST["search_str"])) {
			ilUtil::sendInfo($this->lng->txt("grp_search_enter_search_string"));
			$this->searchUserForm();

			return false;
		}

		if (!count($result = $this->__search(ilUtil :: stripSlashes($_POST["search_str"]), $_POST["search_for"]))) {
			ilUtil::sendInfo($this->lng->txt("grp_no_results_found"));
			$this->searchUserForm();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_usr_selection.html");
		$this->__showButton("searchUserForm", $this->lng->txt("grp_new_search"));

		$counter = 0;
		$f_result = array ();

		switch ($_POST["search_for"]) {
			case "usr" :
				foreach ($result as $user) {
					if (!$tmp_obj = ilObjectFactory :: getInstanceByObjId($user["id"], false)) {
						continue;
					}

					$user_ids[$counter] = $user["id"];

					$f_result[$counter][] = ilUtil :: formCheckbox(0, "user[]", $user["id"]);
					$f_result[$counter][] = $tmp_obj->getLogin();
					$f_result[$counter][] = $tmp_obj->getFirstname();
					$f_result[$counter][] = $tmp_obj->getLastname();
					$f_result[$counter][] = ilFormat :: formatDate($tmp_obj->getLastLogin());

					unset ($tmp_obj);
					++ $counter;
				}
				$this->__showSearchUserTable($f_result, $user_ids);

				return true;

			case "role" :
				foreach ($result as $role) {
					// exclude anonymous role
					if ($role["id"] == ANONYMOUS_ROLE_ID) {
						continue;
					}

					if (!$tmp_obj = ilObjectFactory :: getInstanceByObjId($role["id"], false)) {
						continue;
					}

					// exclude roles with no users assigned to
					if ($tmp_obj->getCountMembers() == 0) {
						continue;
					}

					$role_ids[$counter] = $role["id"];

					$f_result[$counter][] = ilUtil :: formCheckbox(0, "role[]", $role["id"]);
					$f_result[$counter][] = array ($tmp_obj->getTitle(), $tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();

					unset ($tmp_obj);
					++ $counter;
				}

				$this->__showSearchRoleTable($f_result, $role_ids);

				return true;

			case "grp" :
				foreach ($result as $group) {
					if (!$tree->isInTree($group["id"])) {
						continue;
					}

					if (!$tmp_obj = ilObjectFactory :: getInstanceByRefId($group["id"], false)) {
						continue;
					}

					// exclude myself :-)
					if ($tmp_obj->getId() == $this->object->getId()) {
						continue;
					}

					$grp_ids[$counter] = $group["id"];

					$f_result[$counter][] = ilUtil :: formCheckbox(0, "group[]", $group["id"]);
					$f_result[$counter][] = array ($tmp_obj->getTitle(), $tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();

					unset ($tmp_obj);
					++ $counter;
				}

				if (!count($f_result)) {
					ilUtil::sendInfo($this->lng->txt("grp_no_results_found"));
					$this->searchUserFormObject();

					return false;
				}

				$this->__showSearchGroupTable($f_result, $grp_ids);

				return true;
		}
	}
	function searchCancelled() {
		ilUtil::sendInfo($this->lng->txt("action_aborted"), true);
		ilUtil :: redirect($this->ctrl->getLinkTarget($this, "members"));
	}
	function __search($a_search_string, $a_search_for) {
		include_once ("classes/class.ilSearch.php");

		$this->lng->loadLanguageModule("content");
		$search = & new ilSearch($_SESSION["AccountId"]);
		$search->setPerformUpdate(false);
		$search->setSearchString(ilUtil :: stripSlashes($a_search_string));
		$search->setCombination("and");
		$search->setSearchFor(array (0 => $a_search_for));
		$search->setSearchType('new');

		if ($search->validate($message)) {
			$search->performSearch();
		} else {
			ilUtil::sendInfo($message, true);
			$this->ctrl->redirect($this, "searchUserForm");
		}

		return $search->getResultByType($a_search_for);
	}

	function __showSearchUserTable($a_result_set, $a_user_ids = NULL, $a_cmd = "search") {
		$return_to = "searchUserForm";

		if ($a_cmd == "listUsersRole" or $a_cmd == "listUsersGroup") {
			$return_to = "search";
		}

		$tbl = & $this->__initTableGUI();
		$tpl = & $tbl->getTemplateObject();

		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME", $return_to);
		$tpl->setVariable("BTN_VALUE", $this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME", "addUser");
		$tpl->setVariable("BTN_VALUE", $this->lng->txt("add"));
		$tpl->parseCurrentBlock();

		if (!empty ($a_user_ids)) {
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME", "user");
			$tpl->setVariable("JS_ONCLICK", ilUtil :: array_php2js($a_user_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS", 5);
		$tpl->setVariable("IMG_ARROW", ilUtil :: getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"), "icon_usr_b.gif", $this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array ("", $this->lng->txt("username"), $this->lng->txt("firstname"), $this->lng->txt("lastname"), $this->lng->txt("last_visit")));
		$tbl->setHeaderVars(array ("", "login", "firstname", "lastname", "last_visit"), array ("ref_id" => $this->ref_id, "cmd" => $a_cmd, "cmdClass" => "ilobjgroupgui", "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array ("", "33%", "33%", "33%"));

		$this->__setTableGUIBasicData($tbl, $a_result_set);
		$tbl->render();

		$this->tpl->setVariable("SEARCH_RESULT_TABLE", $tbl->tpl->get());

		return true;
	}

	function __showSearchRoleTable($a_result_set, $a_role_ids = NULL) {
		$tbl = & $this->__initTableGUI();
		$tpl = & $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME", "searchUserForm");
		$tpl->setVariable("BTN_VALUE", $this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME", "listUsersRole");
		$tpl->setVariable("BTN_VALUE", $this->lng->txt("grp_list_users"));
		$tpl->parseCurrentBlock();

		if (!empty ($a_role_ids)) {
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME", "role");
			$tpl->setVariable("JS_ONCLICK", ilUtil :: array_php2js($a_role_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS", 5);
		$tpl->setVariable("IMG_ARROW", ilUtil :: getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"), "icon_usr_b.gif", $this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array ("", $this->lng->txt("obj_role"), $this->lng->txt("grp_count_members")));
		$tbl->setHeaderVars(array ("", "title", "nr_members"), array ("ref_id" => $this->ref_id, "cmd" => "search", "cmdClass" => "ilobjgroupgui", "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array ("", "80%", "19%"));

		$this->__setTableGUIBasicData($tbl, $a_result_set, "role");
		$tbl->render();

		$this->tpl->setVariable("SEARCH_RESULT_TABLE", $tbl->tpl->get());

		return true;
	}

	/**
	 * standard implementation for tables
	 * use 'from' variable use different initial setting of table
	 *
	 */
	function __setTableGUIBasicData(&$tbl,&$result_set,$a_from = "")
	{
		switch ($a_from)
		{
			case "clipboardObject":
				$offset = $_GET["offset"];
				$order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				$tbl->disable("footer");
				break;

			default:
				$offset = $_GET["offset"];
				$order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}

	function __showSearchGroupTable($a_result_set, $a_grp_ids = NULL) {
		$tbl = & $this->__initTableGUI();
		$tpl = & $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME", "searchUserForm");
		$tpl->setVariable("BTN_VALUE", $this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME", "listUsersGroup");
		$tpl->setVariable("BTN_VALUE", $this->lng->txt("grp_list_users"));
		$tpl->parseCurrentBlock();

		if (!empty ($a_grp_ids)) {
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME", "group");
			$tpl->setVariable("JS_ONCLICK", ilUtil :: array_php2js($a_grp_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS", 5);
		$tpl->setVariable("IMG_ARROW", ilUtil :: getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"), "icon_usr_b.gif", $this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array ("", $this->lng->txt("obj_grp"), $this->lng->txt("grp_count_members")));
		$tbl->setHeaderVars(array ("", "title", "nr_members"), array ("ref_id" => $this->ref_id, "cmd" => "search", "cmdClass" => "ilobjgroupgui", "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array ("", "80%", "19%"));

		$this->__setTableGUIBasicData($tbl, $a_result_set, "group");
		$tbl->render();

		$this->tpl->setVariable("SEARCH_RESULT_TABLE", $tbl->tpl->get());

		return true;
	}
	function listUsersRole() {
		global $rbacsystem, $rbacreview;

		$_SESSION["grp_role"] = $_POST["role"] = $_POST["role"] ? $_POST["role"] : $_SESSION["grp_role"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if (!$rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilias->error_obj->MESSAGE);
		}

		if (!is_array($_POST["role"])) {
			ilUtil::sendInfo($this->lng->txt("grp_no_roles_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_usr_selection.html");
		$this->__showButton("searchUserForm", $this->lng->txt("grp_new_search"));

		// GET ALL MEMBERS
		$members = array ();
		foreach ($_POST["role"] as $role_id) {
			$members = array_merge($rbacreview->assignedUsers($role_id), $members);
		}

		$members = array_unique($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array ();
		foreach ($members as $user) {
			if (!$tmp_obj = ilObjectFactory :: getInstanceByObjId($user, false)) {
				continue;
			}

			if (!$tmp_obj->active) {
				continue;
			}

			$user_ids[$counter] = $user;

			$f_result[$counter][] = ilUtil :: formCheckbox(0, "user[]", $user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = ilFormat :: formatDate($tmp_obj->getLastLogin());

			unset ($tmp_obj);
			++ $counter;
		}
		$this->__showSearchUserTable($f_result, $user_ids, "listUsersRole");

		return true;
	}

	function listUsersGroup() {
		global $rbacsystem, $rbacreview, $tree;

		$_SESSION["grp_group"] = $_POST["group"] = $_POST["group"] ? $_POST["group"] : $_SESSION["grp_group"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if (!$rbacsystem->checkAccess("write", $this->ref_id)) {
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"), $this->ilias->error_obj->MESSAGE);
		}

		if (!is_array($_POST["group"])) {
			ilUtil::sendInfo($this->lng->txt("grp_no_groups_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.grp_usr_selection.html");
		$this->__showButton("searchUserForm", $this->lng->txt("grp_new_search"));

		// GET ALL MEMBERS
		$members = array ();
		foreach ($_POST["group"] as $group_id) {
			if (!$tree->isInTree($group_id)) {
				continue;
			}
			if (!$tmp_obj = ilObjectFactory :: getInstanceByRefId($group_id)) {
				continue;
			}

			$members = array_merge($tmp_obj->getGroupMemberIds(), $members);

			unset ($tmp_obj);
		}

		$members = array_unique($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array ();
		foreach ($members as $user) {
			if (!$tmp_obj = ilObjectFactory :: getInstanceByObjId($user, false)) {
				continue;
			}

			$user_ids[$counter] = $user;

			$f_result[$counter][] = ilUtil :: formCheckbox(0, "user[]", $user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = ilFormat :: formatDate($tmp_obj->getLastLogin());

			unset ($tmp_obj);
			++ $counter;
		}
		$this->__showSearchUserTable($f_result, $user_ids, "listUsersGroup");

		return true;
	}

	function addUser() {
		//echo "addUserObject() ausgefuehrt;";
		$user_ids = $_POST["user"];

		if (empty ($user_ids[0])) {
			//echo "...nix weitergeleitet...";
			// TODO: jumps back to grp content. go back to last search result
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"), $this->ilErr->MESSAGE);
		} else {
			//echo "...Daten weitergeleitet...";
		}
		$_SESSION["il_track_stat2"] = "choice";
		$_SESSION["userSelected_stat"] = $_POST["user"];
		$this->show();

	}

	/**
	* execute command
	*/
	function & executeCommand() {
		if (isset($_POST["cmd"]["searchUserForm"]))
		{
			$cmd = "searchUserForm";
		} elseif (isset($_POST["cmd"]["outputStatistics"]))
		{
			$cmd = "outputStatistics";
		} else switch ($this->ctrl->getNextClass()) {
			default :
				$cmd = $this->__getDefaultCommand();
		}
		$this-> $cmd ();
		return true;
	}

}
?>
