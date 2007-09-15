/**
* Abstract controller class of the MCV pattern, extended by all controlllers.
*
* @author	Jeroen Wijering
* @version	1.7
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
	private var itemsPlayed:Number = 0;


	/** Constructor. **/
	function AbstractController(cfg:Object,fed:Object) {
		config = cfg;
		feeder = fed;
		if(config["shuffle"] == "true") {
			randomizer = new Randomizer(feeder.feed);
			currentItem = randomizer.pick();
		} else {
			currentItem = 0;
		}
		feeder.addListener(this);
	};


	/** Complete the build of the MCV cycle and start flow of events. **/
	public function startMCV(mar:Array) {};


	/** Receive events from the views. **/
	public function getEvent(typ:String,prm:Number) {
		trace("controller: "+typ+": "+prm);
		switch(typ) {
			case "playpause": 
				setPlaypause(prm);
				break;
			case "prev":
				setPrev();
				break;
			case "next":
				setNext();
				break;
			case "stop":
				setStop();
				break;
			case "scrub":
				setScrub(prm);
				break;
			case "volume":
				setVolume(prm);
				break;
			case "playitem":
				setPlayitem(prm);
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
			default:
				trace("controller: incompatible event received");
				break;
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


	/** Switch audiotrack on and off **/
	private function setAudio() {};


	/** Sending changes to all registered models. **/
	private function sendChange(typ:String,prm:Number):Void {
		for(var i=0; i<registeredModels.length; i++) {
			registeredModels[i].getChange(typ,prm);
		}
	};


	/** check with feedupdates if current item is also changed **/
	public function onFeedUpdate() {
		for(var i=0;i < feeder.feed.length; i++) {
			if (feeder.feed[i]['file'] == currentURL) {
				currentItem = i;
				sendChange("item",currentItem);
			}
		}
		if(feeder.feed[currentItem]['file'] != currentURL) {
			setStop();
			if(randomizer != undefined) {
				randomizer = new Randomizer(feeder.feed);
			}
			if(currentItem >= feeder.feed.length) {
				if(config["shuffle"] == "false") {
					currentItem = 0;
				} else {
					currentItem = randomizer.pick();
				}
				sendChange("item",currentItem);
			}
		}
	};


}