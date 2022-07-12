import Observable, { ObservableLike } from "./observable";
/**
 * Applies an accumulator function over the source Observable, and returns
 * each intermediate result. It is basically the same as `.reduce()`, but
 * it continuously yields accumulated values, not just after the input
 * completed.
 * If no accumulator seed is supplied then the first input value will be used
 * as a seed. To be applied to an input observable using `pipe()`.
 */
declare function scan<T>(accumulator: (accumulated: T, value: T, index: number) => Promise<T> | T): (observable: ObservableLike<T>) => Observable<T>;
declare function scan<In, Out>(accumulator: (accumulated: Out, value: In, index: number) => Promise<Out> | Out, seed?: Out): (observable: ObservableLike<In>) => Observable<Out>;
export default scan;
