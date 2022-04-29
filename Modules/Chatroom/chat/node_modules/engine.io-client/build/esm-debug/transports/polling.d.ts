import { Transport } from "../transport.js";
export declare abstract class Polling extends Transport {
    private polling;
    /**
     * Transport name.
     */
    get name(): string;
    /**
     * Opens the socket (triggers polling). We write a PING message to determine
     * when the transport is open.
     *
     * @api private
     */
    doOpen(): void;
    /**
     * Pauses polling.
     *
     * @param {Function} callback upon buffers are flushed and transport is paused
     * @api private
     */
    pause(onPause: any): void;
    /**
     * Starts polling cycle.
     *
     * @api public
     */
    poll(): void;
    /**
     * Overloads onData to detect payloads.
     *
     * @api private
     */
    onData(data: any): void;
    /**
     * For polling, send a close packet.
     *
     * @api private
     */
    doClose(): void;
    /**
     * Writes a packets payload.
     *
     * @param {Array} data packets
     * @param {Function} drain callback
     * @api private
     */
    write(packets: any): void;
    /**
     * Generates uri for connection.
     *
     * @api private
     */
    uri(): string;
    abstract doPoll(): any;
    abstract doWrite(data: any, callback: any): any;
}
