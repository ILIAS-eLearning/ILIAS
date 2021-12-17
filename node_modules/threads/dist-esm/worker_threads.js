// Webpack hack
// tslint:disable no-eval
let implementation;
function selectImplementation() {
    return typeof __non_webpack_require__ === "function"
        ? __non_webpack_require__("worker_threads")
        : eval("require")("worker_threads");
}
export default function getImplementation() {
    if (!implementation) {
        implementation = selectImplementation();
    }
    return implementation;
}
