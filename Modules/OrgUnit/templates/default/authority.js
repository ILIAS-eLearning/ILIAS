var ilOrgUnitAuthorityInput = {
    settings: {},
    data: {},
    init: function (settings, data) {
        // console.log(settings);
        console.log(data);
        console.log(JSON.parse(data));
    }
};
(function ($) {
    $.fn.ilOrgUnitAuthorityInput = function (options) {
        var settings = $.extend({
            // These are the defaults.
            color: "#556b2f",
            backgroundColor: "white"
        }, options);
        ilOrgUnitAuthorityInput.init(settings);
    };
}(jQuery));