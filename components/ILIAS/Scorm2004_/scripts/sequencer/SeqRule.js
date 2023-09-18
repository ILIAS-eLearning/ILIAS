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
