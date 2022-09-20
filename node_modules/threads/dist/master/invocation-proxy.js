"use strict";
/*
 * This source file contains the code for proxying calls in the master thread to calls in the workers
 * by `.postMessage()`-ing.
 *
 * Keep in mind that this code can make or break the program's performance! Need to optimize more…
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.createProxyModule = exports.createProxyFunction = void 0;
const debug_1 = __importDefault(require("debug"));
const observable_fns_1 = require("observable-fns");
const common_1 = require("../common");
const observable_promise_1 = require("../observable-promise");
const transferable_1 = require("../transferable");
const messages_1 = require("../types/messages");
const debugMessages = debug_1.default("threads:master:messages");
let nextJobUID = 1;
const dedupe = (array) => Array.from(new Set(array));
const isJobErrorMessage = (data) => data && data.type === messages_1.WorkerMessageType.error;
const isJobResultMessage = (data) => data && data.type === messages_1.WorkerMessageType.result;
const isJobStartMessage = (data) => data && data.type === messages_1.WorkerMessageType.running;
function createObservableForJob(worker, jobUID) {
    return new observable_fns_1.Observable(observer => {
        let asyncType;
        const messageHandler = ((event) => {
            debugMessages("Message from worker:", event.data);
            if (!event.data || event.data.uid !== jobUID)
                return;
            if (isJobStartMessage(event.data)) {
                asyncType = event.data.resultType;
            }
            else if (isJobResultMessage(event.data)) {
                if (asyncType === "promise") {
                    if (typeof event.data.payload !== "undefined") {
                        observer.next(common_1.deserialize(event.data.payload));
                    }
                    observer.complete();
                    worker.removeEventListener("message", messageHandler);
                }
                else {
                    if (event.data.payload) {
                        observer.next(common_1.deserialize(event.data.payload));
                    }
                    if (event.data.complete) {
                        observer.complete();
                        worker.removeEventListener("message", messageHandler);
                    }
                }
            }
            else if (isJobErrorMessage(event.data)) {
                const error = common_1.deserialize(event.data.error);
                if (asyncType === "promise" || !asyncType) {
                    observer.error(error);
                }
                else {
                    observer.error(error);
                }
                worker.removeEventListener("message", messageHandler);
            }
        });
        worker.addEventListener("message", messageHandler);
        return () => {
            if (asyncType === "observable" || !asyncType) {
                const cancelMessage = {
                    type: messages_1.MasterMessageType.cancel,
                    uid: jobUID
                };
                worker.postMessage(cancelMessage);
            }
            worker.removeEventListener("message", messageHandler);
        };
    });
}
function prepareArguments(rawArgs) {
    if (rawArgs.length === 0) {
        // Exit early if possible
        return {
            args: [],
            transferables: []
        };
    }
    const args = [];
    const transferables = [];
    for (const arg of rawArgs) {
        if (transferable_1.isTransferDescriptor(arg)) {
            args.push(common_1.serialize(arg.send));
            transferables.push(...arg.transferables);
        }
        else {
            args.push(common_1.serialize(arg));
        }
    }
    return {
        args,
        transferables: transferables.length === 0 ? transferables : dedupe(transferables)
    };
}
function createProxyFunction(worker, method) {
    return ((...rawArgs) => {
        const uid = nextJobUID++;
        const { args, transferables } = prepareArguments(rawArgs);
        const runMessage = {
            type: messages_1.MasterMessageType.run,
            uid,
            method,
            args
        };
        debugMessages("Sending command to run function to worker:", runMessage);
        try {
            worker.postMessage(runMessage, transferables);
        }
        catch (error) {
            return observable_promise_1.ObservablePromise.from(Promise.reject(error));
        }
        return observable_promise_1.ObservablePromise.from(observable_fns_1.multicast(createObservableForJob(worker, uid)));
    });
}
exports.createProxyFunction = createProxyFunction;
function createProxyModule(worker, methodNames) {
    const proxy = {};
    for (const methodName of methodNames) {
        proxy[methodName] = createProxyFunction(worker, methodName);
    }
    return proxy;
}
exports.createProxyModule = createProxyModule;
