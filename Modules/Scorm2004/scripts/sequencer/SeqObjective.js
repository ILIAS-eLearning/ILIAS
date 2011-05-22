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
