"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.registerObservableSymbol = exports.getSymbol = exports.hasSymbol = exports.hasSymbols = void 0;
const hasSymbols = () => typeof Symbol === "function";
exports.hasSymbols = hasSymbols;
const hasSymbol = (name) => exports.hasSymbols() && Boolean(Symbol[name]);
exports.hasSymbol = hasSymbol;
const getSymbol = (name) => exports.hasSymbol(name) ? Symbol[name] : "@@" + name;
exports.getSymbol = getSymbol;
function registerObservableSymbol() {
    if (exports.hasSymbols() && !exports.hasSymbol("observable")) {
        Symbol.observable = Symbol("observable");
    }
}
exports.registerObservableSymbol = registerObservableSymbol;
if (!exports.hasSymbol("asyncIterator")) {
    Symbol.asyncIterator = Symbol.asyncIterator || Symbol.for("Symbol.asyncIterator");
}
