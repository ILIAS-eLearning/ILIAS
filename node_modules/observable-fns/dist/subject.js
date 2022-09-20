"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const observable_1 = require("./observable");
// TODO: This observer iteration approach looks inelegant and expensive
// Idea: Come up with super class for Subscription that contains the
//       notify*, ... methods and use it here
/**
 * A subject is a "hot" observable (see `multicast`) that has its observer
 * methods (`.next(value)`, `.error(error)`, `.complete()`) exposed.
 *
 * Be careful, though! With great power comes great responsibility. Only use
 * the `Subject` when you really need to trigger updates "from the outside" and
 * try to keep the code that can access it to a minimum. Return
 * `Observable.from(mySubject)` to not allow other code to mutate.
 */
class MulticastSubject extends observable_1.default {
    constructor() {
        super(observer => {
            this._observers.add(observer);
            return () => this._observers.delete(observer);
        });
        this._observers = new Set();
    }
    next(value) {
        for (const observer of this._observers) {
            observer.next(value);
        }
    }
    error(error) {
        for (const observer of this._observers) {
            observer.error(error);
        }
    }
    complete() {
        for (const observer of this._observers) {
            observer.complete();
        }
    }
}
exports.default = MulticastSubject;
