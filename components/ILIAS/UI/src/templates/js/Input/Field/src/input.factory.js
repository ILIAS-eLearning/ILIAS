/**
 * This script serves as the bootstrap file for all inputs within the
 * Field/src/ directory (which have been implemented as ES6 modules).
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 *
 * The script is necessary due to rollup.js bundeling, which creates
 * duplicate declarations if e.g. classes were to extend from each-
 * other and are bundled into separate files.
 */

import TextareaFactory from "./Textarea/textarea.factory";
import MarkdownFactory from "./Markdown/markdown.factory";

var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};

(function (Input) {
    Input.textarea = new TextareaFactory();
    Input.markdown = new MarkdownFactory();
})(il.UI.Input);
