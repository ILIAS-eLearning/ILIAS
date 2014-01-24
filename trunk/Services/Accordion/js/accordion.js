// inspired by accordion.js v2.0, Copyright (c) 2007 stickmanlabs
// Author: Kevin P Miller | http://www.stickmanlabs.com
// MIT-style licensed
//
// Complete rewrite using YUI instead of prototype by Alex Killing for
// ILIAS open source


ilAccordionData = Array();

// Success Handler
var ilAccSuccessHandler = function(o)
{
	// parse headers function
	function parseHeaders()
	{
	}
}

// Success Handler
var ilAccFailureHandler = function(o)
{
	//alert('FailureHandler');
}

function ilAccordionJSHandler(sUrl)
{
	var ilAccCallback =
	{
		success: ilAccSuccessHandler,
		failure: ilAccFailureHandler
	};

	var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, ilAccCallback);
	
	return false;
}


function ilGetNextSibling(n)
{
	do n = n.nextSibling;
	while (n && n.nodeType != 1);
	return n;
}

//var accordion = Class.create();
var accordion = function() {};
accordion.prototype =
{

	//
	//  Setup the Variables
	//
	showAccordion : null,
	currentAccordion : null,
	duration : null,
	effects : [],
	animating : false,
	
	//  
	//  Initialize the accordions
	//
	initialize: function(container, options) {
		this.options = options;
	
		var accordions = YAHOO.util.Dom.getElementsByClassName(this.options.classNames.toggle, 'div', container);
		for (k in accordions)
		{
			var accordion = accordions[k];
			
			if (accordion.parentNode.parentNode.id == container) {
				YAHOO.util.Event.addListener(accordion, "click", this.clickHandler, accordion, this);
				accordion.onclick = function() {return false;};
				
				var n = ilGetNextSibling(accordion);
				
				if (n != undefined)
				{
					
					if (this.options.direction == 'horizontal')
					{
						n.style.width = '0px';
					}
					else
					{
						n.style.height = '0px';
					}
					n.style.display = 'none';
					this.currentAccordion = n;
				}
			}
		}			
	},
	
	
	//
	//  Handle click on accordion header
	//
	clickHandler : function(e, accordion) {
		if (this.animating) {
			return false;
		}
		
		this.effects = [];
	
		this.currentAccordion = ilGetNextSibling(accordion);

		if (this.currentAccordion == this.showAccordion)
		{
			this.deactivate();
		}
		else
		{
			this._handleAccordion();
		}
	},
	
	
	// 
	// Deactivate an active accordion
	//
	deactivate : function() {

		this.currentAccordion.style.display = 'block';
		if (this.options.direction == 'vertical')
		{
			var myAnim = new YAHOO.util.Anim(this.showAccordion, { 
				height: { to: 0 }  
				}, 1, YAHOO.util.Easing.easeOut);
		}
		else
		{
			var myAnim = new YAHOO.util.Anim(this.showAccordion, { 
				width: { to: 0 }  
				}, 1, YAHOO.util.Easing.easeOut);
		}
		myAnim.duration = 0.5;
		myAnim.onComplete.subscribe(function(a, b, t) {
				if (t.options.classNames.activeHead && t.showAccordion && t.options.classNames.activeHead != "") {
					$(t.showAccordion.parentNode).children("div:first").children("div:first").
						removeClass(t.options.classNames.activeHead);
				}
				t.showAccordion.style.height = 'auto';
				t.showAccordion.style.display = 'none';
				t.showAccordion = null;
				t.animating = false;
				if (typeof t.options.save_url != "undefined" && t.options.save_url != "")
				{
					ilAccordionJSHandler(t.options.save_url + "&tab_nr=0");
				}
			}, this);
		myAnim.animate();
	},

	//
	// Handle the open/close actions of the accordion
	//
	_handleAccordion : function() {

		// fade in the new accordion (currentAccordion)
		if (this.options.direction == 'vertical')
		{
			this.currentAccordion.style.position = 'relative';
			this.currentAccordion.style.left = '-1000px';
			this.currentAccordion.style.display = 'block';
			var nh = this.options.defaultSize.height ? this.options.defaultSize.height : this.currentAccordion.scrollHeight
			this.currentAccordion.style.height = '0px';
			this.currentAccordion.style.position = '';
			this.currentAccordion.style.left = '';
			var myAnim = new YAHOO.util.Anim(this.currentAccordion, { 
				height: {
					from: 0,
					to: nh }  
				}, 1, YAHOO.util.Easing.easeOut);
		}
		else
		{
			this.currentAccordion.style.display = 'block';
			var myAnim = new YAHOO.util.Anim(this.currentAccordion, { 
				width: {
					from: 0,
					to: this.options.defaultSize.width ? this.options.defaultSize.width : this.currentAccordion.scrollWidth }  
				}, 1, YAHOO.util.Easing.easeOut);
		}
		myAnim.duration = 0.5;
		this.animating = true;
		myAnim.onComplete.subscribe(function(a, b, t) {
				if (t.showAccordion) {
					t.showAccordion.style.display = 'none';
				}
				t.currentAccordion.style.height = 'auto';
				
				// add active class to opened accordion
				if (t.options.classNames.activeHead && t.options.classNames.activeHead != '')
				{
					if (t.showAccordion) {
						$(t.showAccordion.parentNode).children("div:first").children("div:first").
							removeClass(t.options.classNames.activeHead);
					}
					$(t.currentAccordion.parentNode).children("div:first").children("div:first").
						addClass(t.options.classNames.activeHead);
				}
				
				// set the currently shown accordion
				t.showAccordion = t.currentAccordion;
				if (typeof t.options.save_url != "undefined" && t.options.save_url != "")
				{
					var tab_nr = 1;
					var cel = t.showAccordion.parentNode;
					while(cel = cel.previousSibling)
					{
						if (cel.nodeName.toUpperCase() == 'DIV')
						{
							tab_nr++;
						}
					}
					ilAccordionJSHandler(t.options.save_url + "&tab_nr=" + tab_nr);
				}
				
				t.animating = false;
			}, this);
		myAnim.onStart.subscribe(function(a, b, t) {
				t.currentAccordion.style.display = 'block';
			}, this);
		
		// fade out the currently shown accordion (showAccordion)
		if (this.showAccordion)
		{
			if (this.options.direction == 'vertical')
			{
				var myAnim2 = new YAHOO.util.Anim(this.showAccordion, { 
					height: { to: 0 }  
					}, 1, YAHOO.util.Easing.easeOut);
			}
			else
			{
				var myAnim2 = new YAHOO.util.Anim(this.showAccordion, { 
					width: { to: 0 }  
					}, 1, YAHOO.util.Easing.easeOut);
			}
			myAnim2.duration = 0.5;
			
			myAnim2.animate();

		}
		myAnim.animate();
	}
}

