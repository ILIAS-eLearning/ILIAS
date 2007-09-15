/**
* A couple of commonly used string operations.
*
* @author	Jeroen Wijering
* @version	1.2
**/


class com.jeroenwijering.utils.StringMagic {

	
	/** Array with day string representations. **/
	static var DAYS:Array = Array("Sunday","Monday","Tuesday","Wednesday",
		"Thursday","Friday","Saturday");
	/** Array with month string representations. **/
	static var MONTHS_EN:Array= Array("January","February","March","April","May",
		"June","July","August","September","October","November","December");
	static var MONTHS_NL:Array= Array("Januari","Februari","Maart","April","Mei",
		"Juni","Juli","Augustus","September","Oktober","November","December");
	/** Supporting array to translate RFC2822 months to number. **/
	static var MONTH_INDEXES:Object = {January:0,February:1,March:2,April:3,
		May:4,June:5,July:6,August:7,September:8,October:9,November:10,
		December:11,Jan:0,Feb:1,Mar:2,Apr:3,May:4,Jun:5,Jul:6,Aug:7,Sep:8,
		Oct:9,Nov:10,Dec:11};
	static var TEXTS:Object = {
		en:Array(" days"," day"," hours"," hour",
			" minutes"," minute"," ago"),
		nl:Array(" dagen"," dag"," uur",
			" uur"," minuten"," minuut"," geleden")
	}


	/** 
	* Strip tags and breaks from a string
	* 
	* @param str	The string to process.
	* @return		The filered string.
	**/
	static function stripTagsBreaks(str:String):String {
		if(str.length == 0 || str == undefined) { return ""; }
		var tmp:Array = str.split("\n");
		str = tmp.join("");
		var tmp:Array = str.split("\r");
		str = tmp.join("");
		var i:Number = str.indexOf("<");
		while(i != -1) {
			var j = str.indexOf(">",i+1);
			j == -1 ? j = str.length-1: null;
			str = str.substr(0,i) + str.substr(j+1,str.length);
			i = str.indexOf("<",i);
		}
		return str;
	};


	/**
	* Chop string into a number of lines five lines max.
	*
	* @param str	The input string.
	* @param cap	The average number of characters a line should have.
	* @param nbr	The max number of lines the return can have.
	* @return		The string with linebreaks included.
	**/
	static function chopString(str:String,cap:Number,nbr:Number):String {
		for(var i=cap; i<str.length; i+=cap) {
			if(i == cap*nbr) {
				if(str.indexOf(" ",i-5) == -1) {
					return str;
				} else {
					return str.substr(0,str.indexOf(" ",i-5));
				}
			} else  if(str.indexOf(" ",i) > 0) {
				str = str.substr(0,str.indexOf(" ",i-3)) + "\n" +
					str.substr(str.indexOf(" ",i-3)+1);
			}
		}
		return str;
	};


	/** 
	* Add a leading zero and convert number to string.
	*
	* @param nbr	The number to convert.
	* @return		Te resulting string.
	**/
	static function addLeading(nbr:Number):String { 
		if(nbr < 10) { 
			return "0"+Math.floor(nbr); 
		} else { 
			return Math.floor(nbr).toString(); 
		}
	};


	/** 
	* Build a delaystring for a timestamp. 
	* 
	* @param stp	The stamp the delay should be calculated of.
	* @param sht	Use short notation or not.
	* @param lan	Language to use, defaults to english
	**/
	static function delayString(stp:Number,sht:Boolean,lan:String):String {
		lan == undefined ? lan = "en": null;
		var dat = new Date();
		var dif = Math.round(dat.valueOf()/1000) - stp;
		dif < 0 || isNaN(dif) ? dif = 0: null;
		if(sht == true) {
			var hr:Number = Math.floor(dif/3600);
			var mi:Number = Math.floor(dif%3600/60);
			return hr+":"+StringMagic.addLeading(mi) + 
				StringMagic.TEXTS[lan][6];
		} else { 
			var ret:String = "";
			var dy:Number = Math.floor(dif/86400);
			if(dy >1) {
				ret = dy + StringMagic.TEXTS[lan][0]; 
			} else if (dy == 1) { 
				ret = dy+StringMagic.TEXTS[lan][1];
			}
			var hr:Number = Math.floor(dif%86400/3600);
			if(hr > 1) { 
				ret.length > 0 ? ret = ret+", "+hr+StringMagic.TEXTS[lan][2]:
					ret=hr+StringMagic.TEXTS[lan][2];
			} else if (hr == 1) {
				ret.length > 0 ? ret = ret+", "+hr+StringMagic.TEXTS[lan][3]:
					ret = hr+StringMagic.TEXTS[lan][3];
			}
			var mi:Number = Math.floor(dif%3600/60);
			if(mi == 1 && dy == 0) { 
				ret.length>0 ? ret=ret+", "+mi+StringMagic.TEXTS[lan][5]:
					ret=mi+StringMagic.TEXTS[lan][5];
			} else if(!(mi == 0 && ret.length > 0) && dy == 0) {
				ret.length>0 ? ret=ret+", "+mi+StringMagic.TEXTS[lan][4]:
					ret=mi+StringMagic.TEXTS[lan][4];
			}
			return ret+StringMagic.TEXTS[lan][6];
		}
	};


	/** Remove char from string **/
	static function removeCharFromString(str:String, char:String):String {
		var my_array:Array = str.split(char);
		var newStr = new String();
		for (var i = 0; i<my_array.length; i++) {
			trace(my_array[i]);
			newStr += my_array[i];
		}
		return newStr;
	};


}