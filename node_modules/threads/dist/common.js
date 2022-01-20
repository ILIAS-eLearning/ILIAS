"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.serialize = exports.deserialize = exports.registerSerializer = void 0;
const serializers_1 = require("./serializers");
let registeredSerializer = serializers_1.DefaultSerializer;
function registerSerializer(serializer) {
    registeredSerializer = serializers_1.extendSerializer(registeredSerializer, serializer);
}
exports.registerSerializer = registerSerializer;
function deserialize(message) {
    return registeredSerializer.deserialize(message);
}
exports.deserialize = deserialize;
function serialize(input) {
    return registeredSerializer.serialize(input);
}
exports.serialize = serialize;
