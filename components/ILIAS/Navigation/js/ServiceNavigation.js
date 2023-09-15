// remove non-javascript form with java script table
il.Util.addOnLoad(ilInitLastVisitedNav);

/** 
* Last visited navigation
*/
function ilInitLastVisitedNav()
{
	// get all spans
	obj = document.getElementById('ilNavHistory');
	if (obj)
		obj.style.display='none';
	
	// get all spans
	obj = document.getElementById('ilNavHistoryDiv');
	if (obj)
		obj.style.display='block';

	// get all spans
	obj = document.getElementById('ilNavHistoryDivPH');
	if (obj)
		obj.style.display='block';

}

/**
* Show last visited table
*/
function ilLastVisitedNavOn()
{
	obj = document.getElementById('ilNavHistoryTable');
	obj.style.display='';
}

/**
* Hide last visited table
*/
function ilLastVisitedNavOff()
{
	obj = document.getElementById('ilNavHistoryTable');
	obj.style.display='none';
}
