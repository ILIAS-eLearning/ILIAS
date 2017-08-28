
	// SINGLETON:	namespace
	// PURPOSE:
	window.namespace	= new function()
	{
		// ------------------------------------------------------------------
		// METHOD:	_main (PRIVATE)
		// PURPOSE: Main namespace functionality loop
		// NOTES:	none
		// PARAMS:	[string]	sNamespace:	Namespace,
		//			[boolean]	bCreate:	Whether or not to create the namespace if it does not exists,
		//			[boolean]	bReturnObj:	Whether or not to return the namespace object
		// RETURNS: [boolean]	Either the namespace object or success boolean depending upon bReturnObj parameter
		function _main(sNamespace, bCreate, bReturnObj)
		{
			var oNamespace	= null;

			try
			{
				var i				= 0;
				var oParent			= window;
				var aNamespace		= sNamespace.split(/\./g);
				var nNamespaceCount	= aNamespace.length;
				var sNS;

				while (i < nNamespaceCount)
				{
					sNS	= aNamespace[i];
					if (typeof(sNS) != 'string' || sNS.length < 1)
					{
						throw new Error('Invalid namespace');
					}

					// If cerate flag parameter is true and the namespace does not exist, create it
					if (bCreate & !oParent[sNS])
					{
                        //alert(sNS);
						oParent[sNS]	= {};
					} else {

                    }

					// Set the parent object and increment the index for the next iteration
					oParent	= oParent[sNS];
					i++;
				}
				oNamespace	= oParent;
			}
			catch (ex)
			{
				return (bReturnObj === true)? null : false;
			}

			return (bReturnObj === true)? oNamespace : true;
		}

		// ------------------------------------------------------------------
		// METHOD:	create (PUBLIC)
		// PURPOSE: To retrieve a namespace object.
		// NOTES:	If namespace object does not exist, this method first creates it.
		// PARAMS:	[string]	sNamespace:	Namespace
		// RETURNS: [object]	Returns namespace object, null if there was a failure
		this.create		= function(sNamespace)
		{
			return _main(sNamespace, true, true);
		};

		// ------------------------------------------------------------------
		// METHOD:	exists (PUBLIC)
		// PURPOSE: Determines whether or not a namespace exists
		// NOTES:	none
		// PARAMS:	[string]	sNamespace:	Namespace
		// RETURNS: [boolean]	Namespace exists
		this.exists		= function(sNamespace)
		{
			return _main(sNamespace, false, false);
		};
	};

	namespace.create("YAHOO");

	function hasYME()
	{
		var ymeGrid;
		var hasYME = "0";
		try
		{
			ymeGrid = new ActiveXObject("YMP.YMPDatagrid.1");
			if (ymeGrid)
			{
				return true;
			}
		}
		catch (e)
		{
			return false;
		}
		return false;
	}

	function getWMP()
	{
		var windowsmedia = {};
		try
		{
			oWMP = new ActiveXObject('WMPlayer.OCX.7');
			if (oWMP)
			{
				windowsmedia.installed = true;
				windowsmedia.version=parseFloat(oWMP.versionInfo);
				if (windowsmedia.version.toString().length == 1) windowsmedia.version+= '.0';
			}
		}
		catch(e) {}
		return windowsmedia;
	}

	namespace.create("YAHOO.YMusic_Domains");
	namespace.create("YAHOO.Video");
	namespace.create("YAHOO.Radio");
	namespace.create("YAHOO.Sample");
	namespace.create("YAHOO.YME");
	namespace.create("YAHOO.WMP");

	//Yahoo Domains
	YAHOO.YMusic_Domains.video	=	"http://mv.us.music.yahoo.com";
	YAHOO.YMusic_Domains.radio	=	"http://radio.us.music.yahoo.com";
	YAHOO.YMusic_Domains.music	=	"http://music.yahoo.com";
	YAHOO.YMusic_Domains.sample	=	"http://sample.music.yahoo.com";


	//Video properties
	YAHOO.Video.defaultClientID	=	"1";

	//Radio properties
	YAHOO.Radio.defaultClientID	=	"1";

	//Sample properties
	YAHOO.Sample.isActive		=	"False";

	//YME properties
	YAHOO.YME.installed			=	hasYME();

	//WMP properties
	YAHOO.WMP					=	getWMP();

	// ------------------------------------------------------------------

	// Modified Adobe workaround for forced user activation of ActiveX object in IE

	function AC_Generateobj(objAttrs, params, embedAttrs, doc)
	{
		var str = ['<object '];

		for (var i in objAttrs)
		{
		  str[str.length] = i + '="' + objAttrs[i] + '" ';
		}
		str[str.length] = '>';
		for (var i in params)
		{
		  str[str.length] = '<param name="' + i + '" value="' + params[i] + '" /> ';
		}
		str[str.length] = '<embed ';
		for (var i in embedAttrs)
		{
		  str[str.length] = i + '="' + embedAttrs[i] + '" ';
		}
		str[str.length] = ' ></embed></object>';

        if(doc)
        {
            doc.write(str.join(''));
        }
		else
		{
		    document.write(str.join(''));
		}
	}
	function AC_FL_RunContent()
	{
		var ret = AC_GetArgs ( arguments, "flash" );
		AC_Generateobj(ret.objAttrs, ret.params, ret.embedAttrs, ret.document);
	}

	function AC_WMP_RunContent()
	{
		var ret = AC_GetArgs( arguments, "wmp" );
		AC_Generateobj(ret.objAttrs, ret.params, ret.embedAttrs);
	}

	function AC_GetArgs(args, objectType)
	{
		switch (objectType)
		{
			case "flash":
				var classid = "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000";
				var mimeType = "application/x-shockwave-flash";
				var codebase = "http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0";
				break;
			case "wmp":
				var classid = "CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95";
				var mimeType = "application/x-oleobject";
				var codebase = "http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701";
				break;
			default:
		}

		 var ret = new Object();
		 ret.embedAttrs = new Object();
		 ret.params = new Object();
		 ret.objAttrs = new Object();
		 for (var i=0; i < args.length; i=i+2)
		 {
			var currArg = args[i].toLowerCase();
			switch (currArg)
			{
				case "classid":
					break;
				case "pluginspage":
					ret.embedAttrs[args[i]] = args[i+1];
					break;
				case "src":
				case "movie":
					ret.embedAttrs[args[i]] = args[i+1];
					if (objectType == "flash")
					{
						ret.params["movie"] = args[i+1];
					}
					break;
				case "onblur":
				case "oncellchange":
				case "onclick":
				case "ondblClick":
				case "ondrag":
				case "ondragend":
				case "ondragenter":
				case "ondragleave":
				case "ondragover":
				case "ondrop":
				case "onfinish":
				case "onfocus":
				case "onhelp":
				case "onmousedown":
				case "onmouseup":
				case "onmouseover":
				case "onmousemove":
				case "onmouseout":
				case "onkeypress":
				case "onkeydown":
				case "onkeyup":
				case "onload":
				case "onlosecapture":
				case "onpropertychange":
				case "onreadystatechange":
				case "onrowsdelete":
				case "onrowenter":
				case "onrowexit":
				case "onrowsinserted":
				case "onstart":
				case "onscroll":
				case "onbeforeeditfocus":
				case "onactivate":
				case "onbeforedeactivate":
				case "ondeactivate":
				case "type":
				case "codebase":
					ret.objAttrs[args[i]] = args[i+1];
					break;
				case "width":
				case "height":
				case "align":
				case "vspace":
				case "hspace":
				case "class":
				case "title":
				case "accesskey":
				case "name":
				case "id":
				case "tabindex":
					ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];
					break;
				case "document":
				    ret.document = args[i+1];
				    break;
				default:
					ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
			}
	  }
	  if (classid) ret.objAttrs["classid"] = classid;
	  if (mimeType) ret.embedAttrs["type"] = mimeType;
	  return ret;
	}
