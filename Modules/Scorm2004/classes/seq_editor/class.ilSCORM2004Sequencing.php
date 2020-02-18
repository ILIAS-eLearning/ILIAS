<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilSCORM2004Sequencing
*
* Sequencing Template class for SCORM 2004 Editing
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ModulesScorm2004
*/

require_once("./Modules/Scorm2004/classes/seq_editor/class.ilSCORM2004Item.php");


class ilSCORM2004Sequencing extends ilSCORM2004Item
{


// **********************
    // ATTRIBUTE DECLARATION
    // **********************

    public $activityAbsoluteDurationLimit = 0 ;
    public $activityExperiencedDurationLimit = 0;
    public $attemptAbsoluteDurationLimit = 0;
    public $attemptExperiencedDurationLimit = 0;
    public $attemptLimit= 0;
    public $beginTimeLimit = 0 ;
    public $choice = true;
    public $choiceExit = true;
    public $completionSetByContent = false;
    public $constrainChoice = false;
    public $seqNodeId = null;
    public $endTimeLimit = null ;
    public $flow = false;
    public $forwardOnly = false;
    public $id = 0;
    public $measureSatisfactionIfActive = true;
    public $objectiveMeasureWeight =1.0;
    public $objectiveSetByContent = false;
    public $preventActivation = false;
    public $randomizationTiming = "never";
    public $reorderChildren = false;
    public $requiredForCompleted = "always";
    public $requiredForIncomplete = "always";
    public $requiredForNotSatisfied ="always";
    public $requiredForSatisfied ="always";
    public $rollupObjectiveSatisfied = true;
    public $rollupProgressCompletion = true;
    public $selectCount = 0;
    public $selectionTiming = false;
    public $sequencingId = null;
    public $tracked = true;
    public $useCurrentAttemptObjectiveInfo = true;
    public $useCurrentAttemptProgressInfo = true;
    public $importid = null;
    public $node = null;

