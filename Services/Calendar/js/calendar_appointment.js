(function($) {
    $(document).ready(function(){
        $('.calfullcontent').each(function(){
            /*22476*/
            var event_color = $(this).css('backgroundColor');
            $(this).parents(".calevent").css('background-color',event_color);
        });

        $('.createhover').mouseover(function(){
            var id = this.id.split("_")[1];
            $('#new_link_'+id).css('visibility', 'visible');
        });

        $('.createhover').mouseout(function(){
            var id = this.id.split("_")[1];
            $('#new_link_'+id).css('visibility', 'hidden');
        });

    });
})($);