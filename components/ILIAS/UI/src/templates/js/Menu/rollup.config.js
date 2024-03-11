import terser from '@rollup/plugin-terser';
import copyright from '../../../../../../../scripts/Copyright-Checker/copyright';
import preserveCopyright from '../../../../../../../scripts/Copyright-Checker/preserveCopyright';

export default {
  external: [
    'jquery',
    'ilias',
    'document'
  ],
  input: './src/drilldown.js',
  output: {
    file: '../../../../../../../public/components/ILIAS/UI/src/templates/js/Menu/dist/drilldown.js',
    format: 'iife',
    banner: copyright,
    globals: {
      jquery: '$',
      ilias: 'il',
      document: 'document'
    },
    plugins: [
      terser({
        format: {
          comments: preserveCopyright,
        }
      })
    ]
  }
};
