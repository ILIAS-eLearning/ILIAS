interface SubscriptionObserver<T> {
    next(value: T): void;
    error(error: any): void;
    complete(): void;
}
export declare class AsyncSerialScheduler<T> {
    private _baseObserver;
    private _pendingPromises;
    constructor(observer: SubscriptionObserver<T>);
    complete(): void;
    error(error: any): void;
    schedule(task: (next: (value: T) => void) => Promise<void>): void;
}
export {};
