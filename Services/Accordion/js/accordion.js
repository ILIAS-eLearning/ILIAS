// inspired by accordion.js v2.0, Copyright (c) 2007 stickmanlabs
// Author: Kevin P Miller | http://www.stickmanlabs.com
// MIT-style licensed
//
// Complete rewrite using YUI instead of prototype by Alex Killing for
// ILIAS open source


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
				t.showAccordion.style.height = 'auto';
				t.showAccordion.style.display = 'none';
				t.showAccordion = null;
				t.animating = false;
			}, this);
		myAnim.animate();
	},

	//
	// Handle the open/close actions of the accordion
	//
	_handleAccordion : function() {

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
				t.showAccordion = t.currentAccordion;
				t.animating = false;
			}, this);
		myAnim.onStart.subscribe(function(a, b, t) {
				t.currentAccordion.style.display = 'block';
			}, this);
		
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

function ilInitAccordion(id, toggle_class, toggle_active_class, content_class, width, height, direction, behavior)
{
	if (behavior != "ForceAllOpen")
	{
		var acc = new accordion();
		acc.initialize(id, {
			classNames : {
				toggle : toggle_class,
				toggleActive : toggle_active_class,
				content : content_class
			},
			defaultSize : {
				width : width,
				height : height
			},
			direction : direction
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
	
	return acc;
}
