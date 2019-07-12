<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("Services/Table/classes/class.ilTable2GUI.php");
require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");
require_once("Modules/StudyProgramme/classes/model/Progress/class.ilStudyProgrammeProgress.php");
require_once("Modules/StudyProgramme/classes/model/Assignments/class.ilStudyProgrammeAssignment.php");
require_once("Modules/StudyProgramme/classes/class.ilObjStudyProgramme.php");
require_once("Modules/StudyProgramme/classes/class.ilStudyProgrammeUserProgress.php");
require_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
require_once("Services/Link/classes/class.ilLink.php");

/**
 * Class ilObjStudyProgrammeMembersTableGUI
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 *
 */
class ilStudyProgrammeMembersTableGUI extends ilTable2GUI
{
	const COLUMNS = [
		//column, langvar, optional, if_lp_children, if_no_lp_children
		['name', 'name', false, true, true],
		['login', 'login', false, true, true],
		['prg_orgus', 'prg_orgus', true, true, true],
		['prg_status', 'prg_status', false, true, true],
		['prg_completion_date', 'prg_completion_date', true, true, true],
		['prg_completion_by', 'prg_completion_by', true, true, true],
		['points', 'prg_points_reachable', false, true, false],
		['points', 'prg_points_required', false, false, true],
		['points_current', 'prg_points_current', false, false, true],
		['prg_custom_plan', 'prg_custom_plan', true, true, true],
		['prg_belongs_to', 'prg_belongs_to', true, true, true],
		['prg_assign_date', 'prg_assign_date', false, true, true],
		['prg_assigned_by', 'prg_assigned_by', true, true, true],
		['prg_deadline', 'prg_deadline', true, true, true],
		['prg_expiry_date', 'prg_expiry_date', true, true, true],
		['prg_validity', 'prg_validity', true, true, true],
		[null, 'action', false, true, true]
	];


	const OPTION_ALL = -1;
	const VALIDITY_OPTION_VALID = 1;
	const VALIDITY_OPTION_RENEWAL_REQUIRED = 3;

	protected $prg_obj_id;
	protected $prg_ref_id;
	protected $prg_has_lp_children;

	protected $db;
	protected $ui_factory;
	protected $ui_renderer;

	/**
	 * @var ilStudyProgrammeUserProgressDB
	 */
	private $sp_user_progress_db;

	public function __construct(
		$a_prg_obj_id,
		$a_prg_ref_id,
		$a_parent_obj,
		$a_parent_cmd="",
		$a_template_context="",
		ilStudyProgrammeUserProgressDB $sp_user_progress_db,
		ilStudyProgrammePostionBasedAccess $position_based_access
	) {

		$this->setId("sp_member_list");
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);
		$this->position_based_access = $position_based_access;
		$this->prg_obj_id = $a_prg_obj_id;
		$this->prg_ref_id = $a_prg_ref_id;
		$this->prg = ilObjStudyProgramme::getInstanceByRefId($a_prg_ref_id);
		$this->prg_has_lp_children = $a_parent_obj->getStudyProgramme()->hasLPChildren();

		global $DIC;
		$this->db = $DIC['ilDB'];
		$this->ui_factory = $DIC['ui.factory'];
		$this->ui_renderer = $DIC['ui.renderer'];

