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
	
