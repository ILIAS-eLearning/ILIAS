/// <reference lib="es2018" />
import { hasSymbol } from "./_symbols";
export function isAsyncIterator(thing) {
    return thing && hasSymbol("asyncIterator") && thing[Symbol.asyncIterator];
}
export function isIterator(thing) {
    return thing && hasSymbol("iterator") && thing[Symbol.iterator];
}
