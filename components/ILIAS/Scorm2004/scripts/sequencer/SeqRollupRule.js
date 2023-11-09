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

