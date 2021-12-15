/* eslint-env node */

"use strict";

const { inspect } = require("util");

/**
 * @example
 * ```
 * Float16Array.prototype[Symbol.for("nodejs.util.inspect.custom")] = customInspect;
 * ```
 */
exports.customInspect = function customInspect(_deps, options) {
  const length = this.length;

  const array = [];
  for (let i = 0; i < length; ++i) {
    array[i] = this[i];
  }

  return `Float16Array(${length}) ${inspect(array, options)}`;
};
