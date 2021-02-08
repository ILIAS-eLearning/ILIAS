import { listen } from 'ol/events';
import EventType from 'ol/events/EventType';
import { labelCache } from 'ol/render/canvas';
/**
 * Polyfill for Object.assign().  Assigns enumerable and own properties from
 * one or more source objects to a target object.
 * See https://developer.mozilla.org/en/docs/Web/JavaScript/Reference/Global_Objects/Object/assign.
 *
 * @param {!Object} target The target object.
 * @param {...Object} var_sources The source object(s).
 * @return {!Object} The modified target object.
 */
export var assign = typeof Object.assign === 'function'
    ? Object.assign
    : function (target, var_sources) {
        if (target === undefined || target === null) {
            throw new TypeError('Cannot convert undefined or null to object');
        }
        var output = Object(target);
        for (var i = 1, ii = arguments.length; i < ii; ++i) {
            var source = arguments[i];
            if (source !== undefined && source !== null) {
                for (var key in source) {
                    if (source.hasOwnProperty(key)) {
                        output[key] = source[key];
                    }
                }
            }
        }
        return output;
    };
export function deg2rad(degrees) {
    return degrees * Math.PI / 180;
}
export var defaultResolutions = (function () {
    var resolutions = [];
    for (var res = 78271.51696402048; resolutions.length <= 24; res /= 2) {
        resolutions.push(res);
    }
    return resolutions;
})();
/**
 * @param {number} width Width of the canvas.
 * @param {number} height Height of the canvas.
 * @return {HTMLCanvasElement} Canvas.
 */
export function createCanvas(width, height) {
    if (typeof WorkerGlobalScope !== 'undefined' && self instanceof WorkerGlobalScope && typeof OffscreenCanvas !== 'undefined') { // eslint-disable-line
        return /** @type {?} */ (new OffscreenCanvas(width, height));
    }
    else {
        var canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        return canvas;
    }
}
export function getZoomForResolution(resolution, resolutions) {
    var i = 0;
    var ii = resolutions.length;
    for (; i < ii; ++i) {
        var candidate = resolutions[i];
        if (candidate < resolution && i + 1 < ii) {
            var zoomFactor = resolutions[i] / resolutions[i + 1];
            return i + Math.log(resolutions[i] / resolution) / Math.log(zoomFactor);
        }
    }
    return ii - 1;
}
var hairSpacePool = Array(256).join('\u200A');
export function applyLetterSpacing(text, letterSpacing) {
    if (letterSpacing >= 0.05) {
        var textWithLetterSpacing = '';
        var lines = text.split('\n');
        var joinSpaceString = hairSpacePool.slice(0, Math.round(letterSpacing / 0.1));
        for (var l = 0, ll = lines.length; l < ll; ++l) {
            if (l > 0) {
                textWithLetterSpacing += '\n';
            }
            textWithLetterSpacing += lines[l].split('').join(joinSpaceString);
        }
        return textWithLetterSpacing;
    }
    return text;
}
var measureContext;
function getMeasureContext() {
    if (!measureContext) {
        measureContext = createCanvas(1, 1).getContext('2d');
    }
    return measureContext;
}
function measureText(text, letterSpacing) {
    return getMeasureContext().measureText(text).width + (text.length - 1) * letterSpacing;
}
var measureCache = {};
if (labelCache) {
    // Only available when using ES modules
    listen(labelCache, EventType.CLEAR, function () {
        measureCache = {};
    });
}
export function wrapText(text, font, em, letterSpacing) {
    if (text.indexOf('\n') !== -1) {
        var hardLines = text.split('\n');
        var lines = [];
        for (var i = 0, ii = hardLines.length; i < ii; ++i) {
            lines.push(wrapText(hardLines[i], font, em, letterSpacing));
        }
        return lines.join('\n');
    }
    var key = em + ',' + font + ',' + text + ',' + letterSpacing;
    var wrappedText = measureCache[key];
    if (!wrappedText) {
        var words = text.split(' ');
        if (words.length > 1) {
            var ctx = getMeasureContext();
            ctx.font = font;
            var oneEm = ctx.measureText('M').width;
            var maxWidth = oneEm * em;
            var line = '';
            var lines = [];
            // Pass 1 - wrap lines to not exceed maxWidth
            for (var i = 0, ii = words.length; i < ii; ++i) {
                var word = words[i];
                var testLine = line + (line ? ' ' : '') + word;
                if (measureText(testLine, letterSpacing) <= maxWidth) {
                    line = testLine;
                }
                else {
                    if (line) {
                        lines.push(line);
                    }
                    line = word;
                }
            }
            if (line) {
                lines.push(line);
            }
            // Pass 2 - add lines with a width of less than 30% of maxWidth to the previous or next line
            for (var i = 0, ii = lines.length; i < ii && ii > 1; ++i) {
                var line_1 = lines[i];
                if (measureText(line_1, letterSpacing) < maxWidth * 0.35) {
                    var prevWidth = i > 0 ? measureText(lines[i - 1], letterSpacing) : Infinity;
                    var nextWidth = i < ii - 1 ? measureText(lines[i + 1], letterSpacing) : Infinity;
                    lines.splice(i, 1);
                    ii -= 1;
                    if (prevWidth < nextWidth) {
                        lines[i - 1] += ' ' + line_1;
                        i -= 1;
                    }
                    else {
                        lines[i] = line_1 + ' ' + lines[i];
                    }
                }
            }
            // Pass 3 - try to fill 80% of maxWidth for each line
            for (var i = 0, ii = lines.length - 1; i < ii; ++i) {
                var line_2 = lines[i];
                var next = lines[i + 1];
                if (measureText(line_2, letterSpacing) > maxWidth * 0.7 &&
                    measureText(next, letterSpacing) < maxWidth * 0.6) {
                    var lineWords = line_2.split(' ');
                    var lastWord = lineWords.pop();
                    if (measureText(lastWord, letterSpacing) < maxWidth * 0.2) {
                        lines[i] = lineWords.join(' ');
                        lines[i + 1] = lastWord + ' ' + next;
                    }
                    ii -= 1;
                }
            }
            wrappedText = lines.join('\n');
        }
        else {
            wrappedText = text;
        }
        wrappedText = applyLetterSpacing(wrappedText, letterSpacing);
        measureCache[key] = wrappedText;
    }
    return wrappedText;
}
//# sourceMappingURL=util.js.map