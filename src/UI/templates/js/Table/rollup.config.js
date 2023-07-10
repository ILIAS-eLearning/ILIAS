export default {
  input: './src/datatable.js',
  output: {
    file: './dist/datatable.js',
    format: 'es',
    globals: {
      il: 'il',
      jquery: '$',
    },
  },
  external: ['il', 'jquery'],
};
