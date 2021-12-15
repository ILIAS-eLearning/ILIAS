declare type MessagePort = any;
interface WorkerThreadsModule {
    MessagePort: typeof MessagePort;
    isMainThread: boolean;
    parentPort: MessagePort;
}
export default function getImplementation(): WorkerThreadsModule;
export {};