il.Util.addOnLoad(ilInitAccordions);

function ilInitAccordions()
{
	// run through accordions
	for (var i=0; i<ilAccordionData.length; i++)
	{
		ilInitAccordion(ilAccordionData[i][0], ilAccordionData[i][1],
			ilAccordionData[i][2], ilAccordionData[i][3], ilAccordionData[i][4],
			ilAccordionData[i][5], ilAccordionData[i][6], ilAccordionData[i][7],
			ilAccordionData[i][8], ilAccordionData[i][9], ilAccordionData[i][10]);
	}
}

function ilInitAccordionById(id)
{
	// run through accordions
	for (var i=0; i<ilAccordionData.length; i++)
	{
		if (ilAccordionData[i][10] == id)
		{
			ilInitAccordion(ilAccordionData[i][0], ilAccordionData[i][1],
				ilAccordionData[i][2], ilAccordionData[i][3], ilAccordionData[i][4],
				ilAccordionData[i][5], ilAccordionData[i][6], ilAccordionData[i][7],
				ilAccordionData[i][8], ilAccordionData[i][9], ilAccordionData[i][10]);
		}
	}
}

function ilInitAccordion(id, toggle_class, toggle_active_class, content_class, width, height, direction, behavior, save_url,
	active_head_class, int_id)
{
	if (behavior != "ForceAllOpen")
	{
		var acc = new accordion();
		acc.initialize(id, {
			classNames : {
				toggle : toggle_class,
				toggleActive : toggle_active_class,
				content : content_class,
				activeHead : active_head_class
			},
			defaultSize : {
				width : width,
				height : height
			},
			direction : direction,
			save_url : save_url
		});

	}
	if (behavior == "FirstOpen")
	{
		var a_el = YAHOO.util.Dom.getElementsByClassName(toggle_class, 'div', id);

		a1 = a_el[0];
		if (a1)
		{
			acc.clickHandler(null, a1);
		}
	}
	else if (!isNaN(behavior))	// open nth tab, if behaviour is a number
	{
		var a_el = YAHOO.util.Dom.getElementsByClassName(toggle_class, 'div', id);

		a1 = a_el[Number(behavior) - 1];
		if (a1)
		{
			acc.clickHandler(null, a1);
		}
	}
	
	return acc;
}
