// JS port of ADL ADLDuration.java

// call ADLDuration() or ADLDuration({iFormat: format, iValue: value})
function ADLDuration(iOptions)  
{
	var iOptions = ilAugment({
		iFormat: FORMAT_SECONDS,
		iValue: 0
		}, iOptions );
	var iFormat = iOptions.iFormat;
	var iValue = iOptions.iValue;

	if ($iValue == 0)
	{
		mDuration = 0;
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
			mDuration = secs * 1000.0;
			break;
		}
		case FORMAT_SCHEMA:
		{
			// todo: make this work
            var locStart = iValue.indexOf('T');
            var loc = 0;
			if ( locStart != -1 )
			{
				// todo: make this work
				locStart++;
				loc = iValue.indexOf("H", locStart);
			
				if ( loc != -1 )
				{
					hours = iValue.substring(locStart, loc);
					mDuration = Double.parseDouble(hours) * 3600;
					locStart = loc + 1;
				}
				// todo: make this work
				loc = iValue.indexOf("M", locStart);
				if ( loc != -1 )
				{
					min = iValue.substring(locStart, loc);
					mDuration += min * 60;
					locStart = loc + 1;
				}
				// todo: make this work
				loc = iValue.indexOf("S", locStart);
				if ( loc != -1 )
				{
					sec = iValue.substring(locStart, loc);
					mDuration += Double.parseDouble(sec);
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
	UNKNOWN: -999,
	LT: -1,
	EQ: 0,
	GT: 1,
	FORMAT_SECONDS: 0,
	FORMAT_SCHEMA: 1,
	mDuration: 0.0,
	
	// todo: make this work
	round: (iValue)
	{
		iValue = iValue * 10;
		iValue = Math.rint(iValue);
		iValue = iValue / 10;
		return iValue;
	},
	
	// todo: make this work
	format: (iFormat)
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
				var sec = mDuration / 1000.0;
				out = sec;
				break;
			}
			case FORMAT_SCHEMA:
			{
				out = "";
				countHours = 0;
				countMin = 0;
				countSec = 0;

				temp = mDuration;
				
				if (temp >= .1)
				{
					temp = round(temp);
					if ( temp >= 3600 )
					{
						countHours = (long)(temp / 3600);
						temp %= 3600;
					}
					if ( temp > 60 )
					{
						countMin = (long)(temp / 60);
						temp %= 60;
					}
					countSec = round(temp);
				}

				out = "PT";
				
				if ( countHours > 0 )
				{
					out += Long.toString(countHours, 10);
					out +="H";
				}
				if ( countMin > 0 )
				{
					out += Long.toString(countMin, 10);
					out +="M";
				}
				if ( countSec > 0 )
				{
					out += countSec;
					out +="S";
				}
				break;
			}
		}
		return out;
	},
	
	add: (iDur)
	{
		mDuration += iDur.mDuration;
	},
	
	// todo: make this work
	compare: (iDur)
	{
		var relation = ADLDuration.UNKNOWN;
		
		if (mDuration < iDur.mDuration)
		{
			relation = ADLDuration.LT;
		}
		else if (mDuration == iDur.mDuration)
		{
			relation = ADLDuration.EQ;
		}
		else if (mDuration > iDur.mDuration)
		{
			relation = ADLDuration.GT;
		}
		return relation;
	}
}
