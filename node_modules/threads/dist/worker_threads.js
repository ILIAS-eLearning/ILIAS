"use strict";
// Webpack hack
// tslint:disable no-eval
Object.defineProperty(exports, "__esModule", { value: true });
let implementation;
function selectImplementation() {
    return typeof __non_webpack_require__ === "function"
        ? __non_webpack_require__("worker_threads")
        : eval("require")("worker_threads");
}
function getImplementation() {
    if (!implementation) {
        implementation = selectImplementation();
    }
    return implementation;
}
exports.default = getImplementation;
