import * as BrowserImplementation from "./implementation.browser";
/** Default size of pools. Depending on the platform the value might vary from device to device. */
export declare const defaultPoolSize: number;
export declare const getWorkerImplementation: typeof BrowserImplementation.getWorkerImplementation;
/** Returns `true` if this code is currently running in a worker. */
export declare const isWorkerRuntime: typeof BrowserImplementation.isWorkerRuntime;
