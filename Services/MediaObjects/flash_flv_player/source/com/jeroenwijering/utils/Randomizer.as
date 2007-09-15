/**
* Pick random array indexes without having the same picked twice times.
* 
* @author	Jeroen Wijering
* @version	1.1
**/


class com.jeroenwijering.utils.Randomizer {


	/** a reference of the original array **/
	private var originalArray:Array;
	/** a copy of the original array **/
	private var bufferArray:Array;


	/** Constructor. **/
	public function Randomizer(arr:Array) {
		originalArray = arr;
		bufferArray = new Array();
	};


	/** Randomly pick an index from the array given. **/
	public function pick():Number {
		if(bufferArray.length == 0) {
			for(var k=0; k<originalArray.length; k++) {
				if(originalArray[k]['category'] != "preroll" &&
					originalArray[k]['category'] != "postroll") {
					bufferArray.push(k);
				}
			}
		}
		var ran:Number = random(bufferArray.length);
		var idx:Number = bufferArray[ran];
		bufferArray.splice(ran,1);
		return idx;
	};


}