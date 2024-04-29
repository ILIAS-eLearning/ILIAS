import Textarea from "./textarea.class";

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
