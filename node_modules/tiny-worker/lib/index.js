"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

var path = require("path"),
    fork = require("child_process").fork,
    worker = path.join(__dirname, "worker.js"),
    events = /^(error|message)$/,
    defaultPorts = { inspect: 9229, debug: 5858 };
var range = { min: 1, max: 300 };

var Worker = function () {
	function Worker(arg) {
		var _this = this;

		var args = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
		var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : { cwd: process.cwd() };

		_classCallCheck(this, Worker);

		var isfn = typeof arg === "function",
		    input = isfn ? arg.toString() : arg;

		if (!options.cwd) {
			options.cwd = process.cwd();
		}

		//get all debug related parameters
		var debugVars = process.execArgv.filter(function (execArg) {
			return (/(debug|inspect)/.test(execArg)
			);
		});
		if (debugVars.length > 0 && !options.noDebugRedirection) {
			if (!options.execArgv) {
				//if no execArgs are given copy all arguments
				debugVars = Array.from(process.execArgv);
				options.execArgv = [];
			}

			var inspectIndex = debugVars.findIndex(function (debugArg) {
				//get index of inspect parameter
				return (/^--inspect(-brk)?(=\d+)?$/.test(debugArg)
				);
			});

			var debugIndex = debugVars.findIndex(function (debugArg) {
				//get index of debug parameter
				return (/^--debug(-brk)?(=\d+)?$/.test(debugArg)
				);
			});

			var portIndex = inspectIndex >= 0 ? inspectIndex : debugIndex; //get index of port, inspect has higher priority

			if (portIndex >= 0) {
				var match = /^--(debug|inspect)(?:-brk)?(?:=(\d+))?$/.exec(debugVars[portIndex]); //get port
				var port = defaultPorts[match[1]];
				if (match[2]) {
					port = parseInt(match[2]);
				}
				debugVars[portIndex] = "--" + match[1] + "=" + (port + range.min + Math.floor(Math.random() * (range.max - range.min))); //new parameter

				if (debugIndex >= 0 && debugIndex !== portIndex) {
					//remove "-brk" from debug if there
					match = /^(--debug)(?:-brk)?(.*)/.exec(debugVars[debugIndex]);
					debugVars[debugIndex] = match[1] + (match[2] ? match[2] : "");
				}
			}
			options.execArgv = options.execArgv.concat(debugVars);
		}

		delete options.noDebugRedirection;

		this.child = fork(worker, args, options);
		this.onerror = undefined;
		this.onmessage = undefined;

		this.child.on("error", function (e) {
			if (_this.onerror) {
				_this.onerror.call(_this, e);
			}
		});

		this.child.on("message", function (msg) {
			var message = JSON.parse(msg);
			var error = void 0;

			if (!message.error && _this.onmessage) {
				_this.onmessage.call(_this, message);
			}

			if (message.error && _this.onerror) {
				error = new Error(message.error);
				error.stack = message.stack;

				_this.onerror.call(_this, error);
			}
		});

		this.child.send({ input: input, isfn: isfn, cwd: options.cwd, esm: options.esm });
	}

	_createClass(Worker, [{
		key: "addEventListener",
		value: function addEventListener(event, fn) {
			if (events.test(event)) {
				this["on" + event] = fn;
			}
		}
	}, {
		key: "postMessage",
		value: function postMessage(msg) {
			this.child.send(JSON.stringify({ data: msg }, null, 0));
		}
	}, {
		key: "terminate",
		value: function terminate() {
			this.child.kill("SIGINT");
		}
	}], [{
		key: "setRange",
		value: function setRange(min, max) {
			if (min >= max) {
				return false;
			}
			range.min = min;
			range.max = max;

			return true;
		}
	}]);

	return Worker;
}();

module.exports = Worker;
