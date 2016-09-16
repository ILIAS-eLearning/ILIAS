<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
include_once 'Services/Search/classes/class.ilUserFilterGUI.php';

/**
* Class ilObjUserTrackingGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @version $Id: class.ilLPListOfObjectsGUI.php 56470 2014-12-16 15:47:28Z jluetzen $
*
* @ilCtrl_Calls ilLPListOfObjectsGUI: ilUserFilterGUI, ilTrUserObjectsPropsTableGUI, ilTrSummaryTableGUI, ilTrObjectUsersPropsTableGUI, ilTrMatrixTableGUI
*
* @package ilias-tracking
*
*/
class ilLPListOfObjectsGUI extends ilLearningProgressBaseGUI
{
	var $details_id = 0;
	var $details_type = '';
	var $details_mode = 0;

	function ilLPListOfObjectsGUI($a_mode,$a_ref_id)
	{
		global $ilUser,$ilObjDataCache;

		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id);

		// Set item id for details
		$this->__initDetails((int) $_REQUEST['details_id']);
	}
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilUser;

		$this->ctrl->setReturn($this, "");

		switch($this->ctrl->getNextClass())
		{
			case 'iltruserobjectspropstablegui':
				$user_id = (int)$_GET["user_id"];
				$this->ctrl->setParameter($this, "user_id", $user_id);

				$this->ctrl->setParameter($this, "details_id", $this->details_id);

				include_once("./Services/Tracking/classes/repository_statistics/class.ilTrUserObjectsPropsTableGUI.php");
				$table_gui = new ilTrUserObjectsPropsTableGUI($this, "userDetails",
					$user_id, $this->details_obj_id, $this->details_id);
				$this->ctrl->forwardCommand($table_gui);
				break;

			case 'iltrsummarytablegui':
				$cmd = "showObjectSummary";
				if(!$this->details_id)
				{
					$this->details_id = ROOT_FOLDER_ID;
					$cmd =  "show";
				}
				include_once './Services/Tracking/classes/repository_statistics/class.ilTrSummaryTableGUI.php';
			    $table_gui = new ilTrSummaryTableGUI($this, $cmd, $this->details_id);
				$this->ctrl->forwardCommand($table_gui);
				break;

			case 'iltrmatrixtablegui':
				include_once './Services/Tracking/classes/repository_statistics/class.ilTrMatrixTableGUI.php';
			    $table_gui = new ilTrMatrixTableGUI($this, "showUserObjectMatrix", $this->details_id);
				$this->ctrl->forwardCommand($table_gui);
				break;

			case 'iltrobjectuserspropstablegui':
				$this->ctrl->setParameter($this, "details_id", $this->details_id);

				include_once './Services/Tracking/classes/repository_statistics/class.ilTrObjectUsersPropsTableGUI.php';
			    $table_gui = new ilTrObjectUsersPropsTableGUI($this, "details", $this->details_obj_id, $this->details_id);
				$this->ctrl->forwardCommand($table_gui);
				break;

			default:
			    $cmd = $this->__getDefaultCommand();
				$this->$cmd();
		}

		return true;
	}

	function updateUser()
	{
		global $rbacsystem;

		if(isset($_GET["userdetails_id"]))
		{
			$parent = $this->details_id;
			$this->__initDetails((int)$_GET["userdetails_id"]);
		}

		if(!$rbacsystem->checkAccess('edit_learning_progress', $this->details_id))
		{
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->returnToParent($this);
		}

        // START PATCH RUBRIC CPKN 2015
        include_once 'Services/Object/classes/class.ilObjectLP.php';
		$olp = ilObjectLP::getInstance($this->getObjId());
		$lp_mode = $olp->getCurrentMode();

        if($lp_mode==92){
            $passing_grade=$this->saveRubricGrade();
            //only update progress if grading is completed
            if($passing_grade!==false){
                $this->__updateUserRubric($_REQUEST['user_id'], $this->details_obj_id,$passing_grade);
            }

        }else{

    		$this->__updateUser($_REQUEST['user_id'], $this->details_obj_id);
    		ilUtil::sendSuccess($this->lng->txt('trac_update_edit_user'), true);
        }
        // END PATCH RUBRIC CPKN 2015

		$this->ctrl->setParameter($this, "details_id", $this->details_id); // #15043

		// #14993
		if(!isset($_GET["userdetails_id"]))
		{
			$this->ctrl->redirect($this, "details");
		}
		else
		{
			$this->ctrl->setParameter($this, "userdetails_id", (int)$_GET["userdetails_id"]);
			$this->ctrl->redirect($this, "userdetails");
		}


	}

	// START PATCH RUBRIC CPKN 2016
	function confirmRegrade()
	{
		require_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($this->ctrl->getFormAction($this));
		ilUtil::sendQuestion($this->lng->txt('rubric_regrade_warning'));
		$conf->setFormAction($this->ctrl->getFormAction($this));

		$conf->addHiddenItem('user_id',$_POST['user_id']);
		$conf->setConfirm($this->lng->txt('rubric_regrade'), 'regradeUser');
		$conf->setCancel($this->lng->txt('cancel'), 'cancelRegrade');
		$this->tpl->setContent($conf->getHTML());
	}


	function regradeUser()
	{
		$usr_id = $_POST['user_id'];
		$obj_id = ilObject::_lookupObjectId($_GET['ref_id']);
		include_once("./Services/Tracking/classes/rubric/class.ilLPRubricGrade.php");

		ilLPRubricGrade::_prepareForRegrade($obj_id,$usr_id);

		$obj_gui = new ilLPListOfObjectsGUI(0,$_GET['ref_id']);
		$obj_gui->editUser();;
	}

	function cancelRegrade()
	{
		//send back to the rubric
		$obj_gui = new ilLPListOfObjectsGUI(0,$_GET['ref_id']);
		$obj_gui->editUser();
	}


	// END PATCH RUBRIC CPKN 2016


	function editUser()
	{
		global $ilObjDataCache, $rbacsystem;

		$parent_id = $this->details_id;
		if(isset($_GET["userdetails_id"]))
		{
			$this->__initDetails((int)$_GET["userdetails_id"]);
			$sub_id = $this->details_id;
			$cancel = "userdetails";
		}
		else
		{
			$sub_id = NULL;
			$cancel = "details";
		}

		if(!$rbacsystem->checkAccess('edit_learning_progress', $this->details_id))
		{
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->returnToParent($this);
		}


        // START PATCH RUBRIC CPKN 2015
        include_once 'Services/Object/classes/class.ilObjectLP.php';
		$olp = ilObjectLP::getInstance($this->getObjId());
		$lp_mode = $olp->getCurrentMode();

        if($lp_mode==92){

            $this->showRubricGradeForm();

        }else{

            include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
    		$info = new ilInfoScreenGUI($this);
    		$info->setFormAction($this->ctrl->getFormAction($this));
    		$this->__showObjectDetails($info, $this->details_obj_id);
    		$this->__appendUserInfo($info, (int)$_GET['user_id']);
    		// $this->__appendLPDetails($info,$this->details_obj_id,(int)$_GET['user_id']);

    		$this->tpl->setVariable("ADM_CONTENT", $this->__showEditUser((int)$_GET['user_id'], $parent_id, $cancel, $sub_id).$info->getHTML());

        }
        // END PATCH RUBRIC CPKN 2015
	}

	function details()
	{
		global $ilToolbar;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_loo.html','Services/Tracking');

		// Show back button
		if($this->getMode() == self::LP_CONTEXT_PERSONAL_DESKTOP or
		   $this->getMode() == self::LP_CONTEXT_ADMINISTRATION)
		{
			$print_view = false;

			$ilToolbar->addButton($this->lng->txt('trac_view_list'),
				$this->ctrl->getLinkTarget($this,'show'));
		}
		else
		{
			/*
			$print_view = (bool)$_GET['prt'];
			if(!$print_view)
			{
				$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
				$this->ctrl->setParameter($this, 'prt', 1);
				$ilToolbar->addButton($this->lng->txt('print_view'),$this->ctrl->getLinkTarget($this,'details'), '_blank');
				$this->ctrl->setParameter($this, 'prt', '');
			}
			*/
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($this->ctrl->getFormAction($this));
		if($this->__showObjectDetails($info, $this->details_obj_id))
		{
			$this->tpl->setCurrentBlock("info");
			$this->tpl->setVariable("INFO_TABLE",$info->getHTML());
			$this->tpl->parseCurrentBlock();
		}

		$this->__showUsersList($print_view);
	}

	function __showUsersList($a_print_view = false)
	{
		if($this->isAnonymized())
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'));
			return;
		}

		$this->ctrl->setParameter($this, "details_id", $this->details_id);

		include_once "Services/Tracking/classes/repository_statistics/class.ilTrObjectUsersPropsTableGUI.php";
		$gui = new ilTrObjectUsersPropsTableGUI($this, "details", $this->details_obj_id, $this->details_id, $a_print_view);

		$this->tpl->setVariable("LP_OBJECTS", $gui->getHTML());
		$this->tpl->setVariable("LEGEND", $this->__getLegendHTML());

		/*
		if($a_print_view)
		{
			echo $this->tpl->get("DEFAULT", false, false, false, false, false, false);
			exit();
		}
		*/
	}

	function userDetails()
	{
		global $ilObjDataCache, $ilToolbar;

		if($this->isAnonymized())
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'));
			return;
		}

		$this->ctrl->setParameter($this, "details_id", $this->details_id);

		$print_view = (bool)$_GET['prt'];
		if(!$print_view)
		{
			// Show back button
			$ilToolbar->addButton($this->lng->txt('trac_view_list'), $this->ctrl->getLinkTarget($this,'details'));
		}

		$user_id = (int)$_GET["user_id"];
		$this->ctrl->setParameter($this, "user_id", $user_id);

		/*
		if(!$print_view)
		{
			$this->ctrl->setParameter($this, 'prt', 1);
			$ilToolbar->addButton($this->lng->txt('print_view'),$this->ctrl->getLinkTarget($this,'userDetails'), '_blank');
			$this->ctrl->setParameter($this, 'prt', '');
		};
		*/

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_loo.html','Services/Tracking');

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($this->ctrl->getFormAction($this));
		$this->__showObjectDetails($info, $this->details_obj_id);
		$this->__appendUserInfo($info, $user_id);
		// $this->__appendLPDetails($info,$this->details_obj_id,$user_id);
		$this->tpl->setVariable("INFO_TABLE",$info->getHTML());

		include_once("./Services/Tracking/classes/repository_statistics/class.ilTrUserObjectsPropsTableGUI.php");
		$table = new ilTrUserObjectsPropsTableGUI($this, "userDetails", $user_id,
			$this->details_obj_id, $this->details_id, $print_view);
		$this->tpl->setVariable('LP_OBJECTS', $table->getHTML());
		$this->tpl->setVariable('LEGEND', $this->__getLegendHTML());

		/*
		if($print_view)
		{
			echo $this->tpl->get("DEFAULT", false, false, false, false, false, false);
			exit();
		}
		*/
	}

	function show()
	{
		// Clear table offset
		$this->ctrl->saveParameter($this,'offset',0);

		// Show only detail of current repository item if called from repository
		switch($this->getMode())
		{
			case self::LP_CONTEXT_REPOSITORY:
				$this->__initDetails($this->getRefId());
				$this->details();
				return true;
		}

		$this->__listObjects();
	}

	function __listObjects()
	{
		global $ilUser,$ilObjDataCache;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_list_objects.html','Services/Tracking');

		include_once("./Services/Tracking/classes/repository_statistics/class.ilTrSummaryTableGUI.php");
		$lp_table = new ilTrSummaryTableGUI($this, "", ROOT_FOLDER_ID);

		$this->tpl->setVariable("LP_OBJECTS", $lp_table->getHTML());
		$this->tpl->setVariable('LEGEND', $this->__getLegendHTML());
	}

	function __initDetails($a_details_id)
	{
		global $ilObjDataCache;

		if(!$a_details_id)
		{
			$a_details_id = $this->getRefId();
		}
		if($a_details_id)
		{
			$_GET['details_id'] = $a_details_id;
			$this->details_id = $a_details_id;
			$this->details_obj_id = $ilObjDataCache->lookupObjId($this->details_id);
			$this->details_type = $ilObjDataCache->lookupType($this->details_obj_id);

			include_once 'Services/Object/classes/class.ilObjectLP.php';
			$olp = ilObjectLP::getInstance($this->details_obj_id);
			$this->details_mode = $olp->getCurrentMode();
		}
	}

	/**
	 * Show object-based summarized tracking data
	 */
	function showObjectSummary()
	{
		global $tpl, $ilToolbar;

		/*
		$print_view = (bool)$_GET['prt'];
		if(!$print_view)
		{
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
			$this->ctrl->setParameter($this, 'prt', 1);
			$ilToolbar->addButton($this->lng->txt('print_view'),$this->ctrl->getLinkTarget($this,'showObjectSummary'), '_blank');
			$this->ctrl->setParameter($this, 'prt', '');
		}
		*/

		include_once("./Services/Tracking/classes/repository_statistics/class.ilTrSummaryTableGUI.php");
		$table = new ilTrSummaryTableGUI($this, "showObjectSummary", $this->getRefId(), $print_view);
		if(!$print_view)
		{
			$tpl->setContent($table->getHTML());
		}
		else
		{
			$tpl->setVariable("ADM_CONTENT", $table->getHTML());
			echo $tpl->get("DEFAULT", false, false, false, false, false, false);
			exit();
		}
	}

	/**
	 * Show object user matrix
	 */
	function showUserObjectMatrix()
	{
		global $tpl;

		if($this->isAnonymized())
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'));
			return;
		}


		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_loo.html','Services/Tracking');

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($this->ctrl->getFormAction($this));
		if($this->__showObjectDetails($info, $this->details_obj_id))
		{
			$this->tpl->setCurrentBlock("info");
			$this->tpl->setVariable("INFO_TABLE",$info->getHTML());
			$this->tpl->parseCurrentBlock();
		}

		include_once("./Services/Tracking/classes/repository_statistics/class.ilTrMatrixTableGUI.php");
		$table = new ilTrMatrixTableGUI($this, "showUserObjectMatrix", $this->getRefId());
		$this->tpl->setVariable('LP_OBJECTS', $table->getHTML());
		$this->tpl->setVariable('LEGEND', $this->__getLegendHTML());
	}

    // START PATCH RUBRIC CPKN 2015
    /**
     *  Save Rubric Grade
     */
    private function saveRubricGrade()
    {
        // bring in the rubric card object
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricGrade.php");
        $rubricObj=new ilLPRubricGrade($this->getObjId());

        if($rubricObj->objHasRubric()){
            $rubricObj->grade($rubricObj->load());
            ilUtil::sendSuccess($this->lng->txt('rubric_card_save'));
        }else{
            ilUtil::sendFailure($this->lng->txt('rubric_card_not_defined'));
        }

        if($rubricObj->isGradeCompleted()){
            return($rubricObj->getPassingGrade());
        }else{
            return(false);
        }

    }
    /**
     *  Show Rubric Grade
     */
    public function showRubricGradeForm($history_id = NULL)
    {
        include_once('./Services/Tracking/classes/rubric/class.ilLPRubricGrade.php');
        include_once('./Services/Tracking/classes/rubric/class.ilLPRubricGradeGUI.php');
        $rubricObj=new ilLPRubricGrade($this->getObjId());
        $rubricGui=new ilLPRubricGradeGUI();
        $a_user = ilObjectFactory::getInstanceByObjId((int)$_REQUEST['user_id']);
        if($rubricObj->objHasRubric() && $rubricObj->isRubricComplete()){
			$rubricGui->setUserHistoryId($history_id);
			if($rubricObj->isGradingLocked()) {
				$rubricGui->setRubricGradeLocked($rubricObj->getRubricGradeLocked());
				$rubricGui->setGradeLockOwner($rubricObj->getGradeLockOwner());
			}
            $rubricGui->setRubricData($rubricObj->load());
			$rubricGui->setUserHistory($rubricObj->getUserHistory((int)$_REQUEST['user_id']));
            $rubricGui->setUserData($rubricObj->getRubricUserGradeData((int)$_REQUEST['user_id'],$history_id));
            $rubricGui->getRubricGrade(
                $this->ctrl->getFormAction($this),
                $a_user->getFullName(),
                (int)$_REQUEST['user_id']
            );
        }else{
			if(!$rubricObj->objHasRubric()) {
           	 	ilUtil::sendFailure($this->lng->txt('rubric_card_not_defined'));
			}
			elseif(!$rubricObj->isRubricComplete())
			{
				ilUtil::sendFailure($this->lng->txt('rubric_card_not_completed').'<a href="'.$this->ctrl->getLinkTargetByClass('illplistofobjectsgui', 'showRubricCardForm')
				.'">'.$this->lng->txt('rubric_card_please_complete').'</a>');
			}
        }
	}

	// START PATCH RUBRIC CPKN 2016
	function viewHistory()
	{
		//send back to the rubric
		$obj_gui = new ilLPListOfObjectsGUI(0,$_GET['ref_id']);
		$obj_gui->showRubricGradeForm($_REQUEST['grader_history']);
	}

	// END PATCH RUBRIC CPKN 2016


	/**
     * Save Rubric Card
     */
    public function saveRubricCard()
    {
        // bring in the rubric card object
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricCard.php");
        $rubricObj=new ilLPRubricCard($this->getObjId());

        $rubricObj->save();

        ilUtil::sendSuccess($this->lng->txt('rubric_card_save'));

        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricCardGUI.php");
        $rubricGui=new ilLPRubricCardGUI();

        if($rubricObj->objHasRubric()){
            $rubricGui->setRubricData($rubricObj->load());
        }
        $rubricGui->setPassingGrade($rubricObj->getPassingGrade());

        $rubricGui->getRubricCard($this->ctrl->getFormAction($this));

    }

    /**
     * Show Rubric Form
     */
    public function showRubricCardForm()
    {
        global $tpl;

		if($this->isAnonymized())
		{
			ilUtil::sendFailure($this->lng->txt('permission_denied'));
			return;
		}
        // bring in GUI and DB objects
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricCard.php");
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricCardGUI.php");

        // instantiate rubric objects
        $rubricGui=new ilLPRubricCardGUI();
        $rubricObj=new ilLPRubricCard($this->getObjId());

        // check to see if rubric data exists for this object, assign data if it does
        if($rubricObj->objHasRubric()){
            $rubricGui->setRubricData($rubricObj->load());
        }
		$rubricGui->setRubricMode($rubricObj->_lookupRubricMode());
        $rubricGui->setPassingGrade($rubricObj->getPassingGrade());
        if($rubricObj->isLocked()){
			$rubricGui->setRubricLocked($rubricObj->getRubricLocked());
			$rubricGui->setRubricOwner($rubricObj->getRubricOwner());
		}
        $rubricGui->getRubricCard($this->ctrl->getFormAction($this));

    }

	public function lockRubricCardForm()
	{
		include_once("./Services/Tracking/classes/rubric/class.ilLPRubricCardGUI.php");
		include_once("./Services/Tracking/classes/rubric/class.ilLPRubricCard.php");
		$rubricObj=new ilLPRubricCard($this->getObjId());
		$rubricObj->lockUnlock();
		if($rubricObj->isLocked())
		{
			$this->saveRubricCard();
		}
		$this->showRubricCardForm();
	}

	public function lockRubricGradeForm()
	{
		include_once("./Services/Tracking/classes/rubric/class.ilLPRubricGradeGUI.php");
		include_once("./Services/Tracking/classes/rubric/class.ilLPRubricGrade.php");
		$rubricObj=new ilLPRubricGrade($this->getObjId());
		$rubricObj=new ilLPRubricGrade($this->getObjId());
		$rubricObj->lockUnlockGrade();
		if($rubricObj->isGradingLocked())
		{
			$passing_grade=$this->saveRubricGrade();
			//only update progress if grading is completed
			if($passing_grade!==false){
				$this->__updateUserRubric($_REQUEST['user_id'], $this->details_obj_id,$passing_grade);
			}
		}
		$this->showRubricGradeForm();
	}



	public function exportPDF()
    {
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricGradeGUI.php");
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricGrade.php");
        $rubricObj=new ilLPRubricGrade($this->getObjId());
        $rubricGui=new ilLPRubricGradeGUI();

		if($rubricObj->objHasRubric()){
			$rubricGui->setRubricData($rubricObj->load());
			$html = $rubricGui->getPDFViewHTML($this->getObjId());
            $html = self::removeScriptElements($html);

            $css = '<style>
					.ilHeaderDesc
					{
						display:block;
						text-align:center;

					}

                    table
                    {
                        table-layout: fixed;
                    }

                    td
                    {
                        padding: 10px;
                        border: 1px solid grey;
                    }
                    tr
                    {
                        padding: 10px;
                        border: 1px solid grey;
                    }
                    th
                    {
                        padding: 10px;
                        border: 1px solid grey;
                    }
                    </style>';

			self::generatePDF($css.$html, 'D', 'rubric');
		}
    }

    public static function generatePDF($pdf_output, $output_mode, $filename=null)
    {
        require_once './Services/PDFGeneration/classes/class.ilPDFGeneration.php';
		ob_clean();
        define ('PDF_PAGE_ORIENTATION', 'L');

        if (substr($filename, strlen($filename) - 4, 4) != '.pdf')
        {
            $filename .= '.pdf';
        }
        $job = new ilPDFGenerationJob();
        $job->setAutoPageBreak(true)
            ->setCreator('rubric')
            ->setFilename($filename)
            ->setMarginLeft('20')
            ->setMarginRight('20')
            ->setMarginTop('20')
            ->setMarginBottom('20')
            ->setOutputMode($output_mode)
            ->addPage($pdf_output);
        ilPDFGeneration::doJob($job);
    }

    /**
     * @param $html
     * @return string
     */
    private static function removeScriptElements($html)
    {
        if(!is_string($html) || !strlen(trim($html)))
        {
            return $html;
        }
        $dom = new DOMDocument("1.0", "utf-8");
        if(!@$dom->loadHTML('<?xml encoding="UTF-8">' . $html))
        {
            return $html;
        }
        $invalid_elements = array();
        $script_elements     = $dom->getElementsByTagName('script');
        foreach($script_elements as $elm)
        {
            $invalid_elements[] = $elm;
        }
        foreach($invalid_elements as $elm)
        {
            $elm->parentNode->removeChild($elm);
        }
        $dom->encoding = 'UTF-8';
        $cleaned_html = $dom->saveHTML();
        if(!$cleaned_html)
        {
            return $html;
        }
        return $cleaned_html;
    }

    // END PATCH RUBRIC CPKN 2015
}
?>