/**
* Abstract model class of the players MCV pattern, extended by all models.
*
* @author	Jeroen Wijering
* @version	1.4
**/


import com.jeroenwijering.players.*;


class com.jeroenwijering.players.AbstractModel {


	/** a list of all registered views **/
	private var registeredViews:Array;
	/** a reference to the controller **/
	private var controller:AbstractController;
	/** reference to the config array **/
	private var config:Object;
	/** reference to the feed array **/
	private var feeder:Object;
	/** item that's currently playing **/
	private var currentItem:Number;
	/** url of the item that's currently used by this model **/
	private var currentURL:String;
	/** array with extensions used by a model **/
	private var mediatypes:Array;
	/** boolean to check if a model is currently active **/
	private var isActive:Boolean;
	/** current playhead position **/
	private var currentPosition:Number;


	/** Constructor. **/
	function AbstractModel(vws:Array,ctr:AbstractController,
		cfg:Object,fed:Object) {
		registeredViews = vws;
		controller = ctr;
		config = cfg;
		feeder = fed;
	};


	/** Receive changes from the PlayerController. **/
	public function getChange(typ:String,prm:Number):Void {
		trace("model: "+typ+": "+prm);
		switch(typ) {
			case "item":
				setItem(prm);
				break;
			case "start":
				if(isActive == true) { setStart(prm); }
				break;
			case "pause":
				if(isActive == true) { setPause(prm); }
				break;
			case "stop":
				if(isActive == true) { setStop(); }
				break;
			case "volume":
				setVolume(prm);
				break;
			default:
				trace("Model: incompatible change received");
				break;
		}
	};


	/** Set new item and check if the model should be the active one. **/
	private function setItem(idx:Number) {
		currentItem = idx;
		var fnd:Boolean = false;
		for (var i=0; i<mediatypes.length; i++) {
			if(feeder.feed[idx]["type"] == mediatypes[i]) {
				fnd = true;
			}
		}
		if(feeder.feed[idx]["start"] > 0) {
			currentPosition = feeder.feed[idx]["start"];
		}
		if(fnd == true) {
			isActive = true;
			sendUpdate("item",idx);
		} else {
			isActive = false;
		}
	};


	/** Start function. **/
	private function setStart(prm:Number) {};


	/** Pause function. **/
	private function setPause(prm:Number) {};


	/** Stop function. **/
	private function setStop() {};


	/** Set volume and pass through if active. **/
	private function setVolume(vol:Number) { 
		if(isActive == true) { sendUpdate("volume",vol); }
	};


	/** Send updates to the views. **/
	private function sendUpdate(typ:String,prm:Number,pr2:Number) {
		for(var i=0; i<registeredViews.length; i++) {
			registeredViews[i].getUpdate(typ,prm,pr2);
		}
		if(typ == 'size') {
			controller.getEvent(typ,prm,pr2);
		}
	};


	/** Send a "complete" event directly to the controller. **/
	private function sendCompleteEvent() {
		controller.getEvent("complete"); 
	};


}