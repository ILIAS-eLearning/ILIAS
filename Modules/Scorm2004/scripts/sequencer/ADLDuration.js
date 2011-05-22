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
