export default {
  input: './src/table.js',
  output: {
    file: './dist/table.js',
    format: 'iife',
    globals: {
      il: 'il',
      jquery: '$'
    }
  },
  external: ['il', 'jquery']
};