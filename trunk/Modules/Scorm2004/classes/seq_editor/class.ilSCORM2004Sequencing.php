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

var $activityAbsoluteDurationLimit = 0 ;   
var $activityExperiencedDurationLimit = 0;    
var $attemptAbsoluteDurationLimit = 0;   
var $attemptExperiencedDurationLimit = 0;   
var $attemptLimit= 0;   
var $beginTimeLimit = 0 ;   
var $choice = true;   
var $choiceExit = true;   
var $completionSetByContent = false;   
var $constrainChoice = false;   
var $seqNodeId = null;
var $endTimeLimit = null ;   
var $flow = false;   
var $forwardOnly = false;   
var $id = 0;   
var $measureSatisfactionIfActive = true;   
var $objectiveMeasureWeight =1.0;   
var $objectiveSetByContent = false;   
var $preventActivation = false;   
var $randomizationTiming = "never";   
var $reorderChildren = false;   
var $requiredForCompleted = "always";   
var $requiredForIncomplete = "always";   
var $requiredForNotSatisfied ="always";   
var $requiredForSatisfied ="always";   
var $rollupObjectiveSatisfied = true;   
var $rollupProgressCompletion = true;   
var $selectCount = 0;   
var $selectionTiming = false;   
var $sequencingId = null;   
var $tracked = true;   
var $useCurrentAttemptObjectiveInfo = true;   
var $useCurrentAttemptProgressInfo = true;   
var $importid = null;
var $node = null;

// **********************
// CONSTRUCTOR METHOD
// **********************

