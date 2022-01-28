export interface SerializedError {
    __error_marker: "$$error";
    message: string;
    name: string;
    stack?: string;
}
export declare enum MasterMessageType {
    cancel = "cancel",
    run = "run"
}
export declare type MasterJobCancelMessage = {
    type: MasterMessageType.cancel;
    uid: number;
};
export declare type MasterJobRunMessage = {
    type: MasterMessageType.run;
    uid: number;
    method?: string;
    args: any[];
};
export declare type MasterSentMessage = MasterJobCancelMessage | MasterJobRunMessage;
export declare enum WorkerMessageType {
    error = "error",
    init = "init",
    result = "result",
    running = "running",
    uncaughtError = "uncaughtError"
}
export declare type WorkerUncaughtErrorMessage = {
    type: WorkerMessageType.uncaughtError;
    error: {
        message: string;
        name: string;
        stack?: string;
    };
};
export declare type WorkerInitMessage = {
    type: WorkerMessageType.init;
    exposed: {
        type: "function";
    } | {
        type: "module";
        methods: string[];
    };
};
export declare type WorkerJobErrorMessage = {
    type: WorkerMessageType.error;
    uid: number;
    error: SerializedError;
};
export declare type WorkerJobResultMessage = {
    type: WorkerMessageType.result;
    uid: number;
    complete?: true;
    payload?: any;
};
export declare type WorkerJobStartMessage = {
    type: WorkerMessageType.running;
    uid: number;
    resultType: "observable" | "promise";
};
export declare type WorkerSentMessage = WorkerInitMessage | WorkerJobErrorMessage | WorkerJobResultMessage | WorkerJobStartMessage | WorkerUncaughtErrorMessage;
