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
	JS port of ADL SeqObjectiveTracking.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	SeqObjectiveTracking.java by ADL Co-Lab, which is licensed as:
	
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

function SeqObjectiveTracking(iObj,iLearnerID,iScopeID)  
{
	if (iObj != null)
	{
		this.mObj = iObj;
		this.mLearnerID = iLearnerID;
		this.mScopeID = iScopeID;

		if (iObj.mMaps != null)
		{
			for (var i = 0; i < this.mObj.mMaps.length; i++)
			{
				var map = this.mObj.mMaps[i];
				
				if (map.mReadStatus)
				{
					this.mReadStatus = map.mGlobalObjID;
				}
			
				if (map.mReadMeasure)
				{
					this.mReadMeasure = map.mGlobalObjID;
				}
				
				if (map.mReadRawScore)
				{
					this.mReadRawScore = map.mGlobalObjID;
				}
				
				if (map.mReadMinScore)
				{
					this.mReadMinScore = map.mGlobalObjID;
				}
				
				if (map.mReadMaxScore)
				{
					this.mReadMaxScore = map.mGlobalObjID;
				}
				
				if (map.mReadCompletionStatus)
				{
					this.mReadCompletionStatus = map.mGlobalObjID;
				}
				
				if (map.mReadProgressMeasure)
				{
					this.mReadProgressMeasure = map.mGlobalObjID;
				}
				
				if (map.mWriteStatus)
				{
					if (this.mWriteStatus == null)
					{
						this.mWriteStatus = new Array();
					}
				
					// todo: check
					this.mWriteStatus[this.mWriteStatus.length] = map.mGlobalObjID;
				}
			
				if (map.mWriteMeasure)
				{
					if (this.mWriteMeasure == null)
					{
						this.mWriteMeasure = new Array();
					}
					
					// todo: check
					this.mWriteMeasure[this.mWriteMeasure.length] = map.mGlobalObjID;
				}
				
				if (map.mWriteRawScore)
				{
					if (this.mWriteRawScore == null)
					{
						this.mWriteRawScore = new Array();
					}
					
					// todo: check
					this.mWriteRawScore[this.mWriteRawScore.length] = map.mGlobalObjID;
				}
				
				if (map.mWriteMinScore)
				{
					if (this.mWriteMinScore == null)
					{
						this.mWriteMinScore = new Array();
					}
					
					// todo: check
					this.mWriteMinScore[this.mWriteMinScore.length] = map.mGlobalObjID;
				}
				
				if (map.mWriteMaxScore)
				{
					if (this.mWriteMaxScore == null)
					{
						this.mWriteMaxScore = new Array();
					}
					
					// todo: check
					this.mWriteMaxScore[this.mWriteMaxScore.length] = map.mGlobalObjID;
				}
				
				if (map.mWriteCompletionStatus)
				{
					if (this.mWriteCompletionStatus == null)
					{
						this.mWriteCompletionStatus = new Array();
					}
					
					// todo: check
					this.mWriteCompletionStatus[this.mWriteCompletionStatus.length] = map.mGlobalObjID;
				}
				
				if (map.mWriteProgressMeasure)
				{
					if (this.mWriteProgressMeasure == null)
					{
						this.mWriteProgressMeasure = new Array();
					}
					
					// todo: check
					this.mWriteProgressMeasure[this.mWriteProgressMeasure.length] = map.mGlobalObjID;
				}
			}
		}
	}
}
//this.SeqObjectiveTracking = SeqObjectiveTracking;
SeqObjectiveTracking.prototype = 
{
	mLearnerID: null,
	mScopeID: null,
	mObj: null,
	mDirtyObj: false,
	mSetOK: false,
	mHasSatisfied: false,
	mSatisfied: false,
	mHasMeasure: false,
	mMeasure: 0.0,
	mHasRawScore: false,
	mRawScore: 0,
	mHasMinScore: false,
	mMinScore: 0,
	mHasMaxScore: false,
	mMaxScore: 0,
	mHasProgressMeasure: false,
	mProgressMeasure: 0.0,
	mHasCompletionStatus: false,
	mCompletionStatus: "unknown",
	mReadStatus: null,
	mReadMeasure: null,
	mReadRawScore: null,
	mReadMinScore: null,
	mReadMaxScore: null,
	mReadCompletionStatus: null,
	mReadProgressMeasure: null,
	mWriteStatus: null,
	mWriteMeasure: null,
	mWriteRawScore: null,
	mWriteMinScore: null,
	mWriteMaxScore: null,
	mWriteCompletionStatus: null,
	mWriteProgressMeasure: null,
	
	// getter/setter
	getObjID: function () { return this.mObj.mObjID; },
	getObj: function () { return this.mObj; },
	setDirtyObj: function () { this.mDirtyObj = true; },
	
	forceObjStatus: function (iSatisfied)
	{
		if (iSatisfied == TRACK_UNKNOWN)
		{
			this.clearObjStatus();
		}
		else
		{
			// Set any global objectives
			if (this.mWriteStatus != null)
			{
				for (var i = 0; i < this.mWriteStatus.length; i++)
				{
					adl_seq_utilities.setGlobalObjSatisfied(this.mWriteStatus[i], 
						this.mLearnerID,this.mScopeID,iSatisfied);
				}
			}
			
			this.mHasSatisfied = true;
			if (iSatisfied == TRACK_SATISFIED)
			{
				this.mSatisfied = true;
			}
			else
			{
				this.mSatisfied = false;
			}
		}
	},
	
	// todo: optimization: can be merged with previous function
	setObjStatus: function (iSatisfied)
	{
		// If the objective is only satisfied my measure, don't set its status
		if (this.mObj.mSatisfiedByMeasure && !this.mSetOK)
		{
			// obj satisfied by measure
		}
		else
		{
			if (iSatisfied == TRACK_UNKNOWN)
			{
				this.clearObjStatus();
			}
			else
			{
				// Set any global objectives
				if (this.mWriteStatus != null)
				{
					for (var i = 0; i < this.mWriteStatus.length; i++)
					{
						adl_seq_utilities.setGlobalObjSatisfied(this.mWriteStatus[i], 
							this.mLearnerID, this.mScopeID, iSatisfied);
					}
				}
				this.mHasSatisfied = true;

				if (iSatisfied == TRACK_SATISFIED)
				{
					this.mSatisfied = true;
				}
				else
				{
					this.mSatisfied = false;
				}
			}
		}
	},
	
	// todo: optimization: can be merged with previous function
	clearObjStatus: function ()
	{
		var statusChange = false;

		if (this.mHasSatisfied)
		{
			if (this.mObj.mSatisfiedByMeasure)
			{
				// only satisfied by measure
			}
			else
			{
				if (this.mWriteStatus != null)
				{
					for (var i = 0; i < this.mWriteStatus.length; i++)
					{
						adl_seq_utilities.setGlobalObjSatisfied(this.mWriteStatus[i],
							this.mLearnerID, this.mScopeID, TRACK_UNKNOWN);
					}
				}
				// Clear the satisfaction status
				this.mHasSatisfied = false;
				statusChange = true;
			}
		}
		return statusChange;
	},
	
	clearObjMeasure: function (iAffectSatisfaction)
	{
		var statusChange = false;

		if (this.mHasMeasure)
		{
			if (this.mWriteMeasure != null)
			{
				for (var i = 0; i < this.mWriteMeasure.length; i++)
				{
					adl_seq_utilities.setGlobalObjMeasure(this.mWriteMeasure[i], 
						this.mLearnerID, this.mScopeID, TRACK_UNKNOWN);
				}
			}
			
			// Clear the measure
			this.mHasMeasure = false;
			
			// If measure is used to determine status, status is also cleared
			if (iAffectSatisfaction)
			{
				this.forceObjStatus(TRACK_UNKNOWN);
			}
		}
		return statusChange;	// ???
	},
	
	clearObjRawScore: function ()
	{
		var statusChange = false;

		if (this.mHasRawScore)
		{
			if (this.mWriteRawScore != null)
			{
				for (var i = 0; i < this.mWriteRawScore.length; i++)
				{
					adl_seq_utilities.setGlobalObjRawScore(this.mWriteRawScore[i], 
						this.mLearnerID, this.mScopeID, TRACK_UNKNOWN);
				}
			}
			
			// Clear the satisfaction status
			this.mHasRawScore = false;
			statusChange = true;	
		}
		return statusChange;
	},
	
	clearObjMinScore: function ()
	{
		var statusChange = false;

		if (this.mHasMinScore)
		{
			if (this.mWriteMeasure != null)
			{
				for (var i = 0; i < this.mWriteMinScore.length; i++)
				{
					adl_seq_utilities.setGlobalObjMinScore(this.mWriteMinScore[i], 
						this.mLearnerID, this.mScopeID, TRACK_UNKNOWN);
				}
			}
			
			// Clear the measure
			this.mHasMinScore = false;
			statusChange = true;
		}
		
		return statusChange;	
	},
	
	clearObjMaxScore: function ()
	{
		var statusChange = false;

		if (this.mHasMaxScore)
		{
			if (this.mWriteMaxScore != null)
			{
				for (var i = 0; i < this.mWriteMaxScore.length; i++)
				{
					adl_seq_utilities.setGlobalObjMaxScore(this.mWriteMaxScore[i], 
						this.mLearnerID, this.mScopeID, TRACK_UNKNOWN);
				}
			}
			
			// Clear the measure
			this.mHasMaxScore = false;
			statusChange = true;
		}
		
		return statusChange;	
	},
	
	clearObjProgressMeasure: function ()
	{
		var statusChange = false;

		if (this.mHasProgressMeasure)
		{
			if (this.mWriteProgressMeasure != null)
			{
				for (var i = 0; i < this.mWriteProgressMeasure.length; i++)
				{
					adl_seq_utilities.setGlobalObjProgressMeasure(this.mWriteProgressMeasure[i], 
						this.mLearnerID, this.mScopeID, TRACK_UNKNOWN);
				}
			}
			
			// Clear satisfaction status
			this.mHasProgressMeasure = false;
			statusChange = true;
		}
		
		return statusChange;	
	},
	
	clearObjCompletionStatus: function ()
	{
		var statusChange = false;

		if (this.mHasCompletionStatus)
		{
			if (this.mWriteCompletionStatus != null)
			{
				for (var i = 0; i < this.mWriteCompletionStatus.length; i++)
				{
					adl_seq_utilities.setGlobalObjCompletion(this.mWriteCompletionStatus[i], 
						this.mLearnerID, this.mScopeID, TRACK_UNKNOWN);
				}
			}
			
			// Clear satisfaction status
			this.mHasCompletionStatus = false;
			statusChange = true;
		}
		
		return statusChange;	
	},
	
	setObjMeasure: function (iMeasure, iAffectSatisfaction)             
	{
		
		// Validate the range of the measure
		if (iMeasure < -1.0 || iMeasure > 1.0)
		{
			// assume unknown
			this.clearObjMeasure(iAffectSatisfaction);
		}
		else
		{
			this.mHasMeasure = true;
			this.mMeasure = iMeasure;
			
			//Set any global objectives
			if ( this.mWriteMeasure != null)
			{
				for ( var i = 0; i < this.mWriteMeasure.length; i++ )
				{
					var objID = this.mWriteMeasure[i];
					
					adl_seq_utilities.setGlobalObjMeasure(objID, 
							this.mLearnerID, this.mScopeID, (iMeasure + ""));	
				}
			}
			
			// If objective status is determined by measure, set it
			if ( iAffectSatisfaction == true)
			{
				if ( this.mMeasure >= this.mObj.mMinMeasure)
				{
					this.forceObjStatus(TRACK_SATISFIED);
				}
				else
				{
					this.forceObjStatus(TRACK_NOTSATISFIED);
				}
			}
		}
	},
	
	// call getObjStatis(retry) or getObjStatis(retry, {iUseLocal: use_local})
	getObjStatus: function (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iUseLocal: false
			}, iOptions );
		var iUseLocal = iOptions.iUseLocal;
		
		var ret = TRACK_UNKNOWN;
		var done = false;

		// if satisfied by measure, ensure that it has been set if a measure is
		// available.

		if (this.mObj.mSatisfiedByMeasure==true)
		{			
			done = true;
			var measure = null;
			
			// Is there a 'read' objective map?
			if (this.mReadMeasure != null)
			{
				measure = adl_seq_utilities.getGlobalObjMeasure(this.mReadMeasure, 
					this.mLearnerID, this.mScopeID);
			}
			
			if (this.mHasMeasure==true && measure == null)
			{
				if (this.mHasMeasure==true && !(iIsRetry==true && this.mDirtyObj==true))
				{
					measure = parseFloat(this.mMeasure);
				}
			}

			var val = -999.0;
			
			if (measure!=null) {
				val = parseFloat(measure);
			}
				
			// Validate the range of the measure
			if ( val < -1.0 || val > 1.0 )
			{
				// invalid measure
			}
			else
			{
				if (val >= this.mObj.mMinMeasure)
				{
					ret = TRACK_SATISFIED;
				}
				else
				{
					ret = TRACK_NOTSATISFIED;
				}
			}
		}

		if (done==false)
		{
			var globalvalexists = false;
			
			// Is there a 'read' objective map?
			if (this.mReadStatus != null)
			{
				// Retrieve shared competency mastery status
				var status = adl_seq_utilities.getGlobalObjSatisfied(this.mReadStatus, 
					this.mLearnerID, this.mScopeID);
				if (status != null)
				{
					ret = status;
					done = true;
					globalvalexists = true;
				}
			}
			
			if (globalvalexists == false)
			{
				if (this.mHasSatisfied==true && (done==false || iUseLocal==true))
				{
					if (this.mHasSatisfied==true && !(iIsRetry==true && this.mDirtyObj==true))
					{
						if (this.mSatisfied==true)
						{
							ret = TRACK_SATISFIED;
						}
						else
						{
							ret = TRACK_NOTSATISFIED;
						}
					}
				}
			}
		}
		return ret;
	},
	
	// call getObjMeasure(retry) or getObjMeasure(retry, {iUseLocal: use_local})
	getObjMeasure: function (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iUseLocal: false
			}, iOptions );
		var iUseLocal = iOptions.iUseLocal;
		
		// Do not assume there is a valid measure
		var ret = TRACK_UNKNOWN;
		var done = false;
		var globalvalexists = false;

		// Is there a 'read' objective map?
		if (this.mReadMeasure != null)
		{
			var measure = adl_seq_utilities.getGlobalObjMeasure(this.mReadMeasure, 
				this.mLearnerID, this.mScopeID);

			// Always use shared measure if available
			if (measure != null)
			{
				ret = measure;
				done = true;
				globalvalexists = true;
			}
		}

		if ( globalvalexists == false )
		{
			if (this.mHasMeasure==true && (done==false || iUseLocal==true ))
			{
				if (this.mHasMeasure==true && !(iIsRetry==true && this.mDirtyObj==true))
				{
					ret = this.mMeasure;
				}
			}
		}

		if (ret != TRACK_UNKNOWN &&
			this.mObj.mSatisfiedByMeasure==true && !(iIsRetry==true && this.mDirtyObj==true))
		{
			var val = -999.0;
			val = ret;

			// Validate the range of the measure
			if ( val < -1.0 || val > 1.0 )
			{
				// invalid measure
			}
			else
			{
				this.mSetOK = true;
				if (val >= this.mObj.mMinMeasure)
				{
					this.setObjStatus(TRACK_SATISFIED);
				}
				else
				{
					this.setObjStatus(TRACK_NOTSATISFIED);
				}
				this.mSetOK = false;
			}
		}
		return ret;
	},
	
	getByMeasure: function ()
	{
		var byMeasure = false;
		
		if (this.mObj != null)
		{
			byMeasure = this.mObj.mSatisfiedByMeasure;
		}
		
		return byMeasure;
	},
	
	getObjRawScore: function (iIsRetry)
	{
		// Do not assume there is a valid raw score
		var ret = TRACK_UNKNOWN;
		var done = false;
		var globalvalexists = false;
		
		// Is there a 'read' objective map?
		if (this.mReadRawScore != null)
		{
			var rawscore = adl_seq_utilities.getGlobalObjRawScore(this.mReadRawScore, 
				this.mLearnerID, this.mScopeID);

			// Always use shared raw score if available
			if (rawscore != null)
			{
				ret = rawscore;
				done = true;
				globalvalexists = true;
			}
		}
		
		if ( globalvalexists == false )
		{
			if (this.mHasRawScore==true && done==false)
			{
				if (this.mHasRawScore==true && !(iIsRetry==true && this.mDirtyObj==true))
				{
					ret = this.mRawScore + '';
				}
			}
		}

		return ret;
		
	},
	
	setObjRawScore: function (iRawScore)
	{
		this.mHasRawScore = true;
		this.mRawScore = iRawScore;
		
		// Set any global objectives
		if (this.mWriteRawScore != null)
		{
				for ( var i = 0; i < this.mWriteRawScore.length; i++)
					{
						var objID = this.mWriteRawScore[i];
						
						adl_seq_utilities.setGlobalObjRawScore(objID, 
								this.mLearnerID,this.mScopeID, (iRawScore + ''));
					}
		}
	},
	
	getObjMinScore: function (iIsRetry)
	{
		// Do not assume there is a valid min score
		var ret = TRACK_UNKNOWN;
		var done = false;
		var globalvalexists = false;
		
		// Is there a 'read' objective map?
		if (this.mReadMinScore != null)
		{
			var minscore = adl_seq_utilities.getGlobalObjMinScore(this.mReadMinScore, 
				this.mLearnerID, this.mScopeID);

			// Always use shared raw score if available
			if (minscore != null)
			{
				ret = minscore;
				done = true;
				globalvalexists = true;
			}
		}
		
		if ( globalvalexists == false )
		{
			if (this.mHasMinScore==true && done==false)
			{
				if (this.mHasMinScore==true && !(iIsRetry==true && this.mDirtyObj==true))
				{
					ret = this.mMinScore + '';
				}
			}
		}

		return ret;
		
	},
	
	setObjMinScore: function (iMinScore)
	{
		this.mHasMinScore = true;
		this.mMinScore = iMinScore;
		
		// Set any global objectives
		if (this.mWriteMinScore != null)
		{
				for ( var i = 0; i < this.mWriteMinScore.length; i++)
					{
						var objID = this.mWriteMinScore[i];
						
						adl_seq_utilities.setGlobalObjMinScore(objID, 
								this.mLearnerID,this.mScopeID, (iMinScore + ''));
					}
		}
	},
	
	getObjMaxScore: function (iIsRetry)
	{
		// Do not assume there is a valid max score
		var ret = TRACK_UNKNOWN;
		var done = false;
		var globalvalexists = false;
		
		// Is there a 'read' objective map?
		if (this.mReadMaxScore != null)
		{
			var maxscore = adl_seq_utilities.getGlobalObjMaxScore(this.mReadMaxScore, 
				this.mLearnerID, this.mScopeID);

			// Always use shared raw score if available
			if (maxscore != null)
			{
				ret = maxscore;
				done = true;
				globalvalexists = true;
			}
		}
		
		if ( globalvalexists == false )
		{
			if (this.mHasMaxScore==true && done==false)
			{
				if (this.mHasMaxScore==true && !(iIsRetry==true && this.mDirtyObj==true))
				{
					ret = this.mMaxScore + '';
				}
			}
		}

		return ret;
		
	},
	
	setObjMaxScore: function (iMaxScore)
	{
		this.mHasMaxScore = true;
		this.mMaxScore = iMaxScore;
		
		// Set any global objectives
		if (this.mWriteMaxScore != null)
		{
				for ( var i = 0; i < this.mWriteMaxScore.length; i++)
					{
						var objID = this.mWriteMaxScore[i];
						
						adl_seq_utilities.setGlobalObjMaxScore(objID, 
								this.mLearnerID,this.mScopeID, (iMaxScore + ''));
					}
		}
	},
	
	getObjProgressMeasure: function (iDirtyProgress)
	{
		// Do not assume there is a valid progress measure
		var ret = TRACK_UNKNOWN;
		var done = false;
		var globalvalexists = false;
		
		// Is there a 'read' objective map?
		if (this.mReadProgressMeasure != null)
		{
			var progress = adl_seq_utilities.getGlobalObjProgressMeasure(this.mReadProgressMeasure, 
				this.mLearnerID, this.mScopeID);

			// Always use shared raw score if available
			if (progress != null)
			{
				if (progress != TRACK_UNKNOWN)
				{
					ret = progress;
					done = true;
					globalvalexists = true;
				}
			}
		}
		
		if ( globalvalexists == false )
		{
			if (this.mHasProgressMeasure==true && done==false)
			{
				if (this.mHasProgressMeasure==true && !(this.mDirtyObj==true))
				{
					ret = this.mProgressMeasure + '';
				}
			}
		}

		if (ret != TRACK_UNKNOWN && !(iDirtyProgress==true && this.mDirtyObj==true))
		{
			var valid = true;
			
			
			if (!valid || (ret < 0.0 || ret > 1.0 ))
			{
				ret = TRACK_UNKNOWN;
			}
		}
		return ret;
	},
	
	setObjProgressMeasure: function (iProgressMeasure)
	{
		// Validate the range of the measure
		if (iProgressMeasure < 0.0 || iProgressMeasure > 1.0)
		{
			this.clearObjProgressMeasure();
		}
		else
		{
			this.mHasProgressMeasure = true;
			this.mProgressMeasure = iProgressMeasure;
			// Set any global objectives
			if (this.mWriteProgressMeasure != null)
			{
				for (var i = 0; i < this.mWriteProgressMeasure.length; i++)
				{
					var objID = this.mWriteProgressMeasure[i];
					
					adl_seq_utilities.setGlobalObjProgressMeasure(objID, 
						this.mLearnerID, this.mScopeID, (iProgressMeasure+''));
				}
			}
		}
	},

	getObjCompletionStatus: function (iDirtyProgress)
	{
		var ret = TRACK_UNKNOWN;
		var done = false;
		
		var globalvalexists = false;
		// Is there a 'read' objective map?
		if (this.mReadCompletionStatus != null)
		{
			// Retrieve shared competency mastery status
			var status = adl_seq_utilities.getGlobalObjCompletion(this.mReadCompletionStatus, 
				this.mLearnerID, this.mScopeID);
			if (status != null)
			{
				ret = status;
				done = true;
				globalvalexists = true;
			}
		}
		
		if (globalvalexists == false)
		{
			if (this.mHasCompletionStatus==true && (done==false))
			{
				if (this.mHasCompletionStatus==true && !(this.mDirtyProgress==true))
				{	
					ret = this.mCompletionStatus;
				}
			}
		}
		return ret;
	},
	
	setObjCompletionStatus: function(iCompletionStatus)
	{
		this.mCompletionStatus = iCompletionStatus;
		this.mHasCompletionStatus = true;
		
		//Set any global objectives
		if ( this.mWriteCompletionStatus != null)
		{
			for (var i = 0; i< this.mWriteCompletionStatus.length; i++)
			{
				var objID = this.mWriteCompletionStatus[i];
				
				adl_seq_utilities.setGlobalObjCompletion(objID, 
						this.mLearnerID, this.mScopeID, iCompletionStatus);
				
			}
		}	
	}
}//end SeqObjectiveTracking
