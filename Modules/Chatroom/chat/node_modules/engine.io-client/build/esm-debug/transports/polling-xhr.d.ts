import { DefaultEventsMap, Emitter } from "@socket.io/component-emitter";
import { Polling } from "./polling.js";
export declare class XHR extends Polling {
    private readonly xd;
    private readonly xs;
    private pollXhr;
    /**
     * XHR Polling constructor.
     *
     * @param {Object} opts
     * @api public
     */
    constructor(opts: any);
    /**
     * Creates a request.
     *
     * @param {String} method
     * @api private
     */
    request(opts?: {}): Request;
    /**
     * Sends data.
     *
     * @param {String} data to send.
     * @param {Function} called upon flush.
     * @api private
     */
    doWrite(data: any, fn: any): void;
    /**
     * Starts a poll cycle.
     *
     * @api private
     */
    doPoll(): void;
}
export declare class Request extends Emitter<DefaultEventsMap, DefaultEventsMap> {
    private readonly opts;
    private readonly method;
    private readonly uri;
    private readonly async;
    private readonly data;
    private xhr;
    private setTimeoutFn;
    private index;
    static requestsCount: number;
    static requests: {};
    /**
     * Request constructor
     *
     * @param {Object} options
     * @api public
     */
    constructor(uri: any, opts: any);
    /**
     * Creates the XHR object and sends the request.
     *
     * @api private
     */
    create(): void;
    /**
     * Called upon successful response.
     *
     * @api private
     */
    onSuccess(): void;
    /**
     * Called if we have data.
     *
     * @api private
     */
    onData(data: any): void;
    /**
     * Called upon error.
     *
     * @api private
     */
    onError(err: any): void;
    /**
     * Cleans up house.
     *
     * @api private
     */
    cleanup(fromError?: any): void;
    /**
     * Called upon load.
     *
     * @api private
     */
    onLoad(): void;
    /**
     * Aborts the request.
     *
     * @api public
     */
    abort(): void;
}
