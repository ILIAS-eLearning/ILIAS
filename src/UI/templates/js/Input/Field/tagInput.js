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
            options: [],
            selected_options: [],
            options_provider_url: '',
            extendable: false,
            suggestion_starts: 1,
            max_chars: 2000,
            suggestion_limit: 50,
            debug: _DEBUG,
            query_wildcard: "%query",
            allow_duplicates: false,
            highlight: true,
            hint: true,
            tag_class: "label label-primary"
        };
        var _BLOODHOUND_DEFAULT = {
            datumTokenizer: function (d) {
                return Bloodhound.tokenizers.obj.whitespace('name');
            },
            queryTokenizer: function (d) {
                return Bloodhound.tokenizers.whitespace;
            },
            identify: function (obj) {
                return obj.id;
            }
        };
        var _SOURCE_DEFAULT = {
            name: '',
            source: null,
            displayKey: 'name'
        };

        var _initData = function () {
            var data_sources = [];
            if (_CONFIG.options_provider_url.length > 0) {
                var bhRemoteConfig = Object.assign({}, _BLOODHOUND_DEFAULT);
                bhRemoteConfig.remote = {
                    url: _CONFIG.options_provider_url,
                    wildcard: _CONFIG.query_wildcard
                };

                var bhRemote = new Bloodhound(bhRemoteConfig);
                bhRemote.initialize();

                var remoteSource = Object.assign({}, _SOURCE_DEFAULT);
                remoteSource.name = 'remote';
                remoteSource.source = bhRemote.ttAdapter();

                data_sources.push(remoteSource);
            }

            var bhLocalConfig = Object.assign({}, _BLOODHOUND_DEFAULT);
            bhLocalConfig.local = [{name: "Fschmid"}, {name: "anonymous"}, {name: "root"}];

            var bhLocal = new Bloodhound(bhLocalConfig);
            bhLocal.initialize();

            var localSource = Object.assign({}, _SOURCE_DEFAULT);
            localSource.name = 'local';
            localSource.source = bhLocal.ttAdapter();
            data_sources.push(localSource);

            return data_sources
        };

        /**
         *
         * @param id
         * @param config
         */
        var init = function (id, config) {
            // Initialize ID and Configuration
            id = '#' + id;
            _CONFIG = $.extend(
                _CONFIG
                , config);
            _DEBUG = _CONFIG.debug;
            _log("config", _CONFIG);

            // Bloodhound
            var data_sources = _initData();
            _log('datasources', data_sources);

            // TagInput
            $(id).tagsinput({
                tagClass: _CONFIG.tag_class,
                cancelConfirmKeysOnEmpty: true,
                maxChars: _CONFIG.max_chars,
                allowDuplicates: _CONFIG.allow_duplicates,
                itemValue: 'id',
                itemText: 'name',
                trimValue: true,
                freeInput: _CONFIG.extendable,
                typeaheadjs: [{
                    minLength: _CONFIG.suggestion_starts,
                    highlight: _CONFIG.highlight,
                    hint: _CONFIG.hint,
                    limit: _CONFIG.suggestion_limit
                }, data_sources]
            });

            // Hooks
            $(id).on('beforeItemAdd', function (event) {
                // if (!event.item.hasOwnProperty('id')) {
                //     event.item = {id: 9999, name: event.item};
                // }
                // _CONFIG.options.push(event.item);
                // bhLocal.initialize();
                // console._log(event.item.id);
                _log("item", event.item);
                // _log("Index", bloodhound.index);
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
