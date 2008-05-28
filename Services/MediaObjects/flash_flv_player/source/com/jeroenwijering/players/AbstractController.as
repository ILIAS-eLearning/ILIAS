/**
* Abstract controller class of the MCV pattern, extended by all controlllers.
*
* @author	Jeroen Wijering
* @version	1.8
**/


import com.jeroenwijering.players.*;
import com.jeroenwijering.utils.*;


class com.jeroenwijering.players.AbstractController 
	implements com.jeroenwijering.feeds.FeedListener {


	/** Randomizer instance **/
	private var randomizer:Randomizer;
	/** array with all registered models **/
	private var registeredModels:Array;
	/** reference to the config array **/
	private var config:Object;
	/** reference to the feed array **/
	private var feeder:Object;
	/** Current item **/
	private var currentItem:Number;
	/** Current item **/
	private var currentURL:String;
	/** Current item **/
	private var isPlaying:Boolean;
	/** Number of items played: used for repeat=list **/
	private var itemsPlayed:Number;
	/** Array that saves the original video dimensions. **/
	private var sizes:Array;


	/** Constructor. **/
	function AbstractController(cfg:Object,fed:Object) {
		config = cfg;
		feeder = fed;
		feeder.addListener(this);
	};


	/** Complete the build of the MCV cycle and start flow of events. **/
	public function startMCV(mar:Array) {};


	/** Receive events from the views. **/
	public function getEvent(typ:String,prm:Number,pr2:Number) {
		//trace("controller: "+typ+": "+prm);
		switch(typ) {
			case "playpause": 
				setPlaypause();
				break;
			case "prev":
				if(noCommercial()) { setPrev(); }
				break;
			case "next":
				if(noCommercial()) { setNext(); }
				break;
			case "stop":
				if(noCommercial()) { setStop(); }
				break;
			case "scrub":
				if(noCommercial()) { setScrub(prm); }
				break;
			case "volume":
				setVolume(prm);
				break;
			case "playitem":
				if(noCommercial()) { setPlayitem(prm); }
				break;
			case "getlink":
				setGetlink(prm);
				break;
			case "fullscreen":
				setFullscreen();
				break;
			case "complete":
				setComplete();
				break;
			case "captions":
				setCaptions();
				break;
			case "audio":
				setAudio();
				break;
			case "size":
				saveSizes(prm,pr2);
				break;
			default:
				trace("controller: incompatible event received");
				break;
		}
	};


	/** Check for commercial **/
	private function noCommercial():Boolean {
		if(feeder.feed[currentItem]['category'] == 'commercial' ||
			feeder.feed[currentItem]['category'] == 'preroll' || 
			feeder.feed[currentItem]['category'] == 'postroll') {
			return false;
		} else {
			return true;
		}
	};


	/** PlayPause switch **/
	private  function setPlaypause() {};


	/** Play previous item. **/
	private  function setPrev() {};


	/** Play next item. **/
	private function setNext() {};


	/** Stop and clear item. **/
	private function setStop() {};


	/** Forward scrub number to model. **/
	private function setScrub(prm:Number) {};


	/** Play a new item. **/
	private function setPlayitem(itm:Number) {
		currentURL = feeder.feed[itm]['file'];
	};


	/** Get url from an item if link exists, else playpause. **/
	private function setGetlink(idx:Number) {};


	/** Determine what to do if an item is completed. **/
	private function setComplete() {};


	/** Volume event handler **/
	private function setVolume(prm:Number) {};


	/** Switch fullscreen mode **/
	private function setFullscreen() {};


	/** Switch captions on and off **/
	private function setCaptions() {};


	/** Switch captions on and off **/
	private function saveSizes(pr1:Number,pr2:Number) {
		sizes = new Array(pr1,pr2);
	};


	/** Switch audiotrack on and off **/
	private function setAudio() {};


	/** Sending changes to all registered models. **/
	private function sendChange(typ:String,prm:Number):Void {
		for(var i=0; i<registeredModels.length; i++) {
			registeredModels[i].getChange(typ,prm);
		}
	};


	/** check with feedupdates if current item is also changed **/
	public function onFeedUpdate(typ:String) {
		if(typ == 'new') {
			setStop();
			startMCV();
		} else  if (typ == 'add') {
			if (feeder.feed[currentItem+1]['file'] == currentURL) {
				currentItem++;
				sendChange("item",currentItem);
			}
			if(randomizer != undefined) {
				randomizer = new Randomizer(feeder.feed);
			}
		} else if(typ == 'remove') {
			if (feeder.feed[currentItem-1]['file'] == currentURL) {
				currentItem--;
				sendChange("item",currentItem);
				if(randomizer != undefined) {
					randomizer = new Randomizer(feeder.feed);
				}
			} else if(feeder.feed[currentItem]['file'] != currentURL) {
				setStop();
				startMCV();
			} else {
				if(randomizer != undefined) {
					randomizer = new Randomizer(feeder.feed);
				}
			}
		}
	};


}