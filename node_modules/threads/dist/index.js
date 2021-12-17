"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    Object.defineProperty(o, k2, { enumerable: true, get: function() { return m[k]; } });
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __exportStar = (this && this.__exportStar) || function(m, exports) {
    for (var p in m) if (p !== "default" && !Object.prototype.hasOwnProperty.call(exports, p)) __createBinding(exports, m, p);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.Transfer = exports.DefaultSerializer = exports.expose = exports.registerSerializer = void 0;
var common_1 = require("./common");
Object.defineProperty(exports, "registerSerializer", { enumerable: true, get: function () { return common_1.registerSerializer; } });
__exportStar(require("./master/index"), exports);
var index_1 = require("./worker/index");
Object.defineProperty(exports, "expose", { enumerable: true, get: function () { return index_1.expose; } });
var serializers_1 = require("./serializers");
Object.defineProperty(exports, "DefaultSerializer", { enumerable: true, get: function () { return serializers_1.DefaultSerializer; } });
var transferable_1 = require("./transferable");
Object.defineProperty(exports, "Transfer", { enumerable: true, get: function () { return transferable_1.Transfer; } });