/**
* Constructor
* @access	public
*/
function ilSCORM2004Sequencing($a_treeid = null,$a_rootlevel=false)
{
	parent::ilSCORM2004Item($a_treeid, $a_rootlevel);
		
	if ($a_treeid != null) {
		$xpath_obj = new DOMXPath($this->dom);
		$obj_node_list = $xpath_obj->query('//controlMode');
		$this->setNode($obj_node_list->item(0));
		if ($obj_node_list->length!=1 ) {
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


function getActivityAbsoluteDurationLimit()
{
	return $this->activityAbsoluteDurationLimit;
}

function getActivityExperiencedDurationLimit()
{
	return $this->activityExperiencedDurationLimit;
}

function getAttemptAbsoluteDurationLimit()
{
	return $this->attemptAbsoluteDurationLimit;
}

function getAttemptExperiencedDurationLimit()
{
	return $this->attemptExperiencedDurationLimit;
}

function getAttemptLimit()
{
	return $this->attemptLimit;
}

function getBeginTimeLimit()
{
	return $this->beginTimeLimit;
}

function getChoice()
{
	return self::convertStringToBool($this->node->getAttribute("choice"));
}

function getChoiceExit()
{
	return self::convertStringToBool($this->node->getAttribute("choiceExit"));
}

function getCompletionSetByContent()
{
	return $this->completionSetByContent;
}

function getConstrainChoice()
{
	return $this->constrainChoice;
}

function getSeqNodeId()
{
	return $this->seqNodeId;
}

function getEndTimeLimit()
{
	return $this->endTimeLimit;
}

function getFlow()
{
	return self::convertStringToBool($this->node->getAttribute("flow"));
}

function getForwardOnly()
{
	return self::convertStringToBool($this->node->getAttribute("forwardOnly"));
}

function getId()
{
return $this->id;
}

function getMeasureSatisfactionIfActive()
{
	return $this->measureSatisfactionIfActive;
}

function getObjectiveMeasureWeight()
{
	return $this->objectiveMeasureWeight;
}

function getObjectiveSetByContent()
{
	return $this->objectiveSetByContent;
}

function getPreventActivation()
{
	return $this->preventActivation;
}

function getRandomizationTiming()
{
	return $this->randomizationTiming;
}

function getReorderChildren()
{
	return $this->reorderChildren;
}

function getRequiredForCompleted()
{
	return $this->requiredForCompleted;
}

function getRequiredForIncomplete()
{
	return $this->requiredForIncomplete;
}

function getRequiredForNotSatisfied()
{
	return $this->requiredForNotSatisfied;
}

function getRequiredForSatisfied()
{
	return $this->requiredForSatisfied;
}

function getRollupObjectiveSatisfied()
{
	return $this->rollupObjectiveSatisfied;
}

function getRollupProgressCompletion()
{
	return $this->rollupProgressCompletion;
}

function getSelectCount()
{
	return $this->selectCount;
}

function getSelectionTiming()
{
	return $this->selectionTiming;
}

function getSequencingId()
{
	return $this->sequencingId;
}

function getTracked()
{
	return $this->tracked;
}

function getUseCurrentAttemptObjectiveInfo()
{
	return $this->useCurrentAttemptObjectiveInfo;
}

function getUseCurrentAttemptProgressInfo()
{
	return $this->useCurrentAttemptProgressInfo;
}

// **********************
// SETTER METHODS
// **********************


function setActivityAbsoluteDurationLimit($val)
{
	$this->activityAbsoluteDurationLimit =  $val;
}

function setActivityExperiencedDurationLimit($val)
{
	$this->activityExperiencedDurationLimit =  $val;
}

function setAttemptAbsoluteDurationLimit($val)
{
	$this->attemptAbsoluteDurationLimit =  $val;
}

function setAttemptExperiencedDurationLimit($val)
{
	$this->attemptExperiencedDurationLimit =  $val;
}

function setAttemptLimit($val)
{
	$this->attemptLimit =  $val;
}

function setBeginTimeLimit($val)
{
	$this->beginTimeLimit =  $val;
}

function setChoice($a_choice)
{
	$this->node->setAttribute("choice",$a_choice ? "true": "false");
}

function setChoiceExit($a_choicexit)
{
	$this->node->setAttribute("choiceExit",$a_choicexit  ? "true": "false");
}

function setCompletionSetByContent($val)
{
	$this->completionSetByContent =  $val;
}

function setConstrainChoice($val)
{
	$this->constrainChoice =  $val;
}

function setSeqNodeId($a_seqnodeid)
{
	$this->seqNodeId = $a_seqnodeid;
}

function setEndTimeLimit($val)
{
	$this->endTimeLimit =  $val;
}

function setFlow($a_flow)
{
	$this->node->setAttribute("flow",$a_flow  ? "true": "false");
}

function setForwardOnly($a_forwardonly)
{
	$this->node->setAttribute("forwardOnly",$a_forwardonly  ? "true": "false");
}

function setId($val)
{
	$this->id =  $val;
}

function setMeasureSatisfactionIfActive($val)
{
	$this->measureSatisfactionIfActive =  $val;
}

function setObjectiveMeasureWeight($val)
{
	$this->objectiveMeasureWeight =  $val;
}

function setObjectiveSetByContent($val)
{
	$this->objectiveSetByContent =  $val;
}

function setPreventActivation($val)
{
	$this->preventActivation =  $val;
}

function setRandomizationTiming($val)
{
	$this->randomizationTiming =  $val;
}

function setReorderChildren($val)
{
	$this->reorderChildren =  $val;
}

function setRequiredForCompleted($val)
{
	$this->requiredForCompleted =  $val;
}

function setRequiredForIncomplete($val)
{
	$this->requiredForIncomplete =  $val;
}

function setRequiredForNotSatisfied($val)
{
	$this->requiredForNotSatisfied =  $val;
}

function setRequiredForSatisfied($val)
{
	$this->requiredForSatisfied =  $val;
}

function setRollupObjectiveSatisfied($val)
{
	$this->rollupObjectiveSatisfied =  $val;
}

function setRollupProgressCompletion($val)
{
	$this->rollupProgressCompletion =  $val;
}

function setSelectCount($val)
{
	$this->selectCount =  $val;
}

function setSelectionTiming($val)
{
	$this->selectionTiming =  $val;
}

function setSequencingId($val)
{
	$this->sequencingId =  $val;
}

function setTracked($val)
{
	$this->tracked =  $val;
}

function setUseCurrentAttemptObjectiveInfo($val)
{
	$this->useCurrentAttemptObjectiveInfo =  $val;
}

function setUseCurrentAttemptProgressInfo($val)
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
private static function convertStringToBool($a_string){
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

?>
