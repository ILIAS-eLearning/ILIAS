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
 */

import PreviewRenderer from "./preview.renderer.js";
import Markdown from "./markdown.class.js";

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class MarkdownFactory {
    /**
     * @type {Array<string, Markdown>}
     */
    instances = [];

    /**
     * @param {string} input_id
     * @param {string} preview_url
     * @param {string} parameter_name
     * @return {void}
     * @throws {Error} if the input was already initialized.
     */
    init(input_id, preview_url, parameter_name) {
        if (undefined !== this.instances[input_id]) {
            throw new Error(`Markdown with input-id '${input_id}' has already been initialized.`);
        }

        this.instances[input_id] = new Markdown(
            new PreviewRenderer(parameter_name, preview_url),
            input_id
        );
    }

    /**
     * @param {string} input_id
     * @param {Markdown|null}
     */
    get(input_id) {
        return this.instances[input_id] ?? null;
    }
}
