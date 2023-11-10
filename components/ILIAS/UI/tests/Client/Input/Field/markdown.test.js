/**
 * These tests describe the minimal functionalities of a
 * ILIAS\Component\Input\Field\Markdown input.
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

import MarkdownFactory from "../../../../../../../components/ILIAS/UI/src/templates/js/Input/Field/src/Markdown/markdown.factory";
import PreviewRenderer from "../../../../../../../components/ILIAS/UI/src/templates/js/Input/Field/src/Markdown/preview.renderer";
import Markdown from "../../../../../../../components/ILIAS/UI/src/templates/js/Input/Field/src/Markdown/markdown.class";
import {assert, expect} from "chai";
import {JSDOM} from "jsdom";

/**
 * Input-ID that should be used to initialize instances, it will be used when
 * setting up the mocked DOM.
 *
 * @type {string}
 */
const test_input_id = 'test_input_id';

/**
 * Initializes the global window and document variable that holds a mocked
 * DOM containing the textarea html.
 *
 * @return {void}
 */
function initMockedDom() {
    let dom = new JSDOM(
        `
            <div class="c-input-markdown">
                <div class="c-input-markdown__controls">
                    <div class="btn-group il-viewcontrol-mode" aria-label="" role="group">
                        <button class="btn btn-default engaged" aria-label="edit" aria-pressed="true" data-action="#" id="view_control_edit">edit</button>
                        <button class="btn btn-default" aria-label="view" aria-pressed="false" data-action="#" id="view_control_view">view</button>
                    </div>
                    <div class="c-input-markdown__actions">
                        <span data-action="insert-heading"><button class="btn btn-default" data-action="#" id="action_heading">H</button></span>
                        <span data-action="insert-link"><button class="btn btn-default" data-action="#" id="action_link">L</button></span>
                        <span data-action="insert-bold"><button class="btn btn-default" data-action="#" id="action_bold">B</button></span>
                        <span data-action="insert-italic"><button class="btn btn-default" data-action="#" id="action_italic">I</button></span>
                        <span data-action="insert-ordered-list"><button class="btn btn-default" data-action="#" id="action_ordered_list">OL</button></span>
                        <span data-action="insert-unordered-list"><button class="btn btn-default" data-action="#" id="action_unordered_list">UL</button></span>
                    </div>
                </div>
                <div class="ui-input-textarea">
                    <textarea id="${test_input_id}" class="form-control form-control-sm" name="test_input_1"></textarea>
                </div>
                <div class="c-input-markdown__preview hidden"></div>
            </div>
        `,
        {
            url: 'https://localhost',
        }
    );

    global.window = dom.window;
    global.document = dom.window.document;

    // otherwise instanceof HTMLCollection checks fail, due to
    // not being in an actual "window".
    global.HTMLCollection = dom.window.HTMLCollection;
    // same goes for HTMLSpanElement instanceof checks.
    global.HTMLSpanElement = dom.window.HTMLSpanElement;
}

