"use strict";
/*
 * This file is only a stub to make './implementation' resolve to the right module.
 */
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.isWorkerRuntime = exports.getWorkerImplementation = exports.defaultPoolSize = void 0;
// We alias `src/master/implementation` to `src/master/implementation.browser` for web
// browsers already in the package.json, so if get here, it's safe to pass-through the
// node implementation
const BrowserImplementation = __importStar(require("./implementation.browser"));
const NodeImplementation = __importStar(require("./implementation.node"));
const runningInNode = typeof process !== 'undefined' && process.arch !== 'browser' && 'pid' in process;
const implementation = runningInNode ? NodeImplementation : BrowserImplementation;
/** Default size of pools. Depending on the platform the value might vary from device to device. */
exports.defaultPoolSize = implementation.defaultPoolSize;
exports.getWorkerImplementation = implementation.getWorkerImplementation;
/** Returns `true` if this code is currently running in a worker. */
exports.isWorkerRuntime = implementation.isWorkerRuntime;
