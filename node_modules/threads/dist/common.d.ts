import { JsonSerializable, SerializerImplementation } from "./serializers";
export declare function registerSerializer(serializer: SerializerImplementation<JsonSerializable>): void;
export declare function deserialize(message: JsonSerializable): any;
export declare function serialize(input: any): JsonSerializable;
