/**
* Keyboard input management of the players MCV pattern.
* SPACE: playpause,UP:prev,DOWN:next,LEFT:volume-10,RIGHT:volume+10
*
* @author	Jeroen Wijering
* @version	1.3
**/


import com.jeroenwijering.players.*;


class com.jeroenwijering.players.InputView extends AbstractView { 


	/** The current volume **/
	private var currentVolume:Number;
	/** The current elapsed time **/
	private var currentTime:Number;


	/** Constructor **/
	function InputView(ctr:AbstractController,cfg:Object,fed:Object) {
		super(ctr,cfg,fed);
		Key.addListener(this);
	};


	/** Save current elapsed time **/
	private function setTime(elp:Number,rem:Number) { currentTime = elp; };


	/** Save current volume **/
	private function setVolume(vol:Number) { currentVolume = vol; };


	/** KeyDown handler, forwarded by Key object **/
	public function onKeyDown() {
		if (Key.getCode() == 32 && SearchView.focussed != true) {
			sendEvent("playpause"); 
		} else if (Key.getCode() == 37) {
			if(feeder.feed.length == 1) {
				sendEvent("scrub",currentTime-15);
			} else {
				 sendEvent("prev");
			}
		} else if (Key.getCode() == 39) {
			if(feeder.feed.length == 1) {
				sendEvent("scrub",currentTime+15);
			} else {
				sendEvent("next");
			}
		} else if (Key.getCode() == 38) {
			sendEvent("volume",currentVolume+10);
		} else if (Key.getCode() == 40) {
			sendEvent("volume",currentVolume-10);
		} else if (Key.getCode() == 77) {
			sendEvent("volume",0);
		}
	};


}