var prop_width_height = new Object();

/** 
* Hide all ilFormHelpLink elements
*/
function ilPropWidthHeightSync(id)
{
	var post_var = id.substr(0, id.lastIndexOf("_"));
	var changed = id.substr(id.lastIndexOf("_") + 1);
	var width_el = document.getElementById(post_var + "_width");
	var height_el = document.getElementById(post_var + "_height");
	var constr_el = document.getElementById(post_var + "_constr");
	if (constr_el && constr_el.checked == true && prop_width_height[post_var] > 0)
	{
		if (changed == "width" && width_el && height_el)
		{
			height_el.value = Math.round(width_el.value / parseFloat(prop_width_height[post_var]));
		}
		if (changed == "height" && width_el && height_el)
		{
			width_el.value = Math.round(height_el.value * parseFloat(prop_width_height[post_var]));
		}
		if (changed == "constr" && width_el && height_el)
		{
			height_el.value = Math.round(width_el.value / parseFloat(prop_width_height[post_var]));
		}
	}
}

