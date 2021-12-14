/// <reference lib="dom" />
// tslint:disable function-constructor no-eval no-duplicate-super max-classes-per-file
import getCallsites from "callsites";
import { EventEmitter } from "events";
import { cpus } from 'os';
import * as path from "path";
import { fileURLToPath } from "url";
let tsNodeAvailable;
export const defaultPoolSize = cpus().length;
function detectTsNode() {
    if (typeof __non_webpack_require__ === "function") {
        // Webpack build: => No ts-node required or possible
        return false;
    }
    if (tsNodeAvailable) {
        return tsNodeAvailable;
    }
    try {
        eval("require").resolve("ts-node");
        tsNodeAvailable = true;
    }
    catch (error) {
        if (error && error.code === "MODULE_NOT_FOUND") {
            tsNodeAvailable = false;
        }
        else {
            // Re-throw
            throw error;
        }
    }
    return tsNodeAvailable;
}
function createTsNodeModule(scriptPath) {
    const content = `
    require("ts-node/register/transpile-only");
    require(${JSON.stringify(scriptPath)});
  `;
    return content;
}
function rebaseScriptPath(scriptPath, ignoreRegex) {
    const parentCallSite = getCallsites().find((callsite) => {
        const filename = callsite.getFileName();
        return Boolean(filename &&
            !filename.match(ignoreRegex) &&
            !filename.match(/[\/\\]master[\/\\]implementation/) &&
            !filename.match(/^internal\/process/));
    });
    const rawCallerPath = parentCallSite ? parentCallSite.getFileName() : null;
    let callerPath = rawCallerPath ? rawCallerPath : null;
    if (callerPath && callerPath.startsWith('file:')) {
        callerPath = fileURLToPath(callerPath);
    }
    const rebasedScriptPath = callerPath ? path.join(path.dirname(callerPath), scriptPath) : scriptPath;
    return rebasedScriptPath;
}
function resolveScriptPath(scriptPath, baseURL) {
    const makeRelative = (filePath) => {
        // eval() hack is also webpack-related
        return path.isAbsolute(filePath) ? filePath : path.join(baseURL || eval("__dirname"), filePath);
    };
    const workerFilePath = typeof __non_webpack_require__ === "function"
        ? __non_webpack_require__.resolve(makeRelative(scriptPath))
        : eval("require").resolve(makeRelative(rebaseScriptPath(scriptPath, /[\/\\]worker_threads[\/\\]/)));
    return workerFilePath;
}
function initWorkerThreadsWorker() {
    // Webpack hack
    const NativeWorker = typeof __non_webpack_require__ === "function"
        ? __non_webpack_require__("worker_threads").Worker
        : eval("require")("worker_threads").Worker;
    let allWorkers = [];
    class Worker extends NativeWorker {
        constructor(scriptPath, options) {
            const resolvedScriptPath = options && options.fromSource
                ? null
                : resolveScriptPath(scriptPath, (options || {})._baseURL);
            if (!resolvedScriptPath) {
                // `options.fromSource` is true
                const sourceCode = scriptPath;
                super(sourceCode, Object.assign(Object.assign({}, options), { eval: true }));
            }
            else if (resolvedScriptPath.match(/\.tsx?$/i) && detectTsNode()) {
                super(createTsNodeModule(resolvedScriptPath), Object.assign(Object.assign({}, options), { eval: true }));
            }
            else if (resolvedScriptPath.match(/\.asar[\/\\]/)) {
                // See <https://github.com/andywer/threads-plugin/issues/17>
                super(resolvedScriptPath.replace(/\.asar([\/\\])/, ".asar.unpacked$1"), options);
            }
            else {
                super(resolvedScriptPath, options);
            }
            this.mappedEventListeners = new WeakMap();
            allWorkers.push(this);
        }
        addEventListener(eventName, rawListener) {
            const listener = (message) => {
                rawListener({ data: message });
            };
            this.mappedEventListeners.set(rawListener, listener);
            this.on(eventName, listener);
        }
        removeEventListener(eventName, rawListener) {
            const listener = this.mappedEventListeners.get(rawListener) || rawListener;
            this.off(eventName, listener);
        }
    }
    const terminateWorkersAndMaster = () => {
        // we should terminate all workers and then gracefully shutdown self process
        Promise.all(allWorkers.map(worker => worker.terminate())).then(() => process.exit(0), () => process.exit(1));
        allWorkers = [];
    };
    // Take care to not leave orphaned processes behind. See #147.
    process.on("SIGINT", () => terminateWorkersAndMaster());
    process.on("SIGTERM", () => terminateWorkersAndMaster());
    class BlobWorker extends Worker {
        constructor(blob, options) {
            super(Buffer.from(blob).toString("utf-8"), Object.assign(Object.assign({}, options), { fromSource: true }));
        }
        static fromText(source, options) {
            return new Worker(source, Object.assign(Object.assign({}, options), { fromSource: true }));
        }
    }
    return {
        blob: BlobWorker,
        default: Worker
    };
}
function initTinyWorker() {
    const TinyWorker = require("tiny-worker");
    let allWorkers = [];
    class Worker extends TinyWorker {
        constructor(scriptPath, options) {
            // Need to apply a work-around for Windows or it will choke upon the absolute path
            // (`Error [ERR_INVALID_PROTOCOL]: Protocol 'c:' not supported`)
            const resolvedScriptPath = options && options.fromSource
                ? null
                : process.platform === "win32"
                    ? `file:///${resolveScriptPath(scriptPath).replace(/\\/g, "/")}`
                    : resolveScriptPath(scriptPath);
            if (!resolvedScriptPath) {
                // `options.fromSource` is true
                const sourceCode = scriptPath;
                super(new Function(sourceCode), [], { esm: true });
            }
            else if (resolvedScriptPath.match(/\.tsx?$/i) && detectTsNode()) {
                super(new Function(createTsNodeModule(resolveScriptPath(scriptPath))), [], { esm: true });
            }
            else if (resolvedScriptPath.match(/\.asar[\/\\]/)) {
                // See <https://github.com/andywer/threads-plugin/issues/17>
                super(resolvedScriptPath.replace(/\.asar([\/\\])/, ".asar.unpacked$1"), [], { esm: true });
            }
            else {
                super(resolvedScriptPath, [], { esm: true });
            }
            allWorkers.push(this);
            this.emitter = new EventEmitter();
            this.onerror = (error) => this.emitter.emit("error", error);
            this.onmessage = (message) => this.emitter.emit("message", message);
        }
        addEventListener(eventName, listener) {
            this.emitter.addListener(eventName, listener);
        }
        removeEventListener(eventName, listener) {
            this.emitter.removeListener(eventName, listener);
        }
        terminate() {
            allWorkers = allWorkers.filter(worker => worker !== this);
            return super.terminate();
        }
    }
    const terminateWorkersAndMaster = () => {
        // we should terminate all workers and then gracefully shutdown self process
        Promise.all(allWorkers.map(worker => worker.terminate())).then(() => process.exit(0), () => process.exit(1));
        allWorkers = [];
    };
    // Take care to not leave orphaned processes behind
    // See <https://github.com/avoidwork/tiny-worker#faq>
    process.on("SIGINT", () => terminateWorkersAndMaster());
    process.on("SIGTERM", () => terminateWorkersAndMaster());
    class BlobWorker extends Worker {
        constructor(blob, options) {
            super(Buffer.from(blob).toString("utf-8"), Object.assign(Object.assign({}, options), { fromSource: true }));
        }
        static fromText(source, options) {
            return new Worker(source, Object.assign(Object.assign({}, options), { fromSource: true }));
        }
    }
    return {
        blob: BlobWorker,
        default: Worker
    };
}
let implementation;
let isTinyWorker;
function selectWorkerImplementation() {
    try {
        isTinyWorker = false;
        return initWorkerThreadsWorker();
    }
    catch (error) {
        // tslint:disable-next-line no-console
        console.debug("Node worker_threads not available. Trying to fall back to tiny-worker polyfill...");
        isTinyWorker = true;
        return initTinyWorker();
    }
}
export function getWorkerImplementation() {
    if (!implementation) {
        implementation = selectWorkerImplementation();
    }
    return implementation;
}
export function isWorkerRuntime() {
    if (isTinyWorker) {
        return typeof self !== "undefined" && self.postMessage ? true : false;
    }
    else {
        // Webpack hack
        const isMainThread = typeof __non_webpack_require__ === "function"
            ? __non_webpack_require__("worker_threads").isMainThread
            : eval("require")("worker_threads").isMainThread;
        return !isMainThread;
    }
}
