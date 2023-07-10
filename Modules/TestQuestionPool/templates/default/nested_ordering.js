var nested_ordering_input = (() => {
    let pub = {};
    pub.init = (instance_id, indentation_post_var, config) => {
        config.group = 1;
        config.expandBtnHTML = '';
        config.collapseBtnHTML = '';
        config.scroll = true;

        target = '#nestable__' + instance_id;

        let fetchIndentations = (e) => {
            let containerSelector = target;
            let indent = 0, selektor = containerSelector + ' ';

            $(containerSelector).find('input[type=hidden].hiddenIndentationInput').remove();

            do
            {
                var found = false;
                selektor += ' > ' + config.listNodeName + ' > li';
                $(selektor).each(
                    function(pos, item)
                    {
                        var val = $(item).attr('data-id');
                        found = true;
                        $(containerSelector).append(
                            '<input type="hidden" class="hiddenIndentationInput" name="' + indentation_post_var + '[' + val + ']" value="' + indent + '" />'
                        );
                    }
                );
                indent++;
             }
            while(found);
        };

        $(target).nestable(config)
                .on('change', fetchIndentations);
        fetchIndentations(target);
    };

    return pub;
})();