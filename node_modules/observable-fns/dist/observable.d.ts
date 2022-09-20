/**
 * Based on <https://raw.githubusercontent.com/zenparsing/zen-observable/master/src/Observable.js>
 * At commit: f63849a8c60af5d514efc8e9d6138d8273c49ad6
 */
import "./symbols";
export declare type UnsubscribeFn = () => void;
export declare type Subscriber<T> = (observer: SubscriptionObserver<T>) => (UnsubscribeFn | Subscription<any> | void);
export interface ObservableLike<T> {
    subscribe: (observer: Observer<T>) => (UnsubscribeFn | {
        unsubscribe: UnsubscribeFn;
    } | void);
    [Symbol.observable](): Observable<T> | ObservableLike<T>;
}
export interface Observer<T> {
    start?(subscription: Subscription<T>): any;
    next?(value: T): void;
    error?(errorValue: any): void;
    complete?(): void;
}
export declare class Subscription<T> {
    _cleanup?: ReturnType<Subscriber<T>>;
    _observer?: Observer<T>;
    _queue?: Array<{
        type: "next" | "error" | "complete";
        value: any;
    }>;
    _state: "initializing" | "ready" | "buffering" | "running" | "closed";
    constructor(observer: Observer<T>, subscriber: Subscriber<T>);
    get closed(): boolean;
    unsubscribe(): void;
}
export declare class SubscriptionObserver<T> {
    private _subscription;
    constructor(subscription: Subscription<T>);
    get closed(): boolean;
    next(value: T): void;
    error(value: any): void;
    complete(): void;
}
/**
 * The basic Observable class. This primitive is used to wrap asynchronous
 * data streams in a common standardized data type that is interoperable
 * between libraries and can be composed to represent more complex processes.
 */
export declare class Observable<T> {
    [Symbol.observable]: () => this;
    private _subscriber;
    constructor(subscriber: Subscriber<T>);
    subscribe(onNext: (value: T) => void, onError?: (error: any) => void, onComplete?: () => void): Subscription<T>;
    subscribe(observer: Observer<T>): Subscription<T>;
    pipe<Out extends ObservableLike<any>>(first: (input: ObservableLike<T>) => Out): Out;
    pipe<Out extends ObservableLike<any>, Inter1 extends ObservableLike<any>>(first: (input: ObservableLike<T>) => Inter1, second: (input: Inter1) => Out): Out;
    pipe<Out extends ObservableLike<any>, Inter1 extends ObservableLike<any>, Inter2 extends ObservableLike<any>>(first: (input: ObservableLike<T>) => Inter1, second: (input: Inter1) => Inter2, third: (input: Inter2) => Out): Out;
    pipe<Out extends ObservableLike<any>, Inter1 extends ObservableLike<any>, Inter2 extends ObservableLike<any>, Inter3 extends ObservableLike<any>>(first: (input: ObservableLike<T>) => Inter1, second: (input: Inter1) => Inter2, third: (input: Inter2) => Inter3, fourth: (input: Inter3) => Out): Out;
    pipe<Out extends ObservableLike<any>, Inter1 extends ObservableLike<any>, Inter2 extends ObservableLike<any>, Inter3 extends ObservableLike<any>, Inter4 extends ObservableLike<any>>(first: (input: ObservableLike<T>) => Inter1, second: (input: Inter1) => Inter2, third: (input: Inter2) => Inter3, fourth: (input: Inter3) => Inter4, fifth: (input: Inter4) => Out): Out;
    pipe<Out extends ObservableLike<any>, Inter1 extends ObservableLike<any>, Inter2 extends ObservableLike<any>, Inter3 extends ObservableLike<any>, Inter4 extends ObservableLike<any>, Inter5 extends ObservableLike<any>>(first: (input: ObservableLike<T>) => Inter1, second: (input: Inter1) => Inter2, third: (input: Inter2) => Inter3, fourth: (input: Inter3) => Inter4, fifth: (input: Inter4) => Inter5, sixth: (input: Inter5) => Out): Out;
    pipe<Out extends ObservableLike<T>>(...mappers: Array<(input: Out) => Out>): Out;
    tap(onNext: (value: T) => void, onError?: (error: any) => void, onComplete?: () => void): Observable<T>;
    tap(observer: Observer<T>): Observable<T>;
    forEach(fn: (value: T, done: UnsubscribeFn) => void): Promise<unknown>;
    map<R>(fn: (value: T) => R): Observable<R>;
    filter<R extends T>(fn: (value: T) => boolean): Observable<R>;
    reduce<R>(fn: (accumulated: R | T, value: T) => R): Observable<R | T>;
    reduce<R>(fn: (accumulated: R, value: T) => R, seed: R): Observable<R>;
    concat<R>(...sources: Array<Observable<R>>): Observable<T | R>;
    flatMap<R>(fn: (value: T) => ObservableLike<R>): Observable<R>;
    static from<I>(x: Observable<I> | ObservableLike<I> | ArrayLike<I>): Observable<I>;
    static of<I>(...items: I[]): Observable<I>;
}
export default Observable;
