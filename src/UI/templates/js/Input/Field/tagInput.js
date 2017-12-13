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
                max_chars: 2000
            }, config);

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

            if (configuration.data_url) {
                log("Remote: ", configuration.data_url);
                bloodhoundConf.prefetch = {
                    url: configuration.data_url
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
                    // limit: 50,
                    displayKey: 'name'
                }
            });
            $(id).on('beforeItemAdd', function (event) {
                // console.log(event);
                log("Index:", bloodhound.index);
            });

        };

        var log = function (key, data) {
            console.log("***********************");
            console.log(key + ":");
            console.log(data);
        };

        var initData = function (data_url, options) {
            var data = {};
            if (data_url) {
                log("init async bloodhound");
                data = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    prefetch: {
                        url: data_url,
                        filter: function (list) {
                            return $.map(list, function (dataname) {
                                return {name: dataname};
                            });
                        }
                    }
                });
            } else {
                data = new Bloodhound({
                    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
                    queryTokenizer: Bloodhound.tokenizers.whitespace,
                    local: options
                });
            }
            data.initialize();
            return data;
        };


        return {
            init: init
        };

    })($);
})($, il.UI.Input);
