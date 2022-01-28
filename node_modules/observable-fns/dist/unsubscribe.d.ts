declare type UnsubscribeFn = () => void;
/**
 * Unsubscribe from a subscription returned by something that looks like an observable,
 * but is not necessarily our observable implementation.
 */
declare function unsubscribe(subscription: (UnsubscribeFn | {
    unsubscribe: UnsubscribeFn;
} | void)): void;
export default unsubscribe;
