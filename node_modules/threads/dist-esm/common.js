import { extendSerializer, DefaultSerializer } from "./serializers";
let registeredSerializer = DefaultSerializer;
export function registerSerializer(serializer) {
    registeredSerializer = extendSerializer(registeredSerializer, serializer);
}
export function deserialize(message) {
    return registeredSerializer.deserialize(message);
}
export function serialize(input) {
    return registeredSerializer.serialize(input);
}
