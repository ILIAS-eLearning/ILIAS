/**
 * Handle multi values for select and text input fields
 */
var ilMultiFormValues = {
	
	// 
	auto_complete_urls: {},
	
	/**
	 * Bind click events and handle preset values
	 */
	init: function() {		
		// add click event to +-icons
		$('button[id*="ilMultiAdd"]').bind('click', function(e) {
			ilMultiFormValues.addEvent(e);
		});
		// add click event to --icons
		$('button[id*="ilMultiRmv"]').bind('click', function(e) {
			ilMultiFormValues.removeEvent(e);
		});		
		// add click event to down-icons
		$('button[id*="ilMultiDwn"]').bind('click', function(e) {
			ilMultiFormValues.downEvent(e);
		});
		// add click event to up-icons
		$('button[id*="ilMultiUp"]').bind('click', function(e) {
			ilMultiFormValues.upEvent(e);
		});				
		// return triggers add  (BEFORE adding preset items) 
		$('button[id*="ilMultiAdd"]').each(function() {						
			var id = $(this).attr('id').split('~');	
			// only text inputs are supported yet
			$('div[id*="ilFormField~' + id[1] + '~' + id[2] + '"]').find('input:text[id*="' + id[1] + '"]').bind('keydown', function(e) {
				ilMultiFormValues.keyDown(e);
			});		
		});
		
		// handle preset values (in hidden inputs)
		$('input[id*="ilMultiValues"]').each(function() {		
			ilMultiFormValues.handlePreset(this);				
		});				
	},	
	
	/**
	 * Add multi item (click event)
	 * 
	 * @param event e
	 */
	addEvent: function(e) {
		var id = $(e.delegateTarget).attr('id').split('~');
		ilMultiFormValues.add(id[1], id[2], '');
	},

	/**
	 * Remove multi item (click event)
	 * 
	 * @param event e
	 */
	removeEvent: function(e) {
		var id = $(e.delegateTarget).attr('id').split('~');			
		if($('div[id*="ilFormField~' +  id[1] + '"]').length > 1) {
			$('div[id*="ilFormField~' + id[1] + '~' + id[2] + '"]').remove();
		}
		else {
			$('div[id*="ilFormField~' + id[1] + '~' + id[2] + '"]').find('input:text[id*="' + id[1] + '"]').attr('value', '');
		}
	},
	
	/**
	 * Move multi item down (click event)
	 * 
	 * @param event e
	 */
	downEvent: function(e) {
		var id = $(e.delegateTarget).attr('id').split('~');		
		var original_element = $('div[id*="ilFormField~' + id[1] + '~' + id[2] + '"]');
		var next = $(original_element).next();
		if(next[0])
		{
			$(next).after($(original_element));
		}
	},
	
	/**
	 * Move multi item up (click event)
	 * 
	 * @param event e
	 */
	upEvent: function(e) {
		var id = $(e.delegateTarget).attr('id').split('~');
		var original_element = $('div[id*="ilFormField~' + id[1] + '~' + id[2] + '"]');		
		var prev = $(original_element).prev();
		if(prev[0])
		{
			$(prev).before($(original_element));
		}
	},

	/**
	 * Add multi item
	 * 
	 * @param string group_id 
	 * @param int index 
	 * @param mixed preset 
	 */
	add: function(group_id, index, preset) {
//console.log(group_id);
//console.log(index);
		// find maximum id in group
		var new_id = 0;
		var sub_id = 0;
		$('div[id*="ilFormField~' + group_id + '"]').each(function() {		
			sub_id = $(this).attr('id').split('~')[2];
			sub_id = parseInt(sub_id);
			if(sub_id > new_id)	{
				new_id = sub_id;
			}		
		});	
		new_id = new_id + 1;

		var original_element = $('div[id*="ilFormField~' + group_id + '~' + index + '"]');

		// clone original element
		var new_element = $(original_element).clone();

		// fix id of cloned element
		$(new_element).attr('id', 'ilFormField~' + group_id + '~' + new_id);

		// binding +-icon
		$(new_element).find('[id*="ilMultiAdd"]').each(function() {				
			$(this).attr('id', 'ilMultiAdd~' + group_id + '~' + new_id);	
			$(this).bind('click', function(e) {
				ilMultiFormValues.addEvent(e);
			});		
		});

		// binding --icon
		$(new_element).find('[id*="ilMultiRmv"]').each(function() {							
			$(this).attr('id', 'ilMultiRmv~' + group_id + '~' + new_id);			
			$(this).bind('click', function(e) {
				ilMultiFormValues.removeEvent(e);
			});			
		});
		
		// binding down-icon
		$(new_element).find('[id*="ilMultiDwn"]').each(function() {							
			$(this).attr('id', 'ilMultiDwn~' + group_id + '~' + new_id);			
			$(this).bind('click', function(e) {
				ilMultiFormValues.downEvent(e);
			});			
		});
	
		// binding up-icon
		$(new_element).find('[id*="ilMultiUp"]').each(function() {							
			$(this).attr('id', 'ilMultiUp~' + group_id + '~' + new_id);			
			$(this).bind('click', function(e) {
				ilMultiFormValues.upEvent(e);
			});			
		});

		// resetting value for new elements if none given
		ilMultiFormValues.setValue(new_element, preset);

		// insert clone into html	
		$(original_element).after(new_element);
		
		// add autocomplete
		if (typeof ilMultiFormValues.auto_complete_urls[group_id] != 'undefined' &&
			ilMultiFormValues.auto_complete_urls[group_id] != "") {
			$('[id="' + group_id + '~' + new_id + '"]').autocomplete({
				source: ilMultiFormValues.auto_complete_urls[group_id],
				minLength: 3
			});
		}
	},

	/**
	 * Use value from hidden item to add preset multi items
	 * 
	 * @param node element
	 */
	handlePreset: function(element) {	
		// build id for added elements
		var element_id = $(element).attr('id').split('~');
		element_id = element_id[1];

		// add element for each additional value
		var values = $(element).attr('value').split('~');	
		$(values).each(function(i) {
			// 1st value can be ignored
			if(i > 0) {
				ilMultiFormValues.add(element_id, i-1, this);		
			}
		});	
	},

	/**
	 * Set value for input element, set option for select
	 * 
	 * @param node element
	 * @param mixed preset
	 */
	setValue: function(element, preset) {
		var group_id = $(element).attr('id').split('~');
		var element_id = group_id[2];
		group_id = group_id[1];

		// fix id of first element?	
		var original = $('#' + group_id);
		if(original) {
			$(original).attr('id', group_id + '~0');
		}

		// only select and text inputs are supported yet

		// fixing id
		//$(element).find('select[id*="' + group_id + '"]').attr('id', group_id + '~' + element_id);
		//$(element).find('input:text[id*="' + group_id + '"]').attr('id', group_id + '~' + element_id);
		// new version, alex 10.5.2013, works also if multiple input fields are within one div
		$(element).find('select[id*="' + group_id + '"], input:text[id*="' + group_id + '"], span[id*="' + group_id + '"], input:hidden[id*="hidden' + group_id + '"]').each(function() {
				var cid = $(this).attr('id').split('~');
				$(this).attr('id', cid[0] + '~' + element_id);
			});


		// try to set value 
		if(preset != '') {
			$(element).find('select[id*="' + group_id + '"] option[value="' + preset + '"]').attr('selected', true);
		}
		else {
			$(element).find('select[id*="' + group_id + '"] option:selected').removeAttr('selected');
		}
		$(element).find('input:text[id*="' + group_id + '"]').attr('value', preset);
		
		// non-editable value
		$(element).find('span[id*="' + group_id + '"]').html(preset);
		$(element).find('input:hidden[id*="hidden' + group_id + '"]').attr('value', preset);

		// return triggers add					
		$(element).find('input:text[id*="' + group_id + '"]').bind('keydown', function(e) {
			ilMultiFormValues.keyDown(e);
		});				

		return;		
	},
	
	keyDown: function(e) {
		if(e.which == 13)
		{
			e.preventDefault();

			var id = $(e.delegateTarget).attr('id').split('~');
			if(id.length  < 2)
			{
				id[1] = "0";
			}
			$('[id="ilMultiAdd~'+id[0]+'~'+id[1]+'"]').click();
		}
	},
	
	addAutocomplete: function (id, url) {
		ilMultiFormValues.auto_complete_urls[id] = url;
	}
};

$(document).ready(function() {
  ilMultiFormValues.init();
});