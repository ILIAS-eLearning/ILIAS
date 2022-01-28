import { expose } from "./index";
export * from "./index";
if (typeof global !== "undefined") {
    global.expose = expose;
}
if (typeof self !== "undefined") {
    self.expose = expose;
}
