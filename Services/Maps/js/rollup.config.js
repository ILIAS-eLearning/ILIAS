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

import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import terser from '@rollup/plugin-terser';
import copyright from '../../../CI/Copyright-Checker/copyright';
import preserveCopyright from '../../../CI/Copyright-Checker/preserveCopyright';

export default {
  input: './src/ilOl.js',
  output: {
    file: './dist/ServiceOpenLayers.js',
    format: 'iife',
    globals: {
      il: 'il',
    },
    banner: copyright,
  },
  external: ['il'],
  plugins: [
    resolve(),
    commonjs(),
    terser({
      format: {
        comments: preserveCopyright,
      },
    }),
  ],
  onwarn(warning, superOnWarn) {
    /*
         * skip certain warnings
         * https://github.com/openlayers/openlayers/issues/10245
         */
    if (warning.code === 'THIS_IS_UNDEFINED') {
      return;
    }
    superOnWarn(warning);
  },
};
