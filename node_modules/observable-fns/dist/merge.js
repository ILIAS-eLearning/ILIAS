"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const observable_1 = require("./observable");
const unsubscribe_1 = require("./unsubscribe");
function merge(...observables) {
    if (observables.length === 0) {
        return observable_1.Observable.from([]);
    }
    return new observable_1.Observable(observer => {
        let completed = 0;
        const subscriptions = observables.map(input => {
            return input.subscribe({
                error(error) {
                    observer.error(error);
                    unsubscribeAll();
                },
                next(value) {
                    observer.next(value);
                },
                complete() {
                    if (++completed === observables.length) {
                        observer.complete();
                        unsubscribeAll();
                    }
                }
            });
        });
        const unsubscribeAll = () => {
            subscriptions.forEach(subscription => unsubscribe_1.default(subscription));
        };
        return unsubscribeAll;
    });
}
exports.default = merge;
