/**
* Rotator extension of the controller.
*
* @author	Jeroen Wijering
* @version	1.6
**/


import com.jeroenwijering.players.AbstractController;
import com.jeroenwijering.utils.Randomizer;


class com.jeroenwijering.players.RotatorController extends AbstractController{


	/** Which one of the models to send the changes to **/
	private var currentModel:Number;
	/** use SharedObject to save current file, item and volume **/
	private var playerSO:SharedObject;


	/** Constructor, inherited from super **/
	function RotatorController(car:Object,ply:Object) { 
		super(car,ply);
		playerSO = SharedObject.getLocal("com.jeroenwijerin.players", "/");
		if(playerSO.data.volume != undefined && _root.volume == undefined) {
			config["volume"] = playerSO.data.volume;
		}
		if(playerSO.data.useaudio != undefined && 
			_root.useaudio == undefined) {
			config["useaudio"] = playerSO.data.useaudio;
		}
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
		if(config["autostart"] == "false") {
			sendChange("start",0);
			sendChange("pause",0);
			isPlaying = false;
		} else { 
			sendChange("start",0);
			isPlaying = true;
		}
	};


	/** PlayPause switch **/
	private  function setPlaypause() {
		if(isPlaying == true) {
			isPlaying = false;
			sendChange("pause");
		} else { 
			isPlaying = true;
			sendChange("start");
		}
	};


	/** Play previous item. **/
	private  function setPrev() {
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
		isPlaying == true ? sendChange("start",prm): sendChange("pause",prm);
	};


	/** Play a new item. **/
	private function setPlayitem(itm:Number) {
		if(itm != currentItem) {
			sendChange("stop");
			itm > feeder.feed.length-1 ? itm = feeder.feed.length-1: null;
			currentItem = itm;
			sendChange("item",itm);
		}
		if(feeder.feed[itm]["start"] == undefined) {
			sendChange("start",0);
		} else {
			sendChange("start",feeder.feed[itm]["start"]);
		}
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
		if(config["repeat"]=="false" || (config["repeat"] == "list"
		 	&& itemsPlayed >= feeder.feed.length)) {
			sendChange("pause",0);
			isPlaying = false;
			itemsPlayed = 0;
		} else {
			if(config["shuffle"] == "true") {
				setPlayitem(randomizer.pick());
			} else if(currentItem == feeder.feed.length - 1) {
				setPlayitem(0);
			} else { 
				setPlayitem(currentItem+1);
			}
		}
	};


	/** Audiotrack toggle **/
	private function setAudio() {
		if(config["useaudio"] == "true") {
			config["useaudio"] = "false";
			config["clip"].audio.setStop();
			config["clip"].navigation.audioBtn.icnOff._visible = true;
			config["clip"].navigation.audioBtn.icnOn._visible = false;
		} else {
			config["useaudio"] = "true";
			config["clip"].audio.setStart();
			config["clip"].navigation.audioBtn.icnOff._visible = false;
			config["clip"].navigation.audioBtn.icnOn._visible = true;
		}
		playerSO.data.useaudio = config["useaudio"];
		playerSO.flush();
	};


	/** Switch active model and send changes. **/
	private function sendChange(typ:String,prm:Number):Void {
		if(typ == "item") { 
			currentModel == 0 ? currentModel = 1: currentModel = 0;
		}
		registeredModels[currentModel].getChange(typ,prm);
	};


}