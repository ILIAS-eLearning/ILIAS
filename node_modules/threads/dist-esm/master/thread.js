import { $errors, $events, $terminate } from "../symbols";
function fail(message) {
    throw Error(message);
}
/** Thread utility functions. Use them to manage or inspect a `spawn()`-ed thread. */
export const Thread = {
    /** Return an observable that can be used to subscribe to all errors happening in the thread. */
    errors(thread) {
        return thread[$errors] || fail("Error observable not found. Make sure to pass a thread instance as returned by the spawn() promise.");
    },
    /** Return an observable that can be used to subscribe to internal events happening in the thread. Useful for debugging. */
    events(thread) {
        return thread[$events] || fail("Events observable not found. Make sure to pass a thread instance as returned by the spawn() promise.");
    },
    /** Terminate a thread. Remember to terminate every thread when you are done using it. */
    terminate(thread) {
        return thread[$terminate]();
    }
};
