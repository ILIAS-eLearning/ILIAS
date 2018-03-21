(function($) {
    $(document).ready(function(){
        $('.calfullcontent').each(function(){
            /*22476*/
            var event_color = $(this).css('backgroundColor');
            $(this).parents(".calevent").css('background-color',event_color);
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