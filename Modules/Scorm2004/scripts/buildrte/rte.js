// Build: 2014919005624 
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
	JS port of ADL ADLAuxiliaryResource.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	ADLAuxiliaryResource.java by ADL Co-Lab, which is licensed as:
	
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

function ADLAuxiliaryResource()
{
}

//this.ADLAuxiliaryResource = ADLAuxiliaryResource;
ADLAuxiliaryResource.prototype = 
{
	mTitle: null,
	mResourceID: null,
	mParameter: null
};
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
	JS port of ADL ADLDuration.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	ADLDuration.java by ADL Co-Lab, which is licensed as:
	
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

var UNKNOWN = -999;
var LT = -1;
var EQ = 0;
var GT = 1;
var FORMAT_SECONDS = 0;
var FORMAT_SCHEMA = 1;

// call ADLDuration() or ADLDuration({iFormat: format, iValue: value})
function ADLDuration(iOptions)  
{
	var iOptions = ilAugment({
		iFormat: FORMAT_SECONDS,
		iValue: 0
		}, iOptions );
	var iFormat = iOptions.iFormat;
	var iValue = iOptions.iValue;

	if (iValue == 0)
	{
		this.mDuration = 0;
	}
	
	var hours = null;
	var min = null;
	var sec = null;

	switch (iFormat)
	{
		case FORMAT_SECONDS:
		{
            var secs = 0.0;
			secs = iValue;
			//this.mDuration = secs * 1000.0;
			this.mDuration = parseFloat(secs);
			break;
		}
		case FORMAT_SCHEMA:
		{
			// todo: make this work for Y/M/D
            var locStart = iValue.indexOf('T');

            var loc = 0;
			if ( locStart != -1 )
			{

				locStart++;
				loc = iValue.indexOf("H", locStart);			
				if ( loc != -1 )
				{
					hours = iValue.substring(locStart, loc);
					this.mDuration = parseFloat(hours) * 3600;
					locStart = loc + 1;
				}

				loc = iValue.indexOf("M", locStart);
				if ( loc != -1 )
				{
					min = iValue.substring(locStart, loc);
					this.mDuration += parseFloat(min) * 60;
					locStart = loc + 1;
				}

				loc = iValue.indexOf("S", locStart);
				if ( loc != -1 )
				{
					sec = iValue.substring(locStart, loc);
					this.mDuration += parseFloat(sec);
				}
			}
			break;
		}
		default:
		{
			// Do nothing
		}
	}
}
//this.ADLDuration = ADLDuration;
ADLDuration.prototype = 
{
	mDuration: 0.0,				// milliseconds ?
	
	round: function (iValue)
	{
		iValue = iValue * 10;
		iValue = Math.round(iValue);
		iValue = iValue / 10;
		return iValue;
	},
	
	format: function (iFormat)
	{
		var out = null;
		var countHours = 0;
		var countMin = 0;
		var countSec = 0;
		var temp = 0;
		
		switch (iFormat)
		{
			case FORMAT_SECONDS:
			{
				//var sec = this.mDuration / 1000.0;
				var sec = this.mDuration;
				out = sec;
				break;
			}
			case FORMAT_SCHEMA:
			{
				out = "";
				countHours = 0;
				countMin = 0;
				countSec = 0;

				temp = this.mDuration;
				
				if (temp >= .1)
				{
					temp = this.round(temp);
					if ( temp >= 3600 )
					{
						countHours = (temp / 3600);
						temp %= 3600;
					}
					if ( temp > 60 )
					{
						countMin = (temp / 60);
						temp %= 60;
					}
					countSec = this.round(temp);
				}

				out = "PT";
				
				if ( countHours > 0 )
				{
					out = out + Math.floor(countHours);
					out +="H";
				}
				if ( countMin > 0 )
				{
					//out += Long.toString(countMin, 10);
					out = out + Math.floor(countMin);
					out +="M";
				}
				if ( countSec > 0 )
				{
					out = out + countSec;
					out +="S";
				}
				break;
			}
		}
		return out;
	},
	
	// add duration (in seconds)
	add: function (iDur)
	{
		this.mDuration += parseFloat(iDur.mDuration);
	},
	
	compare: function (iDur)
	{
		var relation = UNKNOWN;
		
		if (this.mDuration < iDur.mDuration)
		{
			relation = LT;
		}
		else if (this.mDuration == iDur.mDuration)
		{
			relation = EQ;
		}
		else if (this.mDuration > iDur.mDuration)
		{
			relation = GT;
		}
		return relation;
	}
};
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
	JS port of ADL ADLLaunch.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	SeqCondition.java by ADL Co-Lab, which is licensed as:
	
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

var LAUNCH_TOC = "_TOC_";
var LAUNCH_COURSECOMPLETE = "_COURSECOMPLETE_";
var LAUNCH_EXITSESSION = "_ENDSESSION_";
var LAUNCH_SEQ_BLOCKED = "_SEQBLOCKED_";
var LAUNCH_NOTHING = "_NOTHING_";
var LAUNCH_ERROR = "_ERROR_";
var LAUNCH_ERROR_DEADLOCK = "_DEADLOCK_";
var LAUNCH_ERROR_INVALIDNAVREQ = "_INVALIDNAVREQ_";
var LAUNCH_SEQ_ABANDON = "_SEQABANDON_";
var LAUNCH_SEQ_ABANDONALL = "_SEQABANDONALL_";

function ADLLaunch()
{
}

//this.ADLLaunch = ADLLaunch;
ADLLaunch.prototype = 
{
	mSeqNonContent: null,
	mEndSession: false,
	mActivityID: null,
	mResourceID: null,
	mStateID: null,
	mNumAttempt: 0,
	mDeliveryMode: "normal",
	mMaxTime: null,
	mNavState: null
};
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
	JS port of ADL ADLObjStatus.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	ADLObjStatus.java by ADL Co-Lab, which is licensed as:
	
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

function ADLObjStatus()  
{
}
//this.ADLObjStatus = ADLObjStatus;
ADLObjStatus.prototype = 
{
	mObjID: null,
	mHasMeasure: false,
	mMeasure: 1.0,
	mStatus: TRACK_UNKNOWN,
	mHasRawScore : false,
   	mHasMinScore : false,
   	mHasMaxScore : false,
	mHasProgressMeasure : false,
	mRawScore : 0,
   	mMinScore : 0,
   	mMaxScore : 0,
   	mCompletionStatus : "unknown",
	mProgressMeasure : 0
   };
