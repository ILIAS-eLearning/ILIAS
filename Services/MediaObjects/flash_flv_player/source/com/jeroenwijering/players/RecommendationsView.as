/**
* Thumbnailbar with recommended videos.
*
* @author	Jeroen Wijering
* @version	1.0
**/


import com.jeroenwijering.utils.ImageLoader;
import com.jeroenwijering.utils.Animations;
import com.jeroenwijering.utils.XMLParser;
import com.jeroenwijering.players.AbstractController;
import com.jeroenwijering.players.AbstractView;


class com.jeroenwijering.players.RecommendationsView extends AbstractView { 


	/** reference to config Array **/
	private var config:Object;
	/** reference to feed Array **/
	private var feeder:Object;
	/** Reference to the movieclip **/
	private var clip:MovieClip;
	/** XML parser reference **/
	private var parser:XMLParser;
	/** Recommendations array **/
	private var recommendations:Array;
	/** Current show offset **/
	private var offset:Number = 0;
	/** Number of thumbs maximum on screen **/
	private var maximum:Number;



	/** Constructor **/
	function RecommendationsView(ctr:AbstractController,
		cfg:Object,fed:Object) {
		config = cfg;
		feeder = fed;
		clip =  config["clip"].recommendations;
		var ref = this;
		parser = new XMLParser();
		parser.onComplete = function() {
			ref.loadRecommendations(this.output);
		};
		Stage.addListener(this);
		setButtons();
	};


	/** Set the colors, clicks and dimensions of the buttons. **/
	private function setButtons() {
		var ref = this;
		maximum = Math.floor((config['displaywidth']-44)/70);
		clip._visible = false;
		clip.txt._x = 10;
		clip.txt._width = config['displaywidth'] -20;
		clip.txt.textColor = config['backcolor'];
		clip.prv._x = config['displaywidth']/2 - maximum*35;
		clip.nxt._x = config['displaywidth']/2 + maximum*35;
		clip.prv.col = new Color(clip.prv);
		clip.prv.col.setRGB(config['backcolor']);
		clip.prv.onRelease = function() {
			this.col.setRGB(ref.config['backcolor']);
			ref.showRecommendations(ref.offset - ref.maximum);
		};
		clip.prv._visible = false;
		clip.nxt.col = new Color(clip.nxt);
		clip.nxt.col.setRGB(config['backcolor']);
		clip.nxt.onRelease = function() {
			this.col.setRGB(ref.config['backcolor']);
			ref.showRecommendations(ref.offset + ref.maximum);
		};
		clip.nxt._visible = false;
		clip.itm._visible = false;
		for(var i=0; i<maximum; i++) {
			clip.itm.duplicateMovieClip('itm'+i,i);
			clip['itm'+i]._x = clip.prv._x+i*70 + 5;
			clip['itm'+i].ldr=new ImageLoader(clip['itm'+i].img,"true",60,45);
			clip['itm'+i].ldr.onLoadFinished = function() {
				Animations.fadeIn(this.targetClip._parent);
			};
			clip['itm'+i].img.setMask(clip['itm'+i].msk);
			clip['itm'+i].cl1 = new Color(clip['itm'+i].bdr);
			clip['itm'+i].cl1.setRGB(config['frontcolor']);
			clip['itm'+i].cl2 = new Color(clip['itm'+i].icn);
			clip['itm'+i].cl2.setRGB(config['backcolor']);
			clip['itm'+i].icn._visible = false;
			clip['itm'+i].onRollOver = function() {
				this.cl1.setRGB(ref.config['backcolor']);
				this.icn._visible = true;
				ref.setTitle(this.num);
			};
			clip['itm'+i].onRollOut = function() {
				this.cl1.setRGB(ref.config['frontcolor']);
				this.icn._visible = false;
				ref.clearTitle();
			};
			clip['itm'+i].onRelease = function() {
				ref.getLink(this.num);
			};
			clip['itm'+i]._visible = false;
			clip['itm'+i]._alpha = 0;
		}
	};


	/** Load the recommendations from XML **/
	private function loadRecommendations(rcm:Object) {
		recommendations = new Array();
		for (var i=0; i<rcm['childs'].length; i++) {
			var obj = new Object();
			for (var j=0; j < rcm['childs'][i]['childs'].length; j++) {
				obj[rcm['childs'][i]['childs'][j]['name']] =
					rcm['childs'][i]['childs'][j]['value'];
			}
			recommendations.push(obj);
		}
		if(recommendations.length < maximum) {
			for(var i=0; i<recommendations.length; i++) {
				clip['itm'+i]._x += 35*(maximum-recommendations.length);
			}
		}
		showRecommendations(0);
	};


	/** Show the recommendations on screen **/
	private function showRecommendations(off:Number) {
		arguments.length == 1 ? offset = off: null;
		offset == 0 ? clip.prv._visible = false: clip.prv._visible = true;
		offset >= recommendations.length-maximum ? clip.nxt._visible = false: clip.nxt._visible = true;
		for(var i=0; i<maximum; i++) {
			clip['itm'+i].num = i+offset;
			if(recommendations[i+offset] == undefined) {
				clip['itm'+i]._visible = false;
				clip['itm'+i]._alpha = 0;
			} else {
				clip['itm'+i].ldr.loadImage(recommendations[i+offset]['image']);
			}
		}
		if(Stage['displayState'] == "fullScreen") {
			clip._x = Math.round(Stage.width/2 - clip._width/2)-10;
			clip._y = Stage.height-165;
		} else {
			clip._x = Math.round(config['displaywidth']/2 - clip._width/2)-10;
			clip._y = config['displayheight']-85;
		}
	};


	/** lower the list with related items **/
	private function setState(stt:Number) {
		if(stt == 3) {
			if(recommendations == undefined) {
				parser.parse(config['recommendations']);
			} else {
				showRecommendations();
			}
			clip._visible = true;
			config['clip'].display.thumb._alpha = 33;
		} else if (stt == 1 || stt == 2) {
			clip._visible = false;
			config['clip'].display.thumb._alpha = 100;
		}
	};


	/** Set the title of the rolled over thumb. **/
	private function setTitle(idx:Number) {
		clip.txt.text = recommendations[idx]['title'];
	};


	/** Clear the title field again. **/
	private function clearTitle() {
		clip.txt.text = "";
	};


	/** Jump to the page with the requested file **/
	private function getLink(idx:Number) {
		getURL(recommendations[idx]['link'],config['linktarget']);
	};


	/** OnResize Handler: catches stage resizing. **/
	public function onResize() {
		if(config['displayheight'] >= config["height"]) {
			config["height"] = config["displayheight"] = Stage.height;
			config["width"] = config["displaywidth"] = Stage.width;
		}
		showRecommendations(); 
	};


	/** Catches fullscreen escape.  **/
	public function onFullScreen(fs:Boolean) {
		showRecommendations();
	};


}