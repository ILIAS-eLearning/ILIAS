/**
* User input management of the players MCV pattern.
*
* @author	Jeroen Wijering
* @version	1.9
**/


import com.jeroenwijering.players.AbstractController;


class com.jeroenwijering.players.PlayerController extends AbstractController {

	
	/** use SharedObject to save current file, item and volume **/
	private var playerSO:SharedObject;


	/** Constructor, save arrays and set currentItem. **/
	function PlayerController(cfg:Object,fed:Object) {
		super(cfg,fed);
		playerSO = SharedObject.getLocal("com.jeroenwijerin.players", "/");
		if(playerSO.data.volume != undefined && _root.volume == undefined) {
			config["volume"] = playerSO.data.volume;
		}
		if(playerSO.data.usecaptions != undefined && 
			_root.usecaptions == undefined) {
			config["usecaptions"] = playerSO.data.usecaptions;
		}
		if(playerSO.data.useaudio != undefined && 
			_root.useaudio == undefined) {
			config["useaudio"] = playerSO.data.useaudio;
		}
	};


	/** Complete the build of the MCV cycle and start flow of events. **/
	public function startMCV(mar:Array) {
		registeredModels = mar;
		if(feeder.feed[currentItem-1]['category']=='preroll') {
			currentItem--;
		}
		sendChange("item",currentItem);
		if(config["autostart"] == "muted") {
			sendChange("volume",0);
		} else {
			sendChange("volume",config["volume"]);
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
	private function setPlaypause(dpl:Number) {
		if((feeder.feed[currentItem]['category'] == 'preroll' ||
			feeder.feed[currentItem]['category'] == 'postroll') && dpl==1) {
			getURL(feeder.feed[currentItem]["link"],config["linktarget"]);
		} else if(isPlaying == true) {
			isPlaying = false;
			sendChange("pause");
		} else { 
			isPlaying = true;
			sendChange("start");
		}
	};


	/** Play previous item. **/
	private function setPrev() {
		if(currentItem == 0) { var i:Number = feeder.feed.length - 1; }
		else { var i:Number = currentItem-1; }
		setPlayitem(i);
	};


	/** Play next item. **/
	private function setNext() {
		if(currentItem == feeder.feed.length - 1) { 
			var i:Number = 0; 
		} else if(feeder.feed[currentItem]['category']=='preroll') {
			var i:Number = currentItem+2;
		} else { 
			var i:Number = currentItem+1;
		}
		setPlayitem(i);
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
		if(feeder.feed[currentItem]['category'] == 'preroll' || 
			feeder.feed[currentItem]['category'] == 'postroll') {
				return;
		} else if(isPlaying == true) {
			sendChange("start",prm);
		} else {
			sendChange("pause",prm);
		}
	};


	/** Play a new item. **/
	private function setPlayitem(itm:Number) {
		if(feeder.feed[itm-1]['category']=='preroll' && currentItem!=itm-1) {
			itm--;
		} else if (feeder.feed[itm]['category']=='postroll' && 
			currentItem==itm+1) {
			itm--;
		}
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
		if(feeder.feed[currentItem]['category'] == 'preroll' || 
			feeder.feed[currentItem+1]['category'] == 'postroll') {
			setPlayitem(currentItem+1);
		} else if(config["repeat"] == "false" || (config["repeat"] == "list"
			&& itemsPlayed == feeder.feed.length)) {
			sendChange("pause",0);
			isPlaying = false;
			itemsPlayed = 0;
			if(feeder.feed[currentItem]['category'] == 'postroll') {
				currentItem--;
				sendChange("stop");
				sendChange("item",currentItem);
			}
		} else {
			if(config["shuffle"] == "true") {
				var i:Number = randomizer.pick();
			} else if(currentItem == feeder.feed.length - 1) {
				var i:Number = 0;
			} else { 
				var i:Number = currentItem+1;
			}
			setPlayitem(i);
		}
	};


	/** Check volume percentage and forward to models. **/
	private function setVolume(prm) {
		if (prm < 0 ) { prm = 0; } else if (prm > 100) { prm = 100; }
		if(config["volume"] == 0 && prm == 0) { prm = 80; }
		config["volume"] = prm;
		sendChange("volume",prm);
		playerSO.data.volume = prm;
		playerSO.flush();
	};


	/** Fullscreen switch function. **/
	private function setFullscreen() {
		if(Stage["displayState"] == "normal" && 
			config["usefullscreen"] == "true") { 
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


}