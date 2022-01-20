import { Observable, ObservableLike } from "observable-fns";
export { Observable };
declare const $observers: unique symbol;
/**
 * Observable subject. Implements the Observable interface, but also exposes
 * the `next()`, `error()`, `complete()` methods to initiate observable
 * updates "from the outside".
 *
 * Use `Observable.from(subject)` to derive an observable that proxies all
 * values, errors and the completion raised on this subject, but does not
 * expose the `next()`, `error()`, `complete()` methods.
 */
export declare class Subject<T> extends Observable<T> implements ObservableLike<T> {
    private [$observers];
    constructor();
    complete(): void;
    error(error: any): void;
    next(value: T): void;
}
