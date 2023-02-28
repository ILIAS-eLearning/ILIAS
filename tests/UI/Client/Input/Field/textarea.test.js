/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */

import TextareaFactory from "../../../../../src/UI/templates/js/Input/Field/src/Textarea/textarea.factory";
import Textarea from "../../../../../src/UI/templates/js/Input/Field/src/Textarea/textarea.class";
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
 * @param {number} max_limit=null
 * @return {void}
 */
function initMockedDom() {
    let dom = new JSDOM(
        `
            <div class="ui-input-textarea">
                <textarea id="${test_input_id}" class="form-control form-control-sm" name="test_input_1"></textarea>
                <div class="help-block">
                    Characters remaining: <span data-action="remainder">0</span>
                </div>
            </div>
        `,
        {
            url: 'https://localhost',
        }
    );

    global.window = dom.window;
    global.document = dom.window.document;

    // otherwise instanceof HTMLSpanElement checks fail, due to
    // not being in an actual "window".
    global.HTMLSpanElement = dom.window.HTMLSpanElement;
}

describe('Textarea input', function () {

    beforeEach(initMockedDom);

    it('can return lines relative to the current selection.', function () {
        const input = new Textarea(test_input_id);

        const line_1 = 'this is line 1';
        const line_2 = 'this is line 2';
        const line_3 = 'this is line 3';

        input.textarea.value = line_1 + '\n' + line_2 + '\n' + line_3;

        // selection or cursor is at the begining of line_2.
        input.textarea.selectionStart = input.textarea.selectionEnd = line_1.length + 1;

        expect(input.getLinesBeforeSelection()).to.have.ordered.members([line_1]);
        expect(input.getLinesOfSelection()).to.have.ordered.members([line_2]);
        expect(input.getLinesAfterSelection()).to.have.ordered.members([line_3]);
    });

    it('can return lines relative to the current multiline selection.', function () {
        const input = new Textarea(test_input_id);

        const line_1 = 'this is line 1';
        const line_2 = 'this is line 2';
        const line_3 = 'this is line 3';
        const line_4 = 'this is line 4';

        input.textarea.value = line_1 + '\n' + line_2 + '\n' + line_3 + '\n' + line_4;

        // selection starts at the begining of line_2 and ends before the newline on line_3.
        input.textarea.selectionStart = line_1.length + 1;
        input.textarea.selectionEnd = input.textarea.selectionStart + line_2.length + 1;

        expect(input.getLinesBeforeSelection()).to.have.ordered.members([line_1]);
        expect(input.getLinesOfSelection()).to.have.ordered.members([line_2, line_3]);
        expect(input.getLinesAfterSelection()).to.have.ordered.members([line_4]);
    });

    it('can update the textarea content and selection.', function () {
        const input = new Textarea(test_input_id);

        const content = '0123456789';
        const position = 5;

        input.textarea.value = '';
        input.textarea.selectionStart = input.textarea.selectionEnd = 0;

        input.updateTextareaContent(content, position, position);

        assert.strictEqual(input.textarea.value, content);
        assert.strictEqual(input.textarea.selectionStart, position);
        assert.strictEqual(input.textarea.selectionEnd, position);
    });

    it('can update the remainder if the content is updated programaticaly.', function () {
        const content = '12345';
        const max_limit = 10;
        const remainder = 5;

        // serverside rendering automatically adds this attribute,
        // in this unit test however, we append it manually.
        document.getElementById(test_input_id)?.setAttribute('maxLength', max_limit);

        const input = new Textarea(test_input_id);

        input.updateTextareaContent(content);

        assert.isNotNull(input.remainder);
        assert.equal(input.remainder.innerHTML, remainder);
    });

    it('can update the remainder according to the current value.', function () {
        const content = '123456789';
        const max_limit = 10;
        const remainder = 1;

        // serverside rendering automatically adds this attribute,
        // in this unit test however, we append it manually.
        document.getElementById(test_input_id)?.setAttribute('maxLength', max_limit);

        const input = new Textarea(test_input_id);

        assert.equal(input.remainder.innerHTML, 0);

        input.textarea.value = content;
        input.updateRemainderCountHook({});

        assert.equal(input.remainder.innerHTML, remainder);
    });

});

describe('Textarea factory', function () {

    beforeEach(initMockedDom);

    it('can initialize textarea instances.', function () {
        const factory = new TextareaFactory();

        factory.init(test_input_id, null, null);

        expect(factory.instances[test_input_id]).to.be.an.instanceOf(Textarea);
    });

    it('can only instantiate the same ID once.', function () {
        const factory = new TextareaFactory();

        factory.init(test_input_id, null, null);

        expect(function () {
            factory.init(test_input_id, null, null);
        }).to.throw(Error);
    });

    it('can return an already created instance.', function () {
        const factory = new TextareaFactory();

        factory.init(test_input_id, null, null);

        const instance = factory.get(test_input_id);

        expect(instance).to.be.an.instanceOf(Textarea);
    });

});
