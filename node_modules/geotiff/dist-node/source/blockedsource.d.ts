export class BlockedSource extends BaseSource {
    /**
     *
     * @param {Source} source The underlying source that shall be blocked and cached
     * @param {object} options
     */
    constructor(source: Source, { blockSize, cacheSize }?: object);
    source: Source;
    blockSize: any;
    blockCache: QuickLRU<any, any>;
    blockRequests: Map<any, any>;
    blockIdsToFetch: Set<any>;
    abortedBlockIds: Set<any>;
    /**
     *
     * @param {AbortSignal} signal
     */
    fetchBlocks(signal: AbortSignal): void;
    /**
     *
     * @param {Set} blockIds
     * @returns {BlockGroup[]}
     */
    groupBlocks(blockIds: Set<any>): BlockGroup[];
    /**
     *
     * @param {Slice[]} slices
     * @param {Map} blocks
     */
    readSliceData(slices: Slice[], blocks: Map<any, any>): ArrayBuffer[];
}
import { BaseSource } from "./basesource.js";
import QuickLRU from "quick-lru";
declare class BlockGroup {
    /**
     *
     * @param {number} offset
     * @param {number} length
     * @param {number[]} blockIds
     */
    constructor(offset: number, length: number, blockIds: number[]);
    offset: number;
    length: number;
    blockIds: number[];
}
export {};
//# sourceMappingURL=blockedsource.d.ts.map