/**
 * Creates a new promise and exposes its resolver function.
 * Use with care!
 */
export declare function createPromiseWithResolver<T>(): [Promise<T>, (result: T) => void];
