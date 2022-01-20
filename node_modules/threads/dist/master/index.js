"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Worker = exports.BlobWorker = exports.isWorkerRuntime = exports.Thread = exports.spawn = exports.Pool = void 0;
const implementation_1 = require("./implementation");
Object.defineProperty(exports, "isWorkerRuntime", { enumerable: true, get: function () { return implementation_1.isWorkerRuntime; } });
var pool_1 = require("./pool");
Object.defineProperty(exports, "Pool", { enumerable: true, get: function () { return pool_1.Pool; } });
var spawn_1 = require("./spawn");
Object.defineProperty(exports, "spawn", { enumerable: true, get: function () { return spawn_1.spawn; } });
var thread_1 = require("./thread");
Object.defineProperty(exports, "Thread", { enumerable: true, get: function () { return thread_1.Thread; } });
/** Separate class to spawn workers from source code blobs or strings. */
exports.BlobWorker = implementation_1.getWorkerImplementation().blob;
/** Worker implementation. Either web worker or a node.js Worker class. */
exports.Worker = implementation_1.getWorkerImplementation().default;
