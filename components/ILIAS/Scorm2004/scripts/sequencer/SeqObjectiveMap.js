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
