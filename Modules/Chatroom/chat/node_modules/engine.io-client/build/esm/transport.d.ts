import { DefaultEventsMap, Emitter } from "@socket.io/component-emitter";
import { SocketOptions } from "./socket.js";
export declare abstract class Transport extends Emitter<DefaultEventsMap, DefaultEventsMap> {
    protected opts: SocketOptions;
    protected supportsBinary: boolean;
    protected query: object;
    protected readyState: string;
    protected writable: boolean;
    protected socket: any;
    protected setTimeoutFn: typeof setTimeout;
    /**
     * Transport abstract constructor.
     *
     * @param {Object} options.
     * @api private
     */
    constructor(opts: any);
    /**
     * Emits an error.
     *
     * @param {String} str
     * @return {Transport} for chaining
     * @api protected
     */
    protected onError(msg: any, desc: any): this;
    /**
     * Opens the transport.
     *
     * @api public
     */
    private open;
    /**
     * Closes the transport.
     *
     * @api public
     */
    close(): this;
    /**
     * Sends multiple packets.
     *
     * @param {Array} packets
     * @api public
     */
    send(packets: any): void;
    /**
     * Called upon open
     *
     * @api protected
     */
    protected onOpen(): void;
    /**
     * Called with data.
     *
     * @param {String} data
     * @api protected
     */
    protected onData(data: any): void;
    /**
     * Called with a decoded packet.
     *
     * @api protected
     */
    protected onPacket(packet: any): void;
    /**
     * Called upon close.
     *
     * @api protected
     */
    protected onClose(): void;
    protected abstract doOpen(): any;
    protected abstract doClose(): any;
    protected abstract write(packets: any): any;
}
