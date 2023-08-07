export default [
  {
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
  },
  {
    input: './src/presentationtable.js',
    output: {
      file: './dist/presentationtable.js',
      format: 'es',
      globals: {
        il: 'il',
      },
    },
    external: ['il'],
  },
];