// JS port of ADL ADLSeqUtilities.java
// FAKE: only the functions used in rollup procedure
function ADLSeqUtilities()  
{
	this.satisfied = new Object();
	this.measure = new Object();
	this.status = new Object();
	this.score_raw = new Object();
	this.score_min = new Object();
	this.score_max = new Object();
	this.completion_status = new Object();
	this.progress_measure = new Object();
	
}
ADLSeqUtilities.prototype = 
{
	// usage: adl_seq_utilities.setGlobalObjSatisfied(2, 10, "scope", true);
	setGlobalObjSatisfied: function (iObjID, iLearnerID, iScopeID, iSatisfied)
	{
	//	alert(iObjID+" ,  "+iLearnerID+ ", "+iScopeID+", "+iSatisfied);
		if(this.satisfied[iObjID] == null) this.satisfied[iObjID] = new Object();
		if(this.satisfied[iObjID][iLearnerID] == null) this.satisfied[iObjID][iLearnerID] = new Object();
		this.satisfied[iObjID][iLearnerID][iScopeID] = iSatisfied;
	},
	
	getGlobalObjSatisfied: function (iObjID, iLearnerID, iScopeID)
	{
		if (this.satisfied[iObjID] != null
			&& this.satisfied[iObjID][iLearnerID] != null
			&& this.satisfied[iObjID][iLearnerID][iScopeID] != null)
		{
			return this.satisfied[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},
	
	setGlobalObjMeasure: function (iObjID, iLearnerID,iScopeID, iMeasure)
	{
	   // alert(iObjID+" ,  "+iLearnerID+", "+iScopeID+", "+iMeasure);
		
		if(this.measure[iObjID] == null) this.measure[iObjID] = new Object();
		if(this.measure[iObjID][iLearnerID] == null) this.measure[iObjID][iLearnerID] = new Object();
		this.measure[iObjID][iLearnerID][iScopeID] = iMeasure;
	},
	
	getGlobalObjMeasure: function (iObjID, iLearnerID, iScopeID)
	{
	
		//alert("GET GLOBAL"+iObjID+""+iLearnerID+""+iScopeID);
		if (this.measure[iObjID] != null
			&& this.measure[iObjID][iLearnerID]
			&& this.measure[iObjID][iLearnerID][iScopeID])
		{
			return this.measure[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},
	
	setCourseStatus: function (iCourseID, iLearnerID, iSatisfied, iMeasure, iCompleted)
	{
		//fix for IE
		if (this.status==null) {
			this.status = new Object();
		}
		
		if(this.status[iCourseID] == null) this.status[iCourseID] = new Object();
			this.status[iCourseID][iLearnerID] =
			{satisfied: iSatisfied, measure: iMeasure, completed: iCompleted};
	},
	
	getGlobalObjRawScore: function (iObjID, iLearnerID, iScopeID)
	{
		if (this.score_raw[iObjID] != null
			&& this.score_raw[iObjID][iLearnerID]
			&& this.score_raw[iObjID][iLearnerID][iScopeID])
		{
			return this.score_raw[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},
	
	setGlobalObjRawScore: function (iObjID, iLearnerID,iScopeID, iScore_Raw)
	{
	   // alert(iObjID+" ,  "+iLearnerID+", "+iScopeID+", "+iScore_Raw);
		
		if(this.score_raw[iObjID] == null) this.score_raw[iObjID] = new Object();
		if(this.score_raw[iObjID][iLearnerID] == null) this.score_raw[iObjID][iLearnerID] = new Object();
		this.score_raw[iObjID][iLearnerID][iScopeID] = iScore_Raw;
	},
	
	getGlobalObjMinScore: function (iObjID, iLearnerID, iScopeID)
	{
		if (this.score_min[iObjID] != null
			&& this.score_min[iObjID][iLearnerID]
			&& this.score_min[iObjID][iLearnerID][iScopeID])
		{
			return this.score_min[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},
	
	setGlobalObjMinScore: function (iObjID, iLearnerID,iScopeID, iScore_Min)
	{
	   // alert(iObjID+" ,  "+iLearnerID+", "+iScopeID+", "+iScore_Min);
		
		if(this.score_min[iObjID] == null) this.score_min[iObjID] = new Object();
		if(this.score_min[iObjID][iLearnerID] == null) this.score_min[iObjID][iLearnerID] = new Object();
		this.score_min[iObjID][iLearnerID][iScopeID] = iScore_Min;
	},
	
	getGlobalObjMaxScore: function (iObjID, iLearnerID, iScopeID)
	{
		if (this.score_max[iObjID] != null
			&& this.score_max[iObjID][iLearnerID]
			&& this.score_max[iObjID][iLearnerID][iScopeID])
		{
			return this.score_max[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},
	
	setGlobalObjMaxScore: function (iObjID, iLearnerID,iScopeID, iScore_Max)
	{
	   // alert(iObjID+" ,  "+iLearnerID+", "+iScopeID+", "+iScore_Max);
		
		if(this.score_max[iObjID] == null) this.score_max[iObjID] = new Object();
		if(this.score_max[iObjID][iLearnerID] == null) this.score_max[iObjID][iLearnerID] = new Object();
		this.score_max[iObjID][iLearnerID][iScopeID] = iScore_Max;
	},
	
	getGlobalObjProgressMeasure: function (iObjID, iLearnerID, iScopeID)
	{
		if (this.progress_measure[iObjID] != null
			&& this.progress_measure[iObjID][iLearnerID]
			&& this.progress_measure[iObjID][iLearnerID][iScopeID])
		{
			return this.progress_measure[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},
	
	setGlobalObjProgressMeasure: function (iObjID, iLearnerID,iScopeID, iProgressMeasure)
	{
	   // alert(iObjID+" ,  "+iLearnerID+", "+iScopeID+", "+iScore_Raw);
		
		if(this.progress_measure[iObjID] == null) this.progress_measure[iObjID] = new Object();
		if(this.progress_measure[iObjID][iLearnerID] == null) this.progress_measure[iObjID][iLearnerID] = new Object();
		this.progress_measure[iObjID][iLearnerID][iScopeID] = iProgressMeasure;
	},
	
	getGlobalObjCompletion: function (iObjID, iLearnerID, iScopeID)
	{
		if(this.completion_status[iObjID] != null
			&& this.completion_status[iObjID][iLearnerID]
			&& this.completion_status[iObjID][iLearnerID][iScopeID])
		{
			return this.completion_status[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},

	setGlobalObjCompletion: function(iObjID, iLearnerID,iScopeID, iCompletionStatus)
	{
		if(this.completion_status[iObjID] == null) this.completion_status[iObjID] = new Object();
		if(this.completion_status[iObjID][iLearnerID] == null) this.completion_status[iObjID][iLearnerID] = new Object();
		this.completion_status[iObjID][iLearnerID][iScopeID] = iCompletionStatus;
	}
	
};
var adl_seq_utilities = new ADLSeqUtilities();
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
};/*
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
	JS port of ADL ADLTOC.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	ADLTOC.java by ADL Co-Lab, which is licensed as:
	
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

function ADLTOC()
{
}

//this.ADLTOC = ADLTOC;
ADLTOC.prototype = 
{
	mTitle: "",
	mDepth: -1,
	mCount: -1,
	mLeaf: false,
	mParent: -1,
	mInChoice: false,
	mIsEnabled: true,
	mIsVisible: true,
	mIsCurrent: false,
	mIsSelectable: true,
	mID: null
};
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
	JS port of ADL ADLTracking.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	ADLTracking.java by ADL Co-Lab, which is licensed as:
	
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

var TRACK_UNKNOWN = "unknown";
var TRACK_SATISFIED = "satisfied";
var TRACK_NOTSATISFIED = "notSatisfied";
var TRACK_COMPLETED = "completed";
var TRACK_INCOMPLETE = "incomplete";

//call ADLTracking(iObjs, iLearnerID, iScopeID) or 
// ADLTracking(iObjs, iLearnerID, iScopeID, {iThreshold: threshold, iWeight: weight})
function ADLTracking(iObjs, iLearnerID, iScopeID, iOptions)  
{
	if ((typeof(iOptions) != 'undefined' && iOptions != null))
		this.mProgressDeterminedByMeasure = true;
	
	var options = ilAugment({
		iThreshold: 1.0,
		iWeight: 1.0
		}, iOptions);
	
	this.mProgressThreshold = options.iThreshold;
	this.mProgressWeight = options.iWeight;

	if (iObjs != null)
	{
		for (var i = 0; i < iObjs.length; i++)
		{
			obj = iObjs[i];
			// Construct an objective for each local objective
			objTrack = new SeqObjectiveTracking(obj, iLearnerID, iScopeID);

			if (this.mObjectives == null)
			{
				this.mObjectives = new Object();
			}

			// todo check
			this.mObjectives[obj.mObjID] = objTrack;

			// Remember if this objective contributes to rollup
			if (obj.mContributesToRollup)
			{
				this.mPrimaryObj = obj.mObjID;
			}
		}
	}
	else
	{
		// All activities must have at least one objective and that objective
		// is the primary objective
		def = new SeqObjective();
		def.mContributesToRollup = true;
		objTrack = new SeqObjectiveTracking(def, iLearnerID, iScopeID);
		
		if (this.mObjectives == null)
		{
			this.mObjectives = new Object();
		}
		// todo check
		this.mObjectives[def.mObjID] = objTrack;
		this.mPrimaryObj = def.mObjID;
	}

}
//this.ADLTracking = ADLTracking;
ADLTracking.prototype = 
{
	mDirtyPro: false,
	mObjectives: null,
	mPrimaryObj: "_primary_",
	mProgressDeterminedByMeasure: false,
	mProgressThreshold: 1.0,
	mProgressWeight: 1.0,
	mAttemptAbDur: null,
	mAttemptExDur: null,
	mAttempt: 0,
	setDirtyObj: function ()
	{
		if (this.mObjectives != null)
		{
			for (var k in this.mObjectives)
			{
				obj = this.mObjectives[k];
				obj.setDirtyObj();
				this.mObjectives[k] = obj;
			}
		}
	},
   setCompletionStatus: function (iCompleted)
   {
      if ( ! this.mProgressDeterminedByMeasure )
      {
         this.mObjectives[this.mPrimaryObj].setObjCompletionStatus(iCompleted);
      }
   },
   getCompletionStatus: function (iUseCurrent)
   {
      var isDirty = (iUseCurrent)?false:this.mDirtyPro;
      var status = TRACK_UNKNOWN;
      var obj = this.mObjectives[this.mPrimaryObj];
      if ( this.mProgressDeterminedByMeasure )
      {
         status = obj.getObjProgressMeasure(isDirty);
      }
      else
      {
         status = obj.getObjCompletionStatus(isDirty);
      }
      return status;
   },
   setProgressMeasure: function (iProMeasure)
   {
      var obj = this.mObjectives[this.mPrimaryObj];
      obj.setObjProgressMeasure(iProMeasure);
      if ( this.mProgressDeterminedByMeasure && (iProMeasure >= 0 && iProMeasure <= 1) )
      {
         var completion = TRACK_UNKNOWN;
         if ( parseFloat(iProMeasure) >= parseFloat(this.mProgressThreshold) )
         {
            completion = TRACK_COMPLETED;
         }
         else
         {
            completion = TRACK_INCOMPLETE;
         }
         obj.setObjCompletionStatus(completion);
      }
      this.mObjectives[this.mPrimaryObj] = obj;
   },
   getProgressMeasure: function ()
   {
      return this.mObjectives[this.mPrimaryObj].getObjProgressMeasure(this.mDirtyPro);
   },
   getProgressDeterminedByMeasure: function ()
   {
      return this.mProgressDeterminedByMeasure;
   },
   getProgressMeasureWeight: function ()
   {
      return this.mProgressWeight;
   },
   setProgressMeasureWeight: function (iWeight)
   {
      if (iWeight >= 0 && iWeight <= 1)
      {
         this.mProgressWeight = iWeight;
      }
   },
   setProgressMeasureThreshold: function (ithresh)
   {
      if (ithresh >= 0 && ithresh <= 1)
      {
         this.mProgressThreshold = ithresh;
      }
   },
   hasProgressMeasure: function ()
   {
      return this.mObjectives[this.mPrimaryObj].getObjProgressMeasure(this.mDirtyPro) != TRACK_UNKNOWN;
   },
   clearProMeasure: function ()
   {
      this.mObjectives[this.mPrimaryObj].clearObjMeasure();
   },
   getProgressThreshold: function ()
   {
      return this.mProgressThreshold;
   }
};
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
	JS port of ADL ADLValidRequests.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	ADLValidRequests.java by ADL Co-Lab, which is licensed as:
	
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

function ADLValidRequests()
{
}

//this.ADLValidRequests = ADLValidRequests;
ADLValidRequests.prototype = 
{
	mStart: false,
	mResume: false,
	mContinue: false,
	mContinueExit: false,
	mPrevious: false,
	mSuspend: false,
	mChoice: null,
	mJump: null,
	mTOC: null
}
function ilAugment (oSelf, oOther)
{
	if (oSelf == null)
	{
		oSelf = {};
	}
	for (var i = 1; i < arguments.length; i++)
	{
		var o = arguments[i];
		if (typeof(o) != 'undefined' && o != null)
		{
			for (var j in o)
			{
				oSelf[j] = o[j];
			}
		}
	}
	return oSelf;
}

function clone(what)
{
	for (i in what)
	{
		if (typeof(what[i]) == 'object')
		{
			this[i] = new cloneObject(what[i]);
		}
		else
			this[i] = what[i];
	}
}

function index_of(haystack, needle, start)
{
	var index = -1;
	if (start == null)
	{
		start = 0;
	}
	
	for(var j=start; j < haystack.length; j++)
	{
		if (haystack[j] != null &&
			haystack[j] == needle)
		{
			index = j;
			break;
		}
	}
	return index;
}

/**
* Log a Message
*/
function sclog(mess, type)
{
	elm = document.getElementById("ilLogPre");
	if (elm) 
	{
		elm.innerHTML = elm.innerHTML + mess + '<br />';
	}
}

/**
* Clear the Log
*/
function sclogclear()
{
	elm = all("ilLogPre");
	if (elm) 
	{
		elm.innerHTML = '';
	}
}


/**
* Dump a variable
*/
function sclogdump(param, depth)
{
	if (!depth)
	{
		depth = 0;
	}
	
	var pre = '';
	for (var j=0; j < depth; j++)
	{
		pre = pre + '    ';
	}
	
	//sclog(typeof param);
	switch (typeof param)
	{
		case 'boolean':
			if(param) sclog(pre + "true (boolean)"); else sclog(pre + "false (boolean)");
			break;

		case 'number':
			sclog(pre + param + ' (number)');
			break;

		case 'string':
			sclog(pre + param + ' (string)');
			break;

		case 'object':
			if (param === null)
			{
				sclog(pre + 'null');
			}
			if (param instanceof Array) sclog(pre + '(Array) {');
			else if (param instanceof Object) sclog(pre + '(Object) {');
			for (var k in param)
			{
				//if (param.hasOwnProperty(k)) // hasOwnProperty requires Safari 1.2
				//{
					if (typeof param[k] != "function")
					{
						sclog(pre + '[' + k + '] => ');
						sclogdump(param[k], depth + 1);
					}
				//}
			}
			sclog(pre + '}');
			break;
			
		case 'function':
			// we do not show functions
			break;

		default:
			sclog(pre + "unknown: " + (typeof param));
			break;
		
	}
}

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
	JS port of ADL SeqActivity.java
	@author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
	
	This .js file is GPL licensed (see above) but based on
	SeqActivity.java by ADL Co-Lab, which is licensed as:
	
	ADL SCORM 2004 4th Edition Sample Run-Time Environment

The ADL SCORM 2004 4th Ed. Sample Run-Time Environment is licensed under
Creative Commons Attribution-Noncommercial-Share Alike 3.0 United States.

The Advanced Distributed Learning Initiative allows you to:
  *  Share - to copy, distribute and transmit the work.
  *  Remix - to adapt the work. 

Under the following conditions:
  *  Attribution. You must attribute the work in the manner specified by the author or
     licensor (but not in any way that suggests that they endorse you or your use
     of the work).
  *  Noncommercial. You may not use this work for commercial purposes. 
  *  Share Alike. If you alter, transform, or build upon this work, you may distribute
     the resulting work only under the same or similar license to this one. 

For any reuse or distribution, you must make clear to others the license terms of this work. 

Any of the above conditions can be waived if you get permission from the ADL Initiative. 
Nothing in this license impairs or restricts the author's moral rights.
*/

var TIMING_NEVER = "never";
var TIMING_ONCE = "once";
var TIMING_EACHNEW = "onEachNewAttempt";
var TER_EXITALL = "_EXITALL_";

function SeqActivity()  
{
}
//this.SeqActivity = SeqActivity;
SeqActivity.prototype = 
{
	mPreConditionRules: null,
	mPostConditionRules: null,
	mExitActionRules: null,
	mXML: null,
	mDepth: 0,
	mCount: -1,
	mLearnerID: "_NULL_",
	mScopeID: null,
	mActivityID: null,
	mResourceID: null,
	mStateID: null,
	mTitle: null,
	mIsVisible: true,
	mOrder: -1,
	mActiveOrder: -1,
	mSelected: true,
	mParent: null,
	mIsActive: false,
	mIsSuspended: false,
	mChildren: null,
	mActiveChildren: null,
	mDeliveryMode: "normal",
	mControl_choice: true,
	mControl_choiceExit: true,
	mControl_flow: false,
	mControl_forwardOnly: false,
	mConstrainChoice: false,
	mPreventActivation: false,
	mUseCurObj: true,
	mUseCurPro: true,
	mMaxAttemptControl: false,
	mMaxAttempt: 0,
	mAttemptAbDurControl: false,
	mAttemptAbDur: null,
	mAttemptExDurControl: false,
	mAttemptExDur: null,
	mActivityAbDurControl: false,
	mActivityAbDur: null,
	mActivityExDurControl: false,
	mActivityExDur: null,
	mBeginTimeControl: false,
	mBeginTime: null,
	mEndTimeControl: false,
	mEndTime: null,
	mAuxResources: null,
	mRollupRules: null,
	mActiveMeasure: true,
	mRequiredForSatisfied: ROLLUP_CONSIDER_ALWAYS,
	mRequiredForNotSatisfied: ROLLUP_CONSIDER_ALWAYS,
	mRequiredForCompleted: ROLLUP_CONSIDER_ALWAYS,
	mRequiredForIncomplete: ROLLUP_CONSIDER_ALWAYS,
	mObjectives: null,
	mObjMaps: null,
	mIsObjectiveRolledUp: true,
	mObjMeasureWeight: 1.0,
	mIsProgressRolledUp: true,
	mSelectTiming: "never",
	mSelectStatus: false,
	mSelectCount: 0,
	mSelection: false,
	mRandomTiming: "never",
	mReorder: false,
	mRandomized: false,
	mIsTracked: true,
	mContentSetsCompletion: false,
	mContentSetsObj: false,
	mCurTracking: null,
	mTracking: null,
	mNumAttempt: 0,
	mNumSCOAttempt: 0,
	mActivityAbDur_track: null,
	mActivityExDur_track: null,
	mPrimaryStatusSetBySCO: false,
	mProgressDeterminedByMeasure: false,
	mProgressThreshold: 1.0,
	mProgressWeight: 1.0,
	mPrimaryProgressSetBySCO: false,

	
	
	// getter/setter
	isPrimaryStatusSetBySCO: function () { return this.mPrimaryStatusSetBySCO; },
	primaryStatusSetBySCO: function (val) { this.mPrimaryStatusSetBySCO = val; },
	isPrimaryProgressSetBySCO: function () { return this.mPrimaryProgressSetBySCO; },
	primaryProgressSetBySCO: function (val) { this.mPrimaryProgressSetBySCO = val; },
	getControlModeChoice: function () { return this.mControl_choice; },
	setControlModeChoice: function (iChoice) { this.mControl_choice = iChoice; },
	getControlModeChoiceExit: function () { return this.mControl_choiceExit; },
	setControlModeChoiceExit: function (val) { this.mControl_choiceExit = val; },
	getControlModeFlow: function () { return this.mControl_flow; },
	setControlModeFlow: function (val) { this.mControl_flow = val; },
	getControlForwardOnly: function () { return this.mControl_forwardOnly; },
	setControlForwardOnly: function (val) { this.mControl_forwardOnly = val; },
	getConstrainChoice: function () { return this.mConstrainChoice; },
	setConstrainChoice: function (val) { this.mConstrainChoice = val; },
	getPreventActivation: function () { return this.mPreventActivation; },
	setPreventActivation: function (val) { this.mPreventActivation = val; },
	getUseCurObjective: function () { return this.mUseCurObj; },
	setUseCurObjective: function (val) { this.mUseCurObj = val; },
	getUseCurProgress: function () { return this.mUseCurPro; },
	setUseCurProgress: function (val) { this.mUseCurPro = val; },
	getPreSeqRules: function () { return this.mPreConditionRules; },
	setPreSeqRules: function (val) { this.mPreConditionRules = val; },
	getExitSeqRules: function () { return this.mExitActionRules; },
	setExitSeqRules: function (val) { this.mExitActionRules = val; },
	getPostSeqRules: function () { return this.mPostConditionRules; },
	setPostSeqRules: function (val) { this.mPostConditionRules = val; },
	getAttemptLimitControl: function () { return this.mMaxAttemptControl; },
	getAttemptLimit: function () { return this.mMaxAttempt; },
	getAttemptAbDurControl: function () { return this.mAttemptAbDurControl; },
	getAttemptExDurControl: function () { return this.mAttemptExDurControl; },
	getActivityAbDurControl: function () { return this.mActivityAbDurControl; },
	getActivityExDurControl: function () { return this.mActivityExDurControl; },
	getBeginTimeLimitControl: function () { return this.mBeginTimeControl; },
	getBeginTimeLimit: function () { return this.mBeginTime; },
	getEndTimeLimitControl: function () { return this.mEndTimeControl; },
	getEndTimeLimit: function () { return this.mEndTime; },
	getAuxResources: function () { return this.mAuxResources; },
	setAuxResources: function (val) { this.mAuxResources = val; },
	getRollupRules: function () { return this.mRollupRules; },
	setRollupRules: function (val) { this.mRollupRules = val; },
	getSatisfactionIfActive: function () { return this.mActiveMeasure; },
	setSatisfactionIfActive: function (val) { this.mActiveMeasure = val; },
	getRequiredForSatisfied: function () { return this.mRequiredForSatisfied; },
	setRequiredForSatisfied: function (val) { this.mRequiredForSatisfied = val; },
	getRequiredForNotSatisfied: function () { return this.mRequiredForNotSatisfied; },
	setRequiredForNotSatisfied: function (val) { this.mRequiredForNotSatisfied = val; },
	getRequiredForCompleted: function () { return this.mRequiredForCompleted; },
	setRequiredForCompleted: function (val) { this.mRequiredForCompleted = val; },
	getRequiredForIncomplete: function () { return this.mRequiredForIncomplete; },
	setRequiredForIncomplete: function (val) { this.mRequiredForIncomplete = val; },
	getObjectives: function () { return this.mObjectives; },
	getIsObjRolledUp: function () { return this.mIsObjectiveRolledUp; },
	setIsObjRolledUp: function (val) { this.mIsObjectiveRolledUp = val; },
	getObjMeasureWeight: function () { return this.mObjMeasureWeight; },
	setObjMeasureWeight: function (val) { this.mObjMeasureWeight = val; },
	getIsProgressRolledUp: function () { return this.mIsProgressRolledUp; },
	setIsProgressRolledUp: function (val) { this.mIsProgressRolledUp = val; },
	getSelectionTiming: function () { return this.mSelectTiming; },
	setSelectionTiming: function (val) { this.mSelectTiming = val; },
	getSelectStatus: function () { return this.mSelectStatus; },
	getRandomTiming: function () { return this.mRandomTiming; },
	getReorderChildren: function () { return this.mReorder; },
	setReorderChildren: function (val) { this.mReorder = val; },
	getIsTracked: function () { return this.mIsTracked; },
	setIsTracked: function (val) { this.mIsTracked = val; },
	getSetCompletion: function () { return this.mContentSetsCompletion; },
	setSetCompletion: function (val) { this.mContentSetsCompletion = val; },
	getSetObjective: function () { return this.mContentSetsObj; },
	setSetObjective: function (val) { this.mContentSetsObj = val; },
	getResourceID: function () { return this.mResourceID; },
	setResourceID: function (val) { this.mResourceID = val; },
	getDeliveryMode: function () { return this.mDeliveryMode; },
	getStateID: function () { return this.mStateID; },
	setStateID: function (val) { this.mStateID = val; },
	getID: function () { return this.mActivityID; },
	setID: function (val) { this.mActivityID = val; },
	getTitle: function () { return this.mTitle; },
	setTitle: function (val) { this.mTitle = val; },
	getXMLFragment: function () { return this.mXML; },
	setXMLFragment: function (val) { this.mXML = val; },
	getLearnerID: function () { return this.mLearnerID; },
	setLearnerID: function (val) { this.mLearnerID = val; },
	getIsSelected: function () { return this.mSelected; },
	setIsSelected: function (val) { this.mSelected = val; },
	getScopeID: function () { return this.mScopeID; },
	setScopeID: function (val) { this.mScopeID = val; },
	getIsVisible: function () { return this.mIsVisible; },
	setIsVisible: function (val) { this.mIsVisible = val; },
	getIsActive: function () { return this.mIsActive; },
	setIsActive: function (val) { this.mIsActive = val; },
	getIsSuspended: function () { return this.mIsSuspended; },
	setIsSuspended: function (val) { this.mIsSuspended = val; },
	getNumSCOAttempt: function () { return this.mNumSCOAttempt; },
	getParent: function () { return this.mParent; },
	setParent: function (val) { this.mParent = val; },
	getActiveOrder: function () { return this.mActiveOrder; },
	setActiveOrder: function (val) { this.mActiveOrder = val; },
	getDepth: function () { return this.mDepth; },
	setDepth: function (val) { this.mDepth = val; },
	getCount: function () { return this.mCount; },
	setCount: function (val) { this.mCount = val; },
	getSelection: function () { return this.mSelection; },
	setSelection: function (val) { this.mSelection = val; },
	getRandomized: function () { return this.mRandomized; },
	setRandomized: function (val) { this.mRandomized = val; },
	setOrder: function (val) { this.mOrder = val; },
	//: function () { return this.; },
	//: function (val) { this. = val; },
	
	setAttemptLimit: function (iMaxAttempt)
	{
		if (iMaxAttempt != null)
		{
			var value = iMaxAttempt;
			if (value >= 0)
			{
				this.mMaxAttemptControl = true;
				this.mMaxAttempt = value;
			}
			else
			{
				this.mMaxAttemptControl = false;
				this.mMaxAttempt = -1;
			}
		}
	},
	
	getAttemptAbDur: function ()
	{
		var dur = null;
	
		if (this.mAttemptAbDur != null)
		{
			dur = this.mAttemptAbDur.format(FORMAT_SCHEMA);
		}
		return dur;
	},
	
	setAttemptAbDur: function (iDur)
	{
		if (iDur != null)
		{
			this.mAttemptAbDurControl = true;
			this.mAttemptAbDur = new ADLDuration(
				{iFormat: FORMAT_SCHEMA, iValue: iDur});
		}
		else
		{
			this.mAttemptAbDurControl = false;
			this.mAttemptAbDur = null;
		}
	},
	
	getAttemptExDur: function ()
	{
		var dur = null;
		if (this.mAttemptExDur != null)
		{
			dur = this.mAttemptExDur.format(FORMAT_SCHEMA);
		}
		return dur;
	},
	
	setAttemptExDur: function (iDur)
	{
		if ( iDur != null )
		{
			this.mAttemptExDurControl = true;
			this.mAttemptExDur = new ADLDuration(
				{iFormat: FORMAT_SCHEMA, iValue: iDur});
		}
		else
		{
			this.mAttemptExDurControl = false;
		}
	},
	
	getActivityAbDur: function ()
	{
		var dur = null;
		if (this.mActivityAbDur != null)
		{
			dur = this.mActivityAbDur.format(FORMAT_SCHEMA);
		}
		return dur;
	},
   
	setActivityAbDur: function (iDur)
	{
		if ( iDur != null )
		{
			this.mActivityAbDurControl = true;
			this.mActivityAbDur = new ADLDuration(
				{iFormat: FORMAT_SCHEMA, iValue: iDur});
		}
		else
		{
			this.mActivityAbDurControl = false;
		}
	},
	
	getActivityExDur: function ()
	{
		var dur = null;
		if (this.mActivityExDur != null)
		{
			dur = this.mActivityExDur.format(FORMAT_SCHEMA);
		}
		return dur;
	},
	
	setActivityExDur: function (iDur)
	{
		if ( iDur != null )
		{
			this.mActivityExDurControl = true;
			this.mActivityExDur = new ADLDuration(
				{iFormat: FORMAT_SCHEMA, iValue: iDur});
		}
		else
		{
			this.mActivityExDurControl = false;
		}
	},
	
	setBeginTimeLimit: function (iTime)
	{
		if (iTime != null)
		{
			this.mBeginTimeControl = true;
			this.mBeginTime = iTime;
		}
		else
		{
			this.mBeginTimeControl = false;
		}
	},
	
	setEndTimeLimit: function (iTime)
	{
		if (iTime != null)
		{
			this.mEndTimeControl = true;
			this.mEndTime = iTime;
		}
		else
		{
			this.mEndTimeControl = false;
		}
	},
	
	setObjectives: function (iObjs)
	{

		if (mObjectives != null)
		{
			if (this.mOjbectives.length > 0 )
				{
					if ( iObjs != null)
						{
							for ( var i = 0; i < iObjs.length; i++)
								{
									var toadd = iObjs[i];
									var contained = false;
									
									for ( var j = 0; j < this.mObjectives.length; j++)
									{			
											if (this.mObjectives[j] == toadd)
											{
												contained = true;
												this.mObjectives[j].merge(toadd);
											}
											if ( !contained)
											{
												this.mObjectives[this.mObjectives.length] = iObjs[i];
											}
									}		
								}
						}
				}
		}
		
		else
		{
			this.mObjectives = iObjs;
		}
		
			if ( this.mObjectives != null)
			{
				for (var i = 0; i < this.mObjectives.length; i++)
				{
					obj = iObjs[i];
				
					if (obj.mMaps != null)
					{
						if (this.mObjMaps == null)
						{
							this.mObjMaps = new Object();	// Hashtable
						}
						this.mObjMaps[obj.mObjID] = obj.mMaps;
				}
			}
		}
	},
	
	setSelectionTiming: function (iTiming)
	{
		// Validate vocabulary
		if (!(iTiming == TIMING_NEVER || 
			iTiming == TIMING_ONCE ||
			iTiming == TIMING_EACHNEW))
		{
			this.mSelectTiming = TIMING_NEVER;
		}
		else
		{
			this.mSelectTiming = iTiming;
		}
   },
   
	getSelectCount: function ()
	{
		// If the number to be randomized is greater than the number of children
		// available, no  selection is required
		if (this.mChildren != null)
		{
			if (this.mSelectCount >= this.mChildren.length)
			{
				this.mSelectTiming = "never";
				this.mSelectCount = mChildren.length;
			}
		}
		else
		{
			// No children to select from; can't select
			this.mSelectStatus = false;
			this.mSelectCount = 0;
		}
		return this.mSelectCount;
	},
	
	setSelectCount: function (iCount)
	{
		if (iCount >= 0)
		{
			this.mSelectStatus = true;
			this.mSelectCount = iCount;
		}
		else
		{
			this.mSelectStatus = false;
		}
	},
	
	setRandomTiming: function (iTiming)
	{
		// Validate vocabulary
		if (!(iTiming == TIMING_NEVER || 
			iTiming == TIMING_ONCE ||
			iTiming == TIMING_EACHNEW ))
		{
			this.mSelectTiming = TIMING_NEVER;
		}
		else
		{
			this.mRandomTiming = iTiming;
		}
	},
	
	setDeliveryMode: function (iDeliveryMode)
	{
		// Test vocabulary
		if (iDeliveryMode == "browse" || iDeliveryMode == "review" ||
			iDeliveryMode == "normal")
		{
			this.mDeliveryMode = iDeliveryMode;
		}
		else
		{
			this.mDeliveryMode = "normal";
		}
	},
	
	getActivityAttempted: function ()
	{
		return(this.mNumAttempt != 0);
	},
	
	getAttemptCompleted: function (iIsRetry)
	{
		var progress = TRACK_UNKNOWN;
		if (this.mIsTracked)
		{
			this.initADLTracking();					
			
			if (!(this.mCurTracking.mDirtyPro==true && iIsRetry==true))
			{	
				if ( !this.mCurTracking.getProgressDeterminedByMeasure())
				{
					progress = this.mCurTracking.getCompletionStatus(this.mUseCurPro);
				}
				else
				{
					if ( this.mCurTracking.hasProgressMeasure())
					{
						var measure = this.mCurTracking.getProgressMeasure();
						progress = (parseFloat(measure) >= parseFloat(this.mCurTracking.getProgressThreshold()))?TRACK_COMPLETED:TRACK_INCOMPLETE;
						this.setProgressMeasure(measure);
					}
				}
			}
		}
		return(progress == TRACK_COMPLETED);
	},
	
	getPriObjAttemptCompleted: function (iIsRetry)
	{
		var progress = TRACK_UNKNOWN;
		if ( this.mIsTracked )
		{
			this.initADLTracking();
			if (!(this.mCurTracking.mDirtyPro==true && iIsRetry==true))
			{	
				if ( !this.mCurTracking.getProgressDeterminedByMeasure())
				{
					progress = this.mCurTracking.getCompletionStatus(this.mUseCurPro);
				}
				else
				{
					if ( this.mCurTracking.hasProgressMeasure())
					{
						var measure = this.mCurTracking.getProgressMeasure();
						progress = (parseFloat(measure) >= parseFloat(this.mCurTracking.getProgressThreshold()))?TRACK_COMPLETED:TRACK_INCOMPLETE;
						
					}
				}
			}
			
		}
		return (progress == TRACK_COMPLETED);
	},
	
	setProgress: function (iProgress)
	{
		var statusChange = false;
		
		if (this.mIsTracked==true)
		{
			// Validate state data
			if (iProgress == TRACK_UNKNOWN ||
				iProgress == TRACK_COMPLETED ||
				iProgress == TRACK_INCOMPLETE)
			{
				this.initADLTracking();
				
				var prev = this.mCurTracking.getCompletionStatus(this.mUseCurPro);
				this.mCurTracking.setCompletionStatus(iProgress);
				statusChange = !(prev == iProgress);
			}
		}
		return statusChange;
	},
	
	getProgressStatus: function (iIsRetry)
	{
		var status = false;
		if (this.mIsTracked==true)
		{
			if (this.mCurTracking != null)
			{
				if (!(this.mCurTracking.mDirtyPro==true && iIsRetry==true))
				{					
					status = (this.mCurTracking.getCompletionStatus(this.mUseCurPro) != TRACK_UNKNOWN);
				}
			}
		}
		return status;
	},
	
	// call getObjMeasureStatus(retry) or
	// getObjMeasureStatus(retry, {iObjID: obj_id, iUseLocal: use_local})
	getObjMeasureStatus: function (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null,
			iUseLocal: false
			}, iOptions);
		var iObjID = iOptions.iObjID;
		var iUseLocal = iOptions.iUseLocal;
		
		var status = false;

		if (this.mIsTracked==true)
		{
			this.initADLTracking();
   
			if (this.mCurTracking != null)
			{
				
				// A null objective indicates the primary objective
				if (iObjID == null)
				{
					iObjID = this.mCurTracking.mPrimaryObj;
				}
				obj = this.mCurTracking.mObjectives[iObjID];
				
				if (obj != null)
				{
					var result = null;
					result = obj.getObjMeasure(iIsRetry, iUseLocal);
					
					if (result != TRACK_UNKNOWN)
					{
						status = true;
					}
				}
			}
		}
		return status;
	},
	
	// call clearObjMeasure() or clearObjMeasure({iObjID: obj_id})
	clearObjMeasure: function (iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var statusChange = false;
		if (this.mCurTracking != null)
		{
			if (iObjID == null)
			{
				iObjID = this.mCurTracking.mPrimaryObj;
			}
			obj = this.mCurTracking.mObjectives[iObjID];

			if (obj != null)
			{
				objD = obj.getObj();
				var affectSatisfaction = objD.mSatisfiedByMeasure;
				
				if (affectSatisfaction)
				{
					affectSatisfaction = !objD.mContributesToRollup ||
						(this.mActiveMeasure || !this.mIsActive);
				}
				statusChange = obj.clearObjMeasure(affectSatisfaction);
			}
		}
		return statusChange;
	},
	
	// call setObjMeasure(measure) or setObjMeasure(measure, {iObjID: obj_id})
	setObjMeasure: function (iMeasure, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;
		var statusChange = false;

		if (this.mIsTracked)
		{
			if (this.mCurTracking != null)
			{
				if (iObjID == null)
				{
					iObjID = this.mCurTracking.mPrimaryObj;
				}
				obj = this.mCurTracking.mObjectives[iObjID];

				if (obj != null)
				{
					var prev = obj.getObjStatus(false);
					var objD = obj.getObj();
					var affectSatisfaction = objD.mSatisfiedByMeasure;
   
					if (affectSatisfaction)
					{
						affectSatisfaction = !objD.mContributesToRollup ||
							(this.mActiveMeasure || !this.mIsActive);
					}
					obj.setObjMeasure(iMeasure, affectSatisfaction);
					statusChange = (prev != obj.getObjStatus(false));
				}
			}
		}
		return statusChange;
	},
	
	getObjSatisfiedByMeasure: function ()
	{
		var byMeasure = false;
		
		if (this.mCurTracking != null)
		{
			var obj = this.mCurTracking.mObjectives[this.mCurTracking.mPrimaryObj];
			
			if (obj != null)
			{
				byMeasure = obj.getByMeasure();
			}
		}
		return byMeasure;
	},
	
	// call getObjMinMeasure() or getObjMinMeasure({iObjID: obj_id})
	getObjMinMeasure: function (iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var minMeasure = -1.0;
		
		if (iObjID == null)
		{
			iObjID = this.mCurTracking.mPrimaryObj;
		}
		if (this.mObjectives != null)
		{
			for (var i = 0; i < this.mObjectives.length; i++)
			{
				var obj = this.mObjectives[i];
				
				if (iObjID == obj.mObjID)
				{
					minMeasure = obj.mMinMeasure;
				}
			}
		}
		return minMeasure;
	},
	
	getObjMeasure: function (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var measure = 0.0;
		if (this.mIsTracked)
		{
			this.initADLTracking();
   
			// A null objective indicates the primary objective
			if (iObjID == null)
			{
				iObjID = this.mCurTracking.mPrimaryObj;
			}
			
			if (this.mCurTracking != null)
			{
				var obj = this.mCurTracking.mObjectives[iObjID];
   
				if (obj != null)
				{
					var result = null;
					result = obj.getObjMeasure(iIsRetry);
					
					if (result != TRACK_UNKNOWN)
					{
						measure = parseFloat(result);
					}
				}
			}
		}
		return measure;
	},
	
	triggerObjMeasure: function ()
	{
		var measure = 0.0;

		if (this.mIsTracked)
		{
			this.initADLTracking();
			
			if (this.mCurTracking != null)
			{
				var obj = this.mCurTracking.mObjectives[this.mCurTracking.mPrimaryObj];

				if (obj != null)
				{
					if (obj.getObj().mSatisfiedByMeasure)
					{
						var result = null;
						
						result = obj.getObjMeasure(false);
						if (result != TRACK_UNKNOWN)
						{
							measure = parseFloat(result);
							obj.setObjMeasure(measure, true);
						}
						else
						{
							obj.clearObjMeasure(true);
						}
					}
				}
			}
		}
	},
	
	// use this for getObjStatus(str iObjID, bool iIsRetry)
	getObjIdStatus: function (iObjID, iIsRetry)
	{
		var status = false;
		
		if ( this.mIsTracked==true )
		{
			this.initADLTracking();
			
			if ( iObjID == null )
			{
				status = this.getObjStatus(iIsRetry);
			}
			else if ( this.mCurTracking != null )
			{
				var obj = this.mCurTracking.mObjectives[iObjID];

				if (obj != null)
				{ 
					var objData = obj.getObj();
					
					if (objData.mSatisfiedByMeasure==false 
						|| this.mActiveMeasure==true 
						|| this.mIsActive==false)
					{              
						var result = null;
						result = obj.getObjStatus(iIsRetry);
						if (result != TRACK_UNKNOWN)
						{
							status = true;
						}
					}
				}
			}
		}
		
		return status;
	},
	
	// call getObjStatus(retry) or
	// getObjStatus(retry, {iUseLocal: use_local})
	getObjStatus: function (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null,
			iUseLocal: false
			}, iOptions);
		var iObjID = iOptions.iObjID;
		var iUseLocal = iOptions.iUseLocal;

		var status = false;
		if (this.mIsTracked==true)
		{
			if ( this.mPrimaryStatusSetBySCO )
			{
				status = true;
				var obj = this.mCurTracking.mObjectives[this.mCurTracking.mPrimaryObj];
				if ( obj != null )
				{
					var res = obj.getObjStatus(iIsRetry, iUseLocal);
					status = res != TRACK_UNKNOWN;
				}
			}
			else
			{
				this.initADLTracking();
				if (this.mCurTracking != null)
				{
					
					if (iObjID == null) {
						iObjID = this.mCurTracking.mPrimaryObj;
					}
					
					var obj = this.mCurTracking.mObjectives[iObjID];

					if (obj != null)
					{
						var objData = obj.getObj();
						
						if (objData.mSatisfiedByMeasure==false || this.mActiveMeasure==true ||
							this.mIsActive==false)
						{              
							var result = null;
							result = obj.getObjStatus(iIsRetry, iUseLocal);
							if (result != TRACK_UNKNOWN)
							{
								status = true;
							}
						}
					}
				}
			}
		}
		return status;
	},
	
	getObjSatValue: function ()
	{
		var status = "";
		var obj = this.mCurTracking.mObjectives[this.mCurTracking.mPrimaryObj];
		if ( obj != null )
		{
			status = obj.getObjStatus(false, true);
		}
		return status;
	},
	
	getProgressValue: function()
	{
		return this.mCurTracking.getCompletionStatus(false);
	},
	
	// call setObjSatisfied(status) or 
	// setObjSatisfied(status, {iObjID: obj_id})
	setObjSatisfied: function (iStatus, iOptions)  
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

 
		var statusChange = false;

		if (this.mIsTracked)
		{         
			if (this.mCurTracking != null)
			{
				if (iObjID == null)
				{
					iObjID = this.mCurTracking.mPrimaryObj;
				}

				var obj = this.mCurTracking.mObjectives[iObjID];
				
				if (obj != null)
				{
					// Validate desired value
					if (iStatus == TRACK_UNKNOWN ||
						iStatus == TRACK_SATISFIED ||
						iStatus == TRACK_NOTSATISFIED)
					{
					
						var result = obj.getObjStatus(false);
						obj.setObjStatus(iStatus);
						statusChange = (result != iStatus);
					}
				}
			}
		}
		return statusChange;
	},
	
	// call getObjSatisfied(is_retry) or
	// getObjSatisfied(is_retry, {iObjID: obj_id})
	getObjSatisfied: function (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var status = false;
		
		if (this.mIsTracked)
		{
			this.initADLTracking();
			
			if (this.mCurTracking != null)
			{
				
				if (iObjID == null)
				{
					iObjID = this.mCurTracking.mPrimaryObj;
				}

				var obj = this.mCurTracking.mObjectives[iObjID];
				
				if (obj != null)
				{
					var objData = obj.getObj();
					
					if (!objData.mSatisfiedByMeasure || this.mActiveMeasure ||
						!this.mIsActive)
					{
						var result = null;
						
						result = obj.getObjStatus(iIsRetry);
						if (result == TRACK_SATISFIED)
						{
							status = true;
						}
					}
				}
			}
		}
		return status;
	},
	
	setCurAttemptExDur: function (iDur)
	{
		if (this.mCurTracking != null)
		{
			this.mCurTracking.mAttemptAbDur = iDur;
		}
	},
	
	evaluateLimitConditions: function ()
	{
		// This is an implementation of UP.1
		var disabled = false;
		
		if (this.mCurTracking != null)
		{
			// Test max attempts
			if (this.mMaxAttemptControl)
			{
				if (this.mNumAttempt >= this.mMaxAttempt)
				{
					disabled = true;
				}
			}
		
			if (this.mActivityAbDurControl && !disabled)
			{
				if (this.mActivityAbDur.compare(this.mActivityAbDur_track) 
					!= LT)
				{
					disabled = true;
				}
			}
		
			if (this.mActivityExDurControl && !disabled)
			{
				if (this.mActivityExDur.compare(this.mActivityExDur_track) != 
					LT)
				{
					disabled = true;
				}
			}
		
			if (this.mAttemptAbDurControl && !disabled)
			{
				if (this.mActivityAbDur.compare(this.mCurTracking.mAttemptAbDur)
					!= LT)
				{
					disabled = true;
				}
			}
		
			if (this.mAttemptExDurControl && !disabled)
			{
				if (this.mActivityExDur.compare(this.mCurTracking.mAttemptExDur)
					!= LT)
				{
					disabled = true;
				}
			}
		
			if (this.mBeginTimeControl && !disabled)
			{
				// -+- TODO -+-
				if (false)
				{
					disabled = true;
				}
			}
		
			if (this.mEndTimeControl && !disabled)
			{
				// -+- TODO -+-
				if (false)
				{
					disabled = true;
				}
			}
		}
		return disabled;
	},
	
	incrementSCOAttempt: function ()
	{
		this.mNumSCOAttempt++;
	},
	
	incrementAttempt: function ()
	{
		// Store existing tracking information for historical purposes
		if (this.mCurTracking != null)
		{
			if (this.mTracking == null)
			{
				this.mTracking = new Array();
			}
			this.mTracking[this.mTracking.length] = this.mCurTracking;
		}
		
		var track;
		// Create a set of tracking information for the new attempt
		if ( this.mProgressDeterminedByMeasure == true )
		{
			track = new ADLTracking(this.mObjectives, this.mLearnerID, this.mScopeID,
									this.mProgressThreshold, this.mProgressWeight);
		}
		else
		{
			track = new ADLTracking(this.mObjectives, this.mLearnerID, this.mScopeID);
		}
		
		this.mNumAttempt++;
		track.mAttempt = this.mNumAttempt;
		track.setProgressMeasureThreshold(this.mProgressThreshold);
		track.setProgressMeasureWeight(this.mProgressWeight);
		
		this.mCurTracking = track;
		
		// If this is a cluster, check useCurrent flags
		if (this.mActiveChildren != null)
		{
			for (var i = 0; i < this.mActiveChildren.length; i++)
			{
				var temp = this.mActiveChildren[i];
				
				// Flag 'dirty' data if we are supposed to only use 'current attempt
				// status -- Set existing data to 'dirty'.  When a new attempt on a
				// a child activity begins, the new tracking information will be 
				// 'clean'.
				if (this.mUseCurObj==true)
				{
					temp.setDirtyObj();
				}
				
				if (this.mUseCurPro==true)
				{
					temp.setDirtyPro();
				}
				this.mActiveChildren[i] = temp;
			}
		}	
	},
	
	setDirtyObj: function ()
	{
		if (this.mCurTracking != null)
		{
			this.mCurTracking.setDirtyObj();
		}
		
		// If this is a cluster, check useCurrent flags
		if (this.mActiveChildren != null)
		{
			for (var i = 0; i < this.mActiveChildren.length; i++)
			{
				var temp = this.mActiveChildren[i];
				
				if (this.mUseCurObj==true)
				{
					temp.setDirtyObj();
				}
				this.mActiveChildren[i] = temp;
			}
		}
	},
	
	setDirtyPro: function ()
	{
		if (this.mCurTracking != null)
		{
			this.mCurTracking.mDirtyPro = true;
		}
		
		// If this is a cluster, check useCurrent flags
		if (this.mActiveChildren != null)
		{
			for (var i = 0; i < this.mActiveChildren.length; i++)
			{
				var temp = this.mActiveChildren[i];
				if (this.mUseCurPro==true)
				{
					temp.setDirtyPro();
				}
				this.mActiveChildren[i] = temp;
			}
		}
	},
	
	resetNumAttempt: function ()
	{
		// Clear all current and historical tracking information.
		this.mNumAttempt = 0;
		this.mCurTracking = null;
		this.mTracking = null;
	},
	
	getNumAttempt: function ()
	{
		var attempt = 0;
		if (this.mIsTracked)
		{
			attempt = this.mNumAttempt;
		}
		return attempt;
	},
	
	getObjIDs: function (iObjID, iRead)
	{
		// Attempt to find the ID associated with the rolledup objective
		if (iObjID == null)
		{
			if (this.mCurTracking != null)
			{
				iObjID = this.mCurTracking.mPrimaryObj;
			}
		}
		
		var objSet = new Array();
		var mapSet = new Array();
		
		if (this.mIsTracked)
		{
			if (this.mObjMaps != null)
			{
				mapSet = this.mObjMaps[iObjID];
				if (mapSet != null)
				{
					for (var i = 0; i < mapSet.length; i++)
					{
						var map = mapSet[i];
						
						if (!iRead && map.hasWriteMaps() )
						{
							if (objSet == null)
							{
								objSet = new Array();
							}
							
							objSet[objSet.length] = map.mGlobalObjID;
						}
						else if (iRead && map.hasReadMaps() )
						{
							if (objSet == null)
							{
								objSet = new Array();
							}
							objSet[objSet.length] = map.mGlobalObjID;
						}
					}
				}
			}
		}
		return objSet;
	},
	
	addChild: function (ioChild)
	{
		if (this.mChildren == null)
		{
			this.mChildren = new Array();
		}
		
		// To maintain consistency, adding a child activity will set the active
		// children to the set of all children.
		this.mActiveChildren = this.mChildren;
		
		this.mChildren[mChildren.length] = ioChild;
		
		// Tell the child who its parent is and its order in relation to its
		// siblings.
		ioChild.setOrder(this.mChildren.length - 1);
		ioChild.setActiveOrder(this.mChildren.length - 1);
		ioChild.setParent(this);
	},
	
	setChildren: function (ioChildren, iAll)
	{
		var walk = null; 
		
		if (iAll)
		{
			this.mChildren = ioChildren;
			this.mActiveChildren = ioChildren;
			
			for (var i = 0; i < ioChildren.length; i++)
			{
				walk = ioChildren[i];
				
				walk.setOrder(i);
				walk.setActiveOrder(i);
				walk.setParent(this);
				walk.setIsSelected(true);
			}
		}
		else
		{
			for (var i = 0; i < this.mChildren.length; i++)
			{
				walk = this.mChildren[i];
				walk.setIsSelected(false);
			}
			
			this.mActiveChildren = ioChildren;
			
			for (var i = 0; i < ioChildren.length; i++)
			{
				walk = ioChildren[i];
				walk.setActiveOrder(i);
				walk.setIsSelected(true);
				walk.setParent(this);
			}
		}
	},
	
	getChildren: function (iAll)
	{
		var result = null;
		
		if (iAll)
		{
			result = this.mChildren;
		}
		else
		{
			result = this.mActiveChildren;
		}
		
		return result;
	},
	
	hasChildren: function (iAll)
	{
		var result = false;
		
		if (iAll)
		{
			result = (this.mChildren != null);
		}
		else
		{
			result = (this.mActiveChildren != null);
		}
		
		return result;
	},
	
	getNextSibling: function (iAll)
	{
		var next = null;
		var target = -1;
		
		// Make sure this activity has a parent
		if (this.mParent != null)
		{
			if (iAll)
			{
				target = this.mOrder + 1; 
			}
			else
			{
				target = this.mActiveOrder + 1;
			}
			
			// Make sure there is a 'next' sibling
			if (target < this.mParent.getChildren(iAll).length)
			{
				var all = this.mParent.getChildren(iAll);
				next = all[target];
			}
		}
		return next;
	},
	
	getPrevSibling: function (iAll)
	{
		var prev = null;
		var target = -1;
		
		// Make sure this activity has a parent
		if (this.mParent != null)
		{
			if (iAll)
			{
				target = this.mOrder - 1;
			}
			else
			{
				target = this.mActiveOrder - 1;
			}
			
			// Make sure there is a 'next' sibling
			if (target >= 0)
			{
				var all = this.mParent.getChildren(iAll);
				prev = all[target];
			}
		}
		return prev;
	},
	
	getParentID: function ()
	{
		// If the parent is not null
		if (this.mParent != null)
		{
			return this.mParent.mActivityID;
		}
		
		return null;
	},
	
	getObjStatusSet: function ()
	{
		var objSet = null;
		
		this.initADLTracking();
		
		if (this.mCurTracking.mObjectives != null)
		{
			objSet = new Array();
			
			for (var key in this.mCurTracking.mObjectives)
			{
				// Only include objectives with IDs
				if (key != "_primary_")
				{
					var obj = this.mCurTracking.mObjectives[key];
					var objStatus = new ADLObjStatus();
					
					objStatus.mObjID = obj.getObjID();
					var measure = obj.getObjMeasure(false);
					
					objStatus.mHasMeasure = (measure != TRACK_UNKNOWN);
					
					if (objStatus.mHasMeasure)
					{
						objStatus.mMeasure = parseFloat(measure);
					}
					
					objStatus.mHasRawScore = obj.getObjRawScore(false) != TRACK_UNKNOWN;
					if ( objStatus.mHasRawScore ) objStatus.mRawScore = obj.getObjRawScore(false);
					
					objStatus.mHasMinScore = obj.getObjMinScore(false) != TRACK_UNKNOWN;
					if ( objStatus.mHasMinScore ) objStatus.mMinScore = obj.getObjMinScore(false);
					
					objStatus.mHasMaxScore = obj.getObjMaxScore(false) != TRACK_UNKNOWN;
					if ( objStatus.mHasMaxScore ) objStatus.mMaxScore = obj.getObjMaxScore(false);
										
					objStatus.mHasProgressMeasure = obj.getObjProgressMeasure(false) != TRACK_UNKNOWN;
					if ( objStatus.mHasProgressMeasure ) objStatus.mProgressMeasure = obj.getObjProgressMeasure(false);
					
					objStatus.mCompletionStatus = obj.getObjCompletionStatus(false);
					
					objStatus.mStatus = obj.getObjStatus(false);
					objSet[objSet.length] = objStatus;
				}
			}
		}
		
		if (objSet != null)
		{
			if (objSet.length == 0)
			{
				objSet = null;
			}
		}
		return objSet;
	},
	
	initADLTracking: function ()
	{
		if (this.mCurTracking == null)
		{
			var track;
			
			if ( this.mProgressDeterminedByMeasure == true)
			{
				track = new ADLTracking(this.mObjectives,this.mLearnerID,
				this.mScopeID, this.mProgressThreshold, this.mProgressWeight);
			}
			else
			{
				track = new ADLTracking(this.mObjectives, this.mLearnerID, this.mScopeID);
			}
		
			track.mAttempt = this.mNumAttempt;
			
			this.mCurTracking = track;
			
			// set progress measure stuff
			track.setProgressMeasureThreshold(this.mProgressThreshold);
			track.setProgressMeasureWeight(this.mProgressWeight);
		}
	},
	
	getProMeasure: function( iIsRetry )
	{
		var measure = -1.0;
		
		if( this.mIsTracked )
		{
			this.initADLTracking();
			
			if ( this.mCurTracking != null )
			{
				//make sure the current state is valid
				if ( !(this.mCurTracking.mDirtyPro && iIsRetry))
				{
					if ( this.mCurTracking.hasProgressMeasure() )
					{
						measure = this.mCurTracking.getProgressMeasure();
					}
				}
			}
		}
		return measure;
	},
	
	setProMeasure: function( iProMeasure )
	{
		if ( this.mIsTracked )
		{
			if ( this.mCurTracking != null)
			{
				this.mCurTracking.setProgressMeasure( iProMeasure );
			}
		}
	},
	
	clearProMeasure: function()
	{
		if ( this.mCurTracking != null)
		{
			if ( this.mCurTracking != null)
			{
				this.mCurTracking.clearProMeasure();
			}
		}
	},
	
	setProgressDeterminedByMeasure: function( iDeterminedByMeasure)
	{
		this.mProgressDeterminedByMeasure = iDeterminedByMeasure;
	},
	
	setProgressThreshold: function( iThreshold )
	{
		if ( iThreshold >= 0 && iThreshold <= 1.0)
		{
			this.mProgressThreshold = iThreshold;
		}
	},
	
	setProgressWeight: function ( iWeight )
	{
		if ( iWeight >= 0 && iWeight <= 1.0)
		{
			this.mProgressWeight = iWeight;
			
			if ( this.mCurTracking != null)
			{
				this.mCurTracking.setProgressMeasureWeight( iWeight);
			}
		}
	},
	
	clearObjStatus: function()
	{
		var statusChange = false;
		
		if ( this.mCurTracking != null)
		{
			var obj = this.mCurTracking.mObjectives[this.mCurTracking.mPrimaryObj];
			
			if ( obj != null )
			{
				statusChange = obj.clearObjStatus();
			}
		}
	},
	
	getObjProgressStatus: function( iObjID, iRollup )
	{
		var status = false;
		
		if ( this.mIsTracked )
		{
			this.initADLTracking();
			var primary = false;
			
			if ( this.mCurTracking != null )
			{
				primary = iObjID == null || iObjID == (this.mCurTracking.mPrimaryObj);
			}
			
			if ( primary == true)
			{
				status = this.getProgressStatus( iRollup );
			}
			else if ( this.mCurTracking != null)
			{
				var obj = this.mCurTracking.mObjectives[iObjID];
				
				if ( obj != null)
				{
					var result = obj.getObjCompletionStatus( iRollup );
					status = !(result == TRACK_UNKNOWN);
				}
			}
		}
		return status;
	},
	
	getObjAttemptCompleted: function( iObjID, iRollup)
	{
		var status = false;
		
		if ( this.mIsTracked )
		{
			this.initADLTracking();
			var primary = false;
			
			if ( this.mCurTracking != null )
			{
				primary = iObjID == null || iObjID == this.mCurTracking.mPrimaryObj;
			}
			if ( primary == true)
			{
				status = this.getPriObjAttemptCompleted(iRollup);
			}
			else if ( this.mCurTracking != null)
			{
				var obj = this.mCurTracking.mObjectives[iObjID];
				
				if ( obj != null)
				{
					var result = obj.getObjCompletionStatus( iRollup );
					status = result == TRACK_COMPLETED;
				}
			}
		}
		return status;
	},

	setObjRawScore: function (iObjID, iRawScore)
	{
      if ( this.mIsTracked )
      {
         if ( iObjID != null )
         {
            var obj = this.getObj(iObjID);
            if ( obj != null )
            {
               obj.setObjRawScore(iRawScore);
            }
         }

      }
   },

   clearObjRawScore: function (iObjID)
   {
      var statusChange = false;
      var obj;
      if (iObjID != null)
      {
         obj = this.mCurTracking.mObjectives[iObjID];
         if ( obj != null )
         {
            statusChange = obj.clearObjRawScore();
         }
      }
      return statusChange;
   },
   
   setObjMinScore: function (iObjID, iMinScore)
   {
      if ( this.mIsTracked )
      {
         if ( iObjID != null )
         {
            var obj = this.getObj(iObjID);
            if ( obj != null )
            {
               obj.setObjMinScore(iMinScore);
            }
         }
      }
   },
   
   clearObjMinScore: function (iObjID)
   {
      var statusChange = false;
      var obj;
      if (iObjID != null)
      {
         obj = this.mCurTracking.mObjectives[iObjID];
         if ( obj != null )
         {
            statusChange = obj.clearObjMinScore();
         }
      }
      return statusChange;
   },
   
   setObjMaxScore: function (iObjID, iMaxScore)
   {
      if ( this.mIsTracked )
      {
         if ( iObjID != null )
         {
            var obj = this.getObj(iObjID);
            if ( obj != null )
            {
               obj.setObjMaxScore(iMaxScore);
            }
         }
      }
   },
   
   clearObjMaxScore: function (iObjID)
   {
      var statusChange = false;
      var obj;
      if (iObjID != null)
      {
         obj = this.mCurTracking.mObjectives[iObjID];
         if ( obj != null )
         {
            statusChange = obj.clearObjMaxScore();
         }
      }
      return statusChange;
   },
   
   setObjProgressMeasure: function (iObjID, iProgressMeasure)
   {
      
		var obj;
		if ( iObjID != null )
		{
			obj = this.getObj(iObjID);
			if ( obj != null )
			{
				obj.setObjProgressMeasure(iProgressMeasure);
			}
		}
      
   },
   
   clearObjProgressMeasure: function (iObjID)
   {
      var statusChange = false;
      var obj;
      if (iObjID != null)
      {
         obj = this.mCurTracking.mObjectives[iObjID];
         if ( obj != null )
         {
            statusChange = obj.clearObjProgressMeasure();
         }
      }
      return statusChange;
   },
   
   setObjCompletionStatus: function (iObjID, iCompletionStatus)
   {
		if ( iObjID != null )
        {
			var obj = this.getObj(iObjID);
			if ( obj != null )
			{
			   obj.setObjCompletionStatus(iCompletionStatus);
			}
        }
      
   },
   
   clearObjCompletionStatus: function (iObjID)
   {
      var statusChange = false;
      var obj;
      if (this.mCurTracking != null)
      {
         obj = this.mCurTracking.mObjectives[iObjID];
         if ( obj != null )
         {
            statusChange = obj.clearObjCompletionStatus();
         }
      }
      return statusChange;
   },
   
   getObj: function (iObjID)
   {
		if ( this.mCurTracking == null )
		{
			this.mCurTracking = new ADLTracking(this.mObjectives, this.mLearnerID, this.mScopeID);
			track.mAttempt = this.mNumAttempt;
		}
		return this.mCurTracking.mObjectives[iObjID];
   },
   
	setProgressMeasure: function (iProgressMeasure)
	{
		var statusChange = false;
		if ( this.mIsTracked )
		{
			var proMeasure = iProgressMeasure;
			this.initADLTracking();
			var previousMeasure = (this.mCurTracking.hasProgressMeasure())?this.mCurTracking.getProgressMeasure():-1;
			this.mCurTracking.setProgressMeasure(proMeasure);
			statusChange = previousMeasure != proMeasure;
		}
		return statusChange;
	},
	
	getCompletedByMeasure: function ()
	{
		var compByMeasure = false;
		if ( this.mIsTracked )
		{
			this.initADLTracking();
			compByMeasure = this.mCurTracking.getProgressDeterminedByMeasure();
		}
		return compByMeasure;
	},
	
	getProMeasureWeight: function ()
	{
		var weight = 0;
		if ( this.mIsTracked )
		{
			this.initADLTracking();
			weight = this.mCurTracking.getProgressMeasureWeight();
		}
		return weight;
	},
	
	getProMeasureStatus: function (iIsRetry)
	{
		var valid = false;
		if ( this.mIsTracked )
		{
			this.initADLTracking();
			if ( ! (this.mCurTracking.mDirtyPro && iIsRetry) )
			{
				valid = this.mCurTracking.hasProgressMeasure();
			}
		}
		return valid;
	}
};
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
	JS port of ADL SeqActivityTree.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	SeqActivityTree.java by ADL Co-Lab, which is licensed as:
	
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

function SeqActivityTree(iCourseID, iLearnerID, iScopeID, iRoot)
{
	this.mCourseID = iCourseID;
	this.mLearnerID = iLearnerID;
	this.mScopeID = iScopeID;
	this.mRoot = iRoot;
	
	this.dsMap = new Object();
}

//this.SeqActivityTree = SeqActivityTree;
SeqActivityTree.prototype = 
{
	dataStoreLoc: null,
	mRoot: null,
	mValidReq: null,
	mLastLeaf: null,
	mScopeID: null,
	mCourseID: null,
	mLearnerID: null,
	mCurActivity: null,
	mFirstCandidate: null,
	mSuspendAll: null,
	mActivityMap: null,
	mObjSet: null,
	mObjMap: null,
	mObjScan: false,
	mDataScopedForAllAttempts: true,

	// trivial getter/setter
	getScopeID: function () { return this.mScopeID; },
	setRoot: function (iRoot) { this.mRoot = iRoot; },
	getRoot: function () { return this.mRoot; },
	setLastLeaf: function (iLastLeaf) { this.mLastLeaf = iLastLeaf; },
	getLastLeaf: function () { return this.mLastLeaf; },
	setValidRequests: function (iValidRequests) { this.mValidReq = iValidRequests; },
	getValidRequests: function () { return this.mValidReq; },
	getCurrentActivity: function () { return this.mCurActivity; },
	setCurrentActivity: function (iCurrent) {this.mCurActivity = iCurrent; },
	setFirstCandidate: function (iFirst) { this.mFirstCandidate = iFirst; },
	setSuspendAll: function (iSuspendTarget) { this.mSuspendAll = iSuspendTarget; },
	getSuspendAll: function () { return this.mSuspendAll; },
	getLearnerID: function () { return this.mLearnerID; },
	setCourseID: function (iCourseID) { this.mCourseID = iCourseID; },
	getCourseID: function () { return this.mCourseID; },

	setLearnerID: function (iLearnerID)
	{
		this.mLearnerID = iLearnerID;
	
		this.buildActivityMap();
		
		if (!(this.mActivityMap == null || iLearnerID == null))
		{
			for (var act in this.mActivityMap)
			{
				act.setLearnerID(iLearnerID);
			}
		}
	},
	
	setScopeID: function (iScopeID)
	{
		this.mScopeID = iScopeID;
		
		if (this.mScopeID != null)
		{
			this.buildActivityMap();
			
			if (this.mActivityMap != null)
			{
				for (var act in this.mActivityMap)
				{
					act.setScopeID(this.mScopeID);
				}
			}
		}
	},
	
	getFirstCandidate: function ()
	{
		if (this.mFirstCandidate == null)
		{
			return this.mCurActivity;
		}
		return this.mFirstCandidate;
	},
	
	getActivity: function (iActivityID)
	{
		// Make sure the Activity Map has been created
		if (this.mActivityMap == null)
		{
			this.buildActivityMap();
		}
		
		var temp = null;
		
		if (iActivityID != null)
		{
			temp = this.mActivityMap[iActivityID];
		}
		return temp;
	},

	getObjMap: function (iObjID)
	{
		var actSet = null;
		
		// If we haven't scanned the current tree for global objective IDs, do 
		// it now.
		if (!this.mObjScan)
		{
			this.scanObjectives();
			
			// Do not allow an empty set
			if (this.mObjMap != null)
			{
				if (this.mObjMap.length == 0)
				{
					this.mObjMap = null;
				}
			}
		}
		if (this.mObjMap != null)
		{
			actSet = this.mObjMap[iObjID];
		}
		return actSet;
	},

	getGlobalObjectives: function ()
	{
		// If we haven't scanned the current tree for global objective IDs, do 
		// it now.
		if (!this.mObjScan)
		{
			this.scanObjectives();
		}
		
		// Do not return an empty set
		if (this.mObjSet != null)
		{
			if (this.mObjSet.length == 0)
			{
				this.mObjSet = null;
			}
		}
		
		return this.mObjSet;
	},

	clearSessionState: function ()
	{
		this.mActivityMap = null;
	},

	setDepths: function ()
	{
		if (this.mRoot != null)
		{
			// Walk the activity tree, setting depths
			var walk =  this.mRoot;
			var depth = 0;
			
			var lookAt = new Array();
			var depths = new Array();
			
			while (walk != null)
			{
				// Check if the activity has children
				if (walk.hasChildren(true))
				{
					// Look at its children later
					lookAt[lookAt.length] = walk;
					depths[depths.length] = (depth + 1);
				}
				
				walk.setDepth(depth);
				
				// Walk the current level of the tree
				walk = walk.getNextSibling(true);
				
				// If there is not another sibling
				if (walk == null)
				{
					// Look one level deeper
					if (lookAt.length != 0)
					{
						// Remove the activity from the 'lookat' list
						walk = lookAt[0];
						//delete lookAt[0];
						lookAt.splice(0,1)
						// Remove the depth of the new activity from the 'depths' list
						depth = depths[0];
						//delete depths[0];
						depths.splice(0,1);
						// Start at the first child of the activity
						// todo: check
						temp = walk.getChildren(true);
						//rewrite using temp variable
						walk=temp[0];
					}
				}
			}
		}
	},

	setTreeCount: function ()
	{
		if (this.mRoot != null)
		{
			// Walk the activity tree, setting count
			var walk =  this.mRoot;
			var count = 0;
			
			var lookAt = new Array();
			
			while (walk != null)
			{
				count++;
				walk.setCount(count);
				
				// Save the activity for later
				if (walk.hasChildren(true))
				{
					lookAt[lookAt.length] = walk;
					walk = walk.getChildren(true)[0];
				}
				else
				{
					walk = walk.getNextSibling(true);
				}
				
				while (lookAt.length != 0 && walk == null)
				{
					// Remove the activity from the 'lookat' list
					walk = lookAt[0];
					//delete lookAt[0];
					lookAt.splice(0,1);
					walk = walk.getNextSibling(true);
				}
			}
		}
	},

	buildActivityMap: function ()
	{
		// Create or clear the activity map
		this.mActivityMap = new Object();
		if (this.mRoot != null)
		{
			this.addChildActivitiestoMap(this.mRoot);
		}
	},

	addChildActivitiestoMap: function (iNode)
	{
		// Make sure the node is not empty
		if (iNode != null)
		{
			var children = iNode.getChildren(true);
			var i = 0;
			
			// Add the current activity to the activity map
			this.mActivityMap[iNode.getID()] = iNode;
			
			// If the activity has children, add each child to the activity map
			if (children != null)
			{
				for (i = 0; i < children.length; i++)
				{
					this.addChildActivitiestoMap(children[i]);
				}
			}
		}
	},

	scanObjectives: function ()
	{
		// Walk the activity tree, recording all mapped global objectives
		var walk =  this.mRoot;
		var lookAt = new Array();
		
		while (walk != null)
		{
			// Check if the activity has children
			if (walk.hasChildren(true))
			{
				// Look at its children later
				lookAt[lookAt.length] = walk;
			}
			
			// Check if the activity references global objectives
			var objs = walk.getObjectives();
			
			if (objs != null)
			{
				for (var i = 0; i < objs.length; i++)
				{
					var obj = objs[i];
					
					if (obj.mMaps != null)
					{
						for (var j = 0; j < obj.mMaps.length; j++)
						{
							var map = obj.mMaps[j];
							var target = map.mGlobalObjID;
							
							// Make sure we haven't already added this objective
							if (this.mObjSet == null)
							{
								this.mObjSet = new Array();
								this.mObjSet[0] = target;
							}
							else
							{
								var found = false;
								
								for (var k = 0; k < this.mObjSet.length && !found; k++)
								{
									var id = this.mObjSet[k];
									found = (id == target);
								}
								if (!found)
								{
									this.mObjSet[this.mObjSet.length] = target;
								}
							}
							
							// If this is a 'read' objective add it to our obj map
							if ((map.mReadStatus || map.mReadMeasure || map.mReadCompletionStatus || map.mReadProgressMeasure)&& obj.mContributesToRollup)
							{
								if (this.mObjMap == null)
								{
									this.mObjMap = new Object();
								}
								
								var actList = this.mObjMap[target];
								
								if (actList == null)
								{
									actList = new Array();
								}
								
								actList[actList.length] = walk.getID();
								this.mObjMap[target] = actList;
							}
						}
					}
				}
			}
			
			// Walk the current level of the tree
			walk = walk.getNextSibling(true);
			
			// If there is not another sibling
			if (walk == null)
			{
				// Look one level deeper
				if (lookAt.length != 0)
				{
					// Remove the activity from the 'lookat' list
					walk = lookAt[0];
					//delete lookAt[0];
					lookAt.splice(0,1);
					// Start at the first child of the activity
					walk = walk.getChildren(true)[0];
				}
			}
		}
		this.mObjScan = true;
	},
	
	//Sets whether the data store collection persists for all attempts
	setDataScopedForAllAttempts: function (iAttributeValue) 
		{ 
			this.mDataScopedForAllAttempts = iAttributeValue; 
		},
	
	//Indicates if the data store collection is persisted for all attempts
	dataScopedForAllAttempts: function() 
		{ 
			return this.mDataScopedForAllAttempts; 
		},

	//Gets the activity map.  Returns Map of the activities
	getActivityMap: function()
	{
		if ( this.mActivityMap == null)
			{
				this.buildActivityMap();
			}
		return this.mActivityMap;
	}
};
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
	JS port of ADL SeqCondition.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	SeqCondition.java by ADL Co-Lab, which is licensed as:
	
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

var SATISFIED = "satisfied";
var OBJSTATUSKNOWN = "objectiveStatusKnown";
var OBJMEASUREKNOWN = "objectiveMeasureKnown";
var OBJMEASUREGRTHAN = "objectiveMeasureGreaterThan";
var OBJMEASURELSTHAN = "objectiveMeasureLessThan";
var COMPLETED = "completed";
var PROGRESSKNOWN = "activityProgressKnown";
var ATTEMPTED= "attempted";
var ATTEMPTSEXCEEDED = "attemptLimitExceeded";
var TIMELIMITEXCEEDED = "timeLimitExceeded";
var OUTSIDETIME = "outsideAvailableTimeRange";
var ALWAYS = "always";
var NEVER = "never";

function SeqCondition()
{
}

//this.SeqCondition = SeqCondition;
SeqCondition.prototype = 
{
	mCondition: null,
	mNot: false,
	mObjID: null,
	mThreshold: 0.0
};
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
	JS port of ADL SeqConditionSet.java
	@author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
	
	This .js file is GPL licensed (see above) but based on
	SeqConditionSet.java by ADL Co-Lab, which is licensed as:
	
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

var EVALUATE_UNKNOWN = 0;
var EVALUATE_TRUE = 1;
var EVALUATE_FALSE = -1;
var COMBINATION_ALL = "all";
var COMBINATION_ANY = "any";


function SeqConditionSet(iRollup)
{
	if (iRollup==true)
	{
		this.mRollup = iRollup;
	}
}

//this.SeqConditionSet = SeqConditionSet;
SeqConditionSet.prototype = 
{
	mCombination: null,
	mConditions: null,
	mRetry: false,
	mRollup: false,
	
	evaluate: function (iThisActivity, iOptions)
	{
		var iOptions = ilAugment({
			iIsRetry: this.mRetry
			}, iOptions );
		var iIsRetry = iOptions.iIsRetry;
		mRetry = iIsRetry;
		var result = EVALUATE_UNKNOWN;

		// Make sure we have a valid target activity  
		if (iThisActivity != null)
		{
			if (this.mConditions != null)
			{
				// Evaluate this rule's conditions
				if (this.mCombination == COMBINATION_ALL)
				{
					result = EVALUATE_TRUE;
					
					for (var i = 0; i < this.mConditions.length; i++)
					{
						var thisEval = this.evaluateCondition(i, iThisActivity);
						if (thisEval != EVALUATE_TRUE)
						{
							result = thisEval;
							// done with this evaluation
							break;
						}
					}
				}
				else if (this.mCombination == COMBINATION_ANY)
				{
					// Assume we have enough information to evaluate
					result = EVALUATE_FALSE;
					for (var i = 0; i < this.mConditions.length; i++)
					{
						var thisEval = this.evaluateCondition(i, iThisActivity);
					
						if (thisEval == EVALUATE_TRUE)
						{
							result = EVALUATE_TRUE;
							
							// done with this evaluation
							break;
						}
						else if (thisEval == EVALUATE_UNKNOWN)
						{
						// Something is missing...
							result = EVALUATE_UNKNOWN;
						}
					
					}
				}
			}
		}
		
		// Reset the 'retry' flag
		this.mRetry = false;
		return result;
	},
	
	evaluateCondition: function (iIndex, iTarget)
	{
		var result = EVALUATE_UNKNOWN;
		// Make sure this condition exists
		if (iIndex < this.mConditions.length)
		{
			var cond = this.mConditions[iIndex];
			
			// evaluate the current condtion
			if (cond.mCondition == ALWAYS)
			{
				result = EVALUATE_TRUE;
			}
			else if (cond.mCondition == NEVER)
			{
				result = EVALUATE_FALSE;
			}
			else if (cond.mCondition == SATISFIED)
			{

				if (iTarget.getObjIdStatus(cond.mObjID, this.mRollup))
				{
					result = (iTarget.getObjSatisfied(this.mRollup,{iObjID:cond.mObjID}))
						? EVALUATE_TRUE
						: EVALUATE_FALSE;
				}
				else
				{
					result = EVALUATE_UNKNOWN;
				}
			}
			else if (cond.mCondition == OBJSTATUSKNOWN)
			{
				result = iTarget.getObjIdStatus(cond.mObjID, this.mRollup)
					? EVALUATE_TRUE
					: EVALUATE_FALSE;
			}
			else if (cond.mCondition == OBJMEASUREKNOWN)
			{
				result = iTarget.getObjMeasureStatus(this.mRollup, {iObjID:cond.mObjID})
					? EVALUATE_TRUE
					: EVALUATE_FALSE;
			}
			else if (cond.mCondition == OBJMEASUREGRTHAN)
			{
				if (iTarget.getObjMeasureStatus(this.mRollup,{iObjID:cond.mObjID}))
				{
					result = (iTarget.getObjMeasure(this.mRollup, {iObjID: cond.mObjID}) >
						cond.mThreshold )
						? EVALUATE_TRUE
						: EVALUATE_FALSE;   
				}
				else
				{
					result = EVALUATE_UNKNOWN;
				}
			}
			else if (cond.mCondition == OBJMEASURELSTHAN)
			{
				if (iTarget.getObjMeasureStatus(this.mRollup,{iObjID:cond.mObjID}))
				{
					result = (iTarget.getObjMeasure(this.mRollup, {iObjID:cond.mObjID}) <
						cond.mThreshold)
						? EVALUATE_TRUE
						: EVALUATE_FALSE;
				}
				else
				{
					result = EVALUATE_UNKNOWN;
				}
			}
			else if (cond.mCondition == COMPLETED)
			{
				if (iTarget.getObjProgressStatus(cond.mObjID, this.mRollup))
				{
					result = iTarget.getObjAttemptCompleted(cond.mObjID, this.mRollup)
						? EVALUATE_TRUE
						: EVALUATE_FALSE;
				}
				else
				{
					result = EVALUATE_UNKNOWN;
				}
			}
			else if (cond.mCondition == PROGRESSKNOWN)
			{
				result = iTarget.getObjProgressStatus(cond.mObjID, this.mRollup)
					? EVALUATE_TRUE
					: EVALUATE_FALSE;
			}
			else if (cond.mCondition == ATTEMPTED)
			{
				result = iTarget.getActivityAttempted()
					? EVALUATE_TRUE
					: EVALUATE_FALSE;
			}
			else if (cond.mCondition == ATTEMPTSEXCEEDED)
			{
				if (iTarget.getAttemptLimitControl())
				{
					var maxAttempt = iTarget.getAttemptLimit();
					
					// Check if this limit condition exists
					if (maxAttempt >= 0)
					{
						result = (iTarget.getNumAttempt() >= maxAttempt)
							? EVALUATE_TRUE
							: EVALUATE_FALSE;
					}
				}
			}
			else if (cond.mCondition == TIMELIMITEXCEEDED)
			{
				// add later with other time tracking implementation...
				// -+- TODO -+-
			}
			else if (cond.mCondition == OUTSIDETIME)
			{
				
				// add later with other time tracking implementation...
				// -+- TODO -+-
			}
			
			// Account for condition operator
			if (cond.mNot && result != EVALUATE_UNKNOWN)
			{
				
				result = (result == EVALUATE_FALSE)
					? EVALUATE_TRUE
					: EVALUATE_FALSE;
					
			}
		}
		return result;
	}
};
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
	JS port of ADL SeqNavRequest.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	SeqNavRequest.java by ADL Co-Lab, which is licensed as:
	
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

var NAV_NONE = 0;
var NAV_START = 1;
var NAV_RESUMEALL = 2;
var NAV_CONTINUE = 3;
var NAV_PREVIOUS = 4;
var NAV_ABANDON = 5;
var NAV_ABANDONALL = 6;
var NAV_SUSPENDALL = 7;
var NAV_EXIT = 8;
var NAV_EXITALL = 9;
var NAV_JUMP = 10;
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
	JS port of ADL SeqObjective.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	SeqObjective.java by ADL Co-Lab, which is licensed as:
	
	ADL SCORM 2004 4th Edition Sample Run-Time Environment

The ADL SCORM 2004 4th Ed. Sample Run-Time Environment is licensed under
Creative Commons Attribution-Noncommercial-Share Alike 3.0 United States.

The Advanced Distributed Learning Initiative allows you to:
  *  Share - to copy, distribute and transmit the work.
  *  Remix - to adapt the work. 

Under the following conditions:
  *  Attribution. You must attribute the work in the manner specified by the author or
     licensor (but not in any way that suggests that they endorse you or your use
     of the work).
  *  Noncommercial. You may not use this work for commercial purposes. 
  *  Share Alike. If you alter, transform, or build upon this work, you may distribute
     the resulting work only under the same or similar license to this one. 

For any reuse or distribution, you must make clear to others the license terms of this work. 

Any of the above conditions can be waived if you get permission from the ADL Initiative. 
Nothing in this license impairs or restricts the author's moral rights.

*/

function SeqObjective()  
{
	this.mMaps = new Array();
}
//this.SeqObjective = SeqObjective;
SeqObjective.prototype = 
{
	mObjID: "_primary_",
	mSatisfiedByMeasure: false,
	mActiveMeasure: true,
	mMinMeasure: 1,
	mContributesToRollup: false,
	
	equals: function( iToCompare )	
	{
		if (iToCompare instanceof SeqObjective)
			{
				var other = iToCompare;
				return (this.mObjID == other.mObjID);	
			}
		return false;
	},
	
	hashCode: function ()
	{
		return (this.mObjID != null) ? (mObjID).hashCode() : 0;
	},
	
	merge: function ( toadd )
	{
		if ( this.equals(toadd) )
		{
			if (this.mMaps != null)
			{
				for ( var i = 0; i < toadd.mMaps.length; i++ )
				{
					var candidate = toadd.mMaps[i];
					var location = this.contains(candidate);
					if ( location > -1 )
					{
						var mymap = this.mMaps.splice(location, 1);
						this.mMaps.push(mymap.merge(candidate));
					}
					else
					{
						this.mMaps.push(candidate);
					}
				}
			}
			else
			{
				this.mMaps = toadd.mMaps;
			}
		}
	},
	
	contains: function (candidate)
	{
		for ( var i = 0; i < this.mMaps.length; i++ )
		{
			if ( this.mMaps[i].equals(candidate) ) return i;
		}
		return -1;
	}
};
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
	JS port of ADL SeqObjectiveMap.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	SeqObjectiveMap.java by ADL Co-Lab, which is licensed as:
	
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

function SeqObjectiveMap()  
{
}
SeqObjectiveMap.prototype = 
{
	mGlobalObjID: null,
	mReadStatus: true,
	mReadMeasure: true,
	mReadRawScore: true,
	mReadMinScore: true,
	mReadMaxScore: true,
	mReadCompletionStatus: true,
	mReadProgressMeasure: true,
	mWriteStatus: false,
	mWriteMeasure: false,
	mWriteRawScore: false,
	mWriteMinScore: false,
	mWriteMaxScore: false,
	mWriteCompletionStatus: false,
	mWriteProgressMeasure: false,
	
	hasWriteMaps: function () 
	{ 
		return (this.mWriteCompletionStatus || this.mWriteMaxScore || this.mWriteMeasure ||
				this.mWriteMinScore || this.mWriteProgressMeasure || this.mWriteRawScore ||
				this.mWriteStatus);
	},

	hasReadMaps: function()
	{
		return (this.mReadCompletionStatus || this.mReadMaxScore || this.mReadMeasure ||
				this.mReadMinScore || this.mReadProgressMeasure || this.ReadRawScore ||
				this.mReadStatus);
	},
	
	equals: function( iToCompare )	
	{
		if (iToCompare instanceof SeqObjectiveMap)
			{
				var other = iToCompare;
				return this.mGlobalObjID == other.mGlobalObjID ;
				
			}
		return false;
	},
	
	hashCode: function ()
	{
		return (this.mGlobalObjID != null) ? (mGlobalObjID).hashCode() : 0;
	},
	
	merge: function ( candidate )
	{
		var ret = new SeqObjectiveMap();
		if (this.mGlobalObjID == candidate.mGlobalObjID)
		{
			ret.mReadStatus = this.mReadStatus || candidate.mReadStatus;
			ret.mReadMeasure = this.mReadMesure || candidate.mReadMeasure;
			ret.mReadRawScore = this.mReadRawScore || candidate.mReadRawScore;
			ret.mReadMinScore = this.mReadMinScore || candidate.mReadMinScore;
			ret.mReadMaxScore = this.mReadMaxScore || candidate.mReadMaxScore;
			ret.mReadCompletionStatus = this.mReadCompletionStatus || candidate.mReadCompletionStatus;
			ret.mReadProgressMeasure = this.mReadProgressMeasure || candidate.mReadProgressMeasure;
			ret.mWriteStatus = this.mWriteStatus || candidate.mWriteStatus;
			ret.mWriteMeasure = this.mWriteMeasure || candidate.mWriteMeasure;
			ret.mWriteRawScore = this.mWriteRawScore || candidate.mWriteRawScore;
			ret.mWriteMinScore = this.mWriteMinScore || candidate.mWriteMinScore;
			ret.mWriteMaxScore = this.mWriteMaxScore || candidate.mWriteMaxScore;
			ret.mWriteCompletionStatus = this.mWriteCompletionStatus || candidate.mWriteCompletionStatus;
			ret.mWriteProgressMeasure = this.mWriteProgressMeasure || candidate.mWriteProgressMeasure;		
		}
		return ret;
	}
};
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
	JS port of ADL SeqRollupRule.java
	@author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
	
	This .js file is GPL licensed (see above) but based on
	SeqRollupRule.java by ADL Co-Lab, which is licensed as:
	
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

var ROLLUP_ACTION_NOCHANGE = 0;
var ROLLUP_ACTION_SATISFIED = 1;
var ROLLUP_ACTION_NOTSATISFIED = 2;
var ROLLUP_ACTION_COMPLETED = 3;
var ROLLUP_ACTION_INCOMPLETE = 4;
var ROLLUP_CONSIDER_ALWAYS = "always";
var ROLLUP_CONSIDER_ATTEMPTED = "ifAttempted";
var ROLLUP_CONSIDER_NOTSKIPPED = "ifNotSkipped";
var ROLLUP_CONSIDER_NOTSUSPENDED = "ifNotSuspended";
var ROLLUP_SET_ALL = "all";
var ROLLUP_SET_ANY = "any";
var ROLLUP_SET_NONE = "none";
var ROLLUP_SET_ATLEASTCOUNT = "atLeastCount";
var ROLLUP_SET_ATLEASTPERCENT = "atLeastPercent";


function SeqRollupRule()  
{
}
//this.SeqRollupRule = SeqRollupRule;
SeqRollupRule.prototype = 
{
	mAction: ROLLUP_ACTION_SATISFIED,
	mChildActivitySet: ROLLUP_SET_ALL,
	mMinCount: 0,
	mMinPercent: 0.0,
	mConditions: null,
	
	setRollupAction: function (iAction)
	{
		if (iAction == "satisfied")
		{
			this.mAction = ROLLUP_ACTION_SATISFIED;
		}
		else if (iAction == "notSatisfied")
		{
			this.mAction = ROLLUP_ACTION_NOTSATISFIED;
		}
		else if (iAction == "completed")
		{
			this.mAction = ROLLUP_ACTION_COMPLETED;
		}
		else if (iAction == "incomplete")
		{
			this.mAction = ROLLUP_ACTION_INCOMPLETE;
		}
	},
	
	evaluate: function (iChildren)
	{
		// Evaluate 'this' rollup rule, using the activity's children
		var result = false;
	
		if (this.mChildActivitySet == ROLLUP_SET_ALL)
		{
			result = this.evaluateAll(iChildren);
		}
		else if (this.mChildActivitySet == ROLLUP_SET_ANY)
		{
			result = this.evaluateAny(iChildren);
		}
		else if (this.mChildActivitySet == ROLLUP_SET_NONE)
		{
			result = this.evaluateNone(iChildren);
		}
		else if (this.mChildActivitySet == ROLLUP_SET_ATLEASTCOUNT)
		{
			result = this.evaluateMinCount(iChildren);
		}
		else if (this.mChildActivitySet == ROLLUP_SET_ATLEASTPERCENT)
		{
			result = this.evaluateMinPercent(iChildren);
		}
		
		var action = ROLLUP_ACTION_NOCHANGE;
		
		if (result)
		{
			action = this.mAction;
		}

		return action;
	},
	
	isIncluded: function (iActivity)
	{
		// Assume all children are included in rollup
		var include = true;

		// Make sure the activity is tracked
		if (iActivity.getIsTracked())
		{

			// Make sure the delivery mode is 'Normal'
			if (iActivity.getDeliveryMode() == "normal")
			{
				
				if (this.mAction == ROLLUP_ACTION_SATISFIED ||
					this.mAction == ROLLUP_ACTION_NOTSATISFIED )
				{
					include = iActivity.getIsObjRolledUp();
				}
				else if (this.mAction == ROLLUP_ACTION_COMPLETED ||
					this.mAction == ROLLUP_ACTION_INCOMPLETE )
				{
					include = iActivity.getIsProgressRolledUp();
					
				}
			}
			else
			{
				include = false;
			}
		}
		else
		{
			include = false;
		}

		// Check 'Is Required For' SCORM Sequencing extensions
		if (include)
		{
			var consider = null;
			
			switch (this.mAction)
			{
				case ROLLUP_ACTION_SATISFIED :
					consider = iActivity.getRequiredForSatisfied();
					break;
			
				case ROLLUP_ACTION_NOTSATISFIED :
					consider = iActivity.getRequiredForNotSatisfied();
					break;
			
				case ROLLUP_ACTION_COMPLETED :
					consider = iActivity.getRequiredForCompleted();
					break;
			
				case ROLLUP_ACTION_INCOMPLETE :
					consider = iActivity.getRequiredForIncomplete();
					break;
			
				default:
					include = false;
			}
			
			if (consider != null)
			{
				if (consider == ROLLUP_CONSIDER_NOTSUSPENDED)
				{
					if (iActivity.getActivityAttempted() && 
						iActivity.getIsSuspended())
					{
						include = false;
					}
				
				}
				else if (consider ==ROLLUP_CONSIDER_ATTEMPTED)
				{
					include = iActivity.getActivityAttempted();
				}
				else if (consider == ROLLUP_CONSIDER_NOTSKIPPED)
				{
					// Check if the activity should be 'skipped'.
					
					// Attempt to get rule information from the activity node
					var skippedRules = iActivity.getPreSeqRules();
					var result = null;
					
					if (skippedRules != null)
					{
						result = skippedRules.
							evaluate(RULE_TYPE_SKIPPED, iActivity, false);
					}
					
					// If the rule evaluation did not return null,
					// the activity is skipped, don't include it in rollup
					if (result != null)
					{
						include = false;
					}
				}
				else
				{
					include = true;
				}
			}
		}
		return include;
	},
	
	evaluateAll: function (iChildren)
	{
		var result = true;
		var emptySet = true;
		var considered = false;
	
		var tempActivity = null;
	
		var i = 0;
		while (result && (i < iChildren.length))
		{
			// Look at the next child for evaluation 
			tempActivity = iChildren[i];
			// Make sure the child is included in rollup 
			if (this.isIncluded(tempActivity)==true)
			{
			
				considered = true;
				var eval = this.mConditions.evaluate(tempActivity);
				result = result && (eval == EVALUATE_TRUE);
				emptySet = emptySet && (eval == EVALUATE_UNKNOWN);
			}
			i++;
		}
		
		if (considered  && emptySet)
		{
			result = false;
		}
		return result;
	},
	
	evaluateAny: function (iChildren)
	{
		var result = false;
		
		var tempActivity = null;
		
		var i = 0;
		while ((!result) && (i < iChildren.length))
		{
			// Look at the next child for evaluation 
			tempActivity = iChildren[i];
			
			// Make sure the child is included in rollup 
			if (this.isIncluded(tempActivity))
			{
				var eval = this.mConditions.evaluate(tempActivity);
				
				result = result || (eval == EVALUATE_TRUE);
			}
			i++;
		}
		return result;
	},
	
	evaluateNone: function (iChildren)
	{
		var result = true;
		var tempActivity = null;
		
		var i = 0;
		while (result && (i < iChildren.length))
		{
			// Look at the next child for evaluation 
			tempActivity = iChildren[i];
			
			// Make sure the child is included in rollup 
			if (this.isIncluded(tempActivity))
			{
				var eval = this.mConditions.evaluate(tempActivity);  
				
				result = result && 
					!( eval == EVALUATE_TRUE || eval == EVALUATE_UNKNOWN );
			}
			i++;
		}
		return result;
	},
	
	evaluateMinCount: function (iChildren)
	{
		var count = 0;
		var emptySet = true;
		
		var tempActivity = null;
		
		var i = 0;
		while ((count < this.mMinCount) && i < iChildren.length)
		{
			// Look at the next child for evaluation 
			tempActivity = iChildren[i];
			
			// Make sure the child is included in rollup 
			if (this.isIncluded(tempActivity))
			{
				var eval = this.mConditions.evaluate(tempActivity);
				
				if (eval == EVALUATE_TRUE)
				{
					count++;
				}
				emptySet = emptySet && (eval == EVALUATE_UNKNOWN);
			}
			i++;
		}
		
		var result = false;
		if (!emptySet) 
		{
			result = (count >= this.mMinCount);
		}
		
		return result;
	},
	
	evaluateMinPercent: function (iChildren)
	{
		var countAll = 0;
		var count = 0;
		var emptySet = true;
		
		var tempActivity = null;
		
		var i = 0;
		while (i < iChildren.length)
		{
			// Look at the next child for evaluation 
			tempActivity = iChildren[i];
			
			// Make sure the child is included in rollup 
			if (this.isIncluded(tempActivity))
			{  
				countAll++;
				var eval = this.mConditions.evaluate(tempActivity);
				if (eval == EVALUATE_TRUE)
				{
					count++;
				}
				emptySet = emptySet && (eval == EVALUATE_UNKNOWN);
			}
			i++;
		}
		
		var result = false;
		if (emptySet==false) 
		{
			result = (count >= parseFloat(((this.mMinPercent * countAll) + 0.5)));
		}
		return result;
	}
	
};

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
	JS port of ADL SeqRollupRuleset.java
	@author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
	
	This .js file is GPL licensed (see above) but based on
	SeqRollupRuleset.java by ADL Co-Lab, which is licensed as:
	
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

function SeqRollupRuleset(mRollupRules)  
{
	if (mRollupRules)
	{
		mRollupRules = iRules;
	}
}
//this.SeqRollupRuleset = SeqRollupRuleset;
SeqRollupRuleset.prototype = 
{
	mRollupRules: null,
	mIsSatisfied: false,
	mIsNotSatisfied: false,
	mIsCompleted: false,
	mIsIncomplete: false,
	
	evaluate: function (ioThisActivity)
	{
		// Clear previous evaluation state -- nothing should change due to rollup.
		this.mIsCompleted = false;
		this.mIsIncomplete = false;
		this.mIsSatisfied = false;
		this.mIsNotSatisfied = false;
		
		// This method implements part of RB.1.5
		
		// Evaluate all defined rollup rules for this activity.
		// Make sure there is a legal target and a set of children. 
		
		if (ioThisActivity != null)
		{
			if (ioThisActivity.getChildren(false) != null)
			{
				// Step 3.1 -- apply the Measure Rollup Process
				ioThisActivity = this.applyMeasureRollup(ioThisActivity);
				
				// Apply Progress Measure Rollup Process				
            		ioThisActivity=this.applyProgressMeasureRollup(ioThisActivity);
				
				var satisfiedRule = false;
				var completedRule = false;
				
				if (this.mRollupRules != null)
				{
					// Confirm at least one rule is defined for both sets --
					//  Complete/Incomplete and Satisfied/Not Satisfied
					for (var i = 0; i < this.mRollupRules.length; i++)
					{
						var rule = this.mRollupRules[i];
						
						if (rule.mAction == ROLLUP_ACTION_SATISFIED ||
							rule.mAction == ROLLUP_ACTION_NOTSATISFIED)
						{
							satisfiedRule = true;
						}
						
						if (rule.mAction == ROLLUP_ACTION_COMPLETED ||
							rule.mAction == ROLLUP_ACTION_INCOMPLETE)
						{
							completedRule = true;
						}
					}
				}
				
				// If no satisfied rule is defined, use default objective rollup
				if (satisfiedRule==false)
				{
					if (this.mRollupRules == null )
					{
						this.mRollupRules = new Array();
					}
				
					// Create default Not Satisfied rule
					var set = new SeqConditionSet(true);
					var cond = new SeqCondition();
					var rule = new SeqRollupRule();
					
					set.mCombination = COMBINATION_ANY;
					set.mConditions = new Array();
					
					cond.mCondition = OBJSTATUSKNOWN;
					set.mConditions[0] = cond;
					
					//cond = new SeqCondition();
					//cond.mCondition = SATISFIED;
					//cond.mNot = true;
					//set.mConditions[1] = cond;
					
					rule.mAction = ROLLUP_ACTION_NOTSATISFIED;
					rule.mConditions = set;
					
					// Add the default Not Satisfied rule to the set
					this.mRollupRules[this.mRollupRules.length] = rule;
					
					// Create default Satisfied rule
					rule = new SeqRollupRule();
					set = new SeqConditionSet(true);
					cond = new SeqCondition();
					
					set.mCombination = COMBINATION_ALL;
					cond.mCondition = SATISFIED;
					set.mConditions = new Array();
					set.mConditions[0] = cond;
					
					rule.mAction = ROLLUP_ACTION_SATISFIED;
					rule.mConditions = set;
					
					// Add the default Satisfied rule to the set
					this.mRollupRules[this.mRollupRules.length] = rule;
				}
				
				// If no completion rule is defined, use default completion rollup
				if (completedRule==false)
				{
					if (this.mRollupRules == null)
					{
						this.mRollupRules = new Array();
					}
					
					// Create default Incomplete rule
					var set = new SeqConditionSet(true);
					var cond = new SeqCondition();
					var rule = new SeqRollupRule();
					
					set.mCombination = COMBINATION_ANY;
					set.mConditions = new Array();
					
					cond.mCondition = PROGRESSKNOWN;	
					set.mConditions[0] = cond;
					
					//cond = new SeqCondition();
					//cond.mCondition = COMPLETED;
					//cond.mNot = true;
					//set.mConditions[1] = cond;
					
					rule.mAction = ROLLUP_ACTION_INCOMPLETE;
					rule.mConditions = set;
					
					// Add the default Incomplete rule to the set
					this.mRollupRules[this.mRollupRules.length] = rule;
					
					// Create default Completion rule
					rule = new SeqRollupRule();
					set = new SeqConditionSet(true);
					cond = new SeqCondition();
					
					set.mCombination = COMBINATION_ALL;
					cond.mCondition = COMPLETED;
					set.mConditions = new Array();
					set.mConditions[0] = cond;
					
					rule = new SeqRollupRule();
					rule.mAction = ROLLUP_ACTION_COMPLETED;
					rule.mConditions = set;
					
					// Add the default Completion rule to the set
					this.mRollupRules[this.mRollupRules.length] = rule;
				}
				
				// Evaluate all rollup rules.
				for (var i = 0; i < this.mRollupRules.length; i++)
				{
					var rule = this.mRollupRules[i];
					//alert("EVAL CHILDREN FOR: "+ioThisActivity.mActivityID);
					var result = rule.evaluate(ioThisActivity.getChildren(false));
				
					// Track state changes
					switch (result)
					{
						case ROLLUP_ACTION_NOCHANGE:
							break;
						
						case ROLLUP_ACTION_SATISFIED:
							this.mIsSatisfied = true;
							break;
						
						case ROLLUP_ACTION_NOTSATISFIED:
							this.mIsNotSatisfied = true;
							break;
						
						case ROLLUP_ACTION_COMPLETED:
							this.mIsCompleted = true;
							break;
						
						case ROLLUP_ACTION_INCOMPLETE:
							this.mIsIncomplete = true;
							break;
						
						default:
							break;
					}
				}
				
				// If a measure threshold exists, it was already used to determine
				// the activity's status.  Otherwise, use the results of the rollup
				if (!ioThisActivity.getObjSatisfiedByMeasure())
				{
					if (this.mIsSatisfied)
					{
						ioThisActivity.setObjSatisfied(TRACK_SATISFIED);
					}
					else if (this.mIsNotSatisfied)
					{
						if ( ioThisActivity.isPrimaryStatusSetBySCO() && ioThisActivity.getObjSatValue()== TRACK_UNKNOWN )	
							{//ignore
								
							}
						else											
						{
							ioThisActivity.setObjSatisfied(TRACK_NOTSATISFIED);
						}
					}
				}
				
				if (!ioThisActivity.getCompletedByMeasure())
					{
						if (this.mIsCompleted == true)
						{
							ioThisActivity.setProgress(TRACK_COMPLETED);
						}
						else if (this.mIsIncomplete == true)
						{
							if ( ioThisActivity.isPrimaryProgressSetBySCO() && (ioThisActivity.getProgressValue() == TRACK_UNKNOWN))
							{//ignore
								
							}
							else
							{
								ioThisActivity.setProgress(TRACK_INCOMPLETE);
							}
						}
					}
			}
		}
		return ioThisActivity;
	},
	
	applyMeasureRollup: function (ioThisActivity)
	{
		sclogdump("MeasureRollup [RB.1.1]","seq");
		var total = 0.0;
		var countedMeasure = 0.0;
		
		var children = ioThisActivity.getChildren(false);
		
		// Progress Measure Rollup Behavior 
		for (var i = 0; i < children.length; i++)
		{
			var child = children[i];
			if (child.getIsTracked())
			{
				// Make sure a non-zero weight is defined
				if (child.getObjMeasureWeight() > 0.0)
				{
					countedMeasure += parseFloat(child.getObjMeasureWeight());
					//alert("LOOK FOR MEASURE for: "+child.mActivityID+"is :"+child.getObjMeasure(false));
					
					// If a measure is defined for the child
					if (child.getObjMeasureStatus(false))
					{
						total += (parseFloat(child.getObjMeasureWeight()) * parseFloat(child.getObjMeasure(false)));	
					}
				}
			}
		}
		
		
		//check for counted measure
		if (countedMeasure > 0.0)
		{
			ioThisActivity.setObjMeasure(total / countedMeasure);
		}
		else
		{
			ioThisActivity.clearObjMeasure();
		}
	  	return ioThisActivity;
	},
	
	applyProgressMeasureRollup: function (ioThisActivity)
	{
		var total = 0;
		var countedMeasure = 0;
		var children = ioThisActivity.getChildren(false);
		for ( var i = 0; i < children.length; i++ )
		{
			var child = children[i];
			if ( child.getIsTracked() )
			{
				if ( child.getProMeasureWeight() > 0 )
				{
					countedMeasure += parseFloat(child.getProMeasureWeight());
					
					if ( child.getProMeasureStatus(false) )
					{
						total += (parseFloat(child.getProMeasureWeight()) * parseFloat(child.getProMeasure(false)));
					}
				}
			}
		}
		if ( countedMeasure > 0 )
		{
			ioThisActivity.setProMeasure(total/countedMeasure);
		}
		else 
		{
			ioThisActivity.clearProMeasure();
		}
		
		return ioThisActivity;
	},
   
	size: function ()
	{
		if (this.mRollupRules != null)
		{
			return this.mRollupRules.length;
		}
	
		return 0;
	}
};
	
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
	JS port of ADL SeqRule.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	SeqRule.java by ADL Co-Lab, which is licensed as:
	
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

var SEQ_ACTION_NOACTION = "noaction";
var SEQ_ACTION_IGNORE = "ignore";
var SEQ_ACTION_SKIP = "skip";
var SEQ_ACTION_DISABLED = "disabled";
var SEQ_ACTION_HIDEFROMCHOICE = "hiddenFromChoice";
var SEQ_ACTION_FORWARDBLOCK = "stopForwardTraversal";
var SEQ_ACTION_EXITPARENT = "exitParent";
var SEQ_ACTION_EXITALL = "exitAll";
var SEQ_ACTION_RETRY = "retry";
var SEQ_ACTION_RETRYALL = "retryAll";
var SEQ_ACTION_CONTINUE = "continue";
var SEQ_ACTION_PREVIOUS = "previous";
var SEQ_ACTION_EXIT = "exit";

function SeqRule()  
{
}
//this.SeqRule = SeqRule;
SeqRule.prototype = 
{
	mAction: SEQ_ACTION_IGNORE,
	mConditions: null,
	
	evaluate: function (iType, iThisActivity, iRetry)
	{
		sclogdump("SequencingRuleCheckSub [UP.2.1]","seq");
	   
		var result = SEQ_ACTION_NOACTION;
		var doEvaluation = false;
		
		// Filter the rule type prior to performing the evaluation.
		switch (iType)
		{
			case RULE_TYPE_ANY:
			{
				doEvaluation = true;
				break;
			}
			
			case RULE_TYPE_POST:
			{
				if (this.mAction == SEQ_ACTION_EXITPARENT || 
					this.mAction == SEQ_ACTION_EXITALL ||
					this.mAction == SEQ_ACTION_RETRY ||
					this.mAction == SEQ_ACTION_RETRYALL ||
					this.mAction == SEQ_ACTION_CONTINUE ||
					this.mAction == SEQ_ACTION_PREVIOUS)
				{
					doEvaluation = true;
				}
				break;
			}
			
			case RULE_TYPE_EXIT:
			{
				if (this.mAction == SEQ_ACTION_EXIT)
				{
					doEvaluation = true;
				}
				break;
			}
			
			case RULE_TYPE_SKIPPED:
			{
				if (this.mAction == SEQ_ACTION_SKIP)
				{
					doEvaluation = true;
				}
				break;
			}
			
			case RULE_TYPE_DISABLED:
			{
				if (this.mAction == SEQ_ACTION_DISABLED)
				{
					doEvaluation = true;
				}
				break;
			}
			
			case RULE_TYPE_HIDDEN:
			{
				if (this.mAction == SEQ_ACTION_HIDEFROMCHOICE)
				{
					doEvaluation = true;
				}
				break;
			}
			
			case RULE_TYPE_FORWARDBLOCK:
			{
				if (this.mAction == SEQ_ACTION_FORWARDBLOCK)
				{
					doEvaluation = true;
				}
				break;
			}
			
			default:
			{
				break;
			}
		}
		
		// Make sure the type of the current rule allows it to be evaluated.
		if (doEvaluation)
		{
			// Make sure we have a valid target activity 
			if (iThisActivity != null)
			{
			
				if (this.mConditions.evaluate(iThisActivity, {iIsRetry:iRetry}) == EVALUATE_TRUE)
				{	
					result = this.mAction;
					
				}
			}
		}
		
		return result;
	}
};
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
	JS port of ADL SeqRuleset.java
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	SeqRuleset.java by ADL Co-Lab, which is licensed as:
	
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

var RULE_TYPE_ANY = 1;
var RULE_TYPE_EXIT = 2;
var RULE_TYPE_POST = 3;
var RULE_TYPE_SKIPPED = 4;
var RULE_TYPE_DISABLED = 5;
var RULE_TYPE_HIDDEN = 6;
var RULE_TYPE_FORWARDBLOCK = 7;

function SeqRuleset(iRules)
{
	this.mRules = iRules;
}

//this.SeqRuleset = SeqRuleset;
SeqRuleset.prototype = 
{
	mRules: null,

	evaluate: function (iType, iThisActivity, iRetry)
	{
		sclogdump("SequencingRulesCheck [UP.2]","seq");
	   
		var action = null;
		
		// Evaluate all sequencing rules of type 'iType'.
		// Evaluation stops at the first rule that evaluates to true 
		if (this.mRules != null)
		{
			var cont = true;
			
			for (var i = 0; i < this.mRules.length && cont; i++)
			{
				var rule = this.mRules[i];
				var result = rule.evaluate(iType, iThisActivity, iRetry);
				
				if (result != SEQ_ACTION_NOACTION)
				{
					cont = false;
					action = result;
				}
			}
		}
		
		return action;
	},
	
	size: function ()
	{
		if (this.mRules != null)
		{
			return this.mRules.length;
		}
		
		return 0;
	}
};
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

/**
 * @author  Hendrik Holtmann <holtmann@mac.com>, Alex Killing <alex.killing@gmx.de>, Alfred Kohnert <alfred.kohnert@bigfoot.com>, Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @version $Id$
*/
//Fix for InternetExplorer to avoid searching API in a non-available opener after closing tab
var windowOpenerLoc;
try{windowOpenerLoc=window.opener.location;}catch(e){}
window.opener=null;
// settings for log
var log_auto_flush = false;

var log_buffer = "";

var ilRTEDisabledClass = 'ilc_rte_mlink_RTELinkDisabled';

debugWindow = null;

// to move NavigationBar
var  leftViewWidth=230;
$('#dragbar').mousedown(function(e){
	e.preventDefault();
	$('#zmove').css("display","block");
	$('#zmove').mousemove(function(e){
		leftViewWidth=e.pageX;
		$('#dragbar').css("left",e.pageX);
		$('#leftView').css("width",e.pageX);
		$('#tdResource').css("left",e.pageX+2);
	})
});
$(document).mouseup(function(e){
	$('#dragbar').unbind('mousemove');
	$('#zmove').css("display","none");
	$(document).unbind('mousemove');
});

// for log
function PopupCenter(pageURL, title,w,h) {
	var left = (screen.width/2)-(w/2);
	var top = (screen.height/2)-(h/2);
	debugWindow = window.open (pageURL, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
	debugWindow.focus();
} 


function toggleView() {
	elm_left = all("leftView");
	elm_drag = all("dragbar");
	elm_right= all("tdResource");
	elm_tree = all("treeView");
	elm_log = all("ilLog");
	elm_controls = all("treeControls");
	elm_toggle=all("treeToggle");
	
	if (treeView==false) {
		elm_left.style.width=leftViewWidth+'px';
		elm_drag.style.left=leftViewWidth+'px';
		elm_drag.style.display='block';
		elm_right.style.left=(leftViewWidth+2)+'px';
		elm_tree.style.display='block';
		elm_log.style.display='block';
		elm_controls.style.display='block';
		elm_toggle.innerHTML=this.config.langstrings['btnhidetree'];
		treeView=true;
	} else {
		elm_left.style.width='0';
		elm_drag.style.display='none';
		elm_right.style.left='0';
		elm_tree.style.display='none';
		elm_log.style.display='none';
		elm_controls.style.display='none';
		elm_toggle.innerHTML=this.config.langstrings['btnshowtree'];

		treeView=false;
	}
}


function toggleTree() {
	elm = all("toggleTree");
	
	if (treeState==false) {
		elm.innerHTML="Collapse All";
		//treeYUI.expandAll();
		il.NestedList.expandAll('rte_tree');
		treeState=true;
	} else {
		elm.innerHTML="Expand All";
		//treeYUI.collapseAll();
		il.NestedList.collapseAll('rte_tree');
		treeState=false;
	}
}

function toggleLog() {
	elm = all("toggleLog");
	if (logState==false) {
		elm.innerHTML="Hide Log";
		logState=true;
		onWindowResize();
	} else {
		elm.innerHTML="Show Log";
		logState=false;
		onWindowResize();
	}
}

function sclog(mess, type)
{
	if (disable_all_logging) {
		return;
	}	

	if (type=="seq" && disable_sequencer_logging==true) return;
	log_auto_flush=true;

	switch (type)
	{
		case "cmi": 
			mess='<font color="green">'+mess+'</font>';
		case "info": 
			mess='<font color="orange">'+mess+'</font>';
		case "error": 
			mess='<font color="red">'+mess+'</font>';
		case "seq": 
			mess='<font color="blue">'+mess+'</font>';	
		default: 
			mess=mess;
	}
	if (log_auto_flush)
	{
		elm = all("ilLogPre");
		if (elm) 
		{
			elm.innerHTML = elm.innerHTML + mess + '<br />';
			sclogscroll();
		}
	}
	else
	{
		log_buffer = log_buffer + mess + '<br />';
	}
}

/**
* flush log
*/
function sclogflush()
{
	return;
	elm = all("ilLogPre");
	if (elm) 
	{
		elm.innerHTML = elm.innerHTML + log_buffer;
		sclogscroll();
	}
	log_buffer = "";
}

/**
* Clear the Log
*/
function sclogclear()
{
	elm = all("ilLogPre");
	if (elm) 
	{
		elm.innerHTML = '';
	}
}

/**
* Dump a variable
*/
function sclogdump(param, type)
{
	if (disable_all_logging) {
		return;
	}
	
	
	depth = 0;
	
	var pre = '';
	for (var j=0; j < depth; j++)
	{
		pre = pre + '    ';
	}
	
	//sclog(typeof param);
	switch (typeof param)
	{
		case 'boolean':
			if(param) sclog(pre + "true (boolean)"); else sclog(pre + "false (boolean)",type);
			break;

		case 'number':
			sclog(pre + param + ' (number)',type);
			break;

		case 'string':
			sclog(pre + param + ' (string)',type);
			break;

		case 'object':
			if (param === null)
			{
				sclog(pre + 'null');
			}
			if (param instanceof Array) sclog(pre + '(Array) {',type);
			else if (param instanceof Object) sclog(pre + '(Object) {');
			for (var k in param)
			{
				//if (param.hasOwnProperty(k)) // hasOwnProperty requires Safari 1.2
				//{
					if (typeof param[k] != "function",type)
					{
						sclog(pre + '[' + k + '] => ');
						sclogdump(param[k], depth + 1);
					}
				//}
			}
			sclog(pre + '}',type);
			break;
			
		case 'function':
			// we do not show functions
			break;

		default:
			sclog(pre + "unknown: " + (typeof param),type);
			break;
		
	}
}

/**
* scroll the log div to the end
*/
function sclogscroll()
{
	var top = document.getElementById('ilLog').scrollTop;
	var height = document.getElementById('ilLog').scrollHeight;
	var offset = document.getElementById('ilLog').offsetHeight;
	
//alert ("Top: " + top + ", Height: " + height + ", Offset: " + offset);
	
	if (top < 
		(height - offset - 1))
	{
		document.getElementById('ilLog').scrollTop = height - offset + 20;
	}
}

function ISODurationToCentisec(str)
{
  // Only gross syntax check is performed here
  // Months calculated by approximation based on average number
  // of days over 4 years (365*4+1), not counting the extra day
  // every 1000 years. If a reference date was available,
  // the calculation could be more precise, but becomes complex,
  // since the exact result depends on where the reference date
  // falls within the period (e.g. beginning, end or ???)
  // 1 year ~ (365*4+1)/4*60*60*24*100 = 3155760000 centiseconds
  // 1 month ~ (365*4+1)/48*60*60*24*100 = 262980000 centiseconds
  // 1 day = 8640000 centiseconds
  // 1 hour = 360000 centiseconds
  // 1 minute = 6000 centiseconds
  var aV = new Array(0,0,0,0,0,0);
  var bErr = false;
  var bTFound = false;
  if (str.indexOf("P") != 0) bErr = true;
  if (!bErr)
  {
    var aT = new Array("Y","M","D","H","M","S")
    var p=0;
    var i = 0;
    str = str.substr(1); //get past the P
    for (i = 0 ; i < aT.length; i++)
    {
      if (str.indexOf("T") == 0)
      {
        str = str.substr(1);
        i = Math.max(i,3);
        bTFound = true;
      }
      p = str.indexOf(aT[i]);
      //alert("Checking for " + aT[i] + "\nstr = " + str);
      if (p > -1)
      {
        // Is this a M before or after T? Month or Minute?
        if ((i == 1) && (str.indexOf("T") > -1) && (str.indexOf("T") < p)) continue;
        if (aT[i] == "S")
        {
          aV[i] = parseFloat(str.substr(0,p))
        }
        else
        {
          aV[i] = parseInt(str.substr(0,p))
        }
        if (isNaN(aV[i]))
        {
          bErr = true;
          break;
        }
        else if ((i > 2) && (!bTFound))
        {
          bErr = true;
          break;
        }
        str = str.substr(p+1);
      }
    }
    if ((!bErr) && (str.length != 0)) bErr = true;
    //alert(aV.toString())
  }
  if (bErr)
  {
     //alert("Bad format: " + str)
    return 0
  }
  return aV[0]*3155760000 + aV[1]*262980000
      + aV[2]*8640000 + aV[3]*360000 + aV[4]*6000
      + Math.round(aV[5]*100)
}

function timeStringParse(iTime, ioArray)    
{
	var mInitArray=new Array();
	var mTempArray2= new Array(); 
	mTempArray2[0]="0";
	mTempArray2[1]="0";
	mTempArray2[2]="0";

	var mDate = "0";
	var mTime = "0";

	// make sure the string is not null
	if ( iTime == null )
	{
		return ioArray;
	}
     
  // make sure that the string has the right format to split
	if ( ( iTime.length == 1 ) || ( iTime.indexOf("P") == -1 ) )
	{
		return ioArray;
	}
	mInitArray = iTime.split("P");

	// T is present so split into day and time part
	// when "P" is first character in string, rest of string goes in
	// array index 1
	if ( mInitArray[1].indexOf("T") != -1 )
	{
		mTempArray2 = mInitArray[1].split("T");
		mDate =  mTempArray2[0];
		mTime =  mTempArray2[1];
	}
	else
	{
		mDate =  mInitArray[1];
	}

	// Y is present so get year
	if ( mDate.indexOf("Y") != -1 )
	{
		mInitArray = mDate.split("Y");
		tempInt = parseInt(mInitArray[0],10);
		ioArray[0] = parseInt(tempInt,10);
	}
	else
	{
		mInitArray[1] = mDate;
	}

	// M is present so get month
	if ( mDate.indexOf("M") != -1 )
	{
		mTempArray2 = mInitArray[1].split("M");
		tempInt = parseInt(mTempArray2[0],10);
		ioArray[1] = parseInt(tempInt,10);
	}
	else
	{
		if ( mInitArray.length != 2 )
		{
			mTempArray2[1] = "";
		}
		else
		{
			mTempArray2[1] = mInitArray[1];
		}
	}

	// D is present so get day
	if ( mDate.indexOf("D") != -1 )
	{
		mInitArray = mTempArray2[1].split("D");
		tempInt = parseInt(mInitArray[0],10);
		ioArray[2] = parseInt(tempInt,10);
	}
	else
	{
		mInitArray = new Array();
		mInitArray[0]="";
		mInitArray[1]="";
	}

	// if string has time portion
	if ( mTime!="0")
	{
		// H is present so get hour
		if ( mTime.indexOf("H") != -1 )
		{
			mInitArray =  mTime.split("H");
			tempInt = parseInt(mInitArray[0],10);
			ioArray[3] = parseInt(tempInt,10);
		}
		else
		{
			mInitArray[1] = mTime;
		}

		// M is present so get minute
		if ( mTime.indexOf("M") != -1 )
		{
			mTempArray2 = mInitArray[1].split("M");
			tempInt = parseInt(mTempArray2[0],10);
			ioArray[4] = parseInt(tempInt,10);
		}
		else
		{
			if ( mInitArray.length != 2 )
			{
				mTempArray2[1] = "";
			}
			else
			{
				mTempArray2[1] = mInitArray[1];
			}
		}

		// S is present so get seconds
		if ( mTime.indexOf("S") != -1 )
		{
			mInitArray = mTempArray2[1].split("S");

			if ( mTime.indexOf(".") != -1)
			{
				// split requires this regular expression for "."
				mTempArray2 = mInitArray[0].split(".");

				// correct for case such as ".2"
				if ( mTempArray2[1].length == 1 )
				{
				 mTempArray2[1] = mTempArray2[1] + "0";
				}

				tempInt2 = parseInt(mTempArray2[1],10);
				ioArray[6] = parseInt(tempInt2,10);
				tempInt = parseInt(mTempArray2[0],10);
				ioArray[5] = parseInt(tempInt,10);
			}
			else
			{
				tempInt = parseInt(mInitArray[0],10);
				ioArray[5] = parseInt(tempInt,10);
			}
		}
	}

	return ioArray;
}


function addTimes(iTimeOne,iTimeTwo) {
	  var mTimeString = null;
      var multiple = 1;
      mFirstTime = new Array();
      mSecondTime = new Array();

      for (var i = 0; i < 7; i++)
      {
         mFirstTime[i] = 0;
         mSecondTime[i] = 0;
      }

      mFirstTime=timeStringParse(iTimeOne, mFirstTime); 
      mSecondTime=timeStringParse(iTimeTwo, mSecondTime);
	   // add first and second time arrays  
      for (var i = 0; i < 7; i++)
      {
	     mFirstTime[i] =parseInt(mFirstTime[i],10) + parseInt(mSecondTime[i],10);
      }
	
	// adjust seconds, minutes, hours, and days if addition
      // results in too large a number
      if ( mFirstTime[6] > 99 )
      {
         multiple = parseFloat(mFirstTime[6] / 100);
         mFirstTime[6] = mFirstTime[6] % 100;
         mFirstTime[5] = parseInt(mFirstTime[5],10) + multiple;
      }

      if ( mFirstTime[5] > 59 )
      {
         multiple = parseFloat(mFirstTime[5] / 60);
         mFirstTime[5] = mFirstTime[5] % 60;
         mFirstTime[4] =parseInt(mFirstTime[4],10)+ multiple;
      }
      if ( mFirstTime[4] > 59 )
      {
         multiple = parseFloat(mFirstTime[4] / 60);
         mFirstTime[4] = mFirstTime[4] % 60;
         mFirstTime[3] =parseInt(mFirstTime[3],10) + multiple;
      }

      if ( mFirstTime[3] > 23 )
      {
         multiple = parseFloat(mFirstTime[3] / 24);
         mFirstTime[3] = mFirstTime[3] % 24;
         mFirstTime[2] = parseInt(mFirstTime[2],10)+ multiple;
      }
	
	  // create the new timeInterval string
      mTimeString = "P";
      if ( mFirstTime[0] != 0 )
      {
         tempInt = parseInt(mFirstTime[0],10);
         mTimeString +=  tempInt.toString();
         mTimeString += "Y";
      }
      if ( mFirstTime[1] != 0 )
      {
         tempInt = parseInt(mFirstTime[1],10);
         mTimeString +=  tempInt.toString();
         mTimeString +=  "M";
      }

      if ( mFirstTime[2] != 0 )
      {
         tempInt = parseInt(mFirstTime[2],10);
         mTimeString +=  tempInt.toString();
         mTimeString += "D";
      }

      if ( ( mFirstTime[3] != 0 ) || ( mFirstTime[4] != 0 ) 
           || ( mFirstTime[5] != 0 ) || (mFirstTime[6] != 0) )
      {
         mTimeString +=  "T";
      }

      if ( mFirstTime[3] != 0 )
      {
         tempInt = parseInt(mFirstTime[3],10);
         mTimeString +=  tempInt.toString();
         mTimeString +=  "H";
      }

      if ( mFirstTime[4] != 0 )
      {
         tempInt =parseInt(mFirstTime[4],10);
         mTimeString +=  tempInt.toString();
         mTimeString += "M";
      }

      if ( mFirstTime[5] != 0 )
      {
         tempInt = parseInt(mFirstTime[5],10);
         mTimeString +=  tempInt.toString();
      }

      if ( mFirstTime[6] != 0 )
      {
         if ( mFirstTime[5] == 0 )
         {
            mTimeString += "0";
         }
         mTimeString += ".";
         if ( mFirstTime[6] < 10 )
         {
            mTimeString += "0";
         }
         tempInt2 = parseInt(mFirstTime[6],10);
         mTimeString +=  tempInt2.toString();
      }
      if ( ( mFirstTime[5] != 0 ) || ( mFirstTime[6] != 0 ) )
      {
         mTimeString += "S";
      }

      return mTimeString;
}



/* Time related Data Types */

function Duration (mixed) 
{
	this.value = new Date(typeof(mixed) === "number" ? mixed : Duration.parse(mixed || ""));
}

this.Duration = Duration;

Duration.prototype.set = function (obj)
{
	this.value.setTime(obj && obj.valueOf ? obj.valueOf() : obj); } ;

Duration.prototype.add = function (obj)
{
	var val = this.value.getTime() + obj && obj.valueOf ? obj.valueOf() : obj;
	this.value.setTime(val);
	return val;
};

Duration.parse = function (str) 
{
	var m = String(str).match(/^P(\d+Y)?(\d+M)?(\d+D)?(T(\d+H)?(\d+M)?((\d+)\.?(\d*)?S)?)?$/);
	if (!m) return null;
	return m[4]==="T" ? null : Date.UTC (
		(parseInt(m[1]) || 0)+1970,
		(parseInt(m[2]) || 0),
		(parseInt(m[3]) || 0)+1,
		parseInt(m[5]) || 0,
		parseInt(m[6]) || 0,
		parseInt(m[8]) || 0,
		parseInt(m[9]) || 0
	);
};

Duration.toString = function (d) 
{
	if (typeof d ==="number") d = new Date(d);
	var t, r = ['P'];
	if ((t = d.getUTCFullYear()-1970)) {r.push(t + 'Y');}
	if ((t = d.getUTCMonth())) {r.push(t + 'M');}
	if ((t = d.getUTCDate()-1)) {r.push(t + 'D');}
	r.push('T');
	if ((t = d.getUTCHours())) {r.push(t + 'H');}
	if ((t = d.getUTCMinutes())) {r.push(t + 'M');}
	if ((t = d.getUTCSeconds()+d.getUTCMilliseconds()/1000)) {r.push(t.toFixed(2)+ 'S');}
	return r.join("");
};

Duration.prototype.toString = function () 
{
	return Duration.toString(this.value);
};

Duration.prototype.valueOf = function () 
{
	return this.value.getTime();
};

function DateTime (mixed, utc) 
{
	this.value = new Date(typeof(mixed) === "number" ? mixed : DateTime.parse(mixed || "", utc));
}

this.DateTime = DateTime;

DateTime.parse = function (str, utc) 
{
	var m = String(str).match(/^\d{4}(-\d{2}(-\d{2}(T\d{2}(:\d{2}(:\d{2}(\.\d{1,2}([-+Z](\d{2}(:\d{2})?)?)?)?)?)?)?)?)?$/);
	if (!m) return null;
	var a = [
		m[0] ? Number(m[0].substr(0,4)) : 0, // yyyy
		m[1] ? Number(m[1].substr(1,2))-1 : 0, // mm
		m[2] ? Number(m[2].substr(1,2)) : 1, // dd
		m[3] ? Number(m[3].substr(1,2)) : 0, // hh
		m[4] ? Number(m[4].substr(1,2)) : 0, // mm
		m[5] ? Number(m[5].substr(1,2)) : 0, // ss
		m[6] ? Number(m[6].substr(1,2)) : 0, // ff
		m[7] ? m[7].substr(0,1) : utc ? 'Z' : '+', // z
		m[8] ? Number(m[8].substr(0,2)) : 0, // zhh
		m[9] ? Number(m[9].substr(1,2)) : 0 // zmm
	];
	var z = a[7]==='Z' ? (new Date()).getTimezoneOffset() : ((a[8] || 0)*60 + (a[9] || 0)) * (a[7]==='-' ? -1 : 1);
	var d = new Date(a[0], a[1], a[2], a[3], a[4], a[5], a[6]);	
	if (a[0]<1970 || a[0]>2038 || a[1]<0 || a[1]>12 || 
		d.getMonth()!==a[1] || d.getDate()!==a[2] || 
		a[3]>23 || a[4]>59 || a[5]>59 || 
		a[8]>23 || a[9]>59 || 
		m[7] && m[7]!=="Z" && !m[8]) return null;
	d.setTime(d.getTime()+z); 
	return d;
};

DateTime.toString = function (d, parts, prec) 
{
	function f(n) 
	{
		return (n<10 ? '0' : '') + n;
	}
	if (typeof d ==="number") d = new Date(d);
	var r = [
		d.getFullYear(),
		'-', f(d.getMonth()+1),
		'-', f(d.getDate()),
		'T', f(d.getHours()),
		':', f(d.getMinutes()),
		':', f(d.getSeconds()), 
		f((d.getMilliseconds()/1000).toFixed(prec || 3).substr(1))
	];
	return r.slice(0, 2*(parts || 7)-1).join("");
};

DateTime.prototype.toString = function (parts, prec) 
{
	return DateTime.toString(this.value, parts, prec);
};

DateTime.prototype.valueOf = function () 
{
	return this.value.getTime();
};



/* Core Data Objects for Activity Tree and Usertracking */


function Activity() 
{
	this.cmi_node_id = "$" + remoteInsertId++;
	this.hideLMSUIs = new Object();
	this.objectives = new Object();
	this.primaryObjective = new Object(); // reference to objective or self
	this.comments = new Object();
	this.interactions = new Object();
	this.rules = new Object();
}
this.Activity = Activity;
Activity.prototype = 
{
	dirty : 0,
	accesscount : 0,
	accessduration : 0,
	accessed : 0,
	activityAbsoluteDuration : 0,
	activityAbsoluteDurationLimit : 0,
	activityAttemptCount : 0,
	activityExperiencedDuration : 0,
	activityExperiencedDurationLimit : 0,
	activityProgressStatus : false,
	attemptAbsoluteDuration : 0,
	attemptAbsoluteDurationLimit : null,
	attemptCompletionAmount : 0,
	attemptCompletionStatus : false,
	attemptExperiencedDuration : 0,
	attemptExperiencedDurationLimit : 0,
	attemptLimit : 0,
	attemptProgressStatus : false,
	audio_captioning : 0,
	audio_level : 0,
	beginTimeLimit : 0,
	choice : true,
	choiceExit : true,
	completion : 0,
	completion_status : null,
	completionSetByContent : false,
	completionThreshold : null,
	constrainChoice : false,
	cp_node_id : null,
	created : 0,
	credit : 'credit',
	dataFromLMS : null,
	delivery_speed : 0,
	endTimeLimit : 0,
	entry : 'ab-initio',
	exit : null,
	flow : false,
	foreignId: 0,
	forwardOnly : false,
	id : null, // item id not sequencing
	index: 0, // numerical index in activities
	isvisible : true,
	language : null,
	location : null,
	max : null,
	measureSatisfactionIfActive : true,
	min : null,
	modified : 0,
	objectiveMeasureWeight : 1.0,
	objectiveSetByContent : false,
	parameters : null,
	parent : null,
	preventActivation : false,
	progress_measure : null,
	randomizationTiming : 'never',
	raw : null,
	reorderChildren : false,
	requiredForCompleted : 'always',
	requiredForIncomplete : 'always',
	requiredForNotSatisfied : 'always',
	requiredForSatisfied : 'always',
	resourceId : null,
	rollupObjectiveSatisfied : true,
	rollupProgressCompletion : true,
	scaled : null,
	scaled_passing_score: null,
	selectCount : 0,
	selectionTiming : 'never',
	session_time : 0,
	success_status : null,
	suspend_data : null,
	timeLimitAction : null,
	title : null,
	total_time : 'PT0H0M0S',
	tracked : true,
	useCurrentAttemptObjectiveInfo : true,
	useCurrentAttemptProgressInfo : true
};
 
function Interaction(cmi_node_id) 
{
	this.cmi_node_id = cmi_node_id;
	this.correct_responses = new Object();
	this.objectives = new Object();
}
this.Interaction = Interaction;
Interaction.prototype = 
{
	cmi_interaction_id : 0,
	description : null,
	id : null,
	latency : null,
	learner_response : null,
	result : null,
	timestamp : null,
	type : null,
	weighting : null
};

function Comment(cmi_node_id) 
{
	this.cmi_node_id = cmi_node_id;
}
this.Comment = Comment;
Comment.prototype = 
{
	cmi_comment_id : 0,
	comment : null,
	timestamp : null,
	location : null,
	sourceIsLMS : false
};

function CorrectResponse(cmi_interaction_id)  
{
	this.cmi_interaction_id = cmi_interaction_id;
}
this.CorrectResponse = CorrectResponse;
CorrectResponse.prototype = 
{
	cmi_correct_response_id : 0,
	pattern : null
};

function Objective(cmi_node_id, cmi_interaction_id)  
{
	this.cmi_interaction_id = cmi_interaction_id;
	this.cmi_node_id = cmi_node_id;
	this.mapinfos = new Object();
}
this.Objective = Objective;
Objective.prototype = 
{
	cmi_objective_id : 0,
	cp_node_id : 0,
	foreignId: 0,
	id : null,
	objectiveID : null,
	completion_status : null,
	description : null,
	max : null,
	min : null,
	raw : null,
	scaled : null,
	progress_measure : null,
	success_status : null,
	scope : "local",
	minNormalizedMeasure : 1.0,
	primary : false,
	satisfiedByMeasure : false
};

function Mapinfo() {}
this.Mapinfo = Mapinfo;
Mapinfo.prototype = 
{
	cp_node_id : 0,
	foreignId: 0,
	readNormalizedMeasure : true,
	readSatisfiedStatus : true,
	targetObjectiveID : null,
	writeNormalizedMeasure : false,
	writeSatisfiedStatus : false
};

function Rule() 
{
	this.conditions = new Object();
}
this.Rule = Rule;
Rule.prototype = 
{
	action : null,
	childActivitySet : 'all',
	conditionCombination : null,
	cp_node_id : 0,
	foreignId: 0,
	minimumCount : 0,
	minimumPercent : 0,
	type : null
};

function Condition() {}
this.Condition = Condition;
Condition.prototype = 
{
	condition : null,
	cp_node_id : 0,
	foreignId: 0,
	measureThreshold : null,
	operator : 'noOp',
	referencedObjective : null
};

function HideLMSUI() {}
this.HideLMSUI = HideLMSUI;
HideLMSUI.prototype = 
{
	cp_node_id : 0,
	foreignId: 0,
	value : null
};


/* User Interface Objects (crossbrowser) */


function UIEvent (e, w) 
{
	if (!w) 
	{
		w = window;
	}
	this._ie = !e && w.event;
	this._event = e || w.event;
	this.keyCode = this._ie ? w.event.keyCode : this._event.which;
	this.shiftKey = this._ie ? w.event.shiftKey : this._event.shiftKey;
	this.ctrlKey = this._ie ? w.event.ctrlKey : this._event.ctrlKey;
	this.srcElement = e.target || w.event.srcElement;
	this.type = this._event.type;
}

this.UIEvent = UIEvent;

UIEvent.prototype.getIdElement = function () {
	return getAncestor(this.srcElement, 'id', true);
};	

UIEvent.prototype.getHrefElement = function () {
	return getAncestor(this.srcElement, 'href', true);
};	

UIEvent.prototype.stop = function () {
	var e = this._event;
	if (e.preventDefault) 
	{ 
		e.preventDefault(); 
		e.stopPropagation(); 
	} 
	else 
	{
		e.returnValue = false;
		e.cancelBubble = true;
	}
};


/* User Interface Methods (DOM, Events, CSS, crossbrowser) */


function attachUIEvent (obj, name, func) 
{
	if (window.Event) 
	{
		obj.addEventListener(name, func, false);
	} 
	else if (obj.attachEvent) 
	{
		obj.attachEvent('on'+name, func);
	} 
	else 
	{
		obj[name] = func;
	}
}
	
function detachUIEvent(obj, name, func) 
{
	if (window.Event) 
	{
		obj.removeEventListener(name, func, false);
	} 
	else if (obj.attachEvent) 
	{
		obj.detachEvent('on'+name, func);
	} 
	else 
	{
		obj[name] = '';
	}
}

	// CSS handling
function getCurrentStyle (elm, prop)
{
	var doc = elm.ownerDocument;
	if(elm.currentStyle) {
		return elm.currentStyle[prop];
	} else if (doc.defaultView && doc.defaultView.getComputedStyle) {
		return doc.defaultView.getComputedStyle(elm, '').getPropertyValue(fromCamelCase(prop));
	} else if (elm.style && elm.style[prop]) {
		return elm.style[prop];
	} else {
		return null;
	}
}

function getAncestor (elm, attr, pattern, includeSelf) 
{
	if (elm && elm.nodeType===1) 
	{
		return null;
	}
	if (!includeSelf) 
	{
		elm = elm.parentNode;
	}
	do {
		if (elm[attr]) 
		{
			if (!pattern || (pattern instanceof RegExp) ? 
				pattern.match(elm[attr]) : elm[attr]==pattern)
			{
				break;
			}
		}
		elm = elm.parentNode;
	} while (elm);
	return elm;
}

function getDesendents(elm, tagName, className, filter, depth) 
{
	function check(pattern, value) 
	{
		switch (typeof(pattern))
		{
			case 'string':
				return pattern.charAt()==="!" ^ pattern===value;
			case 'function':
				return pattern(value);
			case 'object':
				return pattern instanceof RegExp ? pattern.test(value) : pattern[value];
		}
	}
	if (elm && elm.nodeType===1) 
	{
		return null;
	}
	var children = elm.childNodes;
	var sink = [];
	for (var i=0, ni=children.length; i<ni; i++)
	{
		var child = children[i];
		if (child.nodeType!==1) {continue;}
		if (tagName && !check(tagName, child.tagName)) {continue;}
		if (className && !check(className, child.className)) {continue;}
		switch (typeof(filter))
		{
			case 'function':
				if (!filter(child)) {continue;}
				break;
			case 'object':
				for (var k in filter) {
					if (!check(filter[k], elm[k])) {continue;}
				}
				break;
		}
		sink.push(child);
		if (depth===undefined || depth) 
		{
			sink = sink.concat(getDesendents(child, tagName, className, filter, depth-1));
		}
	}
	return sink;
}

function all(id, win) 
{
	if (id && id.nodeType===1) {return id;} // already HTMLElement
	var doc  = (win ? win : window).document;
	var elm = doc.getElementById(id);
	return !elm ? null : elm.length ? elm.item(0) : elm;
}

function addClass (elm, name) 
{
	elm = all(elm);
	if (elm && !hasClass(elm, name)) 
	{
		elm.className = trim(elm.className + " " + name);
	}
}

function hasClass(elm, name) 
{
	elm = all(elm);
	return elm && (" " + elm.className + " ").indexOf(" " + name + " ")>-1;
}

function removeClass(elm, name) 
{
	elm = all(elm);
	if (elm) 
	{
		elm.className = trim((" " + elm.className + " ").replace(name, " "));
	}
}

function replaceClass(elm, oldname, newname) 
{
	elm = all(elm);
	removeClass(elm, oldname);
	addClass(elm, newname);
}

function toggleClass(elm, name, state) 
{
	if (name == "disabled")
	{
		name = ilRTEDisabledClass;
	}

	elm = all(elm);
	if (state===undefined) {
		state = !hasClass(elm, name);
	}
	if (!state) {
		removeClass(elm, name);
	}
	else {
		addClass(elm, name);
	}
}

function getOuterHTML(elm)
{
	return elm.outerHTML!==undefined ? elm.outerHTML : elm.outerHTML;
}

function setOuterHTML(elm, markup)
{
	if (elm.outerHTML!==undefined) 
	{
		elm.outerHTML = markup;
	}
	else
	{
		var range = elm.ownerDocument.createRange();
		range.setStartBefore(elm);
		var fragment = range.createContextualFragment(markup);
		elm.parentNode.replaceChild(fragment, elm);
	}
}


/* Date Methods (server time sensible) */

function currentTime() 
{
	var d = new Date();
	return d.getTime() + (Date.remoteOffset || 0);
}


/* String functions */

function trim (str, norm) 
{
	var r = String(str).replace(/^\s+|\s+$/g, '');
	return norm ? r.replace(/\s+/g, ' ') : r;
}

function repeat(obj, times) 
{
	return (new Array(times+1)).join(obj);
}

// converts string input "toCamelCase" to string output "to-camel-case"
function fromCamelCase(s) 
{
	return s.charAt(0) + s.substring(1).replace(/([A-Z])/g, function(match) {return '-' + match.toLowerCase();});
}

// converts string input "to-camel-case" to string output "toCamelCase"
function toCamelCase(s) 
{
	return s.replace(/(\-\w)/g, function(match) {return match.substring(1).toUpperCase();});
}


/* Number functions */

function numberFormat(num, dec, len) 
{
	var s = num.toFixed(dec);
	if (len && s.length<len) 
	{
		while (s.length<len) 
		{
			s = '0' + s;
		}
	}
	return s;
}



/* Object functions */

function copyOf(obj, ref) 
{
	switch (typeof obj) {
		case 'object':
			var r = new obj.constructor();
			if (obj instanceof Array) // instanceof requires MSIE5+ 
			{ 
				for (var i=0, ni=obj.length; i<ni; i+=1) 
				{
					r[i] = copyOf(obj[i], ref);
				}
			} 
			else 
			{
				for (var k in obj) 
				{
					if (obj.hasOwnProperty(k)) // hasOwnProperty requires Safari 1.2
					{  
						r[k] = copyOf(obj[k], ref);
					}
				}
			}
			return r;
		case 'function':
		case 'unknown':
			// should not be copied but referenced if ref flag is set
			return ref ? obj : undefined;
		default: 
			return obj;
	}
}


/* JSON and XMLHHTP functions */

function createHttpRequest() 
{
	try 
	{
		return window.XMLHttpRequest 
			? new window.XMLHttpRequest()
			: new window.ActiveXObject('MSXML2.XMLHTTP');
	} 
	catch (e) 
	{
		throw new Error('cannot create XMLHttpRequest');
	}
}

function sendAndLoad(url, data, callback, user, password, headers) 
{
	function HttpResponse(xhttp) 
	{
		this.status = Number(xhttp.status);
		this.content = String(xhttp.responseText);
		this.type = String(xhttp.getResponseHeader('Content-Type'));
	}
	function onStateChange() 
	{
		if (xhttp.readyState === 4) { // COMPLETED
			if (typeof callback === 'function') {
				callback(new HttpResponse(xhttp));
			} else {
				return new HttpResponse(xhttp);
			} 
		}
	}		
	var xhttp = createHttpRequest();
	var async = !!callback;
	var post = !!data; 
	xhttp.open(post ? 'POST' : 'GET', url, async, user, password);
	if (typeof headers !== 'object') 
	{
		headers = new Object();
	}
	if (post) 
	{
		headers['Content-Type'] = 'application/x-www-form-urlencoded';
	}
	if (headers && headers instanceof Object) 
	{
		for (var k in headers) {
			xhttp.setRequestHeader(k, headers[k]);
		}
	}
	if (async) 
	{
		xhttp.onreadystatechange = onStateChange;
		xhttp.send(data ? String(data) : '');				
	} else 
	{
		xhttp.send(data ? String(data) : '');				
		return onStateChange();
	}
}

function sendJSONRequest (url, data, callback, user, password, headers) 
{		
	if (typeof headers !== "object") {headers = {};}
	headers['Accept'] = 'text/javascript';
	headers['Accept-Charset'] = 'UTF-8';
	var r = sendAndLoad(url, toJSONString(data), callback, user, password, headers);
	
	if (r.content) {
		if (r.content.indexOf("login.php")>-1 || r.content.indexOf("formlogin")>-1) {
			var thref=window.location.href;
			thref=thref.substring(0,thref.indexOf('ilias.php'))+"Modules/Scorm2004/templates/default/session_timeout.html";
			window.location.href = thref;
		}
	}
	
	if ((r.status===200 && (/^text\/javascript;?.*/i).test(r.type)) || r.status===0)
	{
		return parseJSONString(r.content);
	}
	else
	{
		return r.content;
	}
}
	
function toJSONString (v, tab) {
	tab = tab ? tab : "";
	var nl = tab ? "\n" : "";
	function fmt(n) {
		return (n < 10 ? '0' : '') + n;
	}
	function esc(s) {
		var c = {'\b': '\\b', '\t': '\\t', '\n': '\\n', '\f': '\\f', '\r': '\\r', '"' : '\\"', '\\': '\\\\'};
		return '"' + s.replace(/[\x00-\x1f\\"]/g, function (m) {
			var r = c[m];
			if (r) {
				return r;
			} else {
				r = m.charAt(0);
				return "\\u00" + (r < 16 ? '0' : '') + r.toString(16);
			}
		}) + '"';
	}
	switch (typeof v) {
	case 'string':
		return esc(v);
	case 'number':
		return isFinite(v) ? String(v) : 'null';			
	case 'boolean':
		return String(v);			
	case 'object':
		if (v===null) {
			return 'null';
		} else if (v instanceof Date) {
			return '"' + v.getValue(v) + '"'; // msec not ISO
		} else if (v instanceof Array) {
			var ra = new Array();
			for (var i=0, ni=v.length; i<ni; i+=1) {
				ra.push(v[i]===undefined ? 'null' : toJSONString(v[i], tab.charAt(0) + tab));
			}
			return '[' + nl + tab + ra.join(',' + nl + tab) + nl + tab + ']';
		} else {
			var ro = new Array();
			for (var k in v) {	
				if (v.hasOwnProperty && v.hasOwnProperty(k)) {
					ro.push(esc(String(k)) + ':' + toJSONString(v[k], tab.charAt(0) + tab));
				}
			}
			return '{' + nl + tab + ro.join(',' + nl + tab) + nl + tab + '}';
		}
	}
}

function parseJSONString (s) 
{
/*	var re = /^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/;
	try 
		{
		if (re.test(s)) 
		{
			return window.eval('(' + s + ')');
		} 
	} catch (e) {}
	throw new SyntaxError('parseJSONString: ' + s.substr(0, 200));*/
	if (s.length>1) {
		return window.eval('(' + s + ')');
	} else {
		return null;
	}	
	
}


/* localization methods */

function setLocalStrings(obj)
{
	extend(translate, obj);
}
	
function translate(key, params) 
{
	var value = key in translate ? translate[key] : key;
	if (typeof params === 'object') 
	{
		value = String(value).replace(/\{(\w+)\}/g, function (m) {
			return m in params ? params[m] : m;
		});
	} 
	return value; 
}
	

/* array helpers */

function keys(obj) 
{
	var r = [];
	for (var k in obj) 
	{
		r.push(k);
	}
	return r;
}

function values(obj, attr) 
{
	var r = [];
	for (var k in obj) 
	{
		r.push(attr ? obj[k][attr] : obj[k]);
	}
	return r;
}

function walkItems (root, name, func, sink, depth) 
{
	var data=null, subdata=null;
	var items = root[name];
	var arraySink = sink && sink instanceof Array;
	if (depth===undefined) 
	{
		depth = 0;
	}
	for (var k in items) 
	{
		var item = items[k];
		if (!arraySink) 
		{
			func(item, sink, depth);
		}
		
		if (item && item[name]) 
		{
			subdata = walkItems(item, name, func, arraySink ? [] : sink, depth+1);
		}
		if (arraySink) 
		{
			data = func(item, subdata, depth);
			if (data!==undefined && subdata!==undefined) 
			{
				data[name] = subdata;
			}
			sink.push(data);
		}
	}
	return sink;
}


/* class/prototype helpers */

function inherits (subClass, baseClass) 
{
   function inheritance() {}
   inheritance.prototype = baseClass.prototype;
   subClass.prototype = new inheritance();
   subClass.prototype.constructor = subClass;
   subClass.baseConstructor = baseClass;
   subClass.superClass = baseClass.prototype;
}
	 	
function extend(destination, source, nochain, nooverwrite) {
	for (var property in source) 
	{
		if (nochain && source.hasOwnProperty(property)) {continue;}
		if (nooverwrite && destination.hasOwnProperty(property)) {continue;}
		var value = source[property];
		destination[property] = value;
	}
	return destination;
}


var userInteraction = false;
/* ############### GUI ############################################ */
function launchTarget(target, isJump) {
	if(userInteraction){
		userInteraction = false;
		return null;
	}
	onItemUndeliver();
	//TODO:JP - need a way to force jump request
	mlaunch = msequencer.navigateStr(target, isJump);
   
	if (mlaunch.mSeqNonContent == null) {
		//throw away API from previous sco and sync CMI and ADLTree
		onItemDeliver(activities[mlaunch.mActivityID], false);
	} else {
	  //call specialpage
	  	loadPage(gConfig.specialpage_url+"&page="+mlaunch.mSeqNonContent);
	}

}
function launchNavType(navType, isUserCurrentlyInteracting) {	
	if(!isUserCurrentlyInteracting && userInteraction){
		userInteraction = false;
		return null;		
	}
	//if suspendAll set cmi.exit to suspend for active SCO
	if (navType=='SuspendAll') {
		err=currentAPI.SetValueIntern("cmi.exit","suspend");
		//sync
		activities[msequencer.mSeqTree.mCurActivity.mActivityID].exit="suspend";
   	}
		
	//throw away API from previous sco and sync CMI and ADLTree, no api...SCO has to care for termination
	onItemUndeliver();
	
	mlaunch = new ADLLaunch();
	if (navType==='Start') {
		mlaunch = msequencer.navigate(NAV_START);
	}
		
	if (navType==='ResumeAll') {
		mlaunch = msequencer.navigate(NAV_RESUMEALL);
	}
	
	if (navType==='Exit') {
		mlaunch = msequencer.navigate(NAV_EXIT);
	}
	
	if (navType==='ExitAll') {
		mlaunch = msequencer.navigate(NAV_EXITALL);
	}
	
	if (navType==='Abandon') {
		mlaunch = msequencer.navigate(NAV_ABANDON);
	}
	
	if (navType==='AbandonAll') {
		mlaunch = msequencer.navigate(NAV_ABANDONALL);
	}
	
	if (navType==='SuspendAll') {
		mlaunch = msequencer.navigate(NAV_SUSPENDALL);
								
		if (typeof headers !== "object") {headers = {};}
		headers['Accept'] = 'text/javascript';
		headers['Accept-Charset'] = 'UTF-8';
		
		//look for tracking
		var acts=msequencer.mSeqTree.mActivityMap;
		var curtracking = new Object();
		var tracking = new Object();
		var states = new Object();
		var root = new Object();
		//save root elements
		for (var element in msequencer) {
			if (!(msequencer[element] instanceof Object)) {
				root[element]=msequencer[element];
			}
		}
		
		for (var element in acts) {
			curtracking[element]=acts[element].mCurTracking;
			tracking[element]=acts[element].mTracking;
			//iterate over other properties
			if (!states[element]) {states[element]=new Object();}
			for (subelement in acts[element]) {
				if (!(acts[element][subelement] instanceof Object) && !(acts[element][subelement] instanceof Array) ) {
					states[element][subelement]=acts[element][subelement];
				}
			}
		}
		
		
		var validreq=msequencer.mSeqTree.mValidReq;
		
		//just IDs
		var lastleaf=msequencer.mSeqTree.mLastLeaf;
		var firstcandidate=msequencer.mSeqTree.mFirstCandidate.mActivityID;
		var suspendall=msequencer.mSeqTree.mSuspendAll.mActivityID;
		var curactivity=msequencer.mSeqTree.mCurActivity.mActivityID;
	
		var suspendedTree= new Object();
		suspendedTree['mCurTracking']=curtracking;
		suspendedTree['mTracking']=tracking;
		suspendedTree['States']=states;
		suspendedTree['mCurActivity']=null;
		suspendedTree['mValidReq']=validreq;
		suspendedTree['mLastLeaf']=lastleaf;
		suspendedTree['mFirstCandidate']=firstcandidate;
		suspendedTree['mSuspendAll']=suspendall;
		suspendedTree['root']=root;
		
		var strTree = JSON.stringify(suspendedTree); //toJSONString(suspendedTree);
		
		var r = sendAndLoad(this.config.suspend_url, strTree, null, null, null, headers);
	}
	
	if (navType==='Previous') {		
		mlaunch = msequencer.navigate(NAV_PREVIOUS);
	}
	
	if (navType==='Continue') {
		mlaunch = msequencer.navigate(NAV_CONTINUE);		
	}
	
	if (mlaunch.mActivityID) {
		onItemDeliver(activities[mlaunch.mActivityID], false);
	} else {
  		//call specialpage
  		loadPage(gConfig.specialpage_url+"&page="+mlaunch.mSeqNonContent);
	}
		
}
function onDocumentClick (e) 
{
	e = new UIEvent(e);
	var target = e.srcElement;
	
	userInteraction = true;
	//integration of ADL Sqeuencer
	
	if (target.tagName !== 'A' || !target.id || target.className.match(new RegExp(ilRTEDisabledClass))
		|| target.className.match(new RegExp('ilc_rte_tlink_RTETreeLinkDisabled')))
	{
		// ignore clicks on other elements than A
		// or non identified elements or disabled elements (non active Activities)
	} 

	//handle eventes like Contine, Previous, Exit...
	else if (target.id.substr(0, 3) ==='nav') 
	{
		var navType=target.id.substr(3);
		launchNavType(navType, userInteraction);						
	} 
	
	//SCO selected by user directly (itm is used as ITEM_PREFIX)
	else if (target.id.substr(0, 3)===ITEM_PREFIX ) 
	{
		if (e.altKey) {} // for special commands
		else 
		{
			//throw away API from previous sco and sync CMI and ADLTree
			//onItemUndeliver();
			mlaunch = msequencer.navigateStr( target.id.substr(3).replace(/_____/g,'.'));

 			if (mlaunch.mSeqNonContent == null) {
				//alert(activities[mlaunch.mActivityID]);
				//throw away API from previous sco and sync CMI and ADLTree
				onItemUndeliver();
				//statusHandler(mlaunch.mActivityID,"completion","unknown");
				onItemDeliver(activities[mlaunch.mActivityID], false);
			//	setTimeout("updateNav()",2000);  //temporary fix for timing problems
			} else {
			  //call specialpage
			  	loadPage(gConfig.specialpage_url+"&page="+mlaunch.mSeqNonContent);
			}
		}
	}
	else if (typeof window[target.id + '_onclick'] === "function")
	{
		window[target.id + '_onclick'](target);
	} 
	else if (target.target==="_blank")
	{
		return;
	}
	userInteraction = false;
	e.stop();
}

// set style class for current state
function setState(newState)
{
	replaceClass(document.body, guiState + 'State', newState + 'State');
	guiState = newState;
}

function loadPage(src) {
	
		//deactivate all controls, if session has ended
		if (mlaunch.mSeqNonContent!="_TOC_" && mlaunch.mSeqNonContent!="_SEQABANDON_" && mlaunch.mSeqNonContent!="_SEQABANDONALL_" ) {
			toggleClass('navContinue', 'disabled', true);
			toggleClass('navExit', 'disabled', true);
			toggleClass('navPrevious', 'disabled', true);
			toggleClass('navResumeAll', 'disabled', true);
			toggleClass('navExitAll', 'disabled', true);
			toggleClass('navStart', 'disabled',true);
			toggleClass('navSuspendAll', 'disabled', true);
			toggleClass('treeToggle', 'disabled', true);
		}
		
		var elm = window.document.getElementById(RESOURCE_PARENT);
		if (!elm) 
		{
			return window.alert("Window Container not found");
		}
		var h = elm.clientHeight-20;
		if (self.innerHeight && navigator.userAgent.indexOf("Safari") != -1) // needed for Webkit based browsers
		{
			h = self.innerHeight-60;
		} 
		
		RESOURCE_NAME= "SPECIALPAGE";
		var resContainer = window.document.getElementById("res");
		resContainer.src=src;
		resContainer.name=RESOURCE_NAME;
		onWindowResize();
		ieForceRender();

		if (treeView==true && mlaunch.mSeqNonContent!="_TOC_" && mlaunch.mSeqNonContent!="_SEQABANDON_" && mlaunch.mSeqNonContent!="_SEQABANDONALL_") {
			toggleView();
		}
		
}


// set info label
function setInfo (name, values) 
{
	var elm = all('infoLabel');
	var txt = translate(name, values);
	if (elm) 
	{
		window.top.document.title = elm.innerHTML = txt;
	}
} 

function setToc() 
{	
	var tree=new Array();
	buildNavTree(rootAct,"item",tree);
}


function updateControls(controlState) 
{
	
	if (mlaunch!=null) {
		toggleClass('navContinue', 'disabled', (mlaunch.mNavState.mContinue==false || ((mlaunch.mAcitivty)?typeof(activities[mlaunch.mActivityID].hideLMSUIs['continue'])=="object":false)));
		toggleClass('navExit', 'disabled', (mlaunch.mNavState.mContinueExit==false || ((mlaunch.mAcitivty)?typeof(activities[mlaunch.mActivityID].hideLMSUIs['exit'])=="object":false)));
		toggleClass('navPrevious', 'disabled', (mlaunch.mNavState.mPrevious==false || ((mlaunch.mAcitivty)?typeof(activities[mlaunch.mActivityID].hideLMSUIs['previous'])=="object":false)));
		toggleClass('navResumeAll', 'disabled', mlaunch.mNavState.mResume==false );
		if (mlaunch.mActivityID) {
			toggleClass('navExitAll', 'disabled', typeof(activities[mlaunch.mActivityID].hideLMSUIs['exitAll'])=="object");
		}	
		toggleClass('navStart', 'disabled', mlaunch.mNavState.mStart==false);
		toggleClass('navSuspendAll', 'disabled', (mlaunch.mNavState.mSuspend==false || ((mlaunch.mAcitivty)?typeof(activities[mlaunch.mActivityID].hideLMSUIs['suspendAll'])=="object":false)));
	}	
}


function setResource() 
{
	var id  = openedResource[0];
	var url = openedResource[1];
	var base= openedResource[2];
	if (url.substring(0,4) != "http") url= base + url;
//IE11 problem
	// if (!top.frames[RESOURCE_NAME])
	// {
		var elm = window.document.getElementById(RESOURCE_PARENT);
		if (!elm) 
		{
			return window.alert("Window Container not found");
		}
		var h = elm.clientHeight-20;
		if (self.innerHeight && navigator.userAgent.indexOf("Safari") != -1) // needed for Webkit based browsers
		{
			h = self.innerHeight-60;
		} 
		var resContainer = window.document.getElementById("res");
		resContainer.src=url;
		resContainer.name=RESOURCE_NAME;
	// } 
	// else 
	// {			
		// open(url, RESOURCE_NAME);
	// } 
	
	onWindowResize();
	ieForceRender();
	//reset
	adlnavreq=false;
	sclogdump("Launched: "+id,"info");
	sclogflush();
}

function removeResource(callback) 
{
	guiItem = all(guiItemId);
	if (guiItem) 
	{
		removeClass(guiItem, "ilc_rte_tlink_RTETreeCurrent");
	}
	var resContainer = window.document.getElementById("res");
	resContainer.src="about:blank";
	resContainer.name=RESOURCE_NAME;
	if (typeof(callback) === 'function') 
	{	
		callback();
	}  
}

function onWindowResize() 
{
	var hd = document.documentElement.clientHeight;
	var hb = document.body.clientHeight;
	if (self.innerHeight && navigator.userAgent.indexOf("Safari") != -1) // needed for Webkit based browsers

	{
		hd = self.innerHeight;
	} 
	var tot = hd ? hd : hb;
	var elm = all(RESOURCE_TOP);
	var hh = (tot-elm.offsetTop-4);
	var h = (tot-elm.offsetTop-4) + 'px';
	elm = all("treeView");
	var factor=1;
	if (logState==true) {factor=0.7;}
	if (elm) 
	{
		//elm.style.height = h;
		elm.style.height = (hh*factor -30 ) + "px";
	}
	elm = all("ilLog");
	
	
	if (elm) 
	{
		if (logState==true) {
			elm.style.height = (hh*0.3) + "px";
		} else {
			elm.style.height ="0px";
		}	
	}
	
	elm = all("res");
	if (elm) 
	{
		elm.style.height = h;
	}
	var tbh = $('#toolbar').outerHeight();
	if (document.getElementById("toolbar").style.display == "none") tbh=0;
	$('#leftView').css('top', tbh + "px");
	$('#dragbar').css('top', tbh + "px");
	$('#tdResource').css('top', tbh + "px");
}

function ieForceRender() {
	if(this.config.ie_force_render && ((navigator.userAgent.indexOf("MSIE") > -1 && navigator.userAgent.indexOf("MSIE 6") == -1) || navigator.userAgent.indexOf("like Gecko") > -1)) {
		window.setTimeout("window.resizeBy(1, 1)",10000);
		window.setTimeout("window.resizeBy(-1, -1)",10010);
		window.setTimeout("window.resizeBy(1, 1)",20000);
		window.setTimeout("window.resizeBy(-1, -1)",20010);
	}
}

function buildNavTree(rootAct,name,tree){
	
	// new implementation
	il.NestedList.addList('rte_tree', {ul_class: 'ilc_rte_tul_RTETreeList',
		li_class: 'ilc_rte_tli_RTETreeItem', exp_class: 'ilc_rte_texp_RTETreeExpanded',
		col_class: 'ilc_rte_texp_RTETreeCollapsed'});
	
	var par_id = 0;
	//root node only when in TOC
	if (mlaunch.mNavState.mChoice!=null)
	{
		var id=rootAct.id;
		if (rootAct.isvisible==true && typeof(mlaunch.mNavState.mChoice[id])=="object") {
			var it_id=(ITEM_PREFIX + rootAct.id).replace(/\./g,"_____");
			il.NestedList.addNode('rte_tree', (""+par_id).replace(/\./g,"_____"), it_id,
				"<a href='#this' id='" + it_id + "' target='_self'>" + rootAct.title + "</a>",
				true);
			par_id = ITEM_PREFIX + rootAct.id;
		}	
	}

	function build2(rootAct, par_id){
		if (rootAct.item) {
			for (var i=0;i<rootAct.item.length;i++) {
				//only include if visible
				var id=rootAct.item[i].id;
				if (mlaunch.mNavState.mChoice!=null) {
					if (rootAct.item[i].isvisible==true && typeof(mlaunch.mNavState.mChoice[id])=="object") {
						var it_id=(ITEM_PREFIX + rootAct.item[i].id).replace(/\./g,"_____");
						il.NestedList.addNode('rte_tree', (""+par_id).replace(/\./g,"_____"), it_id,
							"<a href='#this' id='" + it_id + "' target='_self'>" + rootAct.item[i].title + "</a>",
							true);
						var next_par_id = ITEM_PREFIX + rootAct.item[i].id;
					}	
				}
				//further childs
				if(rootAct.item[i].item) {
					build2(rootAct.item[i], next_par_id);
				}
			}
		}	
	}
	
	build2(rootAct, par_id);
	
	$("#treeView").empty();
	il.NestedList.draw('rte_tree', 0, 'treeView');
}




function abortNavigation () 
{
	state = ABORTING; 
}




/* ########### PLAYER loading and TRACKER ####################################### */

function init(config) 
{
	function camWalk(cam, act) 
	{
		function move(act, prop, newprop, id) 
		{
			var k;
			var cls = this[prop.charAt().toUpperCase()+prop.substr(1)] || Object;
			if (!act[prop]) {return;}
			while ((k = act[prop].pop()))
			{
				var subact = new cls();
				act[newprop][k[id] ? k[id] : '$'] = subact;
				for (var kk in k) 
				{
					//subact[kk] = k[kk];
					setItemValue(kk, subact, k);
				}
			}
			delete act[prop];
		}
		
		var k, i, ni, seq, v;
		seq = cam.sequencingId in seqs ? seqs[cam.sequencingId] : {};
		for (k in seq) 
		{
			setItemValue(k, act, seq);
		}
		for (k in cam) 
		{
			setItemValue(k, act, cam);
		}
		act.index = activitiesByNo.length;
		activitiesByNo.push(act);
		act.cp_node_id = act.foreignId;
		activitiesByCAM[act.foreignId] = act;
		activities[act.id] = act;
		if (cam.item) 
		{
			act.item = new Array();
			var availableChildren = [];
			for (i=0, ni=cam.item.length; i<ni; i+=1) 
			{
				var subact = new Activity();
				subact.parent = act; 
				camWalk(cam.item[i], subact);
				availableChildren.push(subact);
				act.item.push(subact);
			}
			act.availableChildren = availableChildren;
		}
		move(act, "objective", "objectives", "objectiveID");
		move(act, "hideLMSUI", "hideLMSUIs", "value");
		move(act, "rule", "rules", "foreignId");
		act.primaryObjective = act;
		for (k in act.objectives) 
		{
			move(act.objectives[k], "mapinfo", "mapinfos", "targetObjectiveID");
			for (var l in act.objectives[k].mapinfos) 
			{
				var dat = sharedObjectives[l];
				if (!dat) 
				{
					dat = new Objective();
					dat.id = l;
					dat.cmi_node_id = globalAct.cmi_node_id;
					sharedObjectives[l] = dat;
				}
			}
			// if we find a primaryObjective overwrite reference to activity as data container
			if (act.objectives[k].primary) 
			{
				act.primaryObjective = act.objectives[k];
			}
		}
	}


	try {config.cmi_data = init_cmi_data;} catch (e) {}
	try {config.cp_data = init_cp_data;} catch (e) {}
	try {config.adlact_data = init_adlact_data;} catch (e) {}
	try {config.globalobj_data = init_globalobj_data;} catch (e) {}
	try {
		delete init_cmi_data;
		delete init_cp_data;
		delete init_adlact_data;
		delete init_globalobj_data;
	} catch (e) {
		init_cmi_data = {};
		init_cp_data = {};
		init_adlact_data = {};
		init_globalobj_data = {};
	}
	this.config = config;
	gConfig=config;
	setInfo('loading');
	setState('loading');
	
	setLocalStrings( // define these strings in host localization table
	{
		'resource_undelivered' : 'Resource unloaded. Use navigation to load a new one.'
	});
	setLocalStrings(this.config.langstrings);
	setTimeout(onWindowLoad, 0);
		
	// Step 1: load manifest data
	
	var cam = this.config.cp_data || sendJSONRequest(this.config.cp_url);

	if (!cam) return alert('Fatal: Could not load content data.');
	
	//if(this.config.sequencing_enabled==false) modify sequencing
	
	// Step 2: load adlActivityTree
	function defaultAct(mActivityID,mTitle,mOrder,mActiveOrder,mChildren,mActiveChildren) {
		return {
			"_SeqActivity":{
				"mPreConditionRules":null,
				"mPostConditionRules":null,
				"mExitActionRules":null,
				"mXML":null,
				"mDepth":0,
				"mCount":-1,
				"mLearnerID":"_NULL_",
				"mScopeID":null,
				"mActivityID":mActivityID,
				"mResourceID":null,
				"mStateID":null,
				"mTitle":mTitle,
				"mIsVisible":true,
				"mOrder":mOrder,
				"mActiveOrder":mActiveOrder,
				"mSelected":true,
				"mParent":null,
				"mIsActive":false,
				"mIsSuspended":false,
				"mChildren":mChildren,
				"mActiveChildren":mChildren,
				"mDeliveryMode":"normal",
				"mControl_choice":true,
				"mControl_choiceExit":true,
				"mControl_flow":false,
				"mControl_forwardOnly":false,
				"mConstrainChoice":false,
				"mPreventActivation":false,
				"mUseCurObj":true,
				"mUseCurPro":true,
				"mMaxAttemptControl":false,
				"mMaxAttempt":0,
				"mAttemptAbDurControl":false,
				"mAttemptAbDur":null,
				"mAttemptExDurControl":false,
				"mAttemptExDur":null,
				"mActivityAbDurControl":false,
				"mActivityAbDur":null,
				"mActivityExDurControl":false,
				"mActivityExDur":null,
				"mBeginTimeControl":false,
				"mBeginTime":null,
				"mEndTimeControl":false,
				"mEndTime":null,
				"mAuxResources":null,
				"mRollupRules":null,
				"mActiveMeasure":true,
				"mRequiredForSatisfied":"always",
				"mRequiredForNotSatisfied":"always",
				"mRequiredForCompleted":"always",
				"mRequiredForIncomplete":"always",
				"mObjectives":null,
				"mObjMaps":null,
				"mIsObjectiveRolledUp":true,
				"mObjMeasureWeight":1,
				"mIsProgressRolledUp":true,
				"mSelectTiming":"never",
				"mSelectStatus":false,
				"mSelectCount":0,
				"mSelection":false,
				"mRandomTiming":"never",
				"mReorder":false,
				"mRandomized":false,
				"mIsTracked":true,
				"mContentSetsCompletion":false,
				"mContentSetsObj":false,
				"mCurTracking":null,
				"mTracking":null,
				"mNumAttempt":0,
				"mNumSCOAttempt":0,
				"mActivityAbDur_track":null,
				"mActivityExDur_track":null
			}
		};
	}

	var adlAct = {};
	if (this.config.sequencing_enabled){
		adlAct = this.config.adlact_data || sendJSONRequest(this.config.adlact_url);
	}
	else {
		adlAct=defaultAct(cam.item.id,cam.item.title,-1,-1,[],[]);
		for (var j=0; j<cam.item.item.length; j++){
			adlAct._SeqActivity.mChildren[j]=defaultAct(cam.item.item[j].id,cam.item.item[j].title,j,j,null,null);
		}
	}

	if (!adlAct) {
		
		return alert('Fatal: Could not load ADLActivityTree.');
		
	} else {	
		
		var tree;
		
		adlTree = buildADLtree(adlAct,tree);
		//set parents
	    adlTree= setParents(adlTree);
		//assign Tree
		//scope equals courseID 
		var actTree = new SeqActivityTree(this.config.course_id,this.config.learner_id,this.config.scope,adlTree);

		actTree.setDepths();
		actTree.setTreeCount();		
		
		actTree.scanObjectives();
		actTree.buildActivityMap();
		
		msequencer.setActivityTree(actTree);	
		
	}		
	
	// convert seq array into seq map and decode seq data en passant
	var seqs = cam.sequencing ? cam.sequencing  : [];
	for (var i=seqs.length; i--;)
	{
		seq = seqs.pop();
		seqs[seq.id] = seq;
		delete seq.foreignId;
	}

	// resolve one step inheritance in sequencing
	for (var k in seqs) 
	{
		seq = seqs[k];
		if (seq.sequencingId) 
		{
			var baseseq = seqs[seq.sequencingId];
			for (k in baseseq) 
			{
				if (seq[k]===undefined) 
				{
					seq[k] = baseseq[k];
				}
			}
			delete seq.id;
			delete seq.sequencingId;
		}
	}

	// copy data from manifest into globalAct
	for (k in cam) 
	{
		if (typeof cam[k] !== "object") 
		{
			globalAct[k] = cam[k];
		}
	}
	
	// identifiy cp_node for saving global activity into cmi_node
	// and add global activity to list of activities
	globalAct.cp_node_id = globalAct.foreignId;
	globalAct.index = activitiesByNo.length;
	activitiesByNo.push(globalAct);
	activitiesByCAM[globalAct.foreignId] = globalAct;
	activities[globalAct.id] = globalAct;
	
	//set data from LMS
	globalAct.learner_id=this.config.learner_id;
	globalAct.learner_name=this.config.learner_name;
	globalAct.auto_review = this.config.auto_review;
	
	// walk throught activities and add some helpful properties
	camWalk(cam.item, rootAct);
		

	// Step 2: load tracking data
	load();
	
	
	//load global objectives
	if (this.config.sequencing_enabled) loadGlobalObj();
	//debugger start with sco-start
	logActive = this.config.debug;
	//get suspend data
	suspendData=null;
	if (this.config.sequencing_enabled) suspendData = this.config.suspend_data;//sendJSONRequest(this.config.get_suspend_url);
	var wasSuspended=false;
	var wasFirstSession;
	if (suspendData) {
		if (suspendData!=null) {
			wasSuspended=true;
		}
	}
	
	if (wasSuspended==true) {
		wasSuspended = true;
        wasFirstSession = false;
		for (var element in suspendData.mTracking) {
			msequencer.mSeqTree.mActivityMap[element].mTracking=suspendData.mTracking[element];
		}
		
		//cur
		var cur=suspendData.mCurActivity;
		msequencer.mSeqTree.mCurActivity=null;
		
		var first=suspendData.mFirstCandidate;
		msequencer.mSeqTree.mFirstCandidate=null;
		
		msequencer.mSeqTree.mLastLeaf=suspendData.mLastLeaf;
		var suspendAll=suspendData.mSuspendAll;
		msequencer.mSeqTree.mSuspendAll=msequencer.mSeqTree.mActivityMap[suspendAll];
		var valid=suspendData.mValidReq;
		msequencer.mSeqTree.mValidReq=valid;
		
		
		
		//set root
		for (var element in suspendData.root) {
			msequencer[element]=suspendData.root[element];
		}
		//set states
		for (var element in suspendData.States) {
			//collect data
			var source=suspendData.States[element];
			for (var subelement in source) {
				msequencer.mSeqTree.mActivityMap[element][subelement]=source[subelement];
			}
		}
		
		//set curtracking
		//lets scan first an assign then
		var tempCur=new Object();
		for (var element in suspendData.mCurTracking) {
			tempCur[element]=new ADLTracking;
			for (var subelement in suspendData.mCurTracking[element]) {	
				if (subelement!="mObjectives") {
					tempCur[element][subelement]=suspendData.mCurTracking[element][subelement];
				} else {
					for (var obj in suspendData.mCurTracking[element]["mObjectives"] ) {
						tempCur[element]["mObjectives"][obj]=new SeqObjectiveTracking();
						//iterate over prop
						for (var prop in suspendData.mCurTracking[element]["mObjectives"][obj]) {
							tempCur[element]["mObjectives"][obj][prop]=suspendData.mCurTracking[element]["mObjectives"][obj][prop];
						}
					}
				}
			}
		}
		
		for (var element in tempCur) {
			//collect data
				msequencer.mSeqTree.mActivityMap[element]["mCurTracking"]=tempCur[element];
		}
		
	}	
	
	initStatusArray();
	
	if (wasSuspended==true) {
	 	mlaunch = msequencer.navigate(NAV_RESUMEALL);
	} else {
		//do a fake launch to check if TOC choice should be displayed
		mlaunch = msequencer.navigate(NAV_NONE);
	
		if (mlaunch.mNavState.mStart) {
			//launch first activity //assume course has not be launched before
			mlaunch = msequencer.navigate(NAV_START);
		} 
	}
	
	var tolaunch=null;
	var count=0;
	
	for (var myitem in mlaunch.mNavState.mChoice) {
		if (mlaunch.mNavState.mChoice[myitem].mInChoice==true && mlaunch.mNavState.mChoice[myitem].mIsSelectable==true && mlaunch.mNavState.mChoice[myitem].mIsEnabled==true) {
			tolaunch=mlaunch.mNavState.mChoice[myitem].mID;
			count=count+1;
		}
	}
	if (count==1 || this.config.hide_navig == 1) {
		toggleView();  //hide tree
	}

	if (config.auto_last_visited==true && config.status.last_visited!=null) {
		launchTarget(config.status.last_visited);
	} else {

		if (mlaunch.mSeqNonContent == null) {
			onItemDeliver(activities[mlaunch.mActivityID], wasSuspended);
		} else {
			if (count==1 && tolaunch!=null) {
				launchTarget(tolaunch);
			} else {
				loadPage(gConfig.specialpage_url+"&page="+mlaunch.mSeqNonContent);	
				updateControls();
				updateNav();
			}
		}
	}

	if (logActive==true) {
		var elm = all("toggleLog");
		elm.style.display ="inline";
	}
	if (this.config.session_ping>0)
	{
		setTimeout("pingSession()", this.config.session_ping*1000);
	}

}

//used for visual tree feedback
function initStatusArray() {
	for (element in msequencer.mSeqTree.mActivityMap) {
		statusArray[element] = new Object();
		statusArray[element]['completion'] = null;
		statusArray[element]['success'] = null;
	}
}

function statusHandler(scoID, type,status) {
	statusArray[scoID][type] = status;
}

function pingSession() 
{
	var r = sendJSONRequest(this.config.ping_url);
	//repeat timer
	setTimeout("pingSession()", this.config.session_ping*1000);
}


function loadGlobalObj() {
	var globalObj =  this.config.globalobj_data || sendJSONRequest(this.config.get_gobjective_url);
	if (globalObj) {
		if (typeof globalObj.satisfied != "undefined"){adl_seq_utilities.satisfied=globalObj.satisfied;}
		if (typeof globalObj.measure != "undefined") {adl_seq_utilities.measure=globalObj.measure;}
		if (typeof globalObj.status != "undefined") {adl_seq_utilities.status=globalObj.status;}
	}
}

function loadSharedData(sco_node_id) {
	var adlData = this.config.adldata_data || sendJSONRequest(this.config.get_adldata_url + "&node_id=" + sco_node_id);
	if(adlData) {
		sharedData = adlData;
	}
}

function saveSharedData(cmiItem) {
	//to increase performance integrate  in save()
	//Do the transform so the "custom" JSON encoder will properly send
	//the input to the server
	var dataOut = new Object();
//	for(i = 0; i < pubAPI.adl.data.length; i++) {
//		var d = pubAPI.adl.data[i];
	for(i = 0; i < cmiItem.adl.data.length; i++) {
		var d = cmiItem.adl.data[i];
		dataOut[d.id] = d.store;
	}
	sd2save = toJSONString(dataOut);
	if (sd2save != saved_shared_data) {
//		var success = sendJSONRequest(this.config.set_adldata_url+"&node_id="+pubAPI.cmi.cp_node_id, dataOut);
		var success = sendJSONRequest(this.config.set_adldata_url+"&node_id="+cmiItem.cmi.cp_node_id, dataOut);
		if(success != "1") { 
			return false;
		}
		saved_shared_data = sd2save;
	}
	return true;
}

function buildADLtree(act, unused){
	var obj=new Object;
	var obj1, res, res2;
	for(var index in act) {
		var value;
   		if ((index.substr(0,1) == "_") ) {
			//create new object
			obj = eval("new "+index.substr(1)+"()");
			obj1 = buildADLtree(act[index],null);
			for(var i in obj1){
				obj[i]=obj1[i];
			}
		} else if ((act[index] instanceof Array)) {
			var toset=new Array();
			var temp=act[index];
			for (var i=0;i<temp.length;i++) {
				res=buildADLtree(temp[i],null);
				toset.push(res);
			}
			if (index!="mActiveChildren") {
				obj[index] = toset;
			}
			//keep trees in sync
			if (index=="mChildren") {
				obj["mActiveChildren"]=toset;
			}
		} else if ((act[index] instanceof Object)){
			//handle object
			res2=buildADLtree(act[index],null);
			obj[index] = res2;
		} else if (!(act[index] instanceof Array) && !(index.substr(0,1) == "_")){
			value = act[index];
			//set learner id and course id
			if (index == "mLearnerID") {value = this.config.learner_id;}
			if (index == "mScopeID") {value = this.config.scope;}
			obj[index] = value;
		}
    }
	return obj;
}


function setParents(obj) {
	for(var index in obj) { 
		if (index == "mChildren") {
			var temp=obj[index];	
			if (temp instanceof Array) {
				if (temp.length>0) {
					for (var i=0;i<temp.length;i++) {
						// get the object
						temp[i]['mParent']=obj;
						//check for further childs in array
					    var ch=setParents(temp[i]);
						temp[i]=ch;
					}
				}
			}	
		}
	
	}
	return obj;
}




function load()
{
	// optionally add parameters for loading level 1 or level 2 data only
	
	var cmi = this.config.cmi_data || sendJSONRequest(this.config.cmi_url);
	
	if (!cmi) return alert('FATAL: Could not load userdata!');
	
	var k, i, ni, row, act, j, nj, dat, id;
	var cmi_node_id, cmi_interaction_id;
	
	if (!remoteMapping) 
	{
		remoteMapping = cmi.schema;
		for (k in remoteMapping) 
		{
			for (i=remoteMapping[k].length; i--; )
			{
				remoteMapping[k][remoteMapping[k][i]] = i;
			}
		}
		while ((row = cmi.data['package'].pop()))
		{
			for (i=remoteMapping['package'].length; i--; )
			{
				globalAct[remoteMapping['package'][i]] = row[i];
			}
//			globalAct.learner_id = globalAct.user_id; //TODO UK check
		}
	}
	
	for (i=cmi.data.node.length; i--; )
	{
		row = cmi.data.node[i];
		act = activitiesByCAM[row[remoteMapping.node.cp_node_id]];
		for (j=remoteMapping.node.length; j--; ) 
		{
			if (row[j]===null) {continue;}
			//act[remoteMapping.node[j]] = row[j];
			setItemValue(j, act, row, remoteMapping.node[j]);
		}
		activitiesByCMI[act.cmi_node_id] = act;
	}
	for (i=0;i<cmi.data.comment.length; i++)
	{
		row = cmi.data.comment[i];
		dat = new Comment();
		for (j=remoteMapping.comment.length; j--; ) 
		{
			//dat[remoteMapping.comment[j]] = row[j];
			setItemValue(j, dat, row, remoteMapping.comment[j]);
		}
		act = activitiesByCMI[row[remoteMapping.comment.cmi_node_id]];
		act.comments[dat.cmi_comment_id] = dat;
	}
	
	var interactions = {};	
	for (i=cmi.data.interaction.length; i--; )
	{
	    row = cmi.data.interaction[i];
	    dat = new Interaction();
	    for (j=remoteMapping.interaction.length; j--; )
	    {
	        //dat[remoteMapping.interaction[j]] = row[j];
	        setItemValue(j, dat, row, remoteMapping.interaction[j]);
	    }
	    act = activitiesByCMI[row[remoteMapping.interaction.cmi_node_id]];
	    act.interactions[dat.cmi_interaction_id] = dat;
	    interactions[dat.cmi_interaction_id] = dat;
	}
	
	for (i=cmi.data.correct_response.length; i--; )
	{
		row = cmi.data.correct_response[i];
		dat = new CorrectResponse();
		for (j=remoteMapping.correct_response.length; j--; ) 
		{
			//dat[remoteMapping.correct_response[j]] = row[j];
			setItemValue(j, dat, row, remoteMapping.correct_response[j]);
		}
		act = interactions[row[remoteMapping.correct_response.cmi_interaction_id]];
		act.correct_responses[dat.cmi_correct_response_id] = dat;//s
	}
	for (i=0;i<cmi.data.objective.length; i++)
	{
		row = cmi.data.objective[i];
		id = row[remoteMapping.objective.id];
		cmi_interaction_id = row[remoteMapping.objective.cmi_interaction_id];
		cmi_node_id = row[remoteMapping.objective.cmi_node_id];
		if (cmi_interaction_id===null || cmi_interaction_id==0) // objective to an activity or shared
		{
			act = activitiesByCMI[cmi_node_id];
			if (act && act.objectives[id]) // local objective specified in manifest
			{
				dat = act.objectives[id];
			}
			else if (act) // local objective of private use in sco
			{
				dat = new Objective();
				 act.objectives[id] = dat;
			}
			else if (sharedObjectives[id]) // shared objective
			{
				dat = sharedObjectives[id];
			}
			// copy data into internal structure
			for (j=remoteMapping.objective.length; j--; ) 
			{
				if (typeof dat !="undefined"){
					dat[remoteMapping.objective[j]] = row[j];
				}	
			}
			dat = new Objective();
			for (j=remoteMapping.objective.length; j--; )
			{
				//dat[remoteMapping.objective[j]] = row[j];
				setItemValue(j, dat, row, remoteMapping.objective[j]);
			}
			act = activitiesByCMI[row[remoteMapping.objective.cmi_node_id]];
			act.objectives[dat.id] = dat;
		}
		else // objective id to an interaction
		{
			interactions[cmi_interaction_id].objectives[id] = {id:id};
		}
	}

}

function save()
{
	// optionally add parameters for save level 1 or level 2 data only
	function walk(collection, type) 
	{
		var schem = remoteMapping[type];
		var res = result[type];
		for (var k in collection) 
		{
			var item = collection[k];
			if(type=="node"){//notracking!
				for(j=0;j<config.status.scos.length;j++) {
					if (config.status.scos[j] == item['cp_node_id']) {
						if (item.success_status == "failed") b_statusFailed=true;
						else if (item.completion_status == "completed" || item.success_status == "passed") i_numCompleted++;
					}
				}
				totalTimeCentisec+=ISODurationToCentisec(item.total_time);
			}
			if (item.dirty===0)  {continue;}
			if (item.options) {
				if (item.options.notracking === true) 
				{
					b_statusUpdate = false;
					continue;
				}
			}
			 if(type=="node") item.dirty=0;//notice as in progress to be saved
			if (type == "objective") {
				if (item.id == null) {
					continue;
				}
			}
			var data = [];
			for (var i=0, ni=schem.length; i<ni; i++) 
			{
				data.push(item[schem[i]]);
			}
			res.push(data);
			
			for (z in collection[k])
			{
				if (
					(this.config.interactions_storable && (z == 'interactions' || z == "correct_responses")) 
					|| (this.config.comments_storable && z == 'comments') 
					|| (this.config.objectives_storable && z == "objectives") 
					)
				{
					
					for (y in collection[k][z])
					{
						var valid = true;
						if (z=="objectives") {
							if (collection[k][z][y]['id'] == null) {
								valid = false;
							}
						}
						if (valid) {
							collection[k][z][y]['cmi_node_id']=collection[k]['cmi_node_id'];
							if(collection[k]['cmi_interaction_id']) collection[k][z][y]['cmi_interaction_id']=collection[k]['cmi_interaction_id'];
						}
						//
						if (z=="correct_responses") collection[k][z][y]['cmi_interaction_id']=collection[k]['cmi_interaction_id'];
						//
					}
					walk(collection[k][z],z.substr(0,z.length-1));
				}
			}
			if (item.dirty!==2 && type=="node") {continue;}
		}
	}
	var b_statusFailed=false, i_numCompleted=0, b_statusUpdate=true, totalTimeCentisec=0;
	var result = {};
	for (var k in remoteMapping) 
	{
		result[k] = [];
	}
	// add shared objectives
	walk (sharedObjectives, "objective");
	// add activities
	walk (activities, 'node');

	result["i_check"]=0;
	result["i_set"]=0;
	var check0="",check1="";
	for (var k in saved) {
		if (result[k].length>0) {
			result["i_check"]+=saved[k].checkplus;
			check0=toJSONString(result[k]);
			check1=toJSONString(saved[k].data);
			if (k=="correct_response") {
				check0+=result["node"][0][15];
				check1+=saved[k].node;
			}
			if (check0===check1) {
				result[k]=[];
			} else {
				saved[k].data=result[k];
				if(k=="correct_response") saved[k].node=result["node"][0][15];
				result["i_set"]+=saved[k].checkplus;
			}
		}
	}


	if (this.config.sequencing_enabled) {

		msequencer.getCourseStatusByGlobalObjectives();

		result["adl_seq_utilities"]=this.adl_seq_utilities;
		if (saved_adl_seq_utilities != toJSONString(this.adl_seq_utilities)) {
			saved_adl_seq_utilities = toJSONString(this.adl_seq_utilities);
			result["changed_seq_utilities"]=1;
		}
		else {
			result["changed_seq_utilities"]=0;
		}
	} else {
		result["adl_seq_utilities"]={};
		result["changed_seq_utilities"]=0;
	}
	var LP_STATUS_IN_PROGRESS_NUM=1, LP_STATUS_COMPLETED_NUM=2,LP_STATUS_FAILED_NUM=3;
	var percentageCompleted=0;
	var now_global_status = LP_STATUS_IN_PROGRESS_NUM;
	if (config.status.lp_mode == 6) { //distinct scos selected
		if (b_statusFailed == true) now_global_status = LP_STATUS_FAILED_NUM;
		else if (config.status.scos.length == i_numCompleted) now_global_status = LP_STATUS_COMPLETED_NUM;
		percentageCompleted=Math.round(i_numCompleted*100/config.status.scos.length);
	}
	else if (config.status.lp_mode == 12) {
		var measure=this.adl_seq_utilities.status[this.config.course_id][this.config.learner_id]["measure"];
		var satisfied=this.adl_seq_utilities.status[this.config.course_id][this.config.learner_id]["satisfied"];
		var completed=this.adl_seq_utilities.status[this.config.course_id][this.config.learner_id]["completed"];
		if (completed=="completed" || satisfied=="satisfied") now_global_status = LP_STATUS_COMPLETED_NUM;
		if (satisfied=="notSatisfied") now_global_status = LP_STATUS_FAILED_NUM;
		if(!isNaN(measure)) percentageCompleted=Math.round(measure*100);
	}
	if (b_statusUpdate == false) now_global_status=config.status.saved_global_status;
	result["saved_global_status"]=config.status.saved_global_status;
	result["now_global_status"]=now_global_status;
	result["percentageCompleted"]=percentageCompleted;
	result["lp_mode"]=config.status.lp_mode;
	result["hash"]=config.status.hash;
	result["p"]=config.status.p;
	result["totalTimeCentisec"]=totalTimeCentisec;
	var to_saved_result = toJSONString(result);
	if (saved_result == to_saved_result) {
		//updateNavForSequencing();
		return true;
	} else {
//		alert("difference: saved_result:\n"+saved_result+"\nresult:\n"+toJSONString(result));
		//alert("Before save "+result.node.length);
		//if (!result.node.length) {return;} 
		if (typeof SOP!="undefined" && SOP==true) result=saveRequest(result);
		else result = this.config.store_url ? sendJSONRequest(this.config.store_url, result): {};
		
		// added to synchronize the new data. it might update the navigation
		updateNavForSequencing();

		// set successful updated elements to clean
		if(typeof result == "object") {
			saved_result = to_saved_result;
			var new_global_status = null;
			for (k in result) {
				if(k == "new_global_status") new_global_status=result[k];
			}

			//sychronize status
			if (config.status.saved_global_status != new_global_status) {
				try{windowOpenerLoc.reload();} catch(e){}
			}
			
			config.status.saved_global_status = new_global_status;
			return true;
		}
	}
	return false;
}

function getAPI(cp_node_id) 
{
	function getAPISet (k, dat, api) 
	{
		if (typeof dat!="undefined" && dat!==null) 
		{
			api[k] = dat.toString();
		}
	}
	
	function getADLExtensionWalk(model, data, api)
	{
		var k, i;
		if (!model.children) return;
	}
	
	function getAPIWalk (model, data, api) 
	{
		var k, i;
		if (!model.children) return;
		for (k in model.children) 
		{
			var mod = model.children[k];
			var dat;
			if (data!=null) {
				//special mapping for comments
				if (k == "comments_from_learner" || k == "comments_from_lms") {
					dat = data['comments'];
				} else {
					dat = data[k];
				}
			} else {
				dat=null;
			}
			if (mod.type===Object) 
			{
				api[k] = {};
				
				for (var i=mod.mapping.length; i--;)
				{
					//TODO include in recursion
					if (k=="score") {
							var d=new Object();
							d['scaled']=data['scaled'];
							d['raw']=data['raw'];
							d['min']=data['min'];
							d['max']=data['max'];
							
							api[k]=d;
					
					} else {
						getAPISet(mod.mapping[i], dat, api[k]);
					}
				}
			}
			else if (mod.type===Array) 
			{
				
				api[k] = [];
				
			//	sclogdump(dat);
				if (mod.mapping) 
				{
				//TODO-this needs a fix!!!
					//dat = data[mod.mapping.name];
				}
				for (i in dat) 
				{
					if (mod.mapping && !mod.mapping.func(dat[i])) continue;
					var d = getAPIWalk(mod, dat[i], {});
					var idname;
					if (k == "comments_from_learner" || k == "comments_from_lms") {
						idname = "cmi_comment_id";
					} else {
						idname = 'cmi_'+ k.substr(0, k.length-1) + '_id';						
					}
					//TODO include in recursion
					if (dat[i]['scaled']) {
						d['score']['scaled']=dat[i]['scaled'];
					}
					if (dat[i]['max']) {
						d['score']['max']=dat[i]['max'];
					}
					if (dat[i]['min']) {
						d['score']['min']=dat[i]['min'];
					}
					if (dat[i]['raw']) {
						d['score']['raw']=dat[i]['raw'];
					}
					if (dat[i]['objectiveID']) {
						d['id'] = dat[i]['objectiveID'];
					} else {
						d[idname] = dat[i][idname];
					}	
					api[k].push(d);	
									
				}
			}
			else 
			{
				getAPISet(k, dat, api);
			}
		}
		return api;
	}
	
	// create api data element with some starting values
	var api = {cmi:{}, adl:{}};
	
	// reference to live data
	var data = activitiesByCAM[cp_node_id];

	//set data=null to null if not suspend
	
	// start recursive process to add current cmi subelements
	getAPIWalk(Runtime.models.cmi.cmi, data, api.cmi);

	return api;
	
}

function setItemValue (key, dest, source, destkey) 
{
	if (source && source.hasOwnProperty(key)) 
	{
		var d = source[key];
		var temp=d;
		if (d!="" && !isNaN(Number(d)) && (/^-?\d{1,32}(\.\d{1,32})?$/.test(d))) {
			d = Number(d);
		} else if (d==="true") {
			d = true;
		} else if (d==="false") {
			d = false;
		}
		//special handling for titles - no conversion
		if (key == "title") {
			d=temp;
		}
		dest[destkey ? destkey : key] = d;
	}
}

function setAPI(cp_node_id, api) 
{

	function setAPIWalk (model, data, api) 
	{
		var k, i;
		if (!model.children) return;
		for (k in model.children) 
		{
			var mod = model.children[k];
			var ap = api[k];
			if (mod.type===Object) // activity.SCORE.max
			{
				for (var i=mod.mapping.length; i--;)
				{
					setItemValue(mod.mapping[i], data, ap);
				}
			}
			else if (mod.type===Array) 
			{
				var map = mod.mapping || {name : k.substr(0, k.length-1)};
				map.dbtable = map.name + "s"; 
				map.dbname = 'cmi_'+ map.name + '_id';
				map.clsname = map.name.charAt().toUpperCase() + map.name.substr(1);
				for (i in ap) 
				{
					var dat = data[map.dbtable];
					var row = ap[i];
					if (map.refunc)  
					{
						var remap = map.refunc(dat[i]);
						row[remap[0]] = remap[1];
					}
					if ((typeof row[map.dbname]=="undefined" || !row[map.dbname])) row[map.dbname]=i;
					var id = row[mod.unique] || row[map.dbname];
					var cls = this[map.clsname] || Object;
					if (!dat[id]) 
					{
						dat[id] = new cls;
					}
					setAPIWalk(mod, dat[id], row);
				}
			}
			else 
			{
				setItemValue(k, data, api);
			}
		}
	}
	
	// reference to live data
	var data = activitiesByCAM[cp_node_id];

	// start recursive process to add current cmi subelements
	setAPIWalk(Runtime.models.cmi.cmi, data, api.cmi);
	
	data.dirty = 2;
	
	return true;
	
}


function dirtyCount() 
{
	var c = 0;
	for (var i=activities.length; i--; ) 
	{
		c += Number(activities[i].dirty); 
	}
	return c;
}


/* ########### PLAYER events ####################################### */

function onWindowLoad () 
{ 
	//dummy - most code removed, cause of sync problems with init method
	//finishing 
	// Hook core events
	if (typeof SOP!="undefined" && SOP==true) {
		attachUIEvent(window, 'beforeunload', onWindowUnload);
	} else {
		attachUIEvent(window, 'unload', onWindowUnload);
	}
	attachUIEvent(document, 'click', onDocumentClick);
	
	/*
	// Show Tree and Controls
	setToc(getTocData(), this.config.package_url);
	*/
	
	// Finishing startup
	setInfo('');
	setState('playing');
	attachUIEvent(window, 'resize', onWindowResize);
	onWindowResize();
}

function onWindowUnload () 
{
	summaryOnUnload = true;
	var result = {};
	result["hash"]=config.status.hash;
	result["p"]=config.status.p;
	result["last"]="";
	if (config.auto_last_visited==true) result["last"]=activities[mlaunch.mActivityID].id;
	if (typeof SOP!="undefined" && SOP==true) result=scormPlayerUnload(result);
	else result=this.config.scorm_player_unload_url ? sendJSONRequest(this.config.scorm_player_unload_url, result): {};
	removeResource();

	//try{windowOpenerLoc.reload();} catch(e){}
}

function onItemDeliver(item, wasSuspendAll) // onDeliver called from sequencing process (deliverSubProcess)
{
	var url = item.href, v;
	// create api if associated resouce is of adl:scormType=sco
	if (item.sco)
	{

		// get data in cmi-1.3 format
		var data = getAPI(item.foreignId);
		if (this.config.fourth_edition) loadSharedData(item.cp_node_id);
		
		// add ADL Request namespace data
		data.adl = {nav : {request_valid: {}}};
		
		var validRequests=msequencer.mSeqTree.getValidRequests();
		
		//we only set Continue, Previous and Choice according to specification
		data.adl.nav.request_valid['continue']=String(validRequests['mContinue']);
		data.adl.nav.request_valid['previous']=String(validRequests['mPrevious']);
		
		var adlcpData = Array();
		for(ds in sharedData)
		{
			var dat = Array();
			dat["id"] = ds;
			dat["store"] = sharedData[ds].store;
			dat["readable"] = sharedData[ds].readSharedData;
			dat["writeable"] = sharedData[ds].writeSharedData;	
			adlcpData.push(dat);
		}
		
		data.adl.data = adlcpData;
		
		var choice=validRequests['mChoice'];
		
		for (var k in choice) {
			//TODO set target
			//data.adl.nav.request_valid['choice'].{k}=true;
			//data.adl.nav.request_valid['choice'];
		}
		//TODO:JP - add valid jump requests?
		
		// add some global values for all sco's in package
		data.cmi.learner_name = globalAct.learner_name;
		data.cmi.learner_id = this.config.cmi_learner_id;
		data.cmi.cp_node_id = item.foreignId;
		data.scoid = item.id;
		data.cmi.session_time = undefined;
		data.cmi.completion_threshold = item.completionThreshold;
		data.cmi.launch_data = item.dataFromLMS;
		data.cmi.time_limit_action = item.timeLimitAction;
		data.cmi.max_time_allowed = item.attemptAbsoluteDurationLimit;
		
		data.cmi.entry="";
		
		//Add learner prefs (since the map stuff is completely nuts and doesn't work right)
		data.cmi.learner_preference = {
			audio_level : (item.audio_level) ? item.audio_level : 1,
			delivery_speed : (item.delivery_speed) ? item.delivery_speed : 1,
			language : item.language,
			audio_captioning : item.audio_captioning
		};


		if (item.objectives) 
		{
			for (k in item.objectives) {
				v=item.objectives[k];
				if (v.primary==true) {
			// REQ_74.3, compute scaled passing score from measure
					if (v.satisfiedByMeasure && v.minNormalizedMeasure!==undefined) 
					{
						v = v.minNormalizedMeasure;
					}
					else if (v.satisfiedByMeasure) 
					{
						v = 1.0;
					}
					else 
					{
						v = null;
					}
					data.cmi.scaled_passing_score = v;
					break; //we found the unique primary objective..so stop
				}	
			}
		}

		//support for auto-review
		item.options = new Object();
		item.options.notracking = false;
		if (globalAct.auto_review != 'n') {
			if (
				(globalAct.auto_review == 'r' && ((item.completion_status == 'completed' && item.success_status != 'failed') || item.success_status == 'passed') ) ||
				(globalAct.auto_review == 'p' && item.success_status == 'passed') ||
				(globalAct.auto_review == 'q' && (item.success_status == 'passed' || item.success_status == 'failed') ) ||
				(globalAct.auto_review == 'c' && item.completion_status == 'completed') ||
				(globalAct.auto_review == 'd' && (item.completion_status == 'completed' && item.success_status == 'passed') ) ||
				(globalAct.auto_review == 'y' && (item.completion_status == 'completed' || item.success_status == 'passed') )
			) {
				data.cmi.mode = "review";
			}
		}
		if (data.cmi.mode == "review") {
			data.cmi.credit = "no-credit";
			item.options.notracking = true;//UK: no better score for example!
		} else {

			if (item.exit!="suspend") {
				//provide us with a clean data set - UK not really clean!
				//data.cmi=Runtime.models.cmi;
				//explicitly set some entries
				data.cmi.completion_status="unknown";
				data.cmi.success_status="unknown";
				data.cmi.entry="ab-initio";
				data.cmi.suspend_data = null;
				data.cmi.total_time="PT0H0M0S"; //UK: not in specification but required by test suite
			} 

			//set resume manually if suspendALL happened before
			if (item.exit=="suspend" || wasSuspendAll) data.cmi.entry="resume";
		}

		//RTE-4-45: If there are additional learner sessions within a learner attempt, the cmi.exit becomes uninitialized (i.e., reinitialized to its default value of (“”) - empty characterstring) at the beginning of each additional learner session within the learner attempt.
		data.cmi.exit="";

		currentAPI = window[Runtime.apiname] = new Runtime(data, onCommit, onTerminate);
	}
	// deliver resource (sco)
	// customize GUI
	
	syncSharedCMI(item);

	scoStartTime = currentTime();

	var envEditor = this.config.envEditor;
	var randNumber="";
	if (envEditor==1) {
		randNumber = "?rand="+Math.floor(Math.random()*1000000)+"&"; 
	} 
	if (item.parameters == null) {
		item.parameters="";
	} 
	if (item.parameters != "" 
	    && 	item.parameters.indexOf('?') === -1 
	    && envEditor==false) {
		item.parameters = "?"+ item.parameters;
	} 
	openedResource=[item.id, item.href+randNumber+item.parameters, this.config.package_url];
	guiItemId = (ITEM_PREFIX + item.id).replace(/\./g,"_____");
	updateNav();
	updateControls();
	setResource();
}


function syncSharedCMI(item) {
	var mStatusVector = msequencer.getObjStatusSet(item.id);
    var mObjStatus = new ADLObjStatus();
	var obj;
	var err;
	//for first attempt
	
	if( mStatusVector != null ) {		
		for(i = 0; i < mStatusVector.length; i++ ) {
		    var idx=-1;
			mObjStatus = mStatusVector[i];
			// Set the objectives id
			//Get existing objectives
			var objCount = currentAPI.GetValueIntern("cmi.objectives._count");
            for( var j = 0; j < objCount; j++ ) {
				var obj = "cmi.objectives." + j + ".id";
				var nr = currentAPI.GetValueIntern(obj);
				if (nr==mObjStatus.mObjID) {
					idx=j;
					break;
				}
				
			}
			if (idx!=-1) {
				// Set the objectives success status
	        	obj = "cmi.objectives." + idx + ".success_status";

				if( mObjStatus.mStatus.toLowerCase()=="satisfied" )
	        	{
		       		err = currentAPI.SetValueIntern(obj,"passed");
	        	}
	          	else if( mObjStatus.mStatus.toLowerCase()=="notsatisfied")
	        	{
       				err = currentAPI.SetValueIntern(obj,"failed");
	        	}
				// Set the objectives scaled score
	        	obj = "cmi.objectives." + idx + ".score.scaled";
				if( mObjStatus.mHasMeasure==true && mObjStatus.mMeasure!=0 ) {
					err = currentAPI.SetValueIntern(obj,mObjStatus.mMeasure);
				}
				if ( mObjStatus.mHasRawScore )
				{
					obj = "cmi.objectives." + idx + ".score.raw";
					err = currentAPI.SetValueIntern(obj, mObjStatus.mRawScore);
				}
				if ( mObjStatus.mHasMinScore )
				{
					obj = "cmi.objectives." + idx + ".score.min";
					err = currentAPI.SetValueIntern(obj, mObjStatus.mMinScore);
				}
				if ( mObjStatus.mHasMaxScore )
				{
					obj = "cmi.objectives." + idx + ".score.max";
					err = currentAPI.SetValueIntern(obj, mObjStatus.mMaxScore);
				}
				if ( mObjStatus.mHasProgressMeasure )
				{
					obj = "cmi.objectives." + idx + ".progress_measure";
					err = currentAPI.SetValueIntern(obj, mObjStatus.mProgressMeasure);
				}
				
				obj = "cmi.objectives." + idx + ".completion_status";
				err = currentAPI.SetValueIntern(obj, mObjStatus.mCompletionStatus);
	      	}
		}	
		
	}	
	//
	
	
}


function syncCMIADLTree(){
	//get global status
	
	var mPRIMARY_OBJ_ID = null;
	var masteryStatus = null;
	var sessionTime = null;
	var entry = null;
	var normalScore=-1.0;
	var progressMeasure = null;
	var completionStatus=null;
	var SCOEntry=null; 
	var suspended = false;
    
	
	// Get the current completion_status
	
	SCOEntry = currentAPI.GetValueIntern("cmi.exit");
	
		
    completionStatus = currentAPI.GetValueIntern("cmi.completion_status");
	var completionSetBySCO = currentAPI.GetValueIntern("cmi.completion_status_SetBySco");

	if (completionStatus == "not attempted") completionStatus = "incomplete";

	progressMeasure = currentAPI.GetValueIntern("cmi.progress_measure");
	if ( progressMeasure == "" || progressMeasure == "unknown" ) //typeOf?
	{
		progressMeasure = null;
	}
	
	
	// Get the current success_status
    masteryStatus = currentAPI.GetValueIntern("cmi.success_status");
	var masterySetBySCO = currentAPI.GetValueIntern("cmi.success_status_SetBySco");
	
    // Get the current entry
	SCOEntry = currentAPI.GetValueIntern("cmi.entry");
	
	// Get the current scaled score
    score = currentAPI.GetValueIntern("cmi.score.scaled");

    // Get the current session time
    sessionTime = currentAPI.GetValueIntern("cmi.session_time");
	
	//get current activity
	var act = msequencer.mSeqTree.getActivity(mlaunch.mActivityID);
	
	if (act.getIsTracked())
	{
//alert("main.syncCMIADLTree:\nactivityid: " + mlaunch.mActivityID);	
		var primaryObjID = null;
		var foundPrimaryObj = false;
		var setPrimaryObjSuccess = false;
		var setPrimaryObjScore = false;
		
		objs = act.getObjectives();
		if( objs != null ) {
			for(var j = 0; j < objs.length; j++ ) {
				obj = objs[j];
				if( obj.mContributesToRollup==true ) {
					if( obj.mObjID != null ) primaryObjID = obj.mObjID;
					break;
				}
			}
		}
	
		//get Objective List
		var numObjs= currentAPI.GetValueIntern("cmi.objectives._count");
		for( var i = 0; i < numObjs; i++ ) {
			var obj = "cmi.objectives." + i + ".id";
			var objID= currentAPI.GetValueIntern(obj);
			if( primaryObjID != null && objID==primaryObjID ) foundPrimaryObj = true;
			else foundPrimaryObj = false;

			obj = "cmi.objectives." + i + ".success_status";
			objMS= currentAPI.GetValueIntern(obj);
			var msSetBySCO = currentAPI.GetValueIntern(obj + "_SetBySco");
	//alert("main.syncCMIADLTree:\nobjMS: " + objMS + "\non this: " + obj);        
			if( objMS=="passed" ) {
				msequencer.setAttemptObjSatisfied(mlaunch.mActivityID, objID, "satisfied");
				if( foundPrimaryObj==true )
				{
				   act.primaryStatusSetBySCO(currentAPI.GetValueIntern(obj + "_SetBySco") == 'true');
				   setPrimaryObjSuccess = true;
				   masteryStatus = objMS;
				}
			}
			else if( objMS=="failed" )
			{
				 msequencer.setAttemptObjSatisfied(mlaunch.mActivityID, objID, "notSatisfied");

				 if( foundPrimaryObj==true)
				 {
					act.primaryStatusSetBySCO(currentAPI.GetValueIntern(obj + "_SetBySco") == 'true');
					setPrimaryObjSuccess = true;
					masteryStatus = objMS;
				 }
			}
			else
			{
				if (msSetBySCO=="true")
				{
					msequencer.setAttemptObjSatisfied(mlaunch.mActivityID, objID, "unknown");
					
					var globs = act.getObjIDs(objID, false);
					if ( globs != null )
					{
						for ( var w = 0; w < globs.length; w++ )
						{
							adl_seq_utilities.setGlobalObjSatisfied(globs[w], msequencer.mSeqTree.mLearnerID, act.getScopeID(), TRACK_UNKNOWN);
						}
					}
					if ( foundPrimaryObj==true )
					{
						act.primaryStatusSetBySCO(true);
						setPrimaryObjSuccess = true;
						masteryStatus = objMS;
					}
				}
			}

			obj = "cmi.objectives." + i + ".score.scaled";
			objScore= currentAPI.GetValueIntern(obj);
			if( objScore!="" && objScore!="unknown" && objScore!=null ) {
				normalScore = objScore; 
				msequencer.setAttemptObjMeasure(mlaunch.mActivityID, objID, normalScore);
				if( foundPrimaryObj==true ){
					setPrimaryObjScore = true;
				}
				
			}     
			else
			 {
				msequencer.clearAttemptObjMeasure(mlaunch.mActivityID, objID);
			 }
			 
			 
			obj = "cmi.objectives." + i + ".completion_status";
			completion = currentAPI.GetValueIntern(obj);
			if ( (completion != "" && completion != "unknown" && completion != null) ||
			     (completion == TRACK_UNKNOWN && currentAPI.GetValueIntern(obj + "_SetBySco") == true) )
			{
				completion = (completion == "not attempted")?"incomplete":completion;
				if ( foundPrimaryObj==true && completionSetBySCO==false )
				{
					completionStatus = completion;
					completionSetBySCO = currentAPI.GetValueIntern(obj + "_SetBySco");
				}
				msequencer.setAttemptObjCompletionStatus(mlaunch.mActivityID, objID, completion);
			}
			else
			{
				msequencer.clearAttemptObjCompletionStatus(mlaunch.mActivityID, objID);
			}
			 
			 
			obj = "cmi.objectives." + i + ".progress_measure";
			var objscore = currentAPI.GetValueIntern(obj);
			if ( objscore != "" && objscore != "unknown" && objscore != null )
			{
				if ( foundPrimaryObj && progressMeasure == null )
				{
					progressMeasure = objscore;
				}
				msequencer.setAttemptObjProgressMeasure(mlaunch.mActivityID, objID, objscore);
			}
			else
			{
				msequencer.clearAttemptObjProgressMeasure(mlaunch.mActivityID, objID);
			}
			 
			objScoreRaw = currentAPI.GetValueIntern("cmi.objectives." + i + ".score.raw");
			if( objScoreRaw != "" && objScoreRaw != "unknown" && objScoreRaw != null) {
				msequencer.setAttemptObjRawScore(mlaunch.mActivityID, objID, objScoreRaw)
			} else {
				msequencer.clearAttemptObjRawScore(mlaunch.mActivityID, objID);
			}
			 
			objScoreMin = currentAPI.GetValueIntern("cmi.objectives." + i + ".score.min");
			if( objScoreMin != "" && objScoreMin != "unknown" && objScoreMin != null) {
				msequencer.setAttemptObjMinScore(mlaunch.mActivityID, objID, objScoreMin)
			} else {
				msequencer.clearAttemptObjMinScore(mlaunch.mActivityID, objID);
			} 
			 
			objScoreMax = currentAPI.GetValueIntern("cmi.objectives." + i + ".score.max");
			if( objScoreMax != "" && objScoreMax != "unknown" && objScoreMax != null) {
				msequencer.setAttemptObjMaxScore(mlaunch.mActivityID, objID, objScoreMax)
			} else {
				msequencer.clearAttemptObjMaxScore(mlaunch.mActivityID, objID);
			}

		}
		
		// Report the completion status
		act.primaryProgressSetBySCO(completionSetBySCO == 'true');
		msequencer.setAttemptProgressStatus(mlaunch.mActivityID, completionStatus);
		if ( progressMeasure != "" && progressMeasure != "unknown" && progressMeasure != null )
		{
			msequencer.setAttemptProgressMeasure(mlaunch.mActivityID, progressMeasure);
		}
		
		if (SCOEntry=="resume" ) {
			 msequencer.reportSuspension(mlaunch.mActivityID, true);
			 // preserve session state
		} else {
			 msequencer.reportSuspension(mlaunch.mActivityID, false);
			//clear state data for this attempt

		}	
		
		// Report the success status
		if( masteryStatus=="passed" )
		{
			msequencer.setAttemptObjSatisfied(mlaunch.mActivityID, mPRIMARY_OBJ_ID, "satisfied");
		}
		else if( masteryStatus=="failed" )
		{
			msequencer.setAttemptObjSatisfied(mlaunch.mActivityID, mPRIMARY_OBJ_ID, "notSatisfied");
		}
		else
		{
			if ( masterySetBySCO==true || masterySetBySCO == "true" )
			{
				
				masteryStatus = currentAPI.GetValueIntern('cmi.success_status');
				//alert("Mastery set by sco: "+ masteryStatus);
				
				act.primaryStatusSetBySCO(true);
				msequencer.setAttemptObjSatisfied(mlaunch.mActivityID, mPRIMARY_OBJ_ID, "unknown");
			
				var priglobs = act.getObjIDs(mPRIMARY_OBJ_ID, false);
				if ( priglobs != null )
				{
					for ( var idx = 0; idx < priglobs.length; idx++ )
					{
						adl_seq_utilities.setGlobalObjSatisfied(priglobs[idx], msequencer.mSeqTree.mLearnerID, act.getScopeID(), "unknown");
					}
				}
			}
		}

		// Report the measure
		if( score!="" && score!="unknown") {
				normalScore = score;
				msequencer.setAttemptObjMeasure(mlaunch.mActivityID, mPRIMARY_OBJ_ID, normalScore);
		}
		else{
			if( setPrimaryObjScore==false )
			{
			   msequencer.clearAttemptObjMeasure(mlaunch.mActivityID, mPRIMARY_OBJ_ID);
			}
		}
	}
	else
	{
		var numObjs= currentAPI.GetValueIntern("cmi.objectives._count");
		for ( var i = 0; i < numObjs; i++ ) 
		{
			var obj = "cmi.objectives." + i + ".id";
			var objID= currentAPI.GetValueIntern(obj);
			
			objScoreRaw = currentAPI.GetValueIntern("cmi.objectives." + i + ".score.raw");
			if( objScoreRaw != "" && objScoreRaw != "unknown" && objScoreRaw != null) {
				msequencer.setAttemptObjRawScore(mlaunch.mActivityID, objID, objScoreRaw)
			} else {
				msequencer.clearAttemptObjRawScore(mlaunch.mActivityID, objID);
			}
			 
			objScoreMin = currentAPI.GetValueIntern("cmi.objectives." + i + ".score.min");
			if( objScoreMin != "" && objScoreMin != "unknown" && objScoreMin != null) {
				msequencer.setAttemptObjMinScore(mlaunch.mActivityID, objID, objScoreMin)
			} else {
				msequencer.clearAttemptObjMinScore(mlaunch.mActivityID, objID);
			} 
			 
			objScoreMax = currentAPI.GetValueIntern("cmi.objectives." + i + ".score.max");
			if( objScoreMax != "" && objScoreMax != "unknown" && objScoreMax != null) {
				msequencer.setAttemptObjMaxScore(mlaunch.mActivityID, objID, objScoreMax)
			} else {
				msequencer.clearAttemptObjMaxScore(mlaunch.mActivityID, objID);
			}
		}
	}
	return [completionStatus,masteryStatus];
}

function onItemUndeliver(noControls) // onUndeliver called from sequencing process (EndAttempt)
{
	// customize GUI
	if (noControls!=true) {
		updateNav();
		updateControls();
	}	
	// throw away the resource
	// it may change api data in this
	removeResource(undeliverFinish);
}

function undeliverFinish(){
//	currentAPI = window[Runtime.apiname] = null;
}

function syncDynObjectives(){
//	var objectives=pubAPI.cmi.objectives;
	var objectives=data.cmi.objectives;
	var act=activities[mlaunch.mActivityID].objectives;
	for (var i=0;i<objectives.length;i++) {
	  if (objectives[i].id) {
		var id=objectives[i].id;
		var obj=objectives[i];
		//check for property
		if (!act.id) {
			act[id]=new Objective();
			act[id]['objectiveID']=id;
			act[id]['id']=id;
			
			//iterate over obj properties
			for (var element in obj) {
				if (element!="id" && element!="cmi_objective_id") {
					if (element!="score") {
						act[id][element]=obj[element];
					}
					//if score then step deeper
					if (element=="score") {
						for (var subelement in obj[element]) {
							act[id][subelement]=obj[element][subelement];
						}
					}
				}	
			}	
		}
	}
  }
}

// sequencer terminated
function onNavigationEnd()
{
	removeResource();
}

function onCommit(data) 
{
	return setAPI(data.cmi.cp_node_id, data);
}

function onTerminate(data) 
{
	var navReq;
	switch (data.cmi.exit)
	{
		case "suspend":
			navReq = {type: "suspend"};
			break;
		case "logout":  //depcracated
			navReq = {type: "ExitAll"};
		case "time-out":
			navReq = {type: "ExitAll"};
			//learner atttempt has ended
		default : // "", "normal"
			break;
	}
	if (data.adl && data.adl.nav) {
		var m = String(data.adl.nav.request).match(/^(\{target=([^\}]+)\})?(choice|jump|continue|previous|suspendAll|exit(All)?|abandon(All)?)$/);
		if (m) {
			navReq = {type: m[3].substr(0, 1).toUpperCase() + m[3].substr(1), target: m[2]};
		}
	}
	if (navReq) 
	{
		// will only work if no navigation is ongoing 
		// so we delay to next script cycle
		// and use closure to retain current variable scope
		//alert('ADLNAV => '+[navReq.type, navReq.target])
	//	alert(navReq.type, navReq.target);

		if (navReq.type!="suspend") {
			adlnavreq=true; 
			//TODO fix for Unix
			if (navReq.type=="Choice" || navReq.type=="Jump") {
				launchTarget(navReq.target, (navReq.type=="Jump"));
			} else {
				launchNavType(navReq.type);
			}
			
		}
	}
	
	updateNavForSequencing();
	if (!this.config.sequencing_enabled) updateNav(); //because could come later - change in 4.5
	//setResource(); - was workaround for IE Mantis 13522, but problem with sending terminate before onunload
	return true;
}


/* ############# GLOBAL CONSTANTS AND VARIABLES ################################ */


var apiIndents = // for mapping internal to api representaiton
{
	'cmi' : 
	{
		'score' : ['raw', 'min', 'max', 'scaled'],
		'learner_preference' : ['audio_captioning', 'audio_level', 'delivery_speed', 'language']
	},
	'objective' : 
	{
		'score' : ['raw', 'min', 'max', 'scaled']
	}
};	


function updateNav(ignore) {

	function signActNode() {
		if(elm && activities[tree[i].mActivityID].href && guiItemId == elm.id) {
			removeClass(elm.parentNode,"ilc_rte_status_RTENotAttempted",1);
			removeClass(elm.parentNode,"ilc_rte_status_RTEIncomplete",1);
			removeClass(elm.parentNode,"ilc_rte_status_RTECompleted",1);
			removeClass(elm.parentNode,"ilc_rte_status_RTEFailed",1);
			removeClass(elm.parentNode,"ilc_rte_status_RTEPassed",1);
			toggleClass(elm, "ilc_rte_tlink_RTETreeCurrent",1);
			toggleClass(elm.parentNode,"ilc_rte_status_RTERunning",1);
		} else {
			removeClass(elm, "ilc_rte_tlink_RTETreeCurrent");
			removeClass(elm.parentNode, "ilc_rte_status_RTERunning");
		}
	}

	//check for tree
	if (!all("treeView")) {
		return;
	}
	//first set it
	if (ignore!=true) {
		setToc();
	}	
	var tree=msequencer.mSeqTree.mActivityMap;
	var disable;
	var first = true;
	for (i in tree) {
		var disable=true;
		var disabled_str = "";
		var test=null;
		if (mlaunch.mNavState.mChoice!=null) {
			test=mlaunch.mNavState.mChoice[i];
		}	
		if (test) {
			if (test['mIsSelectable']==true && test['mIsEnabled']==true ) { 
				disable=false;
			} else {
				disable=true;
				disabled_str="Disabled";
			}
		}
		// if (guiItem && ignore==true) {
			// if (guiItem.id ==ITEM_PREFIX + tree[i].mActivityID)
			// {
				// continue;
			// }
		// }
		var elm = all(ITEM_PREFIX + tree[i].mActivityID.replace(/\./g,"_____"));
		// if (guiItem && ignore==true) {
			// signActNode();
			// continue;
		// }

	//	if (!elm) {return;}
//console.log("-" + ITEM_PREFIX + tree[i].mActivityID + "-" + disable + "-");
		if (disable)
		{
			toggleClass(elm, 'ilc_rte_tlink_RTETreeLinkDisabled', 1);
		}
		else
		{
			toggleClass(elm, 'ilc_rte_tlink_RTETreeLink', 1);
		}
		//search for the node to change
		//set icons
		if (activities[tree[i].mActivityID].sco && activities[tree[i].mActivityID].href) {
			
		
			var node_stat_completion=activities[tree[i].mActivityID].completion_status;
			//not attempted
			if (node_stat_completion==null || node_stat_completion=="not attempted") {
				if(elm) toggleClass(elm.parentNode,"ilc_rte_status_RTENotAttempted",1);
			}
		
			//incomplete
			if (node_stat_completion=="unknown" || node_stat_completion=="incomplete" || statusArray[[tree[i].mActivityID]]['completion'] == "unknown" ||
				statusArray[[tree[i].mActivityID]]['completion'] == "incomplete") {
				if(elm) {
					removeClass(elm.parentNode,"ilc_rte_status_RTENotAttempted",1);
					toggleClass(elm.parentNode,"ilc_rte_status_RTEIncomplete",1);
				}
			}
			
			//just in case-support not required due to spec
			if (node_stat_completion=="browsed") {
				if(elm) {
					removeClass(elm.parentNode,"ilc_rte_status_RTENotAttempted",1);
					toggleClass(elm.parentNode,"ilc_rte_status_RTEBrowsed",1);
				}
			}
			
			//completed
			if (node_stat_completion=="completed" || statusArray[[tree[i].mActivityID]]['completion'] == "completed") {
				if(elm) {
					removeClass(elm.parentNode,"not_attempted",1);
					removeClass(elm.parentNode,"ilc_rte_status_RTEIncomplete",1);
					removeClass(elm.parentNode,"ilc_rte_status_RTEBrowsed",1);
					toggleClass(elm.parentNode,"ilc_rte_status_RTECompleted",1);
				}
			}
			
			//overwrite if we have information on success (interaction sco) - ignore success=unknown
			
			var node_stat_success=activities[tree[i].mActivityID].success_status;
			if (node_stat_success=="passed" || node_stat_success=="failed" || statusArray[[tree[i].mActivityID]]['success'] == "failed" ||
				statusArray[[tree[i].mActivityID]]['success'] == "passed") {
				
				//passed
				if (node_stat_success=="passed" || statusArray[[tree[i].mActivityID]]['success'] == "passed") {
					if(elm) {
						removeClass(elm.parentNode,"ilc_rte_status_RTEFailed",1);
						toggleClass(elm.parentNode,"ilc_rte_status_RTEPassed",1);
					}
				//failed
				} else {
					if(elm) {
						removeClass(elm.parentNode,"ilc_rte_status_RTEPassed",1);
						toggleClass(elm.parentNode,"ilc_rte_status_RTEFailed",1);
					}
				}
			}
			if (elm != null && elm.parentNode)
			{
				toggleClass(elm.parentNode,"ilc_rte_node_RTESco" + disabled_str,1);
			}

		} else {
			if (elm && activities[tree[i].mActivityID].href) {
				toggleClass(elm.parentNode,"ilc_rte_status_RTEAsset",1);
				if (elm.parentNode)
				{
					toggleClass(elm.parentNode,"ilc_rte_node_RTEAsset" + disabled_str,1);
				}
			}
			else if (!activities[tree[i].mActivityID].href && elm != null && elm.parentNode)
			{
				if (!first)
				{
					toggleClass(elm.parentNode,"ilc_rte_node_RTEChapter" + disabled_str,1);
				}
				else
				{
					toggleClass(elm.parentNode,"ilc_rte_node_RTECourse" + disabled_str,1);
				}
			}
		}
		//added to sign actual node
		// if (ignore!=true) 
		signActNode();
		//toggleClass(elm.parentNode, 'hidden', item.hidden);
		first = false;
	}
}

function updateNavForSequencing() {
	if (this.config.sequencing_enabled) {
		// this will update the UI tree 
		var valid = new ADLValidRequests();
		valid = msequencer.getValidRequests(valid);
		msequencer.mSeqTree.setValidRequests(valid);
		mlaunch.mNavState = msequencer.mSeqTree.getValidRequests();
		updateNav(false);
		updateControls();
	}
}

function isIE(versionNumber) {
var detect = navigator.userAgent.toLowerCase();
if(!(navigator && navigator.userAgent && navigator.userAgent.toLowerCase)) {
  	        return false;
  	    } else {
  	        if(detect.indexOf('msie') + 1) {
  	            // browser is internet explorer
  	            var ver = function() {
  	                // http://msdn.microsoft.com/workshop/author/dhtml/overview/browserdetection.asp
  	                // Returns the version of Internet Explorer or a -1
  	                // (indicating the use of another browser).
  	                var rv = -1; // Return value assumes failure
  	                if (navigator.appName == 'Microsoft Internet Explorer') {
  	                    var ua = navigator.userAgent;
  	                    var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
  	                    if (re.exec(ua) != null) {
  	                        rv = parseFloat( RegExp.$1 );
  	                    }
  	                }
  	                return rv;
  	            };
  	            var valid = true;
  	            // if the version can be found and the version is less than our version number it is invalid
  	            if ((ver > -1) && (ver < versionNumber)) {
  	                valid = false;
  	            }
  	            return valid;
  	        } else {
  	            return false;
  	        }
  	    }
}

function pausecomp(millis) 
{
	var date = new Date();
	var curDate = null;

	do { curDate = new Date(); } 
	while(curDate-date < millis);
}

//debug extensions
function refreshDebugger(param) {
	if (param == true) {
		window.setTimeout("debugWindow.location.reload()",2000);
	} else {
		if(b_refreshDebugger_busy==false){
			b_refreshDebugger_busy=true;
			var i_logLength=a_logEntries.length;
			for(var i=0;i<i_logLength;i++){
				if(a_logEntries[i]!="") sendAndLoad(this.config.post_log_url, a_logEntries[i]);
				a_logEntries[i]="";
			}
			if (i_logLength==a_logEntries.length) {
				a_logEntries=[]; //no more entries since start of function
				if (i_logLength>0 && debugWindow!=null && debugWindow.closed!=true) {
					var content = sendJSONRequest(this.config.livelog_url);
					debugWindow.updateLiveLog();
				}
			}
			else window.setTimeout("refreshDebugger()",500);
			b_refreshDebugger_busy=false;
		}
	}
}

function sendLogEntry(timespan,action,key,value,result,errorCode)
{
	var logEntry = new Object();
	logEntry['timespan'] = timespan;
	logEntry['action'] = action;
	logEntry['key'] = key;
	logEntry['value'] = value;
	logEntry['result'] = (typeof(result)!='undefined') ? result : 'undefined';
	logEntry['errorcode'] = errorCode;

	if (fixedFailure==true) logEntry['errorcode']+=100000;
	fixedFailure=false;
	if (toleratedFailure==true) logEntry['errorcode']=200000;
	toleratedFailure=false;

	if (action == "Initialize") {
		logEntryScoId = mlaunch.mActivityID;
		logEntryScoTitle = activities[mlaunch.mActivityID].title;
	}
	logEntry['scoid'] = logEntryScoId;
	logEntry['scotitle'] = logEntryScoTitle;
	a_logEntries.push(toJSONString(logEntry));
	if (action!="DELETE") {
//		var result = sendJSONRequest(this.config.post_log_url, logEntry,refreshDebugger());	
		setTimeout("refreshDebugger()",2000);
	} else {
//		var result = sendJSONRequest(this.config.post_log_url, logEntry,refreshDebugger(true));
		refreshDebugger(true);
	}	
}

function removeByElement(arrayName,arrayElement)
 {
    for(var i=0; i<arrayName.length;i++ )
     {
        if(arrayName[i]==arrayElement)
            arrayName.splice(i,1); 
      } 
 }

function createSummary()
{
	var logEntry = new Object();
	logEntry['action'] = "SUMMARY";
	a_logEntries.push(toJSONString(logEntry));
	refreshDebugger();
//	var result = sendJSONRequest(this.config.post_log_url, logEntry,refreshDebugger(true));	
}
//end debug extensions

// Server related Variables
var remoteMapping = null; // mapping of userdata from client to server representation
var remoteInsertId = 0; // pseudo IDs for newly generated data rows (will be prefixed by "$")

var globalAct = new Activity(); // pseudo activity utilizing manifest/package wide data
var rootAct = new Activity(); // organization node, root of activity tree
var activities = new Object(); // activities by item string identifier 
var activitiesByCAM = new Object(); // activities by cp_node_id
var activitiesByCMI = new Object(); // activities by cmi_node_id
var activitiesByNo = new Array(); // activities by numerical index
var sharedObjectives = new Object(); // global objectives by objective identifier
var sharedData = new Array();


//integration of ADL Sequencer
var msequencer=new ADLSequencer();
var mlaunch=null;
var adlnavreq=null;
var treeYUI=null;
var logState=false;
var treeState=true;


// GUI constants
var ITEM_PREFIX = "itm";
var RESOURCE_PARENT = "tdResource";
var RESOURCE_NAME = "frmResource";
var RESOURCE_TOP = "mainTable";

// GUI Variables 
var guiItemId;
var guiState; // loading, playing, paused, stopped, buffering
var gConfig;

// SEQUENCER Constants: States
var RUNNING = 1; // already executing some navigation command
var WAITING = 0; // not active
var QUERYING = -1; // running without data modification
var ABORTING = -2; // running to end after abort

// SEQUENCER Constants: Rule RegExps for use in condition evaluation
var EXIT_ACTIONS = /^exit$/i;
var POST_ACTIONS = /^exitParent|exitAll|retry|retryAll|continue|previous$/i;
var SKIPPED_ACTIONS = /^skip$/i;
var STOP_FORWARD_TRAVERSAL_ACTIONS = /^stopForwardTraversal$/i;
var HIDDEN_FROM_CHOICE_ACTIONS = /^hiddenFromChoice$/i;
var DISABLED_ACTIONS = /^disabled$/i;

// SEQUENCER Variables
var state = WAITING; 
var SCOEntryedAct = null;

var saved_adl_seq_utilities = "";//{"satisfied":{},"measure":{},"status":{}};
var saved_result;
var saved={
	"comment":{"data":[],"checkplus":8},
	"correct_response":{"data":[],"checkplus":4,"node":""},
	"interaction":{"data":[],"checkplus":2},
	"objective":{"data":[],"checkplus":1}
	};
// SCO related Variables
var currentAPI; // reference to API during runtime of a SCO
var scoStartTime = null;

var openedResource = new Array();

var treeView=true;

// Logging active
var logActive = false;
var scoDebugValues = null;
var scoDebugValuesTest = null;
var logEntryScoId = "";
var logEntryScoTitle = "";
var summaryOnUnload = false;

var b_refreshDebugger_busy=false;
var a_logEntries=[];
var fixedFailure=false;
var toleratedFailure=false;
//course wide variables
//var pubAPI=null;
var statusArray = new Object(); //just used for visual feedback
//var isSaving = true;

var saved_shared_data = "";
var saveOnCommit = true;
// Public interface
window.scorm_init = init;

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

/**
 * @author  Hendrik Holtmann <holtmann@mac.com>, Alfred Kohnert <alfred.kohnert@bigfoot.com>, modifications by Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @version $Id$
*/

function Runtime(cmiItem, onCommit, onTerminate, onDebug) 
{
	// implementation of public methods
	
	// public error code property getter
	function GetLastError() 
	{
		if (logActive)
			sendLogEntry(getMsecSinceStart(),'GetLastError',"","",String(error),"");
		return String(error);
	}

	/**
	 * public error description property getter
	 * error codes and descriptions (see "SCORM Run-Time Environment
	 * Version 1.3" on www.adlnet.org)
	 * @param {string} error number must be string!
	 */	 
	function GetErrorString(param) 
	{
		if (typeof param !== 'string') 
		{
			if (logActive)
				sendLogEntry(getMsecSinceStart(),'GetErrorString',String(param),"","false",201);
			return setReturn(201, 'GetErrorString param must be empty string', '');
		}
		var e = Runtime.errors[param];
		var returnValue = e && e.message ? String(e.message).substr(0,255) : '';
		if (logActive)
			sendLogEntry(getMsecSinceStart(),'GetErrorString',String(param),"",returnValue,0);
		return returnValue;
	}

	/**
	 * public error details getter 
	 * may be useful in debugging
	 * @param {string} required; but not evaluated in this implementation
	 * @return {string} in this implementation always info for last error if any	  
	 */	 
	function GetDiagnostic(param) 
	{
		var returnValue = (error ? String(diagnostic).substr(0,255) : 'no diagnostic');
		if (param != "") returnValue = param + ': ' + returnValue;
		if (logActive)
			sendLogEntry(getMsecSinceStart(),'GetDiagnostic',String(param),"",returnValue,"");
		return returnValue;
	}

	/**
	 * Open connection to data provider 
	 * @access private, but also bound to 'this'
	 * @param {string} required; must be '' 
	 */	 
	function Initialize(param) 
	{
		//for SCORM Test Tool - function checks Values set at previous attempts
		function checkInternalValues(a_debugValues){
			function checkGetValue(cmivar){
				var a_getValues = ['comments_from_lms','completion_threshold','credit','entry','launch_data','learner_id','learner_name','max_time_allowed','mode','scaled_passing_score','time_limit_action','total_time'];
				var b_getValue=false;
				for (var i=0; i<a_getValues.length; i++){
					if(cmivar.indexOf("cmi."+a_getValues[i]) > -1) b_getValue=true;
				}
				return b_getValue;
			}

			var j=0;
			while (j < a_debugValues.length){
				if (a_debugValues[j].indexOf("completion_status") > -1){
					if (GetValueIntern(a_debugValues[j]) !="unknown") removeByElement(a_debugValues,a_debugValues[j]);
					else j++;
				}
				else if (a_debugValues[j].indexOf("success_status") > -1){
					if (GetValueIntern(a_debugValues[j]) !="unknown") removeByElement(a_debugValues,a_debugValues[j]);
					else j++;
				}
				else if (GetValueIntern(a_debugValues[j]) !="" && checkGetValue(a_debugValues[j]) == false)
					removeByElement(a_debugValues,a_debugValues[j]);
				else j++;
			}
		}
		setReturn(-1, 'Initialize(' + param + ')');
		if (param!=='') 
		{
			if (logActive)
				sendLogEntry(getMsecSinceStart(),'Initialize',param,"","false",201);
			return setReturn(201, 'param must be empty string', 'false');
		}
		switch (state) 
		{
			case NOT_INITIALIZED:
				dirty = false;
				if (cmiItem instanceof Object) 
				{
					state = RUNNING;
					//initialize values
					total_time_at_initialize=GetValueIntern("cmi.total_time");

					if (logActive) {
						sendLogEntry(getMsecSinceStart(),'Initialize',"","","true",0);
						scoDebugValues = new Array();
						for (var i=0; i<gConfig.debug_fields.length; i++){
							scoDebugValues[i] = gConfig.debug_fields[i];
						}
						scoDebugValuesTest = new Array();
						for (var i=0; i<gConfig.debug_fields_test.length; i++){
							scoDebugValuesTest[i] = gConfig.debug_fields_test[i];
						}
						if (GetValueIntern("cmi.entry") != "ab-initio") {
							checkInternalValues(scoDebugValues);
							checkInternalValues(scoDebugValuesTest);
						}
					}
					return setReturn(0, '', 'true');
				} 
				else 
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),'Initialize',"","","false",102);
					return setReturn(102, '', 'false');
				}
				break;
			case RUNNING:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),'Initialize',"","","false",103);
				return setReturn(103, '', 'false');
			case TERMINATED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),'Initialize',"","","false",104);
				return setReturn(104, '', 'false');
		}
		if (logActive)
			sendLogEntry(getMsecSinceStart(),'Initialize',"","","false",103);
		return setReturn(103, '', 'false');
	}	

	

	/**
	 * Sending changes to data provider 
	 * @access private, but also bound to 'this'
	 * @param {string} required; must be '' 
	 */	 
	function Commit(param) 
	{
		setReturn(-1, 'Commit(' + param + ')');
		if ((typeof param == "undefined") || param == null) {
			//ToDo: check if allowed by Testsuite; else use with check values
			param = 'undefined';
			fixedFailure=true;
		}
		else if (param!=='')
		{
			if (logActive)
				sendLogEntry(getMsecSinceStart(),'Commit',param.toString(),"","false",201);
			return setReturn(201, 'param must be empty string', 'false');
		}
		switch (state) 
		{
			case NOT_INITIALIZED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),'Commit',param,"","false",142);
				return setReturn(142, '', 'false');
			case RUNNING:
				//calculating not at terminate to avoid save because many contributors of learning modules send at the end just before terminate() commit()
				if ((!cmiItem.cmi.mode || cmiItem.cmi.mode==="normal") && (typeof cmiItem.cmi.session_time!="undefined" || config.time_from_lms==true)) {
					if (config.time_from_lms==true) {
						var interval = (currentTime() - msec)/1000;
						var dur = new ADLDuration({iFormat: FORMAT_SECONDS, iValue: interval});
						cmiItem.cmi.session_time = dur.format(FORMAT_SCHEMA);
					}
					var total_time=addTimes(total_time_at_initialize,cmiItem.cmi.session_time);
					cmiItem.cmi.total_time = total_time.toString();
				}
				//auto suspend
				if (config.auto_suspend==true) cmiItem.cmi.exit="suspend";
				//store correct status in DB; returnValue1 because of IE;
				var statusValues=syncCMIADLTree();
				//statusHandler(cmiItem.scoid,"completion",statusValues[0]);
				//statusHandler(cmiItem.scoid,"success",statusValues[1]);
				var returnValue = onCommit(cmiItem);
				if (returnValue && saveOnCommit == true) {
					if (config.fourth_edition) {
						var sgo=saveSharedData(cmiItem);
					}
					returnValue = save();
				}
				if (returnValue) 
				{
					dirty = false;
					if (logActive && commitByTerminate==false)
						sendLogEntry(getMsecSinceStart(),'Commit',param,"","true",0);
					return setReturn(0, '', 'true');
				} 
				else
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),'Commit',param,"","false",391);
					return setReturn(391, 'Persisting failed', 'false');
				}
				break;
			case TERMINATED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),'Commit',param,"","false",143);
				return setReturn(143, '', 'false');
		}
	}

	/**
	 * Close connection to data provider 
	 * @access private, but also bound to 'this'
	 * @param {string} required; must be '' 
	 */	 
	function Terminate(param) {
		setReturn(-1, 'Terminate(' + param + ')');
		if (param!=='') 
		{
			if (logActive)
				sendLogEntry(getMsecSinceStart(),'Terminate',param,"","false",201);
			return setReturn(201, 'param must be empty string', 'false');
		}
		switch (state) 
		{
			case NOT_INITIALIZED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),'Terminate',"","","false",112);
				return setReturn(112, '', 'false');
			case RUNNING:
				// TODO check for possible exceptions
				// resulting in code 111 (REQ_5.3)
				Runtime.onTerminate(cmiItem, msec); // wrapup from LMS 
				setReturn(-1, 'Terminate(' + param + ') [after wrapup]');
				saveOnCommit = true;
				commitByTerminate=true;
				var returnValue = Commit(''); // wrap up 
				commitByTerminate=false;
				saveOnCommit = true;
				state = TERMINATED;
				if (logActive) {
					sendLogEntry(getMsecSinceStart(),'Terminate',"","",returnValue,0);
					sendLogEntry(getMsecSinceStart(),'ANALYZE',"",scoDebugValues,"","");
					sendLogEntry(getMsecSinceStart(),'ANALYZETEST',"",scoDebugValuesTest,"","");
					if (summaryOnUnload == true) createSummary();
				}
				onTerminate(cmiItem); // callback
				return setReturn(0, '', returnValue);//error should not change if logActive
			case TERMINATED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),'Terminate',"","","false",113);
				return setReturn(113, '', 'false');
		}
	}
	
	/**
	 * Read data element entry 
	 * @access private, but also bound to 'this'
	 * @param {string} required; must be valid cmi element string 
	 */	 
	function GetValue(sPath) 
	{
		//log.info("GetValue: "+sPath);
		setReturn(-1, 'GetValue(' + sPath + ')');
//		state=1;
		switch (state) 
		{
			case NOT_INITIALIZED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),"GetValue",sPath,"","false",122);
				sclogdump("Not initialized","error");
				return setReturn(122, '', '');
			case RUNNING:
				if (typeof(sPath)!=='string') 
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),"GetValue",sPath,"","false",201);
					return setReturn(201, 'must be string', '');
				}
				if (sPath==='') 
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),"GetValue",sPath,"","false",301);
					return setReturn(301, 'cannot be empty string', '');
				}
				var r;
				if (sPath=="cmi.total_time") r=setReturn(0,'',total_time_at_initialize);
				else r=getValue(sPath, false);
				if (logActive) {
					sendLogEntry(getMsecSinceStart(),"GetValue",sPath,"",r,error);
					var a_getValues = ['comments_from_lms','completion_threshold','credit','entry','launch_data','learner_id','learner_name','max_time_allowed','mode','scaled_passing_score','time_limit_action','total_time'];
					for (var j=0; j<a_getValues.length; j++) {
						if (sPath.indexOf("cmi."+a_getValues[j])>-1){
							removeByElement(scoDebugValues,sPath);
							removeByElement(scoDebugValuesTest,sPath);
						}
					}
				}
				return error ? '' : setReturn(0, '', r); 
				// TODO wrap in TRY CATCH
			case TERMINATED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),"GetValue",sPath,"","false",123);
				return setReturn(123, '', '');
		}
	}
	
	//allows to get data even after termination
	function GetValueIntern(sPath) {
		//setReturn(-1, 'GetValueIntern(' + sPath + ')');
		
		var r = getValue(sPath, false);
		//sclogdump("ReturnInern: "+sPath + " : "+ r);
		return error ? '' : setReturn(0, '', r); 	
		
	}
	
	/**
	 * Read data element entry 
	 * @access private
	 * @param {string} required; must be valid cmi element string 
	 * @param {boolean} optional; if true all permissions are assumed as readwrite 
	 */
	function getValue(path, sudo) 
	{
		var tokens = path.split('.');
		return walk(cmiItem, Runtime.models[tokens[0]], tokens, null, sudo, {parent:[]});
	}	
	
	
	//allows to set data ignoring the status
	function SetValueIntern(sPath,sValue) {
		if (typeof sValue == "string") { //all ok
		} else if (typeof sValue == "number") {
			sValue = sValue.toString(10);
		} else { 
			sValue = "";
		}
		var r = setValue(sPath, sValue);
		return error ? '' : setReturn(0, '', r);
	}

	/**
	 * Update or create data element entry 
	 * @access private, but also bound to 'this'
	 * @param {string} required; must be valid cmi element string
	 * @param {string} required; must be valid cmi element value
	 */	  
	function SetValue(sPath, sValue) 
	{
		setReturn(-1, 'SetValue(' + sPath + ', ' + sValue + ')');
		switch (state) 
		{
			case NOT_INITIALIZED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"false",132);
				return setReturn(132, '', 'false');
			case RUNNING:
				if (typeof(sPath)!=='string') 
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"false",201);
					return setReturn(201, 'must be string', 'false');
				}
				if (sPath==='') 
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"false",351);
					return setReturn(351, 'Param 1 cannot be empty string', 'false');
				}
				if ((typeof sValue == "undefined") || sValue == null) {
					if (logActive) sendLogEntry(getMsecSinceStart(),"SetValue",sPath,""+sValue,"false",406);
					return setReturn(406, 'Value cannot be undefined or null', 'false');
				}
				else if (typeof sValue == "object") {
					if (logActive) sendLogEntry(getMsecSinceStart(),"SetValue",sPath,"object: "+String(sValue),"false",406);
					return setReturn(406, 'Value cannot be an object', 'false');
				}
				else if (typeof sValue == "function") {
					if (logActive) sendLogEntry(getMsecSinceStart(),"SetValue",sPath,"function: "+sValue.toString(),"false",406);
					return setReturn(406, 'Value cannot be a function', 'false');
				}
				else if (typeof sValue == "number") {
					sValue = sValue.toString(10);
					fixedFailure=true;
				}
				else if (typeof sValue == "boolean") {
					sValue = ""+sValue;
					fixedFailure=true;
				}
				else {
					sValue = ""+sValue;
				}
				try 
				{
					var r = setValue(sPath, sValue);
					if (!error) {
						if (logActive) {
							sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"true",0);
							removeByElement(scoDebugValues,sPath);
							removeByElement(scoDebugValuesTest,sPath);
							//check logik for 3rd edition
							if (sPath == "cmi.completion_status" && cmiItem.cmi.completion_threshold && cmiItem.cmi.completion_threshold>=0) {
								sendLogEntry("","INFO","completion_status_by_progress_measure",GetValueIntern("cmi.completion_status"),"","");
							}
							if (sPath == "cmi.success_status" && cmiItem.cmi.scaled_passing_score && cmiItem.cmi.scaled_passing_score>=-1) {
								sendLogEntry("","INFO","success_status_by_score_scaled",GetValueIntern("cmi.success_status"),"","");
							}
						}	
						var lastToken = sPath.substring(sPath.lastIndexOf('.') + 1);
						if(lastToken == "completion_status" || lastToken == "success_status") {
							setValue(sPath + "_SetBySco", "true");
						}
						// if (sPath == "cmi.completion_status" && cmiItem.scoid != null ) {
							// statusHandler(cmiItem.scoid,"completion",sValue);
						// }

						// if (sPath == "cmi.success_status" && cmiItem.scoid != null ) {
							// statusHandler(cmiItem.scoid,"success",sValue);
						// }
					} else {
						if (logActive)
							sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"false",error);
					}	
					return error ? 'false' : 'true'; 
				} catch (e) 
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"false",351);
					return setReturn(351, 'Exception ' + e, 'false');
				}
				break;
			case TERMINATED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"false",133);
				return setReturn(133, '', 'false');
		}
	}
	/**
	 * Update or create data element entry
	 * @access private
	 * @param {string} required; must be valid cmi element string 
	 * @param {string} required; must be valid cmi element value
	 * @param {boolean} optional; if true all permissions are assumed as readwrite 
	 */
	function setValue(path, value, sudo) 
	{

		var tokens = path.split('.');
		return walk(cmiItem, Runtime.models[tokens[0]], tokens, value, sudo, {parent:[]});
	}
	
	/**
	 * Synchronized walk on data instance and data model to read/replace content
	 * @access private
	 * @param {object} required; data instance node 
	 * @param {object} required; data model node
	 * @param {array} required; path of tokens to walk down ["cmi", "core", "etc"] 
	 * @param {object} optional; new value for setValue
	 * @param {boolean} optional; if true walks in superuser mode, i.e. ignore permissions 
	 * @param {object} optional; temporary data stored for use in deeper evaluations, used for some context dependencies 
	 */
	function walk(dat, def, path, value, sudo, extra) 
	{
		var setter, token, result, tdat, tdef, k, token2, tdat2, di, token3;
		setter = typeof value === "string";
		token = path.shift();
		if (!def) 
		{
			return setReturn(401, 'Unknown element: ' + token, setter ? 'false' : '');
		}

		tdat = dat[token];
		tdef = def[token];
		if (!tdef) 
		{
			return setReturn(401, 'Unknown element: ' + token, setter ? 'false' : '');
		}
		
		if (tdef.type == Function) // adl.nav.request.choice ... target=blabla 
		{
			token2 = path.shift();
			result = tdef.children.type.getValue(token2, tdef.children);
			return setReturn(0, '', result);
		}
		if (path[0] && path[0].charAt(0)==="_") 
		{
			if (path.length>1) 
			{
				return setReturn(401, 'Unknown element', setter ? 'false' : '');
			}
			if (setter) 
			{
				return setReturn(404, 'read only', 'false');
			}
			if ('_children' === path[0]) 
			{
				if (!tdef.children) 
				{
					return setReturn(301, 'Data model element does not have children', '');
				}
				result = []; 
				for (k in tdef.children) 
				{
					if(k.lastIndexOf("_SetBySco") == -1)
					result.push(k);
				}  
				return setReturn(0, '', result.join(","));
			}
			
			if ('_count' === path[0]) 
			{
				return tdef.type !== Array ? 
					setReturn(301, 'Data model element cannot have count', '') :
					setReturn(0, '', (tdat && tdat.length ? tdat.length : 0).toString());
			}
	
			if (token==="cmi" && '_version' === path[0]) 
			{
				return setReturn(0, '', '1.0');
			}
		}

		if (tdef.type == Array) // checks two tokens in one step e.g. "interactions" and "1"
		{
			token2 = path.shift() || "";
			var m = token2.match(/^([^\d]*)(0|[1-9]\d{0,8})$/);
			if (token2.length===0 || m && m[1]) 
			{
				return setReturn(401, 'Index expected', setter ? 'false' : '');
			} 
			else if (!m) 
			{
				return setReturn(setter ? 351 : 301, 'Index not an integer', setter ? 'false' : '');
			}
			token2 = Number(token2);
			tdat = tdat ? tdat : new Array();
			tdat2 = tdat[token2];
			token3 = path[0] || null;
			//alert(tdat.length+ "compared to:"+token2);
		
			if (setter)
			{
				if (token == "data" && token2 >= tdat.length && 
				 	(token3 == "store" || token3 == "id")) //adl.data special case
				{
					return setReturn(351, 'Index out of bounds', 'false');
				}
				if (token2 > tdat.length) 
				{
					return setReturn(351, 'Data model element collection set out of order', 'false');
				}
				if (tdef.maxOccur && token2+1 > tdef.maxOccur) 
				{
					if (config.checkSetValues) 
						return setReturn(301, '', 'false');
					else toleratedFailure=true;
				}
				if (tdat2 === undefined) 
				{
					tdat2 = new Object();
				}
				extra.index = token2;
				extra.parent.push(dat);
				if (tdef.unique===token3)
				{
					for (di=tdat.length; di--;) 
					{
						if (tdat[di][tdef.unique]===value) 
						{
							if (di!==token2) 
							{
								if (config.checkSetValues) {
									extra.error = {code: 351, diagnostic: "The data model element's value is already in use and is not unique"};
									break;
								}
								else toleratedFailure=true;
							}
						}
					}
				}
				result = walk(tdat2, tdef.children, path, value, sudo, extra);
				if (!error) 
				{
					tdat[token2] = tdat2;
					dat[token] = tdat;
				}
				return result;
			}
			else if (tdat2)
			{
				return walk(tdat2, tdef.children, path, value, sudo, extra);
			}
			else
			{
				if (config.checkSetValues)
					return setReturn(301, 'Data Model Collection Element Request Out Of Range', '');
				else toleratedFailure=true;
			}
		}
		
		if (tdef.type == Object)
		{
			if (typeof tdat === "undefined")
			{
				if (setter)
				{
					tdat = new Object();
					extra.parent.push(dat);
					result = walk(tdat, tdef.children, path, value, sudo, extra);
					if (!error) 
					{
						dat[token] = tdat;
					}
					return result;
				}
				else
				{
					return setReturn(tdef.children[path.pop()] ? 403 : 401, 'Not inited or defined: ' + token, '');
				}
			}
			else
			{
				if (setter) {
					extra.parent.push(dat);
				}
				return walk(tdat, tdef.children, path, value, sudo, extra);
			}
		}

		if (setter)
		{	
			if(token == "store" && dat["writeable"] != undefined && dat["writeable"] == 0) {
				return setReturn(404, 'readonly: ' + token, 'false');
			} 
			if (tdef.permission === READONLY && !sudo) 
			{
				return setReturn(404, 'readonly:' + token, 'false');
			}
			if (tdef.writeOnce && dat[token] && dat[token]!=value) 
			{
				if (config.checkSetValues)
					return setReturn(351, 'write only once', 'false');
				else toleratedFailure=true;
			}
			if (path.length)  
			{ 
				return setReturn(401, 'Unknown element', 'false');
			}
			if (tdef.dependsOn) {
				extra.parent.push(dat);
				var dep = tdef.dependsOn.split(" ");
				for (di=dep.length; di--;) 
				{
					var dj = extra.parent.length-1;
					var dp = dep[di].split(".");
					var dpar = extra.parent;
					if (dpar[dpar.length-dp.length][dp.pop()]===undefined)
					{
						return setReturn(408, 'dependency on ..' + dep[di], 'false');
					}
				}
			}
			result = tdef.type.isValid(value, tdef, extra);
			if (extra.error) 
			{
				if (config.checkSetValues) 
					return setReturn(extra.error.code, extra.error.diagnostic, 'false');
				else toleratedFailure=true;
			}
			if (!result) 
			{
				if (token=="session_time") {
					config.time_from_lms=true;
					fixedFailure=true;
				}
				if (config.checkSetValues)
					return setReturn(406, 'value not valid', 'false');
				else toleratedFailure=true;
			}
			
			if (value.indexOf("{order_matters")==0)
			{
				window.order_matters = true;
			} 

			dat[token] = value;
			dirty = true;
			return setReturn(0, '', 'true');
		}
		else // getter
		{	
			if(token == "store" && dat["readable"] != undefined && dat["readable"] == 0) {
				return setReturn(405, 'writeonly: ' + token, '');
			} 
			if (tdef.permission === WRITEONLY && !sudo) 
			{
				return setReturn(405, 'writeonly:' + token, '');
			}
			else if (path.length)  
			{ 
				return setReturn(401, 'Unknown element', '');
			}
			else if (tdef.getValueOf) 
			{
//				return setReturn(0, '', tdef.getValueOf(tdef, tdat));
				result = setReturn(0, '', tdef.getValueOf(tdef, tdat));
				if(result.error) {
					return setReturn(result.error, '', '');
				} else {
					return setReturn(0, '', result);
				}
			}
			else if (tdat===undefined || tdat===null)
			{
				if (tdef['default']) 
				{
					return setReturn(0, '', tdef['default']);
				}
				else
				{
					return setReturn(403, 'not initialized ' + token, '');
				}
			} 
			else
			{
				if (window.order_matters) 
				{
					window.order_matters = false;
				} 
				return setReturn(0, '', String(tdat));
			}
		}
	}
	
	/**
	 *	@access private
	 *	@param {number}
	 *	@param {string}  
	 *	@param {string}  
	 *	@return {string} 
	 */	 
	function setReturn(errCode, errInfo, returnValue) 
	{
		if (errCode>-1) 
		{
			top.status = [(new Date()).toLocaleTimeString(), errCode, errInfo].join(", ");
		}
		error = errCode;
		diagnostic = (typeof(errInfo)=='string') ? errInfo : '';
		return returnValue;
	}

	/**
	 * useful for transmitting Milliseconds if logActive
	 */
	function getMsecSinceStart()
	{
		return currentTime()-msec;
	}

	// private constants: API states
	var NOT_INITIALIZED = 0;
	var RUNNING = 1;
	var TERMINATED = 2;

	// private constants: permission
	var READONLY  = 1;
	var WRITEONLY = 2;
	var READWRITE = 3;

	// private properties
	var state = NOT_INITIALIZED;
	var error = 0;
	var diagnostic = '';
	var dirty = false;
	var msec = currentTime(); // if session time not set by sco, msec will used as starting time in onterminate
	var me = this; // reference to API for use in methods
	var commitByTerminate=false; //when commit ist startet by terminate, then do not send log if logActive
	var total_time_at_initialize; //to can store total_time with each commit
	
	// possible public methods
	var methods = 
	{
		'Initialize' : Initialize,
		'Terminate' : Terminate,
		'GetValue' : GetValue,
		'GetValueIntern' : GetValueIntern,		
		'SetValue' : SetValue,
		'SetValueIntern' : SetValueIntern,
		'Commit' : Commit,
		'GetLastError' : GetLastError,
		'GetErrorString' : GetErrorString,
		'GetDiagnostic' : GetDiagnostic
	};
		
	// bind public methods 
	for (var k in Runtime.methods) 
	{
		me[k] = methods[k];
	}
	
}

