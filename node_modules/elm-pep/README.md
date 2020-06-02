# elm-pep

npm package for [elm-pep](https://github.com/mpizenberg/elm-pep), a minimalist pointer events polyfill.

## Usage

Install with

    npm install elm-pep

Include in your code with
```js
import 'elm-pep';
```
or configure your bundler to use `elm-pep` as first entry. For webpack, that would be something like
```js
  entry: ['elm-pep', './src/index.js']
```
in `webpack.config.js`.
