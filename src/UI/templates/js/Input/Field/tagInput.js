/**
 * Wraps the BootstrapTagsInput
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
(function ($, UI) {


    il.UI.Input.tagInput = (function ($) {
        var _DEBUG = false;
        var _CONFIG = {
            id: '',
            options: [],
            selected_options: [],
            extendable: false,
            suggestion_starts: 1,
            max_chars: 2000,
            suggestion_limit: 50,
            debug: _DEBUG,
            allow_duplicates: false,
            highlight: true,
            hint: true,
            tag_class: "label label-primary"
        };


        var _initData = function () {
            // var bhLocalConfig = Object.assign({}, _BLOODHOUND_DEFAULT);
            // bhLocalConfig.local = _CONFIG.options;
            //
            // var bhLocal = new Bloodhound(bhLocalConfig);
            // bhLocal.initialize();
            //
            // var localSource = Object.assign({}, _SOURCE_DEFAULT);
            // localSource.name = 'local';
            // localSource.source = bhLocal.ttAdapter();
            //
            // return localSource


            var bloodHoundObj = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.whitespace,
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                // url points to a json file that contains an array of country names, see
                // https://github.com/twitter/typeahead.js/blob/gh-pages/data/countries.json
                local: _CONFIG.options
            });
            bloodHoundObj.initialize();

            return bloodHoundObj;
        };

        /**
         *
         * @param raw_id
         * @param config
         */
        var init = function (raw_id, config) {
            _log('raw_id', raw_id);
            // Initialize ID and Configuration
            var id = '#' + raw_id;
            _CONFIG = $.extend(
                _CONFIG
                , config);
            _DEBUG = _CONFIG.debug;
            _CONFIG.id = raw_id;
            _log("config", _CONFIG);

            // Bloodhound
            var localSource = _initData();
            _log('datasources', localSource);

            // TagInput
            $(id).tagsinput({
                tagClass: _CONFIG.tag_class,
                cancelConfirmKeysOnEmpty: false,
                maxChars: _CONFIG.max_chars,
                allowDuplicates: _CONFIG.allow_duplicates,
                trimValue: true,
                freeInput: _CONFIG.extendable,
                typeaheadjs: {
                    name: 'local',
                    minLength: _CONFIG.suggestion_starts,
                    highlight: _CONFIG.highlight,
                    hint: _CONFIG.hint,
                    limit: _CONFIG.suggestion_limit,
                    source: localSource.ttAdapter()
                }
            });

            // Hooks
            $(id).on('beforeItemAdd', function (event) {
                _log("item", event.item);
            });
            $(id).on('itemAdded', function (event) {
                _log("Added Item", event.item);
                var hidden = $('#hidden-' + _CONFIG.id);
                var val = hidden.val();
                var items = [];
                try {
                    items = JSON.parse(val);
                } catch (e) {
                }
                items.push(event.item);
                _log('Items', items);
                hidden.val(JSON.stringify(items));
            });

        };

        var _log = function (key, data) {
            if (!_DEBUG) {
                return;
            }
            console.log("***********************");
            console.log(key + ":");
            console.log(data);
        };

        return {
            init: init
        };

    })($);
})($, il.UI.Input);
