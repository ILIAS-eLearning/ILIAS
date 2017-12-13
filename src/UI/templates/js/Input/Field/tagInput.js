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
        var DEBUG = false;
        /**
         *
         * @param id
         * @param config
         */
        var init = function (id, config) {
            id = '#' + id;

            var configuration = $.extend({
                // default settings
                options: [],
                selected_options: [],
                options_provider_url: '',
                extendable: false,
                suggestion_starts: 1,
                max_chars: 2000,
                suggestion_limit: 50,
                debug: false
            }, config);

            DEBUG = configuration.debug;
            console.log("Config", configuration);
            var bloodhoundConf = {
                datumTokenizer: function (d) {
                    return d.name;
                    // return [d.name];
                    return Bloodhound.tokenizers.whitespace(d.name);
                },
                queryTokenizer: function (d) {
                    return Bloodhound.tokenizers.whitespace(d.name);
                    return Bloodhound.tokenizers.whitespace;
                },
                // identify: function (obj) {
                //     console.log(obj);
                //     return obj.id;
                // },
                // dupDetector: function (remoteMatch, localMatch) {
                //     return remoteMatch.id === localMatch.id;
                // }
            };

            if (configuration.options_provider_url) {
                log("Remote: ", configuration.options_provider_url);
                bloodhoundConf.remote = {
                    url: configuration.options_provider_url
                }
            }
            if (configuration.options.length > 0) {
                console.log("Local: ", configuration.options);
                bloodhoundConf.local = function () {
                    return configuration.options;
                };
            }

            var bloodhound = new Bloodhound(bloodhoundConf);
            bloodhound.initialize();

            $(id).tagsinput({
                tagClass: 'label label-primary',
                cancelConfirmKeysOnEmpty: true,
                maxChars: configuration.max_chars,
                allowDuplicates: false,
                itemValue: 'id',
                itemText: 'name',
                freeInput: configuration.extendable,
                typeaheadjs: {
                    name: "bloodhound",
                    source: bloodhound.ttAdapter(),
                    minLength: configuration.suggestion_starts,
                    highlight: true,
                    hint: false,
                    limit: configuration.suggestion_limit,
                    displayKey: 'name'
                }
            });
            $(id).on('beforeItemAdd', function (event) {
                log("Index:", bloodhound.index);
            });

        };

        var log = function (key, data) {
            if (!DEBUG) {
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
