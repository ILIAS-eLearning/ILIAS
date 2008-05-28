/**
* MP3 model class of the players MCV pattern.
*
* @author	Jeroen Wijering
* @version	1.4
**/


import com.jeroenwijering.players.*;


class com.jeroenwijering.players.MP3Model extends AbstractModel { 


	/** array with extensions used by this model **/
	private var mediatypes:Array = new Array("mp3","rbs");
	/** Sound instance **/
	private var soundObject:Sound;
	/** MovieClip to apply the sound object to **/
	private var soundClip:MovieClip;
	/** interval ID of the buffer update function **/
	private var loadedInterval:Number;
	/** currently loaded percentage **/
	private var currentLoaded:Number = 0;
	/** interval ID of the position update function **/
	private var positionInterval:Number;
	/** current state of the sound that is playing **/
	private var currentState:Number;
	/** Current volume **/
	private var currentVolume:Number;


	/** Constructor **/
	function MP3Model(vws:Array,ctr:AbstractController,
		cfg:Object,fed:Object,scl:MovieClip) {
		super(vws,ctr,cfg,fed);
		soundClip = scl;
	};


	/** Start a specific sound **/
	private function setStart(pos:Number) {
		if(pos < 1 ) { 
			pos = 0; 
		} else if (pos > feeder.feed[currentItem]["duration"] - 1) { 
			pos = feeder.feed[currentItem]["duration"] - 1;
		}
		clearInterval(positionInterval);
		if(feeder.feed[currentItem]["file"] != currentURL) {
			var ref = this;
			currentURL = feeder.feed[currentItem]["file"];
			soundObject = new Sound(soundClip);
			soundObject.onSoundComplete = function() {
				ref.currentState = 3;
				ref.sendUpdate("state",3);
				ref.sendCompleteEvent();
			};
			soundObject.onLoad = function(scs:Boolean) {
				if(scs == false) {
					ref.currentState = 3;
					ref.sendUpdate("state",3);
					ref.sendCompleteEvent();
				}
			};
			soundObject.loadSound(currentURL,true);
			soundObject.setVolume(currentVolume);
			sendUpdate("load",0);
			loadedInterval = setInterval(this,"updateLoaded",100);
		}
		if(pos != undefined) { 
			currentPosition = pos;
			if(pos == 0) { sendUpdate("time",0,feeder.feed[currentItem]["duration"]); }
		}
		soundObject.start(currentPosition);
		updatePosition();
		sendUpdate("size",0,0);
		positionInterval = setInterval(this,"updatePosition",100);
	};


	/** Read and broadcast the amount of the mp3 that's currently loaded **/
	private function updateLoaded() {
		var pct:Number = Math.round(soundObject.getBytesLoaded() / 
			soundObject.getBytesTotal()*100);
		if(isNaN(pct)) { 
			currentLoaded = 0; 
			sendUpdate("load",0);
		} else if (pct != currentLoaded) {
			sendUpdate("load",pct); 
			currentLoaded = pct;
		} else if(pct >= 100) { 
			clearInterval(loadedInterval);
			currentLoaded = 100;
			sendUpdate("load",100);
		}
	};


	/** Read and broadcast the current position of the song **/
	private function updatePosition() {
		var pos = soundObject.position/1000;
		feeder.feed[currentItem]["duration"] = soundObject.duration/(10*currentLoaded);
		if(pos == currentPosition && currentState != 1) {
			currentState = 1;
			sendUpdate("state",1);
		} else if (pos != currentPosition && currentState != 2) { 	
			currentState = 2;
			sendUpdate("state",2);
		}
		if (pos != currentPosition) {
			currentPosition = pos;
			sendUpdate("time",currentPosition,feeder.feed[currentItem]["duration"]-currentPosition);
		}
	};


	/** Pause the sound that's currently playing. **/
	private function setPause(pos:Number) {
		if(pos < 1) { 
			pos = 0; 
		} else if (pos > feeder.feed[currentItem]["duration"] - 1) { 
			pos = feeder.feed[currentItem]["duration"] - 1; 
		}
		soundObject.stop();
		clearInterval(positionInterval);
		currentState = 0;
		sendUpdate("state",0);
		if(pos != undefined) {
			currentPosition = pos;
			sendUpdate("time",currentPosition,feeder.feed[currentItem]["duration"]-currentPosition);
		}
	};


	/** stop and unload the sound **/
	private function setStop() {
		soundObject.stop();
		clearInterval(positionInterval);
		clearInterval(loadedInterval);
		delete currentURL;
		delete soundObject;
		currentLoaded = 0;
	};


	/** Set volume of the sound object. **/
	private function setVolume(vol:Number) {
		super.setVolume(vol);
		currentVolume = vol;
		soundObject.setVolume(vol);
	};


}