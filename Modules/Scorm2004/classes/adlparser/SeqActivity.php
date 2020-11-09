<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
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
/*
    PHP port of ADL SeqActivity.java
    @author Hendrik Holtmann <holtmann@mac.com>

    This .php file is GPL licensed (see above) but based on
    SeqActivity.java by ADL Co-Lab, which is licensed as:

    Advanced Distributed Learning Co-Laboratory (ADL Co-Lab) Hub grants you
    ("Licensee") a non-exclusive, royalty free, license to use, modify and
    redistribute this software in source and binary code form, provided that
    i) this copyright notice and license appear on all copies of the software;
    and ii) Licensee does not utilize the software in a manner which is
    disparaging to ADL Co-Lab Hub.

    This software is provided "AS IS," without a warranty of any kind.  ALL
    EXPRESS OR IMPLIED CONDITIONS, REPRESENTATIONS AND WARRANTIES, INCLUDING
    ANY IMPLIED WARRANTY OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE
    OR NON-INFRINGEMENT, ARE HEREBY EXCLUDED.  ADL Co-Lab Hub AND ITS LICENSORS
    SHALL NOT BE LIABLE FOR ANY DAMAGES SUFFERED BY LICENSEE AS A RESULT OF
    USING, MODIFYING OR DISTRIBUTING THE SOFTWARE OR ITS DERIVATIVES.  IN NO
    EVENT WILL ADL Co-Lab Hub OR ITS LICENSORS BE LIABLE FOR ANY LOST REVENUE,
    PROFIT OR DATA, OR FOR DIRECT, INDIRECT, SPECIAL, CONSEQUENTIAL,
    INCIDENTAL OR PUNITIVE DAMAGES, HOWEVER CAUSED AND REGARDLESS OF THE
    THEORY OF LIABILITY, ARISING OUT OF THE USE OF OR INABILITY TO USE
    SOFTWARE, EVEN IF ADL Co-Lab Hub HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH
    DAMAGES.
*/


   require_once("SeqObjective.php");
   require_once("SeqRollupRule.php");

   define("TIMING_ONCE", "once");
   define("TIMING_EACHNEW", "onEachNewAttempt");
   define("TER_EXITALL", "_EXITALL_");
   define("TIMING_NEVER", "never");

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

    public $mDepth = 0;

    public $mCount = -1;

    public $mLearnerID = "_NULL_";

    public $mScopeID = null;
    
    public $mActivityID = null;

    public $mResourceID = null;

    public $mStateID = null;

    public $mTitle = null;

    public $mIsVisible = true;
    
    public $mOrder = -1;
    
    public $mActiveOrder = -1;

    public $mSelected = true;

    //SeqActivity converted to array???
    public $mParent = null;

    public $mIsActive = false;
    
    public $mIsSuspended = false;

    //Vector converted to array
    public $mChildren = null;

    //Vector converted to array
    public $mActiveChildren = null;

    public $mDeliveryMode = "normal";

    public $mControl_choice = true;

    public $mControl_choiceExit = true;

    public $mControl_flow = false;

    public $mControl_forwardOnly = false;

    public $mConstrainChoice = false;

    public $mPreventActivation = false;

    public $mUseCurObj = true;
    
    public $mUseCurPro = true;

    public $mMaxAttemptControl = false;

    public $mMaxAttempt = 0;

    public $mAttemptAbDurControl = false;

    //ADLDuration
    public $mAttemptAbDur = null;

    public $mAttemptExDurControl = false;

    public $mAttemptExDur = null;

    public $mActivityAbDurControl = false;

    //ADLDuration
    public $mActivityAbDur = null;

    public $mActivityExDurControl = false;

    //ADLDuration
    public $mActivityExDur = null;

    public $mBeginTimeControl = false;

    public $mBeginTime = null;
    
    public $mEndTimeControl = false;

    public $mEndTime = null;

    //convert to array?
    public $mAuxResources = null;

    //SeqRollupRuleset
    public $mRollupRules = null;

    public $mActiveMeasure = true;

    public $mRequiredForSatisfied = ROLLUP_CONSIDER_ALWAYS;

    public $mRequiredForNotSatisfied = ROLLUP_CONSIDER_ALWAYS;

    public $mRequiredForCompleted = ROLLUP_CONSIDER_ALWAYS;

    public $mRequiredForIncomplete = ROLLUP_CONSIDER_ALWAYS;
    
    //convert to array
    public $mObjectives = null;

    //HashTable convert to assosiative array
    public $mObjMaps = null;
    
    public $mIsObjectiveRolledUp = true;

    public $mObjMeasureWeight = 1.0;

    public $mIsProgressRolledUp = true;

    public $mSelectTiming = "never";
    
    public $mSelectStatus = false;

    public $mSelectCount = 0;

    public $mSelection = false;

    public $mRandomTiming = "never";

    public $mReorder = false;

    public $mRandomized = false;

    public $mIsTracked = true;

    public $mContentSetsCompletion = false;

    public $mContentSetsObj = false;
        
    //ADLTracking
    public $mCurTracking = null;
        
    //convert to array?
    public $mTracking = null;
    
    public $mNumAttempt = 0;
   
    public $mNumSCOAttempt = 0;
    
    //ADLDuration
    public $mActivityAbDur_track = null;
      
    //ADLDuration
    public $mActivityExDur_track = null;
       
    public $mProgressThreshold = 1.0;
       
    public $mProgressDeterminedByMeasure = false;
       
    public $mProgressWeight = 1.0;
    

    public function __construct()
    {
        //$this->mActiveChildren = array();
    }
    
    public function addChild($ioChild)
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
    
    public function setOrder($iOrder)
    {
        $this->mOrder = $iOrder;
    }
    
    public function setActiveOrder($iOrder)
    {
        $this->mActiveOrder = $iOrder;
    }
    
    public function setParent($iParent)
    {
        $this->mParent = $iParent;
    }
    

    
    //setters for public vats
    public function setID($id)
    {
        $this->mActivityID = $id;
    }
   
    public function setResourceID($id)
    {
        $this->mResourceID = $id;
    }
    
    public function setIsVisible($visible)
    {
        $this->mIsVisible = $visible;
    }
    
    public function setCompletionThreshold($compThresh)
    {
        $this->mProgressThreshold = $compThresh;
    }
    
    public function setCompletedByMeasure($compbm)
    {
        $this->mProgressDeterminedByMeasure = $compbm;
    }
    
    public function setProgressWeight($progweight)
    {
        $this->mProgressWeight = $progweight;
    }
    
    public function setControlModeChoice($choice)
    {
        $this->mControl_choice = $choice;
    }
    
    public function setControlModeChoiceExit($choiceExit)
    {
        $this->mControl_choiceExit = $choiceExit;
    }
    
    public function setControlModeFlow($flow)
    {
        $this->mControl_flow = $flow;
    }
    
    public function setControlForwardOnly($forwardOnly)
    {
        $this->mControl_forwardOnly = $forwardOnly;
    }
    
    public function setUseCurObjective($useCurObjective)
    {
        $this->mUseCurObj = $useCurObjective;
    }

    public function setUseCurProgress($useCurProgress)
    {
        $this->mUseCurPro = $useCurProgress;
    }
    
    public function setAttemptLimit($value)
    {
        if ($value >= 0) {
            $this->mMaxAttemptControl = true;
            $this->mMaxAttempt = $value;
        } else {
            $this->mMaxAttemptControl = false;
            $this->mMaxAttempt = -1;
        }
    }
    
    public function setAttemptAbDur($iDur)
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
    
    public function setAttemptExDur($iDur)
    {
        if ($iDur != null) {
            $this->mAttemptExDurControl = true;
        //to be implemented
            // $this->mAttemptExDur = new ADLDuration(ADLDuration.FORMAT_SCHEMA, iDur);
        } else {
            $this->mAttemptExDurControl = false;
        }
    }
    
    public function setActivityAbDur($iDur)
    {
        if ($iDur != null) {
            $this->mActivityAbDurControl = true;
        //$this->mActivityAbDur = new ADLDuration(ADLDuration.FORMAT_SCHEMA, iDur);
        } else {
            $this->mActivityAbDurControl = false;
        }
    }
    
    public function setActivityExDur($iDur)
    {
        if ($iDur != null) {
            $this->mmActivityExDurControl = true;
        // $this->mmActivityExDur = new ADLDuration(ADLDuration.FORMAT_SCHEMA, iDur);
        } else {
            $this->mmActivityExDurControl = false;
        }
    }
    
    public function setBeginTimeLimit($iTime)
    {
        if ($iTime != null) {
            $this->mBeginTimeControl = true;
            $this->mBeginTime = $iTime;
        } else {
            $this->mBeginTimeControl = false;
        }
    }
    
    public function setEndTimeLimit($iTime)
    {
        if ($iTime != null) {
            $this->mEndTimeControl = true;
            $this->mEndTime = $iTime;
        } else {
            $this->mEndTimeControl = false;
        }
    }
    
    public function setRandomTiming($iTiming)
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
    
    public function setSelectCount($iCount)
    {
        if ($iCount >= 0) {
            $this->mSelectStatus = true;
            $this->mSelectCount = $iCount;
        } else {
            $this->mSelectStatus = false;
        }
    }
    
    public function setReorderChildren($iReorder)
    {
        $this->mReorder = $iReorder;
    }


    public function setSelectionTiming($iTiming)
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
    
    public function setIsTracked($iTracked)
    {
        $this->mIsTracked = $iTracked;
    }
    
    public function setSetCompletion($iSet)
    {
        $this->mContentSetsCompletion = $iSet;
    }
    
    public function setSetObjective($iSet)
    {
        $this->mContentSetsObj = $iSet;
    }
    
    public function setPreventActivation($iPreventActivation)
    {
        $this->mPreventActivation = $iPreventActivation;
    }

    public function setConstrainChoice($iConstrainChoice)
    {
        $this->mConstrainChoice = $iConstrainChoice;
    }

    public function setRequiredForSatisfied($iConsider)
    {
        $this->mRequiredForSatisfied = $iConsider;
    }

    public function setRequiredForNotSatisfied($iConsider)
    {
        $this->mRequiredForNotSatisfied = $iConsider;
    }

    public function setRequiredForCompleted($iConsider)
    {
        $this->mRequiredForCompleted = $iConsider;
    }

    public function setRequiredForIncomplete($iConsider)
    {
        $this->mRequiredForIncomplete = $iConsider;
    }

    public function setSatisfactionIfActive($iActiveMeasure)
    {
        $this->mActiveMeasure = $iActiveMeasure;
    }
    
    public function setTitle($title)
    {
        $this->mTitle = $title;
    }
    
    public function setPreSeqRules($iRuleSet)
    {
        $this->mPreConditionRules = $iRuleSet;
    }
   
    public function setExitSeqRules($iRuleSet)
    {
        $this->mExitActionRules = $iRuleSet;
    }

    public function setPostSeqRules($iRuleSet)
    {
        $this->mPostConditionRules = $iRuleSet;
    }
    
    public function setObjectives($iObjs)
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
    
    public function setIsObjRolledUp($iRolledup)
    {
        $this->mIsObjectiveRolledUp = $iRolledup;
    }
    
    public function setObjMeasureWeight($iWeight)
    {
        $this->mObjMeasureWeight = $iWeight;
    }
    
    public function setIsProgressRolledUp($iRolledup)
    {
        $this->mIsProgressRolledUp = $iRolledup;
    }
    
    public function setRollupRules($iRuleSet)
    {
        $this->mRollupRules = $iRuleSet;
    }
    
    public function setAuxResources($iRes)
    {
        $this->mAuxResources = $iRes;
    }


    

    public function getID()
    {
        return $this->mActivityID;
    }
    
    public function getIsVisible()
    {
        return $this->mIsVisible;
    }
}
