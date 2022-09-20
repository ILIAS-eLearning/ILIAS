"use strict";
// tslint:disable no-var-requires
/*
 * This file is only a stub to make './implementation' resolve to the right module.
 */
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const implementation_browser_1 = __importDefault(require("./implementation.browser"));
const implementation_tiny_worker_1 = __importDefault(require("./implementation.tiny-worker"));
const implementation_worker_threads_1 = __importDefault(require("./implementation.worker_threads"));
const runningInNode = typeof process !== 'undefined' && process.arch !== 'browser' && 'pid' in process;
function selectNodeImplementation() {
    try {
        implementation_worker_threads_1.default.testImplementation();
        return implementation_worker_threads_1.default;
    }
    catch (error) {
        return implementation_tiny_worker_1.default;
    }
}
exports.default = runningInNode
    ? selectNodeImplementation()
    : implementation_browser_1.default;
