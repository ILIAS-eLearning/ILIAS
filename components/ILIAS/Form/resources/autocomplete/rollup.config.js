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
import copyright from '../../../../../scripts/Copyright-Checker/copyright.js';
import preserveCopyright from '../../../../../scripts/Copyright-Checker/preserveCopyright.js';

export default {
  external: [
    'ilias',
    'document',
  ],
  input: './src/legacyAutocomplete.js',
  output: {
    file: './dist/legacyAutocomplete.js',
    format: 'iife',
    banner: copyright,
    globals: {
      document: 'document',
      ilias: 'il',
    },
    plugins: [
      terser({
        format: {
          comments: preserveCopyright,
        },
      }),
    ],
  },
};
