import { Observable, ObservableLike, SubscriptionObserver } from "observable-fns";
declare type Initializer<T> = (observer: SubscriptionObserver<T>) => UnsubscribeFn | void;
declare type Thenable<T> = {
    then: (onFulfilled?: (value: T) => any, onRejected?: (error: any) => any) => any;
};
declare type UnsubscribeFn = () => void;
/**
 * Creates a hybrid, combining the APIs of an Observable and a Promise.
 *
 * It is used to proxy async process states when we are initially not sure
 * if that async process will yield values once (-> Promise) or multiple
 * times (-> Observable).
 *
 * Note that the observable promise inherits some of the observable's characteristics:
 * The `init` function will be called *once for every time anyone subscribes to it*.
 *
 * If this is undesired, derive a hot observable from it using `makeHot()` and
 * subscribe to that.
 */
export declare class ObservablePromise<T> extends Observable<T> implements Promise<T> {
    private initHasRun;
    private fulfillmentCallbacks;
    private rejectionCallbacks;
    private firstValue;
    private firstValueSet;
    private rejection;
    private state;
    readonly [Symbol.toStringTag]: "[object ObservablePromise]";
    constructor(init: Initializer<T>);
    private onNext;
    private onError;
    private onCompletion;
    then<TResult1 = T, TResult2 = never>(onFulfilledRaw?: ((value: T) => TResult1 | PromiseLike<TResult1>) | undefined | null, onRejectedRaw?: ((reason: any) => TResult2 | PromiseLike<TResult2>) | undefined | null): Promise<TResult1 | TResult2>;
    catch<Result = never>(onRejected: ((error: Error) => Promise<Result> | Result) | null | undefined): Promise<Result>;
    finally(onCompleted?: (() => void) | null | undefined): Promise<T>;
    static from<T>(thing: Observable<T> | ObservableLike<T> | ArrayLike<T> | Thenable<T>): ObservablePromise<T>;
}
export {};
