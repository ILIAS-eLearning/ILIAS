import { $transferable } from "./symbols";
function isTransferable(thing) {
    if (!thing || typeof thing !== "object")
        return false;
    // Don't check too thoroughly, since the list of transferable things in JS might grow over time
    return true;
}
export function isTransferDescriptor(thing) {
    return thing && typeof thing === "object" && thing[$transferable];
}
export function Transfer(payload, transferables) {
    if (!transferables) {
        if (!isTransferable(payload))
            throw Error();
        transferables = [payload];
    }
    return {
        [$transferable]: true,
        send: payload,
        transferables
    };
}
