// Build: 2009922171047 

function ADLAuxiliaryResource()
{}
ADLAuxiliaryResource.prototype={mTitle:null,mResourceID:null,mParameter:null}
var UNKNOWN=-999;var LT=-1;var EQ=0;var GT=1;var FORMAT_SECONDS=0;var FORMAT_SCHEMA=1;function ADLDuration(iOptions)
{var iOptions=ilAugment({iFormat:FORMAT_SECONDS,iValue:0},iOptions);var iFormat=iOptions.iFormat;var iValue=iOptions.iValue;if(iValue==0)
{this.mDuration=0;}
var hours=null;var min=null;var sec=null;switch(iFormat)
{case FORMAT_SECONDS:{var secs=0.0;secs=iValue;this.mDuration=parseFloat(secs);break;}
case FORMAT_SCHEMA:{var locStart=iValue.indexOf('T');var loc=0;if(locStart!=-1)
{locStart++;loc=iValue.indexOf("H",locStart);if(loc!=-1)
{hours=iValue.substring(locStart,loc);this.mDuration=parseFloat(hours)*3600;locStart=loc+1;}
loc=iValue.indexOf("M",locStart);if(loc!=-1)
{min=iValue.substring(locStart,loc);this.mDuration+=parseFloat(min)*60;locStart=loc+1;}
loc=iValue.indexOf("S",locStart);if(loc!=-1)
{sec=iValue.substring(locStart,loc);this.mDuration+=parseFloat(sec);}}
break;}
default:{}}}
ADLDuration.prototype={mDuration:0.0,round:function(iValue)
{iValue=iValue*10;iValue=Math.round(iValue);iValue=iValue/10;return iValue;},format:function(iFormat)
{var out=null;var countHours=0;var countMin=0;var countSec=0;var temp=0;switch(iFormat)
{case FORMAT_SECONDS:{var sec=this.mDuration;out=sec;break;}
case FORMAT_SCHEMA:{out="";countHours=0;countMin=0;countSec=0;temp=this.mDuration;if(temp>=.1)
{temp=this.round(temp);if(temp>=3600)
{countHours=(temp/3600);temp%=3600;}
if(temp>60)
{countMin=(temp/60);temp%=60;}
countSec=this.round(temp);}
out="PT";if(countHours>0)
{out=out+Math.floor(countHours);out+="H";}
if(countMin>0)
{out=out+Math.floor(countMin);out+="M";}
if(countSec>0)
{out=out+countSec;out+="S";}
break;}}
return out;},add:function(iDur)
{this.mDuration+=parseFloat(iDur.mDuration);},compare:function(iDur)
{var relation=UNKNOWN;if(this.mDuration<iDur.mDuration)
{relation=LT;}
else if(this.mDuration==iDur.mDuration)
{relation=EQ;}
else if(this.mDuration>iDur.mDuration)
{relation=GT;}
return relation;}}
var LAUNCH_TOC="_TOC_";var LAUNCH_COURSECOMPLETE="_COURSECOMPLETE_";var LAUNCH_EXITSESSION="_ENDSESSION_";var LAUNCH_SEQ_BLOCKED="_SEQBLOCKED_";var LAUNCH_NOTHING="_NOTHING_";var LAUNCH_ERROR="_ERROR_";var LAUNCH_ERROR_DEADLOCK="_DEADLOCK_";var LAUNCH_ERROR_INVALIDNAVREQ="_INVALIDNAVREQ_";var LAUNCH_SEQ_ABANDON="_SEQABANDON_";var LAUNCH_SEQ_ABANDONALL="_SEQABANDONALL_";function ADLLaunch()
{}
ADLLaunch.prototype={mSeqNonContent:null,mEndSession:false,mActivityID:null,mResourceID:null,mStateID:null,mNumAttempt:0,mDeliveryMode:"normal",mMaxTime:null,mNavState:null}
function ADLObjStatus()
{}
ADLObjStatus.prototype={mObjID:null,mHasMeasure:false,mMeasure:1.0,mStatus:TRACK_UNKNOWN}
function ADLSeqUtilities()
{this.satisfied=new Object();this.measure=new Object();this.status=new Object();}
ADLSeqUtilities.prototype={setGlobalObjSatisfied:function(iObjID,iLearnerID,iScopeID,iSatisfied)
{if(this.satisfied[iObjID]==null)this.satisfied[iObjID]=new Object();if(this.satisfied[iObjID][iLearnerID]==null)this.satisfied[iObjID][iLearnerID]=new Object();this.satisfied[iObjID][iLearnerID][iScopeID]=iSatisfied;},getGlobalObjSatisfied:function(iObjID,iLearnerID,iScopeID)
{if(this.satisfied[iObjID]!=null&&this.satisfied[iObjID][iLearnerID]!=null&&this.satisfied[iObjID][iLearnerID][iScopeID]!=null)
{return this.satisfied[iObjID][iLearnerID][iScopeID];}
return null;},setGlobalObjMeasure:function(iObjID,iLearnerID,iScopeID,iMeasure)
{if(this.measure[iObjID]==null)this.measure[iObjID]=new Object();if(this.measure[iObjID][iLearnerID]==null)this.measure[iObjID][iLearnerID]=new Object();this.measure[iObjID][iLearnerID][iScopeID]=iMeasure;},getGlobalObjMeasure:function(iObjID,iLearnerID,iScopeID)
{if(this.measure[iObjID]!=null&&this.measure[iObjID][iLearnerID]&&this.measure[iObjID][iLearnerID][iScopeID])
{return this.measure[iObjID][iLearnerID][iScopeID];}
return null;},setCourseStatus:function(iCourseID,iLearnerID,iSatisfied,iMeasure,iCompleted)
{if(this.status==null){this.status=new Object();}
if(this.status[iCourseID]==null)this.status[iCourseID]=new Object();this.status[iCourseID][iLearnerID]={satisfied:iSatisfied,measure:iMeasure,completed:iCompleted};}}
var adl_seq_utilities=new ADLSeqUtilities();
var FLOW_NONE=0;var FLOW_FORWARD=1;var FLOW_BACKWARD=2;var TER_EXIT="_EXIT_";var TER_EXITALL="_EXITALL_";var TER_SUSPENDALL="_SUSPENDALL_";var TER_ABANDON="_ABANDON_";var TER_ABANDONALL="_ABANDONALL_";var SEQ_START="_START_";var SEQ_RETRY="_RETRY_";var SEQ_RESUMEALL="_RESUMEALL_";var SEQ_EXIT="_EXIT_";var SEQ_CONTINUE="_CONTINUE_";var SEQ_PREVIOUS="_PREVIOUS_";function Walk()
{}
Walk.prototype={at:null,direction:FLOW_NONE,endSession:false}
function ADLSequencer()
{}
ADLSequencer.prototype={mSeqTree:null,mEndSession:false,mExitCourse:false,mRetry:false,mExitAll:false,mValidTermination:true,mValidSequencing:true,getActivityTree:function(){return this.mSeqTree;},getObjStatusSet:function(iActivityID)
{var objSet=null;var act=this.getActivity(iActivityID);if(act!=null)
{objSet=act.getObjStatusSet();}
return objSet;},getValidRequests:function(oValid)
{var valid=null;if(this.mSeqTree!=null)
{valid=this.mSeqTree.getValidRequests();if(valid!=null)
{this.validateRequests();valid=this.mSeqTree.getValidRequests();}}
if(valid!=null)
{oValid.mContinue=valid.mContinue;oValid.mContinueExit=valid.mContinueExit;oValid.mPrevious=valid.mPrevious;if(valid.mTOC!=null)
{oValid.mTOC=valid.mTOC.concat(new Array());}
if(valid.mChoice!=null)
{oValid.mChoice=new clone(valid.mChoice);}}
else
{oValid.mContinue=false;oValid.mContinueExit=false;oValid.mPrevious=false;oValid.mChoice=null;oValid.mTOC=null;}},setActivityTree:function(iTree)
{if(iTree!=null)
{this.mSeqTree=iTree;}},getRoot:function()
{var rootActivity=null;if(this.mSeqTree!=null)
{rootActivity=this.mSeqTree.getRoot();}
return rootActivity;},clearSeqState:function()
{var temp=null;this.mSeqTree.setCurrentActivity(temp);this.mSeqTree.setFirstCandidate(temp);},reportSuspension:function(iID,iSuspended)
{var target=this.getActivity(iID);if(target!=null)
{if(target.getIsActive())
{if(!target.hasChildren(false)&&this.mSeqTree.getCurrentActivity()==target)
{target.setIsSuspended(iSuspended);}}}},setAttemptDuration:function(iID,iDur)
{var target=this.getActivity(iID);if(target!=null)
{if(target.getIsActive()&&target.getIsTracked())
{if(!target.hasChildren(false)&&this.mSeqTree.getCurrentActivity()==target)
{target.setCurAttemptExDur(iDur);this.validateRequests();}}}},clearAttemptObjMeasure:function(iID,iObjID)
{var target=this.getActivity(iID);if(target!=null)
{if(target.getIsActive())
{if(!target.hasChildren(false)&&this.mSeqTree.getCurrentActivity()==target)
{var statusChange=target.clearObjMeasure(iObjID);if(statusChange)
{var writeObjIDs=target.getObjIDs(iObjID,false);this.validateRequests();}}}}},setAttemptObjMeasure:function(iID,iObjID,iMeasure)
{var target=this.getActivity(iID);if(target!=null)
{if(target.getIsActive()&&target.getIsTracked())
{if(!target.hasChildren(false)&&this.mSeqTree.getCurrentActivity()==target)
{target.setObjMeasure(iMeasure,{iObjID:iObjID});if(true)
{var writeObjIDs=target.getObjIDs(iObjID,false);this.validateRequests();}}}}},setAttemptObjSatisfied:function(iID,iObjID,iStatus)
{var target=this.getActivity(iID);if(target!=null)
{if(target.getIsActive()&&target.getIsTracked())
{if(!target.hasChildren(false)&&this.mSeqTree.getCurrentActivity()==target)
{var statusChange=target.setObjSatisfied(iStatus,{iObjID:iObjID});if(statusChange)
{var writeObjIDs=target.getObjIDs(iObjID,false);this.validateRequests();}}}}},setAttemptProgressStatus:function(iID,iProgress)
{var target=this.getActivity(iID);if(target!=null)
{if(target.getIsActive()&&target.getIsTracked())
{if(!target.hasChildren(false)&&this.mSeqTree.getCurrentActivity()==target)
{var statusChange=target.setProgress(iProgress);if(statusChange)
{this.validateRequests();}}}}},navigateStr:function(iTarget)
{sclog("NavigationRequest [NB.2.1]","seq");var launch=new ADLLaunch();if(this.mSeqTree==null)
{launch.mSeqNonContent=LAUNCH_ERROR;launch.mEndSession=true;return launch;}
var target=this.getActivity(iTarget);if(target!=null)
{var newSession=false;var cur=this.mSeqTree.getCurrentActivity();if(cur==null)
{this.prepareClusters();newSession=true;}
var process=true;this.validateRequests();if(!newSession)
{var valid=this.mSeqTree.getValidRequests();if(valid!=null)
{if(valid.mChoice!=null)
{var test=valid.mChoice[iTarget];if(test==null)
{launch.mSeqNonContent=LAUNCH_ERROR_INVALIDNAVREQ;process=false;}
else if(!test.mIsSelectable)
{launch.mSeqNonContent=LAUNCH_ERROR_INVALIDNAVREQ;process=false;}}
else
{launch.mSeqNonContent=LAUNCH_ERROR_INVALIDNAVREQ;process=false;}}
else
{launch.mSeqNonContent=LAUNCH_ERROR;launch.mEndSession=true;process=false;}}
if(process)
{this.mValidTermination=true;this.mValidSequencing=true;var seqReq=iTarget;var delReq=null;if(!newSession)
{if(cur.getIsActive())
{seqReq=this.doTerminationRequest(TER_EXIT,false);if(seqReq==null)
{seqReq=iTarget;}}}
if(this.mValidTermination)
{delReq=this.doSequencingRequest(seqReq);}
else
{launch.mSeqNonContent=LAUNCH_NOTHING;}
if(this.mValidSequencing)
{this.doDeliveryRequest(delReq,false,launch);}
else
{launch.mSeqNonContent=LAUNCH_SEQ_BLOCKED;}}
else
{launch.mNavState=this.mSeqTree.getValidRequests();}}
else
{launch.mSeqNonContent=LAUNCH_ERROR;launch.mEndSession=true;}
return launch;},navigate:function(iRequest)
{sclog("NavigationRequest [NB.2.1]","seq");var launch=new ADLLaunch();if(this.mSeqTree==null)
{launch.mSeqNonContent=LAUNCH_ERROR;launch.mEndSession=true;return launch;}
var newSession=false;var cur=this.mSeqTree.getCurrentActivity();if(cur==null)
{this.prepareClusters();newSession=true;this.validateRequests();}
var process=true;var valid=null;if(newSession&&iRequest==NAV_NONE)
{}
else if(newSession&&(iRequest==NAV_EXITALL||iRequest==NAV_ABANDONALL))
{launch.mSeqNonContent=LAUNCH_EXITSESSION;launch.mEndSession=true;process=false;}
else if(iRequest==NAV_CONTINUE||iRequest==NAV_PREVIOUS)
{this.validateRequests();valid=this.mSeqTree.getValidRequests();if(valid==null)
{launch.mSeqNonContent=LAUNCH_ERROR;launch.mEndSession=true;process=false;}
else
{if(iRequest==NAV_CONTINUE)
{if(!valid.mContinue)
{process=false;launch.mSeqNonContent=LAUNCH_ERROR_INVALIDNAVREQ;}}
else
{if(!valid.mPrevious)
{process=false;launch.mSeqNonContent=LAUNCH_ERROR_INVALIDNAVREQ;}}}}
else
{process=this.doIMSNavValidation(iRequest);if(!process)
{launch.mSeqNonContent=LAUNCH_ERROR_INVALIDNAVREQ;}}
if(process)
{this.mValidTermination=true;this.mValidSequencing=true;var seqReq=null;var delReq=null;switch(iRequest)
{case NAV_START:delReq=this.doSequencingRequest(SEQ_START);if(this.mValidSequencing)
{this.doDeliveryRequest(delReq,false,launch);}
else
{launch.mSeqNonContent=LAUNCH_SEQ_BLOCKED;}
break;case NAV_RESUMEALL:delReq=this.doSequencingRequest(SEQ_RESUMEALL);if(this.mValidSequencing)
{this.doDeliveryRequest(delReq,false,launch);}
else
{launch.mSeqNonContent=LAUNCH_SEQ_BLOCKED;}
break;case NAV_CONTINUE:if(cur.getIsActive())
{seqReq=this.doTerminationRequest(TER_EXIT,false);}
if(this.mValidTermination)
{if(seqReq==null)
{delReq=this.doSequencingRequest(SEQ_CONTINUE);}
else
{delReq=this.doSequencingRequest(seqReq);}}
else
{launch.mSeqNonContent=LAUNCH_NOTHING;}
if(this.mValidSequencing)
{this.doDeliveryRequest(delReq,false,launch);}
else
{launch.mSeqNonContent=LAUNCH_SEQ_BLOCKED;}
break;case NAV_PREVIOUS:if(cur.getIsActive())
{seqReq=this.doTerminationRequest(TER_EXIT,false);}
if(this.mValidTermination)
{if(seqReq==null)
{delReq=this.doSequencingRequest(SEQ_PREVIOUS);}
else
{delReq=this.doSequencingRequest(seqReq);}}
else
{launch.mSeqNonContent=LAUNCH_NOTHING;}
if(this.mValidSequencing)
{this.doDeliveryRequest(delReq,false,launch);}
else
{launch.mSeqNonContent=LAUNCH_SEQ_BLOCKED;}
break;case NAV_ABANDON:seqReq=this.doTerminationRequest(TER_ABANDON,false);if(this.mValidTermination)
{delReq=this.doSequencingRequest(SEQ_EXIT);if(!this.mEndSession&&!this.mExitCourse)
{this.validateRequests();}}
else
{launch.mSeqNonContent=LAUNCH_NOTHING;}
if(this.mValidSequencing)
{this.doDeliveryRequest(delReq,false,launch);launch.mSeqNonContent=LAUNCH_SEQ_ABANDON;}
else
{launch.mSeqNonContent=LAUNCH_SEQ_BLOCKED;}
break;case NAV_ABANDONALL:seqReq=this.doTerminationRequest(TER_ABANDONALL,false);if(this.mValidTermination)
{delReq=this.doSequencingRequest(SEQ_EXIT);}
else
{launch.mSeqNonContent=LAUNCH_NOTHING;}
if(this.mValidSequencing)
{this.doDeliveryRequest(delReq,false,launch);launch.mSeqNonContent=LAUNCH_SEQ_ABANDONALL;}
else
{launch.mSeqNonContent=LAUNCH_SEQ_BLOCKED;}
break;case NAV_SUSPENDALL:seqReq=this.doTerminationRequest(TER_SUSPENDALL,false);if(this.mValidTermination)
{delReq=this.doSequencingRequest(SEQ_EXIT);}
else
{launch.mSeqNonContent=LAUNCH_NOTHING;}
if(this.mValidSequencing)
{this.doDeliveryRequest(delReq,false,launch);}
else
{launch.mSeqNonContent=LAUNCH_SEQ_BLOCKED;}
break;case NAV_EXIT:seqReq=this.doTerminationRequest(TER_EXIT,false);if(this.mValidTermination)
{if(seqReq==null)
{delReq=this.doSequencingRequest(SEQ_EXIT);}
else
{delReq=this.doSequencingRequest(seqReq);}
if(!this.mEndSession&&!this.mExitCourse)
{this.validateRequests();}}
else
{launch.mSeqNonContent=LAUNCH_NOTHING;}
if(this.mValidSequencing)
{this.doDeliveryRequest(delReq,false,launch);}
else
{launch.mSeqNonContent=LAUNCH_SEQ_BLOCKED;}
break;case NAV_EXITALL:seqReq=this.doTerminationRequest(TER_EXITALL,false);if(this.mValidTermination)
{delReq=this.doSequencingRequest(SEQ_EXIT);}
else
{launch.mSeqNonContent=LAUNCH_NOTHING;}
if(this.mValidSequencing)
{this.doDeliveryRequest(delReq,false,launch);}
else
{launch.mSeqNonContent=LAUNCH_SEQ_BLOCKED;}
break;case NAV_NONE:launch.mSeqNonContent=LAUNCH_TOC;launch.mNavState=this.mSeqTree.getValidRequests();if(launch.mNavState.mTOC==null)
{launch.mSeqNonContent=LAUNCH_ERROR_INVALIDNAVREQ;}
break;default:launch.mSeqNonContent=LAUNCH_ERROR;}}
else
{launch.mNavState=this.mSeqTree.getValidRequests();if(launch.mNavState==null)
{this.validateRequests();launch.mNavState=this.mSeqTree.getValidRequests();}}
return launch;},getActivity:function(iActivityID)
{var thisActivity=null;if(this.mSeqTree!=null)
{thisActivity=this.mSeqTree.getActivity(iActivityID);}
return thisActivity;},doIMSNavValidation:function(iRequest)
{var ok=true;var process=true;var newSession=false;var cur=this.mSeqTree.getCurrentActivity();var parent=null;if(cur==null)
{newSession=true;}
else
{parent=cur.getParent();}
switch(iRequest)
{case NAV_START:if(!newSession)
{process=false;}
break;case NAV_RESUMEALL:ok=true;if(!newSession)
{ok=false;}
else if(this.mSeqTree.getSuspendAll()==null)
{ok=false;}
if(!ok)
{process=false;}
break;case NAV_CONTINUE:if(newSession)
{process=false;}
else
{if(parent==null||!parent.getControlModeFlow())
{process=false;}}
break;case NAV_PREVIOUS:if(newSession)
{process=false;}
else
{if(parent!=null)
{if(!parent.getControlModeFlow()||parent.getControlForwardOnly())
{process=false;}}
else
{process=false;}}
break;case NAV_ABANDON:ok=true;if(newSession)
{ok=false;}
else if(!cur.getIsActive())
{ok=false;}
if(!ok)
{process=false;}
break;case NAV_ABANDONALL:if(newSession)
{process=false;}
break;case NAV_SUSPENDALL:if(newSession)
{process=false;}
break;case NAV_EXIT:if(newSession)
{ok=false;}
else if(!cur.getIsActive())
{ok=false;}
if(!ok)
{process=false;}
break;case NAV_EXITALL:if(newSession)
{process=false;}
break;default:process=false;}
return process;},validateRequests:function()
{var valid=this.mSeqTree.getValidRequests();var cur=this.mSeqTree.getCurrentActivity();if(cur!=null)
{var test=false;valid=new ADLValidRequests();var tempLaunch=new ADLLaunch();this.mValidTermination=true;this.mValidSequencing=true;var seqReq=null;var seqReqSuccess=false;var delReq=null;if(cur.getIsActive())
{valid.mSuspend=true;}
if(cur.getControlModeChoiceExit()||!cur.getIsActive())
{valid.mTOC=this.getTOC(this.mSeqTree.getRoot());}
if(valid.mTOC!=null)
{var newTOC=new Array();valid.mChoice=this.getChoiceSet(valid.mTOC,newTOC);if(newTOC.length>0)
{valid.mTOC=newTOC;}
else
{valid.mTOC=null;}}
if(cur.getParent()!=null)
{if(cur.getParent().getControlModeFlow())
{valid.mContinue=true;}
test=this.doIMSNavValidation(NAV_PREVIOUS);if(test)
{this.mValidSequencing=true;delReq=this.doSequencingRequest(SEQ_PREVIOUS);if(this.mValidSequencing)
{valid.mPrevious=this.doDeliveryRequest(delReq,true,tempLaunch);}}}}
else
{valid=new ADLValidRequests();if(this.mSeqTree.getSuspendAll()!=null)
{valid.mResume=true;}
else
{var walk=new Walk();walk.at=this.mSeqTree.getRoot();valid.mStart=this.processFlow(FLOW_FORWARD,true,walk,false);if(valid.mStart)
{var ok=true;while(walk.at!=null&&ok)
{ok=!this.checkActivity(walk.at);if(ok)
{walk.at=walk.at.getParent();}
else
{valid.mStart=false;}}}}
valid.mTOC=this.getTOC(this.mSeqTree.getRoot());if(valid.mTOC!=null)
{var newTOC=new Array();valid.mChoice=this.getChoiceSet(valid.mTOC,newTOC);if(newTOC.length>0)
{valid.mTOC=newTOC;}
else
{valid.mTOC=null;}}}
if(valid!=null)
{this.mSeqTree.setValidRequests(valid);}},evaluateExitRules:function(iTentative)
{sclog("SequencingExitActionRulesSub [TB.2.1]","seq");this.mExitCourse=false;var start=this.mSeqTree.getCurrentActivity();var exitAt=null;var exited=null;var path=new Array();if(start!=null)
{var parent=start.getParent();while(parent!=null)
{path[path.length]=parent;parent=parent.getParent();}
while(path.length>0&&(exited==null))
{parent=path[path.length-1];path.splice(path.length-1,1);var exitRules=parent.getExitSeqRules();if(exitRules!=null)
{exited=exitRules.evaluate(RULE_TYPE_EXIT,parent,false);}
if(exited!=null)
{exitAt=parent;}}
if(exited!=null)
{if(iTentative==false)
{this.terminateDescendentAttempts(exitAt);exitAt=this.endAttempt(exitAt,false);}
this.mSeqTree.setFirstCandidate(exitAt);}}},doTerminationRequest:function(iRequest,iTentative)
{sclog("TerminationRequest [TB.2.3]","seq");var seqReq=null;this.mExitAll=false;if(iRequest==null)
{this.mValidTermination=false;return seqReq;}
var cur=this.mSeqTree.getCurrentActivity();if(cur!=null)
{this.mSeqTree.setFirstCandidate(cur);}
else
{this.mValidTermination=false;return seqReq;}
if(iRequest==TER_EXIT)
{if(cur.getIsActive())
{cur=this.endAttempt(cur,iTentative);this.evaluateExitRules(iTentative);var exited=false;do
{exited=false;var process=this.mSeqTree.getFirstCandidate();if(!this.mExitCourse)
{var postRules=process.getPostSeqRules();if(postRules!=null)
{var result=null;result=postRules.evaluate(RULE_TYPE_POST,process,false);if(result!=null)
{sclog("SequencingPostConditionRulesSub [TB.2.2]","seq");if(result==SEQ_ACTION_RETRY)
{seqReq=SEQ_RETRY;if(process==this.mSeqTree.getRoot())
{iRequest=TER_EXITALL;}}
else if(result==SEQ_ACTION_CONTINUE)
{seqReq=SEQ_CONTINUE;}
else if(result==SEQ_ACTION_PREVIOUS)
{seqReq=SEQ_PREVIOUS;}
else if(result==SEQ_ACTION_EXITALL)
{iRequest=TER_EXITALL;}
else if(result==SEQ_ACTION_EXITPARENT)
{process=process.getParent();if(process==null)
{}
else
{this.mSeqTree.setFirstCandidate(process);process=this.endAttempt(process,iTentative);exited=true;}}
else if(result==SEQ_ACTION_RETRYALL)
{seqReq=SEQ_RETRY;iRequest=TER_EXITALL;}
else if(process==this.mSeqTree.getRoot())
{this.mExitCourse=true;}}}
else if(process==this.mSeqTree.getRoot())
{this.mExitCourse=true;}}
else{seqReq=SEQ_EXIT;}}
while(exited);}
else
{this.mValidTermination=false;}}
if(iRequest==TER_EXIT)
{}
else if(iRequest==TER_EXITALL)
{if(!iTentative)
{var process=this.mSeqTree.getFirstCandidate();if(process.getIsActive())
{process=this.endAttempt(process,false);}
this.terminateDescendentAttempts(this.mSeqTree.getRoot());this.endAttempt(this.mSeqTree.getRoot(),false);if(seqReq!=SEQ_RETRY)
{seqReq=SEQ_EXIT;}
this.mSeqTree.setFirstCandidate(this.mSeqTree.getRoot());}
else
{}
this.mSeqTree.setFirstCandidate(this.mSeqTree.getRoot());}
else if(iRequest==TER_SUSPENDALL)
{if(!iTentative)
{var process=this.mSeqTree.getFirstCandidate();this.reportSuspension(process.getID(),true);if(process.getIsActive())
{this.invokeRollup(process,null);this.mSeqTree.setSuspendAll(process);if(!process.getIsSuspended())
{process.incrementSCOAttempt();}}
else
{if(!process.getIsSuspended())
{this.mSeqTree.setSuspendAll(process.getParent());if(this.mSeqTree.getSuspendAll()==null)
{this.mValidTermination=false;}}}
if(this.mValidTermination)
{var start=this.mSeqTree.getSuspendAll();while(start!=null)
{start.setIsActive(false);start.setIsSuspended(true);start=start.getParent();}}}
this.mSeqTree.setFirstCandidate(this.mSeqTree.getRoot());}
else if(iRequest==TER_ABANDON)
{if(!iTentative)
{var process=this.mSeqTree.getFirstCandidate();process.setProgress(TRACK_UNKNOWN);process.setObjSatisfied(TRACK_UNKNOWN,null);process.clearObjMeasure(null);process.setIsActive(false);}}
else if(iRequest==TER_ABANDONALL)
{if(!iTentative)
{var process=this.mSeqTree.getFirstCandidate();process.setProgress(TRACK_UNKNOWN);process.setObjSatisfied(TRACK_UNKNOWN,null);process.clearObjMeasure(null);while(process!=null)
{process.setIsActive(false);process=process.getParent();}
seqReq=SEQ_EXIT;this.mSeqTree.setFirstCandidate(this.mSeqTree.getRoot());}}
else
{this.mValidTermination=false;}
if(!iTentative)
{this.mSeqTree.setCurrentActivity(this.mSeqTree.getFirstCandidate());}
var tmpID=this.mSeqTree.getFirstCandidate().getID();return seqReq;},invokeRollup:function(ioTarget,iWriteObjIDs)
{sclog("OverallRollup [RB.1.5]","seq");var rollupSet=new Object();if(ioTarget==this.mSeqTree.getCurrentActivity())
{var walk=ioTarget;while(walk!=null)
{rollupSet[walk.getID()]=walk.getDepth();var writeObjIDs=walk.getObjIDs(null,false);if(writeObjIDs!=null)
{for(var i=0;i<writeObjIDs.length;i++)
{var objID=writeObjIDs[i];var acts=this.mSeqTree.getObjMap(objID);if(acts!=null)
{for(var j=0;j<acts.length;j++)
{var act=this.getActivity(acts[j]);act=act.getParent();if(act!=null)
{if(act.getIsSelected())
{rollupSet[act.getID()]=act.getDepth();}}}}}}
walk=walk.getParent();}
delete rollupSet[ioTarget.getID()];}
if(iWriteObjIDs!=null)
{for(var i=0;i<iWriteObjIDs.length;i++)
{var objID=iWriteObjIDs[i];var acts=this.mSeqTree.getObjMap(objID);if(acts!=null)
{for(var j=0;j<acts.length;j++)
{var act=this.getActivity(acts[j]);act=act.getParent();if(act!=null)
{if(act.getIsSelected())
{rollupSet.put[act.getID()]=act.getDepth();}}}}}}
var count=0;for(x in rollupSet){count++;}
while(count>0)
{var deepest=null;var depth=-1;for(var key in rollupSet)
{var thisDepth=rollupSet[key];if(depth==-1)
{depth=thisDepth;deepest=this.getActivity(key);}
else if(thisDepth>depth)
{depth=thisDepth;deepest=this.getActivity(key);}}
if(deepest!=null)
{retf=this.doOverallRollup(deepest,rollupSet);rollupSet=retf.ioRollupSet;deepest=retf.ioTarget;count=0;for(x in rollupSet){count++;}
if(deepest==this.mSeqTree.getRoot())
{var satisfied="unknown";if(deepest.getObjStatus(false))
{satisfied=(deepest.getObjSatisfied(false))?"satisfied":"notSatisfied";}
var measure="unknown";if(deepest.getObjMeasureStatus(false))
{measure=deepest.getObjMeasure(false);}
var completed="unknown";if(deepest.getProgressStatus(false))
{completed=(deepest.getAttemptCompleted(false))?"completed":"incomplete";}
adl_seq_utilities.setCourseStatus(this.mSeqTree.getCourseID(),this.mSeqTree.getLearnerID(),satisfied,measure,completed);}}}},doOverallRollup:function(ioTarget,ioRollupSet)
{var rollupRules=ioTarget.getRollupRules();if(rollupRules==null)
{rollupRules=new SeqRollupRuleset();}
ioTarget=rollupRules.evaluate(ioTarget);delete ioRollupSet[ioTarget.getID()];var ret=new Object();ret.ioRollupSet=ioRollupSet;ret.ioTarget=ioTarget;return ret;},prepareClusters:function()
{var walk=this.mSeqTree.getRoot();var lookAt=new Array();if(walk!=null)
{while(walk!=null)
{if(walk.hasChildren(true))
{if(!walk.getSelectionTiming()==TIMING_NEVER)
{if(!walk.getSelection())
{walk=this.doSelection(walk);walk.setSelection(true);}}
if(!walk.getRandomTiming()==TIMING_NEVER)
{if(!walk.getRandomized())
{walk=this.doRandomize(walk);walk.setRandomized(true);}}
if(walk.hasChildren(false))
{lookAt[lookAt.length]=walk;}}
walk=walk.getNextSibling(false);if(walk==null)
{if(lookAt.length!=0)
{walk=lookAt[0];walk=walk.getChildren(false)[0];lookAt.splice(0,1);}}}}},doSelection:function(ioCluster)
{if(ioCluster.getChildren(true)!=null)
{var count=ioCluster.getSelectCount();var all=ioCluster.getChildren(true);var children=null;var set=null;var ok=false;var rand=0;var num=0;var lookUp=0;if(count>0)
{if(count<all.length)
{children=new Array();set=new Array();while(set.length<count)
{ok=false;while(!ok)
{num=Math.floor(Math.random()*all.length);lookUp=index_of(set,num);if(lookUp==-1)
{set[set.length]=num;ok=true;}}}
for(var i=0;i<all.length;i++)
{lookUp=index_of(set,i);if(lookUp!=-1)
{children[children.length]=all[i];}}
ioCluster.setChildren(children,false);}}}
return ioCluster;},doRandomize:function(ioCluster)
{if(ioCluster.getChildren(true)!=null)
{var all=ioCluster.getChildren(false);var set=null;var ok=false;var rand=0;var num=0;var lookUp=0;if(ioCluster.getReorderChildren())
{var reorder=new Array();set=new Array();for(var i=0;i<all.length;i++)
{ok=false;while(!ok)
{num=Math.floor(Math.random()*all.length);lookUp=index_of(set,num);if(lookUp==-1)
{set[set.length]=num;reorder[reorder.length]=all[num];ok=true;}}}
ioCluster.setChildren(reorder,false);}}
return ioCluster;},doSequencingRequest:function(iRequest)
{sclog("SequencingRequest [SB.2.12]","seq");var delReq=null;this.mEndSession=false;var from=this.mSeqTree.getFirstCandidate();if(iRequest==SEQ_START)
{sclog("StartSequencingRequest [SB.2.5]","seq");if(from==null)
{var walk=new Walk();walk.at=this.mSeqTree.getRoot();var success=this.processFlow(FLOW_FORWARD,true,walk,false);if(success)
{delReq=walk.at.getID();}}}
else if(iRequest==SEQ_RESUMEALL)
{sclog("ResumeAllSequencingRequest [SB.2.6]","seq");if(from==null)
{var resume=this.mSeqTree.getSuspendAll();if(resume!=null)
{delReq=resume.getID();}}}
else if(iRequest==SEQ_CONTINUE)
{sclog("ContinueSequencingRequest [SB.2.7]","seq");if(from!=null)
{var parent=from.getParent();if(parent==null||parent.getControlModeFlow())
{var walk=new Walk();walk.at=from;var success=this.processFlow(FLOW_FORWARD,false,walk,false);if(success)
{delReq=walk.at.getID();}
else
{this.terminateDescendentAttempts(this.mSeqTree.getRoot());ret=this.endAttempt(this.mSeqTree.getRoot(),false);this.mSeqTree.setFirstCandidate(ret);this.mEndSession=true;}}}}
else if(iRequest==SEQ_EXIT)
{sclog("ExitSequencingRequest [SB.2.11]","seq");if(from!=null)
{if(!from.getIsActive())
{var parent=from.getParent();if(parent==null)
{this.mEndSession=true;}}}}
else if(iRequest==SEQ_PREVIOUS)
{sclog("PreviousSequencingRequest [SB.2.5]","seq");if(from!=null)
{var parent=from.getParent();if(parent==null||parent.getControlModeFlow())
{var walk=new Walk();walk.at=from;var success=this.processFlow(FLOW_BACKWARD,false,walk,false);if(success)
{delReq=walk.at.getID();}}}}
else if(iRequest==SEQ_RETRY)
{sclog("RetrySequencingRequest [SB.2.10]","seq");if(from!=null)
{if(this.mExitAll||(!(from.getIsActive()||from.getIsSuspended())))
{if(from.getChildren(false)!=null)
{var walk=new Walk();walk.at=from;this.setRetry(true);var success=this.processFlow(FLOW_FORWARD,true,walk,false);this.setRetry(false);if(success)
{delReq=walk.at.getID();}}
else
{delReq=from.getID();}}}}
else
{sclog("ChoiceSequencingRequest [SB.2.9]","seq");var target=this.getActivity(iRequest);if(target!=null)
{var process=true;var parent=target.getParent();if(!target.getIsSelected())
{process=false;}
if(process)
{var walk=target.getParent();while(walk!=null)
{var hideRules=walk.getPreSeqRules();var result=null;if(hideRules!=null)
{result=hideRules.evaluate(RULE_TYPE_HIDDEN,walk,false);}
if(result!=null)
{walk=null;process=false;}
else
{walk=walk.getParent();}}}
if(process)
{if(parent!=null)
{if(!parent.getControlModeChoice())
{process=false;}}}
var common=this.mSeqTree.getRoot();if(process)
{if(from!=null)
{common=this.findCommonAncestor(from,target);if(common==null)
{process=false;}}
else
{from=common;}
if(from==target)
{}
else if(from.getParent()==target.getParent())
{var dir=FLOW_FORWARD;if(target.getActiveOrder()<from.getActiveOrder())
{dir=FLOW_BACKWARD;}
var walk=from;while(walk!=target&&process)
{process=this.evaluateChoiceTraversal(dir,walk);if(dir==FLOW_FORWARD)
{walk=walk.getNextSibling(false);}
else
{walk=walk.getPrevSibling(false);}}}
else if(from==common)
{var walk=target.getParent();while(walk!=from&&process)
{process=this.evaluateChoiceTraversal(FLOW_FORWARD,walk);if(process)
{if(!walk.getIsActive()&&walk.getPreventActivation())
{process=false;continue;}}
walk=walk.getParent();}
if(process)
{process=this.evaluateChoiceTraversal(FLOW_FORWARD,walk);}}
else if(target==common)
{var walk=from.getParent();while(walk!=target&&process)
{process=walk.getControlModeChoiceExit();walk=walk.getParent();}}
else
{var con=null;var walk=from.getParent();while(walk!=common&&process)
{process=walk.getControlModeChoiceExit();if(process&&con==null)
{if(walk.getConstrainChoice())
{con=walk;}}
walk=walk.getParent();}
if(process&&con!=null)
{var walkCon=new Walk();walkCon.at=con;if(target.getCount()>con.getCount())
{this.processFlow(FLOW_FORWARD,false,walkCon,true);}
else
{this.processFlow(FLOW_BACKWARD,false,walkCon,true);}
if(target.getParent()!=walkCon.at&&target!=walkCon.at)
{process=false;}}
walk=target.getParent();while(walk!=common&&process)
{process=this.evaluateChoiceTraversal(FLOW_FORWARD,walk);if(process)
{if(!walk.getIsActive()&&walk.getPreventActivation())
{process=false;continue;}}
walk=walk.getParent();}
if(process)
{process=this.evaluateChoiceTraversal(FLOW_FORWARD,walk);}}
if(process)
{if(target.getChildren(false)!=null)
{var walk=new Walk();walk.at=target;var success=this.processFlow(FLOW_FORWARD,true,walk,false);if(success)
{delReq=walk.at.getID();}
else
{if(this.mSeqTree.getCurrentActivity()!=null&&common!=null)
{this.terminateDescendentAttempts(common);common=this.endAttempt(common,false);this.mSeqTree.setCurrentActivity(target);this.mSeqTree.setFirstCandidate(target);}}}
else
{delReq=target.getID();}}}}
else
{}}
return delReq;},findCommonAncestor:function(iFrom,iTo)
{var ancestor=null;var done=false;var stepFrom=null;if(iFrom==null||iTo==null)
{done=true;}
else
{if(!iFrom.hasChildren(false))
{stepFrom=iFrom.getParent();}
else
{stepFrom=iFrom;}
if(!iTo.hasChildren(false))
{iTo=iTo.getParent();}}
while(!done)
{var success=this.isDescendent(stepFrom,iTo);if(success)
{ancestor=stepFrom;done=true;continue;}
if(!done)
{stepFrom=stepFrom.getParent();}}
return ancestor;},isDescendent:function(iRoot,iTarget)
{var found=false;if(iRoot==null)
{}
else if(iRoot==this.mSeqTree.getRoot())
{found=true;}
else if(iRoot!=null&&iTarget!=null)
{while(iTarget!=null&&!found)
{if(iTarget==iRoot)
{found=true;}
iTarget=iTarget.getParent();}}
return found;},walkTree:function(iDirection,iPrevDirection,iEnter,iFrom,iControl)
{sclog("FlowTreeTraversalSub [SB.2.1]","seq");var next=null;var parent=null;var direction=iDirection;var reversed=false;var done=false;var endSession=false;if(iFrom==null)
{endSession=true;done=true;}
else
{parent=iFrom.getParent();}
if(!done&&parent!=null)
{if(iPrevDirection==FLOW_BACKWARD)
{if(iFrom.getNextSibling(false)==null)
{direction=FLOW_BACKWARD;iFrom=parent.getChildren(false)[0];reversed=true;}}}
if(!done&&direction==FLOW_FORWARD)
{if(iFrom.getID()==this.mSeqTree.getLastLeaf())
{done=true;endSession=true;}
if(!done)
{if(!iFrom.hasChildren(false)||!iEnter)
{next=iFrom.getNextSibling(false);if(next==null)
{var walk=this.walkTree(direction,FLOW_NONE,false,parent,iControl);next=walk.at;endSession=walk.endSession;}}
else
{next=iFrom.getChildren(false)[0];}}}
else if(!done&&direction==FLOW_BACKWARD)
{if(parent!=null)
{if(!iFrom.hasChildren(false)||!iEnter)
{if(iControl&&!reversed)
{if(parent.getControlForwardOnly())
{done=true;}}
if(!done)
{next=iFrom.getPrevSibling(false);if(next==null)
{var walk=this.walkTree(direction,FLOW_NONE,false,parent,iControl);next=walk.at;endSession=walk.endSession;}}}
else
{if(iFrom.getControlForwardOnly())
{next=iFrom.getChildren(false)[0];direction=FLOW_FORWARD;}
else
{var size=iFrom.getChildren(false).length;next=iFrom.getChildren(false)[size-1];}}}}
var walk=new Walk();walk.at=next;walk.direction=direction;walk.endSession=endSession;return walk;},walkActivity:function(iDirection,iPrevDirection,ioFrom)
{sclog("FlowActivityTraversalSub [SB.2.]","seq");var deliver=true;var parent=ioFrom.at.getParent();if(parent!=null)
{if(!parent.getControlModeFlow())
{deliver=false;}}
else
{deliver=false;}
if(deliver)
{var result=null;var skippedRules=ioFrom.at.getPreSeqRules();if(skippedRules!=null)
{result=skippedRules.evaluate(RULE_TYPE_SKIPPED,ioFrom.at,false);}
if(result!=null)
{var walk=this.walkTree(iDirection,iPrevDirection,false,ioFrom.at,true);if(walk.at==null)
{deliver=false;}
else
{ioFrom.at=walk.at;if(iPrevDirection==FLOW_BACKWARD&&walk.direction==FLOW_BACKWARD)
{return this.walkActivity(FLOW_BACKWARD,FLOW_NONE,ioFrom);}
else
{return this.walkActivity(iDirection,iPrevDirection,ioFrom);}}}
else
{if(!this.checkActivity(ioFrom.at))
{if(ioFrom.at.hasChildren(false))
{var walk=this.walkTree(iDirection,FLOW_NONE,true,ioFrom.at,true);if(walk.at!=null)
{ioFrom.at=walk.at;if(iDirection==FLOW_BACKWARD&&walk.direction==FLOW_FORWARD)
{deliver=this.walkActivity(FLOW_FORWARD,FLOW_BACKWARD,ioFrom);}
else
{deliver=this.walkActivity(iDirection,FLOW_NONE,ioFrom);}}
else
{deliver=false;}}}
else
{deliver=false;}}}
return deliver;},processFlow:function(iDirection,iEnter,ioFrom,iConChoice)
{sclog("FlowSub [SB.2.3]","seq");var success=true;var candidate=ioFrom.at;if(candidate!=null)
{var walk=this.walkTree(iDirection,FLOW_NONE,iEnter,candidate,!iConChoice);if(!iConChoice&&walk.at!=null)
{ioFrom.at=walk.at;success=this.walkActivity(iDirection,FLOW_NONE,ioFrom);}
else
{if(iConChoice)
{ioFrom.at=walk.at;}
success=false;}
if(walk.at==null&&walk.endSession)
{this.terminateDescendentAttempts(this.mSeqTree.getRoot());this.mEndSession=true;success=false;}}
else
{success=false;}
return success;},evaluateChoiceTraversal:function(iDirection,iAt)
{sclog("ChoiceActivityTraversalSub [SB.2.4]","seq");var success=true;if(iAt!=null)
{if(true)
{if(iDirection==FLOW_FORWARD)
{var stopTrav=iAt.getPreSeqRules();var result=null;if(stopTrav!=null)
{result=stopTrav.evaluate(RULE_TYPE_FORWARDBLOCK,iAt,false);}
if(result!=null)
{success=false;}}
else if(iDirection==FLOW_BACKWARD)
{var parent=iAt.getParent();if(parent!=null)
{success=!parent.getControlForwardOnly();}}
else
{success=false;}}
else
{success=false;}}
else
{success=false;}
return success;},doDeliveryRequest:function(iTarget,iTentative,oLaunch)
{sclog("DeliveryRequest [DB.1.1]","seq");var deliveryOK=true;var act=this.getActivity(iTarget);if(act==null)
{deliveryOK=false;if(!iTentative)
{if(this.mExitCourse)
{oLaunch.mSeqNonContent=LAUNCH_COURSECOMPLETE;}
else
{if(this.mEndSession)
{oLaunch.mSeqNonContent=LAUNCH_EXITSESSION;}
else
{oLaunch.mSeqNonContent=LAUNCH_SEQ_BLOCKED;}}}}
if(deliveryOK&&act.hasChildren(false))
{deliveryOK=false;oLaunch.mSeqNonContent=LAUNCH_ERROR;oLaunch.mEndSession=this.mEndSession;}
else if(deliveryOK)
{var ok=true;while(act!=null&&ok)
{ok=!this.checkActivity(act);if(ok)
{act=act.getParent();}}
if(!ok)
{deliveryOK=false;oLaunch.mSeqNonContent=LAUNCH_NOTHING;}}
if(!iTentative)
{if(deliveryOK)
{this.contentDelivery(iTarget,oLaunch);this.validateRequests();}
else
{oLaunch.mEndSession=this.mEndSession||this.mExitCourse;if(!oLaunch.mEndSession)
{this.validateRequests();oLaunch.mNavState=this.mSeqTree.getValidRequests();}}}
return deliveryOK;},contentDelivery:function(iTarget,oLaunch)
{sclog("ContentDeliveryEnvironment [DB.2]","seq");var target=this.getActivity(iTarget);var done=false;if(target==null)
{oLaunch.mSeqNonContent=LAUNCH_ERROR;oLaunch.mEndSession=this.mEndSession;done=true;}
var cur=this.mSeqTree.getFirstCandidate();if(cur!=null&&done==false)
{if(cur.getIsActive()==true)
{oLaunch.mSeqNonContent=LAUNCH_ERROR;oLaunch.mEndSession=this.mEndSession;done=true;}}
if(done==false)
{this.clearSuspendedActivity(target);this.terminateDescendentAttempts(target);var begin=new Array();var walk=target;while(walk!=null)
{begin[begin.length]=walk;walk=walk.getParent();}
if(begin.length>0)
{for(var i=begin.length-1;i>=0;i--)
{walk=begin[i];if(!walk.getIsActive())
{if(walk.getIsTracked())
{if(walk.getIsSuspended())
{walk.setIsSuspended(false);}
else
{walk.incrementAttempt();}}
walk.setIsActive(true);}}}
this.mSeqTree.setCurrentActivity(target);this.mSeqTree.setFirstCandidate(target);oLaunch.mEndSession=this.mEndSession;oLaunch.mActivityID=iTarget;oLaunch.mResourceID=target.getResourceID();oLaunch.mStateID=target.getStateID();if(oLaunch.mStateID==null)
{oLaunch.mStateID=iTarget;}
oLaunch.mNumAttempt=target.getNumAttempt()+
target.getNumSCOAttempt();oLaunch.mMaxTime=target.getAttemptAbDur();var services=new Object();var test=null;walk=target;while(walk!=null)
{var curSet=walk.getAuxResources();if(curSet!=null)
{for(var i=0;i<curSet.length;i++)
{var res=null;res=curSet[i];test=services[res.mType];if(test==null)
{services[res.mType]=res;}}}
walk=walk.getParent();}
if(services.length>0)
{oLaunch.mServices=services;}}
this.validateRequests();oLaunch.mNavState=this.mSeqTree.getValidRequests();if(oLaunch.mSeqNonContent!=null)
{oLaunch.mNavState.mContinueExit=false;}},clearSuspendedActivity:function(iTarget)
{sclog("ClearSuspendedActivitySub [DB.2.1]","seq");var act=this.mSeqTree.getSuspendAll();if(iTarget==null)
{act=null;}
if(act!=null)
{if(iTarget!=act)
{var common=this.findCommonAncestor(iTarget,act);while(act!=common)
{act.setIsSuspended(false);var children=act.getChildren(false);if(children!=null)
{var done=false;for(var i=0;i<children.length&&!done;i++)
{var lookAt=children[i];if(lookAt.getIsSuspended())
{act.setIsSuspended(true);done=true;}}}
act=act.getParent();}}
var temp=null;this.mSeqTree.setSuspendAll(temp);}},evaluateLimitConditions:function(iTarget)
{sclog("LimitConditionsCheck [UP.1]","seq");var disabled=false;if(!iTarget.getIsActive()&&!iTarget.getIsSuspended())
{if(iTarget.getAttemptLimitControl())
{disabled=iTarget.getNumAttempt()>=iTarget.getAttemptLimit();}}
return disabled;},terminateDescendentAttempts:function(iTarget)
{sclog("TerminateDescendentAttempts [UP.3]","seq");var cur=this.mSeqTree.getFirstCandidate();if(cur!=null)
{var common=this.findCommonAncestor(cur,iTarget);var walk=cur;while(walk!=common)
{walk=this.endAttempt(walk,false);walk=walk.getParent();}}},endAttempt:function(iTarget,iTentative)
{sclog("EndAttempt [UP.4]","seq");if(iTarget!=null)
{var children=iTarget.getChildren(false);if(children==null&&iTarget.getIsTracked())
{if(!iTarget.getIsSuspended())
{if(!iTarget.getSetCompletion())
{if(!iTarget.getProgressStatus(false))
{iTarget.setProgress(TRACK_COMPLETED);}}
if(!iTarget.getSetObjective())
{if(!iTarget.getObjStatus(false,true))
{iTarget.setObjSatisfied(TRACK_SATISFIED);}}}}
else if(children!=null)
{if(!iTentative)
{iTarget.setIsSuspended(false);for(var i=0;i<children.length;i++)
{var act=children[i];if(act.getIsSuspended())
{iTarget.setIsSuspended(true);break;}}
if(!iTarget.getIsSuspended())
{if(iTarget.getSelectionTiming()==TIMING_EACHNEW)
{iTarget=this.doSelection(iTarget);iTarget.setSelection(true);}
if(iTarget.getRandomTiming()==TIMING_EACHNEW)
{iTarget=this.doRandomize(iTarget);iTarget.setRandomized(true);}}}}
if(!iTentative)
{iTarget.setIsActive(false);if(iTarget.getIsTracked())
{iTarget.triggerObjMeasure();}
this.invokeRollup(iTarget,null);}}
return iTarget;},checkActivity:function(iTarget)
{sclog("CheckActivity [UP.5]","seq");var disabled=false;var result=null;var disabledRules=iTarget.getPreSeqRules();if(disabledRules!=null)
{result=disabledRules.evaluate(RULE_TYPE_DISABLED,iTarget,false);}
if(result!=null)
{disabled=true;}
if(!disabled)
{disabled=this.evaluateLimitConditions(iTarget);}
return disabled;},setRetry:function(iRetry)
{this.mRetry=false;},getChoiceSet:function(iOldTOC,oNewTOC)
{var set=null;var lastLeaf=null;if(iOldTOC!=null)
{var temp=null;set=new Object();for(var i=iOldTOC.length-1;i>=0;i--)
{temp=iOldTOC[i];if(temp.mDepth==-1)
{if(temp.mIsSelectable)
{set[temp.mID]=temp;}}
else if(temp.mIsVisible)
{set[temp.mID]=temp;oNewTOC[oNewTOC.length]=temp;}
if(lastLeaf==null)
{if(temp.mLeaf&&temp.mIsEnabled)
{lastLeaf=temp.mID;}}}}
if(lastLeaf!=null)
{this.mSeqTree.setLastLeaf(lastLeaf);}
if(set!=null)
{var empty=true;for(k in set)
{empty=false;}
if(empty)
{set=null;}}
if(oNewTOC.length==1)
{var temp=oNewTOC[0];if(!temp.mIsEnabled)
{oNewTOC.splice(0,1);}
else if(!temp.mLeaf)
{oNewTOC.splice(0,1);}}
return set;},getTOC:function(iStart)
{var toc=new Array();var temp=null;var done=false;if(this.mSeqTree==null)
{done=true;}
var walk=iStart;var depth=0;var parentTOC=-1;var lookAt=new Array();var flatTOC=new Array();var next=false;var include=false;var collapse=false;if(walk==null)
{walk=this.mSeqTree.getRoot();}
var cur=this.mSeqTree.getFirstCandidate();var curIdx=-1;if(cur==null)
{cur=this.mSeqTree.getCurrentActivity();}
while(!done)
{include=false;collapse=false;next=false;if(walk.getParent()!=null)
{if(walk.getParent().getControlModeChoice())
{include=true;}}
else
{include=true;}
if(include)
{var hiddenRules=walk.getPreSeqRules();var result=null;if(hiddenRules!=null)
{result=hiddenRules.evaluate(RULE_TYPE_HIDDEN,walk,false);}
if(result!=null)
{include=false;collapse=true;}
else
{if(walk.getPreventActivation()&&!walk.getIsActive()&&walk.hasChildren(true))
{if(cur!=null)
{if(walk!=cur&&cur.getParent()!=walk)
{include=false;}}
else
{if(walk.hasChildren(true))
{include=false;}}}}}
if(include)
{var parent=walk.getParent();temp=new ADLTOC();temp.mCount=walk.getCount();temp.mTitle=walk.getTitle();temp.mDepth=depth;temp.mIsVisible=walk.getIsVisible();temp.mIsEnabled=!this.checkActivity(walk);if(temp.mIsEnabled)
{if(walk.getAttemptLimitControl())
{if(walk.getAttemptLimit()==0)
{temp.mIsSelectable=false;}}}
temp.mID=walk.getID();if(walk.getParent()!=null)
{temp.mInChoice=walk.getParent().getControlModeChoice();}
else
{temp.mInChoice=true;}
if(cur!=null)
{if(temp.mID==cur.getID())
{temp.mIsCurrent=true;curIdx=toc.length;}}
temp.mLeaf=!walk.hasChildren(false);temp.mParent=parentTOC;toc[toc.length]=temp;}
else
{temp=new ADLTOC();temp.mCount=walk.getCount();temp.mTitle=walk.getTitle();temp.mIsVisible=walk.getIsVisible();temp.mIsEnabled=!this.checkActivity(walk);if(temp.mIsEnabled)
{if(walk.getAttemptLimitControl())
{if(walk.getAttemptLimit()==0)
{temp.mIsSelectable=false;}}}
temp.mDepth=-(depth);temp.mID=walk.getID();temp.mIsSelectable=false;temp.mLeaf=(walk.getChildren(false)==null);temp.mParent=parentTOC;if(collapse)
{temp.mIsVisible=false;}
toc[toc.length]=temp;}
flatTOC[flatTOC.length]=walk;if(walk.hasChildren(false))
{if(walk.getParent()!=null)
{lookAt[lookAt.length]=walk;}
walk=walk.getChildren(false)[0];parentTOC=toc.length-1;depth++;next=true;}
if(!next)
{walk=walk.getNextSibling(false);temp=toc[toc.length-1];parentTOC=temp.mParent;while(walk==null&&!done)
{if(lookAt.length>0)
{walk=lookAt[lookAt.length-1];lookAt.splice(lookAt.length-1,1);depth--;temp=toc[parentTOC];while(!temp.mID==walk.getID())
{parentTOC=temp.mParent;temp=toc[parentTOC];}
walk=walk.getNextSibling(false);}
else
{done=true;}}
if(walk!=null)
{parentTOC=temp.mParent;}}}
var hidden=-1;var prevented=-1;for(var i=0;i<toc.length;i++)
{var tempAct=flatTOC[i];var tempTOC=toc[i];var checkDepth=(tempTOC.mDepth>=0)?tempTOC.mDepth:(-tempTOC.mDepth);if(hidden!=-1)
{if(checkDepth<=hidden)
{hidden=-1;}
else
{tempTOC.mDepth=-(depth);tempTOC.mIsSelectable=false;tempTOC.mIsVisible=false;}}
if(hidden==-1)
{var hiddenRules=tempAct.getPreSeqRules();var result=null;if(hiddenRules!=null)
{result=hiddenRules.evaluate(RULE_TYPE_HIDDEN,tempAct,false);}
if(result!=null)
{hidden=-tempTOC.mDepth;prevented=-1;}
else
{if(prevented!=-1)
{if(checkDepth<=prevented)
{prevented=-1;}
else
{tempTOC.mDepth=-1;tempTOC.mIsSelectable=false;}}
else
{if(tempAct.getPreventActivation()&&!tempAct.getIsActive()&&tempAct.hasChildren(true))
{if(cur!=null)
{if(tempAct!=cur&&cur.getParent()!=tempAct)
{include=false;prevented=(tempTOC.mDepth>0)?tempTOC.mDepth:-tempTOC.mDepth;temp.mDepth=-1;temp.mIsSelectable=false;}}}}}}}
var noExit=null;if(this.mSeqTree.getFirstCandidate()!=null)
{walk=this.mSeqTree.getFirstCandidate().getParent();}
else
{walk=null;}
while(walk!=null&&noExit==null)
{if(walk.getParent()!=null)
{if(!walk.getControlModeChoiceExit())
{noExit=walk;}}
walk=walk.getParent();}
if(noExit!=null)
{depth=-1;for(var i=0;i<toc.length;i++)
{temp=toc[i];if(temp.mID==noExit.getID())
{depth=(temp.mDepth>0)?temp.mDepth:-temp.mDepth;temp.mDepth=-1;temp.mIsSelectable=false;}
else if(depth==-1)
{temp.mDepth=-1;temp.mIsSelectable=false;}
else if(((temp.mDepth>0)?temp.mDepth:-temp.mDepth)<=depth)
{depth=-1;temp.mDepth=-1;temp.mIsSelectable=false;}}}
temp=toc[0];var root=this.mSeqTree.getRoot();if(!root.getControlModeChoiceExit())
{temp.mIsSelectable=false;}
var con=null;if(this.mSeqTree.getFirstCandidate()!=null)
{walk=this.mSeqTree.getFirstCandidate().getParent();}
else
{walk=null;}
while(walk!=null&&con==null)
{if(walk.getConstrainChoice())
{con=walk;}
walk=walk.getParent();}
if(con!=null)
{var forwardAct=-1;var backwardAct=-1;var list=null;var walkCon=new Walk();walkCon.at=con;this.processFlow(FLOW_FORWARD,false,walkCon,true);if(walkCon.at==null)
{walkCon.at=con;}
var lookFor="";list=walkCon.at.getChildren(false);if(list!=null)
{var size=list.length;lookFor=(list[size-1]).getID();}
else
{lookFor=walkCon.at.getID();}
for(var j=0;j<toc.length;j++)
{temp=toc[j];if(temp.mID==lookFor)
{forwardAct=j;break;}}
walkCon.at=con;this.processFlow(FLOW_BACKWARD,false,walkCon,true);if(walkCon.at==null)
{walkCon.at=con;}
lookFor=walkCon.at.getID();for(var j=0;j<toc.length;j++)
{temp=toc[j];if(temp.mID==lookFor)
{backwardAct=j;break;}}
temp=toc[forwardAct];if(!temp.mLeaf)
{var idx=forwardAct;var foundLeaf=false;while(!foundLeaf)
{for(var i=toc.length-1;i>idx;i--)
{temp=toc[i];if(temp.mParent==idx)
{idx=i;foundLeaf=temp.mLeaf;break;}}}
if(idx!=toc.length)
{forwardAct=idx;}}
var idx=(toc[backwardAct]).mParent;var childID=(toc[backwardAct]).mID;var avalParent=-1;while(idx!=-1)
{temp=toc[idx];if(!temp.mIsSelectable||!temp.mIsEnabled)
{break;}
var check=this.mSeqTree.getActivity(temp.mID);if(check.getControlModeFlow())
{if((check.getChildren(false)[0]).getID()==childID)
{childID=(toc[idx]).mID;avalParent=idx;idx=(toc[avalParent]).mParent;}
else
{break;}}
else
{break;}}
if(avalParent!=-1&&avalParent<backwardAct)
{backwardAct=avalParent;}
for(var i=0;i<toc.length;i++)
{temp=toc[i];if(i<backwardAct||i>forwardAct)
{temp.mIsSelectable=false;}}}
if(toc!=null)
{depth=-1;for(var i=0;i<toc.length;i++)
{temp=toc[i];if(depth!=-1)
{if(depth>=((temp.mDepth>0)?temp.mDepth:-temp.mDepth))
{depth=-1;}
else
{temp.mIsEnabled=false;temp.mIsSelectable=false;}}
if(!temp.mIsEnabled&&depth==-1)
{depth=(temp.mDepth>0)?temp.mDepth:-temp.mDepth;}}}
if(toc!=null&&curIdx!=-1)
{var par=(toc[curIdx]).mParent;var idx;if(cur.getParent()!=null&&cur.getParent().getControlForwardOnly())
{idx=curIdx-1;temp=toc[idx];while(temp.mParent==par)
{temp.mIsSelectable=false;idx--;temp=toc[idx];}}
idx=curIdx;var blocked=false;while(idx<toc.length)
{temp=toc[idx];if(temp.mParent==par)
{if(!blocked)
{var stopTrav=this.getActivity(temp.mID).getPreSeqRules();var result=null;if(stopTrav!=null)
{result=stopTrav.evaluate(RULE_TYPE_FORWARDBLOCK,this.getActivity(temp.mID),false);}
blocked=(result!=null);}
else
{temp.mIsSelectable=false;}}
idx++;}}
if(toc!=null&&curIdx!=-1)
{var curParent=(toc[curIdx]).mParent;var idx=toc.length-1;temp=toc[idx];while(temp.mParent!=-1&&temp.mParent!=curParent)
{temp=toc[temp.mParent];var stopTrav=this.getActivity(temp.mID).getPreSeqRules();var result=null;if(stopTrav!=null)
{result=stopTrav.evaluate(RULE_TYPE_FORWARDBLOCK,this.getActivity(temp.mID),false);}
if(result!=null)
{var blocked=temp.mDepth;for(var i=idx;i<toc.length;i++)
{var tempTOC=toc[i];var checkDepth=((tempTOC.mDepth>=0)?tempTOC.mDepth:(-tempTOC.mDepth));if(checkDepth<=blocked)
{break;}
tempTOC.mIsSelectable=false;}}
idx--;temp=toc[idx];}}
for(var i=0;i<toc.length;i++)
{temp=toc[i];if(!temp.mLeaf)
{var from=this.getActivity(temp.mID);if(from.getControlModeFlow())
{var treeWalk=new Walk();treeWalk.at=from;var success=this.processFlow(FLOW_FORWARD,true,treeWalk,false);if(!success)
{temp.mIsSelectable=false;}}
else
{temp.mIsSelectable=false;}}}
for(var i=toc.length-1;i>=0;i--)
{temp=toc[i];if(temp.mIsCurrent&&temp.mInChoice)
{if(temp.mDepth<0)
{temp.mDepth=-temp.mDepth;}}
if(temp.mDepth>=0)
{while(temp.mParent!=-1)
{temp=toc[temp.mParent];if(temp.mDepth<0)
{temp.mDepth=-temp.mDepth;}}}
else if(temp.mIsVisible)
{temp.mDepth=-1;}}
for(var i=0;i<toc.length;i++)
{temp=toc[i];if(!temp.mIsVisible)
{temp.mDepth=-1;var parents=new Array();for(var j=i+1;j<toc.length;j++)
{temp=toc[j];if(temp.mParent==i&&temp.mDepth>0)
{temp.mDepth--;parents[parents.length]=j;}
else
{if(temp.mDepth!=-1)
{var idx=index_of(parents,temp.mParent);if(idx!=-1)
{temp.mDepth--;parents[parents.length]=j;}}}}}}
for(var i=0;i<toc.length;i++)
{temp=toc[i];if(temp.mIsCurrent&&!temp.mIsVisible)
{var parent=temp.mParent;while(parent!=-1)
{temp.mIsCurrent=false;temp=toc[parent];if(!temp.mIsVisible)
{parent=temp.mParent;}
else
{break;}}
temp.mIsCurrent=true;break;}}
return toc;}}
function ADLTOC()
{}
ADLTOC.prototype={mTitle:"",mDepth:-1,mCount:-1,mLeaf:false,mParent:-1,mInChoice:false,mIsEnabled:true,mIsVisible:true,mIsCurrent:false,mIsSelectable:true,mID:null}
var TRACK_UNKNOWN="unknown";var TRACK_SATISFIED="satisfied";var TRACK_NOTSATISFIED="notSatisfied";var TRACK_COMPLETED="completed";var TRACK_INCOMPLETE="incomplete";function ADLTracking(iObjs,iLearnerID,iScopeID)
{if(iObjs!=null)
{for(var i=0;i<iObjs.length;i++)
{obj=iObjs[i];objTrack=new SeqObjectiveTracking(obj,iLearnerID,iScopeID);if(this.mObjectives==null)
{this.mObjectives=new Object();}
this.mObjectives[obj.mObjID]=objTrack;if(obj.mContributesToRollup)
{this.mPrimaryObj=obj.mObjID;}}}
else
{def=new SeqObjective();def.mContributesToRollup=true;objTrack=new SeqObjectiveTracking(def,iLearnerID,iScopeID);if(this.mObjectives==null)
{this.mObjectives=new Object();}
this.mObjectives[def.mObjID]=objTrack;this.mPrimaryObj=def.mObjID;}}
ADLTracking.prototype={mDirtyPro:false,mObjectives:null,mPrimaryObj:"_primary_",mProgress:TRACK_UNKNOWN,mAttemptAbDur:null,mAttemptExDur:null,mAttempt:0,setDirtyObj:function()
{if(this.mObjectives!=null)
{for(var k in this.mObjectives)
{obj=this.mObjectives[k];obj.setDirtyObj();}}}}
function ADLValidRequests()
{}
ADLValidRequests.prototype={mStart:false,mResume:false,mContinue:false,mContinueExit:false,mPrevious:false,mSuspend:false,mChoice:null,mTOC:null}
function ilAugment(oSelf,oOther)
{if(oSelf==null)
{oSelf={};}
for(var i=1;i<arguments.length;i++)
{var o=arguments[i];if(typeof(o)!='undefined'&&o!=null)
{for(var j in o)
{oSelf[j]=o[j];}}}
return oSelf;}
function clone(what)
{for(i in what)
{if(typeof(what[i])=='object')
{this[i]=new cloneObject(what[i]);}
else
this[i]=what[i];}}
function index_of(haystack,needle,start)
{var index=-1;if(start==null)
{start=0;}
for(var j=start;j<haystack.length;j++)
{if(haystack[j]!=null&&haystack[j]==needle)
{index=j;break;}}
return index;}
function sclog(mess,type)
{elm=document.getElementById("ilLogPre");if(elm)
{elm.innerHTML=elm.innerHTML+mess+'<br />';}}
function sclogclear()
{elm=all("ilLogPre");if(elm)
{elm.innerHTML='';}}
function sclogdump(param,depth)
{if(!depth)
{depth=0;}
var pre=''
for(var j=0;j<depth;j++)
{pre=pre+'    ';}
switch(typeof param)
{case'boolean':if(param)sclog(pre+"true (boolean)");else sclog(pre+"false (boolean)");break;case'number':sclog(pre+param+' (number)');break;case'string':sclog(pre+param+' (string)');break;case'object':if(param===null)
{sclog(pre+'null');}
if(param instanceof Array)sclog(pre+'(Array) {');else if(param instanceof Object)sclog(pre+'(Object) {');for(var k in param)
{if(typeof param[k]!="function")
{sclog(pre+'['+k+'] => ');sclogdump(param[k],depth+1);}}
sclog(pre+'}');break;case'function':break;default:sclog(pre+"unknown: "+(typeof param));break;}}
var TIMING_NEVER="never";var TIMING_ONCE="once";var TIMING_EACHNEW="onEachNewAttempt";var TER_EXITALL="_EXITALL_";function SeqActivity()
{}
SeqActivity.prototype={mPreConditionRules:null,mPostConditionRules:null,mExitActionRules:null,mXML:null,mDepth:0,mCount:-1,mLearnerID:"_NULL_",mScopeID:null,mActivityID:null,mResourceID:null,mStateID:null,mTitle:null,mIsVisible:true,mOrder:-1,mActiveOrder:-1,mSelected:true,mParent:null,mIsActive:false,mIsSuspended:false,mChildren:null,mActiveChildren:null,mDeliveryMode:"normal",mControl_choice:true,mControl_choiceExit:true,mControl_flow:false,mControl_forwardOnly:false,mConstrainChoice:false,mPreventActivation:false,mUseCurObj:true,mUseCurPro:true,mMaxAttemptControl:false,mMaxAttempt:0,mAttemptAbDurControl:false,mAttemptAbDur:null,mAttemptExDurControl:false,mAttemptExDur:null,mActivityAbDurControl:false,mActivityAbDur:null,mActivityExDurControl:false,mActivityExDur:null,mBeginTimeControl:false,mBeginTime:null,mEndTimeControl:false,mEndTime:null,mAuxResources:null,mRollupRules:null,mActiveMeasure:true,mRequiredForSatisfied:ROLLUP_CONSIDER_ALWAYS,mRequiredForNotSatisfied:ROLLUP_CONSIDER_ALWAYS,mRequiredForCompleted:ROLLUP_CONSIDER_ALWAYS,mRequiredForIncomplete:ROLLUP_CONSIDER_ALWAYS,mObjectives:null,mObjMaps:null,mIsObjectiveRolledUp:true,mObjMeasureWeight:1.0,mIsProgressRolledUp:true,mSelectTiming:"never",mSelectStatus:false,mSelectCount:0,mSelection:false,mRandomTiming:"never",mReorder:false,mRandomized:false,mIsTracked:true,mContentSetsCompletion:false,mContentSetsObj:false,mCurTracking:null,mTracking:null,mNumAttempt:0,mNumSCOAttempt:0,mActivityAbDur_track:null,mActivityExDur_track:null,getControlModeChoice:function(){return this.mControl_choice;},setControlModeChoice:function(iChoice){this.mControl_choice=iChoice;},getControlModeChoiceExit:function(){return this.mControl_choiceExit;},setControlModeChoiceExit:function(val){this.mControl_choiceExit=val;},getControlModeFlow:function(){return this.mControl_flow;},setControlModeFlow:function(val){this.mControl_flow=val;},getControlForwardOnly:function(){return this.mControl_forwardOnly;},setControlForwardOnly:function(val){this.mControl_forwardOnly=val;},getConstrainChoice:function(){return this.mConstrainChoice;},setConstrainChoice:function(val){this.mConstrainChoice=val;},getPreventActivation:function(){return this.mPreventActivation;},setPreventActivation:function(val){this.mPreventActivation=val;},getUseCurObjective:function(){return this.mUseCurObj;},setUseCurObjective:function(val){this.mUseCurObj=val;},getUseCurProgress:function(){return this.mUseCurPro;},setUseCurProgress:function(val){this.mUseCurPro=val;},getPreSeqRules:function(){return this.mPreConditionRules;},setPreSeqRules:function(val){this.mPreConditionRules=val;},getExitSeqRules:function(){return this.mExitActionRules;},setExitSeqRules:function(val){this.mExitActionRules=val;},getPostSeqRules:function(){return this.mPostConditionRules;},setPostSeqRules:function(val){this.mPostConditionRules=val;},getAttemptLimitControl:function(){return this.mMaxAttemptControl;},getAttemptLimit:function(){return this.mMaxAttempt;},getAttemptAbDurControl:function(){return this.mAttemptAbDurControl;},getAttemptExDurControl:function(){return this.mAttemptExDurControl;},getActivityAbDurControl:function(){return this.mActivityAbDurControl;},getActivityExDurControl:function(){return this.mActivityExDurControl;},getBeginTimeLimitControl:function(){return this.mBeginTimeControl;},getBeginTimeLimit:function(){return this.mBeginTime;},getEndTimeLimitControl:function(){return this.mEndTimeControl;},getEndTimeLimit:function(){return this.mEndTime;},getAuxResources:function(){return this.mAuxResources;},setAuxResources:function(val){this.mAuxResources=val;},getRollupRules:function(){return this.mRollupRules;},setRollupRules:function(val){this.mRollupRules=val;},getSatisfactionIfActive:function(){return this.mActiveMeasure;},setSatisfactionIfActive:function(val){this.mActiveMeasure=val;},getRequiredForSatisfied:function(){return this.mRequiredForSatisfied;},setRequiredForSatisfied:function(val){this.mRequiredForSatisfied=val;},getRequiredForNotSatisfied:function(){return this.mRequiredForNotSatisfied;},setRequiredForNotSatisfied:function(val){this.mRequiredForNotSatisfied=val;},getRequiredForCompleted:function(){return this.mRequiredForCompleted;},setRequiredForCompleted:function(val){this.mRequiredForCompleted=val;},getRequiredForIncomplete:function(){return this.mRequiredForIncomplete;},setRequiredForIncomplete:function(val){this.mRequiredForIncomplete=val;},getObjectives:function(){return this.mObjectives;},getIsObjRolledUp:function(){return this.mIsObjectiveRolledUp;},setIsObjRolledUp:function(val){this.mIsObjectiveRolledUp=val;},getObjMeasureWeight:function(){return this.mObjMeasureWeight;},setObjMeasureWeight:function(val){this.mObjMeasureWeight=val;},getIsProgressRolledUp:function(){return this.mIsProgressRolledUp;},setIsProgressRolledUp:function(val){this.mIsProgressRolledUp=val;},getSelectionTiming:function(){return this.mSelectTiming;},setSelectionTiming:function(val){this.mSelectTiming=val;},getSelectStatus:function(){return this.mSelectStatus;},getRandomTiming:function(){return this.mRandomTiming;},getReorderChildren:function(){return this.mReorder;},setReorderChildren:function(val){this.mReorder=val;},getIsTracked:function(){return this.mIsTracked;},setIsTracked:function(val){this.mIsTracked=val;},getSetCompletion:function(){return this.mContentSetsCompletion;},setSetCompletion:function(val){this.mContentSetsCompletion=val;},getSetObjective:function(){return this.mContentSetsObj;},setSetObjective:function(val){this.mContentSetsObj=val;},getResourceID:function(){return this.mResourceID;},setResourceID:function(val){this.mResourceID=val;},getDeliveryMode:function(){return this.mDeliveryMode;},getStateID:function(){return this.mStateID;},setStateID:function(val){this.mStateID=val;},getID:function(){return this.mActivityID;},setID:function(val){this.mActivityID=val;},getTitle:function(){return this.mTitle;},setTitle:function(val){this.mTitle=val;},getXMLFragment:function(){return this.mXML;},setXMLFragment:function(val){this.mXML=val;},getLearnerID:function(){return this.mLearnerID;},setLearnerID:function(val){this.mLearnerID=val;},getIsSelected:function(){return this.mSelected;},setIsSelected:function(val){this.mSelected=val;},getScopeID:function(){return this.mScopeID;},setScopeID:function(val){this.mScopeID=val;},getIsVisible:function(){return this.mIsVisible;},setIsVisible:function(val){this.mIsVisible=val;},getIsActive:function(){return this.mIsActive;},setIsActive:function(val){this.mIsActive=val;},getIsSuspended:function(){return this.mIsSuspended;},setIsSuspended:function(val){this.mIsSuspended=val;},getNumSCOAttempt:function(){return this.mNumSCOAttempt;},getParent:function(){return this.mParent;},setParent:function(val){this.mParent=val;},getActiveOrder:function(){return this.mActiveOrder;},setActiveOrder:function(val){this.mActiveOrder=val;},getDepth:function(){return this.mDepth;},setDepth:function(val){this.mDepth=val;},getCount:function(){return this.mCount;},setCount:function(val){this.mCount=val;},getSelection:function(){return this.mSelection;},setSelection:function(val){this.mSelection=val;},getRandomized:function(){return this.mRandomized;},setRandomized:function(val){this.mRandomized=val;},setOrder:function(val){this.mOrder=val;},setAttemptLimit:function(iMaxAttempt)
{if(iMaxAttempt!=null)
{var value=iMaxAttempt;if(value>=0)
{this.mMaxAttemptControl=true;this.mMaxAttempt=value;}
else
{this.mMaxAttemptControl=false;this.mMaxAttempt=-1;}}},getAttemptAbDur:function()
{var dur=null;if(this.mAttemptAbDur!=null)
{dur=this.mAttemptAbDur.format(FORMAT_SCHEMA);}
return dur;},setAttemptAbDur:function(iDur)
{if(iDur!=null)
{this.mAttemptAbDurControl=true;this.mAttemptAbDur=new ADLDuration({iFormat:FORMAT_SCHEMA,iValue:iDur});}
else
{this.mAttemptAbDurControl=false;this.mAttemptAbDur=null;}},getAttemptExDur:function()
{var dur=null;if(this.mAttemptExDur!=null)
{dur=this.mAttemptExDur.format(FORMAT_SCHEMA);}
return dur;},setAttemptExDur:function(iDur)
{if(iDur!=null)
{this.mAttemptExDurControl=true;this.mAttemptExDur=new ADLDuration({iFormat:FORMAT_SCHEMA,iValue:iDur});}
else
{this.mAttemptExDurControl=false;}},getActivityAbDur:function()
{var dur=null;if(this.mActivityAbDur!=null)
{dur=this.mActivityAbDur.format(FORMAT_SCHEMA);}
return dur;},setActivityAbDur:function(iDur)
{if(iDur!=null)
{this.mActivityAbDurControl=true;this.mActivityAbDur=new ADLDuration({iFormat:FORMAT_SCHEMA,iValue:iDur});}
else
{this.mActivityAbDurControl=false;}},getActivityExDur:function()
{var dur=null;if(this.mActivityExDur!=null)
{dur=this.mActivityExDur.format(FORMAT_SCHEMA);}
return dur;},setActivityExDur:function(iDur)
{if(iDur!=null)
{this.mActivityExDurControl=true;this.mActivityExDur=new ADLDuration({iFormat:FORMAT_SCHEMA,iValue:iDur});}
else
{this.mActivityExDurControl=false;}},setBeginTimeLimit:function(iTime)
{if(iTime!=null)
{this.mBeginTimeControl=true;this.mBeginTime=iTime;}
else
{this.mBeginTimeControl=false;}},setEndTimeLimit:function(iTime)
{if(iTime!=null)
{this.mEndTimeControl=true;this.mEndTime=iTime;}
else
{this.mEndTimeControl=false;}},setObjectives:function(iObjs)
{this.mObjectives=iObjs;if(iObjs!=null)
{for(var i=0;i<iObjs.length;i++)
{obj=iObjs[i];if(obj.mMaps!=null)
{if(this.mObjMaps==null)
{this.mObjMaps=new Object();}
this.mObjMaps[obj.mObjID]=obj.mMaps;}}}},setSelectionTiming:function(iTiming)
{if(!(iTiming==TIMING_NEVER||iTiming==TIMING_ONCE||iTiming==TIMING_EACHNEW))
{this.mSelectTiming=TIMING_NEVER;}
else
{this.mSelectTiming=iTiming;}},getSelectCount:function()
{if(this.mChildren!=null)
{if(this.mSelectCount>=this.mChildren.length)
{this.mSelectTiming="never";this.mSelectCount=mChildren.length;}}
else
{this.mSelectStatus=false;this.mSelectCount=0;}
return this.mSelectCount;},setSelectCount:function(iCount)
{if(iCount>=0)
{this.mSelectStatus=true;this.mSelectCount=iCount;}
else
{this.mSelectStatus=false;}},setRandomTiming:function(iTiming)
{if(!(iTiming==TIMING_NEVER||iTiming==TIMING_ONCE||iTiming==TIMING_EACHNEW))
{this.mSelectTiming=TIMING_NEVER;}
else
{this.mRandomTiming=iTiming;}},setDeliveryMode:function(iDeliveryMode)
{if(iDeliveryMode=="browse"||iDeliveryMode=="review"||iDeliveryMode=="normal")
{this.mDeliveryMode=iDeliveryMode;}
else
{this.mDeliveryMode="normal";}},getActivityAttempted:function()
{return(this.mNumAttempt!=0);},getAttemptCompleted:function(iIsRetry)
{var progress=TRACK_UNKNOWN;if(this.mIsTracked)
{if(this.mCurTracking==null)
{track=new ADLTracking(this.mObjectives,this.mLearnerID,this.mScopeID);track.mAttempt=this.mNumAttempt;this.mCurTracking=track;}
if(!(this.mCurTracking.mDirtyPro==true&&iIsRetry==true))
{progress=this.mCurTracking.mProgress;}}
return(progress==TRACK_COMPLETED);},setProgress:function(iProgress)
{var statusChange=false;if(this.mIsTracked==true)
{if(iProgress==TRACK_UNKNOWN||iProgress==TRACK_COMPLETED||iProgress==TRACK_INCOMPLETE)
{if(this.mCurTracking==null)
{this.mCurTracking=new ADLTracking(this.mObjectives,this.mLearnerID,this.mScopeID);}
var prev=this.mCurTracking.mProgress;this.mCurTracking.mProgress=iProgress;statusChange=!(prev==iProgress);}}
return statusChange;},getProgressStatus:function(iIsRetry)
{var status=false;if(this.mIsTracked==true)
{if(this.mCurTracking!=null)
{if(!(this.mCurTracking.mDirtyPro==true&&iIsRetry==true))
{status=(this.mCurTracking.mProgress!=TRACK_UNKNOWN);}}}
return status;},getObjMeasureStatus:function(iIsRetry,iOptions)
{var iOptions=ilAugment({iObjID:null,iUseLocal:false},iOptions);var iObjID=iOptions.iObjID;var iUseLocal=iOptions.iUseLocal;var status=false;if(this.mIsTracked==true)
{if(this.mCurTracking==null)
{var track=new ADLTracking(this.mObjectives,this.mLearnerID,this.mScopeID);track.mAttempt=this.mNumAttempt;this.mCurTracking=track;}
if(this.mCurTracking!=null)
{if(iObjID==null)
{iObjID=this.mCurTracking.mPrimaryObj;}
obj=this.mCurTracking.mObjectives[iObjID];if(obj!=null)
{var result=null;result=obj.getObjMeasure(iIsRetry,iUseLocal);if(result!=TRACK_UNKNOWN)
{status=true;}}}}
return status;},clearObjMeasure:function(iOptions)
{var iOptions=ilAugment({iObjID:null},iOptions);var iObjID=iOptions.iObjID;var statusChange=false;if(this.mCurTracking!=null)
{if(iObjID==null)
{iObjID=this.mCurTracking.mPrimaryObj;}
obj=this.mCurTracking.mObjectives[iObjID];if(obj!=null)
{objD=obj.getObj();var affectSatisfaction=objD.mSatisfiedByMeasure;if(affectSatisfaction)
{affectSatisfaction=!objD.mContributesToRollup||(this.mActiveMeasure||!this.mIsActive);}
statusChange=obj.clearObjMeasure(affectSatisfaction);}}
return statusChange;},setObjMeasure:function(iMeasure,iOptions)
{var iOptions=ilAugment({iObjID:null},iOptions);var iObjID=iOptions.iObjID;var statusChange=false;if(this.mIsTracked)
{if(this.mCurTracking!=null)
{if(iObjID==null)
{iObjID=this.mCurTracking.mPrimaryObj;}
obj=this.mCurTracking.mObjectives[iObjID];if(obj!=null)
{var prev=obj.getObjStatus(false);var objD=obj.getObj();var affectSatisfaction=objD.mSatisfiedByMeasure;if(affectSatisfaction)
{affectSatisfaction=!objD.mContributesToRollup||(this.mActiveMeasure||!this.mIsActive);}
obj.setObjMeasure(iMeasure,affectSatisfaction);statusChange=(prev!=obj.getObjStatus(false));}}}
return statusChange;},getObjSatisfiedByMeasure:function()
{var byMeasure=false;if(this.mCurTracking!=null)
{var obj=this.mCurTracking.mObjectives[this.mCurTracking.mPrimaryObj];if(obj!=null)
{byMeasure=obj.getByMeasure();}}
return byMeasure;},getObjMinMeasure:function(iOptions)
{var iOptions=ilAugment({iObjID:null},iOptions);var iObjID=iOptions.iObjID;var minMeasure=-1.0;if(iObjID==null)
{iObjID=this.mCurTracking.mPrimaryObj;}
if(this.mObjectives!=null)
{for(var i=0;i<this.mObjectives.length;i++)
{var obj=this.mObjectives[i];if(iObjID==obj.mObjID)
{minMeasure=obj.mMinMeasure;}}}
return minMeasure;},getObjMeasure:function(iIsRetry,iOptions)
{var iOptions=ilAugment({iObjID:null},iOptions);var iObjID=iOptions.iObjID;var measure=0.0;if(this.mIsTracked)
{if(this.mCurTracking==null)
{var track=new ADLTracking(this.mObjectives,this.mLearnerID,this.mScopeID);track.mAttempt=this.mNumAttempt;this.mCurTracking=track;}
if(iObjID==null)
{iObjID=this.mCurTracking.mPrimaryObj;}
if(this.mCurTracking!=null)
{var obj=this.mCurTracking.mObjectives[iObjID];if(obj!=null)
{var result=null;result=obj.getObjMeasure(iIsRetry);if(result!=TRACK_UNKNOWN)
{measure=parseFloat(result);}}}}
return measure;},triggerObjMeasure:function()
{var measure=0.0;if(this.mIsTracked)
{if(this.mCurTracking==null)
{var track=new ADLTracking(this.mObjectives,this.mLearnerID,this.mScopeID);track.mAttempt=mNumAttempt;this.mCurTracking=track;}
if(this.mCurTracking!=null)
{var obj=this.mCurTracking.mObjectives[this.mCurTracking.mPrimaryObj];if(obj!=null)
{if(obj.getObj().mSatisfiedByMeasure)
{var result=null;result=obj.getObjMeasure(false);if(result!=TRACK_UNKNOWN)
{measure=parseFloat(result);obj.setObjMeasure(measure,true);}
else
{obj.clearObjMeasure(true);}}}}}},getObjStatus:function(iIsRetry,iOptions)
{var iOptions=ilAugment({iObjID:null,iUseLocal:false},iOptions);var iObjID=iOptions.iObjID;var iUseLocal=iOptions.iUseLocal;var status=false;if(this.mIsTracked==true)
{if(this.mCurTracking==null)
{var track=new ADLTracking(this.mObjectives,this.mLearnerID,this.mScopeID);track.mAttempt=this.mNumAttempt;this.mCurTracking=track;}
else if(this.mCurTracking!=null)
{if(iObjID==null){iObjID=this.mCurTracking.mPrimaryObj;}
var obj=this.mCurTracking.mObjectives[iObjID];if(obj!=null)
{var objData=obj.getObj();if(objData.mSatisfiedByMeasure==false||this.mActiveMeasure==true||this.mIsActive==false)
{var result=null;result=obj.getObjStatus(iIsRetry);if(result!=TRACK_UNKNOWN)
{status=true;}}}}}
return status;},setObjSatisfied:function(iStatus,iOptions)
{var iOptions=ilAugment({iObjID:null},iOptions);var iObjID=iOptions.iObjID;var statusChange=false;if(this.mIsTracked)
{if(this.mCurTracking!=null)
{if(iObjID==null)
{iObjID=this.mCurTracking.mPrimaryObj;}
var obj=this.mCurTracking.mObjectives[iObjID];if(obj!=null)
{if(iStatus==TRACK_UNKNOWN||iStatus==TRACK_SATISFIED||iStatus==TRACK_NOTSATISFIED)
{var result=obj.getObjStatus(false);obj.setObjStatus(iStatus);statusChange=(result!=iStatus);}}}}
return statusChange;},getObjSatisfied:function(iIsRetry,iOptions)
{var iOptions=ilAugment({iObjID:null},iOptions);var iObjID=iOptions.iObjID;var status=false;if(this.mIsTracked)
{if(this.mCurTracking==null)
{var track=new ADLTracking(this.mObjectives,this.mLearnerID,this.mScopeID);track.mAttempt=this.mNumAttempt;this.mCurTracking=track;}
if(this.mCurTracking!=null)
{if(iObjID==null)
{iObjID=this.mCurTracking.mPrimaryObj;}
var obj=this.mCurTracking.mObjectives[iObjID];if(obj!=null)
{var objData=obj.getObj();if(!objData.mSatisfiedByMeasure||this.mActiveMeasure||!this.mIsActive)
{var result=null;result=obj.getObjStatus(iIsRetry);if(result==TRACK_SATISFIED)
{status=true;}}}}}
return status;},setCurAttemptExDur:function(iDur)
{if(this.mCurTracking!=null)
{this.mCurTracking.mAttemptAbDur=iDur;}},evaluateLimitConditions:function()
{var disabled=false;if(this.mCurTracking!=null)
{if(this.mMaxAttemptControl)
{if(this.mNumAttempt>=this.mMaxAttempt)
{disabled=true;}}
if(this.mActivityAbDurControl&&!disabled)
{if(this.mActivityAbDur.compare(this.mActivityAbDur_track)!=LT)
{disabled=true;}}
if(this.mActivityExDurControl&&!disabled)
{if(this.mActivityExDur.compare(this.mActivityExDur_track)!=LT)
{disabled=true;}}
if(this.mAttemptAbDurControl&&!disabled)
{if(this.mActivityAbDur.compare(this.mCurTracking.mAttemptAbDur)!=LT)
{disabled=true;}}
if(this.mAttemptExDurControl&&!disabled)
{if(this.mActivityExDur.compare(this.mCurTracking.mAttemptExDur)!=LT)
{disabled=true;}}
if(this.mBeginTimeControl&&!disabled)
{if(false)
{disabled=true;}}
if(this.mEndTimeControl&&!disabled)
{if(false)
{disabled=true;}}}
return disabled;},incrementSCOAttempt:function()
{this.mNumSCOAttempt++;},incrementAttempt:function()
{if(this.mCurTracking!=null)
{if(this.mTracking==null)
{this.mTracking=new Array();}
this.mTracking[this.mTracking.length]=this.mCurTracking;}
var track=new ADLTracking(this.mObjectives,this.mLearnerID,this.mScopeID);this.mNumAttempt++;track.mAttempt=this.mNumAttempt;this.mCurTracking=track;if(this.mActiveChildren!=null)
{for(var i=0;i<this.mActiveChildren.length;i++)
{var temp=this.mActiveChildren[i];if(this.mUseCurObj==true)
{temp.setDirtyObj();}
if(this.mUseCurPro==true)
{temp.setDirtyPro();}}}},setDirtyObj:function()
{if(this.mCurTracking!=null)
{this.mCurTracking.setDirtyObj();}
if(this.mActiveChildren!=null)
{for(var i=0;i<this.mActiveChildren.length;i++)
{var temp=this.mActiveChildren[i];if(this.mUseCurObj==true)
{temp.setDirtyObj();}}}},setDirtyPro:function()
{if(this.mCurTracking!=null)
{this.mCurTracking.mDirtyPro=true;}
if(this.mActiveChildren!=null)
{for(var i=0;i<this.mActiveChildren.length;i++)
{var temp=this.mActiveChildren[i];if(this.mUseCurPro==true)
{temp.setDirtyPro();}}}},resetNumAttempt:function()
{this.mNumAttempt=0;this.mCurTracking=null;this.mTracking=null;},getNumAttempt:function()
{var attempt=0;if(this.mIsTracked)
{attempt=this.mNumAttempt;}
return attempt;},getObjIDs:function(iObjID,iRead)
{if(iObjID==null)
{if(this.mCurTracking!=null)
{iObjID=this.mCurTracking.mPrimaryObj;}}
var objSet=new Array();var mapSet=new Array();if(this.mIsTracked)
{if(this.mObjMaps!=null)
{mapSet=this.mObjMaps[iObjID];if(mapSet!=null)
{for(var i=0;i<mapSet.length;i++)
{var map=mapSet[i];if(!iRead&&(map.mWriteStatus||map.mWriteMeasure))
{if(objSet==null)
{objSet=new Array();}
objSet[objSet.length]=map.mGlobalObjID;}
else if(iRead&&(map.mReadStatus||map.mReadMeasure))
{if(objSet==null)
{objSet=new Array();}
objSet[objSet.length]=map.mGlobalObjID;}}}}}
return objSet;},addChild:function(ioChild)
{if(this.mChildren==null)
{this.mChildren=new Array();}
this.mActiveChildren=this.mChildren;this.mChildren[mChildren.length]=ioChild;ioChild.setOrder(this.mChildren.length-1);ioChild.setActiveOrder(this.mChildren.length-1);ioChild.setParent(this);},setChildren:function(ioChildren,iAll)
{var walk=null;if(iAll)
{this.mChildren=ioChildren;this.mActiveChildren=ioChildren;for(var i=0;i<ioChildren.length;i++)
{walk=ioChildren[i];walk.setOrder(i);walk.setActiveOrder(i);walk.setParent(this);walk.setIsSelected(true);}}
else
{for(var i=0;i<this.mChildren.length;i++)
{walk=this.mChildren[i];walk.setIsSelected(false);}
this.mActiveChildren=ioChildren;for(var i=0;i<ioChildren.length;i++)
{walk=ioChildren[i];walk.setActiveOrder(i);walk.setIsSelected(true);walk.setParent(this);}}},getChildren:function(iAll)
{var result=null;if(iAll)
{result=this.mChildren;}
else
{result=this.mActiveChildren;}
return result;},hasChildren:function(iAll)
{var result=false;if(iAll)
{result=(this.mChildren!=null);}
else
{result=(this.mActiveChildren!=null);}
return result;},getNextSibling:function(iAll)
{var next=null;var target=-1;if(this.mParent!=null)
{if(iAll)
{target=this.mOrder+1;}
else
{target=this.mActiveOrder+1;}
if(target<this.mParent.getChildren(iAll).length)
{var all=this.mParent.getChildren(iAll);next=all[target];}}
return next;},getPrevSibling:function(iAll)
{var prev=null;var target=-1;if(this.mParent!=null)
{if(iAll)
{target=this.mOrder-1;}
else
{target=this.mActiveOrder-1;}
if(target>=0)
{var all=this.mParent.getChildren(iAll);prev=all[target];}}
return prev;},getParentID:function()
{if(this.mParent!=null)
{return this.mParent.mActivityID;}
return null;},getObjStatusSet:function()
{var objSet=null;if(this.mCurTracking==null)
{var track=new ADLTracking(this.mObjectives,this.mLearnerID,this.mScopeID);track.mAttempt=this.mNumAttempt;this.mCurTracking=track;}
if(this.mCurTracking.mObjectives!=null)
{objSet=new Array();for(var key in this.mCurTracking.mObjectives)
{if(key!="_primary_")
{var obj=this.mCurTracking.mObjectives[key];var objStatus=new ADLObjStatus();objStatus.mObjID=obj.getObjID();var measure=obj.getObjMeasure(false);objStatus.mHasMeasure=(measure!=TRACK_UNKNOWN);if(objStatus.mHasMeasure)
{objStatus.mMeasure=parseFloat(measure);}
objStatus.mStatus=obj.getObjStatus(false);objSet[objSet.length]=objStatus;}}}
if(objSet!=null)
{if(objSet.length==0)
{objSet=null;}}
return objSet;}}
function SeqActivityTree(iCourseID,iLearnerID,iScopeID,iRoot)
{this.mCourseID=iCourseID;this.mLearnerID=iLearnerID;this.mScopeID=iScopeID;this.mRoot=iRoot;}
SeqActivityTree.prototype={mRoot:null,mValidReq:null,mLastLeaf:null,mScopeID:null,mCourseID:null,mLearnerID:null,mCurActivity:null,mFirstCandidate:null,mSuspendAll:null,mActivityMap:null,mObjSet:null,mObjMap:null,mObjScan:false,getScopeID:function(){return this.mScopeID;},setRoot:function(iRoot){this.mRoot=iRoot;},getRoot:function(){return this.mRoot;},setLastLeaf:function(iLastLeaf){this.mLastLeaf=iLastLeaf;},getLastLeaf:function(){return this.mLastLeaf;},setValidRequests:function(iValidRequests){this.mValidReq=iValidRequests;},getValidRequests:function(){return this.mValidReq;},getCurrentActivity:function(){return this.mCurActivity;},setCurrentActivity:function(iCurrent){this.mCurActivity=iCurrent;},setFirstCandidate:function(iFirst){this.mFirstCandidate=iFirst;},setSuspendAll:function(iSuspendTarget){this.mSuspendAll=iSuspendTarget;},getSuspendAll:function(){return this.mSuspendAll;},getLearnerID:function(){return this.mLearnerID;},setCourseID:function(iCourseID){this.mCourseID=iCourseID;},getCourseID:function(){return this.mCourseID;},setLearnerID:function(iLearnerID)
{this.mLearnerID=iLearnerID;this.buildActivityMap();if(!(this.mActivityMap==null||iLearnerID==null))
{for(var act in this.mActivityMap)
{act.setLearnerID(iLearnerID);}}},setScopeID:function(iScopeID)
{this.mScopeID=iScopeID;if(this.mScopeID!=null)
{this.buildActivityMap();if(this.mActivityMap!=null)
{for(var act in this.mActivityMap)
{act.setScopeID(this.mScopeID);}}}},getFirstCandidate:function()
{if(this.mFirstCandidate==null)
{return this.mCurActivity;}
return this.mFirstCandidate;},getActivity:function(iActivityID)
{if(this.mActivityMap==null)
{this.buildActivityMap();}
var temp=null;if(iActivityID!=null)
{temp=this.mActivityMap[iActivityID];}
return temp;},getObjMap:function(iObjID)
{var actSet=null;if(!this.mObjScan)
{this.scanObjectives();if(this.mObjMap!=null)
{if(this.mObjMap.length==0)
{this.mObjMap=null;}}}
if(this.mObjMap!=null)
{actSet=this.mObjMap[iObjID];}
return actSet;},getGlobalObjectives:function()
{if(!this.mObjScan)
{this.scanObjectives();}
if(this.mObjSet!=null)
{if(this.mObjSet.length==0)
{this.mObjSet=null;}}
return this.mObjSet;},clearSessionState:function()
{this.mActivityMap=null;},setDepths:function()
{if(this.mRoot!=null)
{var walk=this.mRoot;var depth=0;var lookAt=new Array();var depths=new Array();while(walk!=null)
{if(walk.hasChildren(true))
{lookAt[lookAt.length]=walk;depths[depths.length]=(depth+1);}
walk.setDepth(depth);walk=walk.getNextSibling(true);if(walk==null)
{if(lookAt.length!=0)
{walk=lookAt[0];lookAt.splice(0,1)
depth=depths[0];depths.splice(0,1);temp=walk.getChildren(true);walk=temp[0];}}}}},setTreeCount:function()
{if(this.mRoot!=null)
{var walk=this.mRoot;var count=0;var lookAt=new Array();while(walk!=null)
{count++;walk.setCount(count);if(walk.hasChildren(true))
{lookAt[lookAt.length]=walk;walk=walk.getChildren(true)[0];}
else
{walk=walk.getNextSibling(true);}
while(lookAt.length!=0&&walk==null)
{walk=lookAt[0];lookAt.splice(0,1);walk=walk.getNextSibling(true);}}}},buildActivityMap:function()
{this.mActivityMap=new Object();if(this.mRoot!=null)
{this.addChildActivitiestoMap(this.mRoot);}},addChildActivitiestoMap:function(iNode)
{if(iNode!=null)
{var children=iNode.getChildren(true);var i=0;this.mActivityMap[iNode.getID()]=iNode;if(children!=null)
{for(i=0;i<children.length;i++)
{this.addChildActivitiestoMap(children[i]);}}}},scanObjectives:function()
{var walk=this.mRoot;var lookAt=new Array();while(walk!=null)
{if(walk.hasChildren(true))
{lookAt[lookAt.length]=walk;}
var objs=walk.getObjectives();if(objs!=null)
{for(var i=0;i<objs.length;i++)
{var obj=objs[i];if(obj.mMaps!=null)
{for(var j=0;j<obj.mMaps.length;j++)
{var map=obj.mMaps[j];var target=map.mGlobalObjID;if(this.mObjSet==null)
{this.mObjSet=new Array();this.mObjSet[0]=target;}
else
{var found=false;for(var k=0;k<this.mObjSet.length&&!found;k++)
{var id=this.mObjSet[k];found=(id==target);}
if(!found)
{this.mObjSet[this.mObjSet.length]=target;}}
if(map.mReadStatus||map.mReadMeasure)
{if(this.mObjMap==null)
{this.mObjMap=new Object();}
var actList=this.mObjMap[target];if(actList==null)
{actList=new Array();}
actList[actList.length]=walk.getID();this.mObjMap[target]=actList;}}}}}
walk=walk.getNextSibling(true);if(walk==null)
{if(lookAt.length!=0)
{walk=lookAt[0];lookAt.splice(0,1);walk=walk.getChildren(true)[0];}}}
this.mObjScan=true;}}
var SATISFIED="satisfied";var OBJSTATUSKNOWN="objectiveStatusKnown";var OBJMEASUREKNOWN="objectiveMeasureKnown";var OBJMEASUREGRTHAN="objectiveMeasureGreaterThan";var OBJMEASURELSTHAN="objectiveMeasureLessThan";var COMPLETED="completed";var PROGRESSKNOWN="activityProgressKnown";var ATTEMPTED="attempted";var ATTEMPTSEXCEEDED="attemptLimitExceeded";var TIMELIMITEXCEEDED="timeLimitExceeded";var OUTSIDETIME="outsideAvailableTimeRange";var ALWAYS="always";var NEVER="never";function SeqCondition()
{}
SeqCondition.prototype={mCondition:null,mNot:false,mObjID:null,mThreshold:0.0}
var EVALUATE_UNKNOWN=0;var EVALUATE_TRUE=1;var EVALUATE_FALSE=-1;var COMBINATION_ALL="all";var COMBINATION_ANY="any";function SeqConditionSet(iRollup)
{if(iRollup==true)
{this.mRollup=iRollup;}}
SeqConditionSet.prototype={mCombination:null,mConditions:null,mRetry:false,mRollup:false,evaluate:function(iThisActivity,iOptions)
{var iOptions=ilAugment({iIsRetry:this.mRetry},iOptions);var iIsRetry=iOptions.iIsRetry;mRetry=iIsRetry;var result=EVALUATE_UNKNOWN;if(iThisActivity!=null)
{if(this.mConditions!=null)
{if(this.mCombination==COMBINATION_ALL)
{result=EVALUATE_TRUE;for(var i=0;i<this.mConditions.length;i++)
{var thisEval=this.evaluateCondition(i,iThisActivity);if(thisEval!=EVALUATE_TRUE)
{result=thisEval;break;}}}
else if(this.mCombination==COMBINATION_ANY)
{result=EVALUATE_FALSE;for(var i=0;i<this.mConditions.length;i++)
{var thisEval=this.evaluateCondition(i,iThisActivity);if(thisEval==EVALUATE_TRUE)
{result=EVALUATE_TRUE;break;}
else if(thisEval==EVALUATE_UNKNOWN)
{result=EVALUATE_UNKNOWN;}}}}}
this.mRetry=false;return result;},evaluateCondition:function(iIndex,iTarget)
{var result=EVALUATE_UNKNOWN;if(iIndex<this.mConditions.length)
{var cond=this.mConditions[iIndex];if(cond.mCondition==ALWAYS)
{result=EVALUATE_TRUE;}
else if(cond.mCondition==NEVER)
{result=EVALUATE_FALSE;}
else if(cond.mCondition==SATISFIED)
{if(iTarget.getObjStatus(this.mRollup,{iObjID:cond.mObjID}))
{result=(iTarget.getObjSatisfied(this.mRollup,{iObjID:cond.mObjID}))?EVALUATE_TRUE:EVALUATE_FALSE;}
else
{result=EVALUATE_UNKNOWN;}}
else if(cond.mCondition==OBJSTATUSKNOWN)
{result=iTarget.getObjStatus(this.mRollup,{iObjID:cond.mObjID})?EVALUATE_TRUE:EVALUATE_FALSE;}
else if(cond.mCondition==OBJMEASUREKNOWN)
{result=iTarget.getObjMeasureStatus(this.mRollup,{iObjID:cond.mObjID})?EVALUATE_TRUE:EVALUATE_FALSE;}
else if(cond.mCondition==OBJMEASUREGRTHAN)
{if(iTarget.getObjMeasureStatus(this.mRollup,{iObjID:cond.mObjID}))
{result=(iTarget.getObjMeasure(this.mRollup,{iObjID:cond.mObjID})>cond.mThreshold)?EVALUATE_TRUE:EVALUATE_FALSE;}
else
{result=EVALUATE_UNKNOWN;}}
else if(cond.mCondition==OBJMEASURELSTHAN)
{if(iTarget.getObjMeasureStatus(this.mRollup,{iObjID:cond.mObjID}))
{result=(iTarget.getObjMeasure(this.mRollup,{iObjID:cond.mObjID})<cond.mThreshold)?EVALUATE_TRUE:EVALUATE_FALSE;}
else
{result=EVALUATE_UNKNOWN;}}
else if(cond.mCondition==COMPLETED)
{if(iTarget.getProgressStatus(this.mRollup))
{result=iTarget.getAttemptCompleted(this.mRollup)?EVALUATE_TRUE:EVALUATE_FALSE;}
else
{result=EVALUATE_UNKNOWN;}}
else if(cond.mCondition==PROGRESSKNOWN)
{result=iTarget.getProgressStatus(this.mRollup)?EVALUATE_TRUE:EVALUATE_FALSE;}
else if(cond.mCondition==ATTEMPTED)
{result=iTarget.getActivityAttempted()?EVALUATE_TRUE:EVALUATE_FALSE;}
else if(cond.mCondition==ATTEMPTSEXCEEDED)
{if(iTarget.getAttemptLimitControl())
{var maxAttempt=iTarget.getAttemptLimit();if(maxAttempt>=0)
{result=(iTarget.getNumAttempt()>=maxAttempt)?EVALUATE_TRUE:EVALUATE_FALSE;}}}
else if(cond.mCondition==TIMELIMITEXCEEDED)
{}
else if(cond.mCondition==OUTSIDETIME)
{}
if(cond.mNot&&result!=EVALUATE_UNKNOWN)
{result=(result==EVALUATE_FALSE)?EVALUATE_TRUE:EVALUATE_FALSE;}}
return result;}}
var NAV_NONE=0;var NAV_START=1;var NAV_RESUMEALL=2;var NAV_CONTINUE=3;var NAV_PREVIOUS=4;var NAV_ABANDON=5;var NAV_ABANDONALL=6;var NAV_SUSPENDALL=7;var NAV_EXIT=8;var NAV_EXITALL=9;
function SeqObjective()
{this.mMaps=new Array();}
SeqObjective.prototype={mObjID:"_primary_",mSatisfiedByMeasure:false,mActiveMeasure:true,mMinMeasure:1,mContributesToRollup:false}
function SeqObjectiveMap()
{}
SeqObjectiveMap.prototype={mGlobalObjID:null,mReadStatus:true,mReadMeasure:true,mWriteStatus:false,mWriteMeasure:false}
function SeqObjectiveTracking(iObj,iLearnerID,iScopeID)
{if(iObj!=null)
{this.mObj=iObj;this.mLearnerID=iLearnerID;this.mScopeID=iScopeID;if(iObj.mMaps!=null)
{for(var i=0;i<this.mObj.mMaps.length;i++)
{var map=this.mObj.mMaps[i];if(map.mReadStatus)
{this.mReadStatus=map.mGlobalObjID;}
if(map.mReadMeasure)
{this.mReadMeasure=map.mGlobalObjID;}
if(map.mWriteStatus)
{if(this.mWriteStatus==null)
{this.mWriteStatus=new Array();}
this.mWriteStatus[this.mWriteStatus.length]=map.mGlobalObjID;}
if(map.mWriteMeasure)
{if(this.mWriteMeasure==null)
{this.mWriteMeasure=new Array();}
this.mWriteMeasure[this.mWriteMeasure.length]=map.mGlobalObjID;}}}}}
SeqObjectiveTracking.prototype={mLearnerID:null,mScopeID:null,mObj:null,mDirtyObj:false,mSetOK:false,mHasSatisfied:false,mSatisfied:false,mHasMeasure:false,mMeasure:0.0,mReadStatus:null,mReadMeasure:null,mWriteStatus:null,mWriteMeasure:null,getObjID:function(){return this.mObj.mObjID;},getObj:function(){return this.mObj;},setDirtyObj:function(){this.mDirtyObj=true;},forceObjStatus:function(iSatisfied)
{if(iSatisfied==TRACK_UNKNOWN)
{this.clearObjStatus();}
else
{if(this.mWriteStatus!=null)
{for(var i=0;i<this.mWriteStatus.length;i++)
{adl_seq_utilities.setGlobalObjSatisfied(this.mWriteStatus[i],this.mLearnerID,this.mScopeID,iSatisfied);}}
this.mHasSatisfied=true;if(iSatisfied==TRACK_SATISFIED)
{this.mSatisfied=true;}
else
{this.mSatisfied=false;}}},setObjStatus:function(iSatisfied)
{if(this.mObj.mSatisfiedByMeasure&&!this.mSetOK)
{}
else
{if(iSatisfied==TRACK_UNKNOWN)
{this.clearObjStatus();}
else
{if(this.mWriteStatus!=null)
{for(var i=0;i<this.mWriteStatus.length;i++)
{adl_seq_utilities.setGlobalObjSatisfied(this.mWriteStatus[i],this.mLearnerID,this.mScopeID,iSatisfied);}}
this.mHasSatisfied=true;if(iSatisfied==TRACK_SATISFIED)
{this.mSatisfied=true;}
else
{this.mSatisfied=false;}}}},clearObjStatus:function()
{var statusChange=false;if(this.mHasSatisfied)
{if(this.mObj.mSatisfiedByMeasure)
{}
else
{if(this.mWriteStatus!=null)
{for(var i=0;i<this.mWriteStatus.length;i++)
{adl_seq_utilities.setGlobalObjSatisfied(this.mWriteStatus[i],this.mLearnerID,this.mScopeID,TRACK_UNKNOWN);}}
this.mHasSatisfied=false;statusChange=true;}}
return statusChange;},clearObjMeasure:function(iAffectSatisfaction)
{var statusChange=false;if(this.mHasMeasure)
{if(this.mWriteMeasure!=null)
{for(var i=0;i<this.mWriteMeasure.length;i++)
{adl_seq_utilities.setGlobalObjMeasure(this.mWriteMeasure[i],this.mLearnerID,this.mScopeID,TRACK_UNKNOWN);}}
this.mHasMeasure=false;if(iAffectSatisfaction)
{this.forceObjStatus(TRACK_UNKNOWN);}}
return statusChange;},setObjMeasure:function(iMeasure,iAffectSatisfaction)
{if(iMeasure<-1.0||iMeasure>1.0)
{this.clearObjMeasure(iAffectSatisfaction);}
else
{this.mHasMeasure=true;this.mMeasure=iMeasure;if(this.mWriteMeasure!=null)
{for(var i=0;i<this.mWriteMeasure.length;i++)
{adl_seq_utilities.setGlobalObjMeasure(this.mWriteMeasure[i],this.mLearnerID,this.mScopeID,iMeasure);}}
if(iAffectSatisfaction)
{if(this.mMeasure>=this.mObj.mMinMeasure)
{this.forceObjStatus(TRACK_SATISFIED);}
else
{this.forceObjStatus(TRACK_NOTSATISFIED);}}}},getObjStatus:function(iIsRetry,iOptions)
{var iOptions=ilAugment({iUseLocal:false},iOptions);var iUseLocal=iOptions.iUseLocal;var ret=TRACK_UNKNOWN;var done=false;if(this.mObj.mSatisfiedByMeasure==true)
{done=true;var measure=null;if(this.mReadMeasure!=null)
{measure=adl_seq_utilities.getGlobalObjMeasure(this.mReadMeasure,this.mLearnerID,this.mScopeID);}
if(this.mHasMeasure==true&&measure==null)
{if(this.mHasMeasure==true&&!(iIsRetry==true&&this.mDirtyObj==true))
{measure=parseFloat(this.mMeasure);}}
var val=-999.0;if(measure!=null){val=parseFloat(measure);}
if(val<-1.0||val>1.0)
{}
else
{if(val>=this.mObj.mMinMeasure)
{ret=TRACK_SATISFIED;}
else
{ret=TRACK_NOTSATISFIED;}}}
if(done==false)
{if(this.mReadStatus!=null)
{var status=adl_seq_utilities.getGlobalObjSatisfied(this.mReadStatus,this.mLearnerID,this.mScopeID);if(status!=null)
{ret=status;done=true;}}
if(this.mHasSatisfied==true&&(done==false||iUseLocal==true))
{if(this.mHasSatisfied==true&&!(iIsRetry==true&&this.mDirtyObj==true))
{if(this.mSatisfied==true)
{ret=TRACK_SATISFIED;}
else
{ret=TRACK_NOTSATISFIED;}}}}
return ret;},getObjMeasure:function(iIsRetry,iOptions)
{var iOptions=ilAugment({iUseLocal:false},iOptions);var iUseLocal=iOptions.iUseLocal;var ret=TRACK_UNKNOWN;var done=false;if(this.mReadMeasure!=null)
{var measure=adl_seq_utilities.getGlobalObjMeasure(this.mReadMeasure,this.mLearnerID,this.mScopeID);if(measure!=null)
{ret=measure;done=true;}}
if(this.mHasMeasure==true&&(done==false||iUseLocal==true))
{if(this.mHasMeasure==true&&!(iIsRetry==true&&this.mDirtyObj==true))
{ret=this.mMeasure;}}
if(ret!=TRACK_UNKNOWN&&this.mObj.mSatisfiedByMeasure==true&&!(iIsRetry==true&&this.mDirtyObj==true))
{var val=-999.0;val=ret;if(val<-1.0||val>1.0)
{}
else
{this.mSetOK=true;if(val>=this.mObj.mMinMeasure)
{this.setObjStatus(TRACK_SATISFIED);}
else
{this.setObjStatus(TRACK_NOTSATISFIED);}
this.mSetOK=false;}}
return ret;},getByMeasure:function()
{var byMeasure=false;if(this.mObj!=null)
{byMeasure=this.mObj.mSatisfiedByMeasure;}
return byMeasure;}}
var ROLLUP_ACTION_NOCHANGE=0;var ROLLUP_ACTION_SATISFIED=1;var ROLLUP_ACTION_NOTSATISFIED=2;var ROLLUP_ACTION_COMPLETED=3;var ROLLUP_ACTION_INCOMPLETE=4;var ROLLUP_CONSIDER_ALWAYS="always";var ROLLUP_CONSIDER_ATTEMPTED="ifAttempted";var ROLLUP_CONSIDER_NOTSKIPPED="ifNotSkipped";var ROLLUP_CONSIDER_NOTSUSPENDED="ifNotSuspended";var ROLLUP_SET_ALL="all";var ROLLUP_SET_ANY="any";var ROLLUP_SET_NONE="none";var ROLLUP_SET_ATLEASTCOUNT="atLeastCount";var ROLLUP_SET_ATLEASTPERCENT="atLeastPercent";function SeqRollupRule()
{}
SeqRollupRule.prototype={mAction:ROLLUP_ACTION_SATISFIED,mChildActivitySet:ROLLUP_SET_ALL,mMinCount:0,mMinPercent:0.0,mConditions:null,setRollupAction:function(iAction)
{if(iAction=="satisfied")
{this.mAction=ROLLUP_ACTION_SATISFIED;}
else if(iAction=="notSatisfied")
{this.mAction=ROLLUP_ACTION_NOTSATISFIED;}
else if(iAction=="completed")
{this.mAction=ROLLUP_ACTION_COMPLETED;}
else if(iAction=="incomplete")
{this.mAction=ROLLUP_ACTION_INCOMPLETE;}},evaluate:function(iChildren)
{var result=false;if(this.mChildActivitySet==ROLLUP_SET_ALL)
{result=this.evaluateAll(iChildren);}
else if(this.mChildActivitySet==ROLLUP_SET_ANY)
{result=this.evaluateAny(iChildren);}
else if(this.mChildActivitySet==ROLLUP_SET_NONE)
{result=this.evaluateNone(iChildren);}
else if(this.mChildActivitySet==ROLLUP_SET_ATLEASTCOUNT)
{result=this.evaluateMinCount(iChildren);}
else if(this.mChildActivitySet==ROLLUP_SET_ATLEASTPERCENT)
{result=this.evaluateMinPercent(iChildren);}
var action=ROLLUP_ACTION_NOCHANGE;if(result)
{action=this.mAction;}
return action;},isIncluded:function(iActivity)
{var include=true;if(iActivity.getIsTracked())
{if(iActivity.getDeliveryMode()=="normal")
{if(this.mAction==ROLLUP_ACTION_SATISFIED||this.mAction==ROLLUP_ACTION_NOTSATISFIED)
{include=iActivity.getIsObjRolledUp();}
else if(this.mAction==ROLLUP_ACTION_COMPLETED||this.mAction==ROLLUP_ACTION_INCOMPLETE)
{include=iActivity.getIsProgressRolledUp();}}
else
{include=false;}}
else
{include=false;}
if(include)
{var consider=null;switch(this.mAction)
{case ROLLUP_ACTION_SATISFIED:consider=iActivity.getRequiredForSatisfied();break;case ROLLUP_ACTION_NOTSATISFIED:consider=iActivity.getRequiredForNotSatisfied();break;case ROLLUP_ACTION_COMPLETED:consider=iActivity.getRequiredForCompleted();break;case ROLLUP_ACTION_INCOMPLETE:consider=iActivity.getRequiredForIncomplete();break;default:include=false;}
if(consider!=null)
{if(consider==ROLLUP_CONSIDER_NOTSUSPENDED)
{if(iActivity.getActivityAttempted()&&iActivity.getIsSuspended())
{include=false;}}
else if(consider==ROLLUP_CONSIDER_ATTEMPTED)
{include=iActivity.getActivityAttempted();}
else if(consider==ROLLUP_CONSIDER_NOTSKIPPED)
{var skippedRules=iActivity.getPreSeqRules();var result=null;if(skippedRules!=null)
{result=skippedRules.evaluate(RULE_TYPE_SKIPPED,iActivity,false);}
if(result!=null)
{include=false;}}
else
{include=true;}}}
return include;},evaluateAll:function(iChildren)
{var result=true;var emptySet=true;var considered=false;var tempActivity=null;var i=0;while(result&&(i<iChildren.length))
{tempActivity=iChildren[i];if(this.isIncluded(tempActivity)==true)
{considered=true;var eval=this.mConditions.evaluate(tempActivity);var parent=tempActivity.getParent().mActivityID;result=result&&(eval==EVALUATE_TRUE);emptySet=emptySet&&(eval==EVALUATE_UNKNOWN);}
i++;}
if(considered&&emptySet)
{result=false;}
return result;},evaluateAny:function(iChildren)
{var result=false;var tempActivity=null;var i=0;while((!result)&&(i<iChildren.length))
{tempActivity=iChildren[i];if(this.isIncluded(tempActivity))
{var eval=this.mConditions.evaluate(tempActivity);result=result||(eval==EVALUATE_TRUE);}
i++;}
return result;},evaluateNone:function(iChildren)
{var result=true;var tempActivity=null;var i=0;while(result&&(i<iChildren.length))
{tempActivity=iChildren[i];if(this.isIncluded(tempActivity))
{var eval=this.mConditions.evaluate(tempActivity);result=result&&!(eval==EVALUATE_TRUE||eval==EVALUATE_UNKNOWN);}
i++;}
return result;},evaluateMinCount:function(iChildren)
{var count=0;var emptySet=true;var tempActivity=null;var i=0;while((count<this.mMinCount)&&i<iChildren.length)
{tempActivity=iChildren[i];if(this.isIncluded(tempActivity))
{var eval=this.mConditions.evaluate(tempActivity);if(eval==EVALUATE_TRUE)
{count++;}
emptySet=emptySet&&(eval==EVALUATE_UNKNOWN);}
i++;}
var result=false;if(!emptySet)
{result=(count>=this.mMinCount);}
return result;},evaluateMinPercent:function(iChildren)
{var countAll=0;var count=0;var emptySet=true;var tempActivity=null;var i=0;while(i<iChildren.length)
{tempActivity=iChildren[i];if(this.isIncluded(tempActivity))
{countAll++;var eval=this.mConditions.evaluate(tempActivity);if(eval==EVALUATE_TRUE)
{count++;}
emptySet=emptySet&&(eval==EVALUATE_UNKNOWN);}
i++;}
var result=false;if(emptySet==false)
{result=(count>=parseFloat(((this.mMinPercent*countAll)+0.5)));}
return result;}}
function SeqRollupRuleset(mRollupRules)
{if(mRollupRules)
{mRollupRules=iRules;}}
SeqRollupRuleset.prototype={mRollupRules:null,mIsSatisfied:false,mIsNotSatisfied:false,mIsCompleted:false,mIsIncomplete:false,evaluate:function(ioThisActivity)
{this.mIsCompleted=false;this.mIsIncomplete=false;this.mIsSatisfied=false;this.mIsNotSatisfied=false;if(ioThisActivity!=null)
{if(ioThisActivity.getChildren(false)!=null)
{ioThisActivity=this.applyMeasureRollup(ioThisActivity);var satisfiedRule=false;var completedRule=false;if(this.mRollupRules!=null)
{for(var i=0;i<this.mRollupRules.length;i++)
{var rule=this.mRollupRules[i];if(rule.mAction==ROLLUP_ACTION_SATISFIED||rule.mAction==ROLLUP_ACTION_NOTSATISFIED)
{satisfiedRule=true;}
if(rule.mAction==ROLLUP_ACTION_COMPLETED||rule.mAction==ROLLUP_ACTION_INCOMPLETE)
{completedRule=true;}}}
if(satisfiedRule==false)
{if(this.mRollupRules==null)
{this.mRollupRules=new Array();}
var set=new SeqConditionSet(true);var cond=new SeqCondition();var rule=new SeqRollupRule();set.mCombination=COMBINATION_ANY;set.mConditions=new Array();cond.mCondition=ATTEMPTED;set.mConditions[0]=cond;cond=new SeqCondition();cond.mCondition=SATISFIED;cond.mNot=true;set.mConditions[1]=cond;rule.mAction=ROLLUP_ACTION_NOTSATISFIED;rule.mConditions=set;this.mRollupRules[this.mRollupRules.length]=rule;rule=new SeqRollupRule();set=new SeqConditionSet(true);cond=new SeqCondition();set.mCombination=COMBINATION_ALL;cond.mCondition=SATISFIED;set.mConditions=new Array();set.mConditions[0]=cond;rule.mAction=ROLLUP_ACTION_SATISFIED;rule.mConditions=set;this.mRollupRules[this.mRollupRules.length]=rule;}
if(completedRule==false)
{if(this.mRollupRules==null)
{this.mRollupRules=new Array();}
var set=new SeqConditionSet(true);var cond=new SeqCondition();var rule=new SeqRollupRule();set.mCombination=COMBINATION_ANY;set.mConditions=new Array();cond.mCondition=ATTEMPTED;set.mConditions[0]=cond;cond=new SeqCondition();cond.mCondition=COMPLETED;cond.mNot=true;set.mConditions[1]=cond;rule.mAction=ROLLUP_ACTION_INCOMPLETE;rule.mConditions=set;this.mRollupRules[this.mRollupRules.length]=rule;rule=new SeqRollupRule();set=new SeqConditionSet(true);cond=new SeqCondition();set.mCombination=COMBINATION_ALL;cond.mCondition=COMPLETED;set.mConditions=new Array();set.mConditions[0]=cond;rule=new SeqRollupRule();rule.mAction=ROLLUP_ACTION_COMPLETED;rule.mConditions=set;this.mRollupRules[this.mRollupRules.length]=rule;}
for(var i=0;i<this.mRollupRules.length;i++)
{var rule=this.mRollupRules[i];var result=rule.evaluate(ioThisActivity.getChildren(false));switch(result)
{case ROLLUP_ACTION_NOCHANGE:break;case ROLLUP_ACTION_SATISFIED:this.mIsSatisfied=true;break;case ROLLUP_ACTION_NOTSATISFIED:this.mIsNotSatisfied=true;break;case ROLLUP_ACTION_COMPLETED:this.mIsCompleted=true;break;case ROLLUP_ACTION_INCOMPLETE:this.mIsIncomplete=true;break;default:break;}}
if(!ioThisActivity.getObjSatisfiedByMeasure())
{if(this.mIsSatisfied)
{ioThisActivity.setObjSatisfied(TRACK_SATISFIED);}
else if(this.mIsNotSatisfied)
{ioThisActivity.setObjSatisfied(TRACK_NOTSATISFIED);}}
if(this.mIsCompleted)
{ioThisActivity.setProgress(TRACK_COMPLETED);}
else if(this.mIsIncomplete)
{ioThisActivity.setProgress(TRACK_INCOMPLETE);}}}
return ioThisActivity;},applyMeasureRollup:function(ioThisActivity)
{sclogdump("MeasureRollup [RB.1.1]","seq");var total=0.0;var countedMeasure=0.0;var children=ioThisActivity.getChildren(false);for(var i=0;i<children.length;i++)
{var child=children[i];if(child.getIsTracked())
{if(child.getObjMeasureWeight()>0.0)
{countedMeasure=parseFloat(countedMeasure)+parseFloat(child.getObjMeasureWeight());if(child.getObjMeasureStatus(false))
{total=parseFloat(total)+parseFloat(child.getObjMeasureWeight()*child.getObjMeasure(false));}}}}
if(countedMeasure>0.0)
{ioThisActivity.setObjMeasure(total/countedMeasure);}
else
{ioThisActivity.clearObjMeasure();}
return ioThisActivity;},size:function()
{if(this.mRollupRules!=null)
{return this.mRollupRules.length;}
return 0;}}
var SEQ_ACTION_NOACTION="noaction";var SEQ_ACTION_IGNORE="ignore";var SEQ_ACTION_SKIP="skip";var SEQ_ACTION_DISABLED="disabled";var SEQ_ACTION_HIDEFROMCHOICE="hiddenFromChoice";var SEQ_ACTION_FORWARDBLOCK="stopForwardTraversal";var SEQ_ACTION_EXITPARENT="exitParent";var SEQ_ACTION_EXITALL="exitAll";var SEQ_ACTION_RETRY="retry";var SEQ_ACTION_RETRYALL="retryAll";var SEQ_ACTION_CONTINUE="continue";var SEQ_ACTION_PREVIOUS="previous";var SEQ_ACTION_EXIT="exit";function SeqRule()
{}
SeqRule.prototype={mAction:SEQ_ACTION_IGNORE,mConditions:null,evaluate:function(iType,iThisActivity,iRetry)
{sclogdump("SequencingRuleCheckSub [UP.2.1]","seq");var result=SEQ_ACTION_NOACTION;var doEvaluation=false;switch(iType)
{case RULE_TYPE_ANY:{doEvaluation=true;break;}
case RULE_TYPE_POST:{if(this.mAction==SEQ_ACTION_EXITPARENT||this.mAction==SEQ_ACTION_EXITALL||this.mAction==SEQ_ACTION_RETRY||this.mAction==SEQ_ACTION_RETRYALL||this.mAction==SEQ_ACTION_CONTINUE||this.mAction==SEQ_ACTION_PREVIOUS)
{doEvaluation=true;}
break;}
case RULE_TYPE_EXIT:{if(this.mAction==SEQ_ACTION_EXIT)
{doEvaluation=true;}
break;}
case RULE_TYPE_SKIPPED:{if(this.mAction==SEQ_ACTION_SKIP)
{doEvaluation=true;}
break;}
case RULE_TYPE_DISABLED:{if(this.mAction==SEQ_ACTION_DISABLED)
{doEvaluation=true;}
break;}
case RULE_TYPE_HIDDEN:{if(this.mAction==SEQ_ACTION_HIDEFROMCHOICE)
{doEvaluation=true;}
break;}
case RULE_TYPE_FORWARDBLOCK:{if(this.mAction==SEQ_ACTION_FORWARDBLOCK)
{doEvaluation=true;}
break;}
default:{break;}}
if(doEvaluation)
{if(iThisActivity!=null)
{if(this.mConditions.evaluate(iThisActivity,{iIsRetry:iRetry})==EVALUATE_TRUE)
{result=this.mAction;}}}
return result;}}
var RULE_TYPE_ANY=1;var RULE_TYPE_EXIT=2;var RULE_TYPE_POST=3;var RULE_TYPE_SKIPPED=4;var RULE_TYPE_DISABLED=5;var RULE_TYPE_HIDDEN=6;var RULE_TYPE_FORWARDBLOCK=7;function SeqRuleset(iRules)
{this.mRules=iRules;}
SeqRuleset.prototype={mRules:null,evaluate:function(iType,iThisActivity,iRetry)
{sclogdump("SequencingRulesCheck [UP.2]","seq");var action=null;if(this.mRules!=null)
{var cont=true;for(var i=0;i<this.mRules.length&&cont;i++)
{var rule=this.mRules[i];var result=rule.evaluate(iType,iThisActivity,iRetry);if(result!=SEQ_ACTION_NOACTION)
{cont=false;action=result;}}}
return action;},size:function()
{if(this.mRules!=null)
{return this.mRules.length;}
return 0;}}
var log_auto_flush=false;var log_buffer="";if(disable_all_logging==true){elm=all("toggleLog");elm.innerHTML="";}
function toggleView(){elm_left=all("leftView");elm_right=all("tdResource");elm_tree=all("treeView");elm_log=all("ilLog");elm_controls=all("treeControls");elm_toggle=all("treeToggle");if(treeView==false){elm_left.style.width='25%';elm_right.style.width='75%';elm_tree.style.display='block';elm_log.style.display='block';elm_controls.style.display='block';elm_toggle.innerHTML=this.config.langstrings['btnhidetree'];treeView=true;}else{elm_left.style.width='0%';elm_right.style.width='100%';elm_tree.style.display='none';elm_log.style.display='none';elm_controls.style.display='none';elm_toggle.innerHTML=this.config.langstrings['btnshowtree'];treeView=false;}}
function toggleTree(){elm=all("toggleTree");if(treeState==false){elm.innerHTML="Collapse All";treeYUI.expandAll();treeState=true;}else{elm.innerHTML="Expand All";treeYUI.collapseAll();treeState=false;}}
function toggleLog(){elm=all("toggleLog");if(logState==false){elm.innerHTML="Hide Log";logState=true;onWindowResize()}else{elm.innerHTML="Show Log";logState=false;onWindowResize();}}
function sclog(mess,type)
{if(disable_all_logging){return;}
if(type=="seq"&&disable_sequencer_logging==true)return;log_auto_flush=true;switch(type)
{case"cmi":mess='<font color="green">'+mess+'</font>';case"info":mess='<font color="orange">'+mess+'</font>';case"error":mess='<font color="red">'+mess+'</font>';case"seq":mess='<font color="blue">'+mess+'</font>';default:mess=mess;}
if(log_auto_flush)
{elm=all("ilLogPre");if(elm)
{elm.innerHTML=elm.innerHTML+mess+'<br />';sclogscroll()}}
else
{log_buffer=log_buffer+mess+'<br />';}}
function sclogflush()
{return;elm=all("ilLogPre");if(elm)
{elm.innerHTML=elm.innerHTML+log_buffer;sclogscroll()}
log_buffer="";}
function sclogclear()
{elm=all("ilLogPre");if(elm)
{elm.innerHTML='';}}
function sclogdump(param,type)
{if(disable_all_logging){return;}
depth=0;var pre=''
for(var j=0;j<depth;j++)
{pre=pre+'    ';}
switch(typeof param)
{case'boolean':if(param)sclog(pre+"true (boolean)");else sclog(pre+"false (boolean)",type);break;case'number':sclog(pre+param+' (number)',type);break;case'string':sclog(pre+param+' (string)',type);break;case'object':if(param===null)
{sclog(pre+'null');}
if(param instanceof Array)sclog(pre+'(Array) {',type);else if(param instanceof Object)sclog(pre+'(Object) {');for(var k in param)
{if(typeof param[k]!="function",type)
{sclog(pre+'['+k+'] => ');sclogdump(param[k],depth+1);}}
sclog(pre+'}',type);break;case'function':break;default:sclog(pre+"unknown: "+(typeof param),type);break;}}
function sclogscroll()
{var top=document.getElementById('ilLog').scrollTop;var height=document.getElementById('ilLog').scrollHeight;var offset=document.getElementById('ilLog').offsetHeight;if(top<(height-offset-1))
{document.getElementById('ilLog').scrollTop=height-offset+20;}}
function timeStringParse(iTime,ioArray)
{var mInitArray=new Array();var mTempArray2=new Array();mTempArray2[0]="0";mTempArray2[1]="0";mTempArray2[2]="0";var mDate="0";var mTime="0";if(iTime==null)
{return;}
if((iTime.length==1)||(iTime.indexOf("P")==-1))
{return;}
mInitArray=iTime.split("P");if(mInitArray[1].indexOf("T")!=-1)
{mTempArray2=mInitArray[1].split("T");mDate=mTempArray2[0];mTime=mTempArray2[1];}
else
{mDate=mInitArray[1];}
if(mDate.indexOf("Y")!=-1)
{mInitArray=mDate.split("Y");tempInt=parseInt(mInitArray[0],10);ioArray[0]=parseInt(tempInt,10);}
else
{mInitArray[1]=mDate;}
if(mDate.indexOf("M")!=-1)
{mTempArray2=mInitArray[1].split("M");tempInt=parseInt(mTempArray2[0],10);ioArray[1]=parseInt(tempInt,10);}
else
{if(mInitArray.length!=2)
{mTempArray2[1]="";}
else
{mTempArray2[1]=mInitArray[1];}}
if(mDate.indexOf("D")!=-1)
{mInitArray=mTempArray2[1].split("D");tempInt=parseInt(mInitArray[0],10);ioArray[2]=parseInt(tempInt,10);}
else
{mInitArray=new Array();mInitArray[0]="";mInitArray[1]="";}
if(mTime.equals!="0")
{if(mTime.indexOf("H")!=-1)
{mInitArray=mTime.split("H");tempInt=parseInt(mInitArray[0],10);ioArray[3]=parseInt(tempInt,10);}
else
{mInitArray[1]=mTime;}
if(mTime.indexOf("M")!=-1)
{mTempArray2=mInitArray[1].split("M");tempInt=parseInt(mTempArray2[0],10);ioArray[4]=parseInt(tempInt,10);}
else
{if(mInitArray.length!=2)
{mTempArray2[1]="";}
else
{mTempArray2[1]=mInitArray[1];}}
if(mTime.indexOf("S")!=-1)
{mInitArray=mTempArray2[1].split("S");if(mTime.indexOf(".")!=-1)
{mTempArray2=mInitArray[0].split(".");if(mTempArray2[1].length==1)
{mTempArray2[1]=mTempArray2[1]+"0";}
tempInt2=parseInt(mTempArray2[1],10);ioArray[6]=parseInt(tempInt2,10);tempInt=parseInt(mTempArray2[0],10);ioArray[5]=parseInt(tempInt,10);}
else
{tempInt=parseInt(mInitArray[0],10);ioArray[5]=parseInt(tempInt,10);}}}
return ioArray;}
function addTimes(iTimeOne,iTimeTwo){var mTimeString=null;var multiple=1;mFirstTime=new Array();mSecondTime=new Array();for(var i=0;i<7;i++)
{mFirstTime[i]=0;mSecondTime[i]=0;}
mFirstTime=timeStringParse(iTimeOne,mFirstTime);mSecondTime=timeStringParse(iTimeTwo,mSecondTime);for(var i=0;i<7;i++)
{mFirstTime[i]=parseInt(mFirstTime[i],10)+parseInt(mSecondTime[i],10);}
if(mFirstTime[6]>99)
{multiple=parseFloat(mFirstTime[6]/100);mFirstTime[6]=mFirstTime[6]%100;mFirstTime[5]=parseInt(mFirstTime[5],10)+multiple;}
if(mFirstTime[5]>59)
{multiple=parseFloat(mFirstTime[5]/60);mFirstTime[5]=mFirstTime[5]%60;mFirstTime[4]=parseInt(mFirstTime[4],10)+multiple;}
if(mFirstTime[4]>59)
{multiple=parseFloat(mFirstTime[4]/60);mFirstTime[4]=mFirstTime[4]%60;mFirstTime[3]=parseInt(mFirstTime[3],10)+multiple;}
if(mFirstTime[3]>23)
{multiple=parseFloat(mFirstTime[3]/24);mFirstTime[3]=mFirstTime[3]%24;mFirstTime[2]=parseInt(mFirstTime[2],10)+multiple;}
mTimeString="P";if(mFirstTime[0]!=0)
{tempInt=parseInt(mFirstTime[0],10);mTimeString+=tempInt.toString();mTimeString+="Y";}
if(mFirstTime[1]!=0)
{tempInt=parseInt(mFirstTime[1],10);mTimeString+=tempInt.toString();mTimeString+="M";}
if(mFirstTime[2]!=0)
{tempInt=parseInt(mFirstTime[2],10);mTimeString+=tempInt.toString();mTimeString+="D";}
if((mFirstTime[3]!=0)||(mFirstTime[4]!=0)||(mFirstTime[5]!=0)||(mFirstTime[6]!=0))
{mTimeString+="T";}
if(mFirstTime[3]!=0)
{tempInt=parseInt(mFirstTime[3],10);mTimeString+=tempInt.toString();mTimeString+="H";}
if(mFirstTime[4]!=0)
{tempInt=parseInt(mFirstTime[4],10);mTimeString+=tempInt.toString();mTimeString+="M";}
if(mFirstTime[5]!=0)
{tempInt=parseInt(mFirstTime[5],10);mTimeString+=tempInt.toString();}
if(mFirstTime[6]!=0)
{if(mFirstTime[5]==0)
{mTimeString+="0";}
mTimeString+=".";if(mFirstTime[6]<10)
{mTimeString+="0";}
tempInt2=parseInt(mFirstTime[6],10);mTimeString+=tempInt2.toString();}
if((mFirstTime[5]!=0)||(mFirstTime[6]!=0))
{mTimeString+="S";}
return mTimeString;}
function Duration(mixed)
{this.value=new Date(typeof(mixed)==="number"?mixed:Duration.parse(mixed||""));}
this.Duration=Duration;Duration.prototype.set=function(obj)
{this.value.setTime(obj&&obj.valueOf?obj.valueOf():obj);};Duration.prototype.add=function(obj)
{var val=this.value.getTime()+obj&&obj.valueOf?obj.valueOf():obj;this.value.setTime(val);return val;};Duration.parse=function(str)
{var m=String(str).match(/^P(\d+Y)?(\d+M)?(\d+D)?(T(\d+H)?(\d+M)?((\d+)\.?(\d*)?S)?)?$/);if(!m)return null;return m[4]==="T"?null:Date.UTC((parseInt(m[1])||0)+1970,(parseInt(m[2])||0),(parseInt(m[3])||0)+1,parseInt(m[5])||0,parseInt(m[6])||0,parseInt(m[8])||0,parseInt(m[9])||0);};Duration.toString=function(d)
{if(typeof d==="number")d=new Date(d);var t,r=['P'];if((t=d.getUTCFullYear()-1970)){r.push(t+'Y');}
if((t=d.getUTCMonth())){r.push(t+'M');}
if((t=d.getUTCDate()-1)){r.push(t+'D');}
r.push('T');if((t=d.getUTCHours())){r.push(t+'H');}
if((t=d.getUTCMinutes())){r.push(t+'M');}
if((t=d.getUTCSeconds()+d.getUTCMilliseconds()/1000)){r.push(t.toFixed(2)+'S');}
return r.join("");};Duration.prototype.toString=function()
{return Duration.toString(this.value);}
Duration.prototype.valueOf=function()
{return this.value.getTime();};function DateTime(mixed,utc)
{this.value=new Date(typeof(mixed)==="number"?mixed:DateTime.parse(mixed||"",utc));}
this.DateTime=DateTime;DateTime.parse=function(str,utc)
{var m=String(str).match(/^\d{4}(-\d{2}(-\d{2}(T\d{2}(:\d{2}(:\d{2}(\.\d{1,2}([-+Z](\d{2}(:\d{2})?)?)?)?)?)?)?)?)?$/);if(!m)return null;var a=[m[0]?Number(m[0].substr(0,4)):0,m[1]?Number(m[1].substr(1,2))-1:0,m[2]?Number(m[2].substr(1,2)):1,m[3]?Number(m[3].substr(1,2)):0,m[4]?Number(m[4].substr(1,2)):0,m[5]?Number(m[5].substr(1,2)):0,m[6]?Number(m[6].substr(1,2)):0,m[7]?m[7].substr(0,1):utc?'Z':'+',m[8]?Number(m[8].substr(0,2)):0,m[9]?Number(m[9].substr(1,2)):0];var z=a[7]==='Z'?(new Date()).getTimezoneOffset():((a[8]||0)*60+(a[9]||0))*(a[7]==='-'?-1:1)
var d=new Date(a[0],a[1],a[2],a[3],a[4],a[5],a[6]);if(a[0]<1970||a[0]>2038||a[1]<0||a[1]>12||d.getMonth()!==a[1]||d.getDate()!==a[2]||a[3]>23||a[4]>59||a[5]>59||a[8]>23||a[9]>59||m[7]&&m[7]!=="Z"&&!m[8])return null;d.setTime(d.getTime()+z);return d;};DateTime.toString=function(d,parts,prec)
{function f(n)
{return(n<10?'0':'')+n;}
if(typeof d==="number")d=new Date(d);var r=[d.getFullYear(),'-',f(d.getMonth()+1),'-',f(d.getDate()),'T',f(d.getHours()),':',f(d.getMinutes()),':',f(d.getSeconds()),f((d.getMilliseconds()/1000).toFixed(prec||3).substr(1))];return r.slice(0,2*(parts||7)-1).join("");};DateTime.prototype.toString=function(parts,prec)
{return DateTime.toString(this.value,parts,prec);};DateTime.prototype.valueOf=function()
{return this.value.getTime();};function Activity()
{this.cmi_node_id="$"+remoteInsertId++;this.hideLMSUIs=new Object();this.objectives=new Object();this.primaryObjective=new Object();this.comments=new Object();this.interactions=new Object();this.rules=new Object();}
this.Activity=Activity;Activity.prototype={dirty:0,accesscount:0,accessduration:0,accessed:0,activityAbsoluteDuration:0,activityAbsoluteDurationLimit:0,activityAttemptCount:0,activityExperiencedDuration:0,activityExperiencedDurationLimit:0,activityProgressStatus:false,attemptAbsoluteDuration:0,attemptAbsoluteDurationLimit:null,attemptCompletionAmount:0,attemptCompletionStatus:false,attemptExperiencedDuration:0,attemptExperiencedDurationLimit:0,attemptLimit:0,attemptProgressStatus:false,audio_captioning:0,audio_level:0,beginTimeLimit:0,choice:true,choiceExit:true,completion:0,completion_status:null,completionSetByContent:false,completionThreshold:null,constrainChoice:false,cp_node_id:null,created:0,credit:'credit',dataFromLMS:null,delivery_speed:0,endTimeLimit:0,entry:'ab-initio',exit:null,flow:false,foreignId:0,forwardOnly:false,id:null,index:0,isvisible:true,language:null,location:null,max:null,measureSatisfactionIfActive:true,min:null,modified:0,objectiveMeasureWeight:1.0,objectiveSetByContent:false,parameters:null,parent:null,preventActivation:false,progress_measure:null,randomizationTiming:'never',raw:null,reorderChildren:false,requiredForCompleted:'always',requiredForIncomplete:'always',requiredForNotSatisfied:'always',requiredForSatisfied:'always',resourceId:null,rollupObjectiveSatisfied:true,rollupProgressCompletion:true,scaled:null,scaled_passing_score:null,selectCount:0,selectionTiming:'never',session_time:0,success_status:null,suspend_data:null,timeLimitAction:null,title:null,total_time:'PT0H0M0S',tracked:true,useCurrentAttemptObjectiveInfo:true,useCurrentAttemptProgressInfo:true};function Interaction(cmi_node_id)
{this.cmi_node_id=cmi_node_id;this.correct_responses=new Object();this.objectives=new Object();}
this.Interaction=Interaction;Interaction.prototype={cmi_interaction_id:0,description:null,id:null,latency:0,learner_response:null,result:null,timestamp:null,type:null,weighting:0};function Comment(cmi_node_id)
{this.cmi_node_id=cmi_node_id;}
this.Comment=Comment;Comment.prototype={cmi_comment_id:0,comment:null,timestamp:null,location:null,sourceIsLMS:false};function CorrectResponse(cmi_interaction_id)
{this.cmi_interaction_id=cmi_interaction_id;}
this.CorrectResponse=CorrectResponse;CorrectResponse.prototype={cmi_correct_response_id:0,pattern:null};function Objective(cmi_node_id,cmi_interaction_id)
{this.cmi_interaction_id=cmi_interaction_id;this.cmi_node_id=cmi_node_id;this.mapinfos=new Object();}
this.Objective=Objective;Objective.prototype={cmi_objective_id:0,cp_node_id:0,foreignId:0,id:null,objectiveID:null,completion_status:null,description:null,max:null,min:null,raw:null,scaled:null,progress_measure:null,success_status:null,scope:"local",minNormalizedMeasure:1.0,primary:false,satisfiedByMeasure:false};function Mapinfo(){}
this.Mapinfo=Mapinfo;Mapinfo.prototype={cp_node_id:0,foreignId:0,readNormalizedMeasure:true,readSatisfiedStatus:true,targetObjectiveID:null,writeNormalizedMeasure:false,writeSatisfiedStatus:false};function Rule()
{this.conditions=new Object();}
this.Rule=Rule;Rule.prototype={action:null,childActivitySet:'all',conditionCombination:null,cp_node_id:0,foreignId:0,minimumCount:0,minimumPercent:0,type:null};function Condition(){}
this.Condition=Condition;Condition.prototype={condition:null,cp_node_id:0,foreignId:0,measureThreshold:null,operator:'noOp',referencedObjective:null};function HideLMSUI(){}
this.HideLMSUI=HideLMSUI;HideLMSUI.prototype={cp_node_id:0,foreignId:0,value:null};function UIEvent(e,w)
{if(!w)
{w=window;}
this._ie=!e&&w.event;this._event=e||w.event;this.keyCode=this._ie?w.event.keyCode:this._event.which;this.shiftKey=this._ie?w.event.shiftKey:this._event.shiftKey;this.ctrlKey=this._ie?w.event.ctrlKey:this._event.ctrlKey;this.srcElement=e.target||w.event.srcElement;this.type=this._event.type;}
this.UIEvent=UIEvent;UIEvent.prototype.getIdElement=function(){return getAncestor(this.srcElement,'id',true);};UIEvent.prototype.getHrefElement=function(){return getAncestor(this.srcElement,'href',true);};UIEvent.prototype.stop=function(){var e=this._event;if(e.preventDefault)
{e.preventDefault();e.stopPropagation();}
else
{e.returnValue=false;e.cancelBubble=true;}};function attachUIEvent(obj,name,func)
{if(window.Event)
{obj.addEventListener(name,func,false);}
else if(obj.attachEvent)
{obj.attachEvent('on'+name,func);}
else
{obj[name]=func;}}
function detachUIEvent(obj,name,func)
{if(window.Event)
{obj.removeEventListener(name,func,false);}
else if(obj.attachEvent)
{obj.detachEvent('on'+name,func);}
else
{obj[name]='';}}
function getCurrentStyle(elm,prop)
{var doc=elm.ownerDocument;if(elm.currentStyle){return elm.currentStyle[prop];}else if(doc.defaultView&&doc.defaultView.getComputedStyle){return doc.defaultView.getComputedStyle(elm,'').getPropertyValue(fromCamelCase(prop));}else if(elm.style&&elm.style[prop]){return elm.style[prop];}else{return null;}}
function getAncestor(elm,attr,pattern,includeSelf)
{if(elm&&elm.nodeType===1)
{return null;}
if(!includeSelf)
{elm=elm.parentNode;}
do{if(elm[attr])
{if(!pattern||(pattern instanceof RegExp)?pattern.match(elm[attr]):elm[attr]==pattern)
{break;}}
elm=elm.parentNode;}while(elm);return elm;}
function getDesendents(elm,tagName,className,filter,depth)
{function check(pattern,value)
{switch(typeof(pattern))
{case'string':return pattern.charAt()==="!"^pattern===value;case'function':return pattern(value);case'object':return pattern instanceof RegExp?pattern.test(value):pattern[value];}}
if(elm&&elm.nodeType===1)
{return null;}
var children=elm.childNodes;var sink=[];for(var i=0,ni=children.length;i<ni;i++)
{var child=children[i];if(child.nodeType!==1){continue;}
if(tagName&&!check(tagName,child.tagName)){continue;}
if(className&&!check(className,child.className)){continue;}
switch(typeof(filter))
{case'function':if(!filter(child)){continue;}
break;case'object':for(var k in filter){if(!check(filter[k],elm[k])){continue;}}
break;}
sink.push(child);if(depth===undefined||depth)
{sink=sink.concat(getDesendents(child,tagName,className,filter,depth-1));}}
return sink;}
function all(id,win)
{if(id&&id.nodeType===1){return id;}
var doc=(win?win:window).document;var elm=doc.getElementById(id);return!elm?null:elm.length?elm.item(0):elm;}
function addClass(elm,name)
{elm=all(elm);if(elm&&!hasClass(elm,name))
{elm.className=trim(elm.className+" "+name);}}
function hasClass(elm,name)
{elm=all(elm);return elm&&(" "+elm.className+" ").indexOf(" "+name+" ")>-1;}
function removeClass(elm,name)
{elm=all(elm);if(elm)
{elm.className=trim((" "+elm.className+" ").replace(name," "));}}
function replaceClass(elm,oldname,newname)
{elm=all(elm);removeClass(elm,oldname);addClass(elm,newname);}
function toggleClass(elm,name,state)
{elm=all(elm);if(state===undefined){state=!hasClass(elm,name);}
if(!state){removeClass(elm,name);}
else{addClass(elm,name);}}
function getOuterHTML(elm)
{return elm.outerHTML!==undefined?elm.outerHTML:elm.outerHTML;}
function setOuterHTML(elm,markup)
{if(elm.outerHTML!==undefined)
{elm.outerHTML=markup;}
else
{var range=elm.ownerDocument.createRange();range.setStartBefore(elm);var fragment=range.createContextualFragment(markup);elm.parentNode.replaceChild(fragment,elm);}}
function currentTime()
{var d=new Date();return d.getTime()+(Date.remoteOffset||0);}
function trim(str,norm)
{var r=String(str).replace(/^\s+|\s+$/g,'');return norm?r.replace(/\s+/g,' '):r;}
function repeat(obj,times)
{return(new Array(times+1)).join(obj);}
function fromCamelCase(s)
{return s.charAt(0)+s.substring(1).replace(/([A-Z])/g,function(match){return'-'+match.toLowerCase();});}
function toCamelCase(s)
{return s.replace(/(\-\w)/g,function(match){return match.substring(1).toUpperCase();});}
function numberFormat(num,dec,len)
{var s=num.toFixed(dec);if(len&&s.length<len)
{while(s.length<len)
{s='0'+s;}}
return s;}
function copyOf(obj,ref)
{switch(typeof obj){case'object':var r=new obj.constructor();if(obj instanceof Array)
{for(var i=0,ni=obj.length;i<ni;i+=1)
{r[i]=copyOf(obj[i],ref);}}
else
{for(var k in obj)
{if(obj.hasOwnProperty(k))
{r[k]=copyOf(obj[k],ref);}}}
return r;case'function':case'unknown':return ref?obj:undefined;default:return obj;}}
function createHttpRequest()
{try
{return window.XMLHttpRequest?new window.XMLHttpRequest():new window.ActiveXObject('MSXML2.XMLHTTP');}
catch(e)
{throw new Error('cannot create XMLHttpRequest');}}
function sendAndLoad(url,data,callback,user,password,headers)
{function HttpResponse(xhttp)
{this.status=Number(xhttp.status);this.content=String(xhttp.responseText);this.type=String(xhttp.getResponseHeader('Content-Type'));}
function onStateChange()
{if(xhttp.readyState===4){if(typeof callback==='function'){callback(new HttpResponse(xhttp));}else{return new HttpResponse(xhttp);}}}
var xhttp=createHttpRequest();var async=!!callback;var post=!!data;xhttp.open(post?'POST':'GET',url,async,user,password);if(typeof headers!=='object')
{headers=new Object();}
if(post)
{headers['Content-Type']='application/x-www-form-urlencoded';}
if(headers&&headers instanceof Object)
{for(var k in headers){xhttp.setRequestHeader(k,headers[k]);}}
if(async)
{xhttp.onreadystatechange=onStateChange;xhttp.send(data?String(data):'');}else
{xhttp.send(data?String(data):'');return onStateChange();}}
function sendJSONRequest(url,data,callback,user,password,headers)
{if(typeof headers!=="object"){headers={};}
headers['Accept']='text/javascript';headers['Accept-Charset']='UTF-8';var r=sendAndLoad(url,toJSONString(data),callback,user,password,headers);if(r.content){if(r.content.indexOf("login.php")>-1){window.location.href="./Modules/Scorm2004/templates/default/session_timeout.html";}}
if((r.status===200&&(/^text\/javascript;?.*/i).test(r.type))||r.status===0)
{return parseJSONString(r.content);}
else
{return r.content;}}
function toJSONString(v,tab){tab=tab?tab:"";var nl=tab?"\n":"";function fmt(n){return(n<10?'0':'')+n;}
function esc(s){var c={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'};return'"'+s.replace(/[\x00-\x1f\\"]/g,function(m){var r=c[m];if(r){return r;}else{r=m.charAt(0);return"\\u00"+(r<16?'0':'')+r.toString(16);}})+'"';}
switch(typeof v){case'string':return esc(v);case'number':return isFinite(v)?String(v):'null';case'boolean':return String(v);case'object':if(v===null){return'null';}else if(v instanceof Date){return'"'+v.getValue(v)+'"';}else if(v instanceof Array){var ra=new Array();for(var i=0,ni=v.length;i<ni;i+=1){ra.push(v[i]===undefined?'null':toJSONString(v[i],tab.charAt(0)+tab));}
return'['+nl+tab+ra.join(','+nl+tab)+nl+tab+']';}else{var ro=new Array();for(var k in v){if(v.hasOwnProperty&&v.hasOwnProperty(k)){ro.push(esc(String(k))+':'+toJSONString(v[k],tab.charAt(0)+tab));}}
return'{'+nl+tab+ro.join(','+nl+tab)+nl+tab+'}';}}}
function parseJSONString(s)
{if(s.length>1){return window.eval('('+s+')');}else{return null;}}
function setLocalStrings(obj)
{extend(translate,obj);}
function translate(key,params)
{var value=key in translate?translate[key]:key;if(typeof params==='object')
{value=String(value).replace(/\{(\w+)\}/g,function(m){return m in params?params[m]:m;});}
return value;}
function keys(obj)
{var r=[];for(var k in obj)
{r.push(k);}
return r;}
function values(obj,attr)
{var r=[];for(var k in obj)
{r.push(attr?obj[k][attr]:obj[k]);}
return r;}
function walkItems(root,name,func,sink,depth)
{var data=null,subdata=null;var items=root[name];var arraySink=sink&&sink instanceof Array;if(depth===undefined)
{depth=0;}
for(var k in items)
{var item=items[k];if(!arraySink)
{func(item,sink,depth);}
if(item&&item[name])
{subdata=walkItems(item,name,func,arraySink?[]:sink,depth+1);}
if(arraySink)
{data=func(item,subdata,depth);if(data!==undefined&&subdata!==undefined)
{data[name]=subdata;}
sink.push(data);}}
return sink;}
function inherits(subClass,baseClass)
{function inheritance(){}
inheritance.prototype=baseClass.prototype;subClass.prototype=new inheritance();subClass.prototype.constructor=subClass;subClass.baseConstructor=baseClass;subClass.superClass=baseClass.prototype;}
function extend(destination,source,nochain,nooverwrite){for(var property in source)
{if(nochain&&source.hasOwnProperty(property)){continue;}
if(nooverwrite&&destination.hasOwnProperty(property)){continue;}
var value=source[property];destination[property]=value;}
return destination;}
function launchTarget(target){onItemUndeliver();mlaunch=msequencer.navigateStr(target);if(mlaunch.mSeqNonContent==null){onItemDeliver(activities[mlaunch.mActivityID]);}else{loadPage(gConfig.specialpage_url+"&page="+mlaunch.mSeqNonContent);}}
function launchNavType(navType){if(navType=='SuspendAll'){pubAPI.cmi.exit="suspend";pubAPI.cmi.entry="resume";activities[msequencer.mSeqTree.mCurActivity.mActivityID].exit="suspend";activities[msequencer.mSeqTree.mCurActivity.mActivityID].entry="resume";}
onItemUndeliver();mlaunch=new ADLLaunch();if(navType==='Start'){mlaunch=msequencer.navigate(NAV_START);}
if(navType==='ResumeAll'){mlaunch=msequencer.navigate(NAV_RESUMEALL);}
if(navType==='Exit'){mlaunch=msequencer.navigate(NAV_EXIT);}
if(navType==='ExitAll'){mlaunch=msequencer.navigate(NAV_EXITALL);}
if(navType==='Abandon'){mlaunch=msequencer.navigate(NAV_ABANDON);}
if(navType==='AbandonAll'){mlaunch=msequencer.navigate(NAV_ABANDONALL);}
if(navType==='SuspendAll'){mlaunch=msequencer.navigate(NAV_SUSPENDALL);if(typeof headers!=="object"){headers={};}
headers['Accept']='text/javascript';headers['Accept-Charset']='UTF-8';var acts=msequencer.mSeqTree.mActivityMap;var curtracking=new Object();var tracking=new Object();var states=new Object();var root=new Object();for(var element in msequencer){if(!(msequencer[element]instanceof Object)){root[element]=msequencer[element];}}
for(var element in acts){curtracking[element]=acts[element].mCurTracking;tracking[element]=acts[element].mTracking;if(!states[element]){states[element]=new Object();}
for(subelement in acts[element]){if(!(acts[element][subelement]instanceof Object)&&!(acts[element][subelement]instanceof Array)){states[element][subelement]=acts[element][subelement];}}}
var validreq=msequencer.mSeqTree.mValidReq;var lastleaf=msequencer.mSeqTree.mLastLeaf;var firstcandidate=msequencer.mSeqTree.mFirstCandidate.mActivityID;var suspendall=msequencer.mSeqTree.mSuspendAll.mActivityID;var curactivity=msequencer.mSeqTree.mCurActivity.mActivityID;var suspendedTree=new Object();suspendedTree['mCurTracking']=curtracking;suspendedTree['mTracking']=tracking;suspendedTree['States']=states;suspendedTree['mCurActivity']=null;suspendedTree['mValidReq']=validreq;suspendedTree['mLastLeaf']=lastleaf;suspendedTree['mFirstCandidate']=firstcandidate;suspendedTree['mSuspendAll']=suspendall;suspendedTree['root']=root;var r=sendAndLoad(this.config.suspend_url,toJSONString(suspendedTree),null,null,null,headers);}
if(navType==='Previous'){mlaunch=msequencer.navigate(NAV_PREVIOUS);}
if(navType==='Continue'){mlaunch=msequencer.navigate(NAV_CONTINUE);}
if(mlaunch.mActivityID){onItemDeliver(activities[mlaunch.mActivityID]);}else{loadPage(gConfig.specialpage_url+"&page="+mlaunch.mSeqNonContent);}}
function onDocumentClick(e)
{e=new UIEvent(e);var target=e.srcElement;if(target.tagName!=='A'||!target.id||  target.className.match(/disabled/))
{}
else if(target.id.substr(0,3)==='nav')
{var navType=target.id.substr(3);launchNavType(navType);}
else if(target.id.substr(0,3)===ITEM_PREFIX)
{if(e.altKey){}
else
{mlaunch=msequencer.navigateStr(target.id.substr(3));if(mlaunch.mSeqNonContent==null){onItemUndeliver();onItemDeliver(activities[mlaunch.mActivityID]);}else{loadPage(gConfig.specialpage_url+"&page="+mlaunch.mSeqNonContent);}}}
else if(typeof window[target.id+'_onclick']==="function")
{window[target.id+'_onclick'](target);}
else if(target.target==="_blank")
{return;}
e.stop();}
function setState(newState)
{replaceClass(document.body,guiState+'State',newState+'State');guiState=newState;}
function loadPage(src){if(mlaunch.mSeqNonContent!="_TOC_"&&mlaunch.mSeqNonContent!="_SEQABANDON_"&&mlaunch.mSeqNonContent!="_SEQABANDONALL_"){toggleClass('navContinue','disabled',true);toggleClass('navExit','disabled',true);toggleClass('navPrevious','disabled',true);toggleClass('navResumeAll','disabled',true);toggleClass('navExitAll','disabled',true);toggleClass('navStart','disabled',true);toggleClass('navSuspendAll','disabled',true);toggleClass('treeToggle','disabled',true);}
var elm=window.document.getElementById(RESOURCE_PARENT);if(!elm)
{return window.alert("Window Container not found");}
var h=elm.clientHeight-20;if(self.innerHeight&&navigator.userAgent.indexOf("Safari")!=-1)
{h=self.innerHeight-60;}
RESOURCE_NAME="SPECIALPAGE";var resContainer=window.document.getElementById("res");resContainer.src=src;resContainer.name=RESOURCE_NAME;onWindowResize();save_global_objectives();if(treeView==true&&mlaunch.mSeqNonContent!="_TOC_"&&mlaunch.mSeqNonContent!="_SEQABANDON_"&&mlaunch.mSeqNonContent!="_SEQABANDONALL_"){toggleView();}}
function setInfo(name,values)
{var elm=all('infoLabel');var txt=translate(name,values);if(elm)
{window.top.document.title=elm.innerHTML=txt;}}
function setToc()
{var tree=new Array();buildNavTree(rootAct,"item",tree);}
function updateControls(controlState)
{if(mlaunch!=null){toggleClass('navContinue','disabled',(mlaunch.mNavState.mContinue==false||typeof(activities[mlaunch.mActivityID].hideLMSUIs['continue'])=="object"));toggleClass('navExit','disabled',(mlaunch.mNavState.mContinueExit==false||typeof(activities[mlaunch.mActivityID].hideLMSUIs['exit'])=="object"));toggleClass('navPrevious','disabled',(mlaunch.mNavState.mPrevious==false||typeof(activities[mlaunch.mActivityID].hideLMSUIs['previous'])=="object"));toggleClass('navResumeAll','disabled',mlaunch.mNavState.mResume==false);if(mlaunch.mActivityID){toggleClass('navExitAll','disabled',typeof(activities[mlaunch.mActivityID].hideLMSUIs['exitAll'])=="object");}
toggleClass('navStart','disabled',mlaunch.mNavState.mStart==false);toggleClass('navSuspendAll','disabled',(mlaunch.mNavState.mSuspend==false||typeof(activities[mlaunch.mActivityID].hideLMSUIs['suspendAll'])=="object"));}}
function setResource(id,url,base)
{url=base+url;if(!top.frames[RESOURCE_NAME])
{var elm=window.document.getElementById(RESOURCE_PARENT);if(!elm)
{return window.alert("Window Container not found");}
var h=elm.clientHeight-20;if(self.innerHeight&&navigator.userAgent.indexOf("Safari")!=-1)
{h=self.innerHeight-60;}
var resContainer=window.document.getElementById("res");resContainer.src=url;resContainer.name=RESOURCE_NAME;}
else
{open(url,RESOURCE_NAME);}
if(guiItem)
{removeClass(guiItem,"current");removeClass(guiItem,"running");}
guiItem=all(ITEM_PREFIX+id);if(guiItem)
{removeClass(guiItem,"not_attempted",1);removeClass(guiItem,"incomplete",1);removeClass(guiItem,"completed",1);removeClass(guiItem,"failed",1);removeClass(guiItem,"passed",1);addClass(guiItem,"current");addClass(guiItem,"running");}
onWindowResize();adlnavreq=false;sclogdump("Launched: "+id,"info");sclogflush();}
function removeResource(callback)
{if(guiItem)
{removeClass(guiItem,"current");}
var resContainer=window.document.getElementById("res");resContainer.src="about:blank";resContainer.name=RESOURCE_NAME;if(typeof(callback)==='function')
{callback();}}
function onWindowResize()
{var hd=document.documentElement.clientHeight;var hb=document.body.clientHeight;if(self.innerHeight&&navigator.userAgent.indexOf("Safari")!=-1)
{hd=self.innerHeight;}
var tot=hd?hd:hb;var elm=all(RESOURCE_TOP);var hh=(tot-elm.offsetTop-4);var h=(tot-elm.offsetTop-4)+'px';elm=all("treeView");var factor=1;if(logState==true){factor=0.7;}
if(elm)
{elm.style.height=(hh*factor-30)+"px";}
elm=all("ilLog");if(elm)
{if(logState==true){elm.style.height=(hh*0.3)+"px";}else{elm.style.height="0px";}}
elm=all("res");if(elm)
{elm.style.height=h;}}
function buildNavTree(rootAct,name,tree){var tocView=all('treeView');treeYUI=new YAHOO.widget.TreeView(tocView);var root=treeYUI.getRoot();if(mlaunch.mNavState.mChoice!=null){var id=rootAct.id;if(rootAct.isvisible==true&&typeof(mlaunch.mNavState.mChoice[id])=="object"){var rootNode=new YAHOO.widget.TextNode(rootAct.title,root,true);rootNode.href="#this";rootNode.target="_self";rootNode.labelElId=ITEM_PREFIX+rootAct.id;}}
build(rootAct,rootNode);function build(rootAct,attach){if(rootAct.item){for(var i=0;i<rootAct.item.length;i++){var id=rootAct.item[i].id;if(mlaunch.mNavState.mChoice!=null){if(rootAct.item[i].isvisible==true&&typeof(mlaunch.mNavState.mChoice[id])=="object"){var sub=new YAHOO.widget.TextNode({label:rootAct.item[i].title,id:ITEM_PREFIX+rootAct.item[i].id},attach,true);sub.href="#this";sub.target="_self";sub.labelElId=ITEM_PREFIX+rootAct.item[i].id;}}
if(rootAct.item[i].item){build(rootAct.item[i],sub);}}}}
treeYUI.draw();treeYUI.expandAll();}
function abortNavigation()
{state=ABORTING;}
function init(config)
{function camWalk(cam,act)
{function move(act,prop,newprop,id)
{var k;var cls=this[prop.charAt().toUpperCase()+prop.substr(1)]||Object;if(!act[prop]){return;}
while((k=act[prop].pop()))
{var subact=new cls();act[newprop][k[id]?k[id]:'$']=subact;for(var kk in k)
{setItemValue(kk,subact,k);}}
delete act[prop];}
var k,i,ni,seq,v;seq=cam.sequencingId in seqs?seqs[cam.sequencingId]:{};for(k in seq)
{setItemValue(k,act,seq);}
for(k in cam)
{setItemValue(k,act,cam);}
act.index=activitiesByNo.length;activitiesByNo.push(act);act.cp_node_id=act.foreignId;activitiesByCAM[act.foreignId]=act;activities[act.id]=act;if(cam.item)
{act.item=new Array();var availableChildren=[];for(i=0,ni=cam.item.length;i<ni;i+=1)
{var subact=new Activity();subact.parent=act;camWalk(cam.item[i],subact);availableChildren.push(subact);act.item.push(subact);}
act.availableChildren=availableChildren;}
move(act,"objective","objectives","objectiveID");move(act,"hideLMSUI","hideLMSUIs","value");move(act,"rule","rules","foreignId");act.primaryObjective=act;for(k in act.objectives)
{move(act.objectives[k],"mapinfo","mapinfos","targetObjectiveID");for(var l in act.objectives[k].mapinfos)
{var dat=sharedObjectives[l];if(!dat)
{dat=new Objective();dat.id=l;dat.cmi_node_id=globalAct.cmi_node_id;sharedObjectives[l]=dat;}}
if(act.objectives[k].primary)
{act.primaryObjective=act.objectives[k];}}}
this.config=config;gConfig=config;setInfo('loading');setState('loading');setLocalStrings({'resource_undelivered':'Resource unloaded. Use navigation to load a new one.'});setLocalStrings(this.config.langstrings);setTimeout(onWindowLoad,0);var cam=this.config.cp_data||sendJSONRequest(this.config.cp_url);if(!cam)return alert('Fatal: Could not load content data.')
var adlAct=this.config.adlact_data||sendJSONRequest(this.config.adlact_url);if(!adlAct){return alert('Fatal: Could not load ADLActivityTree.');}else{var tree;adlTree=buildADLtree(adlAct,tree);adlTree=setParents(adlTree);var actTree=new SeqActivityTree(this.config.course_id,this.config.learner_id,this.config.scope,adlTree);actTree.setDepths();actTree.setTreeCount();actTree.scanObjectives();actTree.buildActivityMap();msequencer.setActivityTree(actTree);}
var seqs=cam.sequencing?cam.sequencing:[];for(var i=seqs.length;i--;)
{seq=seqs.pop();seqs[seq.id]=seq;delete seq.foreignId;}
for(var k in seqs)
{seq=seqs[k];if(seq.sequencingId)
{var baseseq=seqs[seq.sequencingId];for(k in baseseq)
{if(seq[k]===undefined)
{seq[k]=baseseq[k];}}
delete seq.id;delete seq.sequencingId;}}
for(k in cam)
{if(typeof cam[k]!=="object")
{globalAct[k]=cam[k];}}
globalAct.cp_node_id=globalAct.foreignId;globalAct.index=activitiesByNo.length;activitiesByNo.push(globalAct);activitiesByCAM[globalAct.foreignId]=globalAct;activities[globalAct.id]=globalAct;globalAct.learner_id=this.config.learner_id;globalAct.learner_name=this.config.learner_name;globalAct.auto_review=this.config.auto_review;camWalk(cam.item,rootAct);load();loadGlobalObj();suspendData=sendJSONRequest(this.config.get_suspend_url);var wasSuspended=false;var wasFirstSession;if(suspendData){if(suspendData!=null){wasSuspended=true;}}
if(wasSuspended==true){wasSuspended=true;wasFirstSession=false;for(var element in suspendData.mTracking){msequencer.mSeqTree.mActivityMap[element].mTracking=suspendData.mTracking[element];}
var cur=suspendData.mCurActivity;msequencer.mSeqTree.mCurActivity=null;var first=suspendData.mFirstCandidate;msequencer.mSeqTree.mFirstCandidate=null;msequencer.mSeqTree.mLastLeaf=suspendData.mLastLeaf;var suspendAll=suspendData.mSuspendAll;msequencer.mSeqTree.mSuspendAll=msequencer.mSeqTree.mActivityMap[suspendAll];var valid=suspendData.mValidReq;msequencer.mSeqTree.mValidReq=valid;for(var element in suspendData.root){msequencer[element]=suspendData.root[element];}
for(var element in suspendData.States){var source=suspendData.States[element];for(var subelement in source){msequencer.mSeqTree.mActivityMap[element][subelement]=source[subelement];}}
var tempCur=new Object();for(var element in suspendData.mCurTracking){tempCur[element]=new ADLTracking;for(var subelement in suspendData.mCurTracking[element]){if(subelement!="mObjectives"){tempCur[element][subelement]=suspendData.mCurTracking[element][subelement];}else{for(var obj in suspendData.mCurTracking[element]["mObjectives"]){tempCur[element]["mObjectives"][obj]=new SeqObjectiveTracking();for(var prop in suspendData.mCurTracking[element]["mObjectives"][obj]){tempCur[element]["mObjectives"][obj][prop]=suspendData.mCurTracking[element]["mObjectives"][obj][prop];}}}}}
for(var element in tempCur){msequencer.mSeqTree.mActivityMap[element]["mCurTracking"]=tempCur[element];}}
if(wasSuspended==true){mlaunch=msequencer.navigate(NAV_RESUMEALL);}else{mlaunch=msequencer.navigate(NAV_NONE);if(mlaunch.mNavState.mStart){mlaunch=msequencer.navigate(NAV_START);}}
var tolaunch=null;var count=0;for(var myitem in mlaunch.mNavState.mChoice){if(mlaunch.mNavState.mChoice[myitem].mInChoice==true&&mlaunch.mNavState.mChoice[myitem].mIsSelectable==true&&mlaunch.mNavState.mChoice[myitem].mIsEnabled==true){tolaunch=mlaunch.mNavState.mChoice[myitem].mID;count=count+1;}}
if(count==1||this.config.hide_navig==1){toggleView();}
if(mlaunch.mSeqNonContent==null){onItemDeliver(activities[mlaunch.mActivityID]);}else{if(count==1&&tolaunch!=null){launchTarget(tolaunch);}else{loadPage(gConfig.specialpage_url+"&page="+mlaunch.mSeqNonContent);}}
updateControls();updateNav();if(this.config.session_ping>0)
{setTimeout("pingSession()",this.config.session_ping*1000);}}
function pingSession()
{var r=sendJSONRequest(this.config.ping_url);setTimeout("pingSession()",this.config.session_ping*1000);}
function loadGlobalObj(){var globalObj=this.config.globalobj_data||sendJSONRequest(this.config.get_gobjective_url);if(globalObj){if(globalObj.satisfied){adl_seq_utilities.satisfied=globalObj.satisfied;}
if(globalObj.measure){adl_seq_utilities.measure=globalObj.measure;}
if(globalObj.status){adl_seq_utilities.status=globalObj.status;}}}
function buildADLtree(act,unused){var obj=new Object;var obj1,res,res2;for(var index in act){var value;if((index.substr(0,1)=="_")){obj=eval("new "+index.substr(1)+"()");obj1=buildADLtree(act[index],null);for(var i in obj1){obj[i]=obj1[i];}}else if((act[index]instanceof Array)){var toset=new Array();var temp=act[index];for(var i=0;i<temp.length;i++){res=buildADLtree(temp[i],null);toset.push(res);}
if(index!="mActiveChildren"){obj[index]=toset;}
if(index=="mChildren"){obj["mActiveChildren"]=toset;}}else if((act[index]instanceof Object)){res2=buildADLtree(act[index],null);obj[index]=res2;}else if(!(act[index]instanceof Array)&&!(index.substr(0,1)=="_")){value=act[index];if(index=="mLearnerID"){value=this.config.learner_id;}
if(index=="mScopeID"){value=this.config.scope;}
obj[index]=value;}}
return obj;}
function setParents(obj){for(var index in obj){if(index=="mChildren"){var temp=obj[index];if(temp instanceof Array){if(temp.length>0){for(var i=0;i<temp.length;i++) {temp[i]['mParent']=obj;var ch=setParents(temp[i]);temp[i]=ch;}}}}}
return obj;}
function load()
{var cmi=this.config.cmi_data||sendJSONRequest(this.config.cmi_url);if(!cmi)return alert('FATAL: Could not load userdata!');var k,i,ni,row,act,j,nj,dat,id;var cmi_node_id,cmi_interaction_id;if(!remoteMapping)
{remoteMapping=cmi.schema;for(k in remoteMapping)
{for(i=remoteMapping[k].length;i--;)
{remoteMapping[k][remoteMapping[k][i]]=i;}}
while((row=cmi.data['package'].pop()))
{for(i=remoteMapping['package'].length;i--;)
{globalAct[remoteMapping['package'][i]]=row[i];}
globalAct.learner_id=globalAct.user_id;}}
for(i=cmi.data.node.length;i--;)
{row=cmi.data.node[i];act=activitiesByCAM[row[remoteMapping.node.cp_node_id]];for(j=remoteMapping.node.length;j--;)
{if(row[j]===null){continue;}
setItemValue(j,act,row,remoteMapping.node[j]);}
activitiesByCMI[act.cmi_node_id]=act;}
for(i=0;i<cmi.data.comment.length;i++)
{row=cmi.data.comment[i];dat=new Comment();for(j=remoteMapping.comment.length;j--;)
{setItemValue(j,dat,row,remoteMapping.comment[j]);}
act=activitiesByCMI[row[remoteMapping.comment.cmi_node_id]];act.comments[dat.cmi_comment_id]=dat;}
var interactions={};for(i=cmi.data.interaction.length;i--;)
{row=cmi.data.interaction[i];dat=new Interaction();for(j=remoteMapping.interaction.length;j--;)
{setItemValue(j,dat,row,remoteMapping.interaction[j]);}
act=activitiesByCMI[row[remoteMapping.interaction.cmi_node_id]];act.interactions[dat.cmi_interaction_id]=dat;interactions[dat.cmi_interaction_id]=dat;}
for(i=cmi.data.correct_response.length;i--;)
{row=cmi.data.correct_response[i];dat=new CorrectResponse();for(j=remoteMapping.correct_response.length;j--;)
{setItemValue(j,dat,row,remoteMapping.correct_response[j]);}
act=interactions[row[remoteMapping.correct_response.cmi_interaction_id]];act.correct_response[dat.cmi_correct_response_id]=dat;}
for(i=0;i<cmi.data.objective.length;i++)
{row=cmi.data.objective[i];id=row[remoteMapping.objective.id];cmi_interaction_id=row[remoteMapping.objective.cmi_interaction_id];cmi_node_id=row[remoteMapping.objective.cmi_node_id];if(cmi_interaction_id===null||cmi_interaction_id==0)
{act=activitiesByCMI[cmi_node_id];if(act&&act.objectives[id])
{dat=act.objectives[id];}
else if(act)
{dat=new Objective();act.objectives[id]=dat;}
else if(sharedObjectives[id])
{dat=sharedObjectives[id];}
for(j=remoteMapping.objective.length;j--;)
{if(typeof dat!="undefined"){dat[remoteMapping.objective[j]]=row[j];}}}
else
{interactions[cmi_interaction_id].objectives[id]={id:id};}
dat=new Objective();for(j=remoteMapping.objective.length;j--;)
{setItemValue(j,dat,row,remoteMapping.objective[j]);}
act=activitiesByCMI[row[remoteMapping.objective.cmi_node_id]];act.objectives[dat.id]=dat;}}
function save()
{function walk(collection,type)
{var schem=remoteMapping[type];var res=result[type];for(var k in collection)
{var item=collection[k];if(item.dirty===0){continue;}
if(item.options){if(item.options.notracking===true)
{continue;}}
if(type=="objective"){if(item.id==null){alert("Skipped");continue;}}
var data=[];for(var i=0,ni=schem.length;i<ni;i++)
{data.push(item[schem[i]]);}
res.push(data);for(z in collection[k])
{if(z=='interactions'||z=='comments'||z=="objectives"||z=="correct_responses")
{for(y in collection[k][z])
{collection[k][z][y]['cmi_node_id']=collection[k]['cmi_node_id'];}
walk(collection[k][z],z.substr(0,z.length-1));}}
if(item.dirty!==2&&type=="node"){continue;}}}
if(save.timeout)
{window.clearTimeout(save.timeout);}
var result={};for(var k in remoteMapping)
{result[k]=[];}
walk(sharedObjectives,"objective");walk(activities,'node');if(!result.node.length){return;}
result=this.config.cmi_url?sendJSONRequest(this.config.cmi_url,result):{};var i=0;for(k in result)
{i++;var act=activitiesByCAM[k];if(act)
{act.dirty=0;}}
if(typeof this.config.time==="number"&&this.config.time>10)
{clearTimeout(save.timeout);save.timeout=window.setTimeout(save,this.config.time*1000);}
setTimeout("updateNav(true)",1000);return i;}
function getAPI(cp_node_id)
{function getAPISet(k,dat,api)
{if(dat!=undefined&&dat!==null)
{api[k]=dat.toString();}}
function getAPIWalk(model,data,api)
{var k,i;if(!model.children)return;for(k in model.children)
{var mod=model.children[k];var dat;if(data!=null){if(k=="comments_from_learner"||k=="comments_from_lms"){dat=data['comments'];}else{dat=data[k];}}else{dat=null;}
if(mod.type===Object)
{api[k]={};for(var i=mod.mapping.length;i--;)
{if(k=="score"){var d=new Object();d['scaled']=data['scaled'];d['raw']=data['raw'];d['min']=data['min'];d['max']=data['max'];api[k]=d;}else{getAPISet(mod.mapping[i],dat,api[k]);}}}
else if(mod.type===Array)
{api[k]=[];if(mod.mapping)
{}
for(i in dat)
{if(mod.mapping&&!mod.mapping.func(dat[i]))continue;var d=getAPIWalk(mod,dat[i],{});var idname;if(k=="comments_from_learner"||k=="comments_from_lms"){idname="cmi_comment_id";}else{idname='cmi_'+k.substr(0,k.length-1)+'_id';}
if(dat[i]['scaled']){d['score']['scaled']=dat[i]['scaled'];}
if(dat[i]['max']){d['score']['max']=dat[i]['max'];}
if(dat[i]['min']){d['score']['min']=dat[i]['min'];}
if(dat[i]['raw']){d['score']['raw']=dat[i]['raw'];}
if(dat[i]['objectiveID']){d['id']=dat[i]['objectiveID'];}else{d[idname]=dat[i][idname];}
api[k].push(d);}}
else
{getAPISet(k,dat,api);}}
return api;}
var api={cmi:{},adl:{}};var data=activitiesByCAM[cp_node_id];getAPIWalk(Runtime.models.cmi.cmi,data,api.cmi);return api;}
function setItemValue(key,dest,source,destkey)
{if(source&&source.hasOwnProperty(key))
{var d=source[key];var temp=d;if(!isNaN(parseFloat(d))){d=Number(d);}else if(d==="true"){d=true;}else if(d==="false"){d=false;}
if(key=="title"){d=temp;}
dest[destkey?destkey:key]=d;}}
function setAPI(cp_node_id,api)
{function setAPIWalk(model,data,api)
{var k,i;if(!model.children)return;for(k in model.children)
{var mod=model.children[k];var ap=api[k];if(mod.type===Object)
{for(var i=mod.mapping.length;i--;)
{setItemValue(mod.mapping[i],data,ap);}}
else if(mod.type===Array)
{var map=mod.mapping||{name:k.substr(0,k.length-1)};map.dbtable=map.name+"s";map.dbname='cmi_'+map.name+'_id';map.clsname=map.name.charAt().toUpperCase()+map.name.substr(1);for(i in ap)
{var dat=data[map.dbtable];var row=ap[i];if(map.refunc)
{var remap=map.refunc(dat[i]);row[remap[0]]=remap[1];}
if(!row[map.dbname])row[map.dbname]='$'+(remoteInsertId++);var id=row[mod.unique]||row[map.dbname];var cls=this[map.clsname]||Object;if(!dat[id])
{dat[id]=new cls;}
setAPIWalk(mod,dat[id],row);}}
else
{setItemValue(k,data,api);}}}
var data=activitiesByCAM[cp_node_id];setAPIWalk(Runtime.models.cmi.cmi,data,api.cmi);data.dirty=2;return true;}
function dirtyCount()
{var c=0;for(var i=activities.length;i--;)
{c+=Number(activities[i].dirty);}
return c;}
function onWindowLoad()
{attachUIEvent(window,'unload',onWindowUnload);attachUIEvent(document,'click',onDocumentClick);setInfo('');setState('playing');attachUIEvent(window,'resize',onWindowResize);onWindowResize();}
function onWindowUnload()
{onItemUndeliver(true);save_global_objectives();save();}
function onItemDeliver(item)
{var url=item.href,v;if(item.sco)
{var data=getAPI(item.foreignId);data.adl={nav:{request_valid:{}}};var validRequests=msequencer.mSeqTree.getValidRequests();data.adl.nav.request_valid['continue']=String(validRequests['mContinue']);data.adl.nav.request_valid['previous']=String(validRequests['mPrevious']);var choice=validRequests['mChoice'];for(var k in choice){}
data.cmi.learner_name=globalAct.learner_name;data.cmi.learner_id=globalAct.learner_id;data.cmi.cp_node_id=item.foreignId;data.cmi.session_time=undefined;data.cmi.completion_threshold=item.completionThreshold;data.cmi.launch_data=item.dataFromLMS;data.cmi.time_limit_action=item.timeLimitAction;data.cmi.max_time_allowed=item.attemptAbsoluteDurationLimit;if(item.objectives)
{for(k in item.objectives){v=item.objectives[k];if(v.primary==true){if(v.satisfiedByMeasure&&v.minNormalizedMeasure!==undefined)
{v=v.minNormalizedMeasure;}
else if(v.satisfiedByMeasure)
{v=1.0;}
else
{v=null;}
data.cmi.scaled_passing_score=v;break;}}}
pubAPI=data;currentAPI=window[Runtime.apiname]=new Runtime(data,onCommit,onTerminate);}
syncSharedCMI(item);updateNav();updateControls();scoStartTime=currentTime();item.options=new Object();item.options.notracking=false;if(globalAct.auto_review){if(item.completion_status=='completed'||item.success_status=='passed'){pubAPI.mode="review";item.options.notracking=true;}}
if(currentAPI){if((item.exit=="normal"||item.exit==""||item.exit=="time-out"||item.exit=="logout")&&(item.exit!="suspend"&&item.entry!="resume")){err=currentAPI.SetValueIntern("cmi.completion_status","unknown");err=currentAPI.SetValueIntern("cmi.success_status","unknown");err=currentAPI.SetValueIntern("cmi.entry","ab-initio");pubAPI.cmi.entry="ab-initio";pubAPI.cmi.suspend_data=null;pubAPI.cmi.total_time="PT0H0M0S";}
if(item.exit=="suspend"){pubAPI.cmi.entry="resume";pubAPI.cmi.exit="";}
if(item.exit=="time-out"||item.exit=="logout"){err=currentAPI.SetValueIntern("cmi.session_time","PT0H0M0S");pubAPI.cmi.total_time="PT0H0M0S";}}
setResource(item.id,item.href+"?"+item.parameters,this.config.package_url);}
function syncSharedCMI(item){var mStatusVector=msequencer.getObjStatusSet(item.id);var mObjStatus=new ADLObjStatus();var obj;var err
if(mStatusVector!=null){for(i=0;i<mStatusVector.length;i++){var idx=-1;mObjStatus=mStatusVector[i];var objCount=currentAPI.GetValueIntern("cmi.objectives._count");for(var j=0;j<objCount;j++){var obj="cmi.objectives."+j+".id";var nr=currentAPI.GetValueIntern(obj);if(nr==mObjStatus.mObjID){idx=j;break;}}
if(idx!=-1){obj="cmi.objectives."+idx+".success_status";if(mObjStatus.mStatus.toLowerCase()=="satisfied")
{err=currentAPI.SetValueIntern(obj,"passed");}
else if(mObjStatus.mStatus.toLowerCase()=="notsatisfied")
{err=currentAPI.SetValueIntern(obj,"failed");}
obj="cmi.objectives."+idx+".score.scaled";if(mObjStatus.mHasMeasure==true&&mObjStatus.mMeasure!=0){err=currentAPI.SetValueIntern(obj,mObjStatus.mMeasure);}}}}}
function syncCMIADLTree(){var mPRIMARY_OBJ_ID=null;var masteryStatus=null;var sessionTime=null;var entry=null;var normalScore=-1.0;var completionStatus=null;var SCOEntry=null;var suspended=false;SCOEntry=currentAPI.GetValueIntern("cmi.exit");completionStatus=currentAPI.GetValueIntern("cmi.completion_status");if(completionStatus=="not attempted"){completionStatus="incomplete";}
masteryStatus=currentAPI.GetValueIntern("cmi.success_status");SCOEntry=currentAPI.GetValueIntern("cmi.entry");score=currentAPI.GetValueIntern("cmi.score.scaled");sessionTime=currentAPI.GetValueIntern("cmi.session_time");var act=msequencer.mSeqTree.getActivity(mlaunch.mActivityID);var primaryObjID=null;var foundPrimaryObj=false;var setPrimaryObjSuccess=false;var setPrimaryObjScore=false;objs=act.getObjectives();if(objs!=null){for(var j=0;j<objs.length;j++){obj=objs[j];if(obj.mContributesToRollup==true){if(obj.mObjID!=null){primaryObjID=obj.mObjID;}
break;}}}
var numObjs=currentAPI.GetValueIntern("cmi.objectives._count");for(var i=0;i<numObjs;i++){var obj="cmi.objectives."+i+".id";var objID=currentAPI.GetValueIntern(obj);if(primaryObjID!=null&&objID==primaryObjID)
{foundPrimaryObj=true;}else{foundPrimaryObj=false;}
obj="cmi.objectives."+i+".success_status";objMS=currentAPI.GetValueIntern(obj);if(objMS=="passed"){msequencer.setAttemptObjSatisfied(mlaunch.mActivityID,objID,"satisfied");if(foundPrimaryObj==true)
{setPrimaryObjSuccess=true;}}
else if(objMS=="failed")
{msequencer.setAttemptObjSatisfied(mlaunch.mActivityID,objID,"notSatisfied");if(foundPrimaryObj==true)
{setPrimaryObjSuccess=true;}}
else
{msequencer.setAttemptObjSatisfied(mlaunch.mActivityID,objID,"unknown");}
obj="cmi.objectives."+i+".score.scaled";objScore=currentAPI.GetValueIntern(obj);if(objScore!=""&&objScore!="unknown"&&objScore!=null){normalScore=objScore;msequencer.setAttemptObjMeasure(mlaunch.mActivityID,objID,normalScore);if(foundPrimaryObj==true){setPrimaryObjScore=true;}}
else
{msequencer.clearAttemptObjMeasure(mlaunch.mActivityID,objID);}}
msequencer.setAttemptProgressStatus(mlaunch.mActivityID,completionStatus);if(SCOEntry=="resume"){msequencer.reportSuspension(mlaunch.mActivityID,true);}else{msequencer.reportSuspension(mlaunch.mActivityID,false);}
if(masteryStatus=="passed")
{msequencer.setAttemptObjSatisfied(mlaunch.mActivityID,mPRIMARY_OBJ_ID,"satisfied");}
else if(masteryStatus=="failed")
{msequencer.setAttemptObjSatisfied(mlaunch.mActivityID,mPRIMARY_OBJ_ID,"notSatisfied");}
else
{if(setPrimaryObjSuccess==false)
{msequencer.setAttemptObjSatisfied(mlaunch.mActivityID,mPRIMARY_OBJ_ID,"unknown");}}
if(score!=""&&score!="unknown"){normalScore=score;msequencer.setAttemptObjMeasure(mlaunch.mActivityID,mPRIMARY_OBJ_ID,normalScore);}
else{if(setPrimaryObjScore==false)
{msequencer.clearAttemptObjMeasure(mlaunch.mActivityID,mPRIMARY_OBJ_ID);}}}
function onItemUndeliver(noControls)
{if(noControls!=true){updateNav();updateControls();}
removeResource(undeliverFinish);}
function undeliverFinish(){if(currentAPI)
{syncCMIADLTree();var stat=pubAPI.cmi.exit;save_global_objectives();save();if(state!=2){}}
currentAPI=window[Runtime.apiname]=null;if(currentAct)
{currentAct.accessed=currentTime()/1000;if(!currentAct.dirty)currentAct.dirty=1;}}
function syncDynObjectives(){var objectives=pubAPI.cmi.objectives;var act=activities[mlaunch.mActivityID].objectives;for(var i=0;i<objectives.length;i++){if(objectives[i].id){var id=objectives[i].id;var obj=objectives[i];if(!act.id){act[id]=new Objective();act[id]['objectiveID']=id;act[id]['id']=id;for(var element in obj){if(element!="id"&&element!="cmi_objective_id"){if(element!="score"){act[id][element]=obj[element];}
if(element=="score"){for(var subelement in obj[element]){act[id][subelement]=obj[element][subelement];}}}}}}}}
function save_global_objectives(){if(adl_seq_utilities.measure!=null||adl_seq_utilities.satisfied!=null||adl_seq_utilities.status!=null){result=this.config.gobjective_url?sendJSONRequest(this.config.gobjective_url,this.adl_seq_utilities):{};}}
function onNavigationEnd()
{removeResource();}
function onCommit(data)
{return setAPI(data.cmi.cp_node_id,data);}
function onTerminate(data)
{var navReq;switch(data.cmi.exit)
{case"suspend":navReq={type:"suspend"};break;case"logout":navReq={type:"ExitAll"};case"time-out":navReq={type:"ExitAll"};default:break;}
if(data.adl&&data.adl.nav)
{var m=String(data.adl.nav.request).match(/^(\{target=([^\}]+)\})?(choice|continue|previous|suspendAll|exit(All)?|abandon(All)?)$/);if(m)
{navReq={type:m[3].substr(0,1).toUpperCase()+m[3].substr(1),target:m[2]};}}
if(navReq)
{if(navReq.type!="suspend"){adlnavreq=true;if(navReq.type=="Choice"){launchTarget(navReq.target);}else{launchNavType(navReq.type);}}}
return true;}
var apiIndents={'cmi':{'score':['raw','min','max','scaled'],'learner_preference':['audio_captioning','audio_level','delivery_speed','language']},'objective':{'score':['raw','min','max','scaled']}};function updateNav(ignore){if(!all("treeView")){return;}
if(ignore!=true){setToc();}
var tree=msequencer.mSeqTree.mActivityMap;var disable;for(i in tree){var disable=true;var test=null;if(mlaunch.mNavState.mChoice!=null){test=mlaunch.mNavState.mChoice[i];}
if(test){if(test['mIsSelectable']==true&&test['mIsEnabled']==true){disable=false;}else{disable=true;}}
if(guiItem&&ignore==true){if(guiItem.id==ITEM_PREFIX+tree[i].mActivityID)
{continue;}}
var elm=all(ITEM_PREFIX+tree[i].mActivityID);toggleClass(elm,'disabled',disable);if(activities[tree[i].mActivityID].sco&&activities[tree[i].mActivityID].href){var node_stat_completion=activities[tree[i].mActivityID].completion_status;if(node_stat_completion==null||node_stat_completion=="not attempted"){toggleClass(elm,"not_attempted",1);}
if(node_stat_completion=="unknown"||node_stat_completion=="incomplete"){removeClass(elm,"not_attempted",1);toggleClass(elm,"incomplete",1);}
if(node_stat_completion=="browsed"){removeClass(elm,"not_attempted",1);toggleClass(elm,"browsed",1);}
if(node_stat_completion=="completed"){removeClass(elm,"not_attempted",1);removeClass(elm,"incomplete",1);removeClass(elm,"browsed",1);toggleClass(elm,"completed",1);}
var node_stat_success=activities[tree[i].mActivityID].success_status;if(node_stat_success=="passed"||node_stat_success=="failed"){if(node_stat_success=="passed"){removeClass(elm,"failed",1);toggleClass(elm,"passed",1);}else{removeClass(elm,"passed",1);toggleClass(elm,"failed",1);}}}else{if(elm&&activities[tree[i].mActivityID].href){toggleClass(elm,"asset",1);}}}}
function isIE(versionNumber){var detect=navigator.userAgent.toLowerCase();if(!(navigator&&navigator.userAgent&&navigator.userAgent.toLowerCase)){return false;}else{if(detect.indexOf('msie')+1){var ver=function(){var rv=-1;if(navigator.appName=='Microsoft Internet Explorer'){var ua=navigator.userAgent;var re=new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");if(re.exec(ua)!=null){rv=parseFloat(RegExp.$1);}}
return rv;};var valid=true;if((ver>-1)&&(ver<versionNumber)){valid=false;}
return valid;}else{return false}}}
function pausecomp(millis)
{var date=new Date();var curDate=null;do{curDate=new Date();}
while(curDate-date<millis);}
var remoteMapping=null;var remoteInsertId=0;var globalAct=new Activity();var rootAct=new Activity();var activities=new Object();var activitiesByCAM=new Object();var activitiesByCMI=new Object();var activitiesByNo=new Array();var sharedObjectives=new Object();var msequencer=new ADLSequencer();var mlaunch=null;var adlnavreq=null;var treeYUI=null;var logState=false;var treeState=true;var ITEM_PREFIX="itm";var RESOURCE_PARENT="tdResource";var RESOURCE_NAME="frmResource";var RESOURCE_TOP="mainTable";var guiItem;var guiState;var gConfig;var RUNNING=1;var WAITING=0;var QUERYING=-1;var ABORTING=-2;var EXIT_ACTIONS=/^exit$/i;var POST_ACTIONS=/^exitParent|exitAll|retry|retryAll|continue|previous$/i;var SKIPPED_ACTIONS=/^skip$/i;var STOP_FORWARD_TRAVERSAL_ACTIONS=/^stopForwardTraversal$/i;var HIDDEN_FROM_CHOICE_ACTIONS=/^hiddenFromChoice$/i;var DISABLED_ACTIONS=/^disabled$/i;var state=WAITING;var currentAct=null;var SCOEntryedAct=null;var currentAPI;var scoStartTime=null;var treeView=true;var pubAPI=null;var saveOnCommit=true;window.scorm_init=init;
function Runtime(cmiItem,onCommit,onTerminate,onDebug)
{function GetLastError()
{return String(error);}
function GetErrorString(param)
{if(typeof param!=='string')
{return setReturn(201,'GetError param must be empty string','');}
var e=Runtime.errors[param];return e&&e.message?String(e.message).substr(0,255):'';}
function GetDiagnostic(param)
{return param+': '+(error?String(diagnostic).substr(0,255):' no diagnostic');}
function Initialize(param)
{setReturn(-1,'Initialize('+param+')');if(param!=='')
{return setReturn(201,'param must be empty string','false');}
switch(state)
{case NOT_INITIALIZED:dirty=false;if(cmiItem instanceof Object)
{state=RUNNING;return setReturn(0,'','true');}
else
{return setReturn(102,'','false');}
break;case RUNNING:return setReturn(103,'','false');case TERMINATED:return setReturn(104,'','false');}
return setReturn(103,'','false');}
function Commit(param)
{setReturn(-1,'Commit('+param+')');if(param!=='')
{return setReturn(201,'param must be empty string','false');}
switch(state)
{case NOT_INITIALIZED:return setReturn(142,'','false');case RUNNING:var returnValue=onCommit(cmiItem);if(saveOnCommit==true){save();}
if(returnValue)
{dirty=false;return setReturn(0,'','true');}
else
{return setReturn(391,'Persisting failed','false');}
break;case TERMINATED:return setReturn(143,'','false');}}
function Terminate(param){setReturn(-1,'Terminate('+param+')');if(param!=='')
{return setReturn(201,'param must be empty string','false');}
switch(state)
{case NOT_INITIALIZED:return setReturn(112,'','false');case RUNNING:Runtime.onTerminate(cmiItem,msec);setReturn(-1,'Terminate('+param+') [after wrapup]');saveOnCommit=false;var returnValue=Commit('');saveOnCommit=true;state=TERMINATED;onTerminate(cmiItem);return setReturn(error,'',returnValue);case TERMINATED:return setReturn(113,'','false');}}
function GetValue(sPath)
{setReturn(-1,'GetValue('+sPath+')');switch(state)
{case NOT_INITIALIZED:sclogdump("Not initialized","error");return setReturn(122,'','');case RUNNING:if(typeof(sPath)!=='string')
{sclogdump("201: must be string","error");return setReturn(201,'must be string','');}
if(sPath==='')
{sclogdump("301: cannot be empty string","error");return setReturn(301,'cannot be empty string','');}
var r=getValue(sPath,false);sclogdump("GetValue: Return: "+sPath+" : "+r,"cmi");return error?'':setReturn(0,'',r);case TERMINATED:sclogdump("Error 123: Terminated","error");return setReturn(123,'','');}}
function GetValueIntern(sPath){var r=getValue(sPath,false);return error?'':setReturn(0,'',r);}
function getValue(path,sudo)
{var tokens=path.split('.');return walk(cmiItem,Runtime.models[tokens[0]],tokens,null,sudo,{parent:[]});}
function SetValueIntern(sPath,sValue){if(typeof sValue==="number")
{sValue=sValue.toFixed(3);}
else
{sValue=String(sValue);}
var r=setValue(sPath,sValue);return error?'':setReturn(0,'',r);}
function SetValue(sPath,sValue)
{setReturn(-1,'SetValue('+sPath+', '+sValue+')');sclogdump("Set: "+sPath+" : "+sValue,"cmi");switch(state)
{case NOT_INITIALIZED:sclogdump("Error 132: not initialized","error");return setReturn(132,'','false');case RUNNING:if(typeof(sPath)!=='string')
{sclogdump("Error 201: must be string","error");return setReturn(201,'must be string','false');}
if(sPath==='')
{sclogdump("Error 351: 'Param 1 cannot be empty string","error");return setReturn(351,'Param 1 cannot be empty string','false');}
if(typeof sValue==="number")
{sValue=sValue.toFixed(3);}
else
{sValue=String(sValue);}
try
{var r=setValue(sPath,sValue);if(!error){sclogdump("SetValue-return: "+true,"cmi");}else{sclogdump("SetValue-return: "+false,"error");}
return error?'false':'true';}catch(e)
{sclogdump("351: Exception "+e,"error");return setReturn(351,'Exception '+e,'false');}
break;case TERMINATED:sclogdump("Error 133: Terminated","error");return setReturn(133,'','false');}}
function setValue(path,value,sudo)
{var tokens=path.split('.');return walk(cmiItem,Runtime.models[tokens[0]],tokens,value,sudo,{parent:[]});}
function walk(dat,def,path,value,sudo,extra)
{var setter,token,result,tdat,tdef,k,token2,tdat2,di,token3;setter=typeof value==="string";token=path.shift();if(!def)
{return setReturn(401,'Unknown element: '+token,'false');}
tdat=dat[token];tdef=def[token];if(!tdef)
{return setReturn(401,'Unknown element: '+token,'false');}
if(tdef.type==Function)
{token2=path.shift();result=tdef.children.type.getValue(token2,tdef.children);return setReturn(0,'',result);}
if(path[0]&&path[0].charAt(0)==="_")
{if(path.length>1)
{return setReturn(401,'Unknown element','');}
if(setter)
{return setReturn(404,'read only',false);}
if('_children'===path[0])
{if(!tdef.children)
{return setReturn(301,'Data model element does not have children','');}
result=[];for(k in tdef.children)
{result.push(k);}
return setReturn(0,'',result.join(","));}
if('_count'===path[0])
{return tdef.type!==Array?setReturn(301,'Data model element cannot have count',''):setReturn(0,'',(tdat&&tdat.length?tdat.length:0).toString());}
if(token==="cmi"&&'_version'===path[0])
{return setReturn(0,'','1.0');}}
if(tdef.type==Array)
{token2=path.shift()||"";var m=token2.match(/^([^\d]*)(0|[1-9]\d{0,8})$/);if(token2.length===0||m&&m[1])
{return setReturn(401,'Index expected');}
else if(!m)
{return setReturn(setter?351:301,'Index not an integer');}
token2=Number(token2);tdat=tdat?tdat:new Array();tdat2=tdat[token2];token3=path[0]||null;if(setter)
{if(token2>tdat.length)
{return setReturn(351,'Data model element collection set out of order');}
if(tdef.maxOccur&&token2+1>tdef.maxOccur)
{return setReturn(301,'');}
if(tdat2===undefined)
{tdat2=new Object();}
extra.index=token2;extra.parent.push(dat);if(tdef.unique===token3)
{for(di=tdat.length;di--;)
{if(tdat[di][tdef.unique]===value)
{if(di!==token2)
{extra.error={code:351,diagnostic:"not unique"};break;}}}}
result=walk(tdat2,tdef.children,path,value,sudo,extra);if(!error)
{tdat[token2]=tdat2;dat[token]=tdat;}
return result;}
else if(tdat2)
{return walk(tdat2,tdef.children,path,value,sudo,extra);}
else
{return setReturn(301,'Data Model Collection Element Request Out Of Range');}}
if(tdef.type==Object)
{if(typeof tdat==="undefined")
{if(setter)
{tdat=new Object();extra.parent.push(dat);result=walk(tdat,tdef.children,path,value,sudo,extra);if(!error)
{dat[token]=tdat;}
return result;}
else
{return setReturn(tdef.children[path.pop()]?403:401,'Not inited or defined: '+token);}}
else
{if(setter){extra.parent.push(dat);}
return walk(tdat,tdef.children,path,value,sudo,extra);}}
if(setter)
{if(tdef.writeOnce&&dat[token]&&dat[token]!=value)
{return setReturn(351,'write only once');}
if(tdef.permission===READONLY&&!sudo)
{return setReturn(404,'readonly:'+token);}
if(path.length)
{return setReturn(401,'Unknown element');}
if(tdef.dependsOn){extra.parent.push(dat);var dep=tdef.dependsOn.split(" ");for(di=dep.length;di--;)
{var dj=extra.parent.length-1;var dp=dep[di].split(".");var dpar=extra.parent;if(dpar[dpar.length-dp.length][dp.pop()]===undefined)
{return setReturn(408,'dependency on ..'+dep[di]);}}}
result=tdef.type.isValid(value,tdef,extra);if(extra.error)
{return setReturn(extra.error.code,extra.error.diagnostic);}
if(!result)
{return setReturn(406,'value not valid');}
if(value.indexOf("{order_matters")==0)
{window.order_matters=true;}
dat[token]=value;dirty=true;return setReturn(0,'');}
else
{if(tdef.permission===WRITEONLY&&!sudo)
{return setReturn(405,'writeonly:'+token);}
else if(path.length)
{return setReturn(401,'Unknown element');}
else if(tdef.getValueOf)
{return setReturn(0,'',tdef.getValueOf(tdef,tdat));}
else if(tdat===undefined||tdat===null)
{if(tdef['default'])
{return setReturn(0,'',tdef['default']);}
else
{return setReturn(403,'not initialized '+token);}}
else
{if(window.order_matters)
{window.order_matters=false;}
return setReturn(0,'',String(tdat));}}}
function setReturn(errCode,errInfo,returnValue)
{if(errCode>-1)
{top.status=[(new Date()).toLocaleTimeString(),errCode,errInfo].join(", ");}
error=errCode;diagnostic=(typeof(errInfo)=='string')?errInfo:'';return returnValue;}
var NOT_INITIALIZED=0;var RUNNING=1;var TERMINATED=2;var READONLY=1;var WRITEONLY=2;var READWRITE=3;var state=NOT_INITIALIZED;var error=0;var diagnostic='';var dirty=false;var msec=currentTime();var me=this;var methods={'Initialize':Initialize,'Terminate':Terminate,'GetValue':GetValue,'GetValueIntern':GetValueIntern,'SetValue':SetValue,'SetValueIntern':SetValueIntern,'Commit':Commit,'GetLastError':GetLastError,'GetErrorString':GetErrorString,'GetDiagnostic':GetDiagnostic};for(var k in Runtime.methods)
{me[k]=methods[k];}}
Runtime.prototype.version="1.0";Runtime.apiname="API_1484_11";Runtime.errors={0:{code:0,message:'No error'},101:{code:101,message:'General Exeption'},102:{code:102,message:'General Initialization Failure'},103:{code:103,message:'Already Initialized'},104:{code:104,message:'Content Instance Terminated'},111:{code:111,message:'General Termination Failure'},112:{code:112,message:'Termination Before Initialization'},113:{code:113,message:'Termination After Termination'},122:{code:122,message:'Retrieve Data Before Initialization'},123:{code:123,message:'Retrieve Data After Termination'},132:{code:132,message:'Store Data Before Initialization'},133:{code:133,message:'Store Data After Termination'},142:{code:142,message:'Commit Before Initialization'},143:{code:143,message:'Commit After Termination'},201:{code:201,message:'General Argument Error'},301:{code:301,message:'General Get Failure'},351:{code:351,message:'General Set Failure'},391:{code:391,message:'General Commit Failure'},401:{code:401,message:'Undefined Data Model Element'},402:{code:402,message:'Unimplemented Data Model Element'},403:{code:403,message:'Data Model Element Value Not Initialized'},404:{code:404,message:'Data Model Element Is Read Only'},405:{code:405,message:'Data Model Element Is Write Only'},406:{code:406,message:'Data Model Element Type Mismatch'},407:{code:407,message:'Data Model Element Value Out Of Range'},408:{code:408,message:'Data Model Dependency Not Established'}};Runtime.methods={'Initialize':'Initialize','Terminate':'Terminate','GetValue':'GetValue','GetValueIntern':'GetValueIntern','SetValue':'SetValue','SetValueIntern':'SetValueIntern','Commit':'Commit','GetLastError':'GetLastError','GetErrorString':'GetErrorString','GetDiagnostic':'GetDiagnostic'};Runtime.models={'cmi':new function(){var READONLY=1;var WRITEONLY=2;var READWRITE=3;function getDelimiter(str,typ,extra)
{var redelim=new RegExp("^({("+typ+")=([^}]*)})?([\\s\\S]*)$");var rebool=/^(true|false)$/;var m=str.match(redelim);if(m[2]&&(m[2]==="lang"&&!LangType.isValid(m[3])||m[2]!=="lang"&&!BooleanType.isValid(m[3])))
{extra.error={code:406,diagnostic:typ+' not recognized: '+m[3]};}
return m[4];}
var AudioCaptioningState={isValid:function(value){return(/^-1|0|1$/).test(value);}};var BooleanType={isValid:function(value){return(/^(true|false)$/).test(value);}};var CompletionState={isValid:function(value){var valueRange={'completed':1,'incomplete':2,'not attempted':3,'unknown':4};return valueRange[value]>0;}};var CreditState={isValid:function(value){var valueRange={'credit':1,'no-credit':2};return valueRange[value]>0;}};var EntryState={isValid:function(value){var valueRange={'ab-initio':1,'resume':2,'':3};return valueRange[value]>0;}};var ExitState={isValid:function(value){var valueRange={'time-out':1,'suspend':2,'logout':3,'normal':4,'':5};return valueRange[value]>0;}};var InteractionType={isValid:function(value){var valueRange={'true-false':1,'choice':2,'fill-in':3,'long-fill-in':4,'matching':5,'performance':6,'sequencing':7,'likert':8,'numeric':9,'other':10};return valueRange[value]>0;}};var Interval={isValid:function(value){return Duration.parse(value)!==null;}};var LangType={isValid:function(value){var relang=/^(aa|ab|af|ak|sq|am|ar|an|hy|as|av|ae|ay|az|ba|bm|eu|be|bn|bh|bi|bo|bs|br|bg|my|ca|cs|ch|ce|zh|cu|cv|kw|co|cr|cy|cs|da|de|dv|nl|dz|el|en|eo|et|eu|ee|fo|fa|fj|fi|fr|fr|fy|ff|ka|de|gd|ga|gl|gv|el|gn|gu|ht|ha|he|hz|hi|ho|hr|hu|hy|ig|is|io|ii|iu|ie|ia|id|ik|is|it|jv|ja|kl|kn|ks|ka|kr|kk|km|ki|rw|ky|kv|kg|ko|kj|ku|lo|la|lv|li|ln|lt|lb|lu|lg|mk|mh|ml|mi|mr|ms|mk|mg|mt|mo|mn|mi|ms|my|na|nv|nr|nd|ng|ne|nl|nn|nb|no|ny|oc|oj|or|om|os|pa|fa|pi|pl|pt|ps|qu|rm|ro|ro|rn|ru|sg|sa|sr|hr|si|sk|sk|sl|se|sm|sn|sd|so|st|es|sq|sc|sr|ss|su|sw|sv|ty|ta|tt|te|tg|tl|th|bo|ti|to|tn|ts|tk|tr|tw|ug|uk|ur|uz|ve|vi|vo|cy|wa|wo|xh|yi|yo|za|zh|zu|aar|abk|ace|ach|ada|ady|afa|afh|afr|ain|aka|akk|alb|ale|alg|alt|amh|ang|anp|apa|ara|arc|arg|arm|arn|arp|art|arw|asm|ast|ath|aus|ava|ave|awa|aym|aze|bad|bai|bak|bal|bam|ban|baq|bas|bat|bej|bel|bem|ben|ber|bho|bih|bik|bin|bis|bla|bnt|bod|bos|bra|bre|btk|bua|bug|bul|bur|byn|cad|cai|car|cat|cau|ceb|cel|ces|cha|chb|che|chg|chi|chk|chm|chn|cho|chp|chr|chu|chv|chy|cmc|cop|cor|cos|cpe|cpf|cpp|cre|crh|crp|csb|cus|cym|cze|dak|dan|dar|day|del|den|deu|dgr|din|div|doi|dra|dsb|dua|dum|dut|dyu|dzo|efi|egy|eka|ell|elx|eng|enm|enm|epo|est|eus|ewe|ewo|fan|fao|fas|fat|fij|fil|fin|fiu|fon|fra|fre|frm|fro|frr|frs|fry|ful|fur|gaa|gay|gba|gem|geo|ger|gez|gil|gla|gle|glg|glv|gmh|goh|gon|gor|got|grb|grc|gre|grn|gsw|guj|gwi|hai|hat|hau|haw|heb|her|hil|him|hin|hit|hmn|hmo|hrv|hsb|hun|hup|hye|iba|ibo|ice|ido|iii|ijo|iku|ile|ilo|ina|inc|ind|ine|inh|ipk|ira|iro|isl|ita|jav|jbo|jpn|jpr|jrb|kaa|kab|kac|kal|kam|kan|kar|kas|kat|kau|kaw|kaz|kbd|kha|khi|khm|kho|kik|kin|kir|kmb|kok|kom|kon|kor|kos|kpe|krc|krl|kro|kru|kua|kum|kur|kut|lad|lah|lam|lao|lat|lav|lez|lim|lin|lit|lol|loz|ltz|lua|lub|lug|lui|lun|luo|lus|mac|mad|mag|mah|mai|mak|mal|man|mao|map|mar|mas|may|mdf|mdr|men|mga|mic|min|mis|mkd|mkh|mlg|mlt|mnc|mni|mno|moh|mol|mon|mos|mri|msa|mul|mun|mus|mwl|mwr|mya|myn|myv|nah|nai|nap|nau|nav|nbl|nde|ndo|nds|nep|new|nia|nic|niu|nld|nno|nob|nog|non|nor|nqo|nso|nub|nwc|nya|nym|nyn|nyo|nzi|oci|oji|ori|orm|osa|oss|ota|oto|paa|pag|pal|pam|pan|pap|pau|peo|per|phi|phn|pli|pol|pon|por|pra|pro|pus|que|raj|rap|rar|roa|roh|rom|ron|rum|run|rup|rus|sad|sag|sah|sai|sal|sam|san|sas|sat|scc|scn|sco|scr|sel|sem|sga|sgn|shn|sid|sin|sio|sit|sla|slk|slo|slv|sma|sme|smi|smj|smn|smo|sms|sna|snd|snk|sog|som|son|sot|spa|sqi|srd|srn|srp|srr|ssa|ssw|suk|sun|sus|sux|swa|swe|syr|tah|tai|tam|tat|tel|tem|ter|tet|tgk|tgl|tha|tib|tig|tir|tiv|tkl|tlh|tli|tmh|tog|ton|tpi|tsi|tsn|tso|tuk|tum|tup|tur|tut|tvl|twi|tyv|udm|uga|uig|ukr|umb|und|urd|uzb|vai|ven|vie|vol|vot|wak|wal|war|was|wel|wen|wln|wol|xal|xho|yao|yap|yid|yor|ypk|zap|zen|zha|zho|znd|zul|zun|zxx|zza|i|x)(-([a-z]{2}|[a-z0-9][-a-z0-9]{2,7}))?$/i;return relang.test(value);}};var LanguageType={isValid:function(value){return value===""||LangType.isValid(value);}};var ShortIdentifierType={isValid:function(value){var reuri=/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/;var rechars=/^[-~\.\:\/\?#\[\]@\!\$&'\(\)\*+,;=\w]{1,}$/;return reuri.test(value)&&rechars.test(value)&&value.indexOf("[.]")===-1&&value.indexOf("[,]")===-1;}};var LocalizedString={isValid:function(value,definition,extra){var val=getDelimiter(value,'lang',extra);return CharacterString.isValid(val,definition,{max:definition.max?definition.max+20:undefined});}};var ModeState={isValid:function(value){var valueRange={'browse':1,'normal':2,'review':3};return valueRange[value]>0;}};var ResponseType={isValid:function(value,definition,extra){var val,i;var parents=extra.parent;var ispattern=!parents[parents.length-1].id;var correct_responses=parents[parents.length-2].correct_responses||[];var parent=parents[parents.length-(ispattern+1)];var keys={};if(correct_responses.length)
{if(extra.index>={'true-false':1,'choice':10,'fill-in':5,'long-fill-in':5,'likert':1,'matching':5,'performance':5,'sequencing':5,'numeric':1,'other':1}[parent.type])
{extra.error={code:351,diagnostic:'array size exceeded in '+parent.type+' response'};return false;}}
switch(parent.type)
{case'true-false':return BooleanType.isValid(value);case'choice':val=value.split("[,]");if(val.length>36)
{extra.error={code:351};}
if(val.length===1&&!val[0])
{return true;}
for(i=val.length;i--;)
{if(keys[val[i]]||!ShortIdentifierType.isValid(val[i]))
{return false;}
keys[val[i]]=true;}
if(correct_responses)
{for(i=correct_responses.length;i--;)
{if(extra.index!==i&&correct_responses[i].pattern===value)
{extra.error={code:351};}}}
return!extra.error;case'fill-in':val=value;val=getDelimiter(val,'case_matters',extra);val=getDelimiter(val,'order_matters',extra);val=getDelimiter(val,'case_matters',extra);val=val.split("[,]");if(val.length>36)
{extra.error={code:351};}
for(i=val.length;i--;)
{if(extra.error||!LocalizedString.isValid(val[i],{min:0,max:250},extra))
{return false;}}
return true;case'long-fill-in':val=getDelimiter(value,'case_matters',extra);val=getDelimiter(val,'lang',extra).data;return!extra.error&&(/^.{0,4000}$/).test(val);case'likert':return ShortIdentifierType.isValid(value);case'matching':val=value.split("[,]");if(val.length>36){extra.error={code:351};}
for(i=val.length;i--;)
{val[i]=val[i].split("[.]");if(val[i].length!==2||!ShortIdentifierType.isValid(val[i][0])||!ShortIdentifierType.isValid(val[i][1]))
{return false;}}
return!extra.error;case'performance':val=getDelimiter(value,'order_matters',extra);val=val.split("[,]");if(val.length>250)
{extra.error={code:351};}
for(i=val.length;i--;)
{val[i]=val[i].split("[.]");if(val[i].length!==2||val[i][0]!==""&&!ShortIdentifierType.isValid(val[i][0]))
{return false;}}
return!extra.error;case'sequencing':val=value.split("[,]");if(val.length>36)
{extra.error={code:351};}
for(i=val.length;i--;)
{if(!ShortIdentifierType.isValid(val[i]))
{return false;}}
if(correct_responses)
{for(i=correct_responses.length;i--;)
{if(extra.index===i&&correct_responses[i].pattern===value)
{extra.error={code:351};}}}
return!extra.error;case'numeric':if(!ispattern)
{return RealType.isValid(value,{},{});}
else
{val=value.split("[:]");val[0]=!val[0]?Number.NEGATIVE_INFINITY:RealType.isValid(val[0],{},{})?parseFloat(val[0]):NaN;val[1]=!val[1]?Number.POSITIVE_INFINITY:RealType.isValid(val[1],{},{})?parseFloat(val[1]):NaN;return!isNaN(val[0])&&!isNaN(val[1])&&val[0]<=val[1];}
case'other':return value.length<=4000;}}};var ResultState={isValid:function(value){var valueRange={'correct':1,'incorrect':2,'unanticipated':3,'neutral':4};return valueRange[value]>0||RealType.isValid(value,{},{});}};var SuccessState={isValid:function(value){var valueRange={'passed':1,'failed':2,'unknown':3};return valueRange[value]>0;}};var Time={isValid:function(value){return DateTime.parse(value)!==null;}};var TimeLimitAction={isValid:function(value){var valueRange={'exit,message':1,'continue,message':2,'exit,no message':3,'continue,no message':4};return valueRange[value]>0;}};var Uri={isValid:function(value,definition,extra){var re_uri=/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/;var re_char=/[\s]/;var re_urn=/^urn:[a-z0-9][-a-z-0-9]{1,31}:.+$/;var m=value.match(re_uri);return Boolean(m&&m[0]&&!re_char.test(m[0])&&m[0].length<=4000&&(m[2]!=="urn"||re_urn.test(m[0])));}};var CharacterString={isValid:function(value,definition,extra){var min=extra.min?extra.min:definition.min;var max=extra.max?extra.max:definition.max;var pattern=extra.pattern?extra.pattern:definition.pattern;if((min&&String(value).length<min)||(max&&String(value).length>max)){extra.error={code:407};return false;}else if(pattern&&!pattern.test(value)){return false;}else{return true;}}};var RealType={isValid:function(value,definition,extra){var pattern=extra.pattern?extra.pattern:definition.pattern;var min=definition&&typeof definition.min==="number"?definition.min:Number.NEGATIVE_INFINITY;var max=definition&&typeof definition.max==="number"?definition.max:Number.POSITIVE_INFINITY;if(!(/^-?\d{1,32}(\.\d{1,32})?$/).test(value))
{return false;}
else if(Number(value)<min||Number(value)>max)
{extra.error={code:407};return false;}
else if(pattern&&!pattern.test(value))
{return false;}
else
{return true;}}};this.cmi={maxOccur:1,type:Object,permission:READWRITE,children:{comments_from_learner:{maxOccur:250,type:Array,permission:READWRITE,children:{comment:{type:LocalizedString,max:4000,permission:READWRITE},timestamp:{type:Time,permission:READWRITE},location:{type:CharacterString,max:250,permission:READWRITE}},mapping:{name:'comment',func:function(d){return!d.sourceIsLMS;},refunc:function(d){return['sourceIsLMS',0];}}},comments_from_lms:{maxOccur:250,type:Array,permission:READONLY,children:{comment:{type:LocalizedString,max:4000,permission:READONLY},timestamp:{type:Time,permission:READONLY},location:{type:CharacterString,max:250,permission:READONLY}},mapping:{name:'comment',func:function(d){return d.sourceIsLMS;},refunc:function(d){return['sourceIsLMS',1];}}},completion_status:{type:CompletionState,permission:READWRITE,'default':'unknown',getValueOf:function(tdef,tdat){var state=tdat===undefined?tdef['default']:String(tdat);var norm=currentAPI.GetValueIntern("cmi.completion_threshold");var score=currentAPI.GetValueIntern("cmi.progress_measure");if(norm){if(norm!=""&&score!=""){if(Number(score)<Number(norm)){state="incomplete";}else{state="completed";}}else{state="unknown";}}
if(state=="undefined"||state==""){state="unknown";}
currentAPI.SetValueIntern('cmi.completion_status',state);return state;}},completion_threshold:{type:RealType,min:0,max:1,permission:READONLY},credit:{type:CreditState,permission:READONLY,'default':'credit'},entry:{type:EntryState,permission:READONLY,'default':'ab-initio'},exit:{type:ExitState,permission:WRITEONLY,'default':''},interactions:{maxOccur:250,type:Array,permission:READWRITE,children:{correct_responses:{maxOccur:250,type:Array,permission:READWRITE,children:{pattern:{type:ResponseType,permission:READWRITE,dependsOn:'.id .type'}}},description:{type:LocalizedString,max:250,permission:READWRITE,dependsOn:'id'},id:{type:Uri,max:4000,permission:READWRITE,minOccur:1},latency:{type:Interval,permission:READWRITE,dependsOn:'id'},learner_response:{type:ResponseType,permission:READWRITE,dependsOn:'id type'},objectives:{maxOccur:250,type:Array,permission:READWRITE,unique:'id',children:{id:{type:Uri,max:4000,permission:READWRITE,dependsOn:'interactions.id'}}},result:{type:ResultState,permission:READWRITE,dependsOn:'id'},timestamp:{type:Time,permission:READWRITE,dependsOn:'id'},type:{type:InteractionType,permission:READWRITE,dependsOn:'id'},weighting:{type:RealType,permission:READWRITE,dependsOn:'id'}}},launch_data:{type:CharacterString,max:4000,permission:READONLY,'default':''},learner_id:{type:CharacterString,max:4000,permission:READONLY,'default':''},learner_name:{type:LocalizedString,max:250,permission:READONLY,'default':''},learner_preference:{type:Object,permission:READONLY,children:{audio_level:{type:RealType,min:0.0,permission:READWRITE,"default":'1'},language:{type:LanguageType,permission:READWRITE,'default':''},delivery_speed:{type:RealType,min:0,permission:READWRITE,'default':'1'},audio_captioning:{type:AudioCaptioningState,permission:READWRITE,'default':'0'}},mapping:['audio_level','language','delivery_speed','audio_captioning']},location:{type:CharacterString,max:1000,permission:READWRITE,'default':''},max_time_allowed:{type:Interval,permission:READONLY},mode:{type:ModeState,permission:READONLY,'default':'normal'},objectives:{maxOccur:100,type:Array,permission:READWRITE,unique:'id',children:{completion_status:{type:CompletionState,permission:READWRITE,'default':'unknown',dependsOn:'id'},description:{type:LocalizedString,max:250,permission:READWRITE,dependsOn:'id'},id:{type:Uri,max:4000,permission:READWRITE,writeOnce:true},progress_measure:{type:RealType,min:0,max:1,permission:READWRITE},score:{type:Object,permission:READWRITE,children:{scaled:{type:RealType,min:-1,max:1,permission:READWRITE,dependsOn:'objectives.id'},raw:{type:RealType,permission:READWRITE,dependsOn:'objectives.id'},min:{type:RealType,permission:READWRITE,dependsOn:'objectives.id'},max:{type:RealType,permission:READWRITE,dependsOn:'objectives.id'}},mapping:['scaled','raw','min','max']},success_status:{type:SuccessState,permission:READWRITE,'default':'unknown',dependsOn:'id'}},mapping:{name:'objective',func:function(d){return d.objectiveID||d.cmi_node_id;}}},progress_measure:{type:RealType,min:0,max:1,permission:READWRITE},scaled_passing_score:{type:RealType,min:-1,max:1,permission:READONLY},score:{type:Object,permission:READWRITE,children:{scaled:{type:RealType,min:-1,max:1,permission:READWRITE},raw:{type:RealType,permission:READWRITE},min:{type:RealType,permission:READWRITE},max:{type:RealType,permission:READWRITE}},mapping:['scaled','raw','min','max']},session_time:{type:Interval,permission:WRITEONLY},success_status:{type:SuccessState,permission:READWRITE,'default':'unknown',getValueOf:function(tdef,tdat){var state=tdat===undefined?tdef['default']:String(tdat);var norm=pubAPI.cmi.scaled_passing_score;var score=pubAPI.cmi.score.scaled;if(norm){norm=parseFloat(norm);if(norm&&score){score=parseFloat(score);if(score>=norm){state="passed";}else if(score<norm){state="failed";}}else{state="unknown";}}
pubAPI.cmi.successs_status=state;return state;}},suspend_data:{type:CharacterString,max:64000,permission:READWRITE},time_limit_action:{type:TimeLimitAction,permission:READONLY,"default":"continue,no message"},total_time:{type:Interval,permission:READONLY,'default':'PT0H0M0S'}}};},'adl':new function(){var READONLY=1;var WRITEONLY=2;var READWRITE=3;var NavRequest={isValid:function(value,min,max,pattern){return(/^(\{target=[^\}]+\}choice|continue|previous|exit|exitAll|abandon|abandonAll|suspendAll|_none_)$/).test(value);}};var NavState={isValid:function(value,min,max,pattern){return(/^(true|false|unknown)$/).test(value);}};var NavTarget={isValid:function(value,min,max,pattern){return(/^(true|false|unknown)$/).test(value);},getValue:function(param,def){var m=String(param).match(/^\{target=([^\}]+)\}$/);if(m&&m[1]){}
return def['default'];}};this.adl={maxOccur:1,type:Object,permission:READWRITE,children:{nav:{maxOccur:1,type:Object,permission:READWRITE,children:{request:{type:NavRequest,permission:READWRITE,'default':'_none_'},request_valid:{type:Object,permission:READONLY,children:{'continue':{type:NavState,permission:READONLY,'default':'unknown'},'previous':{type:NavState,permission:READONLY,'default':'unknown'},'choice':{type:Function,permission:READONLY,children:{type:NavTarget,permission:READONLY,'default':'unknown'}}}}}}}};}};Runtime.onTerminate=function(data,msec)
{var credit=data.cmi.credit==="credit";var normal=!data.cmi.mode||data.cmi.mode==="normal";var suspended=data.cmi.exit==="suspend";var not_attempted=data.cmi.completion_status==="not attempted";var success=(/^(completed|passed|failed)$/).test(data.cmi.success_status,true);var session_time;if(data.cmi.session_time==undefined){var interval=(currentTime()-msec)/1000;var dur=new ADLDuration({iFormat:FORMAT_SECONDS,iValue:interval});session_time=dur.format(FORMAT_SCHEMA);}else{session_time=data.cmi.session_time;}
if(normal||suspended)
{data.cmi.session_time=session_time.toString();total_time=addTimes(data.cmi.total_time.toString(),data.cmi.session_time);data.cmi.total_time=total_time.toString();data.cmi.entry="";if(data.cmi.exit==="suspend"){data.cmi.entry="resume";data.cmi.session_time="";}}
if(not_attempted)
{data.cmi.success_status='incomplete';}};