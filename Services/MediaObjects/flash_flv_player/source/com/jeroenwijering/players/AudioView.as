/**
* Extra audiotrack management of the players MCV pattern.
*
* @author	Jeroen Wijering
* @version	1.1
**/


import com.jeroenwijering.players.*;

class com.jeroenwijering.players.AudioView extends AbstractView { 


	/** The MovieClip to which the sounds will be attached **/
	private var audioClip:MovieClip;
	/** The Sound object we'll use**/
	private var audioObject:Sound;
	/** Currently active feeditem **/
	private var currentItem:Number;
	/** The current elapsed time **/
	private var currentTime:Number = 0;
	/** The last stop position **/
	private var stopTime:Number;
	/** The current audio time **/
	private var audioTime:Number;
	/** Save the current state **/
	private var currentState:Number;
	/** Check whether an MP3 file is loaded **/
	private var isLoaded:String;
	/** Sync the audio with emtry or not **/
	private var sync:Boolean;


	/** Constructor, loads caption file. **/
	function AudioView(ctr:AbstractController,cfg:Object,fed:Object,
		snc:Boolean) {
		super(ctr,cfg,fed);
		sync = snc;
		var ref = this;
		audioClip = config['clip'].createEmptyMovieClip('audio',
			config['clip'].getNextHighestDepth());
		audioClip.setStart = function() {
			if(ref.stopTime == undefined && ref.sync == false) {
				ref.audioObject.loadSound(ref.feeder.feed[0]['audio'],true);
				ref.audioObject.setVolume(Number(ref.config['volume']));
				ref.audioObject.start(0);
			} else if (ref.sync == false) {
				ref.audioObject.start(ref.stopTime);
			} else if(ref.currentState == 2) {
				ref.audioObject.start(ref.currentTime);
			}
		};
		audioClip.setStop = function() { 
			ref.audioObject.stop();
			ref.stopTime = ref.audioObject.position/1000;
		};
		audioObject = new Sound (audioClip);
		if(config['useaudio'] == "true" && sync == false) { 
			audioClip.setStart();
		}
		if(sync == false) {
			audioObject.onSoundComplete = function() {
				this.start();
			};
		}
	};


	private function setItem(idx:Number) { 
		currentItem = idx;
	};


	private function setState(stt:Number) {
		currentState = stt;
		if(sync == false) { return; }
		if(stt == 2 && config['useaudio'] == "true") {
			audioObject.start(currentTime);
		} else {
			audioObject.stop();
		}
	};


	private function setTime(elp:Number,rem:Number) {
		if(sync == false) { return; }
		if(Math.abs(elp-currentTime) > 1) {
			currentTime = elp;
			audioTime = audioObject.position/1000;
			if(Math.abs(currentTime - audioTime) > 1 &&
				config['useaudio'] == "true") {
				audioObject.start(currentTime);
			}
		}
		if (isLoaded != feeder.feed[currentItem]['audio']) {
			isLoaded = feeder.feed[currentItem]['audio'];
			audioObject.loadSound(isLoaded,true);
			audioObject.setVolume(Number(config['volume']));
		}
	};


}