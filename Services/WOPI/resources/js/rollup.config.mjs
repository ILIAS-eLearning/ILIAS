/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

// import terser from '@rollup/plugin-terser';
// import copyright from '../../../../../scripts/Copyright-Checker/copyright';

export default {
  input: './src/index.js',
  output: {
    file: './dist/wopi.min.js',
    format: 'iife',
    // banner: copyright,
    globals: {
      document: 'document',
      ilias: 'il',
      jquery: '$',
    },
    external: ['document', 'ilias', 'jquery'],
    plugins: [
      // terser({
      //   format: {
      //     comments: 'preserveCopyright',
      //   },
      // }),
    ],
  },
};
