"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});

var _messages = require("./messages.cjs");

function uncurryThis(target) {
  return (thisArg, ...args) => {
    return ReflectApply(target, thisArg, args);
  };
}

function uncurryThisGetter(target, key) {
  return uncurryThis(ReflectGetOwnPropertyDescriptor(target, key).get);
}

const {
  apply: ReflectApply,
  construct: ReflectConstruct,
  defineProperty: ReflectDefineProperty,
  get: ReflectGet,
  getOwnPropertyDescriptor: ReflectGetOwnPropertyDescriptor,
  getPrototypeOf: ReflectGetPrototypeOf,
  has: ReflectHas,
  ownKeys: ReflectOwnKeys,
  set: ReflectSet,
  setPrototypeOf: ReflectSetPrototypeOf
} = Reflect;
exports.ReflectSetPrototypeOf = ReflectSetPrototypeOf;
exports.ReflectSet = ReflectSet;
exports.ReflectOwnKeys = ReflectOwnKeys;
exports.ReflectHas = ReflectHas;
exports.ReflectGetPrototypeOf = ReflectGetPrototypeOf;
exports.ReflectGetOwnPropertyDescriptor = ReflectGetOwnPropertyDescriptor;
exports.ReflectGet = ReflectGet;
exports.ReflectDefineProperty = ReflectDefineProperty;
exports.ReflectConstruct = ReflectConstruct;
exports.ReflectApply = ReflectApply;
const NativeProxy = Proxy;
exports.NativeProxy = NativeProxy;
const NativeNumber = Number;
exports.NativeNumber = NativeNumber;
const {
  isFinite: NumberIsFinite,
  isNaN: NumberIsNaN
} = NativeNumber;
exports.NumberIsNaN = NumberIsNaN;
exports.NumberIsFinite = NumberIsFinite;
const {
  iterator: SymbolIterator,
  species: SymbolSpecies,
  toStringTag: SymbolToStringTag,
  for: SymbolFor
} = Symbol;
exports.SymbolFor = SymbolFor;
exports.SymbolToStringTag = SymbolToStringTag;
exports.SymbolSpecies = SymbolSpecies;
exports.SymbolIterator = SymbolIterator;
const NativeObject = Object;
exports.NativeObject = NativeObject;
const {
  create: ObjectCreate,
  defineProperty: ObjectDefineProperty,
  freeze: ObjectFreeze,
  is: ObjectIs
} = NativeObject;
exports.ObjectIs = ObjectIs;
exports.ObjectFreeze = ObjectFreeze;
exports.ObjectDefineProperty = ObjectDefineProperty;
exports.ObjectCreate = ObjectCreate;
const ObjectPrototype = NativeObject.prototype;
const ObjectPrototype__lookupGetter__ = ObjectPrototype.__lookupGetter__ ? uncurryThis(ObjectPrototype.__lookupGetter__) : (object, key) => {
  if (object == null) {
    throw NativeTypeError(_messages.CANNOT_CONVERT_UNDEFINED_OR_NULL_TO_OBJECT);
  }

  let target = NativeObject(object);

  do {
    const descriptor = ReflectGetOwnPropertyDescriptor(target, key);

    if (descriptor !== undefined) {
      if (ObjectHasOwn(descriptor, "get")) {
        return descriptor.get;
      }

      return;
    }
  } while ((target = ReflectGetPrototypeOf(target)) !== null);
};
exports.ObjectPrototype__lookupGetter__ = ObjectPrototype__lookupGetter__;
const ObjectHasOwn = NativeObject.hasOwn || uncurryThis(ObjectPrototype.hasOwnProperty);
exports.ObjectHasOwn = ObjectHasOwn;
const NativeArray = Array;
const ArrayIsArray = NativeArray.isArray;
exports.ArrayIsArray = ArrayIsArray;
const ArrayPrototype = NativeArray.prototype;
const ArrayPrototypeJoin = uncurryThis(ArrayPrototype.join);
exports.ArrayPrototypeJoin = ArrayPrototypeJoin;
const ArrayPrototypePush = uncurryThis(ArrayPrototype.push);
exports.ArrayPrototypePush = ArrayPrototypePush;
const ArrayPrototypeToLocaleString = uncurryThis(ArrayPrototype.toLocaleString);
exports.ArrayPrototypeToLocaleString = ArrayPrototypeToLocaleString;
const NativeArrayPrototypeSymbolIterator = ArrayPrototype[SymbolIterator];
exports.NativeArrayPrototypeSymbolIterator = NativeArrayPrototypeSymbolIterator;
const ArrayPrototypeSymbolIterator = uncurryThis(NativeArrayPrototypeSymbolIterator);
exports.ArrayPrototypeSymbolIterator = ArrayPrototypeSymbolIterator;
const MathTrunc = Math.trunc;
exports.MathTrunc = MathTrunc;
const NativeArrayBuffer = ArrayBuffer;
exports.NativeArrayBuffer = NativeArrayBuffer;
const ArrayBufferIsView = NativeArrayBuffer.isView;
exports.ArrayBufferIsView = ArrayBufferIsView;
const ArrayBufferPrototype = NativeArrayBuffer.prototype;
const ArrayBufferPrototypeSlice = uncurryThis(ArrayBufferPrototype.slice);
exports.ArrayBufferPrototypeSlice = ArrayBufferPrototypeSlice;
const ArrayBufferPrototypeGetByteLength = uncurryThisGetter(ArrayBufferPrototype, "byteLength");
exports.ArrayBufferPrototypeGetByteLength = ArrayBufferPrototypeGetByteLength;
const NativeSharedArrayBuffer = typeof SharedArrayBuffer !== "undefined" ? SharedArrayBuffer : null;
exports.NativeSharedArrayBuffer = NativeSharedArrayBuffer;
const SharedArrayBufferPrototypeGetByteLength = NativeSharedArrayBuffer && uncurryThisGetter(NativeSharedArrayBuffer.prototype, "byteLength");
exports.SharedArrayBufferPrototypeGetByteLength = SharedArrayBufferPrototypeGetByteLength;
const TypedArray = ReflectGetPrototypeOf(Uint8Array);
exports.TypedArray = TypedArray;
const TypedArrayFrom = TypedArray.from;
const TypedArrayPrototype = TypedArray.prototype;
exports.TypedArrayPrototype = TypedArrayPrototype;
const NativeTypedArrayPrototypeSymbolIterator = TypedArrayPrototype[SymbolIterator];
exports.NativeTypedArrayPrototypeSymbolIterator = NativeTypedArrayPrototypeSymbolIterator;
const TypedArrayPrototypeKeys = uncurryThis(TypedArrayPrototype.keys);
exports.TypedArrayPrototypeKeys = TypedArrayPrototypeKeys;
const TypedArrayPrototypeValues = uncurryThis(TypedArrayPrototype.values);
exports.TypedArrayPrototypeValues = TypedArrayPrototypeValues;
const TypedArrayPrototypeEntries = uncurryThis(TypedArrayPrototype.entries);
exports.TypedArrayPrototypeEntries = TypedArrayPrototypeEntries;
const TypedArrayPrototypeSet = uncurryThis(TypedArrayPrototype.set);
exports.TypedArrayPrototypeSet = TypedArrayPrototypeSet;
const TypedArrayPrototypeReverse = uncurryThis(TypedArrayPrototype.reverse);
exports.TypedArrayPrototypeReverse = TypedArrayPrototypeReverse;
const TypedArrayPrototypeFill = uncurryThis(TypedArrayPrototype.fill);
exports.TypedArrayPrototypeFill = TypedArrayPrototypeFill;
const TypedArrayPrototypeCopyWithin = uncurryThis(TypedArrayPrototype.copyWithin);
exports.TypedArrayPrototypeCopyWithin = TypedArrayPrototypeCopyWithin;
const TypedArrayPrototypeSort = uncurryThis(TypedArrayPrototype.sort);
exports.TypedArrayPrototypeSort = TypedArrayPrototypeSort;
const TypedArrayPrototypeSlice = uncurryThis(TypedArrayPrototype.slice);
exports.TypedArrayPrototypeSlice = TypedArrayPrototypeSlice;
const TypedArrayPrototypeSubarray = uncurryThis(TypedArrayPrototype.subarray);
exports.TypedArrayPrototypeSubarray = TypedArrayPrototypeSubarray;
const TypedArrayPrototypeGetBuffer = uncurryThisGetter(TypedArrayPrototype, "buffer");
exports.TypedArrayPrototypeGetBuffer = TypedArrayPrototypeGetBuffer;
const TypedArrayPrototypeGetByteOffset = uncurryThisGetter(TypedArrayPrototype, "byteOffset");
exports.TypedArrayPrototypeGetByteOffset = TypedArrayPrototypeGetByteOffset;
const TypedArrayPrototypeGetLength = uncurryThisGetter(TypedArrayPrototype, "length");
exports.TypedArrayPrototypeGetLength = TypedArrayPrototypeGetLength;
const TypedArrayPrototypeGetSymbolToStringTag = uncurryThisGetter(TypedArrayPrototype, SymbolToStringTag);
exports.TypedArrayPrototypeGetSymbolToStringTag = TypedArrayPrototypeGetSymbolToStringTag;
const NativeUint16Array = Uint16Array;
exports.NativeUint16Array = NativeUint16Array;

