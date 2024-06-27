// MathJax Configuration
// see https://docs.mathjax.org/en/latest/options/index.html
window.MathJax = {
  loader: {
    load: ['ui/safe']
  },
  options: {
    ignoreHtmlClass: 'tex2jax_ignore|tex2jax_ignore_global',  //  class that marks tags not to search
    processHtmlClass: 'tex2jax_process',                      //  class that marks tags that should be searched
  },
  tex: {
    inlineMath: [
      ['[tex]', '[/tex]'],
      ['<span class="latex">', '</span>'],
      ['\\(', '\\)']],

    displayMath: [
      ['\\[', '\\]']],
  },
  svg: {
    fontCache: 'global',
  },
};
