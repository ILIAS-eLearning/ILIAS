/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Character selector object 
 * (anonymous constructor function)
 */
il.CharSelector = new function() {
	
	/**
	 * Self reference for usage in event handlers
	 * @type object
	 * @private
	 */
	var self = this;
			
	/**
	 * Configuration of the panel
	 * Has to be provided as JSON when init() is called
	 * @type object
	 * @private
	 */		
	var config = {
		pages: [],				// list character pages
		open: 0,				// panel is open
		current_page: 0,		// current page
		ajax_url: ''			// ajax_url
	};
	
	/**
	 * Initialize the selector
	 * called from ilTemplate::addOnLoadCode, 
	 * added by ilCharSelectorGUI::addToPage()
	 * @param object	start configuration as JSON
	 */
	this.init = function(startconf) {
		config = startconf;		
	
		// basic condition
		//if (!self.pageHasInput()) {return;}
		if (config.pages.length < 1) { return; }
		
		if (config.current_page >= config.pages.length) {
			config.current_page = 0;
		}
		
		if (config.open) {
			self.openPanel();
		}
		
		$('#ilCharSelectorToggle').show();
		$('#ilCharSelectorToggle').mousedown(function(){return false;});
		$('#ilCharSelectorToggle').click(self.togglePanel); 
	};
	
	
	/**
	 * Initialize the selector panel and adds it to the DOM
	 */
	this.initPanel = function() {
		if ($('#mainspacekeeper').length > 0)
		{
			$('#mainspacekeeper').prepend($('#ilCharSelectorTemplate').html());
		}
		else if ($('#tst_output').length > 0)
		{
			$('body').prepend($('#ilCharSelectorTemplate').html());
		}
		
		$('#ilCharSelectorScroll').mousedown(function(){return false;});
		$('#ilCharSelectorPrevPage').mousedown(function(){return false;});
		$('#ilCharSelectorNextPage').mousedown(function(){return false;});
		$('#ilCharSelectorPrevPage').click(self.previousPage);
		$('#ilCharSelectorNextPage').click(self.nextPage);
		$('#ilCharSelectorSelPage').change(self.selectPage);
		
		self.renderPage();
	};
	
	/**
	 * Open the selector panel
	 */
	this.openPanel = function() {
		if ($('#ilCharSelectorPanel').length == 0) 
		{
			self.initPanel();
		}
		$('#ilCharSelectorPanel').show();	
		
		if ($('#fixed_content').length > 0)
		{
			// normal page
			$('#fixed_content').addClass('ilContentFixedMovedDown');	
		}
		else if ($('#tst_output').length > 0)
		{
			// kiosk mode in tests
			$('body').removeClass('kiosk');
			$('body').addClass('kioskWithCharSelector');
			$('#ilAll').addClass('ilAllMovedDown');
		}

		$('#ilCharSelectorToggle').addClass('ilCharSelectorToggleOpen');
		config.open = 1;
	};

	/**
	 * Close the selector panel
	 */
	this.closePanel = function() {
		$('#ilCharSelectorPanel').hide();
		
		if ($('#fixed_content').length > 0)
		{
			// normal page
			$('#fixed_content').removeClass('ilContentFixedMovedDown');	
		}
		else if ($('#tst_output').length > 0)
		{
			// kiosk mode in tests
			$('#ilAll').removeClass('ilAllMovedDown');
			$('body').removeClass('kioskWithCharSelector');
			$('body').addClass('kiosk');
		}

		$('#ilCharSelectorToggle').removeClass('ilCharSelectorToggleOpen');
		config.open = 0;
	};

	/**
	 * Toggle the visibility of the selector panel
	 * @return boolean false to prevent further event handling
	 */
	this.togglePanel = function() {
		if (config.open) {
			self.closePanel();		
		} else {
			self.openPanel();
		}
		self.sendState();
		return false;
	};
	
	
	/**
	 * Move to page chosen from the selector
	 */
	this.selectPage = function() {
		config.current_page = $(this).val();
		self.renderPage();
		self.sendState();
	};
	
	
	/**
	 * Move to the previous page
	 */
	this.previousPage = function() {
		if (config.current_page > 0) {
			config.current_page--;
			self.renderPage();
			self.sendState();
		}
	};
	
	
	/**
	 * Move to the next page
	 */
	this.nextPage = function() {
		if (config.current_page < config.pages.length-1) {
			config.current_page++;
			self.renderPage();
			self.sendState();
		}
	};

	/** 
	 * Send the current panel state per ajax
	 */
	this.sendState = function() {
		$.get(config.ajax_url, {
			'open': config.open, 
			'current_page': config.current_page})
		
		.done(function(data) {
			// alert(data);
		});
	}
	
	
	/**
	 * Render the current page of characters
	 */
	this.renderPage = function() {
		
		// adjust the navigation
		$('#ilCharSelectorSelPage').val(config.current_page);
		if (config.current_page == 0) {
			$('#ilCharSelectorPrevPage').addClass('ilCharSelectorDisabled');
		} else {
			$('#ilCharSelectorPrevPage').removeClass('ilCharSelectorDisabled');
		}
		if (config.current_page >= config.pages.length-1) {
			$('#ilCharSelectorNextPage').addClass('ilCharSelectorDisabled');
		} else {
			$('#ilCharSelectorNextPage').removeClass('ilCharSelectorDisabled');
		}
					
		// clear the character area
		$('#ilCharSelectorChars').off('mousedown');
		$('#ilCharSelectorChars').off('click');
		$('#ilCharSelectorChars').empty();
		
		var page = config.pages[config.current_page];
		var chars = '';
		
		// start with 1 (0 is page name)
		for (i = 1; i < page.length; i++) {
			
			if (page[i] instanceof Array) {
				// insert a range of characters
				for (c = page[i][0]; c <= page[i][1]; c++) {
					chars = chars + '<a>' + String.fromCharCode(c) + '</a> ';	
				}
			} 
			else {
				// insert one or more chars on one button
				chars = chars + '<a>' + page[i] + '</a> ';	
			}
		}
		$('#ilCharSelectorChars').append(chars);
		
		// bind the click event to all anchors
		//$('#ilCharSelectorChars').mousedown(function(){return false;});
		$('#ilCharSelectorChars a').click(self.insertChar); 
		$('#ilCharSelectorChars a').mouseover(self.showPreview); 
		$('#ilCharSelectorChars a').mouseout(self.hidePreview); 
		
	};
	
	this.showPreview = function() {
		$('#ilCharSelectorPreview').html($(this).text());
		$('#ilCharSelectorPreview').show();
	}
	
	this.hidePreview = function() {
		$('#ilCharSelectorPreview').hide();
	}

	
	/**
	 * Insert a character to the current text field
	 * @return boolean false to prevent further event handling
	 */
	this.insertChar = function() {
		
		// 'this' is the element that raised the event
		var char = $(this).text();

		// get the focussed element an check its type
		var doc = document;
		var element = doc.activeElement;
		
		// special handling of tinyMCE
		if (element.tagName == 'IFRAME') {
			if ($(element).parent().hasClass('mceIframeContainer')) {
				tinymce.activeEditor.execCommand('mceInsertContent', false, char);
				return;
			}
		}
		
		// normal form elements
		switch (element.tagName) {
			case "INPUT":
				switch ($(element).attr('type').toLowerCase()) {
					case '':
					case 'text':
					case 'password':
					case 'email':
					case 'search':
					case 'url':
						break;					
					default:
						return false;	// no insertion possible
				}
				break;
			case "TEXTAREA":
				break;
			default:
				return false;			// no insertion possible
		}
		
		// insert the char in the active
		if (doc.selection) {
			var sel = doc.selection.createRange();
			sel.text = char;

		} else if (element.selectionStart || element.selectionStart === 0) 
		{
			var startPos = element.selectionStart;
			var endPos = element.selectionEnd;
			var scrollTop = element.scrollTop;
			element.value = element.value.substring(0, startPos) + char + element.value.substring(endPos, element.value.length);
			element.selectionStart = startPos + char.length;
			element.selectionEnd = startPos + char.length;
			element.scrollTop = scrollTop;
		} else {
			element.value += char;
		}
	
		return false;
	};
	
	/**
	 * Checks if the page has input targets
	 * @return boolean
	 */
	this.pageHasInput = function() {
	
		return ( 
			$('#fixed_content').has(
				'textarea'
				+ ',input[type="text"]:not([readonly])'
				+ ',input[type="password"]:not([readonly])'
				+ ',input[type="email"]:not([readonly])'
				+ ',input[type="search"]:not([readonly])'
				+ ',input[type="url"]:not([readonly])'
			).length > 0); 
	};
};