const Uint16ArrayFrom = (...args) => {
  return ReflectApply(TypedArrayFrom, NativeUint16Array, args);
};

exports.Uint16ArrayFrom = Uint16ArrayFrom;
const NativeUint32Array = Uint32Array;
exports.NativeUint32Array = NativeUint32Array;
const NativeFloat32Array = Float32Array;
exports.NativeFloat32Array = NativeFloat32Array;
const ArrayIteratorPrototype = ReflectGetPrototypeOf([][SymbolIterator]());
exports.ArrayIteratorPrototype = ArrayIteratorPrototype;
const ArrayIteratorPrototypeNext = uncurryThis(ArrayIteratorPrototype.next);
exports.ArrayIteratorPrototypeNext = ArrayIteratorPrototypeNext;
const GeneratorPrototypeNext = uncurryThis(function* () {}().next);
exports.GeneratorPrototypeNext = GeneratorPrototypeNext;
const IteratorPrototype = ReflectGetPrototypeOf(ArrayIteratorPrototype);
exports.IteratorPrototype = IteratorPrototype;
const DataViewPrototype = DataView.prototype;
const DataViewPrototypeGetUint16 = uncurryThis(DataViewPrototype.getUint16);
exports.DataViewPrototypeGetUint16 = DataViewPrototypeGetUint16;
const DataViewPrototypeSetUint16 = uncurryThis(DataViewPrototype.setUint16);
exports.DataViewPrototypeSetUint16 = DataViewPrototypeSetUint16;
const NativeTypeError = TypeError;
exports.NativeTypeError = NativeTypeError;
const NativeRangeError = RangeError;
exports.NativeRangeError = NativeRangeError;
const NativeWeakSet = WeakSet;
exports.NativeWeakSet = NativeWeakSet;
const WeakSetPrototype = NativeWeakSet.prototype;
const WeakSetPrototypeAdd = uncurryThis(WeakSetPrototype.add);
exports.WeakSetPrototypeAdd = WeakSetPrototypeAdd;
const WeakSetPrototypeHas = uncurryThis(WeakSetPrototype.has);
exports.WeakSetPrototypeHas = WeakSetPrototypeHas;
const NativeWeakMap = WeakMap;
exports.NativeWeakMap = NativeWeakMap;
const WeakMapPrototype = NativeWeakMap.prototype;
const WeakMapPrototypeGet = uncurryThis(WeakMapPrototype.get);
exports.WeakMapPrototypeGet = WeakMapPrototypeGet;
const WeakMapPrototypeHas = uncurryThis(WeakMapPrototype.has);
exports.WeakMapPrototypeHas = WeakMapPrototypeHas;
const WeakMapPrototypeSet = uncurryThis(WeakMapPrototype.set);
exports.WeakMapPrototypeSet = WeakMapPrototypeSet;