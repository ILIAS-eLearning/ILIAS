/// <reference lib="dom" />
declare const _default: {
    isWorkerRuntime: () => boolean;
    postMessageToMaster: (message: any, transferList?: Transferable[] | undefined) => void;
    subscribeToMasterMessages: (onMessage: (data: any) => void) => () => void;
};
export default _default;
