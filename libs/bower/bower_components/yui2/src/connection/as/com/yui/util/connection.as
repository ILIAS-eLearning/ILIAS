package com.yui.util
{
	import flash.display.Sprite;
	import flash.events.Event;
	import flash.events.IOErrorEvent;
	import flash.events.TimerEvent;
	import flash.events.IEventDispatcher;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.net.URLRequestHeader;
	import flash.net.URLLoader;
	import flash.net.URLVariables;
	import flash.utils.Timer;
	import flash.external.ExternalInterface;

	public class connection extends Sprite
	{
		private var httpComplete:Function;
		private var httpError:Function;
		private var httpTimeout:Function;
		private var loaderMap:Object = {};
		private var yId:String;
		private var handler:String = 'YAHOO.util.Connect.handleXdrResponse';

		public function connection() {
			ExternalInterface.addCallback("send", send);
			ExternalInterface.addCallback("abort", abort);
			ExternalInterface.addCallback("isCallInProgress", isCallInProgress);
			ExternalInterface.call('YAHOO.util.Connect.xdrReady');
		}

		public function send(uri:String, cb:Object, id:uint):void {
			var loader:URLLoader = new URLLoader(),
				request:URLRequest = new URLRequest(uri),
				timer:Timer,
				prop:String;

			for (prop in cb) {
				switch (prop) {
					case "method":
						if(cb.method === 'POST') {
							request.method = URLRequestMethod.POST;
						}
						break;
					case "data":
						request.data = cb.data;
						break;
					case "timeout":
						timer = new Timer(cb.timeout, 1);
						break;
				}
			}

			loaderMap[id] = { c:loader, readyState: 0, t:timer };
			defineListeners(id, timer);
			addListeners(loader, timer);
			loader.load(request);
			start(id);

			if (timer) {
				timer.start();
			}
		}

		private function defineListeners(id:uint, timer:Timer):void {
			httpComplete = function(e:Event):void { success(e, id, timer); };
			httpError = function(e:IOErrorEvent):void { failure(e, id, timer); };

			if (timer) {
				httpTimeout = function(e:TimerEvent):void { abort(id); };
			}
		}

		private function addListeners(loader:IEventDispatcher, timer:IEventDispatcher):void  {
			loader.addEventListener(Event.COMPLETE, httpComplete);
			loader.addEventListener(IOErrorEvent.IO_ERROR, httpError);

			if (timer) {
				timer.addEventListener(TimerEvent.TIMER_COMPLETE, httpTimeout);
			}
		}

		private function removeListeners(id:uint):void  {
			loaderMap[id].c.removeEventListener(Event.COMPLETE, httpComplete);
			loaderMap[id].c.removeEventListener(IOErrorEvent.IO_ERROR, httpError);

			if (loaderMap[id].t) {
				loaderMap[id].t.removeEventListener(TimerEvent.TIMER_COMPLETE, httpTimeout);
			}
		}

		private function start(id:uint):void {
			var response:Object = { tId: id, statusText: 'xdr:start' };

			loaderMap[id].readyState = 2;
			ExternalInterface.call(handler, response);
		}

		private function success(e:Event, id:uint, timer:Timer):void {
			var data:String = encodeURI(e.target.data),
				response:Object = {
					tId: id,
					statusText: 'xdr:success',
					responseText: data
				};

			loaderMap[id].readyState = 4;

			if (timer && timer.running) {
				timer.stop();
			}

			ExternalInterface.call(handler, response);
			destroy(id);
		}

		private function failure(e:Event, id:uint, timer:Timer):void {
			var data:String,
				response:Object = { tId: id, statusText: 'xdr:error' };

			loaderMap[id].readyState = 4;

			if (e is IOErrorEvent) {
				response.responseText = encodeURI(e.target.data);
			}

			if (timer && timer.running) {
				timer.stop();
			}

			ExternalInterface.call(handler, response);
			destroy(id);
		}

		public function abort(id:uint):void {
			var response:Object = { tId: id, statusText: 'xdr:abort' };

			loaderMap[id].c.close();

			if (loaderMap[id].t && loaderMap[id].t.running) {
				loaderMap[id].t.stop();
			}

			ExternalInterface.call(handler, response);
			destroy(id);
		}

		public function isCallInProgress(id:uint):Boolean {
			if (loaderMap[id]) {
				return loaderMap[id].readyState !== 4;
			}
			else {
				return false;
			}
		}

		private function destroy(id:uint):void {
			removeListeners(id);
			delete loaderMap[id];
		}
	}
}