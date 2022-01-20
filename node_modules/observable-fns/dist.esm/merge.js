import { Observable } from "./observable";
import unsubscribe from "./unsubscribe";
function merge(...observables) {
    if (observables.length === 0) {
        return Observable.from([]);
    }
    return new Observable(observer => {
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
            subscriptions.forEach(subscription => unsubscribe(subscription));
        };
        return unsubscribeAll;
    });
}
export default merge;
