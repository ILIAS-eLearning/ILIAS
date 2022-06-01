<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilQuestionEditGUI
*
* @author		Alex Killing <alex.killing@gmx.de>
* @version  $Id$
*
* @ilCtrl_Calls ilQuestionEditGUI: assMultipleChoiceGUI, assClozeTestGUI, assMatchingQuestionGUI, assKprimChoiceGUI
* @ilCtrl_Calls ilQuestionEditGUI: assOrderingQuestionGUI, assImagemapQuestionGUI
* @ilCtrl_Calls ilQuestionEditGUI: assNumericGUI, assTextSubsetGUI, assSingleChoiceGUI, assTextQuestionGUI
* @ilCtrl_Calls ilQuestionEditGUI: assErrorTextGUI, assOrderingHorizontalGUI, assTextSubsetGUI, assFormulaQuestionGUI
* @ilCtrl_Calls ilQuestionEditGUI: assLongMenuGUI
*
* @ingroup ModulesTestQuestionPool
*/
class ilQuestionEditGUI
{
    private \ilGlobalTemplateInterface $main_tpl;
    private \ILIAS\TestQuestionPool\InternalRequestService $request;

    /**
    * Constructor
    */
    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $ilCtrl = $DIC['ilCtrl'];
        $this->request = $DIC->testQuestionPool()->internal()->request();
        $lng = $DIC['lng'];

        if ($this->request->raw("qpool_ref_id")) {
            $this->setPoolRefId($this->request->raw("qpool_ref_id"));
        } elseif ($this->request->raw("qpool_obj_id")) {
            $this->setPoolObjId($this->request->raw("qpool_obj_id"));
        }
        $this->setQuestionId($this->request->getQuestionId());
        $this->setQuestionType($this->request->raw("q_type"));
        $lng->loadLanguageModule("assessment");
        
        $ilCtrl->saveParameter($this, array("qpool_ref_id", "qpool_obj_id", "q_id", "q_type"));
        
