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
 *********************************************************************/

// Configure MathJax on the page
// see https://docs.mathjax.org/en/latest/options/index.html

// This script can be async because it does not have to synchronize with any other script.
// This will allow it to run as soon as it loads (since it is small, there is little cost to that),
// meaning the script to load MathJax itself will be inserted as soon as possible,
// so that MathJax can begin downloading as early as possible.
// see https://docs.mathjax.org/en/latest/web/configuration.html

window.MathJax = {
  loader: {
    load: ['ui/safe'],
  },
  options: {
    ignoreHtmlClass: 'tex2jax_ignore',    //  class that marks tags not to search
    processHtmlClass: 'tex2jax_process',  //  class that marks tags that should be searched
  },
  tex: {
    inlineMath: [
      ['[tex]', '[/tex]'],
      // ['\\(', '\\)']   // prevent native mathjax delimiter
    ],

    displayMath: [
      // ['\\[', '\\]']   // prevent native mathjax delimiter
    ],
  },
  svg: {
    fontCache: 'global',
  },
};

// Load MathJax to the page
(function () {
  const script = document.createElement('script');
  script.src = 'node_modules/mathjax/es5/tex-chtml-full.js';
  script.async = true;
  document.head.appendChild(script);
}());
