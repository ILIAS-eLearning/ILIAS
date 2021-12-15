"use strict";
/// <reference lib="dom" />
// tslint:disable no-shadowed-variable
Object.defineProperty(exports, "__esModule", { value: true });
if (typeof self === "undefined") {
    global.self = global;
}
const isWorkerRuntime = function isWorkerRuntime() {
    return typeof self !== "undefined" && self.postMessage ? true : false;
};
const postMessageToMaster = function postMessageToMaster(data) {
    // TODO: Warn that Transferables are not supported on first attempt to use feature
    self.postMessage(data);
};
let muxingHandlerSetUp = false;
const messageHandlers = new Set();
const subscribeToMasterMessages = function subscribeToMasterMessages(onMessage) {
    if (!muxingHandlerSetUp) {
        // We have one multiplexing message handler as tiny-worker's
        // addEventListener() only allows you to set a single message handler
        self.addEventListener("message", ((event) => {
            messageHandlers.forEach(handler => handler(event.data));
        }));
        muxingHandlerSetUp = true;
    }
    messageHandlers.add(onMessage);
    const unsubscribe = () => messageHandlers.delete(onMessage);
    return unsubscribe;
};
exports.default = {
    isWorkerRuntime,
    postMessageToMaster,
    subscribeToMasterMessages
};
