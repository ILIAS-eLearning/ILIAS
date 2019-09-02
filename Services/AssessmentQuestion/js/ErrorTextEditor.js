let word_selected = function(event) {
	let word = $(this);
	let index = word.attr('data-index');
	let selected_input = word.siblings('input').eq(0);
	let selected_words = selected_input.val().length > 0 ? selected_input.val().split(',') : [];
	
    word.toggleClass('selected');
    
    if(word.hasClass('selected')) {
    	selected_words.push(index);
    }
    else {
    	selected_words = selected_words.filter(function (value){
    		return value != index;
    	});
    }
    
    selected_input.val(selected_words.join(','));
};

$(document).on('click', '.errortext_word', word_selected);