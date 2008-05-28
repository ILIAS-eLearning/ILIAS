/**
* Youtube model class of the players MCV pattern.
* Integrates Youtube playback component.
*
* @author	Jeroen Wijering
* @version	1.0
**/


import com.jeroenwijering.players.*;


class com.jeroenwijering.players.YoutubeModel extends AbstractModel {


	/** Array with extensions used by this model. **/
	private var mediatypes:Array = new Array("youtube");
	/** Clip the YouTube blob is loaded into **/
	private var ytplayer:MovieClip;
	/** Location of the YouTube blob. **/
	private var url:String = "http://gdata.youtube.com/apiplayer";
	/** Developer key for the YouTube player **/
	private var key:String = "AI39si4CHS3-oQa0cHIhANstFbCLE71-qK6CB3mNe0lEx2h1mwXsOz6n1fkPo0yTKpZgYH4jsLgSX1Qg4jXNrYhJYKfMQiPlzw";
	/** Reference to the loader **/
	private var loader:MovieClipLoader;
	/** ID of the current clip to play **/
	private var currentURL:String;
	/** interval ID of the buffer update function **/
	private var loadedInterval:Number;
	/** current percentage of the video that's loaded **/
	private var currentLoaded:Number = 0;
	/** interval ID of the position update function **/
	private var positionInterval:Number;
	/** current position of the video that is playing **/
	private var currentPosition:Number;
	/** Current volume **/
	private var currentVolume:Number;
	/** static referer to the model **/
	private static var instance;
	/** pause-seeking **/
	private var pauseseek:Boolean;
	/** has the update already been sent? **/
	private var updateSent:Boolean;


	/** Constructor **/
	function YoutubeModel(vws:Array,ctr:AbstractController,cfg:Object,fed:Object,fcl:MovieClip) {
		super(vws,ctr,cfg,fed);
		ytplayer = fcl;
		System.security.allowDomain("gdata.youtube.com");
		System.security.allowInsecureDomain("gdata.youtube.com");
		YoutubeModel.instance = this;
	};

	/** wait for the player to load with a loop, then remove the loop. **/
	public function onLoadInit() {
		var ref = this;
		ytplayer.onEnterFrame = function() {
			if (this.isPlayerLoaded()) {
				ref.pauseseek = false;
				this.addEventListener("onStateChange",ref.onPlayerStateChange);
				this.addEventListener("onError",ref.onError);
				ref.onPlayerStateChange(this.getPlayerState());
				ref.setStart(ref.feeder.feed[ref.currentItem]['duration']);
				delete this.onEnterFrame;
				this.setSize(ref.config['displaywidth'],ref.config['displayheight']);
			}
		}
	};


	/** Start a specific video **/
	private function setStart(pos:Number) {
		clearInterval(positionInterval);
		clearInterval(loadedInterval);
		if(feeder.feed[currentItem]["file"] != currentURL) {
			if(!loader) {
				loader = new MovieClipLoader();
				loader.addListener(this);
				loader.loadClip(url+'?key='+key,ytplayer);
			} else {
				currentURL = feeder.feed[currentItem]["file"];
				ytplayer.loadVideoById(getID(currentURL),pos);
				ytplayer.setVolume(config['volume']);
				sendUpdate("load",0);
				sendUpdate("size",320,240);
			}
		} else if(!isNaN(pos)) {
			ytplayer.seekTo(pos,true);
		} else {
			ytplayer.playVideo();
		}
		positionInterval = setInterval(this,"updatePosition",100);
		ytplayer._visible = true;
		trace('true!!');
	};


	/** xtract the current ID from a youtube URL **/
	private function getID(url:String):String {
		var arr = url.split('?');
		for (var i in arr) {
			if(arr[i].substr(0,2) == 'v=') {
				return arr[i].substr(2);
			}
		}
		return '';
	};


	/** Listens for the player's onStateChange event **/
	public function onPlayerStateChange(stt:Number) {
		var ref = YoutubeModel.instance;
		if(ref.currentURL == undefined) { return; }
		switch(Number(stt)) {
			case 0:
				if(!ref.updateSent) { 
					ref.sendUpdate("state",3);
					ref.sendCompleteEvent();
					ref.sendUpdate("time",0,ref.feeder.feed[ref.currentItem]['duration']);
				}
				break;
			case 1:
				delete ref.updateSent;
				if(ref.pauseseek == true) {
					ref.pauseseek = false;
					ref.updatePosition();
					ref.ytplayer.pauseVideo();
				} else {
					ref.sendUpdate("load",100);
					ref.sendUpdate("state",2);
				}
				break;
			case 3:
				ref.sendUpdate("state",1);
				break;
			default:
				ref.sendUpdate("state",0);
				break;
		}
	};


	/** Error received, let's move to the next video **/
	public function onError() {
		var ref = YoutubeModel.instance;
		ref.sendUpdate("state",3);
		ref.sendCompleteEvent();
		ref.sendUpdate("time",0,0);
	};


	/** Read and broadcast the current position of the song **/
	private function updatePosition() {
		var pos = ytplayer.getCurrentTime();
		var dur = ytplayer.getDuration();
		if(isNaN(dur)) {
			pos = 0; 
			dur = feeder.feed[currentItem]['duration'];
		} else {
			feeder.feed[currentItem]['duration'] = dur;
		}
		sendUpdate("time",pos,dur-pos);
		currentPosition = pos;
	};


	/** Pause the video that's currently playing. **/
	private function setPause(pos:Number) {
		clearInterval(positionInterval);
		ytplayer.pauseVideo();
		if(pos > 0) {
			pauseseek = true;
			ytplayer.seekTo(pos,true);
			updatePosition();
		}
		sendUpdate("state",0);
	};


	/** Stop video and clear data. **/
	private function setStop(pos:Number) {
		updateSent = true;
		delete currentURL;
		ytplayer.stopVideo();
		ytplayer._visible = false;
		clearInterval(loadedInterval);
		clearInterval(positionInterval);
	};


	/** Set volume of the sound object. **/
	private function setVolume(vol:Number) {
		super.setVolume(vol);
		currentVolume = vol;
		ytplayer.setVolume(vol);
	};





}