# tiny-worker
Tiny WebWorker for Server

`require()` is available for flexible inline Worker scripts. Optional parameters `args` Array & `options` Object; see `child_process.fork()` documentation.

[![build status](https://secure.travis-ci.org/avoidwork/tiny-worker.svg)](http://travis-ci.org/avoidwork/tiny-worker)

## Example
#### Creating a Worker from a file
The worker script:
```javascript
onmessage = function (ev) {
	postMessage(ev.data);
};
```

The core script:
```javascript
var Worker = require("tiny-worker");
var worker = new Worker("repeat.js");

worker.onmessage = function (ev) {
	console.log(ev.data);
	worker.terminate();
};

worker.postMessage("Hello World!");
```

#### Enable ES6 import/export within Worker file
The worker helper script (helper.js):
```javascript
export const dataFormatter = (data) => {
	return `${data} World!`;
};
```

The worker script (repeat.js):
```javascript
import { dataFormatter } from "./helper";

onmessage = function (ev) {
	const data = dataFormatter(ev.data);
	postMessage(data);
};
```

The core script:
```javascript
var Worker = require("tiny-worker");
var worker = new Worker("repeat.js", [], {esm: true});

worker.onmessage = function (ev) {
	console.log(ev.data);
	worker.terminate();
};

worker.postMessage("Hello");
```

#### Creating a Worker from a Function
```javascript
var Worker = require("tiny-worker");
var worker = new Worker(function () {
	self.onmessage = function (ev) {
		postMessage(ev.data);
	};
});

worker.onmessage = function (ev) {
	console.log(ev.data);
	worker.terminate();
};

worker.postMessage("Hello World!");
```

# Debugging
To be able to debug a child process, it must have a differnt debug port than the parent. 
Tiny worker does this by adding a random port within a range to the parents debug port.
The default Range is `[1, 300]`, it can be changed with the `setRange(min, max)` method.
To disable any automatic port redirection set `options.noDebugRedirection = true`.

### automatic redirection
```javascript
//parent is started with '--debug=1234'
var Worker = require("tiny-worker");
Worker.setRange(2, 20);

var worker = new Worker(function () {
	postMessage(process.debugPort); 
});

worker.onmessage = function (ev) {
	console.log(ev.data); //prints any number between 1236 and 1254
	worker.terminate();
}
```

### manual redirection
```javascript
//parent is started with '--debug=1234'
var Worker = require("tiny-worker");

var worker = new Worker(function () {
	postMessage(process.debugPort); 
}, [], {noDebugRedirection: true, execArgv: ["--debug=1235"]});

worker.onmessage = function (ev) {
	console.log(ev.data); //prints 1235
	worker.terminate();
}
```

## Properties
#### onmessage
Message handler, accepts an `Event`

#### onerror
Error handler, accepts an `Event`

## API
#### addEventListener(event, fn)
Adds an event listener

#### postMessage()
Broadcasts a message to the `Worker`

#### terminate()
Terminates the `Worker`

#### static setRange(min, max)
Sets range for debug ports, only affects current process.
Returns true if successful.

## FAQ
1. I have an orphaned child process that lives on past the parent process' lifespan
  * Most likely a `SIGTERM` or `SIGINT` is not reaching the child process
2. How do I insure all process are terminated?
  * In your core script register a listener for `SIGTERM` or `SIGINT` via `process.on()` which terminates (all) worker process(es) and then gracefully shutdowns via `process.exit(0);`
3. Why `SIGTERM` or `SIGINT`?
  * Unix/BSD will work with `SIGTERM`, but if you also need to support Windows use `SIGINT`

## License
Copyright (c) 2019 Jason Mulligan
Licensed under the BSD-3 license
