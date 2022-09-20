export interface Serializer<Msg = JsonSerializable, Input = any> {
    deserialize(message: Msg): Input;
    serialize(input: Input): Msg;
}
export interface SerializerImplementation<Msg = JsonSerializable, Input = any> {
    deserialize(message: Msg, defaultDeserialize: ((msg: Msg) => Input)): Input;
    serialize(input: Input, defaultSerialize: ((inp: Input) => Msg)): Msg;
}
export declare function extendSerializer<MessageType, InputType = any>(extend: Serializer<MessageType, InputType>, implementation: SerializerImplementation<MessageType, InputType>): Serializer<MessageType, InputType>;
declare type JsonSerializablePrimitive = string | number | boolean | null;
declare type JsonSerializableObject = {
    [key: string]: JsonSerializablePrimitive | JsonSerializablePrimitive[] | JsonSerializableObject | JsonSerializableObject[] | undefined;
};
export declare type JsonSerializable = JsonSerializablePrimitive | JsonSerializablePrimitive[] | JsonSerializableObject | JsonSerializableObject[];
export declare const DefaultSerializer: Serializer<JsonSerializable>;
export {};
