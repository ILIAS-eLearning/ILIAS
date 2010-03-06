var ilOverlayFunc = function() {
};
ilOverlayFunc.prototype =
{
	overlays: {},
	props: {},
	
	add: function (id, ov, tr_id, tr_ev)
	{
		ilOverlayFunc.prototype.overlays[id] = ov;
		ilOverlayFunc.prototype.props[id] = {trigger: tr_id, trigger_event: tr_ev};
		
		if (tr_id != "")
		{
			var trigger = document.getElementById(tr_id);
			YAHOO.util.Event.addListener(trigger, "click",
					function(event) {ilOverlay.toggle(event, id); return false;});
		}
		ov.render();
		this.fixPosition(id);
	},
	
	toggle: function (e, id)
	{
		//console.log("Toggle");
		//console.log(ilOverlayFunc.prototype.overlays[id].cfg.getProperty('visible'));
		if (ilOverlayFunc.prototype.overlays[id].cfg.getProperty('visible'))
		{
			//console.log("Hide");
			ilOverlayFunc.prototype.overlays[id].hide();
		}
		else
		{
			//console.log("Show");
			ilOverlayFunc.prototype.overlays[id].show();
			this.fixPosition(id);
			YAHOO.util.Event.stopPropagation(e);
		}
	},
	
	fixPosition: function(id)
	{
		var el = document.getElementById(id);
		var el_reg = YAHOO.util.Region.getRegion(el);
		var cl_reg = YAHOO.util.Dom.getClientRegion();
		
		el.style.overflow = '';
		
		// make it smaller, if window height is not sufficient
		if (cl_reg.height < el_reg.height + 20)
		{
			var newHeight = cl_reg.height - 20;
			if (newHeight < 150)
			{
				newHeight = 150;
			}
			el.style.height = newHeight + "px";
			el.style.width = el_reg.width + 20 + "px";
			el_reg = YAHOO.util.Region.getRegion(el);
		}
		
		// to low -> show it higher
		if (cl_reg.bottom < el_reg.bottom)
		{
			YAHOO.util.Dom.setY(el, el_reg.y - (el_reg.bottom - cl_reg.bottom));
			el_reg = YAHOO.util.Region.getRegion(el);
		}
		
		// to far to the right -> show it more to the left
		if (cl_reg.right < el_reg.right)
		{
			YAHOO.util.Dom.setX(el, el_reg.x - (el_reg.right - cl_reg.right));
		}
		
		el.style.overflow = 'auto';
	},
	
	hideAllOverlays: function (e) {
		for (var k in ilOverlayFunc.prototype.overlays)
		{
			var el = document.getElementById(k);
			var el_reg = YAHOO.util.Region.getRegion(el);
//console.log(e.pageY + "," + e.pageX);
			if (!el_reg.contains(new YAHOO.util.Point(e.pageX , e.pageY)))
			{
				ilOverlayFunc.prototype.overlays[k].hide();
			}
		}
	}

};
var ilOverlay = new ilOverlayFunc();
YAHOO.util.Event.addListener(document, "click",
	function(e) {ilOverlay.hideAllOverlays(e)});
