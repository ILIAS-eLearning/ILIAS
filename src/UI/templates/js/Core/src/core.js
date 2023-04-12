import il from 'il';
import replaceContent from './core.replaceContent';
import Tooltip from './core.Tooltip';
import URLBuilder from './core.URLBuilder';
import URLBuilderToken from './core.URLBuilderToken';

il.UI = il.UI || {};
il.UI.core = il.UI.core || {};

il.UI.core.replaceContent = replaceContent($);
il.UI.core.Tooltip = Tooltip;
il.UI.core.URLBuilder = URLBuilder;
il.UI.core.URLBuilderToken = URLBuilderToken;
