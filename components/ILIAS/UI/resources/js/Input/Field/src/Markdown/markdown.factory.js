import PreviewRenderer from "./preview.renderer";
import Markdown from "./markdown.class";

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
