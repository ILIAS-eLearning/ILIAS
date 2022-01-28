export {};
declare global {
    interface SymbolConstructor {
        readonly observable: symbol;
    }
}
