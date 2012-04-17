/**
 * Handle multi values for select and text input fields
 */
var ilMultiFormValues = {
	
	/**
	 * Bind click events and handle preset values
	 */
	init: function() {		
		// add click event to +-icons
		$('input:image[id*="ilMultiAdd"]').bind('click', function(e) {
			ilMultiFormValues.addEvent(e);
		});
		// add click event to --icons
		$('input:image[id*="ilMultiRmv"]').bind('click', function(e) {
			ilMultiFormValues.removeEvent(e);
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
		var id = $(e.target).attr('id').split('~');
		ilMultiFormValues.add(id[1], id[2], '');
	},

	/**
	 * Remove multi item (click event)
	 * 
	 * @param event e
	 */
	removeEvent: function(e) {
		var id = $(e.target).attr('id').split('~');			
		if($('div[id*="ilFormField~' +  id[1] + '"]').length > 1) {
			$('div[id*="ilFormField~' + id[1] + '~' + id[2] + '"]').remove();
		}
		else {
			$('div[id*="ilFormField~' + id[1] + '~' + id[2] + '"]').find('input:text[id*="' + id[1] + '"]').attr('value', '');
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

		// resetting value for new elements if none given
		ilMultiFormValues.setValue(new_element, preset);

		// insert clone into html	
		$(original_element).after(new_element);
	},

	/**
	 * Use value from hidden item to add preset multi items
	 * 
	 * @param node element
	 */
	handlePreset: function(element) {	
		// build id for added elements
		var element_id = $(element).attr('id').split('~');
		element_id = element_id[1] + '____';

		// add element for each additional value
		var values = $(element).attr('value').split('~');	
		$(values).each(function(i) {
			// 1st value can be ignored
			if(i > 0) {
				ilMultiFormValues.add(element_id, '0', this);		
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
		$(element).find('select[id*="' + group_id + '"]').attr('id', group_id + '~' + element_id);
		$(element).find('input:text[id*="' + group_id + '"]').attr('id', group_id + '~' + element_id);

		// try to set value 
		if(preset != '') {
			$(element).find('select[id*="' + group_id + '"] option[value=' + preset + ']').attr('selected', true);
		}
		else {
			$(element).find('select[id*="' + group_id + '"] option:selected').removeAttr('selected');
		}
		$(element).find('input:text[id*="' + group_id + '"]').attr('value', preset);

		return;		
	}
};

$(document).ready(function() {
  ilMultiFormValues.init();
});