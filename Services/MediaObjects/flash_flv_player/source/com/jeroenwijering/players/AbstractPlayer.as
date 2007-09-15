/**
* Abstract player class, extended by all other players.
* Class loads config and file objects and sets up MCV triangle.
*
* @author	Jeroen Wijering
* @version	1.9
**/


import com.jeroenwijering.players.*;
import com.jeroenwijering.feeds.*;


class com.jeroenwijering.players.AbstractPlayer implements FeedListener {


	/** Object with all config values **/
	private var config:Object;
	/** Object with all playlist items **/
	public var feeder:FeedManager;
	/** reference to the controller **/
	public var controller:AbstractController;


	/** Player application startup. **/
	public function AbstractPlayer(tgt:MovieClip) {
		config["clip"] = tgt;
		config["clip"]._visible = false;
		Stage.width  > 0 ? config["width"]  = Stage.width:  null;
		Stage.height > 0 ? config["height"] = Stage.height: null;
		loadConfig();
	};


	/** Set config variables or load them from flashvars. **/
	private function loadConfig() {
		for(var cfv in config) {
			if(_root[cfv] != undefined) {
				config[cfv] = unescape(_root[cfv]);
			}
		}
		loadFile();
	};


	/** Load the file or playlist **/
	private function loadFile(str:String) {
		feeder = new FeedManager(true,config["enablejs"],_root.prefix,str);
		feeder.addListener(this);
		feeder.loadFile({file:config["file"]});
	};


	/** Invoked by the feedmanager **/
	public function onFeedUpdate() {
		if(controller == undefined) {
			config["clip"]._visible = true;
			_root.activity._visible = false;
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