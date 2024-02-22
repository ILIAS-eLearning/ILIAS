import terser from '@rollup/plugin-terser';
import copyright from '../../../../../../../../scripts/Copyright-Checker/copyright';
import preserveCopyright from '../../../../../../../../scripts/Copyright-Checker/preserveCopyright';

export default {
  input: './src/input.factory.js',
  output: {
    file: '../../../../../../../../public/components/ILIAS/UI/src/templates/js/Input/Field/dist/input.factory.min.js',
    format: 'es',
    // plugins: [terser()],
  },
};
