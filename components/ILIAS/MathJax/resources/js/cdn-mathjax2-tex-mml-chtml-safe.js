/**
 * Configure the safe mode and load Mathjax from CDN
 * @see https://docs.mathjax.org/en/latest/web/configuration.html#configuring-and-loading-in-one-script
 */

(function () {
  const script = document.createElement('script');
  script.src = 'https://cdn.jsdelivr.net/npm/mathjax@2.7.9/MathJax.js?config=TeX-AMS-MML_HTMLorMML,Safe';
  script.async = true;
  document.head.appendChild(script);
}());
