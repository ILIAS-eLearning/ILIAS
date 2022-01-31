export const hasSymbols = () => typeof Symbol === "function";
export const hasSymbol = (name) => hasSymbols() && Boolean(Symbol[name]);
export const getSymbol = (name) => hasSymbol(name) ? Symbol[name] : "@@" + name;
export function registerObservableSymbol() {
    if (hasSymbols() && !hasSymbol("observable")) {
        Symbol.observable = Symbol("observable");
    }
}
if (!hasSymbol("asyncIterator")) {
    Symbol.asyncIterator = Symbol.asyncIterator || Symbol.for("Symbol.asyncIterator");
}
