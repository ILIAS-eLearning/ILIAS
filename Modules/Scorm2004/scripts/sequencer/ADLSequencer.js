/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
	JS port of ADL ADLSequencer.java
	@author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
	
	This .js file is GPL licensed (see above) but based on
	ADLSequencer.java by ADL Co-Lab, which is licensed as:
	
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

var FLOW_NONE = 0;
var FLOW_FORWARD = 1;
var FLOW_BACKWARD = 2;
var TER_EXIT = "_EXIT_";
var TER_EXITALL = "_EXITALL_";
var TER_SUSPENDALL = "_SUSPENDALL_";
var TER_ABANDON = "_ABANDON_";
var TER_ABANDONALL = "_ABANDONALL_";
var SEQ_START = "_START_";
var SEQ_RETRY = "_RETRY_";
var SEQ_RESUMEALL = "_RESUMEALL_";
var SEQ_EXIT = "_EXIT_";
var SEQ_CONTINUE = "_CONTINUE_";
var SEQ_PREVIOUS = "_PREVIOUS_";

// Walk Class
function Walk()  
{
}
//this.Walk = Walk;
Walk.prototype = 
{
	at: null,
	direction: FLOW_NONE,
	endSession: false
};


// ADLSequencer Class
function ADLSequencer()  
{
}
//this.ADLSequencer = ADLSequencer;
ADLSequencer.prototype = 
{
	mSeqTree: null,
	mEndSession: false,
	mExitCourse: false,
	mRetry: false,
	mExitAll: false,
	mValidTermination: true,
	mValidSequencing: true,
	mIsJump: false,

	// getter/setter
	getActivityTree: function () { return this.mSeqTree; },

	// NOTE:
	// navigate(String) and navigate(iRequest) are converted to
	// navigateStr and navigateRequest (maybe merged in the future)
	
	getObjStatusSet: function (iActivityID)
	{
		var objSet = null;
		var act = this.getActivity(iActivityID);
		
		// Make sure the activity exists
		if (act != null)
		{
			// Ask the activity for its current set of objective status records
			objSet = act.getObjStatusSet();
		}
		
		return objSet;
	},
	
	getValidRequests: function (oValid)
	{
		var valid = null;
		
		if (this.mSeqTree != null)
		{
			valid = this.mSeqTree.getValidRequests();
			
			if (valid != null)
			{
				this.validateRequests();
				valid = this.mSeqTree.getValidRequests();
			}
		}
		
		// Copy the set of valid requests to the return object
		if (valid != null)
		{
			oValid.mContinue = valid.mContinue;
			oValid.mContinueExit = valid.mContinueExit;
			oValid.mPrevious = valid.mPrevious;
			
			if (valid.mTOC != null)
			{
				// clone Array (Vector)
				oValid.mTOC = valid.mTOC.concat(new Array());
			}
			
			if (valid.mChoice != null)
			{
				// clone Object (Hashtable)
				oValid.mChoice = $.extend(true, {}, valid.mChoice);//new clone(valid.mChoice);
			}
			
			if (valid.mJump != null)
			{
				//clone Object (Hashtable)
				oValid.mJump = $.extend(true, {}, valid.mJump);//new clone(valid.mJump);
			}
		}
		else
		{
			// Make sure nothing is valid
			oValid.mContinue = false;
			oValid.mContinueExit = false;
			oValid.mPrevious = false;
			oValid.mChoice = null;
			oValid.mTOC = null;
			oValid.mJump = null;
		}
		return oValid;
	},
	
	setActivityTree: function (iTree)
	{
		// Make sure the activity tree exists.
		if (iTree != null)
		{
			// Set the activity tree to be acted upon
			this.mSeqTree = iTree;
		}
	},
	
	getRoot: function ()
	{
		var rootActivity = null;
		if (this.mSeqTree != null)
		{
			rootActivity = this.mSeqTree.getRoot();
		}
		return rootActivity;
	},
	
	clearSeqState: function ()
	{
		var temp = null;
		
		this.mSeqTree.setCurrentActivity(temp);
		this.mSeqTree.setFirstCandidate(temp);
	},
	
	reportSuspension: function (iID, iSuspended)
	{
		var target = this.getActivity(iID);
		
		// Make sure the target activity is valid
		if (target != null)
		{
			// Confirm the activity is still active
			if (target.getIsActive())
			{
				// If the activity is a leaf and is the current activity
				if (!target.hasChildren(false)  &&
					this.mSeqTree.getCurrentActivity() == target)
				{
					// Set the activity's suspended state
					target.setIsSuspended(iSuspended);
				}
			}
		}
	},
	
	setAttemptDuration: function (iID, iDur)
	{
		var target = this.getActivity(iID);
		
		// Make sure the activity exists
		if (target != null)
		{
			// Make sure the activity is a valid target for status changes
			//   -- the tracked active leaf current activity
			if (target.getIsActive() && target.getIsTracked())
			{
				// If the activity is a leaf and is the current activity
				if (!target.hasChildren(false)  &&
					this.mSeqTree.getCurrentActivity() == target)
				{
					target.setCurAttemptExDur(iDur);
					// Revalidate the navigation requests
					this.validateRequests();
				}
			}
		}
	},
	
	clearAttemptObjMeasure: function (iID, iObjID)
	{
		// Find the target activity
		var target = this.getActivity(iID);
		
		// Make sure the activity exists
		if (target != null)
		{
			// Make sure the activity is a valid target for status changes
			//   -- the active leaf current activity
			if (target.getIsActive())
			{
				// If the activity is a leaf and is the current activity
				if (!target.hasChildren(false)  &&
					this.mSeqTree.getCurrentActivity() == target)
				{
					var statusChange = target.clearObjMeasure(iObjID);
					
					if (statusChange)
					{								
						// Revalidate the navigation requests
						this.validateRequests();
					}
				}
			}
		}
	},
	
	setAttemptObjMeasure: function (iID, iObjID, iMeasure)
	{
		// Find the target activity
		var target = this.getActivity(iID);
		
		// Make sure the activity exists
		if (target != null)
		{
			// Make sure the activity is a valid target for status changes
			//   -- the tracked active leaf current activity
			if (target.getIsActive() && target.getIsTracked())
			{
				// If the activity is a leaf and is the current activity
				if (!target.hasChildren(false)  &&
					this.mSeqTree.getCurrentActivity() == target)
				{
					/* boolean statusChange = */
					//fixed HH
					//target.setObjMeasure(iObjID, iMeasure);
					target.setObjMeasure(iMeasure, {iObjID:iObjID});
					if (true)
					{								
						// Revalidate the navigation requests
						this.validateRequests();
					}
				}
			}
		}
	},
	
	setAttemptObjSatisfied: function (iID, iObjID, iStatus)
	{
		// Find the activity whose status is being set
		var target = this.getActivity(iID);
		
		// Make sure the activity exists
		if (target != null)
		{
			// Make sure the activity is a valid target for status changes
			//   -- the tracked active leaf current activity
			if (target.getIsActive() && target.getIsTracked())
			{
				// If the activity is a leaf and is the current activity
				if (!target.hasChildren(false)  &&
					this.mSeqTree.getCurrentActivity() == target)
				{
					//TODO here occurs some error fix it HH
					//var statusChange = target.setObjSatisfied(iObjID, iStatus);
					var statusChange = target.setObjSatisfied( iStatus,{iObjID: iObjID});
					
					if (statusChange)
					{								
						// Revalidate the navigation requests
						this.validateRequests();
					}
				}
			}
		}
	},
	
	setAttemptProgressStatus: function (iID, iProgress)
	{
		var target = this.getActivity(iID);
		
		// Make sure the activity exists
		if (target != null)
		{
			// Make sure the activity is a valid target for status changes
			//   -- the tracked active leaf current activity
			if (target.getIsActive() && target.getIsTracked())
			{
				// If the activity is a leaf and is the current activity
				if (!target.hasChildren(false)  &&
					this.mSeqTree.getCurrentActivity() == target)
				{
					var statusChange = target.setProgress(iProgress);
					
					if (statusChange)
					{
						// Revalidate the navigation requests
						this.validateRequests();
					}
				}
			}
		}
	},
	
	navigateStr: function (iTarget, iJumpRequest)
	{
		sclog("NavigationRequest [NB.2.1]","seq");			
		//return (iJumpRequest) ? this.jump(iTarget) : this.choice(iTarget);
		if (iJumpRequest)
		{
			return this.jump(iTarget);
		}
		else
		{
			return this.choice(iTarget);
		}
	},	

	navigate: function (iRequest)
	{
		
		sclog("NavigationRequest [NB.2.1]","seq");
		var launch = new ADLLaunch();
		
		// Make sure an activity tree has been associated with this sequencer
		if (this.mSeqTree == null)
		{
			// No activity tree, therefore nothing to do
			//    -- inform the caller of the error.
			launch.mSeqNonContent = LAUNCH_ERROR;
			launch.mEndSession = true;
			return launch;
		}
		
		// If this is a new session, we start at the root.
		var newSession = false;
		var cur = this.mSeqTree.getCurrentActivity();
		if (cur == null)
		{
			
			this.prepareClusters();
			newSession = true;
			this.validateRequests();
		}
		
		var process = true;
		var valid = null;
		
		if (newSession && iRequest == NAV_NONE)
		{
			// Processing a TOC request
		}
		else if (newSession && (iRequest == NAV_EXITALL ||
			iRequest == NAV_ABANDONALL))
		{
			launch.mSeqNonContent = LAUNCH_ERROR_INVALIDNAVREQ;
			launch.mEndSession = true;
			process = false;
		}
		else if (iRequest == NAV_CONTINUE || iRequest == NAV_PREVIOUS)
		{
			this.validateRequests();
			valid = this.mSeqTree.getValidRequests();
		
			// Can't validate requests -- Error
			if (valid == null)
			{
				launch.mSeqNonContent = LAUNCH_ERROR;
				launch.mEndSession = true;
		
				// Invalid request -- do not process
				process = false;
			}
			else
			{
				if (iRequest == NAV_CONTINUE)
				{
					if (!valid.mContinue)
					{
						process = false;
						launch.mSeqNonContent = LAUNCH_ERROR_INVALIDNAVREQ;
					}
				}
				else
				{
					if (!valid.mPrevious)
					{
						process = false;
						launch.mSeqNonContent = LAUNCH_ERROR_INVALIDNAVREQ;
					}
				}
			}
		}
		else
		{
			// Use the IMS Navigation Request Process to validate the request
			process = this.doIMSNavValidation(iRequest);
			
			if (!process)
			{
				launch.mSeqNonContent = LAUNCH_ERROR_INVALIDNAVREQ;
			}
		}
		
		// Process any pending navigation request
		if (process)
		{
			// This block implements the overall sequencing loop
			
			// Clear Global State
			this.mValidTermination = true;
			this.mValidSequencing = true;
			
			var seqReq = null;
			var delReq = null;
		
			// Translate the navigation request into termination and/or sequencing 
			// request(s).
			switch (iRequest)
			{
				case NAV_START:
					delReq = this.doSequencingRequest(SEQ_START);
					if (this.mValidSequencing)
					{
						this.doDeliveryRequest(delReq, false, launch);
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
					}
					break;
		
				case NAV_RESUMEALL:
				
					delReq = this.doSequencingRequest(SEQ_RESUMEALL);
					if (this.mValidSequencing)
					{
						//Make sure the identified activity exists in the tree	
						var act = this.getActivity(delReq);		
						
						if ( act != null && act.hasChildren(false) )
						{
							//Prepare for delivery
							launch.mEndSession = this.mEndSession || this.mExitCourse;
							if ( !launch.mEndSession)
							{
								this.validateRequests();
								launch.mNavState = this.mSeqTree.getValidRequests();
							}
							launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
						}
						else
						{
							this.doDeliveryRequest(delReq, false, launch);
						}
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
					}														
					break;
		
				case NAV_CONTINUE:
					var i = 0;
					if (cur.getIsActive())
					{
						// Issue a termination request of 'exit'
						seqReq = this.doTerminationRequest(TER_EXIT, false);
					}
					if (this.mValidTermination)
					{
						// Issue the pending sequencing request
						if (seqReq == null)
						{
							delReq = this.doSequencingRequest(SEQ_CONTINUE);
						}
						else
						{
							delReq = this.doSequencingRequest(seqReq);
						}
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_NOTHING;
					}
					
					if (this.mValidSequencing)
					{
						this.doDeliveryRequest(delReq, false, launch);
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
					}
					break;
			
				case NAV_PREVIOUS:
					if (cur.getIsActive())
					{
						// Issue a termination request of 'exit'
						seqReq = this.doTerminationRequest(TER_EXIT, false);
					}			
					if (this.mValidTermination)
					{
						// Issue the pending sequencing request
						if (seqReq == null)
						{
							delReq = this.doSequencingRequest(SEQ_PREVIOUS);
						}
						else
						{
							delReq = this.doSequencingRequest(seqReq);
						}
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_NOTHING;
					}
					
					if (this.mValidSequencing)
					{
						this.doDeliveryRequest(delReq, false, launch);
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
					}
					break;
					
				case NAV_ABANDON:
					// Issue a termination request of 'abandon'
					seqReq = this.doTerminationRequest(TER_ABANDON, false);
					
					// The termination process cannot return a sequencing request 
					// because post condition rules are not evaluated.
					if (this.mValidTermination)
					{
						delReq = this.doSequencingRequest(SEQ_EXIT);
						// If the session hasn't ended, re-validate nav requests
						if (!this.mEndSession && !this.mExitCourse)
						{
							this.validateRequests();
						}
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_NOTHING;
					}
					
					if (this.mValidSequencing)
					{
						this.doDeliveryRequest(delReq, false, launch);                  
						launch.mSeqNonContent = LAUNCH_SEQ_ABANDON;                 
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
					}
					
					break;
				
				case NAV_ABANDONALL:
					// Issue a termination request of 'abandonAll'
					seqReq = this.doTerminationRequest(TER_ABANDONALL, false);
					
					// The termination process cannot return a sequencing request 
					// because post condition rules are not evaluated.
					if (this.mValidTermination)
					{
						delReq = this.doSequencingRequest(SEQ_EXIT);
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_NOTHING;
					}
					
					if (this.mValidSequencing)
					{
						this.doDeliveryRequest(delReq, false, launch);
						launch.mSeqNonContent = LAUNCH_SEQ_ABANDONALL;                 
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
					}
					break;
			
				case NAV_SUSPENDALL:
			
					// Issue a termination request of 'suspendAll'
					seqReq = this.doTerminationRequest(TER_SUSPENDALL, false);
					
					// The termination process cannot return a sequencing request 
					// because post condition rules are not evaluated.
					if (this.mValidTermination)
					{
						delReq = this.doSequencingRequest(SEQ_EXIT);
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_NOTHING;
					}
					
					if (this.mValidSequencing)
					{
						this.doDeliveryRequest(delReq, false, launch);
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
					}
					break;
			
				case NAV_EXIT:
			
					// Issue a termination request of 'exit'
					seqReq = this.doTerminationRequest(TER_EXIT, false);
					if (this.mValidTermination)
					{
						if (seqReq == null)
						{
							delReq = this.doSequencingRequest(SEQ_EXIT);
						}
						else
						{
							delReq = this.doSequencingRequest(seqReq);
						}
						
						// If the session hasn't ended, re-validate nav requests
						if (!this.mEndSession && !this.mExitCourse)
						{
							this.validateRequests();
						}
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_NOTHING;
					}
					
					if (this.mValidSequencing)
					{
						this.doDeliveryRequest(delReq, false, launch);
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
					}
					break;
			
				case NAV_EXITALL:
			
					// Issue a termination request of 'exitAll'
					seqReq = this.doTerminationRequest(TER_EXITALL, false);
					
					// The termination process cannot return a sequencing request 
					// because post condition rules are not evaluated.
					if (this.mValidTermination)
					{
						delReq = this.doSequencingRequest(SEQ_EXIT);
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_NOTHING;
					}
					
					if (this.mValidSequencing)
					{
						this.doDeliveryRequest(delReq, false, launch);
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
					}
					break;
			
				case NAV_NONE:
			
					// Don't invoke any termination or sequencing requests,
					// but display a TOC if available
					launch.mSeqNonContent = LAUNCH_TOC;
					
					launch.mNavState = this.mSeqTree.getValidRequests();
					// Make sure that a TOC is realy available
					if (launch.mNavState.mTOC == null)
					{
						launch.mSeqNonContent = LAUNCH_ERROR_INVALIDNAVREQ;
					}
					break;
					
				default:
					launch.mSeqNonContent = LAUNCH_ERROR;
			}
		}
		else
		{
			launch.mNavState = this.mSeqTree.getValidRequests();
			
			// If navigation requests haven't been validated, try to validate now.
			if (launch.mNavState == null)
			{
				this.validateRequests();
				launch.mNavState = this.mSeqTree.getValidRequests();
			}
		}
		return launch;
	},
	
	choice: function (iTarget)							
	{
		var launch = new ADLLaunch();
		
		//Make sure an activity tree has been associated with this sequencer
		if (this.mSeqTree == null)
		{
			//No activity tree, therefore nothing to do
			//  -- inform the caller of the error.
			launch.mSeqNonContent = LAUNCH_ERROR;
			launch.mEndSession = true;
			
			return launch;
			
		}
		
		//Make sure the requested activity exists
		var target = this.getActivity(iTarget);
		
		if (target != null)
		{
			//If this is a new session, we start at the root.
			var newSession = false;
			var cur = this.mSeqTree.getCurrentActivity();
			
			if (cur == null)
			{
				this.prepareClusters();
				newSession = true;
			}
			
			var process = true;
			this.validateRequests();
			
			// If the sequencing session has already begun, confirm the
			// navigation request is valid.
			if( !newSession)
			{
				var valid = this.mSeqTree.getValidRequests();
				
				if ( valid != null)
				{
					//Confirm the target activity is allowed
					if ( valid.mChoice != null )
					{
						var test = valid.mChoice[iTarget];
						
						if ( test == null )
						{
							launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
							process = false;
						}
						else if ( !test.mIsSelectable )
						{
							launch.mSeqNonContent = LAUNCH_ERROR_INVALIDNAVREQ;
							process = false;
						}
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_ERROR_INVALIDNAVREQ;
						process = false;
					}
				}
				else
				{
					launch.mSeqNonContent = LAUNCH_ERROR;
					launch.mEndSession = true;
					
					//Invalid request -- do not process
					process = false;
				}
			}
			
			//If the navigation request is valid process it
			if (process)
			{
				//This block implements the overall sequencing loop
				
				//Clear Global State
				this.mValidTermination = true;
				this.mValidSequencing = true;
				
				var seqReq = iTarget;
				var delReq = null;
				
				//Check if a termination is required
				if (!newSession)
				{
					if (cur.getIsActive())
					{
						//Issue a termination request of 'exit'
						seqReq = this.doTerminationRequest(TER_EXIT, false);
						
						if (seqReq == null)
						{
							seqReq = iTarget;
						}
						
					}
				}
				if (this.mValidTermination == true)
				{
					//Issue the pending sequencing request
					delReq = this.doSequencingRequest(seqReq);
				}
				else
				{
					launch.mSeqNonContent = LAUNCH_NOTHING;
				}
				
				if ( this.mValidSequencing == true)
				{
					this.doDeliveryRequest( delReq, false, launch);
				}
				else
				{
					launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
				}
			}
			else
			{
				launch.mNavState = this.mSeqTree.getValidRequests();
			}
		}
		else
		{
			launch.mSeqNonContent = LAUNCH_ERROR;
			launch.mEndSession = true;
		}
		
		return launch;
	},
	
	
	jump: function (iTarget)
	{
		this.mIsJump = true;
		var launch = new ADLLaunch();
		
		//Make sure an activity tree has been associated with this sequencer
		if (this.mSeqTree == null)
		{
			//No activity tree, therefore nothing to do
			//  -- inform the caller of the error.
			launch.mSeqNonContent = LAUNCH_ERROR;
			launch.mEndSession = true;
			
			return launch;
		}
		
		//Make sure the requested activity exists
		var target = this.getActivity(iTarget);
		
		if (target != null)
		{
			//If this is a new session, we start at the root.
			var process = true;
			
			var cur = this.mSeqTree.getCurrentActivity();
			
			if (cur == null)
			{
				launch.mSeqNonContent = LAUNCH_ERROR_INVALIDNAVREQ;
				process = false;
			}
			
			this.validateRequests();
			
			if (process)
			{
				var valid = this.mSeqTree.getValidRequests();
				
				if (valid != null)
				{
					//Confirm the target activity is allowed
					if (valid.mJump != null)
					{
						var test = valid.mJump[iTarget];
						
						if (test == null)
						{
							launch.mSeqNonContent = LAUNCH_ERROR_INVALIDNAVREQ;
							
							process = false;
						}
					}
					else
					{
						launch.mSeqNonContent = LAUNCH_ERROR;
						launch.mEndSession = true;
						
						//Invalid request -- do not process
						process = false;
					}
				}
			}
			//If the navigation request is valid process it
			if (process)
			{
				// This block implements the overall sequencing loop
				
				// Clear Global State
				this.mValidTermination = true;
				this.mValidSequencing = true;
				
				var seqReq = iTarget;
				var delReq = null;
				
				if (cur.getIsActive())
				{
					// Issue a termination request of 'exit'
					seqReq = this.doTerminationRequest(TER_EXIT, false);
					
					if ( seqReq == null )
					{
						seqReq = iTarget;
					}
				}
				
				if ( this.mValidTermination == true )
				{
					//Issue the pending sequencing request
					delReq = this.doSequencingRequest(seqReq);
				}
				else
				{
					launch.mSeqNonContent = LAUNCH_NOTHING;
				}
				
				if ( this.mValidSequencing )
				{
					this.doDeliveryRequest(delReq, false, launch);
				}
				else
				{
					launch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
				}
			}
			else
			{
				launch.mNavState = this.mSeqTree.getValidRequests();
			}
		}	
		else
		{
			launch.mSeqNonContent = LAUNCH_ERROR;
			launch.mEndSession = true;
		}
		
		this.mIsJump = false;
		return launch;	
	},
	
	
	getActivity: function (iActivityID)
	{
		var thisActivity = null;
		
		if (this.mSeqTree != null)
		{
			// Get an activity node from the activity tree based on its ID
			thisActivity = this.mSeqTree.getActivity(iActivityID);
		}
		return thisActivity;
	},

	doIMSNavValidation: function (iRequest)
	{
		var ok = true;
		
		// Assume the navigation request is valid
		var process = true;
		
		// If this is a new session, we start at the root.
		var newSession = false;
		
		var cur = this.mSeqTree.getCurrentActivity();
		var parent = null;
		
		if (cur == null)
		{
			newSession = true;
		}
		else
		{
			parent = cur.getParent();
		}
		
		// Validate the pending navigation request before processing it.
		// The following tests implement the validation logic of NB.2.1; it 
		// covers all cases where the request, itself, is invalid.
		switch (iRequest)
		{
			case NAV_START:
			
				if (!newSession)
				{
					process = false;
				}
				break;
			
			case NAV_RESUMEALL:
			
				ok = true;
				if (!newSession)
				{
					ok = false;
				}
				else if (this.mSeqTree.getSuspendAll() == null)
				{
					ok = false;
				}
				
				// Request not valid
				if (!ok)
				{
					process = false;
				}
			
				break;
			
			case NAV_CONTINUE:
			
				// Request not valid
				if (newSession)
				{
					process = false;
				}
				else
				{
					if (parent == null || !parent.getControlModeFlow())
					{
						process = false;
					}
				}
				break;
			
			case NAV_PREVIOUS:
			
				if (newSession)
				{
					process = false;
				}
				else
				{
					if (parent != null)
					{
						if (!parent.getControlModeFlow() ||
							parent.getControlForwardOnly())
						{
							process = false;
						}
					}
					else
					{
						process = false;
					}
				}
				break;
			
			case NAV_ABANDON:
			
				ok = true;
				if (newSession)
				{
					ok = false;
				}
				else if (!cur.getIsActive())
				{
					ok = false;
				}
				
				// Request is not valid
				if (!ok)
				{
					process = false;
				}
				break;
			
			case NAV_ABANDONALL:
			
				if (newSession)
				{
					process = false;
				}
				break;
			
			case NAV_SUSPENDALL:
			
				if (newSession)
				{
					process = false;
				}
				break;
			
			case NAV_EXIT:
			
				if (newSession)
				{
					ok = false;
				}
				else if (!cur.getIsActive())
				{
					ok = false;
				}
				
				// Request not valid
				if (!ok)
				{
					process = false;
				}
				break;
			
			case NAV_EXITALL:
			
				if (newSession)
				{
					process = false;
				}
				break;
			
			default:
				process = false;
		}
		
		return process;
	},

	validateRequests: function ()
	{
		var valid = this.mSeqTree.getValidRequests();
		
		// If there is no current activity or the current activity is inactive,
		// no state change could have occured since the last validation.
		var cur = this.mSeqTree.getCurrentActivity();
		if (cur != null)
		{
			var test = false;
			valid = new ADLValidRequests();
			var tempLaunch = new ADLLaunch();
			
			// Clear global state
			this.mValidTermination = true;
			this.mValidSequencing = true;
			
			var seqReq = null;
			var seqReqSuccess = false;
			
			var delReq = null;
			
			// If there is a current activity and it is active,
			// 'suspendAll' is a valid Navigation Request
			if ( cur.getIsActive() )
			{
				valid.mSuspend = true;
			}
			
			// If the current activity does not prevent choiceExit,
			// Test all 'Choice' requests
			if (cur.getControlModeChoiceExit() || !cur.getIsActive())
			{
				valid.mTOC = this.getTOC(this.mSeqTree.getRoot());
			}
			
			if (valid.mTOC != null)
			{
				valid.mJump = this.getJumpSet(valid.mTOC);
				var newTOC = new Array();
				valid.mChoice = this.getChoiceSet(valid.mTOC, newTOC);
				
				if (newTOC.length > 0)
				{
					valid.mTOC = newTOC;
				}
				else
				{
					valid.mTOC = null;
				}
			}
			
			
			
			if ( cur.getParent() != null )
			{
				// Always provide a Continue Button if the current activity
				// is in a 'Flow' cluster
				if (cur.getParent().getControlModeFlow())
				{
					valid.mContinue = true;
				}
				
				test = this.doIMSNavValidation(NAV_PREVIOUS);
				
				if (test)
				{
					// Test the 'Previous' request
					this.mValidSequencing = true;
					
					delReq = this.doSequencingRequest(SEQ_PREVIOUS);
					if (this.mValidSequencing)
					{
						valid.mPrevious = this.doDeliveryRequest(delReq, true, tempLaunch);
					}
				}           
			}     
		}
		else
		{
			valid = new ADLValidRequests();
			// Check to see if a resume All should be processed instead of a start
			if (this.mSeqTree.getSuspendAll() != null)
			{
				valid.mResume = true;
			}
			else
			{
				// Test Start Navigation Request
				var walk = new Walk();
				walk.at = this.mSeqTree.getRoot();
				
				valid.mStart = this.processFlow(FLOW_FORWARD, true, walk, false);
				
				// Validate availablity of the identfied activity if one was
				// identified
				if (valid.mStart)
				{
					var ok = true;
					while (walk.at != null && ok)
					{
						ok = !this.checkActivity(walk.at);
						
						if (ok)
						{
							walk.at = walk.at.getParent();
						}
						else
						{
							valid.mStart = false;
						}
					}
				}
			}
			
			// Test all 'Choice' requests
			valid.mTOC = this.getTOC(this.mSeqTree.getRoot());
			
			if (valid.mTOC != null)
			{
				var newTOC = new Array();
				valid.mJump = this.getJumpSet(valid.mTOC);
				valid.mChoice = this.getChoiceSet(valid.mTOC, newTOC);
				if (newTOC.length > 0)
				{
					valid.mTOC = newTOC;
				}
				else
				{
					valid.mTOC = null;
				}
			}
			
			
		}
		
		// If an updated set of valid requests has completed, associated it with
		// the activity tree
		if (valid != null)
		{
			this.mSeqTree.setValidRequests(valid);
		}
	},
	
	getJumpSet: function(iTOC)
	{
		var jumptargets = new Object();		// Hashtable
		
		if ( iTOC != null )
		{
			for ( var i = 0; i < iTOC.length; i++ )
			{
				var temp = iTOC[i];
				if ( temp.mLeaf && temp.mIsEnabled )
				{
					jumptargets[temp.mID] = temp;
				}
			}
		}
		return jumptargets;
	},
	
	evaluateExitRules: function (iTentative)
	{
		sclog("SequencingExitActionRulesSub [TB.2.1]","seq");
      
		// Clear global state
		this.mExitCourse = false;
		
		// Always begin processing at the current activity
		var start = this.mSeqTree.getCurrentActivity();
		var exitAt = null;
		var exited = null;
		var path = new Array();
		
		if (start != null)
		{
			var parent = start.getParent();
		
			while (parent != null)
			{
				path[path.length] = parent;
				parent = parent.getParent();
			}
		
			// Starting at the root, walk down the tree to the current activity'
			// parent.
			while (path.length > 0  && (exited == null))
			{
				parent = path[path.length - 1];
				//delete path[path.length - 1];
				path.splice(path.length - 1,1);
				// Attempt to get rule information from the activity node
				var exitRules = parent.getExitSeqRules();
				
				if (exitRules != null)
				{
					exited = exitRules.evaluate(RULE_TYPE_EXIT, parent, false);
				}
				// If the rule evaluation did not return null, the activity must
				// have exited.
				if (exited != null)
				{
					exitAt = parent;
				}
			}

			if (exited != null)
			{
				// If this was a 'real' evaluation, end the appropriate attempts.
				if (iTentative==false)
				{
					// If an activity exited, end attempts at all remaining cluster
					// on the 'active' branch.
					this.terminateDescendentAttempts(exitAt);
					
					// End the attempt on the 'exited' activity
					exitAt=this.endAttempt(exitAt, false);
				}
				// Sequencing requests begin at the 'exited' activity
				this.mSeqTree.setFirstCandidate(exitAt);
			}
		}
	},

	doTerminationRequest: function (iRequest, iTentative)
	{
		sclog("TerminationRequest [TB.2.3]","seq");
		// The Termination Request Process may return a sequencing request
		var seqReq = null;
		this.mExitAll = false;
		
		// Ensure the request exists
		if (iRequest == null)
		{
			this.mValidTermination = false;
			return seqReq;
		}
		
		// The Sequencing Request Process will always begin processing at the
		// 'first candidate'.
		// Assume the first candidate for sequencing is the current activity.
		var cur = this.mSeqTree.getCurrentActivity();
		
		if (cur != null)
		{
			this.mSeqTree.setFirstCandidate(cur);
		}
		else
		{
			this.mValidTermination = false;
			return seqReq;
		}
		
		// Apply the termination request
		if (iRequest == TER_EXIT)
		{
			
			// Make sure the current activity is active.
			if (cur.getIsActive())
			{
			
				// End the attempt on the current activity
				cur=this.endAttempt(cur, iTentative);
				
				// Evaluate exit action rules
				this.evaluateExitRules(iTentative);
				
				if (!cur.getIsSuspended())
				{
				
					// Evaluate post conditions
					var exited = false;
					
					do
					{
						exited = false;
					
						// Only process post conditions on the first candidate
						var process = this.mSeqTree.getFirstCandidate();
					
						// Make sure we are not at the root
						if (!this.mExitCourse)
						{
							// This block implements the Sequencing Post Condition Rule
							// Subprocess (SB.2.2)

							// Attempt to get rule information from the activity
							var postRules = process.getPostSeqRules();
						
							if (postRules != null && !(process.getIsSuspended()))
							{
								var result = null;
								result = postRules.evaluate(RULE_TYPE_POST, process, false);
							
								if (result != null)
								{
									// This set of ifs implement TB.2.2
									sclog("SequencingPostConditionRulesSub [TB.2.2]","seq");		                        
									if (result == SEQ_ACTION_RETRY)
									{
										// Override any existing sequencing request
										seqReq = SEQ_RETRY;
									
										// If we are processing the root activity, behave
										// as if this where an exitAll
										if (process == this.mSeqTree.getRoot())
										{        
											// Break from the current loop and jump to the
											// next case
											iRequest = TER_EXITALL;
										}
									}
									else if (result == SEQ_ACTION_CONTINUE)
									{
										// Override any existing sequencing request
										seqReq = SEQ_CONTINUE;
									}
									else if (result == SEQ_ACTION_PREVIOUS)
									{
										// Override any existing sequencing request
										seqReq = SEQ_PREVIOUS;
									}
									else if (result == SEQ_ACTION_EXITALL)
									{
										// Break from the current loop and jump to the
										// next case
										iRequest = TER_EXITALL;
									}
									else if (result == SEQ_ACTION_EXITPARENT)
									{
										process = process.getParent();
		
										if (process == null)
										{
										}
										else
										{
											this.mSeqTree.setFirstCandidate(process);
											process=this.endAttempt(process, iTentative);
											exited = true;
										}
									}
									else if (result == SEQ_ACTION_RETRYALL)
									{
										// Override any existing sequencing request
										seqReq = SEQ_RETRY;
									
										// Break from the current loop and jump to the
										// next case
										iRequest = TER_EXITALL;
									}
									else if (process == this.mSeqTree.getRoot())
									{
										// Exited Root with no postcondition rules
										// End the Course
										this.mExitCourse = true;                        
									}
								}
							}
							else if (process == this.mSeqTree.getRoot())
							{
								// Exited Root with no postcondition rules
								// End the Course
								this.mExitCourse = true;
							}
						}
						else {
							seqReq = SEQ_EXIT;
						}
					}
					while (exited);
				}
			}
			else
			{
				this.mValidTermination = false;
			}
		}
		
		// Double check for an EXIT request
		if (iRequest == TER_EXIT)
		{
			// Already handled
		}
		else if (iRequest == TER_EXITALL)
		{
			// Don't modify the activity tree if this is only a tentative exit
			if (!iTentative)
			{
				var process = this.mSeqTree.getFirstCandidate();
				if (process.getIsActive())
				{
					process=this.endAttempt(process, false);
				}
				
				this.terminateDescendentAttempts(this.mSeqTree.getRoot());
				this.endAttempt(this.mSeqTree.getRoot(), false);
				// only exit if we're not retrying the root
				if (seqReq != SEQ_RETRY)
				{
					seqReq = SEQ_EXIT;
				}
				
				// Start any subsequent seqencing request from the root
				this.mSeqTree.setFirstCandidate(this.mSeqTree.getRoot());
			}
			else
			{
				// Although this was a tentative evaluation, remember that we
				// processed the exitAll so that a retry from the root can be
				// tested
			}
			
			// Start any subsequent seqencing request from the root
			this.mSeqTree.setFirstCandidate(this.mSeqTree.getRoot());
			
		}
		else if (iRequest == TER_SUSPENDALL)
		{
			// Don't modify the activty tree if this is only a tentative exit
			if (!iTentative)
			{
				var process = this.mSeqTree.getFirstCandidate();
				this.reportSuspension(process.getID(), true);
				
				if (process.getIsActive())
				{
					// Invoke rollup
					this.invokeRollup(process, null);
					
					this.mSeqTree.setSuspendAll(process);
					
					// Check to see if the SCO's learner attempt ended
					if (!process.getIsSuspended())
					{
						process.incrementSCOAttempt();
					}
				}
				else
				{
					if (!process.getIsSuspended())
					{
						this.mSeqTree.setSuspendAll(process.getParent());
						
						// Make sure there was a an activity to suspend
						if (this.mSeqTree.getSuspendAll() == null)
						{
							this.mValidTermination = false;
						}
					}
				}
				
				if (this.mValidTermination)
				{
					var start = this.mSeqTree.getSuspendAll();
					
					// This process suspends all clusters up to the root
					while (start != null)
					{
						start.setIsActive(false);
						start.setIsSuspended(true);
						start = start.getParent();
					}
				}
			}
			
			// Start any subsequent seqencing request from the root
			this.mSeqTree.setFirstCandidate(this.mSeqTree.getRoot());
			
		}
		else if (iRequest == TER_ABANDON)
		{
			// Don't modify the activty tree if this is only a tentativen exit
			if (!iTentative)
			{
				var process = this.mSeqTree.getFirstCandidate();

				// Ignore any status values reported by the content
				process.setProgress(TRACK_UNKNOWN);
				//FIXED HH
				process.setObjSatisfied(TRACK_UNKNOWN, null);
				//process.setObjSatisfied(null, TRACK_UNKNOWN);
				process.clearObjMeasure(null);
				
				process.setIsActive(false);
			}
		}
		else if (iRequest == TER_ABANDONALL)
		{
			// Don't modify the activty tree if this is only a tentative exit
			if (!iTentative)
			{
				var process = this.mSeqTree.getFirstCandidate();
				
				// Ignore any status values reported by the content
				process.setProgress(TRACK_UNKNOWN);
				//FIXED HH
				process.setObjSatisfied(TRACK_UNKNOWN, null);
				//process.setObjSatisfied(null, TRACK_UNKNOWN);
				process.clearObjMeasure(null);
				
				while (process != null)
				{
					process.setIsActive(false);
					process = process.getParent();
				}
				
				seqReq = SEQ_EXIT;
				
				// Start any subsequent seqencing request from the root
				this.mSeqTree.setFirstCandidate(this.mSeqTree.getRoot());
			}
		}
		else
		{
			this.mValidTermination = false;
		}
		
		// If this was a 'real' termination request, move the current activity
		if (!iTentative)
		{
			this.mSeqTree.setCurrentActivity(this.mSeqTree.getFirstCandidate());
		}
		
		var tmpID = this.mSeqTree.getFirstCandidate().getID();
		
		if ((this.mSeqTree.getCurrentActivity() == this.mSeqTree.getRoot()) && (seqReq == SEQ_RETRY) &&
								(this.mSeqTree.getScopeID() != null))
			{
				var objectives = this.mSeqTree.getGlobalObjectives(); 
				
				if ( objectives != null)
				{
					adl_seq_utilities.clearGlobalObjs( this.mSeqTree.getLearnerID(), this.mSeqTree.getScopeID(), objectives);
				}
				
			}
		
		return seqReq;
	},

	invokeRollup: function (ioTarget, iWriteObjIDs)
	{
		sclog("OverallRollup [RB.1.5]","seq");
	  
		var rollupSet = new Object();		// Hashtable
		
		// Case #1 -- Rollup applies along the active path
		if (ioTarget == this.mSeqTree.getCurrentActivity())
		{
			var walk = ioTarget;
			
			// Walk from the target to the root, apply rollup rules at each step
			while (walk != null)
			{
				rollupSet[walk.getID()] = walk.getDepth();
				
				var writeObjIDs = walk.getObjIDs(null, false);
				
				if (writeObjIDs != null)
				{
					for (var i = 0; i < writeObjIDs.length; i++)
					{
						var objID = writeObjIDs[i];
						
						// Need to identify all activity's that 'read' this objective
						// into their primary objective -- those activities need to be
						// included in the rollup set
						var acts = this.mSeqTree.getObjMap(objID);
						
						if (acts != null)
						{
							for (var j = 0; j < acts.length; j++)
							{
								var act = this.getActivity(acts[j]);
								
								// Only rollup at the parent of the affected activity
								act = act.getParent();
								
								if (act != null)
								{
									// Only add if the activity is selected
									if (act.getIsSelected())
									{
										do
										{
											rollupSet[act.getID()] = act.getDepth();
											act = act.getParent();
										}while(act != null && act != this.mSeqTree.getRoot());
									}
								}
							}
						}
					}
				}
				
				walk = walk.getParent();
			}
			
			// Remove the Current Activity from the rollup set
			delete rollupSet[ioTarget.getID()];
			//rollupSet.splice(ioTarget.getID(),1);
		}
		
		// Case #2 -- Rollup applies when the state of a global shared objective
		// is written to...
		if (iWriteObjIDs != null)
		{
			for (var i = 0; i < iWriteObjIDs.length; i++)
			{
				var objID = iWriteObjIDs[i];
				
				// Need to identify all activity's that 'read' this objective
				// into their primary objective -- those activities need to be
				// included in the rollup set
				var acts = this.mSeqTree.getObjMap(objID);
				
				if (acts != null)
				{
					for (var j = 0; j < acts.length; j++)
					{
						var act = this.getActivity(acts[j]);
						
						// Only rollup at the parent of the affected activity
						act = act.getParent();
						
						if (act != null)
						{
							// Only add if the activity is selected
							if (act.getIsSelected())
							{
								do
								{
									rollupSet[act.getID()] = act.getDepth();
									act = act.getParent();
								}
								while ( act != null && act != this.mSeqTree.getRoot() );
							}
						}
					}
				}		
			}
		}
		
		//count properties
		var count=0;
		for (x in rollupSet) {
			count++;
		}
		//sclogdump(ioTarget.mActivityID,"error");
		//sclogdump(rollupSet,"error");
		// Perform the deterministic rollup extension
		while (count>0)
		{
			
			// Find the deepest activity
			var deepest = null;
			var depth = -1;
			
			for (var key in rollupSet)
			{
				var thisDepth = rollupSet[key];
				
				if (depth == -1)
				{
					depth = thisDepth;
					deepest = this.getActivity(key);
				}
				else if (thisDepth >= depth)
				{
					depth = thisDepth;
					deepest = this.getActivity(key);
				}
			}
			
			if (deepest != null)
			{
				//JS does not write back modified function parameters
				//rollupSet=this.doOverallRollup(deepest, rollupSet);
				
				retf=this.doOverallRollup(deepest, rollupSet);
				
				rollupSet=retf.ioRollupSet;
				deepest=retf.ioTarget;
				
				count=0;
				for (x in rollupSet) {
						count++;
				}
				
				//rollupSet cound properties
				
				// If rollup was performed on the root, set the course's status
				if (deepest == this.mSeqTree.getRoot())
				{
					
					var satisfied = "unknown";
					if (deepest.getObjStatus(false))
					{
						satisfied = (deepest.getObjSatisfied(false))
							? "satisfied"
							: "notSatisfied";
					}
					
					var measure = "unknown";
					if (deepest.getObjMeasureStatus(false))
					{
						measure = deepest.getObjMeasure(false);
					}
					
					var completed = "unknown";
					if (deepest.getProgressStatus(false))
					{
						completed = (deepest.getAttemptCompleted(false))
							? "completed"
							: "incomplete";
					}
					
					var progmeasure = "unknown";
					if (deepest.getProMeasureStatus(false))
					{
						progmeasure = deepest.getProMeasure(false);
					}
					adl_seq_utilities.setCourseStatus(this.mSeqTree.getCourseID(),
						this.mSeqTree.getLearnerID(),satisfied,measure,completed, progmeasure);
				}
			}
		}
	},

	getCourseStatusByGlobalObjectives: function()
	{
		this.invokeRollup(this.mSeqTree.getFirstCandidate(), null);
	},

	doOverallRollup: function (ioTarget, ioRollupSet)
	{
		// This method implements the loop of RB.1.5.  The other rollup process
		// are encapsulated in the RollupRuleset object.
		// Attempt to get Rollup Rule information from the activity node
		var rollupRules = ioTarget.getRollupRules();
		
		if (rollupRules == null)
		{
			rollupRules = new SeqRollupRuleset();
		}
		
		// Apply the rollup processes to the activity
		ioTarget=rollupRules.evaluate(ioTarget);
		
		// Remove this activity from the rollup set
		delete ioRollupSet[ioTarget.getID()];
		
		//new return object
		var ret=new Object();
		ret.ioRollupSet=ioRollupSet;
		ret.ioTarget=ioTarget;
		//ioRollupSet.splice(ioTarget.getID(),1);
		return ret;
	},

	prepareClusters: function ()
	{
		var walk = this.mSeqTree.getRoot();
		var lookAt = new Array();
		
		if (walk != null)
		{
			while (walk != null)
			{
				// Only prepare clusters
				if (walk.hasChildren(true))
				{
					if (!walk.getSelectionTiming() == TIMING_NEVER)
					{
						if (!walk.getSelection())
						{
							walk=this.doSelection(walk);
							walk.setSelection(true);
						}
					}
					
					if (!walk.getRandomTiming() == TIMING_NEVER)
					{
						if (!walk.getRandomized())
						{
							walk=this.doRandomize(walk);
							walk.setRandomized(true);
						}
					}
					
					// Keep track of children we still need to look at
					if (walk.hasChildren(false))
					{
						lookAt[lookAt.length] = walk;
					}
				}
				
				// Move to next activity
				walk = walk.getNextSibling(false);
				
				if (walk == null)
				{
					if (lookAt.length != 0)
					{
						walk = lookAt[0];
						walk = walk.getChildren(false)[0];
						
						//lookAt.remove(0);
						lookAt.splice(0,1);
					}
				}
			}
		}
	},

	doSelection: function (ioCluster)
	{
		// Make sure this is a cluster
		if (ioCluster.getChildren(true) != null)
		{
			var count = ioCluster.getSelectCount();
			var all = ioCluster.getChildren(true);
			
			var children = null;
			var set = null;
			
			var ok = false;
			var rand = 0;
			var num = 0;
			var lookUp = 0;
			
			// First select the Select Count number of children
			if (count > 0)
			{
				// Check to see if the count exceeds the number of children
				if (count < all.length)
				{
					
					// Select count activities from the set of children
					children = new Array();
					set = new Array();
					
					while (set.length < count)
					{
						// Find an unselected child of the cluster 
						ok = false;
						while (!ok)
						{
							//var gen = new Random();
							//rand = gen.nextInt();
							//num = Math.abs(rand % all.length);
							num = Math.floor(Math.random() * all.length);
							lookUp = index_of(set, num);
							
							if (lookUp == -1)
							{
								set[set.length] = num;
								ok = true;
							}
						}
					}
					
					// Create the selected child vector
					for (var i = 0; i < all.length; i++)
					{
						lookUp = index_of(set, i);
						
						if (lookUp != -1)
						{
							children[children.length] = all[i];
						}
					}
					
					// Assign the selected set of children to the cluster
					ioCluster.setChildren(children, false);
					
				}
			}
		}
		//we have to return this, cause JS does no write back to function parameters
		return ioCluster;
	},

	doRandomize: function (ioCluster)
	{
		// Make sure this is a cluster
		if (ioCluster.getChildren(true) != null)
		{
			var all = ioCluster.getChildren(false);
			var set = null;
			
			var ok = false;
			var rand = 0;
			var num = 0;
			var lookUp = 0;
			
			// Reorder the 'selected' child set if neccessary
			if (ioCluster.getReorderChildren())
			{
				var reorder = new Array();
				set = new Array();
				
				for (var i = 0; i < all.length; i++)
				{
					// Pick an unselected child
					ok = false;
					while (!ok)
					{
						//Random gen = new Random();
						//rand = gen.nextInt();
						//num = Math.abs(rand % all.length);
						//lookUp = set.indexOf(new Integer(num));
						num = Math.floor(Math.random() * all.length);
						lookUp = index_of(set, num);
						
						if (lookUp == -1)
						{
							set[set.length] = num;
							reorder[reorder.length] = all[num];
							ok = true;
						}
					}
				}
				
				// Assign the current set of active children to this cluster
				ioCluster.setChildren(reorder, false);
			}
		}
		return ioCluster;
	},

	doSequencingRequest: function (iRequest)
	{
		// This method implements the Sequencing Request Process (SB.2.12)
		sclog("SequencingRequest [SB.2.12]","seq");
	 
		var delReq = null;
		
		// Clear global state
		this.mEndSession = false;
		
		// All sequencing requests are processed from the First Candidate
		var from = this.mSeqTree.getFirstCandidate();
		
		if (iRequest == SEQ_START)
		{
			// This block implements the Start Sequencing Request Process (SB.2.
			sclog("StartSequencingRequest [SB.2.5]","seq");
	    	
			// Make sure this request will begin a new session
			if (from == null)
			{
				// Begin traversing the activity tree from the root
				var walk = new Walk();
				walk.at = this.mSeqTree.getRoot();
				
				var success = this.processFlow(FLOW_FORWARD, true, walk, false);
				
				if (success)
				{
					// Delivery request is where flow stopped.
					delReq = walk.at.getID();
				}
			}
		}
		else if (iRequest == SEQ_RESUMEALL)
		{
			// This block implements the Resume All Sequencing Request Process
			// (SB.2.6)
			sclog("ResumeAllSequencingRequest [SB.2.6]","seq");
    	  
			// Make sure this request will begin a new session
			if (from == null)
			{
				var resume = this.mSeqTree.getSuspendAll();
				
				if (resume != null)
				{
					delReq = resume.getID();
				}
			}
		}
		else if (iRequest == SEQ_CONTINUE)
		{
			// This block implements the Continue Sequencing Request Process
			// (SB.2.7)
			sclog("ContinueSequencingRequest [SB.2.7]","seq");
    	  
			// Make sure the session has already started
			if (from != null)
			{
				// Confirm 'flow' is enabled
				var parent = from.getParent();
				if (parent == null || parent.getControlModeFlow())
				{
					
					// Begin traversing the activity tree from the root
					var walk = new Walk();
					walk.at = from;
					
					var success = this.processFlow(FLOW_FORWARD, false, walk, false);
					
					if (success)
					{
						// Delivery request is where flow stopped.
						delReq = walk.at.getID();
					}
					else
					{
						// If the Continue Navigation Request failed here, 
						// we walked off the tree -- end the Sequencing Session
						this.terminateDescendentAttempts(this.mSeqTree.getRoot());
						ret=this.endAttempt(this.mSeqTree.getRoot(), false);
						
						// Start any subsequent seqencing request from the root
						this.mSeqTree.setFirstCandidate(ret);
						
						// The sequencing session is over -- set global state
						this.mEndSession = true;
					}
				}
			}
		}
		else if (iRequest == SEQ_EXIT)
		{
			// This block implements the Exit Sequencing Request Process
			// (SB.2.11)
			sclog("ExitSequencingRequest [SB.2.11]","seq");
    	  
			// Make sure the session has already started
			if (from != null)
			{
				if (!from.getIsActive())
				{
					var parent = from.getParent();
					
					if (parent == null)
					{
						// The sequencing session is over -- set global state
						this.mEndSession = true;
					}
				}
			}
		}
		else if (iRequest == SEQ_PREVIOUS)
		{
			// This block implements the Previous Sequencing Request Process
			// (SB.2.8)
			sclog("PreviousSequencingRequest [SB.2.5]","seq");
    	  
			// Make sure the session has already started
			if (from != null)
			{
				// Confirm 'flow' is enabled
				var parent = from.getParent();
				if (parent == null || parent.getControlModeFlow())
				{
					// Begin traversing the activity tree from the root
					var walk = new Walk();
					walk.at = from;
					
					var success = this.processFlow(FLOW_BACKWARD, false, walk, false);
					
					if (success)
					{
						// Delivery request is where flow stopped.
						delReq = walk.at.getID();
					}
				}
			}
		}
		else if (iRequest == SEQ_RETRY)
		{
			// This block implements the Retry Sequencing Request Process
			// (SB.2.10)
			// Make sure the session has already started
			sclog("RetrySequencingRequest [SB.2.10]","seq");
			if (from != null)
			{
				if (this.mExitAll || (!(from.getIsActive() || from.getIsSuspended())))
				{
					if (from.getChildren(false) != null)
					{
						var walk = new Walk();
						walk.at = from;

						// Set 'Retry' flag
						this.setRetry(true);
			    	  	
						var success = this.processFlow(FLOW_FORWARD, true, walk, false);
						
						// Reset 'Retry' flag
						this.setRetry(false);
						
						if (success)
						{
							delReq = walk.at.getID();
						}
					}
					else
					{
						delReq = from.getID();
					}
				}
			}
		}
		else if (this.mIsJump)
		{
			var target = this.getActivity(iRequest);
			
			if ( target != null)
			{
				delReq = target.getID();
			}
		}
		else
		{
			// This block implements the Choice Sequencing Request Process (SB.2)
			sclog("ChoiceSequencingRequest [SB.2.9]","seq");
    	  
			// The sequencing request identifies the target activity
			var target = this.getActivity(iRequest);
			
			if (target != null)
			{
				var process = true;
				var parent = target.getParent();
				
				// Check if the activity should be considered.
				if (!target.getIsSelected())
				{
					// Exception SB.2.9-2
					process = false;
				}
				
				if (process)
				{
					var walk = target;
					
					// Walk up the tree evaluating 'Hide from Choice' rules.
					while (walk != null)
					{
						// Attempt to get rule information from the activity
						var hideRules = walk.getPreSeqRules();
						var result = null;
						
						if (hideRules != null)
						{
							result = hideRules.evaluate(RULE_TYPE_HIDDEN,walk, false);
						}
						
						// If the rule evaluation did not return null, the activity
						// must be hidden.
						if (result != null)
						{
							// Exception SB.2.9-3
							walk = null;
							process = false;
						}
						else
						{
							walk = walk.getParent();
						}
					}
				}
				
				// Confirm the control mode is valid
				if (process)
				{
					if (parent != null)
					{
						if (!parent.getControlModeChoice())
						{
							// Exception SB.2.9-4
							process = false;
						}
					}
				}
				
				var common = this.mSeqTree.getRoot();
				
				if (process)
				{
					if (from != null)
					{
						common = this.findCommonAncestor(from, target);
						
						if (common == null)
						{
							process = false;
						}
					}
					else
					{
						// If the sequencing session has not begun, start at the root
						from = common;
					}
					
					// Choice Case #1 -- The current activity was selected
					if (from == target)
					{
						// Nothing more to do...
					}
					
					// Choice Case #2 -- The current activity and target are in the
					//                   same cluster
					else if (from.getParent() == target.getParent())
					{
						
						var dir = FLOW_FORWARD;
						
						if (target.getActiveOrder() < from.getActiveOrder())
						{
							dir = FLOW_BACKWARD;
						}
						
						var walk = from;
						
						// Make sure no control modes or rules prevent the traversal
						while (walk != target && process)
						{
							process = this.evaluateChoiceTraversal(dir, walk);
							
							if (dir == FLOW_FORWARD)
							{
								walk = walk.getNextSibling(false);
							}
							else
							{
								walk = walk.getPrevSibling(false);
							}
						}
					}
					
					// Choice Case #3 -- Path to the target is forward in the tree
					else if (from == common)
					{
						
						var walk = target.getParent();
						
						while (walk != from && process)
						{
							process = this.evaluateChoiceTraversal(FLOW_FORWARD, walk);
							
							// Test prevent Activation
							if (process)
							{
								if (!walk.getIsActive() && 
									walk.getPreventActivation())
								{
									// Exception 2.9-6
									process = false;
									continue;
								}
							}
							
							walk = walk.getParent();
						}
						
						// Evaluate at the common ancestor
						if (process)
						{
							process = this.evaluateChoiceTraversal(FLOW_FORWARD, walk);
						}
					}
					
					// Choice Case #4 -- Path to target is backward in the tree
					else if (target == common)
					{
						// Don't need to test choiceExit on the current activity 
						// because the navigation request validated.
						var walk = from.getParent();
						
						while (walk != target && process)
						{
							// Need to make sure that none of the 'exiting' activities
							// prevents us from reaching the common ancestor.
							process = walk.getControlModeChoiceExit();
							walk = walk.getParent();
						}
					}
					
					// Choice Case #5 -- Target is a descendent of the ancestor
					else
					{
						var con = null;
						var walk = from.getParent();
						
						// Walk up the tree to the common ancestor
						while (walk != common && process)
						{
							process = walk.getControlModeChoiceExit();
							
							if (process && con == null)
							{
								if (walk.getConstrainChoice())
								{
									con = walk;
								}
							}
							
							walk = walk.getParent();
						}
						
						// Evaluate constrained choice set
						if (process && con != null)
						{
							var walkCon = new Walk();
							walkCon.at = con;
							
							if (target.getCount() > con.getCount())
							{
								this.processFlow(FLOW_FORWARD, false, walkCon, true);
							}
							else
							{
								this.processFlow(FLOW_BACKWARD, false, walkCon, true);   
							}
							
							if (target.getParent() != walkCon.at &&
								target != walkCon.at)
							{
								// Exception SB.2.9-8
								process = false;
							}
						}
						
						// Walk down the tree to the target
						walk = target.getParent();
						
						while (walk != common && process)
						{
							process = this.evaluateChoiceTraversal(FLOW_FORWARD, walk);
							
							// Test prevent Activation
							if (process)
							{
								if (!walk.getIsActive() && 
									walk.getPreventActivation())
								{
									// Exception 2.9-6
									process = false;
									continue;
								}
							}
							walk = walk.getParent();
						}
						
						// Evaluate the common ancestor
						if (process)
						{
							process = this.evaluateChoiceTraversal(FLOW_FORWARD,
								walk);
						}
					}
					
					// Did we reach the target successfully?
					if (process)
					{
						
						// Is the target a cluster
						if (target.getChildren(false) != null)
						{
							var walk = new Walk();
							walk.at = target;
							
							var success = this.processFlow(FLOW_FORWARD,
								true, walk, false);
							
							if (success)
							{
								delReq = walk.at.getID();
							}
							else
							{
								if (this.mSeqTree.getCurrentActivity() != null &&
									common != null)
								{
									this.terminateDescendentAttempts(common);
									common=this.endAttempt(common, false);
									
									// Move the current activity
									this.mSeqTree.setCurrentActivity(target);
									this.mSeqTree.setFirstCandidate(target);
								}
							}
						}
						else
						{
							delReq = target.getID();
						}
					}
				}
			}
			else
			{
			// Exception SB.2.9-1
			}
		}
		
		return delReq;
	},

	findCommonAncestor: function (iFrom, iTo)
	{
		var ancestor = null;
		var done = false;
		var stepFrom = null;
		
		// If either activity is 'null', no common parent
		if (iFrom == null || iTo == null)
		{
			done = true;
		}
		else
		{
			// Get the starting parents -- only look at clusters
			// This algorithm uses the exising 'selected' children.
			if (!iFrom.hasChildren(false))
			{
				stepFrom = iFrom.getParent();
			}
			else
			{
				stepFrom = iFrom;
			}
			
			if (!iTo.hasChildren(false))
			{
				iTo = iTo.getParent();
			}
		}
		
		while (!done)
		{
			// Test if the 'to' activity is a decendent of 'from' parent
			var success = this.isDescendent(stepFrom, iTo);
			
			// If we found the target activity, we are done
			if (success)
			{
				ancestor = stepFrom;
				done = true;
				continue;
			}
		
			// If this isn't the common parent, move up the tree
			if (!done)
			{
				stepFrom = stepFrom.getParent();
			}
		}
		return ancestor;
	},


	isDescendent: function (iRoot, iTarget)
	{
		var found = false;
		
		if (iRoot == null)
		{
		}
		else if (iRoot == this.mSeqTree.getRoot())
		{
			// All activities are descendents of the root
			found = true;
		}
		else if (iRoot != null && iTarget != null)
		{
			while (iTarget != null && !found)
			{
				if (iTarget == iRoot)
				{
					found = true;
				}
				
				iTarget = iTarget.getParent();
			}
		}
		return found;
	},

	walkTree: function (iDirection,iPrevDirection,iEnter,iFrom,iControl)
	{
		sclog("FlowTreeTraversalSub [SB.2.1]","seq");
	   
		// This method implements Flow Subprocess SB.2.1
		var next = null;
		var parent = null;
		
		var direction = iDirection;
		var reversed = false;
		
		var done = false;
		var endSession = false;
		
		if (iFrom == null)
		{
			// The sequencing session is over
			endSession = true;
			done = true;     
		}
		else
		{
			parent = iFrom.getParent();
		}
		
		// Test if we have skipped all of the children in a 'forward-only' 
		// cluster traversing backward
		if (!done && parent != null)
		{
			if (iPrevDirection == FLOW_BACKWARD)
			{
				if (iFrom.getNextSibling(false) == null)
				{
					// Switch traversal direction
					direction = FLOW_BACKWARD;
					
					// Move our starting point
					iFrom = parent.getChildren(false)[0];
					
					reversed = true;
				}
			}
		}
		
		if (!done && direction == FLOW_FORWARD)
		{
			if (iFrom.getID() == this.mSeqTree.getLastLeaf())
			{
				// We are at the last leaf of the tree, the sequencing 
				// session is over
				done = true;
				endSession = true;
			}
			
			if (!done)
			{
				// Is the activity a leaf or a cluster that should not be entered
				if (!iFrom.hasChildren(false) || !iEnter)
				{
					next = iFrom.getNextSibling(false);
					
					if (next == null)
					{
						var walk = this.walkTree(direction, FLOW_NONE,
							false, parent, iControl);
						
						next = walk.at;
						endSession = walk.endSession;
					}
				}
				// Enter the Cluster
				else
				{
					// Return the first child activity
					next = iFrom.getChildren(false)[0];
				}
			}
		}
		else if (!done && direction == FLOW_BACKWARD)
		{
			// Can't walk off the root of the tree
			if (parent != null)
			{
				// Is the activity a leaf or a cluster that should not be entered
				if (!iFrom.hasChildren(false) || !iEnter)
				{
					// Make sure we can move backward
					if (iControl && !reversed)
					{
						if (parent.getControlForwardOnly())
						{
							done = true;
						}
					}
					
					if (!done)
					{
						next = iFrom.getPrevSibling(false);
						
						if (next == null)
						{
							var walk = this.walkTree(direction, FLOW_NONE,
								false, parent, iControl);
							next = walk.at;
							endSession = walk.endSession;
						}
					}
				}
				
				// Enter the cluster backward
				else
				{
					if (iFrom.getControlForwardOnly())
					{
						// Return the first child activity
						next = iFrom.getChildren(false)[0];
						
						// And switch direction
						direction = FLOW_FORWARD;
					}
					else
					{
						var size = iFrom.getChildren(false).length;
						
						// Return the last child activity
						next = iFrom.getChildren(false)[size - 1];
					}
				}
			}
		}
		
		var walk = new Walk();
		walk.at = next;
		walk.direction = direction;
		walk.endSession = endSession;
		
		return walk;
	},


	walkActivity: function (iDirection,iPrevDirection,ioFrom)
	{
		// This method implements Flow Subprocess SB.2.3
		sclog("FlowActivityTraversalSub [SB.2.]","seq");
	   
		var deliver = true;
		var parent = ioFrom.at.getParent();
		
		if (parent != null)
		{
			// Confirm that 'flow' is enabled for the cluster
			if (!parent.getControlModeFlow())
			{
				deliver = false;
			}
		}
		else
		{
			deliver = false;
		}
		
		if (deliver)
		{
			// Check if the activity should be 'skipped'.
			var result = null;
			var skippedRules = ioFrom.at.getPreSeqRules();
			
			if (skippedRules != null)
			{
				//alert("Check prerules");
				result = skippedRules.evaluate(RULE_TYPE_SKIPPED, 
					ioFrom.at, false);
				//alert("Result Prerules"+result);
			}
			// If the rule evaluation did not return null, the activity is skipped
			if (result != null)
			{
				var walk  =
					this.walkTree(iDirection, iPrevDirection, false, ioFrom.at, true);
				
				if (walk.at == null)
				{
					deliver = false;
				}
				else
				{
					ioFrom.at = walk.at;
					
					// Test if we've switched directions...
					if (iPrevDirection == FLOW_BACKWARD &&
						walk.direction == FLOW_BACKWARD)
					{
						return this.walkActivity(FLOW_BACKWARD, FLOW_NONE, ioFrom);
					}
					else
					{
						return this.walkActivity(iDirection, iPrevDirection, ioFrom);
					}
				}
			}
			else
			{
				// The activity was not skipped, make sure it is enabled
				if (!this.checkActivity(ioFrom.at))
				{
					// Make sure the activity being considered is a leaf
					if (ioFrom.at.hasChildren(false))
					{
						var walk = this.walkTree(iDirection,
							FLOW_NONE, true, ioFrom.at, true);
						
						if (walk.at != null)
						{
							ioFrom.at = walk.at;
							
							if (iDirection == FLOW_BACKWARD &&
								walk.direction ==  FLOW_FORWARD)
							{
								deliver = this.walkActivity(FLOW_FORWARD,
									FLOW_BACKWARD, ioFrom);
							}
							else
							{
								deliver = this.walkActivity(iDirection,
									FLOW_NONE, ioFrom);
							}
						}
						else
						{
							deliver = false;
						}
					}
				}
				else
				{
					deliver = false;
				}
			}
		}
		return deliver;
	},

	processFlow: function (iDirection, iEnter, ioFrom, iConChoice)
	{
		// This method implements Flow Subprocess SB.2.3
		sclog("FlowSub [SB.2.3]","seq");
	   
		var success = true;
		var candidate = ioFrom.at;
		
		// Make sure we have somewhere to start from
		if (candidate != null)
		{
			var walk = this.walkTree(iDirection, FLOW_NONE, iEnter,
				candidate, !iConChoice);
			
			if (!iConChoice && walk.at != null)
			{
				ioFrom.at = walk.at;
				success = this.walkActivity(iDirection, FLOW_NONE, ioFrom);
			}
			else
			{
				if (iConChoice)
				{
					ioFrom.at = walk.at;
				}
				success = false;
			}
			
			// Check to see if the sequencing session is ending due to
			// walking off the activity tree
			if (walk.at == null && walk.endSession)
			{
				// End the attempt on the root of the activity tree
				this.terminateDescendentAttempts(this.mSeqTree.getRoot());
				// The sequencing session is over -- set global state
				this.mEndSession = true;
				success = false;
			}
		}
		else
		{
			success = false;
		}
		
		return success;
	},

	evaluateChoiceTraversal: function (iDirection, iAt)
	{
		// This method implements Choice Activity Traversal Subprocess SB.2.4
		sclog("ChoiceActivityTraversalSub [SB.2.4]","seq");
	   
		var success = true;
		
		// Make sure we have somewhere to start from
		if (iAt != null)
		{
			if (true)
			{
				if (iDirection == FLOW_FORWARD)
				{
					// Attempt to get rule information from the activity node
					var stopTrav = iAt.getPreSeqRules();
					var result = null;
					
					if (stopTrav != null)
					{
						result = stopTrav.evaluate(RULE_TYPE_FORWARDBLOCK, iAt, false);
					}
					
					// If the rule evaluation does not return null, can't move to the
					// activity's sibling
					if (result != null)
					{
						success = false;
					}
				}
				else if (iDirection == FLOW_BACKWARD)
				{
					var parent = iAt.getParent();
					
					if (parent != null)
					{
						success = !parent.getControlForwardOnly();
					}
				}
				else
				{
					success = false;
				}
			}
			else
			{
				success = false;
			}
		}
		else
		{
			success = false;
		}
		return success;
	},

	doDeliveryRequest: function (iTarget,iTentative,oLaunch)
	{
		// This method implements DB.1.  Also, if the delivery request is not
		// tentative, it invokes the Content Delivery Environment Process.
		sclog("DeliveryRequest [DB.1.1]","seq");
	   
		var deliveryOK = true;
		
		// Make sure the identified activity exists in the tree.
		var act = this.getActivity(iTarget);
		
		if (act ==  null)
		{
			
			// If there is no activity identified for delivery, there is nothing
			// to delivery -- indentify non-Sequenced content
			deliveryOK = false;
			
			if (!iTentative)
			{
				if (this.mExitCourse)
				{
					oLaunch.mSeqNonContent = LAUNCH_COURSECOMPLETE;
				}
				else
				{
					if (this.mEndSession)
					{
						oLaunch.mSeqNonContent = LAUNCH_EXITSESSION;
					}
					else
					{
						oLaunch.mSeqNonContent = LAUNCH_SEQ_BLOCKED;
					}
				}
			}
		}
		
		// Confirm the target activity is a leaf
		if (deliveryOK && act.hasChildren(false))
		{
			deliveryOK = false;
			
			oLaunch.mSeqNonContent = LAUNCH_ERROR;
			oLaunch.mEndSession = this.mEndSession;
		}
		else if (deliveryOK)
		{
			var ok = true;
			
			// Walk the path from the target activity to the root, checking each
			// activity.
			while (act != null && ok)
			{
				ok = !this.checkActivity(act);
				if (ok)
				{
					act = act.getParent();
				}
			}
			
			if (!ok)
			{
				deliveryOK = false;
				oLaunch.mSeqNonContent = LAUNCH_NOTHING;
			}
		}
		
		// If the delivery request not a tentative request, prepare for deliver
		if (!iTentative)
		{
			// Did the request validate
			if (deliveryOK)
			{
				this.contentDelivery(iTarget, oLaunch);
				this.validateRequests();
			}
			else
			{
				oLaunch.mEndSession = this.mEndSession || this.mExitCourse;
				
				if (!oLaunch.mEndSession)
				{
					this.validateRequests();
					oLaunch.mNavState = this.mSeqTree.getValidRequests();
				}
			}
		}
		return deliveryOK;
	},

	contentDelivery: function (iTarget, oLaunch)
	{
		
		// This method implements the Content Delivery Environment Process (DB.2)
		sclog("ContentDeliveryEnvironment [DB.2]","seq");
	   
		var target = this.getActivity(iTarget);
		var done = false;
		
		if (target == null)
		{
			oLaunch.mSeqNonContent = LAUNCH_ERROR;
			oLaunch.mEndSession = this.mEndSession;
			done = true;
		}
		
		var cur = this.mSeqTree.getFirstCandidate();
		
		if (cur != null  && done==false)
		{
			if (cur.getIsActive()==true)
			{
				oLaunch.mSeqNonContent = LAUNCH_ERROR;
				oLaunch.mEndSession = this.mEndSession;
				done = true;
			}
		}
		
		if (done==false)
		{
			// Clear any 'suspended' activity
			this.clearSuspendedActivity(target);
			
			// End any active attempts
			this.terminateDescendentAttempts(target);
			
			// Begin all required new attempts
			var begin = new Array();
			var walk = target;
			
			while (walk != null)
			{
				begin[begin.length] = walk;
				walk = walk.getParent();
			}
			
			
			if (begin.length > 0)
			{
				for (var i = begin.length - 1; i >= 0; i--)
				{
					walk = begin[i];
					if (!walk.getIsActive())
					{
						if (walk.getIsTracked())
						{
							if (walk.getIsSuspended())
							{
								walk.setIsSuspended(false);
							}
							else
							{
								// Initialize tracking information for the new attempt
								walk.incrementAttempt();
							}
						}
						walk.setIsActive(true);
					}
				}
			}
			
			// Set the tree in the appropriate state
			this.mSeqTree.setCurrentActivity(target);
			this.mSeqTree.setFirstCandidate(target);
			
			// Fill in required launch information
			oLaunch.mEndSession = this.mEndSession;
			oLaunch.mActivityID = iTarget;
			oLaunch.mResourceID = target.getResourceID();
			
			oLaunch.mStateID = target.getStateID();
			if (oLaunch.mStateID == null)
			{
				oLaunch.mStateID = iTarget;
			}
			
			oLaunch.mNumAttempt = target.getNumAttempt() + 
				target.getNumSCOAttempt();
			oLaunch.mMaxTime = target. getAttemptAbDur();
			
			// Create auxilary services vector
			var services = new Object();
			var test = null;
			walk = target;
			
			// Starting at the target activity, walk up the tree adding services
			while (walk != null)
			{
				var curSet = walk.getAuxResources();
				if (curSet != null)
				{
					for (var i = 0; i < curSet.length; i++)
					{
						var res = null;
						res = curSet[i];
						
						// If the resource isn't already included in the set, add it
						test = services[res.mType];
						
						if (test == null)
						{
							services[res.mType] = res;
						}
					}
				}
				
				// Walk up the tree
				walk = walk.getParent();
			}
			
			if (services.length > 0)
			{
				oLaunch.mServices = services;
			}
		}
		
		this.validateRequests();
		oLaunch.mNavState = this.mSeqTree.getValidRequests();
		
		
		// Make sure Continue Exit is not enabled for non-content
		if (oLaunch.mSeqNonContent != null)
		{
			oLaunch.mNavState.mContinueExit = false;
		}
	
	},

	clearSuspendedActivity: function (iTarget)
	{
		// This method implements the Clear Supsended Activity Subprocess (DB.2)
		sclog("ClearSuspendedActivitySub [DB.2.1]","seq");
	   
		var act = this.mSeqTree.getSuspendAll();
		
		if (iTarget == null)
		{
			act = null;
		}
		
		if (act != null)
		{
			if (iTarget != act)
			{
				var common = this.findCommonAncestor(iTarget, act);
				
				while (act != common)
				{
					act.setIsSuspended(false);
					var children = act.getChildren(false);
					
					if (children != null)
					{
						var done = false;
						
						for (var i = 0; i < children.length && !done; i++)
						{
							var lookAt = children[i];
							
							if (lookAt.getIsSuspended())
							{
								act.setIsSuspended(true);
								done = true;
							}
						}
					}
					
					act = act.getParent();
				}
			}
			
			// Clear the suspended activity
			var temp = null;
			this.mSeqTree.setSuspendAll(temp);
		}
	},

	evaluateLimitConditions: function (iTarget)
	{
		// This is an implementation of UP.1
		sclog("LimitConditionsCheck [UP.1]","seq");
	   
		var disabled = false;
		
		// Only test limitConditions if the activity is not active
		if (!iTarget.getIsActive() && !iTarget.getIsSuspended())
		{
			if (iTarget.getAttemptLimitControl())
			{
				disabled = iTarget.getNumAttempt() >= iTarget.getAttemptLimit();
			}
		}
		
		return disabled;
	},

	terminateDescendentAttempts: function (iTarget)
	{
		
		// This is an implementation of the Terminate Descendent Attempts
		// Process (UP.3)
		sclog("TerminateDescendentAttempts [UP.3]","seq");
	   
		var cur = this.mSeqTree.getFirstCandidate();
		
		if (cur != null)
		{
			var common = this.findCommonAncestor(cur, iTarget);
			var walk = cur;
			
			while (walk != common)
			{
				walk=this.endAttempt(walk, false);
				walk = walk.getParent();
			}
		}
	},

	endAttempt: function (iTarget, iTentative)
	{
		sclog("EndAttempt [UP.4]","seq");
	   
		// This is an implementation of the End Attempt Process (UP.4)
		if (iTarget != null)
		{
			var children = iTarget.getChildren(false);
			
			// Is the activity a tracked leaf
			if (children == null && iTarget.getIsTracked())
			{
				// If the attempt was not suspended, perform attempt cleanup
				if (!iTarget.getIsSuspended())
				{
					if (!iTarget.getSetCompletion())
					{
						// If the content hasn't set this value, set it
						if (!iTarget.getProgressStatus(false) && !iTarget.isPrimaryProgressSetBySCO())
						{
							iTarget.setProgress(TRACK_COMPLETED);
						}
					}
					
					if (!iTarget.getSetObjective())
					{
						//alert('Set satisfied');
						// If the content hasn't set this value, set it
						if (!iTarget.getObjStatus(false, true) && !iTarget.isPrimaryStatusSetBySCO() )
						{
							iTarget.setObjSatisfied(TRACK_SATISFIED);
						}
						else if ( iTarget.getObjSatValue() == TRACK_UNKNOWN ) 
						{
							iTarget.clearObjStatus();
						}
					}
				}
			}
			else if (children != null)
			{
				// The activity is a cluster, check if any of its children are
				// suspended.
				
				// Only set suspended state if this is a 'real' termiantion
				if (!iTentative)
				{
					iTarget.setIsSuspended(false);
					for (var i = 0; i < children.length; i++)
					{
						var act = children[i];
						if (act.getIsSuspended())
						{
							iTarget.setIsSuspended(true);
							break;
						}
					}
					
					// If the cluster is not suspended check for selection and
					// randomization 
					if (!iTarget.getIsSuspended())
					{
						if (iTarget.getSelectionTiming() == TIMING_EACHNEW)
						{
							iTarget=this.doSelection(iTarget);
							iTarget.setSelection(true);
						}
						
						if (iTarget.getRandomTiming() == TIMING_EACHNEW)
						{
							iTarget=this.doRandomize(iTarget);
							iTarget.setRandomized(true);
						}
					}
				}
			}
			
			// The activity becomes inactive if this is a 'real' termination
			if (!iTentative)
			{
				iTarget.setIsActive(false);
				
				if (iTarget.getIsTracked())
				{
					// Make sure satisfaction is updated according to measure
					iTarget.triggerObjMeasure();
				}
				
				// Invoke rollup
				this.invokeRollup(iTarget, this.getGlobalObjs(iTarget));//null);            
			}
		}
		return iTarget;
	},
	
	getGlobalObjs: function (iTarget)
	{
		var objs = iTarget.getObjectives();		
		var writeMaps = new Array();
		if ( objs != null )
		{
			
			for ( var i = 0; i < objs.length; i++)
			{
				var s = objs[i];
				
				if ( s.mMaps != null )
				{
					
					for ( var m = 0; m < s.mMaps.length; m++)
					{
						var map = s.mMaps[m];
						
						if ( map.hasWriteMaps() )
						{
							writeMaps.push(map.mGlobalObjID);
						}
					}
				}
			}
		}
		
		return writeMaps;
	},

	checkActivity: function (iTarget)
	{
		sclog("CheckActivity [UP.5]","seq");
	   
		// This is an implementation of UP.5.
		var disabled = false;
		var result = null;
		
		// Attempt to get rule information from the activity node
		var disabledRules = iTarget.getPreSeqRules();
		
		if (disabledRules != null)
		{
			result = disabledRules.evaluate(RULE_TYPE_DISABLED,
				iTarget, false);
		}
		
		// If the rule evaluation did not return null, the activity must
		// be disabled.
		if (result != null)
		{
			disabled = true;
		}
		
		if (!disabled)
		{
			// Evaluate other limit conditions associated with the activity.
			disabled = this.evaluateLimitConditions(iTarget);
		}
		
		return disabled;
	},

	setRetry: function (iRetry)
	{
		this.mRetry = false; //iRetry;
	},

	getChoiceSet: function (iOldTOC, oNewTOC)
	{
		var set = null;		// Hashtable
		var lastLeaf = null;
		
		if (iOldTOC != null)
		{
			var temp = null;
			set = new Object();
			
			// Walk backward along the vector looking for the last available leaf
			for (var i = iOldTOC.length - 1; i >= 0; i--)
			{
				temp = iOldTOC[i];
				
				if (!temp.mIsVisible)
				{
					if (temp.mIsSelectable)
					{
						// Not in the TOC, but still a valid target
						set[temp.mID] = temp;
					}
				}
				else if (temp.mIsVisible)
				{
					set[temp.mID] = temp;
					oNewTOC[oNewTOC.length] = temp;
				}
				
				if (lastLeaf == null)
				{
					if (temp.mLeaf && temp.mIsEnabled)
					{
						lastLeaf = temp.mID;
					}
				}
			}
		}
		
		if (lastLeaf != null)
		{
			this.mSeqTree.setLastLeaf(lastLeaf);
		}
		
		// If there are no items in the set, there is no TOC.
		if (set!= null)
		{
			var empty = true;
			for (k in set)
			{
				empty = false;
			}
			if (empty)
			{
				set = null;
			}
		}
		
		// If there is only one item in the set, it must be the root -- remove it
		// If there is only one item in the set, it is the parent of a
		//    choiceExit == false cluster, it cannot be selected -- no TOC
		if (oNewTOC.length == 1)
		{
			var temp = oNewTOC[0];
			
			if (!temp.mIsEnabled)
			{
				//delete oNewTOC[0];
				oNewTOC.splice(0,1);
			}
			else if (!temp.mLeaf)
			{
				//oNewTOC.remove(0);
				oNewTOC.splice(0,1);
				
			}
		}
		return set;
	},

	getTOC: function (iStart)
	{
		var toc = new Array(); //the return of renderable ADLTOC objects
		var temp = null; // used in iterations over the ADLTOC objects in the tree
		var done = false; // used to create the initial tree in pass 1
		
		// Make sure we have an activity tree
		if (this.mSeqTree == null)
		{
			done = true;
		}
		
		// Perform a breadth-first walk of the activity tree.
		var walk = iStart;
		var depth = 0; //used for tracking the depth of the tree
		var parentTOC = -1; //used to determine the parent of a tree
		var lookAt = new Array();
		var flatTOC = new Array();
		
		// Tree traversal status indicators
		var nextsibling = false;
		var include = false;
		var collapse = false;
		var select = false;
		var choosable = false;
		
	
		// Make sure the activity has been associated with this sequencer
		// If not, build the TOC from the root
		if (walk == null)
		{
			walk = this.mSeqTree.getRoot();
		}
		
		// if there was an activity left when the user Suspend All, bring it up
		var cur = this.mSeqTree.getFirstCandidate();
		var curIdx = -1;
		
		if (cur == null)
		{
			cur = this.mSeqTree.getCurrentActivity();
		}
	
		
		while (!done)
		{
			include = true;  // used in ifs to determine which nodes are in and out of the ADLTOC
			select = false; // used to determine if the node is selectable
			choosable = false; // used to determine if a node is in a cluster with choice = true
			collapse = false; // if a node is hidden (watch vs. invisible), it gets collapsed in the tree
			nextsibling = false; // used to move between siblings in the event the node has children
			
			// If the activity is a valid target for a choice sequecing request,
			// make it selectable in the TOC and determine its attributes
			if (walk.getParent() != null)
			{
				if (walk.getParent().getControlModeChoice())
				{
					select = true;
					choosable = true;
				}
			}
			else
			{
				// Always include the root of the activity tree in the TOC
				select = true;
				choosable = true;
			}
			
			// Make sure the activity we are considering is not disabled or hidden
			if (include)
			{
				// Attempt to get rule information from the activity
				var hiddenRules = walk.getPreSeqRules();
				
				var result = null;
				
				if (hiddenRules != null)
				{
					result = hiddenRules.evaluate(RULE_TYPE_HIDDEN,
						walk, false);
				}
				
				// If the rule evaluation did not return null, the activity
				// must be hidden.
				if (result != null)
				{
					include = false;
					collapse = true;
				}
				else
				{
					// Check if this activity is prevented from corresponds to disabled or invisible or descendants of such nodes.
					// Given that this can be both, its a place to look if something isn't rendering correctly
					if (walk.getPreventActivation() && !walk.getIsActive() && walk.hasChildren(true))
					{
						if (cur != null)
						{
							if (walk != cur && cur.getParent() != walk)
							{
								include = true;
								select = true;
							}
						}
						else
						{
							if (walk.hasChildren(true))
							{
								include = true;
								select = true;
							}
						}
					}
				}
			}
			
			// The activity is included in the TOC, set its attributes
			if (include)
			{
				var parent = walk.getParent();
				
				temp = new ADLTOC();
				
				temp.mCount = walk.getCount();
				temp.mTitle = walk.getTitle();
				temp.mDepth = depth;
				temp.mIsVisible = walk.getIsVisible();
				temp.mIsEnabled = !this.checkActivity(walk);
				temp.mInChoice = choosable;
				temp.mIsSelectable = select;
				
				if ( temp.mIsEnabled )
				{
					if( walk.getAttemptLimitControl() == true)
					{
						if (walk.getAttemptLimit() == 0)
						{
								temp.mIsSelectable = false;
						}
					}
				}	
						
				temp.mID = walk.getID();
				
				// Check if we looking at the 'current' cluster
				if (cur != null)
				{
					if (temp.mID == cur.getID())
					{
						temp.mIsCurrent = true;
						curIdx = toc.length; //the index is set to the last node
					}
				}
				
				temp.mLeaf = !walk.hasChildren(false);
				temp.mParent = parentTOC;
				
				toc[toc.length] = temp; // this node is now added with the relevant information
			}
			else
			{
				temp = new ADLTOC();
				
				temp.mCount = walk.getCount();    
				temp.mTitle = walk.getTitle();
				temp.mIsVisible = walk.getIsVisible();
				
				temp.mIsEnabled = !this.checkActivity(walk);
				temp.mInChoice = choosable;
				temp.mDepth = depth;
				
				temp.mID = walk.getID();
				temp.mIsSelectable = false;
				
				temp.mLeaf = (walk.getChildren(false) == null);
				temp.mParent = parentTOC;
				
				if (collapse)
				{
					temp.mIsVisible = false;
				}
				
				toc[toc.length] = temp;
			}
			
			// Add this activity to the "flat TOC"
			flatTOC[flatTOC.length] = walk;
			
			// If this activity has children, look at them later...the false refers to what are considered children, not as a "false" conditional
			if (walk.hasChildren(false))
			{
				// Remember where we are at and look at the children now,
				// unless we are at the root
				if (walk.getParent() != null)
				{
					lookAt[lookAt.length] = walk;
				}
				
				// Go to the first child
				walk = walk.getChildren(false)[0];
				parentTOC = toc.length - 1;
				depth++;
				nextsibling = true;
				
				next = true;
			}
			
			if (!nextsibling)
			{
				// Move to its sibling
				walk = walk.getNextSibling(false);
				temp = toc[toc.length - 1];
				parentTOC = temp.mParent;
				
				while (walk == null && !done)
				{
					if (lookAt.length > 0)
					{
						// Walk back up the tree to the parent's next sibling
						walk = lookAt[lookAt.length - 1];
						//delete lookAt[lookAt.length - 1];
						lookAt.splice(lookAt.length - 1,1);
						depth--;
						
						// Find the correct parent
						temp = toc[parentTOC];
						
						while (!temp.mID == walk.getID())
						{
							parentTOC = temp.mParent;
							temp = toc[parentTOC];
						}
						
						walk = walk.getNextSibling(false);
					}
					else
					{
						done = true;
					}
				}
				
				if (walk != null)
				{
					parentTOC = temp.mParent;
				}
			}
		} //end while
		
		// After the TOC has been created, mark activites unselectable
		// if the Prevent Activation prevents them being selected,
		// and mark them invisible if they are descendents of a hidden
		// from choice activity
		var hiddenDepth = -1;
		var prevented = -1;
		
		for (var i = 0; i < toc.length; i++)
		{
			var tempAct = flatTOC[i];
			var tempTOC = toc[i];
			var checkDepth = tempTOC.mDepth;
			
			// if hiddenDepth has been determined (i.e. not -1)
			if (hiddenDepth !=-1)
			{
				//Check to see if we are doing hiding activities, if we are outside the tree, we are done
				if (checkDepth <= hiddenDepth)
				{
					hiddenDepth = -1;
				}
				else
				{
					// This must be a descendent of the tree - hide/disable it
					tempTOC.mIsSelectable = false;
					tempTOC.mIsVisible = false;
				}
			}
			
			// Evaluate hide from choice rules if it hasn't been found
			if (hiddenDepth == -1)
			{
				// Attempt to get rule information from the activity
				var hiddenRules = tempAct.getPreSeqRules();
				var result = null;
				
				if (hiddenRules != null)
				{
					result = hiddenRules.evaluate(RULE_TYPE_HIDDEN,
						tempAct, false);
				}
				
				// If the rule evaluation did not return null, the activity
				// must be hidden.
				if (result != null)
				{
					// The depth we are looking for should be positive
					hiddenDepth = tempTOC.mDepth;  
					prevented = -1;
				}
				// if the rule evaluation was null, need to look for prevented activities that are not hidden
				else
				{
					if (prevented != -1)
					{
						// Check to see if we are done preventing activities
						if (checkDepth <= prevented)
						{
							// Reset the check until we find another prevented
							prevented = -1;
						}
						// We don't prevent activation on anything with the depth of the node in question
						else if (tempTOC.mDepth == prevented)
						{
						
						}
						else
						{
							// This must be a prevented descendant
							tempTOC.mIsSelectable = false;
						}
					}
					else
					{                 
						// Check if this activity is prevented from activation
						if (tempAct.getPreventActivation() && !tempAct.getIsActive() && tempAct.hasChildren(true))
						{
							if (cur != null)
							{
								if (tempAct != cur && cur.getParent() != tempAct)
								{
									prevented = tempTOC.mDepth;
								}
							}
							// if cur is null, it won't be equal to the tempActivity or it's parent
							else
							{
								prevented = tempTOC.mDepth;
							}
						}
					} //else
				} //else
			} //if hidden =1
		} //for
		
		// After the TOC has been created, mark activites unselectable
		// if the Choice Exit control prevents them being selected
		var noExit = null;
		
		if (this.mSeqTree.getFirstCandidate() != null)
		{
			walk =  this.mSeqTree.getFirstCandidate().getParent();
		}
		else
		{
			walk = null;
		}
		
		// Walk up the active path looking for a non-exiting cluster
		while (walk != null && noExit == null)
		{
			// We cannot choose any target that is outside of the activiy tree,
			// so choice exit does not apply to the root of the tree
			if (walk.getParent() != null)
			{
				if (!walk.getControlModeChoiceExit())
				{
					noExit = walk;
				}
			}
			
			// Move up the tree
			walk = walk.getParent();
		}
		
		if (noExit != null)
		{
			depth = -1; //depth will track the depth of the disabled node and will catch all nodes less than
			// it is and mark them unselectable
			
			// Only descendents of this activity can be selected.
			for (var i = 0; i < toc.length; i++)
			{
				temp = toc[i];
				
				// When we find the the 'non-exiting' activity, remember its depth
				if (temp.mID == noExit.getID())
				{
					depth = temp.mDepth;
					
					// The cluster activity cannot be selected
					temp.mIsSelectable = false;
				}
				// If we haven't found the the 'non-exiting' activity yet, then the
				// activity being considered cannot be selected.
				else if (depth == -1)
				{
					temp.mIsSelectable = false;
				}
				
				// When we back out of the depth-first-walk and encounter a sibling
				// or parent of the 'non-exiting' activity, start making activity
				// unselectable
				else if (temp.mDepth <= depth)
				{
					depth = -1;
					temp.mIsSelectable = false;
				}
			}
		}
		
		// Boundary Condition -- evaluate choice exit on root
		temp = toc[0];
		var root = this.mSeqTree.getRoot();
		
		if (!root.getControlModeChoiceExit())
		{
			temp.mIsSelectable = false;
		}
		
		// Look for constrained activities relative to the current activity and 
		// mark activites unselectable if they are outside of the avaliable set
		var con = null;
		
		if (this.mSeqTree.getFirstCandidate() != null)
		{
			walk =  this.mSeqTree.getFirstCandidate().getParent();
			
			// constrained choice has no effect on the root
			if (walk != null && walk.getID() == this.mSeqTree.getRoot().getID())
			{
				walk = null;
			}
		}
		else
		{
			walk = null;
		}
		
		// Walk up the tree to the root
		while (walk != null && con == null)
		{
			if (walk.getConstrainChoice())
			{
				con = walk;
			}
			walk = walk.getParent();
		}
		
		// Evaluate constrained choice set
		if (con != null)
		{
			var forwardAct = -1;
			var backwardAct = -1;
			var list = null;
			
			var walkCon = new Walk();
			walkCon.at = con;
			
			// Find the next activity relative to the constrained activity.
			this.processFlow(FLOW_FORWARD, false, walkCon, true);
			
			if (walkCon.at == null)
			{
				walkCon.at = con;
			}
			
			var lookFor = "";
			list = walkCon.at.getChildren(false);
			if (list != null)
			{
				var size = list.length;
				lookFor = (list[size - 1]).getID();
			}
			else
			{
				lookFor = walkCon.at.getID();
			}
			
			for (var j = 0; j < toc.length; j++)
			{
				temp = toc[j];
				
				if (temp.mID == lookFor)
				{
					forwardAct = j;
					break;
				}
			}
			
			// Find the previous activity relative to the constrained activity.
			walkCon.at = con;
			this.processFlow(FLOW_BACKWARD, false, walkCon, true);
			
			if (walkCon.at == null)
			{
				walkCon.at = con;
			}
			
			lookFor = walkCon.at.getID();
			for (var j = 0; j < toc.length; j++)
			{
				temp = toc[j];
				
				if (temp.mID == lookFor)
				{
					backwardAct = j;
					break;
				}
			}
			
			// If the forward activity on either end of the range is a cluster,
			// we need to include its descendents
			temp = toc[forwardAct];
			if (!temp.mLeaf)
			{
				var idx = forwardAct;
				var foundLeaf = false;
				
				while (!foundLeaf)
				{
					for (var i = toc.length - 1; i > idx; i--)
					{
						temp = toc[i];
						
						if (temp.mParent == idx)
						{
							idx = i;
							foundLeaf = temp.mLeaf;
							
							break;
						}
					}
				}
				
				if (idx != toc.length)
				{
					forwardAct = idx;
				}
			}
			
			// Need to check if an ancestor of the first available
			// activity is reachable from the first activity in the
			// constrained range, via flow
			var idx = (toc[backwardAct]).mParent;
			var childID = (toc[backwardAct]).mID;
			var avalParent = -1;
			
			while (idx != -1)
			{
				temp = toc[idx];
				
				// We're done checking as soon as we find an activity
				// that is not available for choice
				if (!temp.mIsSelectable || !temp.mIsEnabled)
				{
					break;
				}
				
				// Need to check if we can "flow" from this activity
				var check = this.mSeqTree.getActivity(temp.mID);
				if (check.getControlModeFlow())
				{
					// First need to check if the constrained activity is the first child
					if ((check.getChildren(false)[0]).getID() == childID)
					{   
						childID = (toc[idx]).mID;
						avalParent = idx;
						idx = (toc[avalParent]).mParent;
					}
					else
					{
						break;
					}
				}
				else
				{
					break;
				}
			}
			
			// Include the available ancestors in the constrained range
			if (avalParent != -1 && avalParent < backwardAct)
			{
				backwardAct = avalParent;
			}
			
			// Disable activities outside of the avaliable range
			for (var i = 0; i < toc.length; i++)
			{
				temp = toc[i];
				
				if (i < backwardAct || i > forwardAct)
				{
					temp.mIsSelectable = false;
				}
			}
		}
		
		// Walk the TOC looking for disabled activities and mark them disabled
		if (toc != null)
		{
			depth = -1;
			
			for (var i = 0; i < toc.length; i++)
			{
				temp = toc[i];
				
				if (depth != -1)
				{
					if (depth >= temp.mDepth)
					{
						depth = -1;
					}
					else
					{
						temp.mIsEnabled = false;
						temp.mIsSelectable = false;
					}
				}
				
				if (!temp.mIsEnabled && depth == -1)
				{
					// Remember where the disabled activity is
					depth = temp.mDepth;
				}
			}
		}
		
		// If there is a current activity, check availablity of its siblings
		// This pass corresponds to Case #2 of the Choice Sequencing Request
		if (toc != null && curIdx != -1)
		{
			var par = (toc[curIdx]).mParent;
			var idx;
			
			// Check if the current activity is in a forward only cluster
			if (cur.getParent() != null && cur.getParent().getControlForwardOnly())
			{
				idx = curIdx - 1;
				
				temp = toc[idx];
				while (temp.mParent == par)
				{
					temp.mIsSelectable = false;
					idx--;
					temp = toc[idx];
				}
			}
			
			// Check for Stop Forward Traversal Rules
			idx = curIdx;
			var blocked = false;
			
			while (idx < toc.length)
			{
				temp = toc[idx];
				if (temp.mParent == par)
				{
					if (!blocked)
					{
						var stopTrav = this.getActivity(temp.mID).getPreSeqRules();
						
						var result = null;
						if (stopTrav != null)
						{
							result = stopTrav.evaluate(RULE_TYPE_FORWARDBLOCK, this.getActivity(temp.mID), false);
						}
						
						// If the rule evaluation did not return null, the activity is blocked
						blocked = (result != null);
					}
					else 
					{
						temp.mIsSelectable = false;
					}
				}
				idx++;
			}
		}
		
		// Evaluate Stop Forward Traversal Rules -- this pass cooresponds to
		// Case #3 and #5 of the Choice Sequencing Request Subprocess.  In these
		// cases, we need to check if the target activity is forward in the 
		// Activity Tree relative to the common ancestor and cuurent activity
		if (toc != null && curIdx != -1)
		{
			var curParent = (toc[curIdx]).mParent;
			
			var idx = toc.length - 1;
			temp = toc[idx];
			
			// Walk backward from last available activity,
			// checking each until we get to a sibling of the current activity
			while (temp.mParent != -1 && temp.mParent != curParent)
			{
				temp = toc[temp.mParent];
				var stopTrav = this.getActivity(temp.mID).getPreSeqRules();
				
				var result = null;
				if (stopTrav != null)
				{
					result = stopTrav.evaluate(RULE_TYPE_FORWARDBLOCK, this.getActivity(temp.mID), false);
				}
			
				// If the rule evaluation did not return null, 
				// then all of its descendents are blocked
				if (result != null)
				{
					// The depth of the blocked activity
					var blocked = temp.mDepth; 
					
					for (var i = idx; i < toc.length; i++)
					{
						var tempTOC = toc[i];
						
						var checkDepth = tempTOC.mDepth;
						
						// Check to see if we are done blocking activities
						if (checkDepth <= blocked)
						{
							break;
						}
						
						// This activity must be a descendent
						tempTOC.mIsSelectable = false;
					}
				}
				
				idx--;
				temp = toc[idx];
			}
		}
		
		// Boundary condition -- if there is a TOC make sure all "selectable"
		// clusters actually flow into content
		for (var i = 0; i < toc.length; i++)
		{
			temp = toc[i];
			
			if (!temp.mLeaf)
			{
				var from = this.getActivity(temp.mID);
				
				// Confirm 'flow' is enabled from this cluster
				if (from.getControlModeFlow())
				{
					// Begin traversing the activity tree from the root
					var treeWalk = new Walk();
					treeWalk.at = from;
					
					var success = this.processFlow(FLOW_FORWARD, true, treeWalk, false);
					if (!success)
					{
						temp.mIsSelectable = false;
					}
				}
				else
				{
					// Cluster does not have flow == true
					temp.mIsSelectable = false;
				}
			}
		}
		//Historially here we have re-drawn depths of nodes that had certain conditions.  With the TOC now using only attributes to decide
		//what is drawn, this code should no longer be necesssary.
		
		//this collapses content with invisible nodes.  It is ok to mark an invisible node with the large negative depth
		for (var i = 0; i < toc.length; i++)
		{
			temp = toc[i];
			
			if (!temp.mIsVisible)
			{
				var parents = new Array();
				
				for (var j = i + 1; j < toc.length; j++)
				{
					temp = toc[j];
					
					if (temp.mParent == i)
					{
						temp.mDepth--;
						parents[parents.length] = j;
					}
					else
					{
						if (temp.mIsVisible) //was related to the depth
						{
							for (var k = 0; k < parents.length; k++ )
							{
								if ( parents[k]== temp.mParent )
									{
										var idx = k;
									}
								else
									{
										var idx = -1;
									}
								
							}
							
							if (idx != -1)
							{
								temp.mDepth--;
								parents[parents.length] = j;
							}
						}
					}
				}
			}
		}
		// this loop isolates a current activity so something is always drawn in bold
		for (var i = 0; i < toc.length; i++)
		{
			temp = toc[i];
			
			if (temp.mIsCurrent && !temp.mIsVisible)
			{
				var parent = temp.mParent; 
				while (parent != -1)
				{
					temp.mIsCurrent = false;
					temp = toc[parent];
					
					if (!temp.mIsVisible)
					{
						parent = temp.mParent;
					}
					else
					{
						parent = -1;
					}
				}
				
				temp.mIsCurrent = true;
				break;
			}
		}      
		
		//hide the entire tree if nothing is selectable other than the root - the root will always
		// be depth = 0 and nothing else should be
		var somethingIsSelectable = false;
		for ( var i = 0; i < toc.length; i++)
		{
			temp = toc[i];
			
			if (temp.mIsSelectable && temp.mIsVisible && temp.mDepth > 0)
			{
				somethingIsSelectable = true;
				break;
			}
		}
		if (!somethingIsSelectable)
		{
			for ( var i = 0; i < toc.length; i++)
			{
				temp = toc[i];
				temp.mIsVisible = false;
			}
		}
		
		return toc;
	},
	
	clearAttemptObjCompletionStatus: function (iActivityID, iObjID)
	{
		//Find the target activity
		var target = this.getActivity(iActivityID);
		
		//Make sure the activity exists
		if ( target != null)
		{
			//Make sure the activity is a valid target for status changes
			//		-- the active leaf current activity
			if ( target.getIsActive() )
			{
				//If the activity is a lead and is the current activity
				if ( !target.hasChildren(false) && 
						this.mSeqTree.getCurrentActivity() == target )
				{
						var statusChange = target.clearObjCompletionStatus(iObjID);
				}
			}
		}
	},
	
	setAttemptObjCompletionStatus: function (iActivityID, iObjID, iCompletion)
	{
		//Find the target activity
		var target = this.getActivity(iActivityID);
		
		//Make sure the activity exists
		if ( target != null)
		{
			//Make sure the activity is a valid target for status changes
			// -- the tracked active leaf current activity
			if ( target.getIsActive() )
			{
				//If the activity is a leaf and is the current activity
				if ( !target.hasChildren(false) &&
						this.mSeqTree.getCurrentActivity() == target )
				{
					target.setObjCompletionStatus(iObjID, iCompletion);
				}
			}
		}
	},
	
	clearAttemptObjProgressMeasure: function(iActivityID, iObjID)
	{
		//Find the target activity
		var target = this.getActivity(iActivityID);
		
		//Make sure the activity exists
		if (target != null)
		{
			//Make sure the activity is a valid target for status changes
			//		--the active lead current activity
			if ( target.getIsActive() )
			{
				//If the activity is a leaf and is the current activity
				if ( !target.hasChildren(false) &&
						this.mSeqTree.getCurrentActivity() == target )
				{
					var statusChange = target.clearObjProgressMeasure(iObjID);
				}
			}
		}
	},
	
	setAttemptObjProgressMeasure:  function(iActivityID, iObjID, iProgressMeasure)
	{
		//Find the target activity
		var target = this.getActivity(iActivityID);
		
		//Make sure the activity exists
		if ( target != null )
		{
			// Make sure the activity is a valid target for status changes
			//  -- the tracked active lead current activity
			if ( target.getIsActive() )
			{
				//If the activity is a leaf and is the current activity
				if ( !target.hasChildren(false) &&
						this.mSeqTree.getCurrentActivity() == target )
				{
					target.setObjProgressMeasure(iObjID, iProgressMeasure);
				}	
			}
		}
	},
	
	clearAttemptObjMaxScore: function (iActivityID, iObjID)
	{
		//Find the target activity
		var target = this.getActivity(iActivityID);
		
		//Make sure the activity exists
		if ( target != null )
		{
			//Make sure the activity is a valid target for status changes
			//	-- the active lead current activity
			if ( target.getIsActive() )
			{
				//If the activity is a leaf and is the current activity
				if (!target.hasChildren(false) &&
						this.mSeqTree.getCurrentActivity() == target )
				{
					var statusChange = target.clearObjMaxScore(iObjID);
				}
			}
		}
	},

	setAttemptObjMaxScore: function(iActivityID, iObjID, iMaxScore)
	{
		//Find the target activity
		var target = this.getActivity(iActivityID);
		
		//Make sure the activity exists
		if ( target != null )
		{
			//Make sure the activity is a valid target for status changes
			//	-- the tracked active leaf current activity
			if ( target.getIsActive() )
			{
				// If the activity is a leaf and is the current activity
				if ( !target.hasChildren(false) &&
						this.mSeqTree.getCurrentActivity() == target)
				{
					target.setObjMaxScore(iObjID, iMaxScore);
				}
			}
		}
	},
	
	clearAttemptObjMinScore: function(iActivityID, iObjID)
	{
		//Find the target activity
		var target = this.getActivity(iActivityID);
		
		//Make sure the activity exists
		if ( target != null )
		{
			//Make sure the activity is a valid target for status changes
			//	-- the tracked active leaf current activity
			if ( target.getIsActive() )
			{
				// If the activity is a leaf and is the current activity
				if ( !target.hasChildren(false) &&
						this.mSeqTree.getCurrentActivity() == target)
				{
					var statusChange = target.clearObjMinScore(iObjID);
				}
			}
		}
	},
	
	setAttemptObjMinScore: function(iActivityID, iObjID, iMinScore)
	{
		//Find the target activity
		var target = this.getActivity(iActivityID);
		
		//Make sure the activity exists
		if ( target != null )
		{
			//Make sure the activity is a valid target for status changes
			//	-- the tracked active leaf current activity
			if ( target.getIsActive() )
			{
				// If the activity is a leaf and is the current activity
				if ( !target.hasChildren(false) &&
						this.mSeqTree.getCurrentActivity() == target)
				{
					target.setObjMinScore(iObjID, iMinScore);
				}
			}
		}
	},
	
	clearAttemptObjRawScore: function(iActivityID, iObjID)
	{
		//Find the target activity
		var target = this.getActivity(iActivityID);
		
		//Make sure the activity exists
		if ( target != null )
		{
			//Make sure the activity is a valid target for status changes
			//	-- the tracked active leaf current activity
			if ( target.getIsActive() )
			{
				// If the activity is a leaf and is the current activity
				if ( !target.hasChildren(false) &&
						this.mSeqTree.getCurrentActivity() == target)
				{
					var statusChange = target.clearObjRawScore(iObjID);
				}
			}
		}
	},
	
	setAttemptObjRawScore: function(iActivityID, iObjID, iRawScore)
	{
		//Find the target activity
		var target = this.getActivity(iActivityID);
		
		//Make sure the activity exists
		if ( target != null )
		{
			//Make sure the activity is a valid target for status changes
			//	-- the tracked active leaf current activity
			if ( target.getIsActive() )
			{
				// If the activity is a leaf and is the current activity
				if ( !target.hasChildren(false) &&
						this.mSeqTree.getCurrentActivity() == target)
				{
					target.setObjRawScore(iObjID, iRawScore);
				}
			}
		}
	},
	
		setAttemptProgressMeasure: function(iID, iProMeasure)
	{
		//Find the target activity
		var target = this.getActivity(iID);
		
		//Make sure the activity exists
		if ( target != null )
		{
			//Make sure the activity is a valid target for status changes
			//	-- the tracked active leaf current activity
			if ( target.getIsActive() && target.getIsTracked() )
			{
				// If the activity is a leaf and is the current activity
				if ( !target.hasChildren(false) &&
						this.mSeqTree.getCurrentActivity() == target)
				{
					var statusChange = target.setProgressMeasure(iProMeasure);
					
					if ( statusChange )
					{
						this.validateRequests();
					}
				}
			}
		}
	}
};