# JS Bundling

We are using [`rollup.js`](https://rollupjs.org/) for module-bundling. This process combines multiple JavaScript files
and bundles them together into one, which is production-ready and loadable in the browser. This reduces network traffic
while allowing us to manage our business logic in smaller portions.

**Bundling a service, module or component is optional although highly recommended.**

## Bundle Structure

If you want to bundle your business logic, please stick with the following file-structure:

```
.
├── dist                contains bundled files for the browser.
├── src                 contains the business logic.
    └── index.js        index-file which gets bundled.
└── rollup.config.js    config for the module-bundler.
```

As indicated by the structure above, the `index.js` file serves as an entry-point to the JavaScript bundle. It is also
the file which ultimately gets bundled by our module-bundler. The name may be something different than `index`, but it
should still indicate this intention.

The configuration file `rollup.config.js` must be located in the bundle root-directory and its name must not be changed.
How this file might look like will be covered in the next chapter.

## Rollup Configuration

This section will list a few common configurations used in the ILIAS JavaScript codebase. For more advanced use-cases
you may look at the [full documentation](https://rollupjs.org/configuration-options) of rollup.

**Please note that the current state of ILIAS does not support loading of ES6 modules in the browser. Therefore, this
docu will only cover the [`iife`](https://rollupjs.org/guide/en/#outputformat) output format.**

#### Minimal Version

A minimal version of this file should look like this:

```javascript
import copyright from './CI/Copyright-Checker/copyright';

export default {
  input: './src/index.js',
  output: {
    file: './dist/index.js',
    format: 'iife',
    banner: copyright,
  },
};
```

#### Using Globals

If your bundle is working with globals they must be announced in this config file as well. The following config file
demonstrates this when working with the global ILIAS namespace `il` and the browsers `document` object.

```javascript
import copyright from './CI/Copyright-Checker/copyright';

export default {
  external: [
    'document',
    'ilias',
  ],
  input: './src/index.js',
  output: {
    file: './dist/index.js',
    format: 'iife',
    banner: copyright,
    globals: {
      document: 'document',
      ilias: 'il',
    }
  },
};
```

With this configuration file you can access these globals in `index.js` like:

```javascript
import document from 'document';
import il from 'ilias';

const element = document.getElementById('someId');

// please note that il will be undefined if not declared in the global scope.
il.SomeModule = {};
```

#### With Minification

You also might want to minify your bundle to reduce network traffic even further. ILIAS
uses [`terser`](https://terser.org/) for minification which is available as
a [plugin](https://www.npmjs.com/package/@rollup/plugin-terser) for `rollup.js`.

**Please pay attention when minifying bundles that include third-party libraries, because copyright notices must be
retained during this process.** If you're unsure about the copyright notices you should not minify your bundle like
this.

```javascript
import terser from '@rollup/plugin-terser';
import copyright from './CI/Copyright-Checker/copyright';
import preserveCopyright from './CI/Copyright-Checker/preserveCopyright';

export default {
  input: './src/index.js',
  output: {
    file: './dist/index.min.js',
    format: 'iife',
    banner: copyright,
    plugins: [
      terser({
        format: {
          comments: preserveCopyright,
        },
      }),
    ],
  },
};
```

Note that the minified version ends in `.min.js` due to the ILIAS JavaScript code-style.

#### With Node Modules

Sometimes we want to include packages from node_modules directly into our bundle, instead of making them globally
accessible unnecessarily. This can be achieved with the
[`@rollup/plugin-node-resolve`](https://www.npmjs.com/package/@rollup/plugin-node-resolve) plugin, which is able to
resolve node_modules imports.

It is possible, that the package you want to include is not written in ES6, but in CommonJS instead, which is not
compatible with `rollup.js` by default. To be able to transpile CommonJS module exports you can use the
[`@rollup/plugin-commonjs`](https://www.npmjs.com/package/@rollup/plugin-commonjs) plugin as well.

```javascript
import copyright from './CI/Copyright-Checker/copyright';
import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';

export default {
  input: './src/index.js',
  output: {
    file: './dist/index.js',
    format: 'iife',
    banner: copyright,
    plugins: [
      commonjs(),
      resolve(),
    ],
  },
};
```

Importing a package from node_modules in `index.js` would look something like this:

```javascript
import MyModule from '@namespace/my-module';
// ...

const module = new MyModule();
// ...
```

## Bundling Process

If your bundle is all set up you can run the following command to create the configured output:

```bash
npx rollup --config "rollup.config.js"
```

## Example

You can find a working example of a bundle in the [`js-bundling`](./code-examples/js-bundling) directory. You can view
it in your browser of choice by opening the [bundling-example.html](./code-examples/js-bundling/bundling-example.html)
file and going to the console.
