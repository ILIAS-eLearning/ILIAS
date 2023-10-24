import terser from '@rollup/plugin-terser';
import copyright from '../../../CI/Copyright-Checker/copyright';

export default {
  input: './src/index.js',
  output: {
    file: './dist/index.min.js',
    format: 'iife',
    banner: copyright,
    globals: {
      document: 'document',
      ilias: 'il',
      jquery: '$',
    },
    external: ['document', 'ilias', 'jquery'],
    plugins: [
      terser({
        format: {
          comments: 'preserveCopyright',
        },
      }),
    ],
  },
};
