import { Observable, ObservableLike } from "./observable";
/**
 * Creates an observable that emits the values emitted by any of its input
 * observables. It completes once all input observables completed.
 */
declare function merge<A>(first: ObservableLike<A>): Observable<A>;
declare function merge<A, B>(first: ObservableLike<A>, second: ObservableLike<B>): Observable<A | B>;
declare function merge<A, B, C>(first: ObservableLike<A>, second: ObservableLike<B>, third: ObservableLike<C>): Observable<A | B | C>;
declare function merge<A, B, C, D>(first: ObservableLike<A>, second: ObservableLike<B>, third: ObservableLike<C>, fourth: ObservableLike<D>): Observable<A | B | C | D>;
declare function merge<A, B, C, D, E>(first: ObservableLike<A>, second: ObservableLike<B>, third: ObservableLike<C>, fourth: ObservableLike<D>, fifth: ObservableLike<E>): Observable<A | B | C | D | E>;
export default merge;
