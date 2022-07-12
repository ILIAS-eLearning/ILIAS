import {
  ArrayIteratorPrototypeNext,
  ArrayPrototypeSymbolIterator,
  GeneratorPrototypeNext,
  IteratorPrototype,
  NativeArrayPrototypeSymbolIterator,
  NativeWeakMap,
  ObjectCreate,
  SymbolIterator,
  SymbolToStringTag,
  WeakMapPrototypeGet,
  WeakMapPrototypeSet,
} from "./primordials.mjs";

/**
 * Wrap ArrayIterator If Array.prototype [@@iterator] has been modified
 *
 * @type {<T>(array: T[]) => Iterable<T>}
 */
export function toSafe(array) {
  if (array[SymbolIterator] === NativeArrayPrototypeSymbolIterator) {
    return array;
  }

  const arrayIterator = ArrayPrototypeSymbolIterator(array);
  return ObjectCreate(null, {
    next: {
      value: function next() {
        return ArrayIteratorPrototypeNext(arrayIterator);
      },
    },

    [SymbolIterator]: {
      value: function values() {
        return this;
      },
    },
  });
}

/** @type {WeakMap<{}, Generator<any>>} */
const generators = new NativeWeakMap();

/** @see https://tc39.es/ecma262/#sec-%arrayiteratorprototype%-object */
const DummyArrayIteratorPrototype = ObjectCreate(IteratorPrototype, {
  next: {
    value: function next() {
      const generator = WeakMapPrototypeGet(generators, this);
      return GeneratorPrototypeNext(generator);
    },
    writable: true,
    configurable: true,
  },

  [SymbolToStringTag]: {
    value: "Array Iterator",
    configurable: true,
  },
});

/** @type {<T>(generator: Generator<T>) => IterableIterator<T>} */
export function wrapGenerator(generator) {
  const dummy = ObjectCreate(DummyArrayIteratorPrototype);
  WeakMapPrototypeSet(generators, dummy, generator);
  return dummy;
}
