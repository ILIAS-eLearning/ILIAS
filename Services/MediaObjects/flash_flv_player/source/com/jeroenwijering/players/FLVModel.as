/**
* FLV model class of the players MCV pattern.
* Handles playback of FLV files, HTTP streams and RTMP streams.
*
* @author	Jeroen Wijering
* @version	1.13
**/


import com.jeroenwijering.players.*;


class com.jeroenwijering.players.FLVModel extends AbstractModel {


	/** array with extensions used by this model **/
	private var mediatypes:Array = new Array("flv","rtmp","mp4","m4v","3gp");
	/** NetConnection object reference **/
	private var connectObject:NetConnection;
	/** NetStream object reference **/
	private var streamObject:NetStream;
	/** Sound object reference **/
	private var soundObject:Sound;
	/** interval ID of the buffer update function **/
	private var loadedInterval:Number;
	/** current percentage of the video that's loaded **/
	private var currentLoaded:Number = 0;
	/** interval ID of the position update function **/
	private var positionInterval:Number;
	/** Duration metadata of the current video **/
	private var metaDuration:Number = 0
	/** current state of the video that is playing **/
	private var currentState:Number;
	/** Current volume **/
	private var currentVolume:Number;
	/** MovieClip with "display" video Object  **/
	private var videoClip:MovieClip;
	/** object with keyframe times and positions, saved for PHP streaming **/
	private var metaKeyframes:Object = new Object();
	/** Boolean to check whether a stop event is fired **/
	private var stopFired:Boolean = false;
	/** Switch for FLV type currently played **/
	private var flvType:String;
	/** reference to the captions object for parsing captionate data **/
	public var capView:Object;


	/** Constructor **/
	function FLVModel(vws:Array,ctr:AbstractController,cfg:Object,
		fed:Object,fcl:MovieClip) {
		super(vws,ctr,cfg,fed);
		connectObject = new NetConnection();
		videoClip = fcl;
		if(config["smoothing"] == "false") { 
			videoClip.display.smoothing = false;
			videoClip.display.deblocking = 1;
		} else {
			videoClip.display.smoothing = true;
			videoClip.display.deblocking = 2;
		}
		videoClip.createEmptyMovieClip("snd",videoClip.getNextHighestDepth());
		soundObject = new Sound(videoClip.snd);
	};


	/** Check which FLV type we use **/
	private function setItem(idx:Number) {
		super.setItem(idx);
		if(isActive == true) {
			if(config["streamscript"] != undefined) { 
				flvType = "HTTP";
			} else if(feeder.feed[currentItem]["type"] == "rtmp") {
				flvType = "RTMP"; 
			} else { 
				flvType = "FLV"; 
			}
		}
	};


	/** Start a specific video **/
	private function setStart(pos:Number) {
		if (pos != undefined) { currentPosition = pos; }
		if(pos < 1) { 
			pos = 0; 
		} else if (pos > metaDuration - 1) { 
			pos = metaDuration - 1; 
		}
		if (flvType=="RTMP" && feeder.feed[currentItem]["id"] != currentURL) {
			connectObject.connect(feeder.feed[currentItem]["file"]);
			currentURL = feeder.feed[currentItem]["id"];
			setStreamObject(connectObject);
			streamObject.play(currentURL);
		} else if(flvType != "RTMP" && 
			feeder.feed[currentItem]["file"] != currentURL) {
			connectObject.connect(null);
			currentURL = feeder.feed[currentItem]["file"];
			if(flvType == "HTTP" ) {
				setStreamObject(connectObject);
				if(config["streamscript"] == "lighttpd") {
					streamObject.play(currentURL);
				} else {
					streamObject.play(config["streamscript"] + 
					"?file=" + currentURL);
				}
			} else {
				setStreamObject(connectObject);
				streamObject.play(currentURL);
			}
		} else {
			if(pos != undefined) { streamObject.seek(pos); }
			streamObject.pause(false);
		}
		videoClip._visible = true;
		videoClip._parent.thumb._visible = false;
		if(flvType == "HTTP" && pos > 0) {
			playKeyframe(currentPosition);
		} else if (flvType == "FLV" && pos > 0) {
			streamObject.seek(currentPosition);
		} else if (flvType == "RTMP" && pos > 0) { 
			streamObject.seek(currentPosition);
		}
		clearInterval(positionInterval);
		positionInterval = setInterval(this,"updatePosition",200);
		clearInterval(loadedInterval);
		loadedInterval = setInterval(this,"updateLoaded",200);
	};