    // **********************
    // CONSTRUCTOR METHOD
    // **********************

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_treeid = null, $a_rootlevel=false)
    {
        parent::__construct($a_treeid, $a_rootlevel);
        
        if ($a_treeid != null) {
            $xpath_obj = new DOMXPath($this->dom);
            $obj_node_list = $xpath_obj->query('//controlMode');
            $this->setNode($obj_node_list->item(0));
            if ($obj_node_list->length!=1) {
                $obj_con = $this->dom->createElement("controlMode");
                $root = $this->dom->getElementsByTagName("sequencing")->item(0);
                $root->appendChild($obj_con);
                $this->node =$this->dom->getElementsByTagName("controlMode")->item(0);
                $this->setFlow(true);
                $this->setChoice(true);
                $this->setForwardOnly(false);
            }
        }
    }


    // **********************
    // GETTER METHODS
    // **********************


    public function getActivityAbsoluteDurationLimit()
    {
        return $this->activityAbsoluteDurationLimit;
    }

    public function getActivityExperiencedDurationLimit()
    {
        return $this->activityExperiencedDurationLimit;
    }

    public function getAttemptAbsoluteDurationLimit()
    {
        return $this->attemptAbsoluteDurationLimit;
    }

    public function getAttemptExperiencedDurationLimit()
    {
        return $this->attemptExperiencedDurationLimit;
    }

    public function getAttemptLimit()
    {
        return $this->attemptLimit;
    }

    public function getBeginTimeLimit()
    {
        return $this->beginTimeLimit;
    }

    public function getChoice()
    {
        return self::convertStringToBool($this->node->getAttribute("choice"));
    }

    public function getChoiceExit()
    {
        return self::convertStringToBool($this->node->getAttribute("choiceExit"));
    }

    public function getCompletionSetByContent()
    {
        return $this->completionSetByContent;
    }

    public function getConstrainChoice()
    {
        return $this->constrainChoice;
    }

    public function getSeqNodeId()
    {
        return $this->seqNodeId;
    }

    public function getEndTimeLimit()
    {
        return $this->endTimeLimit;
    }

    public function getFlow()
    {
        return self::convertStringToBool($this->node->getAttribute("flow"));
    }

    public function getForwardOnly()
    {
        return self::convertStringToBool($this->node->getAttribute("forwardOnly"));
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMeasureSatisfactionIfActive()
    {
        return $this->measureSatisfactionIfActive;
    }

    public function getObjectiveMeasureWeight()
    {
        return $this->objectiveMeasureWeight;
    }

    public function getObjectiveSetByContent()
    {
        return $this->objectiveSetByContent;
    }

    public function getPreventActivation()
    {
        return $this->preventActivation;
    }

    public function getRandomizationTiming()
    {
        return $this->randomizationTiming;
    }

    public function getReorderChildren()
    {
        return $this->reorderChildren;
    }

    public function getRequiredForCompleted()
    {
        return $this->requiredForCompleted;
    }

    public function getRequiredForIncomplete()
    {
        return $this->requiredForIncomplete;
    }

    public function getRequiredForNotSatisfied()
    {
        return $this->requiredForNotSatisfied;
    }

    public function getRequiredForSatisfied()
    {
        return $this->requiredForSatisfied;
    }

    public function getRollupObjectiveSatisfied()
    {
        return $this->rollupObjectiveSatisfied;
    }

    public function getRollupProgressCompletion()
    {
        return $this->rollupProgressCompletion;
    }

    public function getSelectCount()
    {
        return $this->selectCount;
    }

    public function getSelectionTiming()
    {
        return $this->selectionTiming;
    }

    public function getSequencingId()
    {
        return $this->sequencingId;
    }

    public function getTracked()
    {
        return $this->tracked;
    }

    public function getUseCurrentAttemptObjectiveInfo()
    {
        return $this->useCurrentAttemptObjectiveInfo;
    }

    public function getUseCurrentAttemptProgressInfo()
    {
        return $this->useCurrentAttemptProgressInfo;
    }

    // **********************
    // SETTER METHODS
    // **********************


    public function setActivityAbsoluteDurationLimit($val)
    {
        $this->activityAbsoluteDurationLimit =  $val;
    }

    public function setActivityExperiencedDurationLimit($val)
    {
        $this->activityExperiencedDurationLimit =  $val;
    }

    public function setAttemptAbsoluteDurationLimit($val)
    {
        $this->attemptAbsoluteDurationLimit =  $val;
    }

    public function setAttemptExperiencedDurationLimit($val)
    {
        $this->attemptExperiencedDurationLimit =  $val;
    }

    public function setAttemptLimit($val)
    {
        $this->attemptLimit =  $val;
    }

    public function setBeginTimeLimit($val)
    {
        $this->beginTimeLimit =  $val;
    }

    public function setChoice($a_choice)
    {
        $this->node->setAttribute("choice", $a_choice ? "true": "false");
    }

    public function setChoiceExit($a_choicexit)
    {
        $this->node->setAttribute("choiceExit", $a_choicexit  ? "true": "false");
    }

    public function setCompletionSetByContent($val)
    {
        $this->completionSetByContent =  $val;
    }

    public function setConstrainChoice($val)
    {
        $this->constrainChoice =  $val;
    }

    public function setSeqNodeId($a_seqnodeid)
    {
        $this->seqNodeId = $a_seqnodeid;
    }

    public function setEndTimeLimit($val)
    {
        $this->endTimeLimit =  $val;
    }

    public function setFlow($a_flow)
    {
        $this->node->setAttribute("flow", $a_flow  ? "true": "false");
    }

    public function setForwardOnly($a_forwardonly)
    {
        $this->node->setAttribute("forwardOnly", $a_forwardonly  ? "true": "false");
    }

    public function setId($val)
    {
        $this->id =  $val;
    }

    public function setMeasureSatisfactionIfActive($val)
    {
        $this->measureSatisfactionIfActive =  $val;
    }

    public function setObjectiveMeasureWeight($val)
    {
        $this->objectiveMeasureWeight =  $val;
    }

    public function setObjectiveSetByContent($val)
    {
        $this->objectiveSetByContent =  $val;
    }

    public function setPreventActivation($val)
    {
        $this->preventActivation =  $val;
    }

    public function setRandomizationTiming($val)
    {
        $this->randomizationTiming =  $val;
    }

    public function setReorderChildren($val)
    {
        $this->reorderChildren =  $val;
    }

    public function setRequiredForCompleted($val)
    {
        $this->requiredForCompleted =  $val;
    }

    public function setRequiredForIncomplete($val)
    {
        $this->requiredForIncomplete =  $val;
    }

    public function setRequiredForNotSatisfied($val)
    {
        $this->requiredForNotSatisfied =  $val;
    }

    public function setRequiredForSatisfied($val)
    {
        $this->requiredForSatisfied =  $val;
    }

    public function setRollupObjectiveSatisfied($val)
    {
        $this->rollupObjectiveSatisfied =  $val;
    }

    public function setRollupProgressCompletion($val)
    {
        $this->rollupProgressCompletion =  $val;
    }

    public function setSelectCount($val)
    {
        $this->selectCount =  $val;
    }

    public function setSelectionTiming($val)
    {
        $this->selectionTiming =  $val;
    }

    public function setSequencingId($val)
    {
        $this->sequencingId =  $val;
    }

    public function setTracked($val)
    {
        $this->tracked =  $val;
    }

    public function setUseCurrentAttemptObjectiveInfo($val)
    {
        $this->useCurrentAttemptObjectiveInfo =  $val;
    }

    public function setUseCurrentAttemptProgressInfo($val)
    {
        $this->useCurrentAttemptProgressInfo =  $val;
    }


    public function setImportid($a_importid)
    {
        $this->importid = $a_importid;
    }


    public function setNode($a_node)
    {
        $this->node = $a_node;
    }

    public function setDom($a_dom)
    {
        $this->dom = $a_dom;
    }

    //helper
    private static function convertStringToBool($a_string)
    {
        switch ($a_string) {
        case 'true':
            return true;
            break;

        case 'false':
            return false;
            break;
        
        default:
            # code...
            break;
    }
    }
} // class : end
