/**
* View for an actionscript-drawn equalizer (thanks to Brewer).
* The eq. is fake, but it considers playstate and volume.
*
* @author	Jeroen Wijering
* @version	1.1
**/


import com.jeroenwijering.players.*;


class com.jeroenwijering.players.EqualizerView extends AbstractView {


	/** EQ movieclip reference **/
	private var eqClip:MovieClip;
	/** current volume **/
	private var currentVolume:Number;
	/** number of stripes to display in the EQ **/
	private var eqStripes:Number;


	/** Constructor; just inheriting. **/
	function EqualizerView(ctr:AbstractController,cfg:Object,fed:Object) {
		super(ctr,cfg,fed);
		setupEQ();
		Stage.addListener(this);
	};


	/** setup EQ **/
	private function setupEQ() {
		eqClip = config["clip"].equalizer;
		eqClip._y = config["displayheight"] - 50;
		eqStripes = Math.floor((config['displaywidth'] - 20)/6);
		eqClip.stripes.duplicateMovieClip("stripes2",1);
		eqClip.mask.duplicateMovieClip("mask2",3);
		eqClip.stripes._width = eqClip.stripes2._width = 
			config['displaywidth']-20;
		eqClip.stripes.top.col = new Color(eqClip.stripes.top);
		eqClip.stripes.top.col.setRGB(config['lightcolor']);
		eqClip.stripes.bottom.col = new Color(eqClip.stripes.bottom);
		eqClip.stripes.bottom.col.setRGB(0xFFFFFF);
		eqClip.stripes2.top.col = new Color(eqClip.stripes2.top);
		eqClip.stripes2.top.col.setRGB(config['lightcolor']);
		eqClip.stripes2.bottom.col = new Color(eqClip.stripes2.bottom);
		eqClip.stripes2.bottom.col.setRGB(0xFFFFFF);
		eqClip.stripes.setMask(eqClip.mask);
		eqClip.stripes2.setMask(eqClip.mask2);
		eqClip.stripes._alpha = eqClip.stripes2._alpha = 50;
		setInterval(this,"drawEqualizer",100,eqClip.mask);
		setInterval(this,"drawEqualizer",100,eqClip.mask2);
	};


	/** Draw a rondom frame for the equalizer **/
	private function drawEqualizer(tgt:MovieClip) {
		tgt.clear();
	    tgt.beginFill(0x000000, 100);
		tgt.moveTo(0,0);
		var h = Math.round(currentVolume/4);
		for (var j=0; j< eqStripes; j++) {
			var z = random(h)+h/2 + 2;
			if(j == Math.floor(eqStripes/2)) { z = 0; }
			tgt.lineTo(j*6,-1);
			tgt.lineTo(j*6,-z);
			tgt.lineTo(j*6+4,-z);
			tgt.lineTo(j*6+4,-1);
			tgt.lineTo(j*6,-1); 
		}
		tgt.lineTo(j*6,0);
		tgt.lineTo(0,0);
		tgt.endFill();
	};


	/** Change the height to reflect the volume **/
	private function setVolume(vol:Number) { currentVolume = vol; };


	/** Only display the eq if a song is playing **/
	private function setState(stt:Number) { 
		stt == 2 ? eqClip._visible = true: eqClip._visible = false;
	};


	/** Hide the EQ on fullscreen view  **/
	public function onFullScreen(fs:Boolean) { 
		if(fs == true) {
			eqClip._visible = false;
		} else {
			eqClip._visible = true;
		}
	};


}