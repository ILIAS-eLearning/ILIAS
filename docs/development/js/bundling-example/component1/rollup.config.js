import {terser} from 'rollup-plugin-terser';
export default {
  input: './src/component1.js',
  output: [{
    file: './dist/component1.js',
    format: 'es',
    sourcemap: 'inline'
  }, {
      file: './dist/component1.min.js',
      format: 'es',
      plugins: [terser()]
  }]
};