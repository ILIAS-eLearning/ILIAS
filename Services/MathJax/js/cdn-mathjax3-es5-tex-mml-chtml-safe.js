/**
 * Configure the safe mode and load Mathjax from CDN
 * @see https://docs.mathjax.org/en/latest/web/configuration.html#configuring-and-loading-in-one-script
 */


window.MathJax = {
  loader: {
    load: ['ui/safe']
  }
};

(function () {
  var script = document.createElement('script');
  script.src = 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js';
  script.async = true;
  document.head.appendChild(script);
})();