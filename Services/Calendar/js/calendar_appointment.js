(function($) {
    $(document).ready(function(){
        $('.calfullcontent').each(function(){
            var fs = parseInt($(this).parents(".calevent").height());
            $(this).css('height',fs);
        });

        $('.createhover').mouseover(function(){
            var [prefix, id] = this.id.split("_");
            $('#new_link_'+id).css('visibility', 'visible');
        });

        $('.createhover').mouseout(function(){
            var [prefix, id] = this.id.split("_");
            $('#new_link_'+id).css('visibility', 'hidden');
        });

    });
})($);