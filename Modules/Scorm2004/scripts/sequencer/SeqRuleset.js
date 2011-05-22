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
