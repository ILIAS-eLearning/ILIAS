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
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

import terser from '@rollup/plugin-terser';
import copyright from '../../../../../../scripts/Copyright-Checker/copyright.js';
import preserveCopyright from '../../../../../../scripts/Copyright-Checker/preserveCopyright.js';

export default {
  input: './src/facade.js',
  external: [
    'document',
    'ilias',
  ],
  output: {
    file: './dist/progress.min.js',
    format: 'iife',
    globals: {
      document: 'document',
      ilias: 'il',
    },
    banner: copyright,
    plugins: [
      terser({
        format: {
          comments: preserveCopyright,
        },
      }),
    ],
  },
};
