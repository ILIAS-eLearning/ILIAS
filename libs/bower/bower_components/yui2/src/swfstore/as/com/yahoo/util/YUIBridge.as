	package com.yahoo.util
	{
		import flash.display.Stage;
		import flash.external.ExternalInterface;

		public class YUIBridge extends Object
		{
			private var _stage:Stage;
			public var flashvars:Object;
			private var _jsHandler:String;
			private var _swfID:String;

			public function YUIBridge(stage:Stage)
			{
				_stage = stage;
				flashvars = _stage.loaderInfo.parameters;
				if (flashvars["YUIBridgeCallback"] && flashvars["YUISwfId"] && ExternalInterface.available) {
					_jsHandler = flashvars["YUIBridgeCallback"];
					var jsCheck:RegExp = /^[A-Za-z0-9.]*$/g;
					if (!jsCheck.test(_jsHandler)) {
				 		_jsHandler = "";
					}

					_swfID = flashvars["YUISwfId"];
					var idCheck:RegExp = /^yuiswf[0-9]*$/g;
					if (!idCheck.test(_swfID)) {
  						_swfID = "";
					}
				}
			}

			public function addCallbacks (callbacks:Object) : void {
				if (ExternalInterface.available) {
					for (var callback:String in callbacks) {
	 					ExternalInterface.addCallback(callback, callbacks[callback]);
	 					trace("Added callback for " + callbacks[callback] + " named " + callback);
	 				}
	 				sendEvent({type:"swfReady"});
	 			}
			}

			public function sendEvent (evt:Object) : void {
				if(ExternalInterface.available) {
					trace("Sending event " + evt.type);
					ExternalInterface.call("YAHOO.widget.SWF.eventHandler", _swfID, evt);
				}

			}
		}
	}