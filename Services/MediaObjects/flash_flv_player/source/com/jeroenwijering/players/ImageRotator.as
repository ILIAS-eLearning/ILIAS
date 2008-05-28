/**
* Manages startup and overall control of the Flash Image Rotator
*
* @author	Jeroen Wijering
* @version	1.7
**/


import com.jeroenwijering.players.*;


class com.jeroenwijering.players.ImageRotator extends AbstractPlayer { 


	/** Array with all config values **/
	public var config:Object = {
		clip:undefined,
		height:200,
		width:400,
		
		file:undefined,
		image:undefined,
		link:undefined,
		id:undefined,
		type:undefined,
		captions:undefined,
		audio:undefined,

		backcolor:0x000000,
		frontcolor:0xffffff,
		lightcolor:0xffffff,
		screencolor:0x000000,

		kenburns:"false",
		logo:undefined,
		overstretch:"false",
		showicons:"true",
		shownavigation:"true",
		transition:"random",

		autostart:"true",
		repeat:"true",
		rotatetime:5,
		shuffle:"true",
		volume:80,

		enablejs:"false",
		javascriptid:undefined,
		linkfromdisplay:"false",
		linktarget:"_self",
		useaudio:"true",

		abouttxt:"JW Image Rotator 3.16",
		aboutlnk:"http://www.jeroenwijering.com/?about=JW_Image_Rotator"
	};


	/** Constructor **/
	function ImageRotator(tgt:MovieClip) { 
		super(tgt);
	};


	/** Setup all necessary MCV blocks. **/
	private function setupMCV():Void {
		controller = new RotatorController(config,feeder);
		var rov = new RotatorView(controller,config,feeder);
		var ipv = new InputView(controller,config,feeder);
		var vws:Array = new Array(rov,ipv);
		if(config["enablejs"] == "true") {
			var jsv = new JavascriptView(controller,config,feeder);
			vws.push(jsv);
		}
		if(feeder.audio == true) {
			var bav = new AudioView(controller,config,feeder,false);
			vws.push(bav);
		}
		config["displayheight"] = config["height"];
		var im1=new ImageModel(vws,controller,config,feeder,
			config["clip"].img1,true);
		var im2=new ImageModel(vws,controller,config,feeder,
			config["clip"].img2,true);
		var mds:Array = new Array(im1,im2);
		controller.startMCV(mds);
	};


	/** Application startup, used for MTASC compilation **/
	public static function main() {
		var irt = new ImageRotator(_root.rotator);
	}


}