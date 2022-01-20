import Observable from "./observable";
/**
 * A subject is a "hot" observable (see `multicast`) that has its observer
 * methods (`.next(value)`, `.error(error)`, `.complete()`) exposed.
 *
 * Be careful, though! With great power comes great responsibility. Only use
 * the `Subject` when you really need to trigger updates "from the outside" and
 * try to keep the code that can access it to a minimum. Return
 * `Observable.from(mySubject)` to not allow other code to mutate.
 */
declare class MulticastSubject<T> extends Observable<T> {
    private _observers;
    constructor();
    next(value: T): void;
    error(error: any): void;
    complete(): void;
}
export default MulticastSubject;
