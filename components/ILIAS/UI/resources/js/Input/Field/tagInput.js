/**
 * Wraps the TagsInput
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
(function ($) {
  il.UI.Input.tagInput = (function ($) {
    const instances = [];
    const init = function (raw_id, config, value) {
      let _CONFIG = {};
      const _getSettings = function () {
        return {
          whitelist: _CONFIG.options,
          enforceWhitelist: !_CONFIG.userInput,
          duplicates: _CONFIG.allowDuplicates,
          maxTags: _CONFIG.maxItems,
          originalInputValueFormat: (valuesArr) => valuesArr.map((item) => item.value),
          dropdown: {
            enabled: _CONFIG.dropdownSuggestionsStartAfter,
            maxItems: _CONFIG.dropdownMaxItems,
            closeOnSelect: _CONFIG.dropdownCloseOnSelect,
            highlightFirst: _CONFIG.highlight,
          },
          transformTag(tagData) {
            if (!tagData.display) {
              tagData.display = tagData.value;
              tagData.value = encodeURI(tagData.value);
            }
            tagData.display = tagData.display
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;');
          },
        };
      };

      // Initialize ID and Configuration
      _CONFIG = $.extend(_CONFIG, config);
      _CONFIG.id = raw_id;

      const settings = _getSettings();
      settings.delimiters = null;
      settings.templates = {};
      settings.templates.tag = function (tagData) {
        return `<tag contenteditable='false'
                            spellcheck="false" class='tagify__tag'
                            value="${tagData.value}">
                            <x title='remove tag' class='tagify__tag__removeBtn'></x>
                            <div>
                                <span class='tagify__tag-text'>${tagData.display}</span>
                            </div>
                    </tag>`;
      };
      settings.templates.dropdownItem = function (tagData) {
        return `<div class='tagify__dropdown__item' tagifySuggestionIdx="${tagData.tagifySuggestionIdx}">
                            <span>${tagData.display}</span>
                        </div>`;
      };

      const input = document.getElementById(_CONFIG.id);
      const tagify = new Tagify(input, settings);

      tagify.addTags(value);

      instances[raw_id] = tagify;

    	    // see https://github.com/yairEO/tagify "Submit on `Enter` key"
    	    const onTagifyKeyDown = function (e) {
        const { key } = e.detail.originalEvent;
        if (key === 'Enter'
                    && !tagify.state.inputText // assuming user is not in the middle oy adding a tag
                    && !tagify.state.editing // user not editing a tag
        ) {
          const input_values = input.value;
          const values = input_values.trim() ? input_values.split(',') : [];

          setTimeout(() => il.UI.viewcontrol.tag.submit(values));
        }
      };
    };

    const getTagifyInstance = function (raw_id) {
      return instances[raw_id];
    };

    return {
      init,
      getTagifyInstance,
    };
  }($));
}($, il.UI.Input));
