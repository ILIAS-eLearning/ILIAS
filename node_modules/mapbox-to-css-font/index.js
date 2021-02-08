var fontWeights = {
  thin: 100,
  hairline: 100,
  'ultra-light': 100,
  'extra-light': 100,
  light: 200,
  book: 300,
  regular: 400,
  normal: 400,
  plain: 400,
  roman: 400,
  standard: 400,
  medium: 500,
  'semi-bold': 600,
  'demi-bold': 600,
  bold: 700,
  heavy: 800,
  black: 800,
  'extra-bold': 800,
  'ultra-black': 900,
  'extra-black': 900,
  'ultra-bold': 900,
  'heavy-black': 900,
  fat: 900,
  poster: 900
};
var sp = ' ';
var italicRE = /(italic|oblique)$/i;

var fontCache = {};

module.exports = function(fonts, size, lineHeight) {
  var cssData = fontCache[fonts];
  if (!cssData) {
    if (!Array.isArray(fonts)) {
      fonts = [fonts];
    }
    var weight = 400;
    var style = 'normal';
    var fontFamilies = [];
    var haveWeight, haveStyle;
    for (var i = 0, ii = fonts.length; i < ii; ++i) {
      var font = fonts[i];
      var parts = font.split(' ');
      var maybeWeight = parts[parts.length - 1].toLowerCase();
      if (maybeWeight == 'normal' || maybeWeight == 'italic' || maybeWeight == 'oblique') {
        style = haveStyle ? style : maybeWeight;
        parts.pop();
        maybeWeight = parts[parts.length - 1].toLowerCase();
      } else if (italicRE.test(maybeWeight)) {
        maybeWeight = maybeWeight.replace(italicRE, '');
        style = haveStyle ? style : parts[parts.length - 1].replace(maybeWeight, '');
      }
      for (var w in fontWeights) {
        if (maybeWeight == w || maybeWeight == w.replace('-', '') || maybeWeight == w.replace('-', sp)) {
          weight = haveWeight ? weight : fontWeights[w];
          parts.pop();
          break;
        }
      }
      if (!haveWeight && typeof maybeWeight == 'number') {
        weight = maybeWeight;
      }
      var fontFamily = parts.join(sp)
        .replace('Klokantech Noto Sans', 'Noto Sans');
      if (fontFamily.indexOf(sp) !== -1) {
        fontFamily = '"' + fontFamily + '"';
      }
      fontFamilies.push(fontFamily);
    }
    // CSS font property: font-style font-weight font-size/line-height font-family
    cssData = fontCache[fonts] = [style, weight, fontFamilies];
  }
  return cssData[0] + sp + cssData[1] + sp + size + 'px' + (lineHeight ? '/' + lineHeight : '') + sp + cssData[2];
};
