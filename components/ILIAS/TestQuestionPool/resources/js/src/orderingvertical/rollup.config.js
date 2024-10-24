import terser from '@rollup/plugin-terser';
import copyright from '../../../../../../../scripts/Copyright-Checker/copyright';
import preserveCopyright from '../../../../../../../scripts/Copyright-Checker/preserveCopyright';

export default {
  external: [
    'document',
    'ilias',
  ],
  input: './orderingvertical.js',
  output: {
    file: '../../dist/orderingvertical.js',
    format: 'iife',
    banner: copyright,
    globals: {
      document: 'document',
      ilias: 'il',
    },
    plugins: [/*
      terser({
        format: {
          comments: preserveCopyright,
        },
      }),
    */],
  },
};