        $this->new_id_listeners = array();
        $this->new_id_listener_cnt = 0;
    }
    
    /**
    * Set Self-Assessment Editing Mode.
    *
    * @param	boolean	$a_selfassessmenteditingmode	Self-Assessment Editing Mode
    */
    public function setSelfAssessmentEditingMode($a_selfassessmenteditingmode) : void
    {
        $this->selfassessmenteditingmode = $a_selfassessmenteditingmode;
    }

    /**
    * Get Self-Assessment Editing Mode.
    *
    * @return	boolean	Self-Assessment Editing Mode
    */
    public function getSelfAssessmentEditingMode() : bool
    {
        return $this->selfassessmenteditingmode;
    }
    
    /**
    * Set  Default Nr of Tries
    *
    * @param	int	$a_defaultnroftries		Default Nr. of Tries
    */
    public function setDefaultNrOfTries($a_defaultnroftries) : void
    {
        $this->defaultnroftries = $a_defaultnroftries;
    }
    
    /**
    * Get Default Nr of Tries
    *
    * @return	int	Default Nr of Tries
    */
    public function getDefaultNrOfTries() : int
    {
        return $this->defaultnroftries;
    }

    /**
     * Set Page Config
     *
     * @param	object	Page Config
     */
    public function setPageConfig($a_val) : void
    {
        $this->page_config = $a_val;
    }

    /**
     * Get Page Config
     *
     * @return	object	Page Config
     */
    public function getPageConfig() : object
    {
        return $this->page_config;
    }


    /**
    * Add a listener that is notified with the new question ID, when
    * a new question is saved
    */
    public function addNewIdListener(&$a_object, $a_method, $a_parameters = "") : void
    {
        $cnt = $this->new_id_listener_cnt;
        $this->new_id_listeners[$cnt]["object"] = &$a_object;
        $this->new_id_listeners[$cnt]["method"] = $a_method;
        $this->new_id_listeners[$cnt]["parameters"] = $a_parameters;
        $this->new_id_listener_cnt++;
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        $cmd = $ilCtrl->getCmd();
        $next_class = $ilCtrl->getNextClass();
        
        //echo "-".$cmd."-".$next_class."-".$this->request->getQuestionId()."-";
        
        switch ($next_class) {
            default:
                include_once "./Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
                $q_gui = assQuestionGUI::_getQuestionGUI(
                    $this->getQuestionType(),
                    $this->getQuestionId()
                );
                $q_gui->object->setSelfAssessmentEditingMode(
                    $this->getSelfAssessmentEditingMode()
                );
                $q_gui->object->setDefaultNrOfTries(
                    $this->getDefaultNrOfTries()
                );

                if (is_object($this->page_config)) {
                    $q_gui->object->setPreventRteUsage($this->getPageConfig()->getPreventRteUsage());
                }
                $q_gui->object->setObjId($this->getPoolObjId());
                
                for ($i = 0; $i < $this->new_id_listener_cnt; $i++) {
                    $object = $this->new_id_listeners[$i]["object"];
                    $method = $this->new_id_listeners[$i]["method"];
                    $parameters = $this->new_id_listeners[$i]["parameters"];
                    $q_gui->addNewIdListener(
                        $object,
                        $method,
                        $parameters
                    );
                    //var_dump($object);
//var_dump($method);
//var_dump($parameters);
                }

                //$q_gui->setQuestionTabs();
                $count = $q_gui->object->usageNumber();
                if ($count > 0) {
                    global $DIC;
                    $rbacsystem = $DIC['rbacsystem'];
                    if ($rbacsystem->checkAccess("write", $this->getPoolRefId())) {
                        $this->main_tpl->setOnScreenMessage('info', sprintf($lng->txt("qpl_question_is_in_use"), $count));
                    }
                }
                $ilCtrl->setCmdClass(get_class($q_gui));
                $ret = $ilCtrl->forwardCommand($q_gui);
                break;
        }
        
        return $ret;
    }
    
    /**
    * Set Question Id.
    *
    * @param	int	$a_questionid	Question Id
    */
    public function setQuestionId($a_questionid) : void
    {
        $this->questionid = $a_questionid;
        $_GET["q_id"] = $this->questionid; // TODO / TATS: How to address this?
    }

    /**
    * Get Question Id.
    *
    * @return	int	Question Id
    */
    public function getQuestionId() : int
    {
        return $this->questionid;
    }

    /**
    * Set Pool Ref ID.
    *
    * @param	int	$a_poolrefid	Pool Ref ID
    */
    public function setPoolRefId($a_poolrefid) : void
    {
        //echo "<br>Setting Pool Ref ID:".$a_poolrefid;
        $this->poolrefid = $a_poolrefid;
        $_GET["qpool_ref_id"] = $this->poolrefid;
        $this->setPoolObjId(ilObject::_lookupObjId($this->getPoolRefId()));
    }

    /**
    * Get Pool Ref ID.
    *
    * @return	int	Pool Ref ID
    */
    public function getPoolRefId() : int
    {
        return $this->poolrefid;
    }

    /**
    * Set Pool Obj Id.
    *
    * @param	int	$a_poolobjid	Pool Obj Id
    */
    public function setPoolObjId($a_poolobjid) : void
    {
        //echo "<br>Setting Pool Obj ID:".$a_poolobjid;
        $this->poolobjid = $a_poolobjid;
        $_GET["qpool_obj_id"] = $this->poolobjid;
    }

    /**
    * Get Pool Obj Id.
    *
    * @return	int	Pool Obj Id
    */
    public function getPoolObjId() : int
    {
        return $this->poolobjid;
    }

    /**
    * Set Question Type.
    *
    * @param	string	$a_questiontype	Question Type
    */
    public function setQuestionType($a_questiontype) : void
    {
        $this->questiontype = $a_questiontype;
        $_GET["q_type"] = $this->questiontype;
    }

    /**
    * Get Question Type.
    *
    * @return	string	Question Type
    */
    public function getQuestionType() : string
    {
        return $this->questiontype;
    }
}
