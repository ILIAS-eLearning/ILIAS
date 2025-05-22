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
