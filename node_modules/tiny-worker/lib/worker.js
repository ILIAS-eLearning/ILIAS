"use strict";

var fs = require("fs"),
    path = require("path"),
    vm = require("vm"),
    noop = require(path.join(__dirname, "noop.js")),
    events = /^(error|message)$/;

function toFunction(arg) {
	var __worker_evaluated_function_ = null;
	eval("__worker_evaluated_function_ = (" + arg + ")"); // eslint-disable-line no-eval

	return __worker_evaluated_function_;
}

// Bootstraps the Worker
process.once("message", function (obj) {
	var isfn = obj.isfn,
	    input = obj.input,
	    esm = obj.esm,
	    cwd = obj.cwd;

	var exp = isfn ? toFunction(input) : esm ? "require(\"" + input + "\");" : fs.readFileSync(input, "utf8");

	global.self = {
		close: function close() {
			process.exit(0);
		},
		postMessage: function postMessage(msg) {
			process.send(JSON.stringify({ data: msg }, null, 0));
		},
		onmessage: void 0,
		onerror: function onerror(err) {
			process.send(JSON.stringify({ error: err.message, stack: err.stack }, null, 0));
		},
		addEventListener: function addEventListener(event, fn) {
			if (events.test(event)) {
				global["on" + event] = global.self["on" + event] = fn;
			}
		}
	};

	global.__dirname = cwd;
	global.__filename = __filename;
	global.require = esm ? require("esm")(module) : require;

	global.importScripts = function () {
		for (var _len = arguments.length, files = Array(_len), _key = 0; _key < _len; _key++) {
			files[_key] = arguments[_key];
		}

		if (files.length > 0) {
			vm.createScript(files.map(function (file) {
				return fs.readFileSync(file, "utf8");
			}).join("\n")).runInThisContext();
		}
	};

	Object.keys(global.self).forEach(function (key) {
		global[key] = global.self[key];
	});

	process.on("message", function (msg) {
		try {
			(global.onmessage || global.self.onmessage || noop)(JSON.parse(msg));
		} catch (err) {
			(global.onerror || global.self.onerror || noop)(err);
		}
	});

	process.on("error", function (err) {
		(global.onerror || global.self.onerror || noop)(err);
	});

	if (typeof exp === "function") {
		exp();
	} else {
		vm.createScript(exp).runInThisContext();
	}
});

process.once("disconnect", function () {
	return process.exit();
});
