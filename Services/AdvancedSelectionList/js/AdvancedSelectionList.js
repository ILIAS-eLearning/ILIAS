ilAddOnLoad(ilInitAdvSelectionLists);
var il_adv_sel_lists = new Array();

/** 
* Init selection lists
*/
function ilInitAdvSelectionLists()
{
	for (var i = 0; i < il_adv_sel_lists.length; ++i)
	{
		id = il_adv_sel_lists[i];

		// hide non-js section
		obj = document.getElementById('ilAdvSelListNoJS_' + id);
		if (obj)
			obj.style.display='none';
		
		// show js section
		obj = document.getElementById('ilAdvSelListJS_' + id);
		if (obj)
			obj.style.display='block';
	
		// show placeholder
		obj = document.getElementById('ilAdvSelListPH_' + id);
		if (obj)
			obj.style.display='block';
	}
}

/**
* Show selection list
*/
function ilAdvSelListOn(id)
{
	obj = document.getElementById('ilAdvSelListTable_' + id);
	obj.style.display='';
}

/**
* Hide selection list
*/
function ilAdvSelListOff(id)
{
	obj = document.getElementById('ilAdvSelListTable_' + id);
	obj.style.display='none';
}