		$this->setEnableTitle(true);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		// TODO: switch this to internal sorting/segmentation
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setRowTemplate("tpl.members_table_row.html", "Modules/StudyProgramme");
		$this->setShowRowsSelector(false);

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, "view"));

		$this->addColumn("", "", "1", true);
		$this->setSelectAllCheckbox("prgs_ids[]");
		$this->setEnableAllCommand(true);
		$this->addMultiCommands();

		$selected = $this->getSelectedColumns();
		foreach (self::COLUMNS as $column) {
			list($col, $lng_var, $optional, $lp, $no_lp) = $column;

			$show_by_lp = ($this->prg_has_lp_children && $lp) || (!$this->prg_has_lp_children && $no_lp);
			$show_optional = !$optional || ($optional && array_key_exists($col, $selected));

			if($show_by_lp && $show_optional) {
				$this->addColumn($this->lng->txt($lng_var), $col);
			}
		}

		$this->sp_user_progress_db = $sp_user_progress_db;

		$this->initFilter();
		$filter_values = $this->getFilterValues();

		$this->determineOffsetAndOrder();
		$this->determineLimit();

		$members_list = $this->fetchData(
			$a_prg_obj_id,
			$this->getLimit(),
			$this->getOffset(),
			$this->getOrderField(),
			$this->getOrderDirection(),
			$filter_values
		);

		$this->setMaxCount($this->countFetchData($a_prg_obj_id, $filter_values));
		$this->setData($members_list);
	}


	protected function fillRow($a_set) {

		$usr_id = (int)$a_set['usr_id'];

		$may_read_learning_progress = !$this->prg->getAccessControlByOrguPositionsGlobal() ||
											in_array($usr_id, $this->getParentObject()->readLeadningProgress());
		$this->tpl->setCurrentBlock("checkb");
		$this->tpl->setVariable("ID", $a_set["prgrs_id"]);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("FIRSTNAME", $a_set["firstname"]);
		$this->tpl->setVariable("LASTNAME", $a_set["lastname"]);
		$this->tpl->setVariable("LOGIN", $a_set["login"]);
		$this->tpl->setVariable("STATUS", $may_read_learning_progress ? $this->sp_user_progress_db->statusToRepr($a_set["status"]) : '');
		$this->tpl->setVariable("POINTS_REQUIRED", $a_set["points"]);
		$this->tpl->setVariable("ASSIGN_DATE", $a_set["prg_assign_date"]);
		if(!$this->prg_has_lp_children) {
			$this->tpl->setCurrentBlock("points_current");
			$this->tpl->setVariable("POINTS_CURRENT", $may_read_learning_progress ? $a_set["points_current"] : '');
			$this->tpl->parseCurrentBlock();
		}
		foreach ($this->getSelectedColumns() as $column) {
			switch($column) {
				case "prg_orgus":
					$this->tpl->setVariable("ORGUS", $a_set["orgus"]);
					break;
				case "prg_completion_date":
					$this->tpl->setVariable("COMPLETION_DATE", $may_read_learning_progress ? $a_set["completion_date"] : '');
					break;
				case "prg_completion_by":
					if(is_null($a_set["completion_by"])) {
						$this->tpl->touchBlock("comp_by");
					} else {
						$this->tpl->setVariable("COMPLETION_BY", $may_read_learning_progress ? $a_set["completion_by"] : '');
					}

					break;
				case "prg_custom_plan":
					$this->tpl->setVariable("CUSTOM_PLAN",
						$may_read_learning_progress ?
							($a_set["last_change_by"] ? $this->lng->txt("yes") : $this->lng->txt("no")) :
							''
					);
					break;
				case "prg_belongs_to":
					$this->tpl->setVariable("BELONGS_TO", $a_set["belongs_to"]);
					break;
				case "prg_expiry_date":
					$this->tpl->setVariable("EXPIRY_DATE", $may_read_learning_progress ? $a_set["vq_date"] : '');
					break;
				case "prg_assigned_by":
					$this->tpl->setVariable("ASSIGNED_BY", $a_set["prg_assigned_by"]);
					break;
				case "prg_deadline":
					if(is_null($a_set["prg_deadline"])) {
						$this->tpl->touchBlock("deadline");
					} else {
						$this->tpl->setVariable("DEADLINE", $may_read_learning_progress ? $a_set["prg_deadline"] : '');
					}
					break;
				case "prg_validity":
					$this->tpl->setVariable("VALIDITY", $may_read_learning_progress ? $a_set['prg_validity'] : '');
					break;

			}
		}
		$this->tpl->setVariable("ACTIONS", $this->buildActionDropDown( $a_set["actions"]
																	 , $a_set["prgrs_id"]
																	 , $a_set["assignment_id"]
																	 , $usr_id));
	}

	/**
	 * Builds the action menu for each row of the table
	 *
	 * @param stirng 	$a_actions
	 * @param int 	$a_prgrs_id
	 * @param int 	$a_ass_id
	 *
	 * @return ilAdvancedSelectionListGUI
	 */
	protected function buildActionDropDown($a_actions, $a_prgrs_id, $a_ass_id, $a_usr_id) {
		$l = new ilAdvancedSelectionListGUI();
		$no_orgu_position_access_ctrl = !$this->prg->getAccessControlByOrguPositionsGlobal();
		$view_individual_plan = $no_orgu_position_access_ctrl ||
									in_array($a_usr_id, $this->getParentObject()->viewIndividualPlan());
		$manage_members = $no_orgu_position_access_ctrl ||
									in_array($a_usr_id, $this->getParentObject()->manageMembers());
		$edit_individual_plan = $no_orgu_position_access_ctrl ||
									in_array($a_usr_id, $this->getParentObject()->editIndividualPlan());
		foreach($a_actions as $action) {
			switch($action) {
				case ilStudyProgrammeUserProgress::ACTION_MARK_ACCREDITED:
				case ilStudyProgrammeUserProgress::ACTION_UNMARK_ACCREDITED:
					if(!$edit_individual_plan) {
						continue 2;
					}
					break;
				case ilStudyProgrammeUserProgress::ACTION_SHOW_INDIVIDUAL_PLAN:
					if(!$view_individual_plan) {
						continue 2;
					}
					break;
				case ilStudyProgrammeUserProgress::ACTION_REMOVE_USER:
					if(!$manage_members) {
						continue 2;
					}
					break;
			}
			$target = $this->getLinkTargetForAction($action, $a_prgrs_id, $a_ass_id);
			$l->addItem($this->lng->txt("prg_$action"), $action, $target);
		}
		return $l->getHTML();
	}

	/**
	 * Get ilias link for action
	 *
	 * @param stirng 	$a_actions
	 * @param int 	$a_prgrs_id
	 * @param int 	$a_ass_id
	 *
	 * @return string
	 */
	protected function getLinkTargetForAction($a_action, $a_prgrs_id, $a_ass_id) {
		return $this->getParentObject()->getLinkTargetForAction($a_action, $a_prgrs_id, $a_ass_id);
	}

	/**
	 * Get data for table
	 *
	 * @param int 	$a_prg_id
	 * @param int | null 	$limit
	 * @param int | null 	$offset
	 * @param string | null 	$order_column
	 * @param string | null 	$order_direction
	 * @param array 	$filter
	 *
	 * @return string[]
	 */
	protected function fetchData(
		int $a_prg_id,
		int $limit = null,
		int $offset = null,
		string $order_column = null,
		string $order_direction = null,
		array $filter = []
	): array {
		// TODO: Reimplement this in terms of ActiveRecord when innerjoin
		// supports the required rename functionality
		$query = "SELECT prgrs.id prgrs_id"
				   ."     , pcp.firstname"
				   ."     , pcp.lastname"
				   ."     , pcp.login"
				   ."     , pcp.usr_id"
				   ."     , prgrs.points"
				   //the following is a replacement for:
				   //IF(prgrs.status = ".ilStudyProgrammeProgress::STATUS_ACCREDITED.",prgrs.points,prgrs.points_cur)
				   //dirty hack to make it work with oracle :/ 1-|x-a|/max(|x-a|,1) = id_a(x)
				   ."     , prgrs.points_cur*"
				       ."ABS(prgrs.status - ".ilStudyProgrammeProgress::STATUS_ACCREDITED.")"
				           ."/(GREATEST(ABS(prgrs.status - ".ilStudyProgrammeProgress::STATUS_ACCREDITED."),1))"
				   ."     + prgrs.points*"
				       ."(1 -ABS(prgrs.status - ".ilStudyProgrammeProgress::STATUS_ACCREDITED.")"
				           ."/(GREATEST(ABS(prgrs.status - ".ilStudyProgrammeProgress::STATUS_ACCREDITED."),1))) points_current"
				   ."     , prgrs.last_change_by"
				   ."     , prgrs.status"
				   ."     , blngs.title belongs_to"
				   ."     , cmpl_usr.login accredited_by"
				   ."     , cmpl_obj.title completion_by"
				   ."     , cmpl_obj.type completion_by_type"
				   ."     , prgrs.completion_by completion_by_id"
				   ."     , prgrs.assignment_id assignment_id"
				   ."     , prgrs.completion_date"
				   ."     , prgrs.vq_date"
				   ."     , prgrs.deadline prg_deadline"
				   ."     , ass.root_prg_id root_prg_id"
				   ."     , ass.last_change prg_assign_date"
				   ."     , ass_usr.login prg_assigned_by"
				   // for sorting
				   ."     , CONCAT(pcp.firstname, pcp.lastname) name"
				   ."     , (prgrs.last_change_by IS NOT NULL) custom_plan"
				   ;

		$query .= $this->getFrom();
		$query .= $this->getWhere($a_prg_id);
		$query .= $this->getFilterWhere($filter);

		if($order_column !== null) {
			$query .= " ORDER BY $order_column";

			if($order_direction !== null) {
				$query .= " $order_direction";
			}
		}

		if($limit !== null) {
			$this->db->setLimit($limit, $offset !== null ? $offset : 0);
		}

		$res = $this->db->query($query);
		$now = (new DateTime())->format('Y-m-d H:i:s');
		$members_list = array();
		while($rec = $this->db->fetchAssoc($res)) {
			$rec["actions"] = ilStudyProgrammeUserProgress::getPossibleActions(
										$a_prg_id, $rec["root_prg_id"], $rec["status"]);
			$rec['points_current'] = number_format($rec['points_current']);
			if ($rec["status"] == ilStudyProgrammeProgress::STATUS_COMPLETED) {
				//If the status completed is set by crs reference
				//use crs title
				if($rec["completion_by_type"] == "crsr") {
					$completion_id = $rec["completion_by_id"];
					$title = ilContainerReference::_lookupTitle($completion_id);
					$ref_id = ilContainerReference::_lookupTargetRefId($completion_id);
					$url = ilLink::_getStaticLink($ref_id, "crs");
					$lnk = $this->ui_factory->link()->standard($title, $url);
					$rec["completion_by"] = $this->ui_renderer->render($lnk);
				}

				// If the status completed and there is a non-null completion_by field
				// in the set, this means the completion was achieved by some leaf in
				// the program tree.
				if (!$rec["completion_by"]) {
					$prgrs = $this->sp_user_progress_db->getInstanceForAssignment( $this->prg_obj_id
																					  , $rec["assignment_id"]);
					$rec["completion_by"] = implode(", ", $prgrs->getNamesOfCompletedOrAccreditedChildren());
				}
			}
			else if($rec["status"] == ilStudyProgrammeProgress::STATUS_ACCREDITED) {
				$rec["completion_by"] = $rec["accredited_by"];
			}
			if(!$rec['completion_date']) {
				$rec['completion_date'] = '';
			}
			if($rec['vq_date']) {
				$rec['prg_validity'] = $rec["vq_date"] > $now ? $this->lng->txt('prg_still_valid') : $this->lng->txt('prg_renewal_required');
			} else {
				$rec['prg_validity'] = '';
				$rec['vq_date'] = '';
			}

			$usr_id = (int)$rec['usr_id'];
			$rec["orgus"] = \ilObjUser::lookupOrgUnitsRepresentation($usr_id);
			$members_list[] = $rec;
		}

		return $members_list;
	}

	/**
	 * Get maximum number of rows the table could have
	 *
	 * @param int 	$a_prg_id
	 * @param array	$filter
	 *
	 * @return int
	 */
	protected function countFetchData(int $a_prg_id, array $filter = []) {
		// TODO: Reimplement this in terms of ActiveRecord when innerjoin
		// supports the required rename functionality
		$query = "SELECT count(prgrs.id) as cnt";
		$query .= $this->getFrom();
		$query .= $this->getWhere($a_prg_id);
		$query .= $this->getFilterWhere($filter);

		$res = $this->db->query($query);
		$rec = $this->db->fetchAssoc($res);

		return $rec["cnt"];
	}

	/**
	 * Get the sql part FROM
	 *
	 * @return string
	 */
	protected function getFrom() {
		return "  FROM ".ilStudyProgrammeProgressDBRepository::TABLE." prgrs"
				."  JOIN usr_data pcp ON pcp.usr_id = prgrs.usr_id"
				."  JOIN ".ilStudyProgrammeAssignmentDBRepository::TABLE." ass"
						 ." ON ass.id = prgrs.assignment_id"
				."  JOIN object_data blngs ON blngs.obj_id = ass.root_prg_id"
				."  LEFT JOIN usr_data ass_usr ON ass_usr.usr_id = ass.last_change_by"
				."  LEFT JOIN usr_data cmpl_usr ON cmpl_usr.usr_id = prgrs.completion_by"
				."  LEFT JOIN object_data cmpl_obj ON cmpl_obj.obj_id = prgrs.completion_by";
	}

	/**
	 * Get the sql part WHERE
	 *
	 * @param int 	$a_prg_id
	 *
	 * @return string
	 */
	protected function getWhere($a_prg_id) {
		$q = " WHERE prgrs.prg_id = ".$this->db->quote($a_prg_id, "integer");
		if($this->prg->getAccessControlByOrguPositionsGlobal()) {
			$visible = $this->getParentObject()->visibleUsers();
			if(count($visible) > 0) {
				$q .= "	AND ".$this->db->in("prgrs.usr_id",$visible,false,"integer");
			} else {
				$q .= " AND FALSE";
			}
		}
		return $q;
	}

	/**
	 * Get selectable columns
	 *
	 * @return array[] 	$cols
	 */
	public function getSelectableColumns() {

		$cols = [];
		foreach (self::COLUMNS as $column) {
			list($col, $lng_var, $optional, $lp, $no_lp) = $column;
			if($optional) {
				$cols[$col] = ["txt" => $this->lng->txt($lng_var)];
			}
		}
		return $cols;
	}

	/**
	 * Add multicommands to table
	 *
	 * @return null
	 */
	protected function addMultiCommands()
	{
		foreach ($this->getMultiCommands() as $cmd => $caption) {
			$this->addMultiCommand($cmd, $caption);
		}
	}

	/**
	 * Get possible multicommnds
	 *
	 * @return string[]
	 */
	protected function getMultiCommands()
	{
		return array(
			'markAccreditedMulti' => $this->lng->txt('prg_multi_mark_accredited'),
			'unmarkAccreditedMulti' => $this->lng->txt('prg_multi_unmark_accredited'),
			'removeUserMulti' => $this->lng->txt('prg_multi_remove_user'),
			'markRelevantMulti' => $this->lng->txt('prg_multi_mark_relevant'),
			'markNotRelevantMulti' => $this->lng->txt('prg_multi_unmark_relevant'),
			'updateFromCurrentPlanMulti' => $this->lng->txt('prg_multi_update_from_current_plan')
		);
	}

	/**
	 * Get options of filter "validity".
	 *
	 * @return array
	 */
	protected function getValidityOptions(): array
	{
		return [
			self::VALIDITY_OPTION_VALID => $this->lng->txt("prg_still_valid"),
			self::VALIDITY_OPTION_RENEWAL_REQUIRED => $this->lng->txt("prg_renewal_required")
		];
	}

	/**
	 * Get options of filter "status".
	 *
	 * @return array
	 */
	protected function getStatusOptions(): array
	{
		return [
			ilStudyProgrammeProgress::STATUS_IN_PROGRESS => $this->lng->txt("prg_status_in_progress"),
			ilStudyProgrammeProgress::STATUS_COMPLETED => $this->lng->txt("prg_status_completed"),
			ilStudyProgrammeProgress::STATUS_ACCREDITED => $this->lng->txt("prg_status_accredited"),
			ilStudyProgrammeProgress::STATUS_NOT_RELEVANT => $this->lng->txt("prg_status_not_relevant"),
			ilStudyProgrammeProgress::STATUS_FAILED => $this->lng->txt("prg_status_failed")
		];
	}

	/**
	 * @inheritdoc
	 */
	function initFilter()
	{
		$item = $this->addFilterItemByMetaType('prg_validity', self::FILTER_SELECT);
		$item->setOptions(
			[self::OPTION_ALL => $this->lng->txt("all")]
			+ $this->getValidityOptions()
		);

		$item = $this->addFilterItemByMetaType('prg_status', self::FILTER_SELECT);
		$item->setOptions(
			[self::OPTION_ALL => $this->lng->txt("all")]
			+ $this->getStatusOptions()
		);

		$this->addFilterItemByMetaType('name', self::FILTER_TEXT);
		$this->addFilterItemByMetaType('prg_expiry_date', self::FILTER_DATE_RANGE);
	}

	/**
	 * Get filter-values by field id.
	 *
	 * @return array
	 */
	protected function getFilterValues(): array
	{
		$f = [];
		foreach($this->filters as $item) {
			$f[$item->getFieldId()] = $this->getFilterValue($item);
		}
		return $f;
	}

	/**
	 * Get the additional sql WHERE-part for filters.
	 *
	 * @param array $filter
	 * @return string
	 */
	protected function getFilterWhere(array $filter): string
	{
		$buf = [''];

		if(strlen($filter['name']) > 0) {
			$name = substr($this->db->quote($filter['name'], "text"),1,-1);
			$name_filter = 'AND ('.PHP_EOL
				.'pcp.firstname LIKE \'%' .$name .'%\' OR' .PHP_EOL
				.'pcp.lastname LIKE \'%' .$name .'%\' OR' .PHP_EOL
				.'pcp.login LIKE \'%' .$name .'%\''.PHP_EOL
			.')';
			$buf[] = $name_filter;
		}

		if($filter['prg_status'] && (int)$filter['prg_status'] !== self::OPTION_ALL) {
			$buf[] = 'AND prgrs.status = '
				.$this->db->quote($filter['prg_status'], "integer");
		}

		if($filter['prg_validity'] && (int)$filter['prg_validity'] !== self::OPTION_ALL) {
			$operator = '<='; //self::VALIDITY_OPTION_RENEWAL_REQUIRED
			if($filter['prg_validity'] === self::VALIDITY_OPTION_VALID) {
				$operator = '>';
			}
			$buf[] = 'AND prgrs.vq_date ' .$operator .' NOW()';
		}

		$exp_from = $filter['prg_expiry_date']['from'];
		if(! is_null($exp_from)) {
			$dat = $exp_from->get(IL_CAL_DATETIME);
			$buf[] = 'AND prgrs.vq_date >= \'' .$dat .'\'';
		}

		$exp_to = $filter['prg_expiry_date']['to'];
		if(! is_null($exp_to)) {
			$dat = $exp_to->get(IL_CAL_DATETIME);
			$buf[] = 'AND prgrs.vq_date <= \'' .$dat .'\'';
		}

		$conditions = implode(PHP_EOL, $buf);
		return $conditions;
	}

}
