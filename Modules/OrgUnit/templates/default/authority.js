(function ($) {
    var ilOrgUnitAuthorityInput = {
        settings: {},
        data: {},
        init: function (settings) {
            alert(settings);
        }
    };
    $.fn.ilOrgUnitAuthorityInput = function (options) {
        var settings = $.extend({
            // These are the defaults.
            color: "#556b2f",
            backgroundColor: "white"
        }, options);
        ilOrgUnitAuthorityInput.init(settings);
    };
}(jQuery));