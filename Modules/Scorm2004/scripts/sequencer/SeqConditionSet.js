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
