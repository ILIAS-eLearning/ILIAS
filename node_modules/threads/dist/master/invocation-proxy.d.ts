import { ModuleMethods, ModuleProxy, ProxyableFunction, Worker as WorkerType } from "../types/master";
export declare function createProxyFunction<Args extends any[], ReturnType>(worker: WorkerType, method?: string): ProxyableFunction<Args, ReturnType>;
export declare function createProxyModule<Methods extends ModuleMethods>(worker: WorkerType, methodNames: string[]): ModuleProxy<Methods>;
