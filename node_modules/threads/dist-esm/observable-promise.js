import { Observable } from "observable-fns";
const doNothing = () => undefined;
const returnInput = (input) => input;
const runDeferred = (fn) => Promise.resolve().then(fn);
function fail(error) {
    throw error;
}
function isThenable(thing) {
    return thing && typeof thing.then === "function";
}
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
export class ObservablePromise extends Observable {
    constructor(init) {
        super((originalObserver) => {
            // tslint:disable-next-line no-this-assignment
            const self = this;
            const observer = Object.assign(Object.assign({}, originalObserver), { complete() {
                    originalObserver.complete();
                    self.onCompletion();
                }, error(error) {
                    originalObserver.error(error);
                    self.onError(error);
                },
                next(value) {
                    originalObserver.next(value);
                    self.onNext(value);
                } });
            try {
                this.initHasRun = true;
                return init(observer);
            }
            catch (error) {
                observer.error(error);
            }
        });
        this.initHasRun = false;
        this.fulfillmentCallbacks = [];
        this.rejectionCallbacks = [];
        this.firstValueSet = false;
        this.state = "pending";
    }
    onNext(value) {
        if (!this.firstValueSet) {
            this.firstValue = value;
            this.firstValueSet = true;
        }
    }
    onError(error) {
        this.state = "rejected";
        this.rejection = error;
        for (const onRejected of this.rejectionCallbacks) {
            // Promisifying the call to turn errors into unhandled promise rejections
            // instead of them failing sync and cancelling the iteration
            runDeferred(() => onRejected(error));
        }
    }
    onCompletion() {
        this.state = "fulfilled";
        for (const onFulfilled of this.fulfillmentCallbacks) {
            // Promisifying the call to turn errors into unhandled promise rejections
            // instead of them failing sync and cancelling the iteration
            runDeferred(() => onFulfilled(this.firstValue));
        }
    }
    then(onFulfilledRaw, onRejectedRaw) {
        const onFulfilled = onFulfilledRaw || returnInput;
        const onRejected = onRejectedRaw || fail;
        let onRejectedCalled = false;
        return new Promise((resolve, reject) => {
            const rejectionCallback = (error) => {
                if (onRejectedCalled)
                    return;
                onRejectedCalled = true;
                try {
                    resolve(onRejected(error));
                }
                catch (anotherError) {
                    reject(anotherError);
                }
            };
            const fulfillmentCallback = (value) => {
                try {
                    resolve(onFulfilled(value));
                }
                catch (error) {
                    rejectionCallback(error);
                }
            };
            if (!this.initHasRun) {
                this.subscribe({ error: rejectionCallback });
            }
            if (this.state === "fulfilled") {
                return resolve(onFulfilled(this.firstValue));
            }
            if (this.state === "rejected") {
                onRejectedCalled = true;
                return resolve(onRejected(this.rejection));
            }
            this.fulfillmentCallbacks.push(fulfillmentCallback);
            this.rejectionCallbacks.push(rejectionCallback);
        });
    }
    catch(onRejected) {
        return this.then(undefined, onRejected);
    }
    finally(onCompleted) {
        const handler = onCompleted || doNothing;
        return this.then((value) => {
            handler();
            return value;
        }, () => handler());
    }
    static from(thing) {
        if (isThenable(thing)) {
            return new ObservablePromise(observer => {
                const onFulfilled = (value) => {
                    observer.next(value);
                    observer.complete();
                };
                const onRejected = (error) => {
                    observer.error(error);
                };
                thing.then(onFulfilled, onRejected);
            });
        }
        else {
            return super.from(thing);
        }
    }
}
