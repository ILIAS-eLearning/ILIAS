/**
 * Select all checkbox
 * @param {Object} a_form
 * @param {Object} a_varname
 * @param {Object} a_elements
 */
function setCheckboxes(a_form, a_varname,a_type, a_elements)
{
	if (document.forms[a_form].elements['select_' + a_varname + '_' + a_type].checked == false)
		check = false;
	else
		check = true;

	for(i=0;i<a_elements.length;i++) 
	{
		if (typeof(document.forms[a_form].elements['perm_' + a_varname + '_' + a_elements[i]]) != 'undefined') 
		{
    		document.forms[a_form].elements['perm_' + a_varname + "_" + a_elements[i]].checked = check;
    	}
    }

    return true;
}
