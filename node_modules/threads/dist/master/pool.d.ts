import { Observable } from "observable-fns";
import { PoolEvent, PoolEventType, QueuedTask, TaskRunFunction } from "./pool-types";
import { Thread } from "./thread";
export { PoolEvent, PoolEventType, QueuedTask, Thread };
export declare namespace Pool {
    type Event<ThreadType extends Thread = any> = PoolEvent<ThreadType>;
    type EventType = PoolEventType;
}
/**
 * Thread pool managing a set of worker threads.
 * Use it to queue tasks that are run on those threads with limited
 * concurrency.
 */
export interface Pool<ThreadType extends Thread> {
    /**
     * Returns a promise that resolves once the task queue is emptied.
     * Promise will be rejected if any task fails.
     *
     * @param allowResolvingImmediately Set to `true` to resolve immediately if task queue is currently empty.
     */
    completed(allowResolvingImmediately?: boolean): Promise<any>;
    /**
     * Returns a promise that resolves once the task queue is emptied.
     * Failing tasks will not cause the promise to be rejected.
     *
     * @param allowResolvingImmediately Set to `true` to resolve immediately if task queue is currently empty.
     */
    settled(allowResolvingImmediately?: boolean): Promise<Error[]>;
    /**
     * Returns an observable that yields pool events.
     */
    events(): Observable<PoolEvent<ThreadType>>;
    /**
     * Queue a task and return a promise that resolves once the task has been dequeued,
     * started and finished.
     *
     * @param task An async function that takes a thread instance and invokes it.
     */
    queue<Return>(task: TaskRunFunction<ThreadType, Return>): QueuedTask<ThreadType, Return>;
    /**
     * Terminate all pool threads.
     *
     * @param force Set to `true` to kill the thread even if it cannot be stopped gracefully.
     */
    terminate(force?: boolean): Promise<void>;
}
export interface PoolOptions {
    /** Maximum no. of tasks to run on one worker thread at a time. Defaults to one. */
    concurrency?: number;
    /** Maximum no. of jobs to be queued for execution before throwing an error. */
    maxQueuedJobs?: number;
    /** Gives that pool a name to be used for debug logging, letting you distinguish between log output of different pools. */
    name?: string;
    /** No. of worker threads to spawn and to be managed by the pool. */
    size?: number;
}
declare class WorkerPool<ThreadType extends Thread> implements Pool<ThreadType> {
    static EventType: typeof PoolEventType;
    private readonly debug;
    private readonly eventObservable;
    private readonly options;
    private readonly workers;
    private readonly eventSubject;
    private initErrors;
    private isClosing;
    private nextTaskID;
    private taskQueue;
    constructor(spawnWorker: () => Promise<ThreadType>, optionsOrSize?: number | PoolOptions);
    private findIdlingWorker;
    private runPoolTask;
    private run;
    private scheduleWork;
    private taskCompletion;
    settled(allowResolvingImmediately?: boolean): Promise<Error[]>;
    completed(allowResolvingImmediately?: boolean): Promise<void>;
    events(): Observable<PoolEvent<ThreadType>>;
    queue(taskFunction: TaskRunFunction<ThreadType, any>): QueuedTask<ThreadType, any>;
    terminate(force?: boolean): Promise<void>;
}
/**
 * Thread pool constructor. Creates a new pool and spawns its worker threads.
 */
declare function PoolConstructor<ThreadType extends Thread>(spawnWorker: () => Promise<ThreadType>, optionsOrSize?: number | PoolOptions): WorkerPool<ThreadType>;
/**
 * Thread pool constructor. Creates a new pool and spawns its worker threads.
 */
export declare const Pool: typeof PoolConstructor & {
    EventType: typeof PoolEventType;
};