Runtime.prototype.version = "1.0";

Runtime.apiname = "API_1484_11";

Runtime.errors = 
{
	  0 : {code:   0, message: 'No error'},
	101 : {code: 101, message: 'General Exeption'},
	102 : {code: 102, message: 'General Initialization Failure'},
	103 : {code: 103, message: 'Already Initialized'},
	104 : {code: 104, message: 'Content Instance Terminated'},
	111 : {code: 111, message: 'General Termination Failure'},
	112 : {code: 112, message: 'Termination Before Initialization'},
	113 : {code: 113, message: 'Termination After Termination'},
	122 : {code: 122, message: 'Retrieve Data Before Initialization'},
	123 : {code: 123, message: 'Retrieve Data After Termination'},
	132 : {code: 132, message: 'Store Data Before Initialization'},
	133 : {code: 133, message: 'Store Data After Termination'},
	142 : {code: 142, message: 'Commit Before Initialization'},
	143 : {code: 143, message: 'Commit After Termination'},
	201 : {code: 201, message: 'General Argument Error'}, 
	301 : {code: 301, message: 'General Get Failure'},
	351 : {code: 351, message: 'General Set Failure'}, 
	391 : {code: 391, message: 'General Commit Failure'},
	401 : {code: 401, message: 'Undefined Data Model Element'},
	402 : {code: 402, message: 'Unimplemented Data Model Element'},
	403 : {code: 403, message: 'Data Model Element Value Not Initialized'},
	404 : {code: 404, message: 'Data Model Element Is Read Only'},
	405 : {code: 405, message: 'Data Model Element Is Write Only'},
	406 : {code: 406, message: 'Data Model Element Type Mismatch'},
	407 : {code: 407, message: 'Data Model Element Value Out Of Range'},
	408 : {code: 408, message: 'Data Model Dependency Not Established'}
};

