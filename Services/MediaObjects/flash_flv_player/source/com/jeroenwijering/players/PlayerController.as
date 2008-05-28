/**
* User input management of the players MCV pattern.
*
* @author	Jeroen Wijering
* @version	1.12
**/


import com.jeroenwijering.players.AbstractController;
import com.jeroenwijering.utils.Randomizer;
import flash.geom.Rectangle;


class com.jeroenwijering.players.PlayerController extends AbstractController {


	/** use SharedObject to save current file, item and volume **/
	private var playerSO:SharedObject;
	/** save independent mute state **/
	private var muted:Boolean;


	/** Constructor, save arrays and set currentItem. **/
	function PlayerController(cfg:Object,fed:Object) {
		super(cfg,fed);
		playerSO = SharedObject.getLocal("com.jeroenwijering.players", "/");
	};


	/** Complete the build of the MCV cycle and start flow of events. **/
	public function startMCV(mar:Array) {
		if(mar != undefined) { registeredModels = mar; }
		itemsPlayed = 0;
		if(config["shuffle"] == "true") {
			randomizer = new Randomizer(feeder.feed);
			currentItem = randomizer.pick();
		} else {
			currentItem = 0;
		}
		sendChange("item",currentItem);
		if(config["autostart"] == "muted") {
			sendChange("volume",0);
		} else {
			sendChange("volume",Number(config["volume"]));
		}
		if(config["usecaptions"] == "false") { 
			config["clip"].captions._visible = false;
			config["clip"].controlbar.cc.icn._alpha = 40;
		}
		if(config["useaudio"] == "false") {
			config["clip"].audio.setStop();
			config["clip"].controlbar.au.icn._alpha = 40;
		}
		if(config["autostart"] == "false") {
			sendChange("pause",feeder.feed[currentItem]['start']);
			isPlaying = false;
		} else {
			sendChange("start",feeder.feed[currentItem]['start']);
			isPlaying = true;
		}
	};


	/** PlayPause switch **/
	private function setPlaypause() {
		if(isPlaying == true) {
			isPlaying = false;
			sendChange("pause");
		} else { 
			isPlaying = true;
			sendChange("start");
		}
	};


	/** Play previous item. **/
	private function setPrev() {
		if(currentItem == 0) { 
			setPlayitem(feeder.feed.length - 1); 
		} else { 
			setPlayitem(currentItem-1);
		}
	};


	/** Play next item. **/
	private function setNext() {
		if(currentItem == feeder.feed.length - 1) { 
			setPlayitem(0); 
		} else { 
			setPlayitem(currentItem+1);
		}
	};


	/** Stop and clear item. **/
	private function setStop() { 
		sendChange("pause",0);
		sendChange("stop");
		sendChange("item",currentItem);
		isPlaying = false;
	};


	/** Forward scrub number to model. **/
	private function setScrub(prm) {
		if(isPlaying == true) {
			sendChange("start",prm);
		} else {
			sendChange("pause",prm);
		}
	};


	/** Play a new item. **/
	private function setPlayitem(itm:Number) {
		if(itm != currentItem) {
			itm > feeder.feed.length-1 ? itm = feeder.feed.length-1: null;
			if(feeder.feed[currentItem]['file'] != feeder.feed[itm]['file']) {
				sendChange("stop");
			}
			currentItem = itm;
			sendChange("item",itm);

		}
		sendChange("start",feeder.feed[itm]["start"]);
		currentURL = feeder.feed[itm]['file'];
		isPlaying = true;
	};


	/** Get url from an item if link exists, else playpause. **/
	private function setGetlink(idx:Number) {
		if(feeder.feed[idx]["link"] == undefined) {
			setPlaypause();
		} else {
			getURL(feeder.feed[idx]["link"],config["linktarget"]);
		}
	};


	/** Determine what to do if an item is completed. **/
	private function setComplete() {
		itemsPlayed++;
		if(feeder.feed[currentItem]['type'] == "rtmp" || 
			config["streamscript"] != undefined) {
			sendChange("stop");
		}
		if(config["repeat"] == "false" || (config["repeat"] == "list"
			&& itemsPlayed >= feeder.feed.length)) {
			sendChange("pause",0);
			isPlaying = false;
			itemsPlayed = 0;
		} else {
			var itm;
			if(config["shuffle"] == "true") {
				itm = randomizer.pick();
			} else if(currentItem == feeder.feed.length - 1) {
				itm = 0;
			} else { 
				itm = currentItem+1;
			}
			setPlayitem(itm);
		}
	};


	/** Fullscreen switch function. **/
	private function setFullscreen() {
		if(Stage["displayState"] == "normal" && 
			config["usefullscreen"] == "true") {
			if(sizes[0] > 400) {
				Stage["fullScreenSourceRect"] = new Rectangle(0,0,sizes[0],sizes[1]);
			}
			Stage["displayState"] = "fullScreen";
		} else if (Stage["displayState"] == "fullScreen" && 
			config["usefullscreen"] == "true") {
			Stage["displayState"] = "normal";
		} else if (config["fsbuttonlink"] != undefined) {
			sendChange("stop");
			getURL(config["fsbuttonlink"],config["linktarget"]);
		}
	};


	/** Captions toggle **/
	private function setCaptions() {
		if(config["usecaptions"] == "true") {
			config["usecaptions"] = "false";
			config["clip"].captions._visible = false;
			config["clip"].controlbar.cc.icn._alpha = 40;
		} else {
			config["usecaptions"] = "true";
			config["clip"].captions._visible = true;
			config["clip"].controlbar.cc.icn._alpha = 100;
		}
		playerSO.data.usecaptions = config["usecaptions"];
		playerSO.flush();
	};


	/** Audiotrack toggle **/
	private function setAudio() {
		if(config["useaudio"] == "true") {
			config["useaudio"] = "false";
			config["clip"].audio.setStop();
			config["clip"].controlbar.au.icn._alpha = 40;
		} else {
			config["useaudio"] = "true";
			config["clip"].audio.setStart();
			config["clip"].controlbar.au.icn._alpha = 100;
		}
		playerSO.data.useaudio = config["useaudio"];
		playerSO.flush();
	};


	/** Check volume percentage and forward to models. **/
	private function setVolume(prm) {
		if (prm < 0 ) { prm = 0; } else if (prm > 100) { prm = 100; }
		if(prm == 0) {
			if(muted == true) {
				muted = false;
				sendChange("volume",config['volume']);
			} else {
				muted = true;
				sendChange("volume",0);
			}
		} else {
			sendChange("volume",prm);
			config['volume'] = prm;
			muted = false;
		}
		playerSO.data.volume = config["volume"];
		playerSO.flush();
	};


}