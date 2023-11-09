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
