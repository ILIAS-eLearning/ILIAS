/**
* Adds a function to the window onload event
*/
function ilAddOnLoad(func)
{
	if (!document.getElementById | !document.getElementsByTagName) return
	
	var oldonload=window.onload
	if (typeof window.onload != 'function')
	{
		window.onload = func
	}
	else
	{
		window.onload = function()
		{
			oldonload();
			func()
		}
	}
}

/**
* Adds a function to the window unonload event
*/
function ilAddOnUnload(func)
{
	if (!document.getElementById | !document.getElementsByTagName) return
	
	var oldonunload = window.onunload
	if (typeof window.onunload != 'function')
	{
		window.onunload = func
	}
	else
	{
		window.onunload = function()
		{
			oldonunload();
			func()
		}
	}
}
