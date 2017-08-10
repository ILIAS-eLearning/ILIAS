package com.yahoo.yui
{
	import flash.accessibility.AccessibilityProperties;
	import flash.display.DisplayObject;
	import flash.display.Sprite;
	import flash.display.StageAlign;
	import flash.display.StageScaleMode;
	import flash.errors.IOError;
	import flash.events.Event;
	import flash.external.ExternalInterface;
	import flash.system.Security;
	import flash.text.TextField;
	import flash.text.TextFieldAutoSize;
	import flash.text.TextFormat;

	public class YUIAdapter extends Sprite
	{

	//--------------------------------------
	//  Constructor
	//--------------------------------------

		/**
		 * Constructor.
		 */
		public function YUIAdapter()
		{
			if(this.stage)
			{
				this.stage.addEventListener(Event.RESIZE, stageResizeHandler);
				this.stage.scaleMode = StageScaleMode.NO_SCALE;
				this.stage.align = StageAlign.TOP_LEFT;
			}

			super();

			try
			{
				//show error popups
				ExternalInterface.marshallExceptions = true;
			}
			catch(error:Error)
			{
				//do nothing, we're in a flash player that properly displays exceptions
			}

			this._errorText = new TextField();
			this._errorText.defaultTextFormat = new TextFormat("_sans", 10, 0xff0000);
			this._errorText.wordWrap = true;
			this._errorText.autoSize = TextFieldAutoSize.LEFT;
			this._errorText.selectable = false;
			this._errorText.mouseEnabled = false;
			this.addChild(this._errorText);

			this.addEventListener(Event.ADDED, addedHandler);

			if(ExternalInterface.available)
			{
				this.initializeComponent();
				var swfReady:Object = {type: "swfReady"};
				this.dispatchEventToJavaScript(swfReady);
			}
			else
			{
				throw new IOError("Flash YUIComponent cannot communicate with JavaScript content.");
			}


		}

	//--------------------------------------
	//  Properties
	//--------------------------------------

		/**
		 * The element id that references the SWF in the HTML.
		 */
		protected var elementID:String;

		/**
		 * The globally accessible JavaScript function that accepts events through ExternalInterface.
		 */
		protected var javaScriptEventHandler:String;

		/**
		 * The reference to the Flash component.
		 */
		private var _component:DisplayObject;

		/**
		 * @private
		 */
		protected function get component():DisplayObject
		{
			return this._component;
		}

		/**
		 * @private
		 */
		protected function set component(value:DisplayObject):void
		{
			this._component = value;
			this.refreshComponentSize();
		}

		/**
		 * @private
		 * For errors that cannot be passed to JavaScript.
		 * (ONLY SecurityErrors when ExternalInterface is not available!)
		 */
		private var _errorText:TextField;

		/**
		 * @private
		 * Alternative text for assistive technology.
		 */
		private var _altText:String;

	//--------------------------------------
	//  Public Methods
	//--------------------------------------

		/**
		 * Gets the alternative text for assistive technology.
		 */
		public function getAltText():String
		{
			return this._altText;
		}

		/**
		 * Sets the alternative text for assistive technology.
		 */
		public function setAltText(value:String):void
		{
			this._altText = value;
			var accProps:AccessibilityProperties = new AccessibilityProperties();
			accProps.name = this._altText;
			accProps.forceSimple = true;
			accProps.noAutoLabeling = true;
			this.component.accessibilityProperties = accProps;
		}

	//--------------------------------------
	//  Protected Methods
	//--------------------------------------

		/**
		 * To be overridden by subclasses to add ExternalInterface callbacks.
		 */
		protected function initializeComponent():void
		{
			this.elementID = this.loaderInfo.parameters.YUISwfId;
			var idCheck:RegExp = /^yuiswf[0-9]*$/g;
			if (!idCheck.test(this.elementID)) {
  				this.elementID = "";
			}

			this.javaScriptEventHandler = this.loaderInfo.parameters.YUIBridgeCallback;
			var jsCheck:RegExp = /^[A-Za-z0-9.]*$/g;
			if (!jsCheck.test(this.javaScriptEventHandler)) {
				this.javaScriptEventHandler = "";
			}



			var allowedDomain:String = this.loaderInfo.parameters.allowedDomain;
			if(allowedDomain)
			{
				Security.allowDomain(allowedDomain);
				this.log("allowing: " + allowedDomain);
			}

			try
			{
				ExternalInterface.addCallback("getAltText", getAltText);
				ExternalInterface.addCallback("setAltText", setAltText);
			}
			catch(error:SecurityError)
			{
				//do nothing. it will be caught somewhere else.
			}
		}

		/**
		 * Sends a log message to the YUI Logger.
		 */
		protected function log(message:Object, category:String = null):void
		{
			if(message == null) message = "";
			this.dispatchEventToJavaScript({type: "log", message: message.toString(), category: category});
		}

		protected function showFatalError(message:Object):void
		{
			if(!message) message = "";
			if(this._errorText)
			{
				this._errorText.appendText(message.toString());
				//scroll to the new error if needed
				this._errorText.scrollV = this._errorText.maxScrollV;
				this._errorText.mouseEnabled = true;
				this._errorText.selectable = true;
			}
		}

		/**
		 * @private
		 *
		 * Dispatches an event object to the JavaScript wrapper element.
		 */
		protected function dispatchEventToJavaScript(event:Object):void
		{
			try
			{
				if(ExternalInterface.available) {
					ExternalInterface.call("YAHOO.widget.SWF.eventHandler", this.elementID, event);
				}
			}
			catch(error:Error)
			{
				if(error is SecurityError)
				{
					this.showFatalError("Warning: Cannot establish communication between YUI Charts and JavaScript. YUI Charts must be served from HTTP and cannot be viewed locally with file:/// protocol unless location is trusted by Flash Player.\n\nFor more information see:\nhttp://www.adobe.com/products/flashplayer/articles/localcontent/\n\n");
				}
			}
		}

		/**
		 * @private
		 *
		 * The size of the SWF/stage is dependant on the container it is in.
		 * The visual component will resize to match the stage size.
		 */
		protected function stageResizeHandler(event:Event):void
		{
			this.refreshComponentSize();

			if(this._errorText)
			{
				this._errorText.width = this.stage.stageWidth;
				this._errorText.height = this.stage.stageHeight;
			}

			this.log("resize (width: " + this.stage.stageWidth + ", height: " + this.stage.stageHeight + ")", LoggerCategory.INFO);
		}

		/**
		 * @private
		 */
		protected function refreshComponentSize():void
		{
			if(this.component)
			{
				this.component.x = this.component.y = 0;
				this.component.width = this.stage.stageWidth;
				this.component.height = this.stage.stageHeight;
			}
		}

		/**
		 * @private
		 * ensures that errorText is always on top!
		 */
		protected function addedHandler(event:Event):void
		{
			this.setChildIndex(this._errorText, this.numChildren - 1);
		}
	}
}