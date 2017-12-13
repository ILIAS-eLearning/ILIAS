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
                data_url: '',
                extendable: false
            }, config);

            log("Init TagInput with ID", id);
            // log("options", configuration.options);
            // log("selected_options", configuration.selected_options);
            // log("data_url", configuration.data_url);
            // log("extendable", configuration.extendable);
            //
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
                dupDetector: function (remoteMatch, localMatch) {
                    return remoteMatch.id === localMatch.id;
                }
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
                maxChars: 200,
                allowDuplicates: false,
                itemValue: 'id',
                itemText: 'name',
                freeInput: configuration.extendable,
                typeaheadjs: {
                    name: "bloodhound",
                    source: bloodhound.ttAdapter(),
                    minLength: 1,
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
