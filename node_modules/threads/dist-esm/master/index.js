import { getWorkerImplementation, isWorkerRuntime } from "./implementation";
export { Pool } from "./pool";
export { spawn } from "./spawn";
export { Thread } from "./thread";
export { isWorkerRuntime };
/** Separate class to spawn workers from source code blobs or strings. */
export const BlobWorker = getWorkerImplementation().blob;
/** Worker implementation. Either web worker or a node.js Worker class. */
export const Worker = getWorkerImplementation().default;
