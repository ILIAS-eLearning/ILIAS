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
            
            $this->saveRubricGrade();
                        
            $this->__updateUserRubric($_REQUEST['user_id'], $this->details_obj_id);
            
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
    
    		$this->tpl->setVariable("ADM_CONTENT", $this->__showEditUser((int)$_GET['user_id'], $parent_id, $cancel, $sub_id)."<br />$lp_mode".$info->getHTML());
            
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
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricCard.php");
        $rubricObj=new ilLPRubricCard($this->getObjId());
        
        if($rubricObj->objHasRubric()){
            $rubricObj->grade($rubricObj->load());
            ilUtil::sendSuccess($this->lng->txt('rubric_card_save'));
        }else{
            ilUtil::sendFailure($this->lng->txt('rubric_card_not_defined'));                
        }
        
    }
    /**
     *  Show Rubric Grade
     */
    public function showRubricGradeForm()
    {
        
        include_once('./Services/Tracking/classes/rubric/class.ilLPRubricCard.php');
        include_once('./Services/Tracking/classes/rubric/class.ilLPRubricCardGUI.php');
        
        $rubricObj=new ilLPRubricCard($this->getObjId());
        $rubricGui=new ilLPRubricCardGUI();
        
        $a_user = ilObjectFactory::getInstanceByObjId((int)$_GET['user_id']);
        
        if($rubricObj->objHasRubric()){            
            $rubricGui->setRubricData($rubricObj->load());
            $rubricGui->setUserData($rubricObj->getRubricUserGradeData((int)$_GET['user_id']));            
            $rubricGui->getRubricGrade(
                $this->ctrl->getFormAction($this),
                $a_user->getFullName(),
                (int)$_GET['user_id']
            );
        }else{
            ilUtil::sendFailure($this->lng->txt('rubric_card_not_defined'));                
        }
        
    }
     
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
                
        $rubricGui->getRubricCard($this->ctrl->getFormAction($this));
        
        //$obj_lp = ilObjectLP::getInstance($this->getObjId());
        //$rubric=new ilLPRubricCardTableGUI($this,'show',$this->getRefId(),$obj_lp->getCurrentMode());
        
        
        
        //$rubric=new ilLPRubricCardTableGUI($this,'show',$this->getRefId());
        
        //$rubric->setTableTitle('Rubric Card');
        
        //$tpl->setContent($rubric->getHTML());
        
        // create the title
        //$tpl->setContent($);
        
        // create the command row
        
        // create the rubric form
        /*
        include_once './Services/Object/classes/class.ilObjectLP.php';
		$obj_lp = ilObjectLP::getInstance($this->getObjId());
                
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        
        // command row
        $rubric_commandrow_tpl=new ilTemplate('tpl.lp_rubricform_commandrow.html',true,true,'Services/Tracking');        
        $select_prop=new ilSelectInputGUI('Title','rubric_commands');
        $options=array(                      
            'behavior_2'=>$this->lng->txt('rubric_option_behavior_2'),
            'behavior_3'=>$this->lng->txt('rubric_option_behavior_3'),
            'behavior_4'=>$this->lng->txt('rubric_option_behavior_4'),
            'behavior_5'=>$this->lng->txt('rubric_option_behavior_5'),
            'behavior_6'=>$this->lng->txt('rubric_option_behavior_6'),
            'add_group'=>$this->lng->txt('rubric_option_add_group'),
            'add_criteria'=>$this->lng->txt('rubric_option_add_criteria'),
            'del_criteria'=>$this->lng->txt('rubric_option_del_criteria'),            
        );
        $select_prop->setOptions($options);        
        $rubric_commandrow_tpl->setVariable('RURBRIC_COMMANDROW_SELECT',$select_prop->render());        
        //$tpl->setContent();
        
        // table
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricCardTableGUI.php");
        $tbl = new ilLPRubricCardTableGUI($this, 'show', $this->getRefId());//, $obj_lp->getCurrentMode()
        $tpl->setContent($rubric_commandrow_tpl->get().$tbl->getHTML());//$form->getHTML()
        */
        
        /*include_once './Services/Table/classes/class.ilTable2GUI.php';
        $tbl=new ilTable2GUI($this,'show',$this->getRefId());
        $tbl->addColumn('my test','',1);
        $tpl->setContent($tbl->getHTML());*/
        
        /*
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        
        $text_prop = new ilTextInputGUI($this->lng->txt("rubric_label"), "label0");
        $text_prop->setInfo($this->lng->txt("rubric_point"));
        $text_prop->setValue('1234');
        //$tpl->setContent($text_prop->getHTML());
        //$form->addItem($text_prop);
        //$form->add
        $form->addCommandButton("saveRubricCard", $this->lng->txt("save"));
        $form->addCommandButton("view", $this->lng->txt("cancel"));
        
        $tpl->setContent($form->getHTML());
        
        include_once("./Services/Tracking/classes/rubric/class.ilLPRubricCardTableGUI.php");
        $tbl = new ilLPRubricCardTableGUI($this, 'show', $this->getRefId());//, $obj_lp->getCurrentMode()
        $tpl->setContent($form->getHTML().$tbl->getHTML());
        */
		//$tbl->parse($collection);
		
        //$tbl=new ilLPRubricCardTableGUI();
        
        //$tpl->setContent($tbl->getHTML());
        
        
        
        /*
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        
        $text_prop = new ilTextInputGUI($this->lng->txt("rubric_label"), "label0");
        $text_prop->setInfo($this->lng->txt("rubric_point"));
        $text_prop->setValue('1234');
        $form->addItem($text_prop);
        
        $text_prop = new ilTextInputGUI($this->lng->txt("rubric_label"), "label1");
        $text_prop->setInfo($this->lng->txt("rubric_point"));
        $text_prop->setValue('5678');
        $form->addItem($text_prop);
        
        $input_prop=new ilCheckboxGroupInputGUI('hmm1','hmm2');
        $input_prop->setOptions(array('a'=>'aa','b'=>'bb'));
        //$input_prop->addOption();
        $form->addItem($input_prop);
         
        $form->addCommandButton("saveRubricCard", $this->lng->txt("save"));
        $form->addCommandButton("view", $this->lng->txt("cancel"));

        
        
        
        $rubric_tpl=new ilTemplate('tpl.lp_rubricform.html',true,true,'Services/Tracking');
        $rubric_tpl->setVariable('TEST','Blah blah');
        //$tpl->setCurrentBlock("rubric_form");
        //$ilPropertyFormGUI 
        
        //$html=$rubric_tpl->get();
        //$tpl->setContent($html);
        $tpl->setContent($form->getHTML());
        */
        /*
        $language_variables=array(
            'rubric_total',
            'rubric_label',
            'rubric_point',
            'rubric_group',
            'rubric_criteria',
            'rubric_behavior',
            'rubric_label_excellent',
            'rubric_label_good',
            'rubric_label_acceptable',
            'rubric_label_fair',
            'rubric_label_poor',
            'rubric_label_bad',          
            'rubric_option_behavior_2',
            'rubric_option_behavior_3',
            'rubric_option_behavior_4',
            'rubric_option_behavior_5',
            'rubric_option_behavior_6',
            'rubric_option_add_group',
            'rubric_option_add_criteria',
            'rubric_option_del_criteria',
            'rubric_execute',
            'rubric_total',
        );
        
        foreach($language_variables as $variable){
            $this->tpl->setVariable(strtoupper($variable),$this->lng->txt($variable));
        }
        */
        
		
        
        //$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_rubricform.html','Services/Tracking');
        
        
        //$this->tpl->setVariable("LABEL",$this->lng->txt("rubric_label"));
        //$this->tpl->setVariable("POINT",$this->lng->txt("rubric_point"));
        
        
        //include_once("./Services/Tracking/classes/repository_statistics/class.ilLPRubricCardTableGUI.php");
        
        //$table = new ilLPRubricCardTableGUI($this,"showRubricCardForm",$this->getRefId());
        //$this->tpl->setVariable('TEST',array('hi'));
        //$this->tpl->setVariable();
        
        /*
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
        */
        
    }
    // END PATCH RUBRIC CPKN 2015
}
?>