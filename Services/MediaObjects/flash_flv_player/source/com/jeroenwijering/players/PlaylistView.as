/**
* Playlist view management of the players MCV pattern.
*
* @author	Jeroen Wijering
* @version	1.9
**/


import com.jeroenwijering.players.*;
import com.jeroenwijering.utils.*;
import com.jeroenwijering.feeds.FeedListener;


class com.jeroenwijering.players.PlaylistView extends AbstractView 
	implements FeedListener { 


	/** ImageLoader **/
	private var thumbLoader:ImageLoader;
	/** Scroller instance **/
	private var listScroller:Scroller;
	/** Position of the playlist **/
	private var listRight:Boolean;
	/** Position of the playlist **/
	private var listWidth:Number;
	/** number of items in the playlist **/
	private var listLength:Number;
	/** Currently highlighted playlist item **/
	private var currentItem:Number;
	/** Save the current time **/
	private var currentTime:Number = -10;
	


	/** Constructor **/
	function PlaylistView(ctr:AbstractController,cfg:Object,fed:Object) { 
		super(ctr,cfg,fed);
		if(config["displaywidth"] < config["width"]) { 
			listRight = true;
			listWidth = config["width"]-config["displaywidth"]-1;
		} else {
			listRight = false;
			listWidth = config["width"];
		}
		setButtons();
		Stage.addListener(this);
		feeder.addListener(this);
	};


	/** OnLoad event handler; sets up the playlist sizes and colors. **/
	private function setButtons() {
		var ref = this;
		var tgt = config["clip"].playlist;
		tgt.btn._visible = false;
		// iterate playlist and setup each button
		listLength = feeder.feed.length;
		var num = 0;
		for(var i=0; i<feeder.feed.length; i++) {
			if(feeder.feed[i]['category'] != 'commercial' && 
				feeder.feed[i]['category'] != 'preroll' && 
				feeder.feed[i]['category'] != 'postroll') {
			// set text and background
			tgt.btn.duplicateMovieClip("btn"+i,i);
			tgt["btn"+i].txt._width = listWidth - 20;
			tgt["btn"+i].col = new Color(tgt["btn"+i].bck);
			tgt["btn"+i].col.setRGB(config["frontcolor"]);
			tgt["btn"+i].col2 = new Color(tgt["btn"+i].icn);
			tgt["btn"+i].col2.setRGB(config["frontcolor"]);
			tgt["btn"+i].bck._width = listWidth;
			tgt["btn"+i].bck.onRollOver = function() { 
				this._parent.txt.textColor = ref.config["backcolor"];
				this._parent.col.setRGB(ref.config["lightcolor"]);
				this._parent.col2.setRGB(ref.config["backcolor"]);
				if(ref.currentItem != this._parent.getDepth()) {
					this._alpha = 90;
				}
			};
			tgt["btn"+i].bck.onRollOut = function() { 
				this._parent.col.setRGB(ref.config["frontcolor"]);
				if(ref.currentItem != this._parent.getDepth()) {
					this._parent.txt.textColor=ref.config["frontcolor"];
					this._parent.col2.setRGB(ref.config["frontcolor"]);
					this._alpha = 10;
				}
			};
			tgt["btn"+i].bck.onRelease = function() {
				ref.sendEvent("playitem",this._parent.getDepth());
			};
			// set thumbnails
			if(config["thumbsinplaylist"] == "true") {
				tgt["btn"+i].bck._height = 40;
				tgt["btn"+i].icn._y += 9;
				tgt["btn"+i]._y = num*41;
				tgt["btn"+i].txt._height += 20;
				if(feeder.feed[i]["author"]  == undefined) {
					tgt["btn"+i].txt.htmlText = "<b>"+(i+1)+"</b>:<br />"+
						feeder.feed[i]["title"];
				} else {
					tgt["btn"+i].txt.htmlText = "<b>" + 
						feeder.feed[i]["author"] + "</b>:<br />" + 
						feeder.feed[i]["title"];
				}
				if(feeder.feed[i]["image"] != undefined) {
					tgt["btn"+i].txt._x += 60;
					tgt["btn"+i].txt._width -= 60;
					thumbLoader = 
						new ImageLoader(tgt["btn"+i].img,"true",60,40);
					thumbLoader.loadImage(feeder.feed[i]["image"]);
					tgt["btn"+i].img.setMask(tgt["btn"+i].msk);
				} else {
					tgt["btn"+i].msk._height = 10;
					tgt["btn"+i].img._visible = false;
					tgt["btn"+i].msk._visible = false;
				}
			} else {
				tgt["btn"+i]._y = num*23;
				if(feeder.feed[i]["author"]  == undefined) {
					tgt["btn"+i].txt.htmlText = feeder.feed[i]["title"];
				} else {
					tgt["btn"+i].txt.htmlText = feeder.feed[i]["author"] +
						" - " + feeder.feed[i]["title"];
				}
				tgt["btn"+i].msk._height = 10;
				tgt["btn"+i].img._visible = 
					tgt["btn"+i].msk._visible = false;
			}
			tgt["btn"+i].txt.textColor = config["frontcolor"];
			// set link icon
			if(feeder.feed[i]["link"] != undefined) {
				tgt["btn"+i].txt._width -= 20;
				tgt["btn"+i].icn._x = listWidth - 24;
				tgt["btn"+i].icn.onRollOver = function() { 
					this._parent.col2.setRGB(ref.config["lightcolor"]);
				};
				tgt["btn"+i].icn.onRollOut = function() { 
					if(ref.currentItem == this._parent.getDepth()) {
					this._parent.col2.setRGB(ref.config["backcolor"]);
					} else {
					this._parent.col2.setRGB(ref.config["frontcolor"]);
					}
				};
				tgt["btn"+i].icn.onRelease = function() { 
					ref.sendEvent("getlink",this._parent.getDepth());
				};
			} else { 
				tgt["btn"+i].icn._visible = false;
			}
			num++;
		} 
		}
		// setup mask and scrollbar if needed
		var msk = config["clip"].playlistmask;
		if(listRight == true) { 
			msk._x = tgt._x = Number(config["displaywidth"]) + 1;
			msk._y = tgt._y = 0;
			msk._height =  config["displayheight"];
		} else {
			msk._y = tgt._y = config["displayheight"] + 
				config["controlbar"] + config["searchbar"];
			msk._height = config["height"] - msk._y;
		}
		msk._width = listWidth;
		tgt.setMask(msk);
		if(tgt._height > msk._height + 2 && feeder.feed.length > 1) {
			if(config["autoscroll"] == "false") {
				msk._width -= 10;
				for(var i=0; i<feeder.feed.length; i++) {
					tgt["btn"+i].bck._width -= 10;
					tgt["btn"+i].icn._x -= 10;
				}
				listScroller = new Scroller(tgt,msk,false,
					config["frontcolor"],config["lightcolor"]);
			} else {	
				listScroller = new Scroller(tgt,msk,true,
					config["frontcolor"],config["lightcolor"]);
			}
		}
	};


	/** Set a new item as the current playing one **/
	private function setItem(itm:Number):Void {
		var tgt = config["clip"].playlist;
		tgt["btn"+currentItem].col.setRGB(config["frontcolor"]);
		tgt["btn"+currentItem].bck._alpha = 10;
		tgt["btn"+currentItem].col2.setRGB(config["frontcolor"]);
		tgt["btn"+currentItem].txt.textColor = config["frontcolor"];
		currentItem = itm;
		tgt["btn"+currentItem].txt.textColor = config["backcolor"];
		tgt["btn"+currentItem].col2.setRGB(config["backcolor"]);
		tgt["btn"+currentItem].bck._alpha = 90;
		if(config["autoscroll"] == "false") {
			listScroller.scrollTo(tgt["btn"+currentItem]._y);
		}
	};


	/** Set a different chapter if the feed is a chapterindex **/
	private function setTime(elp:Number,rem:Number) {
		if(feeder.ischapters == true && Math.abs(elp-currentTime) > 5) {
			currentTime = elp;
			for (var i=0; i<feeder.feed.length; i++) {
				if(feeder.feed[i]["start"] > currentTime) {
					if(i != currentItem+1) { setItem(i-1); }
					break;
				}
			}
		}
	};


	/** Hide the scrollbar on fullscreen **/
	public function onFullScreen(fs:Boolean) {
		if(listScroller == undefined) {
			break;
		} else if(fs == true) {
			config["clip"].scrollbar._visible = false;
		} else {
			config["clip"].scrollbar._visible = true; 
		}
	};


	/** Render a new playlist when the feed updates **/
	public function onFeedUpdate(typ:String) {
		listScroller.purgeScrollbar();
		delete listScroller;
		var tgt = config["clip"].playlist;
		for(var i=0; i<999; i++) {
			tgt["btn"+i].removeMovieClip();
		}
		setButtons();
		setItem(currentItem);
	};


}