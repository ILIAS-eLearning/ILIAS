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
 * Wraps the TagsInput
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
(function () {
  il.UI.Input.tagInput = (function () {
    const instances = [];
    const init = function (id, config) {
      let options = [];
      if (Array.isArray(config.tags)) {
        options = config.tags.map((tag) => ({
          value: encodeURIComponent(tag.trim()),
          display: tag,
          searchBy: tag
        }));
      }
      else if (typeof config.tags === 'object' && config.tags === null) {
        for (let prop in config.tags) {
          if (config.tags.hasOwnProperty(prop)) {
            options.push({
              value: encodeURIComponent(prop.trim()),
              display: config.tags[prop],
              searchBy: config.tag[prop]
            });
          }
        }
      }
      else {
        throw new Error("config.tags needs to be an Array or an object");
      }

      let elem = document.querySelector("#" + id + " .c-field-tag");
      let tagify = new Tagify(elem,
        {
          whitelist: options,
          enforceWhitelist: !config.user_created_tags_allowed,
          dropdown: {
            enabled: config.suggestion_starts_after,
            maxItems: 200,
            closeOnSelect: false,
            highlightFirst: true,
          },
          duplicates: false,
          delimiters: null,
          maxTags: config.max_tags === -1 ? Infinity : config.max_tags,
          templates: {
            tag: (tagData) =>
                (`<tag contenteditable='false'
                      spellcheck="false"
                      class='tagify__tag'
                      value="${tagData.value}"
                      tabindex="0">
                    <x title='remove tag' class='tagify__tag__removeBtn'></x>
                    <div>
                        <span class='tagify__tag-text'>${tagData.display}</span>
                    </div>
                </tag>`),
            dropdownItem: (tagData) =>
                (`<div class='tagify__dropdown__item'
                      tagifySuggestionIdx="${tagData.tagifySuggestionIdx}"
                      value="${tagData.value}">
                   <span>${tagData.display}</span>
                 </div>`)
          },
          originalInputValueFormat: (values) => values.map((item) => item.value),
          transformTag(tagData) {
            if (!tagData.display) {
              tagData.display = tagData.value;
              tagData.value = encodeURIComponent(tagData.value);
            }
            tagData.display = tagData.display
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;');
          },
        }
      );
      console.log(config.value);
      tagify.addTags(config.value);

      instances[id] = tagify;
    };

    const getTagifyInstance = function (raw_id) {
      return instances[raw_id];
    };

    return {
      init,
      getTagifyInstance,
    };
  }());
}(il.UI.Input));
