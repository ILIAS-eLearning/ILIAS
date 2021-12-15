import Observable, { ObservableLike } from "./observable";
/**
 * Filters the values emitted by another observable.
 * To be applied to an input observable using `pipe()`.
 */
declare function filter<Out, In extends Out>(test: (input: In) => Promise<boolean> | boolean): (observable: ObservableLike<In>) => Observable<Out>;
export default filter;
