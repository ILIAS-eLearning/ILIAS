"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Subject = exports.Observable = void 0;
const observable_fns_1 = require("observable-fns");
Object.defineProperty(exports, "Observable", { enumerable: true, get: function () { return observable_fns_1.Observable; } });
const $observers = Symbol("observers");
/**
 * Observable subject. Implements the Observable interface, but also exposes
 * the `next()`, `error()`, `complete()` methods to initiate observable
 * updates "from the outside".
 *
 * Use `Observable.from(subject)` to derive an observable that proxies all
 * values, errors and the completion raised on this subject, but does not
 * expose the `next()`, `error()`, `complete()` methods.
 */
class Subject extends observable_fns_1.Observable {
    constructor() {
        super(observer => {
            this[$observers] = [
                ...(this[$observers] || []),
                observer
            ];
            const unsubscribe = () => {
                this[$observers] = this[$observers].filter(someObserver => someObserver !== observer);
            };
            return unsubscribe;
        });
        this[$observers] = [];
    }
    complete() {
        this[$observers].forEach(observer => observer.complete());
    }
    error(error) {
        this[$observers].forEach(observer => observer.error(error));
    }
    next(value) {
        this[$observers].forEach(observer => observer.next(value));
    }
}
exports.Subject = Subject;
