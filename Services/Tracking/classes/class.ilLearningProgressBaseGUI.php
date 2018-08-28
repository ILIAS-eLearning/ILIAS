<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';

/**
 * Class ilObjUserTrackingGUI
 * 
 * Base class for all Learning progress gui classes.
 * Defines modes for presentation according to the context in which it was called
 * E.g: mode LP_CONTEXT_PERSONAL_DESKTOP displays only listOfObjects.
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @version $Id$
 *
 * @package ilias-tracking
 *
 */
class ilLearningProgressBaseGUI 
{
	var $tpl = null;
	var $ctrl = null;
	var $lng = null;	
	var $ref_id = 0;
	var $mode = 0;	
	
	protected $anonymized;
	
	/**
	 * @var ilLogger
	 */
	protected $logger;
	
	
	
	const LP_CONTEXT_PERSONAL_DESKTOP = 1;
	const LP_CONTEXT_ADMINISTRATION = 2;
	const LP_CONTEXT_REPOSITORY = 3;
	const LP_CONTEXT_USER_FOLDER = 4;
	const LP_CONTEXT_ORG_UNIT = 5;
	
	const LP_ACTIVE_SETTINGS = 1;
	const LP_ACTIVE_OBJECTS = 2;
	const LP_ACTIVE_PROGRESS = 3;
	// const LP_ACTIVE_LM_STATISTICS = 4; obsolete
	const LP_ACTIVE_USERS = 5;
	const LP_ACTIVE_SUMMARY = 6;
	const LP_ACTIVE_OBJSTATACCESS = 7;
	const LP_ACTIVE_OBJSTATTYPES = 8;
	const LP_ACTIVE_OBJSTATDAILY = 9;
	const LP_ACTIVE_OBJSTATADMIN = 10;
	const LP_ACTIVE_MATRIX = 11;

	function __construct($a_mode,$a_ref_id = 0,$a_usr_id = 0)
	{
		global $DIC;

		$tpl = $DIC['tpl'];
		$ilCtrl = $DIC['ilCtrl'];
		$lng = $DIC['lng'];
		$ilObjDataCache = $DIC['ilObjDataCache'];
		$ilTabs = $DIC['ilTabs'];

		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->lng->loadLanguageModule('trac');
		$this->tabs_gui = $ilTabs;

		$this->mode = $a_mode;
		$this->ref_id = $a_ref_id;
		$this->obj_id = $ilObjDataCache->lookupObjId($this->ref_id);
		$this->obj_type = $ilObjDataCache->lookupType($this->obj_id);
		$this->usr_id = $a_usr_id;

		$this->anonymized = (bool)!ilObjUserTracking::_enabledUserRelatedData();
		if(!$this->anonymized && $this->obj_id)
		{
			include_once "Services/Object/classes/class.ilObjectLP.php";
			$olp = ilObjectLP::getInstance($this->obj_id);
			$this->anonymized = $olp->isAnonymized();
		}
		
		$this->logger = $GLOBALS['DIC']->logger()->trac();
	}

	function isAnonymized()
	{
		return $this->anonymized;
	}

	function getMode()
	{
		return $this->mode;
	}

	function getRefId()
	{
		return $this->ref_id;
	}

	function getObjId()
	{
		return $this->obj_id;
	}

	function getUserId()
	{
		if($this->usr_id)
		{
			return $this->usr_id;
		}
		if((int) $_GET['user_id'])
		{
			return (int) $_GET['user_id'];
		}
		return 0;
	}
	
	// Protected
	function __getDefaultCommand()
	{
		if(strlen($cmd = $this->ctrl->getCmd()))
		{
			return $cmd;
		}
		return 'show';
	}

