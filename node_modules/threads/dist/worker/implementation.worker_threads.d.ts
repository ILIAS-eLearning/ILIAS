declare function testImplementation(): void;
declare const _default: {
    isWorkerRuntime: () => boolean;
    postMessageToMaster: (message: any, transferList?: Transferable[] | undefined) => void;
    subscribeToMasterMessages: (onMessage: (data: any) => void) => () => void;
    testImplementation: typeof testImplementation;
};
export default _default;
