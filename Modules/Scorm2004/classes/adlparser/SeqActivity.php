<?php declare(strict_types=1);

   define("TIMING_ONCE", "once");
   define("TIMING_EACHNEW", "onEachNewAttempt");
   define("TER_EXITALL", "_EXITALL_");
   define("TIMING_NEVER", "never");

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class SeqActivity
{
    
    
    //It's quite bad design to declare these variables public (should be private), but for later JSON serialization PHP needs this
    //cause json_encode ignores private or protected variables
    
     
    //SeqRuleset
    public $mPreConditionRules = null;

    //SeqRuleset
    public $mPostConditionRules = null;

    //SeqRuleset
    public $mExitActionRules = null;

    public $mXML = null;

    public int $mDepth = 0;

    public int $mCount = -1;

    public string $mLearnerID = "_NULL_";

    public $mScopeID = null;
    
    public $mActivityID = null;

    public $mResourceID = null;

    public $mStateID = null;

    public $mTitle = null;

    public bool $mIsVisible = true;
    
    public int $mOrder = -1;
    
    public int $mActiveOrder = -1;

    public bool $mSelected = true;

    //SeqActivity converted to array???
    public $mParent = null;

    public bool $mIsActive = false;
    
    public bool $mIsSuspended = false;

    //Vector converted to array
    public $mChildren = null;

    //Vector converted to array
    public $mActiveChildren = null;

    public string $mDeliveryMode = "normal";

    public bool $mControl_choice = true;

    public bool $mControl_choiceExit = true;

    public bool $mControl_flow = false;

    public bool $mControl_forwardOnly = false;

    public bool $mConstrainChoice = false;

    public bool $mPreventActivation = false;

    public bool $mUseCurObj = true;
    
    public bool $mUseCurPro = true;

    public bool $mMaxAttemptControl = false;

    public int $mMaxAttempt = 0;

    public bool $mAttemptAbDurControl = false;

    //ADLDuration
    public $mAttemptAbDur = null;

    public bool $mAttemptExDurControl = false;

    public $mAttemptExDur = null;

    public bool $mActivityAbDurControl = false;

    //ADLDuration
    public $mActivityAbDur = null;

    public bool $mActivityExDurControl = false;

    //ADLDuration
    public $mActivityExDur = null;

    public bool $mBeginTimeControl = false;

    public $mBeginTime = null;
    
    public bool $mEndTimeControl = false;

    public $mEndTime = null;

    //convert to array?
    public $mAuxResources = null;

    //SeqRollupRuleset
    public $mRollupRules = null;

    public bool $mActiveMeasure = true;

    public string $mRequiredForSatisfied = ROLLUP_CONSIDER_ALWAYS;

    public string $mRequiredForNotSatisfied = ROLLUP_CONSIDER_ALWAYS;

    public string $mRequiredForCompleted = ROLLUP_CONSIDER_ALWAYS;

    public string $mRequiredForIncomplete = ROLLUP_CONSIDER_ALWAYS;
    
    //convert to array
    public $mObjectives = null;

    //HashTable convert to assosiative array
    public $mObjMaps = null;
    
    public bool $mIsObjectiveRolledUp = true;

    public float $mObjMeasureWeight = 1.0;

    public bool $mIsProgressRolledUp = true;

    public string $mSelectTiming = "never";
    
    public bool $mSelectStatus = false;

    public int $mSelectCount = 0;

    public bool $mSelection = false;

    public string $mRandomTiming = "never";

    public bool $mReorder = false;

    public bool $mRandomized = false;

    public bool $mIsTracked = true;

    public bool $mContentSetsCompletion = false;

    public bool $mContentSetsObj = false;
        
    //ADLTracking
    public $mCurTracking = null;
        
    //convert to array?
    public $mTracking = null;
    
    public int $mNumAttempt = 0;
   
    public int $mNumSCOAttempt = 0;
    
    //ADLDuration
    public $mActivityAbDur_track = null;
      
    //ADLDuration
    public $mActivityExDur_track = null;
       
    public float $mProgressThreshold = 1.0;
       
    public bool $mProgressDeterminedByMeasure = false;
       
    public float $mProgressWeight = 1.0;
    

    public function __construct()
    {
        //$this->mActiveChildren = array();
    }
    
    public function addChild($ioChild) : void
    {
        if ($this->mChildren == null) {
            $this->mChildren = array();
        }
        if ($this->mActiveChildren == null) {
            $this->mActiveChildren = array();
        }

        //set class
        $c_ioChild['_SeqActivity'] = $ioChild;

        //keep both in sync
        array_push($this->mChildren, $c_ioChild);
        //array_push($this->mActiveChildren,$c_ioChild);

        //$this->mActiveChildren = $this->mChildren;

        $ioChild->setOrder(count($this->mChildren) - 1);
        $ioChild->setActiveOrder(count($this->mChildren) - 1);

        //set parents on the client
        //$ioChild->setParent($this);
    }
    
    public function setOrder(int $iOrder) : void
    {
        $this->mOrder = $iOrder;
    }
    
    public function setActiveOrder(int $iOrder) : void
    {
        $this->mActiveOrder = $iOrder;
    }
    
    public function setParent($iParent) : void
    {
        $this->mParent = $iParent;
    }
    

    
    //setters for public vats
    public function setID($id) : void
    {
        $this->mActivityID = $id;
    }
   
    public function setResourceID($id) : void
    {
        $this->mResourceID = $id;
    }
    
    public function setIsVisible(bool $visible) : void
    {
        $this->mIsVisible = $visible;
    }
    
    public function setCompletionThreshold(float $compThresh) : void
    {
        $this->mProgressThreshold = $compThresh;
    }
    
    public function setCompletedByMeasure(bool $compbm) : void
    {
        $this->mProgressDeterminedByMeasure = $compbm;
    }
    
    public function setProgressWeight(float $progweight) : void
    {
        $this->mProgressWeight = $progweight;
    }
    
    public function setControlModeChoice(bool $choice) : void
    {
        $this->mControl_choice = $choice;
    }
    
    public function setControlModeChoiceExit(bool $choiceExit) : void
    {
        $this->mControl_choiceExit = $choiceExit;
    }
    
    public function setControlModeFlow(bool $flow) : void
    {
        $this->mControl_flow = $flow;
    }
    
    public function setControlForwardOnly(bool $forwardOnly) : void
    {
        $this->mControl_forwardOnly = $forwardOnly;
    }
    
    public function setUseCurObjective(bool $useCurObjective) : void
    {
        $this->mUseCurObj = $useCurObjective;
    }

    public function setUseCurProgress(bool $useCurProgress) : void
    {
        $this->mUseCurPro = $useCurProgress;
    }
    
    public function setAttemptLimit(int $value) : void
    {
        if ($value >= 0) {
            $this->mMaxAttemptControl = true;
            $this->mMaxAttempt = $value;
        } else {
            $this->mMaxAttemptControl = false;
            $this->mMaxAttempt = -1;
        }
    }
    
    public function setAttemptAbDur($iDur) : void
    {
        if ($iDur != null) {
            $this->mActivityAbDurControl = true;
        //to be implemented
              //convert duration
             //$this->mActivityAbDur = new ADLDuration(ADLDuration.FORMAT_SCHEMA, iDur);
        } else {
            $this->mActivityAbDurControl = false;
        }
    }
    
    public function setAttemptExDur($iDur) : void
    {
        if ($iDur != null) {
            $this->mAttemptExDurControl = true;
        //to be implemented
            // $this->mAttemptExDur = new ADLDuration(ADLDuration.FORMAT_SCHEMA, iDur);
        } else {
            $this->mAttemptExDurControl = false;
        }
    }
    
    public function setActivityAbDur($iDur) : void
    {
        if ($iDur != null) {
            $this->mActivityAbDurControl = true;
        //$this->mActivityAbDur = new ADLDuration(ADLDuration.FORMAT_SCHEMA, iDur);
        } else {
            $this->mActivityAbDurControl = false;
        }
    }
    
    public function setActivityExDur($iDur) : void
    {
        if ($iDur != null) {
            $this->mmActivityExDurControl = true;
        // $this->mmActivityExDur = new ADLDuration(ADLDuration.FORMAT_SCHEMA, iDur);
        } else {
            $this->mmActivityExDurControl = false;
        }
    }
    
    public function setBeginTimeLimit($iTime) : void
    {
        if ($iTime != null) {
            $this->mBeginTimeControl = true;
            $this->mBeginTime = $iTime;
        } else {
            $this->mBeginTimeControl = false;
        }
    }
    
    public function setEndTimeLimit($iTime) : void
    {
        if ($iTime != null) {
            $this->mEndTimeControl = true;
            $this->mEndTime = $iTime;
        } else {
            $this->mEndTimeControl = false;
        }
    }
    
    public function setRandomTiming(string $iTiming) : void
    {
        // Validate vocabulary
        if (!($this->iTiming == TIMING_NEVER ||
                 $this->iTiming == TIMING_ONCE ||
                 $this->iTiming == TIMING_EACHNEW)) {
            $this->mSelectTiming = TIMING_NEVER;
        } else {
            $this->mRandomTiming = $iTiming;
        }
    }
    
    public function setSelectCount(int $iCount) : void
    {
        if ($iCount >= 0) {
            $this->mSelectStatus = true;
            $this->mSelectCount = $iCount;
        } else {
            $this->mSelectStatus = false;
        }
    }
    
    public function setReorderChildren(bool $iReorder) : void
    {
        $this->mReorder = $iReorder;
    }


    public function setSelectionTiming(string $iTiming) : void
    {
    
     // Validate vocabulary
        if (!($this->iTiming == TIMING_NEVER ||
               $this->iTiming == TIMING_ONCE ||
                  $this->iTiming == TIMING_EACHNEW)) {
            $this->mSelectTiming = TIMING_NEVER;
        } else {
            $this->mSelectTiming = $iTiming;
        }
    }
    
    public function setIsTracked(bool $iTracked) : void
    {
        $this->mIsTracked = $iTracked;
    }
    
    public function setSetCompletion(bool $iSet) : void
    {
        $this->mContentSetsCompletion = $iSet;
    }
    
    public function setSetObjective(bool $iSet) : void
    {
        $this->mContentSetsObj = $iSet;
    }
    
    public function setPreventActivation(bool $iPreventActivation) : void
    {
        $this->mPreventActivation = $iPreventActivation;
    }

    public function setConstrainChoice(bool $iConstrainChoice) : void
    {
        $this->mConstrainChoice = $iConstrainChoice;
    }

    public function setRequiredForSatisfied(string $iConsider) : void
    {
        $this->mRequiredForSatisfied = $iConsider;
    }

    public function setRequiredForNotSatisfied(string $iConsider) : void
    {
        $this->mRequiredForNotSatisfied = $iConsider;
    }

    public function setRequiredForCompleted(string $iConsider) : void
    {
        $this->mRequiredForCompleted = $iConsider;
    }

    public function setRequiredForIncomplete(string $iConsider) : void
    {
        $this->mRequiredForIncomplete = $iConsider;
    }

    public function setSatisfactionIfActive(bool $iActiveMeasure) : void
    {
        $this->mActiveMeasure = $iActiveMeasure;
    }
    
    public function setTitle($title) : void
    {
        $this->mTitle = $title;
    }
    
    public function setPreSeqRules($iRuleSet) : void
    {
        $this->mPreConditionRules = $iRuleSet;
    }
   
    public function setExitSeqRules($iRuleSet) : void
    {
        $this->mExitActionRules = $iRuleSet;
    }

    public function setPostSeqRules($iRuleSet) : void
    {
        $this->mPostConditionRules = $iRuleSet;
    }
    
    public function setObjectives($iObjs) : void
    {
        $this->mObjectives = $iObjs;
        for ($i = 0; $i < count($iObjs); $i++) {
            $obj = $iObjs[$i];
            if ($obj['_SeqObjective']->mMaps != null) {
                $index = $obj['_SeqObjective']->mObjID;
                $this->mObjMaps["$index"] = $obj['_SeqObjective']->mMaps;
            }
        }
    }
    
    public function setIsObjRolledUp(bool $iRolledup) : void
    {
        $this->mIsObjectiveRolledUp = $iRolledup;
    }
    
    public function setObjMeasureWeight(float $iWeight) : void
    {
        $this->mObjMeasureWeight = $iWeight;
    }
    
    public function setIsProgressRolledUp(bool $iRolledup) : void
    {
        $this->mIsProgressRolledUp = $iRolledup;
    }
    
    public function setRollupRules($iRuleSet) : void
    {
        $this->mRollupRules = $iRuleSet;
    }
    
    public function setAuxResources($iRes) : void
    {
        $this->mAuxResources = $iRes;
    }


    

    public function getID()
    {
        return $this->mActivityID;
    }
    
    public function getIsVisible() : bool
    {
        return $this->mIsVisible;
    }
}
