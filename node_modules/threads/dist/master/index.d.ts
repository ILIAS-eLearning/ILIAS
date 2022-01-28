import type { BlobWorker as BlobWorkerClass } from "../types/master";
import { Worker as WorkerType } from "../types/master";
import { isWorkerRuntime } from "./implementation";
export { FunctionThread, ModuleThread } from "../types/master";
export { Pool } from "./pool";
export { spawn } from "./spawn";
export { Thread } from "./thread";
export { isWorkerRuntime };
export declare type BlobWorker = typeof BlobWorkerClass;
export declare type Worker = WorkerType;
/** Separate class to spawn workers from source code blobs or strings. */
export declare const BlobWorker: typeof BlobWorkerClass;
/** Worker implementation. Either web worker or a node.js Worker class. */
export declare const Worker: typeof import("../types/master").WorkerImplementation;
