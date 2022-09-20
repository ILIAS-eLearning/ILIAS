import { FunctionThread, ModuleThread, StripAsync, Worker as WorkerType } from "../types/master";
import { WorkerFunction, WorkerModule } from "../types/worker";
declare type ArbitraryWorkerInterface = WorkerFunction & WorkerModule<string> & {
    somekeythatisneverusedinproductioncode123: "magicmarker123";
};
declare type ArbitraryThreadType = FunctionThread<any, any> & ModuleThread<any>;
declare type ExposedToThreadType<Exposed extends WorkerFunction | WorkerModule<any>> = Exposed extends ArbitraryWorkerInterface ? ArbitraryThreadType : Exposed extends WorkerFunction ? FunctionThread<Parameters<Exposed>, StripAsync<ReturnType<Exposed>>> : Exposed extends WorkerModule<any> ? ModuleThread<Exposed> : never;
/**
 * Spawn a new thread. Takes a fresh worker instance, wraps it in a thin
 * abstraction layer to provide the transparent API and verifies that
 * the worker has initialized successfully.
 *
 * @param worker Instance of `Worker`. Either a web worker, `worker_threads` worker or `tiny-worker` worker.
 * @param [options]
 * @param [options.timeout] Init message timeout. Default: 10000 or set by environment variable.
 */
export declare function spawn<Exposed extends WorkerFunction | WorkerModule<any> = ArbitraryWorkerInterface>(worker: WorkerType, options?: {
    timeout?: number;
}): Promise<ExposedToThreadType<Exposed>>;
export {};
