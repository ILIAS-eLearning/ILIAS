/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * You must not remove this notice, or any other, from this software.
 * 
 * PRELIMINARY EDITION
 * This is work in progress and therefore incomplete and buggy ...
 * 
 * Content-Type: application/x-javascript; charset=ISO-8859-1
 * Modul: Player User Interface Methods
 *  
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2007 Alfred Kohnert
 */ 

 

/**
 * Initialization of all relevant player internal objects.
 * This is done in a closure, so not unneccessarily populating global symbol table of javascript 
 * @param object Activity tree (one organization only). 
 * @param function reference Constructor for SCORM API instances on individual items
 * @param object User data from previous tracking sessions as CMIDataStore object.   
 * @param function reference function to send and load data via xmlhttp interface
 * @param object collection of methods to manipulate user view    
 */  
function Player(config, gui) 
{
	
	// inner functions 

	/**
	 * recursive walk through activity tree
	 * to build up an item map to get items by id
	 * use map(something) to populate it
	 * use map[someIdentifier] to retrive an element
	 * identifiers may be orginal manifest id attributes or database cp_node_id's 	 
	 * should it also include other identifiers (manifest, organization, sequencing)?	 
	 * @param array Items of organization
	 */
	function map(items) 
	{
		for (var i=0, ni=items.length; i<ni; i+=1) 
		{
			var itm = items[i];
			map[itm.id] = itm;
			map[itm.foreignId] = itm;			
			if (itm.item) 
			{
				map(itm.item);
			}
		}
	}

	function onItemDeliver(item) // ondeliver called from sequencing process (deliverSubProcess)
	{
		var url = item.href, v;
		// create api if associated resouce is of adl:scormType=sco
		if (item.sco)
		{
			var data = cmidata.getAPI(item.foreignId);
			// add user independend general item values to api
			if (v = item.completionThreshold) data.cmi.completionThreshold = v;
			if (v = item.dataFromLMS) data.cmi.launch_data = v;			
			if (v = item.timeLimitAction) data.cmi.time_limit_action = v;			
			
			// assign api for public use from sco
			window[api.prototype.name] = new api(data, onCommit, onTerminate, gui.onAPIDebug);
		}
		// customize GUI Step 1: adlnav
		var hideLMSUI = {};
		if (item.hideLMSUI) 
		{
			for (var i=0, ni=item.hideLMSUI.length; i<ni; i++) 
			{
				hideLMSUI[item.hideLMSUI[i].value] = true;
			}
		}
		// customize GUI Step 2: imsss
		var seq = item.parentActivity.sequencing;
		if (!seq.flow) hideLMSUI['continue'] = true; 
		if (!seq.flow || seq.forwardOnly) hideLMSUI['previous'] = true; 
		// deliver resource (sco)
		gui.deliver(item.id, item.href, hideLMSUI);
	}
	
	function onItemUndeliver(item) // onundeliver called from sequencing process (EndAttempt)
	{
		// throw away the resource
		// it may change api data in this
		gui.undeliver();
		// throw away api
		window[api.prototype.name] = null;
	}
	
	// sequencer terminated
	function onNavEnd()
	{
		// take away the player, here close window
		// TODO set this in Gui Handler
		gui.show(false);
		/*
		document.body.innerHTML = 'Finished';
		window.close();
		*/
	}
	
	function onCommit(data) 
	{
		return cmidata.setAPI(data.cmi.cp_node_id, data);
	}
	
	function onTerminate(data) 
	{
		// samples for data.adl.nav: "continue", "{target=myuri}choice"
	   if (data.adl && data.adl.nav && typeof data.adl.nav.request === "string")
	   {
	   	var m = data.adl.nav.request.match(/^(\{target=([^\}]+)\})?(choice|continue|previous|exit(All)?|abandon(All)?)$/);
	   	if (!m) return;
	      setTimeout(function () {
				nav.execNavigation({
					type: m[3].substr(0, 1).toUpperCase() + m[3].substr(1), 
					target: m[2]
				});
			}, 0);
		}
		return true;
	}
	
	function onChoice(target) 
	{
		if (target.className.indexOf('content')!==-1) {
			nav.execNavigation({type: 'Choice', target: target.id.substr(3)});
		}
	}

	function onWindowUnload () 
	{ 
		gui.show(false);
		cmidata.save();
	}

	function onDocumentClick (e) 
	{
		var target = e ? (e.target ? e.target : e.srcElement) : event.srcElement;
		gui.stopEvent(e);
		if (target.tagName !== 'A') 
		{
			// ignore clicks on other elements than A
		} 
		else if (target.className === 'btn') 
		{
			if (typeof window[target.id + '_onclick'] === "function")
			{
				return window[target.id + '_onclick']();
			}
			else if (target.id.substr(0, 3)==='api')
			{
				var api = parent[OP_SCORM_RUNTIME.prototype.name];
				if (!api) {
					alert(OP_SCORM_RUNTIME.prototype.name + " not found");
					return false; 
				}  
				var btn = target.id.substr(3);
				var f = document.forms[0];
				if (typeof(api[btn])==="function") {
					f.cmireturn.value = api[btn](f.cmielement.value, f.cmivalue.value);
					f.cmidiagnostic.value = api.GetDiagnostic("");
					f.cmierror.value = api.GetLastError("");
					f.cmidiagnostic.value = api.GetErrorString("");
				} else {
					alert(['not found', btn])
				} 
			}
			else if (target.id.substr(0, 3)==='nav')
			{
				document.title = nav.execNavigation({type: target.id.substr(3), target: target.id});
			}
			else
			{
				alert(target.id)
			}
		} 
		else if (target.className.indexOf('nde ')!==-1) 
		{
			onChoice(target);			
		}
	}
		
	function onWindowLoad () 
	{ 
		// load content package as json data
		gui.setInfo('Loading content package...');
		cpdata = Remoting.sendJSONRequest(config.cp);
		// populate id map from content package object
		map(cpdata.item.item);
	
		// load userdata as json data into cache 
		gui.setInfo('Loading user data...');
		cmidata = new CMICache(config.cmi, 0, 0);
		// load dataset into cache
		cmidata.load();
		
		// finishing startup
		gui.setInfo('');
	
		api = OP_SCORM_RUNTIME;
		nav = new OP_SCORM_SEQUENCING_1_3( //manifest, cmidata, ondeliver, ondebug
			cpdata, // manifest
			cmidata, // cmidata
			onItemDeliver, 
			onItemUndeliver, 
			onNavEnd,
			gui.onSequencerDebug
		);
		
		// 
		gui.attachEvent(window, 'unload', onWindowUnload);
		gui.attachEvent(document, 'click', onDocumentClick);
		gui.render(cpdata.item, cpdata.base);
		gui.all('listView').onchange = function () {
			onChoice(this.options[this.selectedIndex]);
		};
		gui.show(true);
		nav.startOrResume();
	}
	
	this.commit = function () 
	{
		cmidata.save();
	}
	
	// inner variables and initialization 
	var me = this;
	var cpdata, cmidata, api, nav;
	
	
	gui.attachEvent(window, 'load', onWindowLoad);
	
}
