import Observable, { ObservableLike } from "./observable";
/**
 * Maps the values emitted by another observable. In contrast to `map()`
 * the `mapper` function returns an array of values that will be emitted
 * separately.
 * Use `flatMap()` to map input values to zero, one or multiple output
 * values. To be applied to an input observable using `pipe()`.
 */
declare function flatMap<In, Out>(mapper: (input: In) => Promise<Out[]> | AsyncIterableIterator<Out> | IterableIterator<Out> | Out[]): (observable: ObservableLike<In>) => Observable<Out>;
export default flatMap;