Runtime.methods = 
{
	'Initialize' : 'Initialize', 
	'Terminate' : 'Terminate', 
	'GetValue' : 'GetValue', 
	'GetValueIntern' : 'GetValueIntern', 
	'SetValue' : 'SetValue', 
	'SetValueIntern' : 'SetValueIntern',
	'Commit' : 'Commit', 
	'GetLastError' : 'GetLastError', 
	'GetErrorString' : 'GetErrorString', 
	'GetDiagnostic' : 'GetDiagnostic'
};

Runtime.models = 
{
	'cmi' : new function() { // implements API_1484_11

		// private constants: permission
		var READONLY  = 1;
		var WRITEONLY = 2;
		var READWRITE = 3;
		
		function getDelimiter (str, typ, extra) 
		{
			var redelim = new RegExp("^({(" + typ + ")=([^}]*)})?([\\s\\S]*)$");
			var rebool = /^(true|false)$/;
			var m = str.match(redelim);
			if (m[2] && (m[2]==="lang" && !LangType.isValid(m[3]) || m[2]!=="lang" && !BooleanType.isValid(m[3]))) 
			{
				extra.error = {code: 406, diagnostic: typ + ' not recognized: ' + m[3]};
			}
			return m[4]; 
		}

		var AudioCaptioningState = { isValid : function (value) {
			return (/^-1|0|1$/).test(value);
		}};
		
		var BooleanType = { isValid : function (value) {
			return (/^(true|false)$/).test(value);
		}};
		
		var CompletionState = { isValid : function (value) {
			var valueRange = {'completed':1, 'incomplete':2, 'not attempted':3, 'unknown':4};
			return valueRange[value]>0;}
		};
		
		var CreditState = { isValid : function (value) {
			var valueRange = {'credit':1, 'no-credit':2};
			return valueRange[value]>0;}
		};
		
		var EntryState = { isValid : function (value) {
			var valueRange = {'ab-initio':1, 'resume':2, '':3};
			return valueRange[value]>0;}
		};
		
		var ExitState = { isValid : function (value) {
			var valueRange = {'time-out':1, 'suspend':2, 'logout':3, 'normal':4, '':5};
			return valueRange[value]>0;}
		};
		
		var InteractionType = { isValid : function (value) {
			var valueRange = {'true-false':1, 'choice':2, 'fill-in':3, 'long-fill-in':4, 'matching':5, 'performance':6, 'sequencing':7, 'likert':8, 'numeric':9, 'other':10};
			return valueRange[value]>0;
		}};
		
		var Interval = { isValid : function (value) {
			return Duration.parse(value)!==null;
		}};
		
		var LangType = { isValid : function (value) { // general type
			var relang = /^(aa|ab|af|ak|sq|am|ar|an|hy|as|av|ae|ay|az|ba|bm|eu|be|bn|bh|bi|bo|bs|br|bg|my|ca|cs|ch|ce|zh|cu|cv|kw|co|cr|cy|cs|da|de|dv|nl|dz|el|en|eo|et|eu|ee|fo|fa|fj|fi|fr|fr|fy|ff|ka|de|gd|ga|gl|gv|el|gn|gu|ht|ha|he|hz|hi|ho|hr|hu|hy|ig|is|io|ii|iu|ie|ia|id|ik|is|it|jv|ja|kl|kn|ks|ka|kr|kk|km|ki|rw|ky|kv|kg|ko|kj|ku|lo|la|lv|li|ln|lt|lb|lu|lg|mk|mh|ml|mi|mr|ms|mk|mg|mt|mo|mn|mi|ms|my|na|nv|nr|nd|ng|ne|nl|nn|nb|no|ny|oc|oj|or|om|os|pa|fa|pi|pl|pt|ps|qu|rm|ro|ro|rn|ru|sg|sa|sr|hr|si|sk|sk|sl|se|sm|sn|sd|so|st|es|sq|sc|sr|ss|su|sw|sv|ty|ta|tt|te|tg|tl|th|bo|ti|to|tn|ts|tk|tr|tw|ug|uk|ur|uz|ve|vi|vo|cy|wa|wo|xh|yi|yo|za|zh|zu|aar|abk|ace|ach|ada|ady|afa|afh|afr|ain|aka|akk|alb|ale|alg|alt|amh|ang|anp|apa|ara|arc|arg|arm|arn|arp|art|arw|asm|ast|ath|aus|ava|ave|awa|aym|aze|bad|bai|bak|bal|bam|ban|baq|bas|bat|bej|bel|bem|ben|ber|bho|bih|bik|bin|bis|bla|bnt|bod|bos|bra|bre|btk|bua|bug|bul|bur|byn|cad|cai|car|cat|cau|ceb|cel|ces|cha|chb|che|chg|chi|chk|chm|chn|cho|chp|chr|chu|chv|chy|cmc|cop|cor|cos|cpe|cpf|cpp|cre|crh|crp|csb|cus|cym|cze|dak|dan|dar|day|del|den|deu|dgr|din|div|doi|dra|dsb|dua|dum|dut|dyu|dzo|efi|egy|eka|ell|elx|eng|enm|enm|epo|est|eus|ewe|ewo|fan|fao|fas|fat|fij|fil|fin|fiu|fon|fra|fre|frm|fro|frr|frs|fry|ful|fur|gaa|gay|gba|gem|geo|ger|gez|gil|gla|gle|glg|glv|gmh|goh|gon|gor|got|grb|grc|gre|grn|gsw|guj|gwi|hai|hat|hau|haw|heb|her|hil|him|hin|hit|hmn|hmo|hrv|hsb|hun|hup|hye|iba|ibo|ice|ido|iii|ijo|iku|ile|ilo|ina|inc|ind|ine|inh|ipk|ira|iro|isl|ita|jav|jbo|jpn|jpr|jrb|kaa|kab|kac|kal|kam|kan|kar|kas|kat|kau|kaw|kaz|kbd|kha|khi|khm|kho|kik|kin|kir|kmb|kok|kom|kon|kor|kos|kpe|krc|krl|kro|kru|kua|kum|kur|kut|lad|lah|lam|lao|lat|lav|lez|lim|lin|lit|lol|loz|ltz|lua|lub|lug|lui|lun|luo|lus|mac|mad|mag|mah|mai|mak|mal|man|mao|map|mar|mas|may|mdf|mdr|men|mga|mic|min|mis|mkd|mkh|mlg|mlt|mnc|mni|mno|moh|mol|mon|mos|mri|msa|mul|mun|mus|mwl|mwr|mya|myn|myv|nah|nai|nap|nau|nav|nbl|nde|ndo|nds|nep|new|nia|nic|niu|nld|nno|nob|nog|non|nor|nqo|nso|nub|nwc|nya|nym|nyn|nyo|nzi|oci|oji|ori|orm|osa|oss|ota|oto|paa|pag|pal|pam|pan|pap|pau|peo|per|phi|phn|pli|pol|pon|por|pra|pro|pus|que|raj|rap|rar|roa|roh|rom|ron|rum|run|rup|rus|sad|sag|sah|sai|sal|sam|san|sas|sat|scc|scn|sco|scr|sel|sem|sga|sgn|shn|sid|sin|sio|sit|sla|slk|slo|slv|sma|sme|smi|smj|smn|smo|sms|sna|snd|snk|sog|som|son|sot|spa|sqi|srd|srn|srp|srr|ssa|ssw|suk|sun|sus|sux|swa|swe|syr|tah|tai|tam|tat|tel|tem|ter|tet|tgk|tgl|tha|tib|tig|tir|tiv|tkl|tlh|tli|tmh|tog|ton|tpi|tsi|tsn|tso|tuk|tum|tup|tur|tut|tvl|twi|tyv|udm|uga|uig|ukr|umb|und|urd|uzb|vai|ven|vie|vol|vot|wak|wal|war|was|wel|wen|wln|wol|xal|xho|yao|yap|yid|yor|ypk|zap|zen|zha|zho|znd|zul|zun|zxx|zza|i|x)(-([a-z]{2}|[a-z0-9][-a-z0-9]{2,7}))?$/i;
			return relang.test(value);
		}};
		
		var LanguageType = { isValid : function (value) { // preferences.language
			return value==="" || LangType.isValid(value);
		}};
		
		var ShortIdentifierType = { isValid : function (value) {
			// parsable as Uri with restricted set of characters and not empty
			var reuri = /^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/; 
			var rechars = /^[-~\.\:\/\?#\[\]@\!\$&'\(\)\*+,;=\w]{1,}$/; 
			return reuri.test(value) && rechars.test(value) && value.indexOf("[.]")===-1 &&  value.indexOf("[,]")===-1;  
		}};
			
		var LocalizedString = { isValid : function (value, definition, extra) {
			var val = getDelimiter(value, 'lang', extra);
			return CharacterString.isValid(val, definition, {max : definition.max ? definition.max+20 : undefined});}
		};
		
		var ModeState = { isValid : function (value) {
			var valueRange = {'browse':1, 'normal':2, 'review':3};
			return valueRange[value]>0;}
		};
		
		var ResponseType = { isValid : function (value, definition, extra) {
			
			var val, i;
			var parents = extra.parent;
			var ispattern = !parents[parents.length-1].id;
			var correct_responses = parents[parents.length-2].correct_responses || [];
			var parent = parents[parents.length-(ispattern+1)];
			var keys = {}; // hashtable to detect violations to uniqueness
			
			// check if patterns count exceeds SPM
			// cannot be checked above, because it's dependency on type  
			if (correct_responses.length) 
			{
				if (extra.index >= {
					'true-false' : 1,
					'choice' : 10,
					'fill-in' : 5,
					'long-fill-in' : 5,
					'likert' : 1,
					'matching' : 5,
					'performance' : 5,
					'sequencing' : 5,
					'numeric' : 1,
					'other' : 1			
				}[parent.type]) 
				{
					extra.error = {code: 351, diagnostic: 'array size exceeded in ' + parent.type + ' response'};
					return false;
				}
			}
						
			switch (parent.type)
			{
			
				case 'true-false':
					return BooleanType.isValid(value);

				case 'choice':
					val = value.split("[,]");
					if (val.length>36) 
					{
						extra.error = {code: 351};
					}
					if (val.length===1 && !val[0]) 
					{
						return true;
					}
					for (i=val.length; i--;) 
					{						
						if (keys[val[i]] || !ShortIdentifierType.isValid(val[i])) 
						{
							return false;
						}
						keys[val[i]] = true;
					}
					if (correct_responses) 
					{
						for (i=correct_responses.length; i--;) 
						{
							if (extra.index!==i && correct_responses[i].pattern===value) 
							{
								extra.error = {code: 351};
							}
						}
					}
					return !extra.error;
					
				case 'fill-in':
					val = value;
					val = getDelimiter(val, 'case_matters', extra);
					val = getDelimiter(val, 'order_matters', extra);
					// the case matter delimiter may appear before or after the order matters delimiter,  so do it again
					val = getDelimiter(val, 'case_matters', extra); 
					val = val.split("[,]");
					if (val.length > 36) 
					{
						extra.error = {code: 351}; 
					}
					for (i=val.length; i--;) 
					{
						if (extra.error || !LocalizedString.isValid(val[i], {min: 0, max: 250}, extra)) 
						{
							return false;
						}
					} 						
					return true;
					
				case 'long-fill-in':
					val = getDelimiter(value, 'case_matters', extra);
					val = getDelimiter(val, 'lang', extra).data;
					return !extra.error && (/^.{0,4000}$/).test(val);
					
				case 'likert':
					return ShortIdentifierType.isValid(value); 
					
				case 'matching':
					val = value.split("[,]");
					if (val.length>36) {
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) 
					{
						val[i] = val[i].split("[.]");
						if (val[i].length!==2 ||
							!ShortIdentifierType.isValid(val[i][0]) ||
							!ShortIdentifierType.isValid(val[i][1])) 
						{
							return false;
						} 
					}
					return !extra.error;
					
				case 'performance':
					val = getDelimiter(value, 'order_matters', extra);
					val = val.split("[,]");
					if (val.length>250) 
					{
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) 
					{
						val[i] = val[i].split("[.]");
						if (val[i].length!==2 || val[i][0]!=="" && !ShortIdentifierType.isValid(val[i][0])) 
						{
							return false;
						} 
					}
					return !extra.error;

				case 'sequencing':
					val = value.split("[,]");
					if (val.length>36) 
					{
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) 
					{
						if (!ShortIdentifierType.isValid(val[i])) 
						{
							return false;
						}
					}
					if (correct_responses) 
					{
						for (i=correct_responses.length; i--;) 
						{
							if (extra.index===i && correct_responses[i].pattern===value) 
							{
								extra.error = {code: 351};
							}
						}
					}
					return !extra.error;

				case 'numeric':
					if (!ispattern) 
					{
						return RealType.isValid(value, {}, {});
					}
					else 
					{
						val = value.split("[:]");
						val[0] = !val[0] ? Number.NEGATIVE_INFINITY :
							RealType.isValid(val[0], {}, {}) ? parseFloat(val[0]) : NaN;
						val[1] = !val[1] ? Number.POSITIVE_INFINITY : 
							RealType.isValid(val[1], {}, {}) ? parseFloat(val[1]) : NaN;
						return !isNaN(val[0]) && !isNaN(val[1]) && val[0]<=val[1]; 
					}

				case 'other':
					return value.length <= 4000;
					
			} // end type switch

		}};
		
		var ResultState = { isValid : function (value) {
			var valueRange = {'correct':1, 'incorrect':2, 'unanticipated':3, 'neutral':4};
			return valueRange[value]>0 || RealType.isValid(value, {}, {});}
		};
		
		var SuccessState = { isValid : function (value) {
			var valueRange = {'passed':1, 'failed':2, 'unknown':3};
			return valueRange[value]>0;}
		};
		
		var Time = { isValid : function (value) {
			return DateTime.parse(value)!==null;
		}};
		
		var TimeLimitAction = { isValid : function (value) {
			var valueRange = {'exit,message':1, 'continue,message':2, 'exit,no message':3, 'continue,no message':4};
			return valueRange[value]>0;
		}};
		
		var Uri = { isValid : function (value, definition, extra) {
			var re_uri = /^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/;
			var re_char = /[\s]/;
			var re_urn = /^urn:[a-z0-9][-a-z-0-9]{1,31}:.+$/;
			var m = value.match(re_uri);
			return Boolean(m && m[0] && !re_char.test(m[0]) && m[0].length<=4000 && (m[2]!=="urn" || re_urn.test(m[0])));
		}};
		
		var CharacterString = { isValid : function (value, definition, extra) {
			var min = extra.min ? extra.min : definition.min;
			var max = extra.max ? extra.max : definition.max;
			var pattern = extra.pattern ? extra.pattern : definition.pattern;
			if ((min && String(value).length < min) || (max && String(value).length > max)) {
				extra.error = {code: 407};
				return false;
			} else if (pattern && !pattern.test(value)) {
				return false;
			} else {
				return true;
			}
		}};
		
		var RealType = { isValid : function (value, definition, extra) {
			var pattern = extra.pattern ?  extra.pattern : definition.pattern;
			var min = definition && typeof definition.min === "number" ? definition.min :  Number.NEGATIVE_INFINITY;
			var max = definition && typeof definition.max === "number" ? definition.max :  Number.POSITIVE_INFINITY;
			if (!(/^-?\d{0,32}(\.\d{1,32})?$/).test(value) || value == '') 
			{
				return false;
			} 
			else if (Number(value) < min || Number(value) > max) 
			{
				extra.error = {code: 407};
				return false;
			} 
			else if (pattern && !pattern.test(value)) 
			{
				return false;
			} 
			else 
			{
				return true;
			}
		}};
				
		/**
		 * data model definition of API_1484_11 (see 'SCORM Run-Time Environment Version 1.3 on www.adlnet.org)
		 * the definition is a nested ECMA object with each node having the follwing properties
		 * maxOccur, type, permission, children, min, max, pattern, default
		 */
		this.cmi = {maxOccur : 1, type : Object, permission: READWRITE,
			children : { 
				comments_from_learner : {maxOccur: 250, type: Array, permission: READWRITE, 
					children: {
						comment : {type: LocalizedString, max: 4000, permission: READWRITE},
						timestamp : {type: Time, permission: READWRITE},
						location : {type: CharacterString, max: 250, permission: READWRITE}
					},
					mapping : {
						name: 'comment', 
						func: function (d) {return !d.sourceIsLMS;}, 
						refunc: function (d) {return ['sourceIsLMS', 0];}}
				},
				comments_from_lms : {maxOccur: 250, type: Array, permission: READONLY, 
					children: {
						comment : {type: LocalizedString, max: 4000, permission: READONLY},
						timestamp: {type: Time, permission: READONLY},
						location : {type: CharacterString, max: 250, permission: READONLY}
					},
					mapping : {	
						name: 'comment', 
						func: function (d) {return d.sourceIsLMS;}, 
						refunc: function (d) {return ['sourceIsLMS', 1];}}
				},
				completion_status : {type: CompletionState, permission: READWRITE, 'default' : 'unknown', getValueOf : function (tdef, tdat) {
					// special case see Chap. 4.2.4.1
					var state = tdat===undefined ? tdef['default'] : String(tdat);
					var norm=currentAPI.GetValueIntern("cmi.completion_threshold");
					var score=currentAPI.GetValueIntern("cmi.progress_measure");
					if (norm) {
						norm=parseFloat(norm);
						if (norm && score) {
							score=parseFloat(score);
							if (score>=norm) {
								state = "completed";
							} else if (score<norm) {
								state = "incomplete";
							}
						} else {
							state="unknown";
						}
					}
					if (state=="undefined" || state=="" || state == null || state == "null") {
						state = "unknown";
					}
					currentAPI.SetValueIntern("cmi.completion_status",state);
					return state;
				}},
				completion_status_SetBySco : {type: BooleanType, permission: READWRITE, 'default': 'false'},
				completion_threshold : {type: RealType, min: 0, max: 1, permission: READONLY},
				credit : {type: CreditState, permission: READONLY, 'default' : 'credit'},
				entry : {type: EntryState, permission: READONLY, 'default' : 'ab-initio'},
				exit : {type: ExitState, permission: WRITEONLY, 'default' : ''},
				interactions: {maxOccur: 250, type: Array, permission: READWRITE, 
					// unique: 'id' // compare REQ_64.3.5 and REQ_100-5.3 for a funny example of inconsistent specification
					children: {
						correct_responses: {maxOccur: 250, type: Array, permission: READWRITE, 
							children: {
								pattern : {type: ResponseType, permission: READWRITE, dependsOn: '.id .type'}
							}
						},
						description:  {type: LocalizedString, max: 250, permission: READWRITE, dependsOn: 'id'},
						id: {type: Uri, max: 4000, permission: READWRITE, minOccur: 1},
						latency:  {type: Interval, permission: READWRITE, dependsOn: 'id'},
						learner_response:  {type: ResponseType, permission: READWRITE, dependsOn: 'id type'},
						objectives: {maxOccur: 250, type: Array, permission: READWRITE, unique: 'id',
							children : { 
								id : {type: Uri, max: 4000, permission: READWRITE, dependsOn: 'interactions.id'}
							}
						},
						result:  {type: ResultState, permission: READWRITE, dependsOn: 'id'},
						timestamp: {type: Time, permission: READWRITE, dependsOn: 'id'},
						type: {type: InteractionType, permission: READWRITE, dependsOn: 'id'},
						weighting:  {type: RealType, permission: READWRITE, dependsOn: 'id'}
					}
				}, 
				launch_data : {type: CharacterString, max: 4000, permission: READONLY, 'default' : ''},
				learner_id : {type: CharacterString, max: 4000, permission: READONLY, 'default' : ''},
				learner_name : {type: LocalizedString, max: 250, permission: READONLY, 'default' : ''},
				learner_preference: {type: Object, permission: READONLY, 
					children : {
						audio_level: {type: RealType, min: 0.0, permission: READWRITE, "default": '1'},
						language : {type: LanguageType, permission: READWRITE, 'default': ''}, 
						delivery_speed : {type: RealType, min: 0, permission: READWRITE, 'default': '1'}, 
						audio_captioning : {type: AudioCaptioningState, permission: READWRITE, 'default': '0'}
					},
					mapping : ['audio_level', 'language', 'delivery_speed', 'audio_captioning']
				}, 
				location : {type: CharacterString, max: 1000, permission: READWRITE, 'default' : ''},
				max_time_allowed : {type: Interval, permission: READONLY},
				mode: {type: ModeState, permission: READONLY, 'default' : 'normal'},
				objectives: {maxOccur: 100, type: Array, permission: READWRITE, unique: 'id', 
					children: {
						completion_status: {type: CompletionState, permission: READWRITE, 'default': 'unknown', dependsOn: 'id'},
						completion_status_SetBySco : {type: BooleanType, permission: READWRITE, 'default': 'false'},
						description:  {type: LocalizedString, max: 250, permission: READWRITE, dependsOn: 'id'},
						id: {type: Uri, max: 4000, permission: READWRITE, writeOnce: true},
						progress_measure : {type: RealType, min: 0, max: 1, permission: READWRITE},
						score: {type: Object, permission: READWRITE, 
							children: {
								scaled : {type: RealType, min: -1, max: 1, permission: READWRITE, dependsOn: 'objectives.id'},
								raw : {type: RealType, permission: READWRITE, dependsOn: 'objectives.id'},
								min : {type: RealType, permission: READWRITE, dependsOn: 'objectives.id'},
								max : {type: RealType, permission: READWRITE, dependsOn: 'objectives.id'}
							},
							mapping : ['scaled', 'raw', 'min', 'max']
						},
						success_status: {type: SuccessState, permission: READWRITE, 'default': 'unknown', dependsOn: 'id'},
						success_status_SetBySco : {type: BooleanType, permission: READWRITE, 'default' : 'false'}
					},
					mapping : {
						name: 'objective', 
						func: function (d) {return d.objectiveID || d.cmi_node_id;}
					}
				},
				progress_measure : {type: RealType, min: 0, max: 1, permission: READWRITE},
				scaled_passing_score : {type: RealType, min: -1, max: 1, permission: READONLY},
				score: {type: Object, permission: READWRITE, 
					children: {
						scaled : {type: RealType, min: -1, max: 1, permission: READWRITE},
						raw : {type: RealType, permission: READWRITE},
						min : {type: RealType, permission: READWRITE},
						max : {type: RealType, permission: READWRITE}
					},
					mapping : ['scaled', 'raw', 'min', 'max']
				},
				session_time : {type: Interval, permission: WRITEONLY},
				success_status : {type: SuccessState, permission: READWRITE, 'default' : 'unknown', getValueOf : function (tdef, tdat) {
					var state = tdat===undefined ? tdef['default'] : String(tdat);
					var norm=currentAPI.GetValueIntern("cmi.scaled_passing_score");
					var score=currentAPI.GetValueIntern("cmi.score.scaled");
					if (norm) {
						norm=parseFloat(norm);
						if (norm && score) {
							score=parseFloat(score);
					   		if (score>=norm) {
								state = "passed";
					  		} else if (score<norm) {
								state = "failed";
					  		} 
						} else {
							state="unknown";
						}
					}
					currentAPI.SetValueIntern("cmi.success_status",state);
					return state;
				}},
				suspend_data : {type: CharacterString, max: 64000, permission: READWRITE},
				time_limit_action : {type: TimeLimitAction, permission: READONLY, "default": "continue,no message"},
				total_time : {type: Interval, permission: READONLY, 'default' : 'PT0H0M0S'},
				//Not part of CMI, but used to determine whether the success_status has been set by the sco
				success_status_SetBySco: {type: BooleanType, permission: READWRITE, 'default': 'false'}
			} 

		};
	}, // end cmi model
	
	'adl' : new function() { // implements ADL Extensions to API_1484_11
		// private constants: permission
		var READONLY  = 1;
		var WRITEONLY = 2;
		var READWRITE = 3;
	
		
		var Uri = { isValid : function (value, definition, extra) {
			var re_uri = /^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/;
			var re_char = /[\s]/;
			var re_urn = /^urn:[a-z0-9][-a-z-0-9]{1,31}:.+$/;
			var m = value.match(re_uri);
			return Boolean(m && m[0] && !re_char.test(m[0]) && m[0].length<=4000 && (m[2]!=="urn" || re_urn.test(m[0])));
		}};
		
		var CharacterString = { isValid : function (value, definition, extra) {
			var min = extra.min ? extra.min : definition.min;
			var max = extra.max ? extra.max : definition.max;
			var pattern = extra.pattern ? extra.pattern : definition.pattern;
			if ((min && String(value).length < min) || (max && String(value).length > max)) {
				extra.error = {code: 407};
				return false;
			} else if (pattern && !pattern.test(value)) {
				return false;
			} else {
				return true;
			}
		}};
		var NavRequest = { isValid : function (value, min, max, pattern) {
			return (/^(\{target=[^\}]+\}(choice|jump)|continue|previous|exit|exitAll|abandon|abandonAll|suspendAll|_none_)$/).test(value);}
		};
		var NavState = { isValid : function (value, min, max, pattern) {
			return (/^(true|false|unknown)$/).test(value);}
		};
		var NavTarget = {
			isValid : function (value, min, max, pattern) {
				return (/^(true|false|unknown)$/).test(value);
			},
			getValue : function (param, def) {
				var m = String(param).match(/^\{target=([^\}]+)\}$/); 
				if (m && m[1]) {/* id identified, lookup in activity tree */}
				return def['default'];
			}
		};
		this.adl = {maxOccur : 1, type : Object, permission: READWRITE,
			children : {
				nav : {maxOccur : 1, type : Object, permission: READWRITE,
					children : { 
						request : {type: NavRequest, permission: READWRITE, 'default': '_none_'},
						request_valid : {type: Object, permission: READONLY,
							children : {
								'continue' : {type: NavState, permission: READONLY, 'default': 'unknown'},
								'previous' : {type: NavState, permission: READONLY, 'default': 'unknown'},
								// "adl.nav.request_valid.choice.{target=intro}"
								'choice' : {type: Function, permission: READONLY,
									children : {
										type: NavTarget, permission: READONLY, 'default': 'unknown'
									}
								},
								'jump' : {type: Function, permission: READONLY,
									children : {
										type: NavTarget, permission: READONLY, 'default': 'unknown'
									}
								}
							}//end children
						}
					}
				},
				data : {type: Array, permission: READWRITE, unique: 'id', 
							children : {
						id: {type: Uri, max: 4000, permission: READONLY, writeOnce: true, minOccur: 1},
						store: {type: CharacterString, max: 64000, permission: READWRITE, dependsOn : 'id',
						 	 getValueOf : function(tdef, tdat) {
								if(tdat == '' || tdat == null || tdat === "undefined") {
									return {error: 403};
								}
								return tdat;
							 }
						 }
					}
				}
			}
		};
	}
};  // end adl model

Runtime.onTerminate = function (data, msec) /// or user walks away
{
	// added to synchronize the new data. it might update the navigation
	//syncCMIADLTree();
	
	if (all("treeView")!=null) {
		updateNav(true);
	}
};

