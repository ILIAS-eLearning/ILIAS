
/**
 * Wrap map from jquery.
 */

var map = {
    option: [1, '<select multiple="multiple">', '</select>'],
    optgroup: [1, '<select multiple="multiple">', '</select>'],
    legend: [1, '<fieldset>', '</fieldset>'],
    thead: [1, '<table>', '</table>'],
    tbody: [1, '<table>', '</table>'],
    tfoot: [1, '<table>', '</table>'],
    colgroup: [1, '<table>', '</table>'],
    caption: [1, '<table>', '</table>'],
    tr: [2, '<table><tbody>', '</tbody></table>'],
    td: [3, '<table><tbody><tr>', '</tr></tbody></table>'],
    th: [3, '<table><tbody><tr>', '</tr></tbody></table>'],
    col: [2, '<table><tbody></tbody><colgroup>', '</colgroup></table>'],
    _default: [0, '', '']
};

/**
 * Convert the given `html` into DOM elements.
 * @return {Array} of html elements
 *
 * @api public
 */

module.exports = function(html){
    if (typeof html !== 'string') {
        throw new TypeError('String expected');
    }

    // tag name
    var m = /<([\w:]+)/.exec(html);
    if (!m) throw new Error('No elements were generated.');
    var tag = m[1];

    // body support
    if (tag == 'body') {
        var el = document.createElement('html');
        el.innerHTML = html;
        return [el.removeChild(el.lastChild)];
    }

    var elements = [];

    // wrap map
    var wrap = map[tag] || map._default;
    var depth = wrap[0];
    var prefix = wrap[1];
    var suffix = wrap[2];
    var el = document.createElement('div');
    el.innerHTML = prefix + html + suffix;

    // trim away wrapper elements
    while (depth--) {
        el = el.lastChild;
    };

    var els = [];

    var child = el.firstChild;
    do {
        els.push(child);
    } while (child = child.nextElementSibling);

    for (var i=0 ; i<els.length ; ++i) {
        el.removeChild(els[i]);
    }

    return els;
};
