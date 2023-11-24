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
 *
 ******************************************************************** */

import terser from '@rollup/plugin-terser';
import copyright from '../../../../../CI/Copyright-Checker/copyright';
import preserveCopyright from '../../../../../CI/Copyright-Checker/preserveCopyright';

export default [
  {
    input: './src/mainbar.js',
    output: {
      file: './dist/mainbar.js',
      format: 'es',
    },
  },

  {
    input: './src/maincontrols.js',
    output: {
      file: './dist/maincontrols.min.js',
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
        il: 'il',
        jquery: '$',
      },
    },
    external: ['il', 'jquery'],
  },

];
