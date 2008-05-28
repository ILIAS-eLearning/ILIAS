/**
* Basic view class of the players MCV pattern, extended by all views.
* Create you own views by extending this one.
*
* @author	Jeroen Wijering
* @version	1.2
**/


import com.jeroenwijering.players.*;


class com.jeroenwijering.players.AbstractView {


	/** Controller reference **/
	private var controller:AbstractController;
	/** reference to config Array **/
	private var config:Object;
	/** reference to feed Array **/
	private var feeder:Object;


	/** Constructor **/
	function AbstractView(ctr:AbstractController,cfg:Object,fed:Object) {
		controller = ctr;
		config = cfg;
		feeder = fed;
	};


	/** Receive updates from the models. **/
	public function getUpdate(typ:String,pr1:Number,pr2:Number):Void {
		//trace("view: "+typ+": "+pr1+","+pr2);
		switch(typ) {
			case "state":
				setState(pr1);
				break;
			case "load":
				setLoad(pr1);
				break;
			case "time":
				setTime(pr1,pr2);
				break;
			case "item":
				setItem(pr1);
				break;
			case "size":
				setSize(pr1,pr2);
				break;
			case "volume":
				setVolume(pr1);
				break;
			default:
				trace("View: incompatible update received");
				break;
		}
	};


	/** Empty state handler **/
	private function setState(pr1:Number) {};


	/** Empty load handler **/
	private function setLoad(pr1:Number) {};


	/** Empty time handler **/
	private function setTime(pr1:Number,pr2:Number) {};


	/** Empty item handler **/
	private function setItem(pr1:Number) {};


	/** Empty item handler **/
	private function setSize(pr1:Number,pr2:Number) {};


	/** Empty volume handler **/
	private function setVolume(pr1:Number) {};


	/** Send event to the controller. **/
	private function sendEvent(typ:String,prm:Number) {
		controller.getEvent(typ,prm); 
	};


}