/**
* Player that reads all media formats Flash can read.
*
* @author	Jeroen Wijering
* @version	1.10
**/


import com.jeroenwijering.players.*;
import com.jeroenwijering.utils.BandwidthCheck;


class com.jeroenwijering.players.MediaPlayer extends AbstractPlayer {


	/** Array with all config values **/
	private var config:Object = {
		clip:undefined,
		height:260,
		width:320,
		controlbar:20,
		displayheight:undefined,
		displaywidth:undefined,
		searchbar:'false',

		file:undefined,
		fallback:undefined,
		image:undefined,
		link:undefined,
		id:undefined,
		type:undefined,
		captions:undefined,
		audio:undefined,
		category:undefined,

		frontcolor:0x000000,
		backcolor:0xffffff,
		lightcolor:0x000000,
		screencolor:0x000000,

		autoscroll:"false",
		largecontrols:"false",
		logo:undefined,
		showdigits:'true',
		showdownload:'false',
		showeq:'false',
		showicons:'true',
		shownavigation:'true',
		showstop:'false',
		thumbsinplaylist:'true',
		usefullscreen:'true',
		fsbuttonlink:undefined,

		autostart:'false',
		bufferlength:3,
		overstretch:'false',
		repeat:'list',
		rotatetime:5,
		shuffle:'false',
		smoothing:'true',
		volume:80,

		bwfile:"100k.jpg",
		bwstreams:undefined,
		callback:undefined,
		enablejs:'false',
		javascriptid:'',
		linkfromdisplay:'false',
		linktarget:'_blank',
		midroll:undefined,
		prefix:'',
		recommendations:undefined,
		searchlink:'http://search.longtail.tv/?q=',
		streamscript:undefined,
		useaudio:'true',
		usecaptions:'true',
		usemute:'false',
		usekeys:'true',

		abouttxt:'JW Player 3.16',
		aboutlnk:'http://www.jeroenwijering.com/?about=JW_FLV_Media_Player'
	};


	/** Constructor **/
	public function MediaPlayer(tgt:MovieClip) {
		super(tgt);
	};


	/** check bandwidth for streaming **/
	private function checkStream() {
		var ref = this;
		var str = config["bwstreams"].split(",");
		var bwc = new BandwidthCheck(config["bwfile"]);
		bwc.onComplete = function(kbps) {
			trace("bandwidth: "+kbps);
			var bwc = new ContextMenuItem("Detected bandwidth: "+kbps+" kbps");
			bwc.separatorBefore = true;
			ref.manager.context.customItems.push(bwc);
			if(ref.config['enablejs'] == "true" && 
				flash.external.ExternalInterface.available) {
				flash.external.ExternalInterface.call("getBandwidth",kbps);
			}
			for (var i=1; i<str.length; i++) {
				if (kbps < Number(str[i])) {
					ref.loadFile(str[i-1]);
					return;
				}
			}
			ref.loadFile(str[str.length-1]);
		};
	};


	/** Setup all necessary MCV blocks. **/
	private function setupMCV() {
		// set controller
		controller = new PlayerController(config,feeder);
		// set default views
		var dpv = new DisplayView(controller,config,feeder);
		var vws = new Array(dpv);
		if(config['shownavigation'] == "true") {
			var cbv = new ControlbarView(controller,config,feeder);
			vws.push(cbv);
		} else {
			config['clip'].controlbar._visible = false;
		}
		// set optional views
		if(config["displayheight"] < config["height"]-config['controlbar']-config['searchbar'] ||
			config["displaywidth"] < config["width"]) {
			var plv = new PlaylistView(controller,config,feeder);
			vws.push(plv);
		} else {
			config["clip"].playlist._visible = 
				config["clip"].playlistmask._visible  = false;
		}
		if(config["usekeys"] == "true") {
			var ipv = new InputView(controller,config,feeder);
			vws.push(ipv);
		}
		if(config["showeq"] == "true") {
			var eqv = new EqualizerView(controller,config,feeder);
			vws.push(eqv);
		} else {
			config["clip"].equalizer._visible = false;
		}
		var cpv = new CaptionsView(controller,config,feeder);
		vws.push(cpv);
		if(config['recommendations'] != undefined) {
			var rlv = new RecommendationsView(controller,config,feeder);
			vws.push(rlv);
		} else {
			config["clip"].recommendations._visible = false;
		}
		if(config['searchbar'] > 0) {
			var sev = new SearchView(controller,config,feeder);
			vws.push(sev);
		} else {
			config["clip"].search._visible = false;
		}
		if(config['midroll'] != undefined) {
			var mrv = new MidrollView(controller,config,feeder);
			vws.push(mrv);
		} else {
			config["clip"].midroll._visible = false;
		}
		if(feeder.audio == true) {
			var adv = new AudioView(controller,config,feeder,true);
			vws.push(adv);
		}
		if(config["enablejs"] == "true") {
			var jsv = new JavascriptView(controller,config,feeder);
			vws.push(jsv);
		}
		if(config["callback"] != undefined) {
			var cav = new CallbackView(controller,config,feeder);
			vws.push(cav);
		}
		// set models
		var mp3 = new MP3Model(vws,controller,config,feeder,config["clip"]);
		var flv = new FLVModel(vws,controller,config,feeder,config["clip"].display.video);
		var img = new ImageModel(vws,controller,config,feeder,config["clip"].display.image);
		var ytm = new YoutubeModel(vws,controller,config,feeder,config["clip"].display.youtube);
		var mds:Array = new Array(mp3,flv,img,ytm);
		if(feeder.captions == true) { flv.capView = cpv; }
		// start mcv cycle
		controller.startMCV(mds);
	};


	/** Application startup, used for MTASC compilation **/
	public static function main() {
		var mpl = new MediaPlayer(_root.player);
	}


}