/**
* Add a search bar to the player
**/


import com.jeroenwijering.players.*;


class com.jeroenwijering.players.SearchView extends AbstractView { 


	/** Save the focus state (to capture enters) **/
	public static var focussed:Boolean = false;

	/** Constructor **/
	function SearchView(ctr:AbstractController,cfg:Object,fed:Object) {
		super(ctr,cfg,fed);
		Key.addListener(this);
		setDimensions();
	};


	/** Setup the dimensions of the search bar **/
	private function setDimensions() {
		var ref = this;
		var tgt = config['clip'].search;
		if(config["displayheight"] == config["height"] - config["searchbar"]) {
			tgt._y = config['displayheight'];
		} else {
			tgt._y = config['displayheight'] + config['controlbar'];
		}
		tgt.ipt._width = config['width'] - 95;
		tgt.ipt.onSetFocus = function() {
			SearchView.focussed = true;
		};
		tgt.ipt.onKillFocus = function() {
			SearchView.focussed = false;
		};
		tgt.bck._width = config['width'];
		tgt.box._width = config['width'] - 95;
		tgt.btn._x = config['width'] - 86;
		tgt.btn.col = new Color(tgt.btn.icn);
		tgt.btn.col.setRGB(config['frontcolor']);
		tgt.btn.onRollOver = function() {
			this.col.setRGB(ref.config['lightcolor']);
		};
		tgt.btn.onRollOut = function() {
			this.col.setRGB(ref.config['frontcolor']);
		};
		tgt.btn.onRelease = function() {
			ref.doSearch();
		}
	};


	/** start the search function **/
	private function doSearch() {
		var txt = escape(config['clip'].search.ipt.text);
		getURL(config['searchlink']+txt,config['linktarget']);
	};


	/** KeyDown handler, forwarded by Key object **/
	public function onKeyDown() {
		if(Key.getCode() == 13 && SearchView.focussed == true) { doSearch(); }
	};


}