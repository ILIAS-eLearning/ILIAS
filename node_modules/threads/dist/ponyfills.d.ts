export declare type SettlementResult<T> = {
    status: "fulfilled";
    value: T;
} | {
    status: "rejected";
    reason: any;
};
export declare function allSettled<T>(values: T[]): Promise<Array<SettlementResult<T>>>;
