import replaceContent from './core.replaceContent.js'
import Tooltip from './core.Tooltip.js'

if (typeof il === 'undefined') {
    il = {}
}
il.UI = il.UI || {};
il.UI.core = il.UI.core || {};

il.UI.core.replaceContent = replaceContent($);
il.UI.core.Tooltip = Tooltip;

