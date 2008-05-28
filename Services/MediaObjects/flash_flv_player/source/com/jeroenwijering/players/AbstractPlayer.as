/**
* Abstract player class, extended by all other players.
* Class loads config and file objects and sets up MCV triangle.
*
* @author	Jeroen Wijering
* @version	1.9
**/


import com.jeroenwijering.players.*;
import com.jeroenwijering.feeds.*;
import com.jeroenwijering.utils.ConfigManager;


class com.jeroenwijering.players.AbstractPlayer implements FeedListener {


	/** Object with all config values **/
	public var config:Object;
	/** Object with all playlist items **/
	public var feeder:FeedManager;
	/** reference to the controller **/
	public var controller:AbstractController;
	/** reference to config management object **/
	private var manager:ConfigManager;


	/** Player application startup. **/
	public function AbstractPlayer(tgt:MovieClip) {
		var ref = this;
		config["clip"] = tgt;
		manager = new ConfigManager(true);
		manager.onComplete = function() { ref.fillConfig(); };
		manager.loadConfig(config);
	};


	/** Complete config with some default values **/
	private function fillConfig() {
		if(config['shownavigation'] == 'false') {
			config['controlbar'] = 0;
		}
		if (config["searchbar"] == 'true' && config['height'] > 50) {
			config["searchbar"] = 28;
		} else {
			config["searchbar"] = 0;
		}
		if (config["displayheight"] == undefined) {
			config["displayheight"] = config["height"] - config['controlbar'] - config["searchbar"];
		} else if(config["displayheight"] >= config["height"] - config["searchbar"]) {
			config["displayheight"] = config["height"] - config["searchbar"];
		} else {
			config["displayheight"] = Number(config["displayheight"]);
		}
		if (config["displaywidth"] == undefined) {
			config["displaywidth"] = config["width"];
		} else {
			config["displaywidth"] = Number(config["displaywidth"]);
		}
		config["bwstreams"] == undefined ? loadFile(): checkStream();
	};



	/** Placeholder function for bandwidth checking **/
	private function checkStream() {};


	/** Load the file or playlist **/
	private function loadFile(str:String) {
		feeder = new FeedManager(true,config["enablejs"],config['prefix'],str);
		feeder.addListener(this);
		feeder.loadFile(config);
	};


	/** Invoked by the feedmanager **/
	public function onFeedUpdate(typ:String) {
		if(controller == undefined) {
			config["clip"]._visible = true;
			config["clip"]._parent.activity._visible = false;
			setupMCV();
		}
	};


	/** Setup all necessary MCV blocks. **/
	private function setupMCV() {
		controller = new AbstractController(config,feeder);
		var asv = new AbstractView(controller,config,feeder);
		var vws:Array = new Array(asv);
		var asm = new AbstractModel(vws,controller,config,feeder);
		var mds:Array = new Array(asm);
		controller.startMCV(mds);
	};


}