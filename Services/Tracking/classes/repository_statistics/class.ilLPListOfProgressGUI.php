<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilLPListOfProgress
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
* @version $Id: class.ilLPListOfProgressGUI.php 57460 2015-01-26 11:37:42Z jluetzen $
*
* @ilCtrl_Calls ilLPListOfProgressGUI: ilLPProgressTableGUI
*
* @package ilias-tracking
*
*/

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';
require_once './Modules/OrgUnit/classes/class.ilObjOrgUnitAccess.php';

class ilLPListOfProgressGUI extends ilLearningProgressBaseGUI
{
	var $tracked_user = null;
	var $details_id = 0;
	var $details_type = '';
	var $details_mode = 0;

	function ilLPListOfProgressGUI($a_mode,$a_ref_id,$a_user_id = 0)
	{
		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id,$a_user_id);
		$this->__initUser($a_user_id);
		
		// Set item id for details
		$this->__initDetails((int) $_GET['details_id']);
		$this->ctrl->saveParameter($this,'details_id',$_REQUEST['details_id']);
	}
		

	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $ilUser;
		
		$this->ctrl->setReturn($this, "show");
		$this->ctrl->saveParameter($this,'user_id',$this->getUserId());
		switch($this->ctrl->getNextClass())
		{
			case 'illpprogresstablegui':
				include_once './Services/Tracking/classes/repository_statistics/class.ilLPProgressTableGUI.php';
			    $table_gui = new ilLPProgressTableGUI($this, "", $this->tracked_user);
				$this->ctrl->setReturn($this,'show');
				$this->ctrl->forwardCommand($table_gui);
				break;

			default:
				$cmd = $this->__getDefaultCommand();
				$this->$cmd();

		}
		return true;
	}

	function show()
	{
		global $ilObjDataCache;

		switch($this->getMode())
		{
			// Show only detail of current repository item if called from repository
			case self::LP_CONTEXT_REPOSITORY:
				$this->__initDetails($this->getRefId());
				return $this->details();

			case self::LP_CONTEXT_USER_FOLDER:
			case self::LP_CONTEXT_ORG_UNIT:
				// if called from user folder obj_id is id of current user
				$this->__initUser($this->getUserId());
				break;
		}

		// not called from repository
 		$this->__showProgressList();
	}

	function details()
	{
		global $ilToolbar,$ilCtrl,$rbacsystem;

		// Show back button to crs if called from crs. Otherwise if called from personal desktop or administration
		// show back to list
		if((int)$_GET['crs_id'])
		{
			$this->ctrl->setParameter($this,'details_id',(int) $_GET['crs_id']);
			
			$ilToolbar->addButton($this->lng->txt('trac_view_crs'),
				$this->ctrl->getLinkTarget($this,'details'));			
		}
		elseif($this->getMode() == self::LP_CONTEXT_PERSONAL_DESKTOP or
			   $this->getMode() == self::LP_CONTEXT_ADMINISTRATION or
			   $this->getMode() == self::LP_CONTEXT_USER_FOLDER)
		{
			$ilToolbar->addButton($this->lng->txt('trac_view_list'),
				$this->ctrl->getLinkTarget($this,'show'));				
		}

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_progress_container.html','Services/Tracking');

		$olp = ilObjectLP::getInstance($this->details_obj_id);
		$collection = $olp->getCollectionInstance();


		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($ilCtrl->getFormAction($this));
		$this->__appendUserInfo($info, $this->tracked_user);
		$this->__showObjectDetails($info,$this->details_obj_id, false);

		// START PATCH RUBRIC CPKN 2016
		include_once('./Services/Tracking/classes/rubric/class.ilLPRubricGrade.php');
		include_once('./Services/Tracking/classes/rubric/class.ilLPRubricGradeGUI.php');

		//if the user is viewing history show the old status/mark/etc.
		if($olp->getCurrentMode()==92 && $_REQUEST['grader_history'] !== 'current'
				&& !is_null($_REQUEST['grader_history'])){
			$marks = ilLPRubricGrade::_lookupRubricHistoryLP($_REQUEST['grader_history']);
			include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");

			$status_path = ilLearningProgressBaseGUI::_getImagePathForStatus($marks['status']);
			$status_text = ilLearningProgressBaseGUI::_getStatusText($marks['status']);

			$info->addSection($this->lng->txt("trac_progress").": ".ilObject::_lookupTitle($this->details_obj_id));
			$info->addProperty($this->lng->txt('trac_mode'),
					$olp->getModeText($olp->getCurrentMode()));	$info->addProperty($this->lng->txt('trac_status'),
					ilUtil::img($status_path, $status_text)." ".$status_text);
			$info->addProperty($this->lng->txt('trac_mark'),$marks['mark']);
			$info->addProperty($this->lng->txt('trac_comment'),$marks['comments']);

		}else{
			$this->__appendLPDetails($info,$this->details_obj_id,$this->tracked_user->getId());
		}
		// END PATCH RUBRIC CPKN 2016

		// Finally set template variable
		$this->tpl->setVariable("LM_INFO",$info->getHTML());
		
		include_once './Services/Object/classes/class.ilObjectLP.php';

		if($collection)
		{			
			$obj_ids = array();
			foreach($collection->getItems() as $item_id)
			{
				if($collection instanceof ilLPCollectionOfRepositoryObjects)
				{
					$obj_ids[ilObject::_lookupObjectId($item_id)] = array($item_id);
					
				}
				else
				{
					$obj_ids[] = $item_id;
				}				
			}
		}
		
		// #15247
		if(sizeof($obj_ids))
		{
			// seems obsolete
			$personal_only = !$rbacsystem->checkAccess('read_learning_progress',$this->getRefId());

			include_once("./Services/Tracking/classes/repository_statistics/class.ilLPProgressTableGUI.php");
			$lp_table = new ilLPProgressTableGUI($this, "details", $this->tracked_user, $obj_ids, true, $this->details_mode, $personal_only, $this->details_obj_id, $this->details_id);
			$this->tpl->setVariable("LP_OBJECTS", $lp_table->getHTML());
		}
		
		$this->tpl->setVariable("LEGEND",$this->__getLegendHTML());
        
        // START PATCH RUBRIC CPKN 2015
        if($olp->getCurrentMode()==ilLPObjSettings::LP_MODE_RUBRIC)
        {
            $rubricObj=new ilLPRubricGrade($this->getObjId());
            $rubricGui=new ilLPRubricGradeGUI();
            
            $a_user = ilObjectFactory::getInstanceByObjId($_SESSION['AccountId']);
            if($rubricObj->objHasRubric()&&$rubricObj->isRubricComplete()){
				$rubricGui->setUserHistoryId($_REQUEST['grader_history']);
				$rubricGui->setUserHistory($rubricObj->getUserHistory($_SESSION['AccountId']));
                $rubricGui->setRubricData($rubricObj->load());
                $rubricGui->setUserData($rubricObj->getRubricUserGradeData($_SESSION['AccountId'],$_REQUEST['grader_history']));
                $this->tpl->setVariable("LP_OBJECTS", $rubricGui->getStudentViewHTML($this->ctrl->getFormAction($this), $a_user->getFullname(), (int)$_GET['user_id']));
            }
            
        }
        // END PATCH RUBRIC CPKN 2015
	}

	// START PATCH RUBRIC CPKN 2016
	function viewHistory()
	{
		$this->ctrl->setParameter($this,'grader_history',$_POST['grader_history']);
		$this->ctrl->redirect($this,'details');
	}

	// END PATCH RUBRIC CPKN 2016

	function __showProgressList()
	{
		global $ilUser,$ilObjDataCache,$ilCtrl;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_list_progress.html','Services/Tracking');
		
		// User info
		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		$info->setFormAction($ilCtrl->getFormAction($this));
		
		if ($this->__appendUserInfo($info, $this->tracked_user))
		{
			$this->tpl->setCurrentBlock("info_user");
			$this->tpl->setVariable("USER_INFO",$info->getHTML());
			$this->tpl->parseCurrentBlock();
		}

		include_once("./Services/Tracking/classes/repository_statistics/class.ilLPProgressTableGUI.php");
		$lp_table = new ilLPProgressTableGUI($this, "", $this->tracked_user,null,false,null,false,null,null,$this->getMode());
		$this->tpl->setVariable("LP_OBJECTS", $lp_table->getHTML());

		$this->tpl->setVariable("LEGEND", $this->__getLegendHTML());
	}

	function __initUser($a_usr_id = 0)
	{
		global $ilUser,$rbacreview,$rbacsystem;

		if($_POST['user_id'])
		{
			$a_usr_id = $_POST['user_id'];
			$this->ctrl->setParameter($this,'user_id',$_POST['user_id']);
		}

		if($a_usr_id)
		{
			$this->tracked_user = ilObjectFactory::getInstanceByObjId($a_usr_id);
		}
		else
		{
			$this->tracked_user = $ilUser;
		}
		
		// #8762: see ilObjUserGUI->getTabs()
		if($this->mode == self::LP_CONTEXT_USER_FOLDER && $rbacsystem->checkAccess('read',$this->ref_id))
		{
			return true;
		}

		if($this->mode == self::LP_CONTEXT_ORG_UNIT && ilObjOrgUnitAccess::_checkAccessToUserLearningProgress($this->ref_id,$a_usr_id))
		{
			return true;
		}

		// Check access
		if(!$rbacreview->isAssigned($ilUser->getId(),SYSTEM_ROLE_ID))
		{
			$this->tracked_user = $ilUser;
		}
		
		return true;
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
			$ref_ids = ilObject::_getAllReferences($a_details_id);
			
			$this->details_id = $a_details_id;
			$this->details_obj_id = $ilObjDataCache->lookupObjId($this->details_id);
			$this->details_type = $ilObjDataCache->lookupType($this->details_obj_id);
						
			include_once 'Services/Object/classes/class.ilObjectLP.php';
			$olp = ilObjectLP::getInstance($this->details_obj_id);													
			$this->details_mode = $olp->getCurrentMode();
		}
	}

    // START PATCH RUBRIC CPKN 2015

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