export default {
  external: ['il'],
  input: './src/core.js',
  output: {
    file: './dist/ui.js',
    format: 'iife',
    sourcemap: 'inline',
    banner: 'var il = il || {};',
    globals: {
      il: 'il'
    }
  }
};
