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
	private var mediatypes:Array = new Array(
		"flv","rtmp","mp4","m4v","m4a","mov","3gp","3g2");
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
	/** Boolean to check whether a flusk event is fired **/
	private var flushFired:Boolean = false;
	/** Switch for FLV type currently played **/
	private var flvType:String;
	/** check h264 for time offset **/
	private var isH264:Boolean;
	/** check h264 for time offset **/
	private var timeOffset:Number = 0;
	/** reference to the captions object for parsing captionate data **/
	public var capView:Object;
	/** buffer iterator (prevents buffericon showing on slow PC's) **/
	public var bufferCount:Number = 0;



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
			videoClip.display.deblocking = 3;
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
		stopFired = false;
		flushFired = false;
		if (pos != undefined) { currentPosition = pos; }
		if(pos < 1) {
			pos = 0; 
		} else if (pos > feeder.feed[currentItem]["duration"] - 1) { 
			pos = feeder.feed[currentItem]["duration"] - 1; 
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
			if(flvType == "HTTP" && pos != undefined) {
				playKeyframe(currentPosition);
			} else if (flvType != "HTTP" && pos != undefined) {
				streamObject.seek(currentPosition);
				streamObject.pause(false);
			} else if(flvType == "RTMP" && currentPosition > 0 && 
				feeder.feed[currentItem]["duration"] == 0) {
				connectObject.connect(feeder.feed[currentItem]["file"]);
				setStreamObject(connectObject);
				streamObject.play(currentURL);
			} else {
				streamObject.pause(false);
			}
		}
		videoClip._visible = true;
		videoClip._parent.thumb._visible = false;
		clearInterval(positionInterval);
		positionInterval = setInterval(this,"updatePosition",100);
		clearInterval(loadedInterval);
		loadedInterval = setInterval(this,"updateLoaded",100);
	};


	/** Read and broadcast the amount of the flv that's currently loaded **/
	private function updateLoaded() {
		var pct:Number = Math.round(streamObject.bufferLength/
			streamObject.bufferTime*100);
		if(flvType == "FLV") {
			pct = Math.round(streamObject.bytesLoaded/
				streamObject.bytesTotal*100);
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
		var pos = streamObject.time + timeOffset;
		if(pos == currentPosition && currentState != 1 && stopFired != true) {
			if(bufferCount == 5) { 
				currentState = 1;
				sendUpdate("state",1);
				bufferCount = 0;
			} else { 
				bufferCount++;
			}
		} else if (pos != currentPosition && currentState != 2) { 
			bufferCount = 0;
			currentState = 2;
			sendUpdate("state",2);
		} else {
			bufferCount = 0;
		}
		if (pos != currentPosition) {
			currentPosition = pos;
			sendUpdate("time",currentPosition,
				Math.max(feeder.feed[currentItem]["duration"]-currentPosition,0));
		} else if (stopFired == true || 
			(flushFired == true && flvType != "RTMP" && bufferCount == 5)) {
			currentState = 3;
			videoClip._visible = false;
			videoClip._parent.thumb._visible = true;
			sendUpdate("state",3);
			sendCompleteEvent();
			stopFired = false;
			flushFired = false;
		}
	};


	/** Pause the video that's currently playing. **/
	private function setPause(pos:Number) {
		if(pos < 1) { pos = 0; }
		clearInterval(positionInterval);
		if(pos != undefined) {
			currentPosition = pos;
			sendUpdate("time",currentPosition,
				Math.abs(feeder.feed[currentItem]["duration"]-currentPosition));
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
		currentLoaded = 0;
		stopFired = false;
		timeOffset = 0;
		streamObject.close();
		delete streamObject;
		videoClip.display.clear();
	};


	/** Set volume of the sound object. **/
	private function setVolume(vol:Number) {
		super.setVolume(vol);
		currentVolume = vol;
		soundObject.setVolume(vol);
	};


	/** Connect a new stream object to video/audio/callbacks **/
	private function setStreamObject(cnt:NetConnection) {
		_root.tf.text = 'metadata!';
		var ref = this;
		currentLoaded = 0;
		sendUpdate("load",0);
		streamObject = new NetStream(cnt);
		streamObject.setBufferTime(config["bufferlength"]);
		streamObject.onMetaData = function(obj) {
			for (var i in obj) {
				trace(i+': '+obj[i]);
			}
			if(obj.duration > 1) {
				ref.feeder.feed[ref.currentItem]["duration"] = obj.duration;
			}
			if(obj.width > 10) {
				ref.sendUpdate("size",obj.width,obj.height);
			}
			if(obj.seekpoints != undefined) {
				ref.isH264 = true;
				ref.metaKeyframes = new Object();
				ref.metaKeyframes.times = new Array();
				ref.metaKeyframes.filepositions = new Array();
				for (var j in obj.seekpoints) {
					ref.metaKeyframes.times.unshift(
						Number(obj.seekpoints[j]['time']));
					ref.metaKeyframes.filepositions.unshift(
						Number(obj.seekpoints[j]['time']));
				}
			} else {
				ref.metaKeyframes = obj.keyframes;
			}
			if(ref.feeder.feed[ref.currentItem]['start'] > 0) {
				if(ref.flvType == "HTTP") {
					ref.playKeyframe(ref.feeder.feed[ref.currentItem]['start']);
				} else if (ref.flvType == "RTMP") {
					ref.setStart(ref.feeder.feed[ref.currentItem]['start']);
				}
			}
			delete obj;
			delete this.onMetaData;
		};
		streamObject.onStatus = function(object) {
			trace("status: "+object.code);
			if(object.code == "NetStream.Play.Stop" && ref.flvType!='RTMP') {
				ref.stopFired = true;
			} else if (object.code == "NetStream.Play.StreamNotFound") {
				ref.currentState = 3;
				ref.videoClip._visible = false;
				ref.sendUpdate("state",3);
				ref.sendCompleteEvent();
				ref.stopFired = false;
				ref.flushFired = false;
			} else if (object.code == "NetStream.Buffer.Flush") {
				ref.flushFired = true;
			}
		};
		streamObject.onPlayStatus = function(object) {
			if( object.code == "NetStream.Play.Complete" ||
				object.code == "NetStream.Play.Stop") {
				ref.stopFired = true;
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
					if(isH264 == true) {
						timeOffset = metaKeyframes.filepositions[i];
					}
				} else {
					streamObject.play(config["streamscript"]+"?file="+
						currentURL+"&pos="+metaKeyframes.filepositions[i]);
				}
				break;
			}
		}
	};


}