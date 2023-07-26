import copyright from '../../../../../CI/Copyright-Checker/copyright';

export default {
  input: './src/datatable.js',
  output: {
    file: './dist/datatable.js',
    format: 'es',
    banner: copyright,
    globals: {
      il: 'il',
      jquery: '$',
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
