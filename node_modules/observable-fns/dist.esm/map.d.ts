import Observable, { ObservableLike } from "./observable";
/**
 * Maps the values emitted by another observable to different values.
 * To be applied to an input observable using `pipe()`.
 */
declare function map<In, Out>(mapper: (input: In) => Promise<Out> | Out): (observable: ObservableLike<In>) => Observable<Out>;
export default map;
