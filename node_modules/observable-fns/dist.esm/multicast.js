import Observable from "./observable";
import Subject from "./subject";
import unsubscribe from "./unsubscribe";
// TODO: Subject already creates additional observables "under the hood",
//       now we introduce even more. A true native MulticastObservable
//       would be preferable.
/**
 * Takes a "cold" observable and returns a wrapping "hot" observable that
 * proxies the input observable's values and errors.
 *
 * An observable is called "cold" when its initialization function is run
 * for each new subscriber. This is how observable-fns's `Observable`
 * implementation works.
 *
 * A hot observable is an observable where new subscribers subscribe to
 * the upcoming values of an already-initialiazed observable.
 *
 * The multicast observable will lazily subscribe to the source observable
 * once it has its first own subscriber and will unsubscribe from the
 * source observable when its last own subscriber unsubscribed.
 */
function multicast(coldObservable) {
    const subject = new Subject();
    let sourceSubscription;
    let subscriberCount = 0;
    return new Observable(observer => {
        // Init source subscription lazily
        if (!sourceSubscription) {
            sourceSubscription = coldObservable.subscribe(subject);
        }
        // Pipe all events from `subject` into this observable
        const subscription = subject.subscribe(observer);
        subscriberCount++;
        return () => {
            subscriberCount--;
            subscription.unsubscribe();
            // Close source subscription once last subscriber has unsubscribed
            if (subscriberCount === 0) {
                unsubscribe(sourceSubscription);
                sourceSubscription = undefined;
            }
        };
    });
}
export default multicast;
