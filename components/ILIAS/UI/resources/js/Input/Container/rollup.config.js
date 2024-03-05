import terser from '@rollup/plugin-terser';
import copyright from '../../../../../../../../scripts/Copyright-Checker/copyright';
import preserveCopyright from '../../../../../../../../scripts/Copyright-Checker/preserveCopyright';

export default {
  input: './src/container.js',
  output: {
    file: '../../../../../../../../public/components/ILIAS/UI/src/templates/js/Input/Container/dist/container.min.js',
    format: 'iife',
    banner: copyright,
    plugins: [
      terser({
        format: {
          comments: preserveCopyright,
        },
      }),
    ],
    globals: {
      jquery: '$',
    },
  },
  external: ['jquery'],
};
