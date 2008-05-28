/**
* Check user bandwidth/connection speed over HTTP or RTMP.
* 
* @example 
* var bwc = new BandwidthCheck("http://www.server.com/upload/100k.jpg");
* bwc.onComplete = function(kbps) { trace(kbps); };
*
* @author	Brian Weil
* @author	Stefan Richter
* @author	Jeroen Wijering
* @version	1.0
**/


class com.jeroenwijering.utils.BandwidthCheck {


	/** MovieClipLoader instance **/
	private var loader:MovieClipLoader;
	/** NetConnection instance **/
	private var connector:NetConnection;
	/** MovieClip  instance **/
	private var clip:MovieClip;
	/** Start time of test **/
	private var startTime:Number;


	/** Constructor for the BandwidthCheck **/
	function BandwidthCheck(fil:String) {
		var ref = this;
		if (fil.indexOf("rtmp") == -1) {
			loader = new MovieClipLoader;
			loader.addListener(this);
			clip = _root.createEmptyMovieClip("_bwchecker",1);
			loader.loadClip(fil + "?" + random(9999),clip);
		} else {
			connector = new NetConnection();
			connector.onStatus = function(info) {
				if(info.code != "NetConnection.Connect.Success") {
					ref.onComplete(0);
				}
			};
			connector.connect(fil, true);
			connector.onBWDone = function(kbps,dtd,dtt,lat) {
				ref.onComplete(kbps);
			};
			connector.onBWCheck = function() {};
		}
	};


	/** event handler for finished loading **/
	private function onLoadComplete(tgt:MovieClip,hts:Number) {
		tgt._visible = false;
		var dat = new Date();
		var ttl = clip.getBytesTotal();
		var sec = (dat.getTime() - startTime)/1000;
		var klb = ttl * 0.0078125*0.93;
		var kbps = Math.floor(klb/sec);
		onComplete(kbps);
		clip.removeMovieClip();
	};


	/** event handler for loading error **/
	private function onLoadError(tgt:MovieClip,err:String,stt:Number) {
		onComplete(0);
	};


	/** event handler for loading start **/
	private function onLoadStart() {
		var d = new Date();
		startTime = d.getTime();
	};


	/** event handler for completed test **/
	public function onComplete() {};


}