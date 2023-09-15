(function($){
    $(document).ready(function() {
        $.extend({}, ilWizardInput, ilIdentifiedWizardInputExtend).init({
            fieldContainerSelector: '.ilWzdContainerText',
            reindexingRequiredElementsSelectors: ['input:text', 'button'],
           
            handleRowCleanUpCallback: function(rowElem)
            {
                $(rowElem).find('input:text').val('');
            }
        });

    });
})(jQuery);
