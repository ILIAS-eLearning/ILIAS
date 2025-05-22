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

import Textarea from "./textarea.class.js";

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class TextareaFactory {
    /**
     * @type {Array<string, Textarea>}
     */
    instances = [];

    /**
     * @param {string} input_id
     * @return {void}
     * @throws {Error} if the input was already initialized.
     */
    init(input_id) {
        if (undefined !== this.instances[input_id]) {
            throw new Error(`Textarea with input-id '${input_id}' has already been initialized.`);
        }

        this.instances[input_id] = new Textarea(input_id);
    }

    /**
     * @param {string} input_id
     * @return {Textarea|null}
     */
    get(input_id) {
        return this.instances[input_id] ?? null;
    }
}
