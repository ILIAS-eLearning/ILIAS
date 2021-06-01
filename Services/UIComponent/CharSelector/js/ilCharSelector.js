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
	 * Maximum number of buttons shown on one sub page 
	 * @type integer
	 * @private
	 */
	var page_limit = 64;
			
	/**
	 * Number of sub pages
	 * (Needs to be calculated when a page changes)
	 * @type integer
	 * @private
	 */
	var page_subpages = 0;
		
			
	/**
	 * Configuration of the panel
	 * Has to be provided as JSON when init() is called
	 * @type object
	 * @private
	 */		
	var config = {
		pages: [],				// list character pages
		open: 0,				// panel is open
		current_page: 0,		// current block page 
		current_subpage: 0,		// current sub page
		ajax_url: ''			// ajax_url
	};
	
	/**
	 * Texts to be dynamically rendered
	 * @type object
	 * @private
	 */
	var texts = {
        // fau: testNav - add texts for open/close char selector actions in the question menu
		page: '',
        open: '',
        close: ''
        // fau.
	};
	
	
	/**
	 * Initialize the selector
	 * called from ilTemplate::addOnLoadCode, 
	 * added by ilCharSelectorGUI::addToPage()
	 * @param object	start configuration as JSON
	 * @param object	texts to be dynamically rendered
	 */
	this.init = function(a_config, a_texts) {
		config = a_config;
		texts = a_texts;
	
		// basic condition		
		if (config.pages.length < 1) {
            $('.ilCharSelectorToggle').addClass('disabled');
            return;
        }
		
		if (config.current_page >= config.pages.length) {
			config.current_page = 0;
		}
		self.countSubPages();
		if (config.current_subpage >= page_subpages) {
			config.current_subpage = 0;
		}
		
		if (config.open) {
			self.openPanel();
		}
// fau: handle open/close of char selector from the question menu
        else {
            self.closePanel();
        }

		$('.ilCharSelectorToggle').mousedown(function(){return false;});
		$('.ilCharSelectorToggle').click(self.togglePanel); 

        $('.ilCharSelectorMenuToggle').mousedown(function(){return false;});
        $('.ilCharSelectorMenuToggle').click(self.togglePanelFromMenu);
// fau.
	};
	
	
	/**
	 * Initialize the selector panel and adds it to the DOM
	 */
	this.initPanel = function() {
		if ($('#mainspacekeeper').length > 0)
		{
            // using a dedicated spacer element keeps us independent from the responsive menu heights
            // it also helps to respond to a panel resizing
            $('#mainspacekeeper').before($('#ilCharSelectorTemplate').html());
            $('#mainspacekeeper').prepend('<div id="ilCharSelectorSpacer"></div>');
		}
		else if ($('body').hasClass('kiosk'))
		{
			$('#ilAll').before($('#ilCharSelectorTemplate').html());
            $('#ilAll').prepend('<div id="ilCharSelectorSpacer"></div>');
		}

        // avoid loosing focus in the target text field
		$('#ilCharSelectorPanel').mousedown(function(){return false;});
        // except for dropdown fields which must get focus to open
        $('#ilCharSelectorSelPage').mousedown(function(event){event.stopPropagation();});
        $('#ilCharSelectorSelSubPage').mousedown(function(event){event.stopPropagation();});

        $('#ilCharSelectorPrevPage').click(self.previousPage);
		$('#ilCharSelectorNextPage').click(self.nextPage);
		$('#ilCharSelectorSelPage').change(self.selectPage);
		$('#ilCharSelectorSelSubPage').change(self.selectSubPage);
        $('#ilCharSelectorClose').click(self.togglePanel);
        $(window).resize(self.resizePanel);

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
        $('#ilCharSelectorSpacer').show();
		$('.ilCharSelectorToggle').addClass('active');
// fau: testNav - add 'close' text to menu
        $('#ilCharSelectorMenuToggleLink').text(texts.close);
// fau.
        self.resizePanel();
		config.open = 1;
	};

	/**
	 * Close the selector panel
	 */
	this.closePanel = function() {
		$('#ilCharSelectorPanel').hide();
        $('#ilCharSelectorSpacer').hide();
        $('.ilCharSelectorToggle').removeClass('active');
// fau: testNav - add 'open' text to menu
        $('#ilCharSelectorMenuToggleLink').text(texts.open);
// fau.
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

// fau: testNav - toggle panel from a menu
    /**
     * Toggle the visibility of the selector panel from a menu entry
     * @return boolean true to close the menu
     */
    this.togglePanelFromMenu = function() {
        self.togglePanel();
        return true;
    };
// fau.

	/**
	 * Move to page chosen from the selector
	 */
	this.selectPage = function() {
		config.current_page = $(this).val();
		self.countSubPages();
		config.current_subpage = 0;
		self.renderPage();
        self.sendState();
	};
	
	
	/**
	 * Move to sub page chosen from the selector
	 */
	this.selectSubPage = function() {
		config.current_subpage = $(this).val();
		self.renderPage();
        self.sendState();
	};

	
	/**
	 * Move to the previous page
	 */
	this.previousPage = function() {
		if (config.current_subpage > 0) {
			config.current_subpage--;
			self.renderPage();
			self.sendState();
		}
		else if (config.current_page > 0) {
			config.current_page--;
			self.countSubPages();
			config.current_subpage = Math.max(0, page_subpages - 1);
			self.renderPage();
			self.sendState();
		}
	};
	
	
	/**
	 * Move to the next page
	 */
	this.nextPage = function() {
		if (config.current_subpage < page_subpages - 1) {
			config.current_subpage++;
			self.renderPage();
 			self.sendState();
		}
		else if (config.current_page < config.pages.length - 1) {
			config.current_page++;
			self.countSubPages();
			config.current_subpage = 0;
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
			'current_page': config.current_page,
			'current_subpage': config.current_subpage
		}).done(function(data) {
			// alert(data);
		});
	}
	
	/**
	 * Count the number of sub pages of the current page
	 * and set the private class variable page_subpages
	 */
	this.countSubPages = function () {
		var page = config.pages[config.current_page];
		var buttons = 0;
		// start with 1 (0 is page name)
		for (var i = 1; i < page.length; i++) {		
			if (page[i] instanceof Array) {
				buttons += Math.max(0, page[i][1] - page[i][0] + 1);
			} 
			else {
				buttons += 1;
			}
		}
		page_subpages = Math.ceil(buttons / page_limit);
	}
	
	/**
	 * Render the current page of characters
	 */
	this.renderPage = function() {
		
		// adjust the navigation
		//
        if (config.pages.length < 2 && page_subpages <2)
        {
            $('#ilCharSelectorPaging').hide();
        }
        else
        {
            $('#ilCharSelectorPaging').show();

            $('#ilCharSelectorSelPage').val(config.current_page);
            if (config.current_page == 0 &&
                config.current_subpage == 0)
            {
                $('#ilCharSelectorPrevPage').attr('disabled','disabled');
            } else
            {
                $('#ilCharSelectorPrevPage').removeAttr('disabled');
            }
            if (config.current_page >= config.pages.length - 1 &&
                config.current_subpage >= page_subpages -1)
            {
                $('#ilCharSelectorNextPage').attr('disabled','disabled');
            } else
            {
                $('#ilCharSelectorNextPage').removeAttr('disabled');
            }

            // fill the subpage navigation
            var options = '';
            for (var i = 0; i <= page_subpages - 1; i++) {
                options = options
                    + '<option value="' + i + '">'
                    + texts.page + ' ' + (i+1) + ' / ' + page_subpages
                    + '</option>';
            }
            $('#ilCharSelectorSelSubPage').html(options);
            $('#ilCharSelectorSelSubPage').val(config.current_subpage);
        }


		// clear the character area
		$('#ilCharSelectorChars').off('mousedown');
		$('#ilCharSelectorChars').off('click');
		$('#ilCharSelectorChars').empty();
		
		// render the char buttons
		var page = config.pages[config.current_page];
		var first = config.current_subpage * page_limit;
		var last = config.current_subpage * page_limit + page_limit - 1;
		var button = 0;
		var html = '';
		
		// start with index 1 (0 is page name)
		for (var i = 1; i < page.length; i++) {
			
			if (page[i] instanceof Array) {
				// insert a range of characters
				for (var c = page[i][0]; c <= page[i][1]; c++) {
					if (button >= first && button <= last)
					{
						html = html + '<a>' + String.fromCharCode(c) + '</a> ';
					}
					button++;
				}
			} 
			else {
				// insert one or more chars on one button
				if (button >= first && button <= last)
				{
					html = html + '<a>' + page[i] + '</a> ';
				}
				button++;
			}
		}
		$('#ilCharSelectorChars').append(html);
		
		// bind the click event to all anchors
		$('#ilCharSelectorChars a').click(self.insertChar); 
		$('#ilCharSelectorChars a').mouseover(self.showPreview); 
		$('#ilCharSelectorChars a').mouseout(self.hidePreview);

        self.resizePanel();
	};

    /**
     * Handle a resizing of the panel
     */
    this.resizePanel = function() {    
           
        if($('body.kiosk').length > 0)
        {
		    var topsize = ($("#kioskOptions[name='SEBPlugin']").length > 0) ? $("#kioskOptions").css('height') : "0px";
		    $('#ilCharSelectorPanel').css('top',topsize);
        }
        else
        {
            var position = $('nav.breadcrumb_wrapper').position();
            $('#ilCharSelectorPanel').css('top', position.top + $('nav.breadcrumb_wrapper').height());
        }

        $('#ilCharSelectorSpacer').height($('#ilCharSelectorPanel').height()+30);
    }


    this.showPreview = function() {
		$('#ilCharSelectorPreview').html($(this).text());
	}
	
	this.hidePreview = function() {
        $('#ilCharSelectorPreview').html('');
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
		if (element.tagName.toLowerCase() == 'iframe') {
			if ($(element).parent().hasClass('mceIframeContainer')) {
				tinymce.activeEditor.execCommand('mceInsertContent', false, char);
				return;
			}
		}
		
		// normal form elements
		switch (element.tagName.toLowerCase()) {
			case "input":
                if ($(element).attr('type')) {
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
                }
				break;
			case "textarea":
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
	
		var inputs = 
			'textarea'
			+ ',input[type="text"]:not([readonly])'
			+ ',input[type="password"]:not([readonly])'
			+ ',input[type="email"]:not([readonly])'
			+ ',input[type="search"]:not([readonly])'
			+ ',input[type="url"]:not([readonly])';
		
		if ($('#fixed_content').has(inputs) 
			|| $('#tst_output').length > 0 ) {
			return true;
		} else {
			return false;
		}
	};
};