	function __setSubTabs($a_active)
	{
		global $DIC;

		$rbacsystem = $DIC['rbacsystem'];
		$ilObjDataCache = $DIC['ilObjDataCache'];


		
		switch($this->getMode())
		{
			case self::LP_CONTEXT_PERSONAL_DESKTOP:

				include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
				if(ilObjUserTracking::_hasLearningProgressLearner() && 
					ilObjUserTracking::_enabledUserRelatedData())
				{
					$this->tabs_gui->addTarget('trac_progress',
													$this->ctrl->getLinkTargetByClass('illplistofprogressgui',''),
													"","","",$a_active == self::LP_ACTIVE_PROGRESS);
				}

				if(ilObjUserTracking::_hasLearningProgressOtherUsers())
				{
					$this->tabs_gui->addTarget('trac_objects',
													 $this->ctrl->getLinkTargetByClass("illplistofobjectsgui",''),
													 "","","",$a_active == self::LP_ACTIVE_OBJECTS);
				}
				break;


			case self::LP_CONTEXT_REPOSITORY:
				// #12771 - do not show status if learning progress is deactivated					
				include_once './Services/Object/classes/class.ilObjectLP.php';
				$olp = ilObjectLP::getInstance($this->obj_id);			
				if($olp->isActive())
				{
					include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
					$has_read = ilLearningProgressAccess::checkPermission('read_learning_progress', $this->getRefId());
								
					if($this->isAnonymized() || !$has_read)
					{
						$this->ctrl->setParameterByClass('illplistofprogressgui','user_id',$this->getUserId());
						$this->tabs_gui->addSubTabTarget('trac_progress',
														 $this->ctrl->getLinkTargetByClass('illplistofprogressgui',''),
														 "","","",$a_active == self::LP_ACTIVE_PROGRESS);
					}
					else
					{
						// Check if it is a course
						$sub_tab = ($ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($this->getRefId())) == 'crs') ?
							'trac_crs_objects' :
							'trac_objects';

						$this->tabs_gui->addSubTabTarget($sub_tab,
														 $this->ctrl->getLinkTargetByClass("illplistofobjectsgui",''),
														 "","","",$a_active == self::LP_ACTIVE_OBJECTS);
					}

					if($has_read)
					{
						if(!$this->isAnonymized() && 
							!($olp instanceof ilPluginLP) &&
							ilObjectLP::supportsMatrixView($this->obj_type))
						{
							$this->tabs_gui->addSubTabTarget("trac_matrix",
															$this->ctrl->getLinkTargetByClass("illplistofobjectsgui", 'showUserObjectMatrix'),
															"", "", "", $a_active == self::LP_ACTIVE_MATRIX);							
						}

						$this->tabs_gui->addSubTabTarget("trac_summary",
														$this->ctrl->getLinkTargetByClass("illplistofobjectsgui", 'showObjectSummary'),
														"", "", "", $a_active == self::LP_ACTIVE_SUMMARY);
					}
				}				
				include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
				if(!($olp instanceof ilPluginLP) &&
					ilLearningProgressAccess::checkPermission('edit_learning_progress', $this->getRefId()))
				{
					$this->tabs_gui->addSubTabTarget('trac_settings',
													 $this->ctrl->getLinkTargetByClass('illplistofsettingsgui',''),
													 "","","",$a_active == self::LP_ACTIVE_SETTINGS);
				}
				break;

			case self::LP_CONTEXT_ADMINISTRATION:
				/*
				$this->tabs_gui->addSubTabTarget('trac_progress',
									 $this->ctrl->getLinkTargetByClass('illplistofprogressgui',''),
									 "","","",$a_active == self::LP_ACTIVE_PROGRESS);
				*/
				$this->tabs_gui->addSubTabTarget('trac_objects',
									 $this->ctrl->getLinkTargetByClass("illplistofobjectsgui",''),
									 "","","",$a_active == self::LP_ACTIVE_OBJECTS);
				break;

			case self::LP_CONTEXT_USER_FOLDER:
			case self::LP_CONTEXT_ORG_UNIT:
				// No tabs default class is lpprogressgui
				break;

			default:
				die ('No valid mode given');
				break;
		}

