"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const index_1 = require("./index");
if (typeof global !== "undefined") {
    global.Worker = index_1.Worker;
}
else if (typeof window !== "undefined") {
    window.Worker = index_1.Worker;
}
