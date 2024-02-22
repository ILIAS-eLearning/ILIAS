import terser from '@rollup/plugin-terser';
import copyright from '../../../../../../../../scripts/Copyright-Checker/copyright';
import preserveCopyright from '../../../../../../../../scripts/Copyright-Checker/preserveCopyright';

export default {
  input: './src/filter.js',
  output: {
    file: '../../../../../../../../public/components/ILIAS/UI/src/templates/js/Input/Container/dist/filter.min.js',
    format: 'es',
    // plugins: [terser()],
  },
};
