import WorkerThreads from "../worker_threads";
function assertMessagePort(port) {
    if (!port) {
        throw Error("Invariant violation: MessagePort to parent is not available.");
    }
    return port;
}
const isWorkerRuntime = function isWorkerRuntime() {
    return !WorkerThreads().isMainThread;
};
const postMessageToMaster = function postMessageToMaster(data, transferList) {
    assertMessagePort(WorkerThreads().parentPort).postMessage(data, transferList);
};
const subscribeToMasterMessages = function subscribeToMasterMessages(onMessage) {
    const parentPort = WorkerThreads().parentPort;
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
    WorkerThreads();
}
export default {
    isWorkerRuntime,
    postMessageToMaster,
    subscribeToMasterMessages,
    testImplementation
};
