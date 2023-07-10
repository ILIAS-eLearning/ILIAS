/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
export default class PreviewRenderer {
    /**
     * @type {string}
     */
    preview_parameter;

    /**
     * @type {string}
     */
    preview_url;

    /**
     * @param {Markdown} markdown_input
     * @param {string} preview_parameter
     * @param {string} preview_url
     */
    constructor(preview_parameter, preview_url) {
        this.preview_parameter = preview_parameter;
        this.preview_url = preview_url;
    }

    /**
     * @param {string} text
     * @return {Promise<string>}
     */
    async getPreviewHtmlOf(text) {
        if (0 === text.length) {
            return '';
        }

        let data = new FormData();

        data.append(this.preview_parameter, text);

        let response = await fetch(this.preview_url, {
            method: 'POST',
            body: data,
        });

        return response.text();
    }
}