		return true;
	}

	function __buildFooter()
	{
		switch($this->getMode())
		{
			case self::LP_CONTEXT_PERSONAL_DESKTOP:

				$this->tpl->show(true);
		}
	}

	function __buildHeader()
	{
		if($this->getMode() == self::LP_CONTEXT_PERSONAL_DESKTOP)
		{
			$this->tpl->setTitle($this->lng->txt("learning_progress"));
			
			// set locator
/*
			$this->tpl->setVariable("TXT_LOCATOR", $this->lng->txt("locator"));
			$this->tpl->touchBlock("locator_separator");
			$this->tpl->touchBlock("locator_item");
			//$this->tpl->setCurrentBlock("locator_item");
			//$this->tpl->setVariable("ITEM", $this->lng->txt("personal_desktop"));
			//$this->tpl->setVariable("LINK_ITEM",
			//						$this->ctrl->getLinkTargetByClass("ilpersonaldesktopgui"));
			//$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $this->lng->txt("learning_progress"));
			$this->tpl->setVariable("LINK_ITEM",
									$this->ctrl->getLinkTargetByClass('illearningprogressgui'));
			$this->tpl->parseCurrentBlock();
*/
		
			// display infopanel if something happened
			ilUtil::infoPanel();
		}

	}

	/**
	* insert path
	*/
	function __insertPath(&$a_tpl,$a_ref_id)
	{
		global $DIC;

		$tree = $DIC['tree'];

		$path_arr = $tree->getPathFull($a_ref_id);
		$counter = 0;
		foreach($tree->getPathFull($a_ref_id) as $data)
		{
			if($counter++)
			{
				$path .= " -> ";
			}
			$path .= $data['title'];
		}
		$a_tpl->setCurrentBlock("path_item");
		$a_tpl->setVariable("PATH_ITEM",$path);
		$a_tpl->parseCurrentBlock();

		$a_tpl->setCurrentBlock("path");
		$a_tpl->parseCurrentBlock();

		return $path;
	}

	function __showImageByStatus(&$tpl,$a_status,$tpl_prefix = "")
	{
		return ilLearningProgressBaseGUI::_showImageByStatus($tpl,$a_status,$tpl_prefix);
	}
	
	// we need this public in table classes
	public static function _showImageByStatus(&$tpl,$a_status,$tpl_prefix = "")
	{
		global $DIC;

		$lng = $DIC['lng'];
		
		$tpl->setVariable($tpl_prefix."STATUS_IMG",
			ilLearningProgressBaseGUI::_getImagePathForStatus($a_status));
		$tpl->setVariable($tpl_prefix."STATUS_ALT",$lng->txt($a_status));
		
		return true;
	}
	
	/**
	 * Get image path for status
	 */
	static function _getImagePathForStatus($a_status)
	{
		include_once("./Services/Tracking/classes/class.ilLPStatus.php");

		// constants are either number or string, so make comparison string-based
		switch((string)$a_status)
		{
			case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
			case ilLPStatus::LP_STATUS_IN_PROGRESS:
			case ilLPStatus::LP_STATUS_REGISTERED:
				return ilUtil::getImagePath('scorm/incomplete.svg');
				break;

			case ilLPStatus::LP_STATUS_COMPLETED_NUM:
			case ilLPStatus::LP_STATUS_COMPLETED:
			case ilLPStatus::LP_STATUS_PARTICIPATED:
				return ilUtil::getImagePath('scorm/complete.svg');
				break;
			
			case ilLPStatus::LP_STATUS_NOT_ATTEMPTED:
			case ilLPStatus::LP_STATUS_NOT_PARTICIPATED:
			case ilLPStatus::LP_STATUS_NOT_REGISTERED:
				return ilUtil::getImagePath('scorm/not_attempted.svg');
				break;

			case ilLPStatus::LP_STATUS_FAILED_NUM:
			case ilLPStatus::LP_STATUS_FAILED:
				return ilUtil::getImagePath('scorm/failed.svg');
				break;
			
			default:
				return ilUtil::getImagePath('scorm/not_attempted.svg');
				break;
		}		
	}

	/**
	 * Get status alt text
	 */
	static function _getStatusText($a_status, $a_lng = null)
	{
		global $DIC;

		$lng = $DIC['lng'];
		
		if(!$a_lng)
		{
			$a_lng = $lng;
		}
		
		include_once("./Services/Tracking/classes/class.ilLPStatus.php");
//echo "#".$a_status."#";
		switch($a_status)
		{
			case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
				return $a_lng->txt(ilLPStatus::LP_STATUS_IN_PROGRESS);
				
			case ilLPStatus::LP_STATUS_COMPLETED_NUM:
				return $a_lng->txt(ilLPStatus::LP_STATUS_COMPLETED);

			case ilLPStatus::LP_STATUS_FAILED_NUM:
				return $a_lng->txt(ilLPStatus::LP_STATUS_FAILED);

			default:
				if ($a_status === ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM)
				{
					return $a_lng->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED);
				}
				return $a_lng->txt($a_status);
		}		
	}


	// Protected Table gui methods
	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}


	/**
	* show details about current object. Uses an existing info_gui object.
	*/
	function __showObjectDetails(&$info,$item_id = 0,$add_section = true)
	{
		global $DIC;

		$ilObjDataCache = $DIC['ilObjDataCache'];

		$details_id = $item_id ? $item_id : $this->details_id;
		
		include_once 'Services/Object/classes/class.ilObjectLP.php';
		$olp = ilObjectLP::getInstance($details_id);													
		$mode = $olp->getCurrentMode();

		include_once './Services/MetaData/classes/class.ilMDEducational.php';
		if($mode == ilLPObjSettings::LP_MODE_VISITS ||
		   ilMDEducational::_getTypicalLearningTimeSeconds($details_id))
		{
			// Section object details
			if($add_section)
			{
				$info->addSection($this->lng->txt('details'));
			}

			if($mode == ilLPObjSettings::LP_MODE_VISITS)
			{
				$info->addProperty($this->lng->txt('trac_required_visits'), ilLPObjSettings::_lookupVisits($details_id));
			}

			if($seconds = ilMDEducational::_getTypicalLearningTimeSeconds($details_id))
			{
				$info->addProperty($this->lng->txt('meta_typical_learning_time'), ilDatePresentation::secondsToString($seconds));
			}

			return true;
		}
		return false;
	}

	function __appendUserInfo(&$info, $a_user)
	{
		global $DIC;

		$ilUser = $DIC['ilUser'];
		
		// #13525 - irrelevant personal data is not to be presented
		return;

		if(!is_object($a_user))
		{
			$a_user = ilObjectFactory::getInstanceByObjId($a_user);
		}

		if($a_user->getId() != $ilUser->getId())
		{
			$info->addSection($this->lng->txt("trac_user_data"));
			// $info->addProperty($this->lng->txt('username'),$a_user->getLogin());
			// $info->addProperty($this->lng->txt('name'),$a_user->getFullname());
			$info->addProperty($this->lng->txt('last_login'),
				ilDatePresentation::formatDate(new ilDateTime($a_user->getLastLogin(),IL_CAL_DATETIME)));
			$info->addProperty($this->lng->txt('trac_total_online'),
				ilDatePresentation::secondsToString(ilOnlineTracking::getOnlineTime($a_user->getId())));
		   return true;
		}
	}

	function __appendLPDetails(&$info,$item_id,$user_id)
	{
		global $DIC;

		$ilObjDataCache = $DIC['ilObjDataCache'];

		$type = $ilObjDataCache->lookupType($item_id);
		
		// Section learning_progress
		// $info->addSection($this->lng->txt('trac_learning_progress'));
		// see ilLPTableBaseGUI::parseTitle();
		$info->addSection($this->lng->txt("trac_progress").": ".ilObject::_lookupTitle($item_id));
		
		$olp = ilObjectLP::getInstance($item_id);
		$info->addProperty($this->lng->txt('trac_mode'),
			$olp->getModeText($olp->getCurrentMode()));
		
		switch($type)
		{
			case 'lm':
			case 'htlm':
				include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
				$progress = ilLearningProgress::_getProgress($user_id,$item_id);
			
				if($progress['access_time'])
				{
					$info->addProperty($this->lng->txt('last_access'),
						ilDatePresentation::formatDate(new ilDateTime($progress['access_time'],IL_CAL_UNIX)));
				}
				else
				{
					$info->addProperty($this->lng->txt('last_access'),$this->lng->txt('trac_not_accessed'));
				}
				$info->addProperty($this->lng->txt('trac_visits'),(int) $progress['visits']);
				if(ilObjectLP::supportsSpentSeconds($type))
				{
					$info->addProperty($this->lng->txt('trac_spent_time'),ilDatePresentation::secondsToString($progress['spent_seconds']));
				}
				// fallthrough
				
			case 'exc':
			case 'tst':
			case 'file':
			case 'mcst':
			case 'svy':
			case 'crs':
			case 'sahs':
			case 'grp':
			case 'iass':
			case 'copa':
			case 'sess':
				// display status as image
				include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
				$status = $this->__readStatus($item_id,$user_id);
				$status_path = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
				$status_text = ilLearningProgressBaseGUI::_getStatusText($status);
				$info->addProperty($this->lng->txt('trac_status'), 
					ilUtil::img($status_path, $status_text)." ".$status_text);
				
				// #15334 - see ilLPTableBaseGUI::isPercentageAvailable()
				$mode = $olp->getCurrentMode();
				if(in_array($mode, array(ilLPObjSettings::LP_MODE_TLT, 
					ilLPObjSettings::LP_MODE_VISITS, 
					// ilLPObjSettings::LP_MODE_OBJECTIVES, 
					ilLPObjSettings::LP_MODE_SCORM,
					ilLPObjSettings::LP_MODE_TEST_PASSED)))
				{
					include_once 'Services/Tracking/classes/class.ilLPStatus.php';
					$perc = ilLPStatus::_lookupPercentage($item_id, $user_id);
					$info->addProperty($this->lng->txt('trac_percentage'), (int)$perc."%");
				}				
				break;

		}
		
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';

		if(ilObjectLP::supportsMark($type))
		{			
			if(strlen($mark = ilLPMarks::_lookupMark($user_id,$item_id)))
			{
				$info->addProperty($this->lng->txt('trac_mark'),$mark);
			}
		}
		
		if(strlen($comment = ilLPMarks::_lookupComment($user_id,$item_id)))
		{
			$info->addProperty($this->lng->txt('trac_comment'),$comment);
		}
	}

	static function __readStatus($a_obj_id,$user_id)
	{
		include_once 'Services/Tracking/classes/class.ilLPStatus.php';
		$status = ilLPStatus::_lookupStatus($a_obj_id, $user_id);

		include_once("./Services/Tracking/classes/class.ilLPStatus.php");
		switch($status)
		{
			case ilLPStatus::LP_STATUS_IN_PROGRESS_NUM:
				return ilLPStatus::LP_STATUS_IN_PROGRESS;

			case ilLPStatus::LP_STATUS_COMPLETED_NUM:
				return ilLPStatus::LP_STATUS_COMPLETED;

			case ilLPStatus::LP_STATUS_FAILED_NUM:
				return ilLPStatus::LP_STATUS_FAILED;

			case ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM:
				return ilLPStatus::LP_STATUS_NOT_ATTEMPTED;

			default:
				return $status;
		}
	}

	/**
	* Function that sorts ids by a given table field using WHERE IN
	* E.g: __sort(array(6,7),'usr_data','lastname','usr_id') => sorts by lastname
	* 
	* @param array Array of ids
	* @param string table name
	* @param string table field
	* @param string id name
	* @return array sorted ids
	*
	* @access protected
	*/
	function __sort($a_ids,$a_table,$a_field,$a_id_name)
	{
		global $DIC;

		$ilDB = $DIC['ilDB'];

		if(!$a_ids)
		{
			return array();
		}

		// comment by mjansen: Requesting database in gui classes?

		// use database to sort user array
		$where = "WHERE ".$ilDB->in($a_id_name, $a_ids, false, 'integer')." ";

		$query = "SELECT ".$a_id_name." FROM ".$a_table." ".
			$where.
			"ORDER BY ".$a_field;

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$ids[] = $row->$a_id_name;
		}
		return $ids ? $ids : array();
	}

	function __getPercent($max,$reached)
	{
		if(!$max)
		{
			return "0%";
		}

		return sprintf("%d%%",$reached / $max * 100);
	}

	function __readItemStatusInfo($a_items)
	{
		global $DIC;

		$ilObjDataCache = $DIC['ilObjDataCache'];

		include_once 'Services/Object/classes/class.ilObjectLP.php';
		
		foreach($a_items as $item_id)
		{			
			$olp = ilObjectLP::getInstance($item_id);													
			
			$this->obj_data[$item_id]['type'] = $ilObjDataCache->lookupType($item_id);
			$this->obj_data[$item_id]['mode'] = $olp->getCurrentMode();						
			if($this->obj_data[$item_id]['mode'] == ilLPObjSettings::LP_MODE_TLT)
			{
				include_once './Services/MetaData/classes/class.ilMDEducational.php';
				$this->obj_data[$item_id]['tlt'] = ilMDEducational::_getTypicalLearningTimeSeconds($item_id);
			}
			if($this->obj_data[$item_id]['mode'] == ilLPObjSettings::LP_MODE_VISITS)
			{				
				$this->obj_data[$item_id]['visits'] = ilLPObjSettings::_lookupVisits($item_id);
			}
			if($this->obj_data[$item_id]['mode'] == ilLPObjSettings::LP_MODE_SCORM)
			{
				$collection = $olp->getCollectionInstance();
				if($collection)
				{
					$this->obj_data[$item_id]['scos'] = count($collection->getItems());
				}
			}
		}
	}

	function __getLegendHTML()
	{
		global $DIC;

		$lng = $DIC['lng'];
		
		$tpl = new ilTemplate("tpl.lp_legend.html", true, true, "Services/Tracking");
		$tpl->setVariable("IMG_NOT_ATTEMPTED",
			ilUtil::getImagePath("scorm/not_attempted.svg"));
		$tpl->setVariable("IMG_IN_PROGRESS",
			ilUtil::getImagePath("scorm/incomplete.svg"));
		$tpl->setVariable("IMG_COMPLETED",
			ilUtil::getImagePath("scorm/completed.svg"));
		$tpl->setVariable("IMG_FAILED",
			ilUtil::getImagePath("scorm/failed.svg"));
		$tpl->setVariable("TXT_NOT_ATTEMPTED",
			$lng->txt("trac_not_attempted"));
		$tpl->setVariable("TXT_IN_PROGRESS",
			$lng->txt("trac_in_progress"));
		$tpl->setVariable("TXT_COMPLETED",
			$lng->txt("trac_completed"));
		$tpl->setVariable("TXT_FAILED",
			$lng->txt("trac_failed"));
		
		include_once "Services/UIComponent/Panel/classes/class.ilPanelGUI.php";
		$panel = ilPanelGUI::getInstance();
		$panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_SECONDARY);
		$panel->setBody($tpl->get());
		
		return $panel->getHTML();
	}
	
	protected function initEditUserForm($a_user_id, $a_obj_id, $a_cancel = null)
	{
		global $DIC;

		$lng = $DIC['lng'];
		$ilCtrl = $DIC['ilCtrl'];
		
		include_once 'Services/Object/classes/class.ilObjectLP.php';
		$olp = ilObjectLP::getInstance($a_obj_id);		
		$lp_mode = $olp->getCurrentMode();
		
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();		
		
		$form->setFormAction($ilCtrl->getFormAction($this, "updateUser"));
		
		$form->setTitle($lng->txt("edit").": ".ilObject::_lookupTitle($a_obj_id));
		$form->setDescription($lng->txt('trac_mode').": ".$olp->getModeText($lp_mode));
		
		include_once "Services/User/classes/class.ilUserUtil.php";
		$user = new ilNonEditableValueGUI($lng->txt("user"), null, true);
		$user->setValue(ilUserUtil::getNamePresentation($a_user_id, true));
		$form->addItem($user);
				
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';
		$marks = new ilLPMarks($a_obj_id, $a_user_id);
		
		if(ilObjectLP::supportsMark(ilObject::_lookupType($a_obj_id)))
		{
			$mark = new ilTextInputGUI($lng->txt("trac_mark"), "mark");
			$mark->setValue($marks->getMark());
			$mark->setMaxLength(32);
			$form->addItem($mark);
		}
		
		$comm = new ilTextInputGUI($lng->txt("trac_comment"), "comment");
		$comm->setValue($marks->getComment());
		$form->addItem($comm);
			
		if($lp_mode == ilLPObjSettings::LP_MODE_MANUAL || 
			$lp_mode == ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR)
		{
			include_once("./Services/Tracking/classes/class.ilLPStatus.php");
			$completed = ilLPStatus::_lookupStatus($a_obj_id, $a_user_id);	
			
			$status = new ilCheckboxInputGUI($lng->txt('trac_completed'), "completed");
			$status->setChecked(($completed == ilLPStatus::LP_STATUS_COMPLETED_NUM));
			$form->addItem($status);
		}
			
		$form->addCommandButton("updateUser", $lng->txt('save'));
		
		if($a_cancel)
		{
			$form->addCommandButton($a_cancel, $lng->txt('cancel'));
		}
		
		return $form;
	}

	function __showEditUser($a_user_id, $a_ref_id, $a_cancel, $a_sub_id = false)
	{				
		global $DIC;

		$ilCtrl = $DIC['ilCtrl'];
		
		if(!$a_sub_id)
        {
			$obj_id = ilObject::_lookupObjId($a_ref_id);
		}
		else
		{
			$ilCtrl->setParameter($this,'userdetails_id',$a_sub_id);
			$obj_id = ilObject::_lookupObjId($a_sub_id);
		}		
				
		$ilCtrl->setParameter($this, 'user_id', $a_user_id);
		$ilCtrl->setParameter($this, 'details_id', $a_ref_id);
		
		$form = $this->initEditUserForm($a_user_id, $obj_id, $a_cancel);
		
		return $form->getHTML();
	}

	function __updateUser($user_id, $obj_id)
	{		
		$form = $this->initEditUserForm($user_id, $obj_id);
		if($form->checkInput())
		{
			include_once 'Services/Tracking/classes/class.ilLPMarks.php';

			$marks = new ilLPMarks($obj_id, $user_id);
			$marks->setMark($form->getInput("mark"));
			$marks->setComment($form->getInput("comment"));
			
			$do_lp = false;
			
			// status/completed is optional
			$status = $form->getItemByPostVar("completed");
			if(is_object($status))
			{			
				if($marks->getCompleted() != $form->getInput("completed"))
				{
					$marks->setCompleted($form->getInput("completed"));
					$do_lp = true;
				}
			}

			$marks->update();

			// #11600
			if($do_lp)
			{
				include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
				ilLPStatusWrapper::_updateStatus($obj_id, $user_id);			
			}
		}
	}
	
	static function isObjectOffline($a_obj_id, $a_type = null)
	{
		global $DIC;

		$objDefinition = $DIC['objDefinition'];
		$ilObjDataCache = $DIC['ilObjDataCache'];

		if(!$a_type)
		{
			$a_type = $ilObjDataCache->lookupType($a_obj_id);
		}
		
		if($objDefinition->isPluginTypeName($a_type))
		{
			return false;
		}
		
		$class = "ilObj".$objDefinition->getClassName($a_type)."Access";
		include_once $objDefinition->getLocation($a_type)."/class.".$class.".php";
		return call_user_func(array($class,'_isOffline'), $a_obj_id);

		// PHP 5.3 only ?
		//return $class::_isOffline($obj_id);
	}
}

?>