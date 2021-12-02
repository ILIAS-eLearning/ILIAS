# JS Bundling

We are using [rollup.js](https://rollupjs.org/) to bundle modules to larger components. The single files SHOULD contain export ES6 modules and the overall package SHOULD also be an ES6 module.

## Install Rollup.js

(not necessary as long as we keep npm modules in our git repo)

```
> npm i --save-dev rollup
> npm i --save-dev rollup-plugin-terser        // optional minification
```


## Bundling a component

In your component put your Javascript source under `js/src` and create a directory `js/dist`.

Create a `rollup.config.js` file in your main `js` directory. Minimal version is:

```
export default {
  input: './src/component.js',
  output: {
    file: './dist/component.js',
    format: 'iife',
    sourcemap: 'inline'             // optional: creates inline source map
  }
};
```

Run `npx rollup -c rollup.config.js`.

## Optional: Minification

The terser plugin allows to easily perform a minification of your code.

```
import {terser} from 'rollup-plugin-terser';
export default {
  input: './src/component.js',
  output: [{
    file: './dist/component.js',
    format: 'es'
  },
  {
    file: './dist/component.min.js',
    format: 'es',
    plugins: [terser()]
  }]
};
```

## Optional: Adding to `il` object

You might provide you components API only through ES6 imports. If the component should be added to the global `il` object you might inject the following script code to a page. This might be solved differently in the future, see [js-modules.md](./js-bundling.md).

```
<script type="module">
  import c from './component/js/dist/component.js';
  il.component = c;
</script>
```
