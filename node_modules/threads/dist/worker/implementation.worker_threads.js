"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const worker_threads_1 = __importDefault(require("../worker_threads"));
function assertMessagePort(port) {
    if (!port) {
        throw Error("Invariant violation: MessagePort to parent is not available.");
    }
    return port;
}
const isWorkerRuntime = function isWorkerRuntime() {
    return !worker_threads_1.default().isMainThread;
};
const postMessageToMaster = function postMessageToMaster(data, transferList) {
    assertMessagePort(worker_threads_1.default().parentPort).postMessage(data, transferList);
};
const subscribeToMasterMessages = function subscribeToMasterMessages(onMessage) {
    const parentPort = worker_threads_1.default().parentPort;
    if (!parentPort) {
        throw Error("Invariant violation: MessagePort to parent is not available.");
    }
    const messageHandler = (message) => {
        onMessage(message);
    };
    const unsubscribe = () => {
        assertMessagePort(parentPort).off("message", messageHandler);
    };
    assertMessagePort(parentPort).on("message", messageHandler);
    return unsubscribe;
};
function testImplementation() {
    // Will throw if `worker_threads` are not available
    worker_threads_1.default();
}
exports.default = {
    isWorkerRuntime,
    postMessageToMaster,
    subscribeToMasterMessages,
    testImplementation
};