describe('Markdown input', function () {

    beforeEach(initMockedDom);

    it('can insert characters before and after the current selection.', function () {
        const input = new Markdown({}, test_input_id);

        const content_before_selection = 'wo';
        const content_after_selection = 'rd';
        const selected_content = 'x';

        input.textarea.value = content_before_selection + selected_content + content_after_selection;
        input.textarea.selectionStart = content_before_selection.length;
        input.textarea.selectionEnd = input.textarea.selectionStart + selected_content.length;

        const before_characters = 'ab';
        const after_characters = 'yz';

        input.insertCharactersAroundSelection(before_characters, after_characters);

        const expected_content =
            content_before_selection +
            before_characters +
            selected_content +
            after_characters +
            content_after_selection;

        const expected_selection_start = content_before_selection.length + before_characters.length;
        const expected_selection_end =
            content_before_selection.length +
            before_characters.length +
            selected_content.length;

        assert.strictEqual(input.textarea.value, expected_content);
        assert.strictEqual(input.textarea.selectionStart, expected_selection_start);
        assert.strictEqual(input.textarea.selectionEnd, expected_selection_end);
    });

    it('can toggle bullet-points of all currently selected lines.', function () {
        const input = new Markdown({}, test_input_id);

        const line_1 = 'this is line 1';
        const line_2 = 'this is line 2';
        const line_3 = 'this is line 3';
        const line_4 = 'this is line 4';

        input.textarea.value = line_1 + '\n' + line_2 + '\n' + line_3 + '\n' + line_4;

        // selection starts at the begining of line_2 and ends at the begining of line_3.
        input.textarea.selectionStart = line_1.length + 1;
        input.textarea.selectionEnd = input.textarea.selectionStart + line_2.length + 1;

        const expected_prefix = '- ';
        const expected_line_1 = line_1;
        const expected_line_2 = expected_prefix + line_2;
        const expected_line_3 = expected_prefix + line_3;
        const expected_line_4 = line_4;
        const expected_selection_start = line_1.length + 1;
        const expected_selection_end = expected_selection_start + expected_line_2.length + 1 + expected_line_3.length;

        input.applyTransformationToSelection(input.getBulletPointTransformation());

        expect(input.getLinesBeforeSelection()).to.have.ordered.members([expected_line_1]);
        expect(input.getLinesOfSelection()).to.have.ordered.members([expected_line_2, expected_line_3]);
        expect(input.getLinesAfterSelection()).to.have.ordered.members([expected_line_4]);

        assert.strictEqual(input.textarea.selectionStart, expected_selection_start);
        assert.strictEqual(input.textarea.selectionEnd, expected_selection_end);
    });

    it('can toggle the enumeration of all currently selected lines.', function () {
        const input = new Markdown({}, test_input_id);

        const line_1 = 'this is line 1';
        const line_2 = 'this is line 2';
        const line_3 = 'this is line 3';
        const line_4 = 'this is line 4';

        input.textarea.value = line_1 + '\n' + line_2 + '\n' + line_3 + '\n' + line_4;

        // selection starts at the begining of line_2 and ends at the begining of line_3.
        input.textarea.selectionStart = line_1.length + 1;
        input.textarea.selectionEnd = input.textarea.selectionStart + line_2.length + 1;

        const expected_line_1 = line_1;
        const expected_line_2 = '1. ' + line_2;
        const expected_line_3 = '2. ' + line_3;
        const expected_line_4 = line_4;
        const expected_selection_start = expected_line_1.length + 1;
        const expected_selection_end = expected_selection_start + expected_line_2.length + 1 + expected_line_3.length;

        input.applyTransformationToSelection(input.getEnumerationTransformation());

        expect(input.getLinesBeforeSelection()).to.have.ordered.members([expected_line_1]);
        expect(input.getLinesOfSelection()).to.have.ordered.members([expected_line_2, expected_line_3]);
        expect(input.getLinesAfterSelection()).to.have.ordered.members([expected_line_4]);

        assert.strictEqual(input.textarea.selectionStart, expected_selection_start);
        assert.strictEqual(input.textarea.selectionEnd, expected_selection_end);
    });

    it('can insert a single enumeration on the current line.', function () {
        const input = new Markdown({}, test_input_id);

        const line_content = 'this is a line';
        const line_1 = '1. ' + line_content;
        const line_2 = '';
        const line_3 = '2. ' + line_content;
        const line_4 = '3. ' + line_content;

        input.textarea.value = line_1 + '\n' + line_2 + '\n' + line_3 + '\n' + line_4;

        // selection starts at the begining of line_2.
        input.textarea.selectionStart = input.textarea.selectionEnd = line_1.length + 1;

        const expected_line_1 = line_1;
        const expected_line_2 = '2. ';
        const expected_line_3 = '3. ' + line_content;
        const expected_line_4 = '4. ' + line_content;
        const expected_selection_start = expected_line_1.length + 1 + expected_line_2.length;

        input.insertSingleEnumeration();

        expect(input.getLinesBeforeSelection()).to.have.ordered.members([expected_line_1]);
        expect(input.getLinesOfSelection()).to.have.ordered.members([expected_line_2]);
        expect(input.getLinesAfterSelection()).to.have.ordered.members([expected_line_3, expected_line_4]);

        assert.strictEqual(input.textarea.selectionStart, expected_selection_start);
        assert.strictEqual(input.textarea.selectionEnd, expected_selection_start);
    });

    it('cannot insert any more characters if the max-limit is reached.', function () {
        const max_limit = 10;

        // serverside rendering automatically adds this attribute,
        // in this unit test however, we append it manually.
        document.getElementById(test_input_id)?.setAttribute('maxLength', max_limit);

        const input = new Markdown({}, test_input_id);

        const content = '0123456789';
        const postion = 5;

        input.textarea.value = content;
        // selection is in the middle of the characters.
        input.textarea.selectionStart = input.textarea.selectionEnd = postion;

        input.insertCharactersAroundSelection('a', 'b');

        assert.strictEqual(input.textarea.value, content);
    });

});

describe('Markdown factory', function () {

    beforeEach(initMockedDom);

    it('can initialize markdown instances.', function () {
        const factory = new MarkdownFactory();

        factory.init(test_input_id, null, null);

        expect(factory.instances[test_input_id]).to.be.an.instanceOf(Markdown);
    });

    it('can only instantiate the same ID once.', function () {
        const factory = new MarkdownFactory();

        factory.init(test_input_id, null, null);

        expect(function () {
            factory.init(test_input_id, null, null);
        }).to.throw(Error);
    });

    it('can return an already created instance.', function () {
        const factory = new MarkdownFactory();

        factory.init(test_input_id, null, null);

        const instance = factory.get(test_input_id);

        expect(instance).to.be.an.instanceOf(Markdown);
    });

});

describe('Markdown preview-renderer', function () {

    it('can handle empty strings without request.', async function () {
        const funny_url = '"Hey, if you can read this, the test obviously failed (as it should\'ve!)."'
        const renderer = new PreviewRenderer('p', funny_url);

        const preview_html = await renderer.getPreviewHtmlOf('');

        assert.strictEqual(preview_html, '');
    });

});