	/** Read and broadcast the amount of the flv that's currently loaded **/
	private function updateLoaded() {
		if(flvType == "FLV") {
			var pct:Number = Math.round(streamObject.bytesLoaded/
				streamObject.bytesTotal*100);
		} else {
			var pct:Number = Math.round(streamObject.bufferLength/
				streamObject.bufferTime*100);
		}
		if(isNaN(pct)) { 
			currentLoaded = 0;
			sendUpdate("load",0);
		} else if (pct > 95) {
			clearInterval(loadedInterval);
			currentLoaded = 100;
			sendUpdate("load",100);
		} else if (pct != currentLoaded) { 
			currentLoaded= pct;
			sendUpdate("load",currentLoaded);
		}
	};


	/** Read and broadcast the current position of the song **/
	private function updatePosition() {
		var pos = streamObject.time;
		if(pos == currentPosition && currentState != 1 && 
			streamObject.bufferLength < config["bufferlength"]-1) {
			currentState = 1;
			sendUpdate("state",1);
		} else if (pos != currentPosition && currentState != 2) { 
			currentState = 2;
			sendUpdate("state",2);
		}
		if (pos != currentPosition) {
			currentPosition = pos;
			if(metaDuration<currentPosition) {
				metaDuration = currentPosition;
			}
			sendUpdate("time",currentPosition,metaDuration-currentPosition);
		} else if (streamObject.bufferLength < config["bufferlength"]-1 
			&& stopFired == true) {
			currentState = 3;
			videoClip._visible = false;
			videoClip._parent.thumb._visible = true;
			sendUpdate("state",3);
			sendCompleteEvent();
			stopFired = false;
		}
	};


	/** Pause the video that's currently playing. **/
	private function setPause(pos:Number) {
		if(pos < 1) { pos = 0; }
		clearInterval(positionInterval);
		if(pos != undefined) {
			currentPosition = pos;
			sendUpdate("time",currentPosition,Math.abs(metaDuration-currentPosition));
			streamObject.seek(currentPosition);
		}
		streamObject.pause(true);
		currentState = 0;
		sendUpdate("state",0);
	};


	/** Stop video and clear data. **/
	private function setStop(pos:Number) {
		clearInterval(loadedInterval);
		clearInterval(positionInterval);
		videoClip._visible = false;
		delete currentURL;
		delete currentLoaded;
		delete currentPosition;
		delete metaKeyframes;
		metaDuration = 0;
		currentLoaded = 0;
		stopFired = false;
		streamObject.close();
		delete streamObject;
	};


	/** Set volume of the sound object. **/
	private function setVolume(vol:Number) {
		super.setVolume(vol);
		currentVolume = vol;
		soundObject.setVolume(vol);
	};


	/** Connect a new stream object to video/audio/callbacks **/
	private function setStreamObject(cnt:NetConnection) {
		var ref = this;
		currentLoaded = 0;
		sendUpdate("load",0);
		streamObject = new NetStream(cnt);
		streamObject.setBufferTime(config["bufferlength"]);
		streamObject.onMetaData = function(obj) {
			obj.duration>1 ? ref.metaDuration = obj.duration: null;
			if(obj.width > 10) {
				ref.sendUpdate("size",obj.width,obj.height);
			}
			ref.frameRate = obj.framerate;
			ref.metaKeyframes = obj.keyframes;
			if(ref.feeder.feed[ref.currentItem]['start'] > 0 &&
				ref.flvType == "HTTP") {
				ref.playKeyframe(ref.feeder.feed[ref.currentItem]['start']);
			}
			delete obj;
			delete this.onMetaData;
		};
		streamObject.onStatus = function(object) {
			if(object.code == "NetStream.Buffer.Flush" || 
				object.code == "NetStream.Play.Stop") {
				ref.stopFired = true;
			} else if (object.code == "NetStream.Play.StreamNotFound") {
				ref.currentState = 3;
				ref.videoClip._visible = false;
				ref.sendUpdate("state",3);
				ref.sendCompleteEvent();
				ref.stopFired = false;
			}
		};
		streamObject.onCaption = function(cap:Array) {
			ref.capView.onCaptionate(cap);
		};
		videoClip.display.attachVideo(streamObject);
		videoClip.snd.attachAudio(streamObject);
	};


	/** Play from keyframe position from metadata **/
	private function playKeyframe(pos:Number) {
		for (var i=0; i< metaKeyframes.times.length; i++) {
			if((metaKeyframes.times[i] <= pos) && 
				(metaKeyframes.times[i+1] >= pos)) {
				if(config["streamscript"] == "lighttpd") {
					streamObject.play(currentURL+"?start="+
						metaKeyframes.filepositions[i]);
				} else {
					streamObject.play(config["streamscript"]+"?file="+
						currentURL+"&pos="+metaKeyframes.filepositions[i]);
				}
				break;
			}
		}
	};


}