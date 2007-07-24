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
	mReadStatus: null,
	mReadMeasure: null,
	mWriteStatus: null,
	mWriteMeasure: null,
	
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
		// If the objective is only satified my measure, don't set its status
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

			// Set any global objectives
			if (this.mWriteMeasure != null)
			{
				for (var i = 0; i < this.mWriteMeasure.length; i++)
				{
					adl_seq_utilities.setGlobalObjMeasure(this.mWriteMeasure[i], 
						this.mLearnerID, this.mScopeID,iMeasure);
				}
			}

			// If objective status is determined by measure, set it
			if (iAffectSatisfaction)
			{
				if (this.mMeasure >= this.mObj.mMinMeasure)
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
		// avaliable.

		if (this.mObj.mSatisfiedByMeasure)
		{
			done = true;
			var measure = null;
			
			// Is there a 'read' objective map?
			if (this.mReadMeasure != null)
			{
				measure = adl_seq_utilities.getGlobalObjMeasure(this.mReadMeasure, 
					this.mLearnerID, this.mScopeID);
			}
			
			if (this.mHasMeasure && measure == null)
			{
				if (this.mHasMeasure && !(iIsRetry && this.mDirtyObj))
				{
					measure = parseFloat(this.mMeasure);
				}
			}

			var val = -999.0;
			val = parseFloat(measure);

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

		if (!done)
		{
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
				}
			}

			if (this.mHasSatisfied && (!done || iUseLocal))
			{
				if (this.mHasSatisfied && !(iIsRetry && this.mDirtyObj))
				{
					if (this.mSatisfied)
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
			}
		}

		if (this.mHasMeasure && (!done || iUseLocal ))
		{
			if (this.mHasMeasure && !(iIsRetry && this.mDirtyObj))
			{
				ret = this.mMeasure;
			}
		}

		if (ret != TRACK_UNKNOWN &&
			this.mObj.mSatisfiedByMeasure && !(iIsRetry && this.mDirtyObj))
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
	}
}
