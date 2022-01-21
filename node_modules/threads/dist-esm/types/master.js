/// <reference lib="dom" />
// tslint:disable max-classes-per-file
import { $errors, $events, $terminate, $worker } from "../symbols";
/** Event as emitted by worker thread. Subscribe to using `Thread.events(thread)`. */
export var WorkerEventType;
(function (WorkerEventType) {
    WorkerEventType["internalError"] = "internalError";
    WorkerEventType["message"] = "message";
    WorkerEventType["termination"] = "termination";
})(WorkerEventType || (WorkerEventType = {}));
