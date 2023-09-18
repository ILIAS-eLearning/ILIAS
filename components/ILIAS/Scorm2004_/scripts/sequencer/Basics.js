function ilAugment (oSelf, oOther)
{
	if (oSelf == null)
	{
		oSelf = {};
	}
	for (var i = 1; i < arguments.length; i++)
	{
		var o = arguments[i];
		if (typeof(o) != 'undefined' && o != null)
		{
			for (var j in o)
			{
				oSelf[j] = o[j];
			}
		}
	}
	return oSelf;
}

function clone(what)
{
	for (i in what)
	{
		if (typeof(what[i]) == 'object')
		{
			this[i] = new cloneObject(what[i]);
		}
		else
			this[i] = what[i];
	}
}

function index_of(haystack, needle, start)
{
	var index = -1;
	if (start == null)
	{
		start = 0;
	}
	
	for(var j=start; j < haystack.length; j++)
	{
		if (haystack[j] != null &&
			haystack[j] == needle)
		{
			index = j;
			break;
		}
	}
	return index;
}

/**
* Log a Message
*/
function sclog(mess, type)
{
	elm = document.getElementById("ilLogPre");
	if (elm) 
	{
		elm.innerHTML = elm.innerHTML + mess + '<br />';
	}
}

/**
* Clear the Log
*/
function sclogclear()
{
	elm = all("ilLogPre");
	if (elm) 
	{
		elm.innerHTML = '';
	}
}


/**
* Dump a variable
*/
function sclogdump(param, depth)
{
	if (!depth)
	{
		depth = 0;
	}
	
	var pre = '';
	for (var j=0; j < depth; j++)
	{
		pre = pre + '    ';
	}
	
	//sclog(typeof param);
	switch (typeof param)
	{
		case 'boolean':
			if(param) sclog(pre + "true (boolean)"); else sclog(pre + "false (boolean)");
			break;

		case 'number':
			sclog(pre + param + ' (number)');
			break;

		case 'string':
			sclog(pre + param + ' (string)');
			break;

		case 'object':
			if (param === null)
			{
				sclog(pre + 'null');
			}
			if (param instanceof Array) sclog(pre + '(Array) {');
			else if (param instanceof Object) sclog(pre + '(Object) {');
			for (var k in param)
			{
				//if (param.hasOwnProperty(k)) // hasOwnProperty requires Safari 1.2
				//{
					if (typeof param[k] != "function")
					{
						sclog(pre + '[' + k + '] => ');
						sclogdump(param[k], depth + 1);
					}
				//}
			}
			sclog(pre + '}');
			break;
			
		case 'function':
			// we do not show functions
			break;

		default:
			sclog(pre + "unknown: " + (typeof param));
			break;
		
	}
}

