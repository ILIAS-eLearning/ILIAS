# <a href="https://github.com/petamoriken/float16">float16</a>

<p align="center">
  IEEE 754 half-precision floating-point for JavaScript<br>
  See <a href="https://esdiscuss.org/topic/float16array">the archive of the ES Discuss Float16Array topic</a> for details
</p>

<p align="center">
  <a href="https://www.npmjs.com/package/@petamoriken/float16">
    <img src="https://img.shields.io/npm/dw/@petamoriken/float16?logo=npm&amp;style=flat-square" alt="npm downloads">
  </a>
  <a href="https://www.jsdelivr.com/package/npm/@petamoriken/float16">
    <img src="https://img.shields.io/jsdelivr/npm/hw/@petamoriken/float16?label=hits&amp;logo=jsDelivr&amp;style=flat-square" alt="jsDelivr hits">
  </a>
  <a href="https://www.npmjs.com/package/@petamoriken/float16">
    <img src="https://img.shields.io/npm/v/@petamoriken/float16.svg?label=version&amp;logo=npm&amp;style=flat-square" alt="npm version">
  </a>
  <a href="https://deno.land/x/float16">
    <img src="https://img.shields.io/github/v/tag/petamoriken/float16?label=version&amp;logo=deno&amp;style=flat-square" alt="deno version">
  </a>
  <br>
  <a href="https://github.com/petamoriken/float16/blob/master/package.json">
    <img src="https://img.shields.io/badge/dependencies-none-brightgreen?style=flat-square" alt="dependencies">
  </a>
  <a href="https://github.com/petamoriken/float16/blob/master/LICENSE">
    <img src="https://img.shields.io/npm/l/@petamoriken/float16.svg?style=flat-square" alt="license">
  </a>
  <a href="https://codecov.io/gh/petamoriken/float16">
    <img src="https://img.shields.io/codecov/c/gh/petamoriken/float16?logo=codecov&amp;style=flat-square" alt="codecov">
  </a>
</p>

<p align="center">
  <a href="https://saucelabs.com/u/petamoriken">
    <img src="https://saucelabs.com/browser-matrix/petamoriken.svg" alt="Sauce Labs browser matrix">
  </a>
</p>

## Install

```console
npm install @petamoriken/float16
```

```console
yarn add @petamoriken/float16
```

## Import

### Node.js or Bundler (webpack, rollup.js, esbuild, etc)

```js
// ES Modules
import {
  Float16Array, isFloat16Array, isTypedArray,
  getFloat16, setFloat16,
  hfround,
} from "@petamoriken/float16";
```

```js
// CommonJS
const {
  Float16Array, isFloat16Array, isTypedArray,
  getFloat16, setFloat16,
  hfround,
} = require("@petamoriken/float16");
```

### Deno

You can get modules from the [deno.land/x](https://deno.land/x/float16) hosting
service.

```ts
import {
  Float16Array, isFloat16Array, isTypedArray,
  getFloat16, setFloat16,
  hfround,
} from "https://deno.land/x/float16/mod.ts";
```

### Browser

Deliver a `browser/float16.mjs` or `browser/float16.js` file in the npm package
from your Web server with the JavaScript `Content-Type` HTTP header.

```html
<!-- Module Scripts -->
<script type="module">
  import {
    Float16Array, isFloat16Array, isTypedArray,
    getFloat16, setFloat16,
    hfround,
  } from "DEST/TO/float16.mjs";
</script>
```

```html
<!-- Classic Scripts -->
<script src="DEST/TO/float16.js"></script>
<script>
  const {
    Float16Array, isFloat16Array, isTypedArray,
    getFloat16, setFloat16,
    hfround,
  } = float16;
</script>
```

<details>
  <summary>Or, you can use <a href="https://www.jsdelivr.com/package/npm/@petamoriken/float16">jsDelivr CDN</a>.</summary>

  ```html
  <!-- Module Scripts -->
  <script type="module">
    import {
      Float16Array, isFloat16Array, isTypedArray,
      getFloat16, setFloat16,
      hfround,
    } from "https://cdn.jsdelivr.net/npm/@petamoriken/float16/+esm";
  </script>
  ```

  ```html
  <!-- Classic Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/@petamoriken/float16/browser/float16.min.js"></script>
  <script>
    const {
      Float16Array, isFloat16Array, isTypedArray,
      getFloat16, setFloat16,
      hfround,
    } = float16;
  </script>
  ```

</details>

## Support engines

**This package only requires ES2015 features** and does not use
environment-dependent features (except for `inspect/`), so you can use it
without any problems. It works fine with
[the current officially supported versions of Node.js](https://github.com/nodejs/Release).

`Float16Array` implemented by `Proxy` and `Reflect`, so IE11 is never supported
even if you use polyfills.

### Pre-transpiled JavaScript files (CommonJS, IIFE)

`lib/` and `browser/` directories in the npm package have JavaScript files
already transpiled, and they have been tested automatically in the following
environments:

- Node.js: Active LTS
- Firefox: last 2 versions and ESR
- Chrome: last 2 versions
- Edge: last 2 versions
- Safari: last 2 versions

## API

### `Float16Array`

`Float16Array` is similar to `TypedArray` such as `Float32Array`
([MDN](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Float32Array)).

```js
const array = new Float16Array([1.0, 1.1, 1.2, 1.3]);
for (const value of array) {
  // 1, 1.099609375, 1.19921875, 1.2998046875
  console.log(value);
}

// Float16Array(4) [ 2, 2.19921875, 2.3984375, 2.599609375 ]
array.map((value) => value * 2);
```

### `isFloat16Array`

`isFloat16Array` is a utility function to check whether the value given as an
argument is an instance of `Float16Array` or not.

```js
const buffer = new ArrayBuffer(256);

// true
isFloat16Array(new Float16Array(buffer));

// false
isFloat16Array(new Float32Array(buffer));
isFloat16Array(new Uint16Array(buffer));
isFloat16Array(new DataView(buffer));
```

### `isTypedArray`

`isTypedArray` is a utility function to check whether the value given as an
argument is an instance of a type of `TypedArray` or not. Unlike
`util.types.isTypedArray` in Node.js, this returns `true` for `Float16Array`.

```js
const buffer = new ArrayBuffer(256);

// true
isTypedArray(new Float16Array(buffer));
isTypedArray(new Float32Array(buffer));
isTypedArray(new Uint16Array(buffer));

// false
isTypedArray(new DataView(buffer));
```

### `getFloat16`, `setFloat16`

`getFloat16` and `setFloat16` are similar to `DataView` methods such as
`DataView#getFloat32`
([MDN](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/DataView/getFloat32))
and `DataView#setFloat32`
([MDN](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/DataView/setFloat32)).

```ts
declare function getFloat16(view: DataView, byteOffset: number, littleEndian?: boolean): number;
declare function setFloat16(view: DataView, byteOffset: number, value: number, littleEndian?: boolean): void;
```

```js
const buffer = new ArrayBuffer(256);
const view = new DataView(buffer);

view.setUint16(0, 0x1234);
getFloat16(view, 0); // 0.0007572174072265625

// You can append methods to DataView instance
view.getFloat16 = (...args) => getFloat16(view, ...args);
view.setFloat16 = (...args) => setFloat16(view, ...args);

view.getFloat16(0); // 0.0007572174072265625

view.setFloat16(0, Math.PI, true);
view.getFloat16(0, true); // 3.140625
```

### `hfround`

`hfround` is similar to `Math.fround`
([MDN](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Math/fround)).
This function returns nearest half-precision float representation of a number.

```ts
declare function hfround(x: number): number;
```

```js
Math.fround(1.337); // 1.3370000123977661
hfround(1.337); // 1.3369140625
```

## `Float16Array` limitations (edge cases)

<details>
  <summary><code>Float16Array</code> has some limitations, because it is impossible to completely reproduce the behavior of <code>TypedArray</code>. Be careful when checking if it is a <code>TypedArray</code> or not by using <code>ArrayBuffer.isView</code>, and when using Web standards such as <code>structuredClone</code> and WebGL.</summary>

  ### Built-in functions

  Built-in `TypedArray` objects use "internal slots" for built-in methods. Some
  limitations exist because the `Proxy` object can't trap internal slots
  ([explanation](https://javascript.info/proxy#built-in-objects-internal-slots)).

  This package isn't polyfill, in other words, it doesn't change native global
  functions and static/prototype methods.

  E.g. `ArrayBuffer.isView` is the butlt-in method that checks if it has the
  `[[ViewedArrayBuffer]]` internal slot. It returns `false` for `Proxy` object
  such as `Float16Array` instance.

  ```js
  ArrayBuffer.isView(new Float32Array(10)); // true
  ArrayBuffer.isView(new Float16Array(10)); // false
  ```

  ### The structured clone algorithm (Web Workers, IndexedDB, etc)

  The structured clone algorithm copies complex JavaScript objects. It is used
  internally when invoking `structuredClone()`, to transfer data between Web
  Workers via `postMessage()`, storing objects with IndexedDB, or copying objects
  for other APIs
  ([MDN](https://developer.mozilla.org/en-US/docs/Web/API/Web_Workers_API/Structured_clone_algorithm)).

  It can't clone `Proxy` object such as `Float16Array` instance, you need to
  convert it to `Uint16Array` or deal with `ArrayBuffer` directly.

  ```js
  const array = new Float16Array([1.0, 1.1, 1.2]);
  const cloned = structuredClone({ buffer: array.buffer });
  ```

  ### WebGL

  WebGL requires `Uint16Array` for buffer or texture data whose types are
  `gl.HALF_FLOAT` (WebGL 2) or `ext.HALF_FLOAT_OES` (WebGL 1 extension). Do not
  apply the `Float16Array` object directly to `gl.bufferData` or `gl.texImage2D`
  etc.

  ```js
  // WebGL 2 example
  const vertices = new Float16Array([
    -0.5, -0.5,  0,
     0.5, -0.5,  0,
     0.5,  0.5,  0,
  ]);

  const buffer = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, buffer);

  // wrap in Uint16Array
  gl.bufferData(gl.ARRAY_BUFFER, new Uint16Array(vertices.buffer), gl.STATIC_DRAW);
  gl.vertexAttribPointer(location, 3, gl.HALF_FLOAT, false, 0, 0);

  gl.bindBuffer(gl.ARRAY_BUFFER, null);
  gl.enableVertexAttribArray(location);
  ```

  ### Others

  See JSDoc comments in `src/Float16Array.mjs` for details. If you don't write
  hacky code, you shouldn't have any problems.

</details>

## `Float16Array` custom inspection

<details>
  <summary>Provides custom inspection for Node.js and Deno, which makes the results of <code>console.log</code> more readable.
  </summary>

  ### Node.js

  ```js
  // ES Modules
  import { Float16Array } from "@petamoriken/float16";
  import { customInspect } from "@petamoriken/float16/inspect";

  Float16Array.prototype[Symbol.for("nodejs.util.inspect.custom")] = customInspect;
  ```

  ```js
  // CommonJS
  const { Float16Array } = require("@petamoriken/float16");
  const { customInspect } = require("@petamoriken/float16/inspect");

  Float16Array.prototype[Symbol.for("nodejs.util.inspect.custom")] = customInspect;
  ```

  ### Deno

  ```ts
  import { Float16Array } from "https://deno.land/x/float16/mod.ts";
  import { customInspect } from "https://deno.land/x/float16/inspect.ts";

  // deno-lint-ignore no-explicit-any
  (Float16Array.prototype as any)[Symbol.for("Deno.customInspect")] = customInspect;
  ```

</details>

## Development

<details>
  <summary>Manual build and test</summary>

  ### Manual build

  First, download devDependencies.

  ```console
  yarn
  ```

  Build `lib/`, `browser/` files.

  ```console
  yarn run build
  ```

  Build `docs/` files (for browser test).

  ```console
  yarn run docs
  ```

  ### Test

  First, download devDependencies.

  ```console
  yarn
  ```

  #### Node.js test

  ```console
  NODE_ENV=test yarn build:lib
  yarn test
  ```

  #### Browser test

  ```console
  NODE_ENV=test yarn build:browser
  yarn docs
  ```

  Access `docs/test/index.html` with browsers.

  You can access current [test page](https://petamoriken.github.io/float16/test)
  ([power-assert version](https://petamoriken.github.io/float16/test/power)) in
  `master` branch.

</details>

## License

MIT License

This software contains productions that are distributed under
[the Apache 2.0 License](http://www.apache.org/licenses/LICENSE-2.0).
Specifically, `index.d.ts` is modified from the original
[TypeScript lib files](https://github.com/microsoft/TypeScript/tree/main/src/lib).
