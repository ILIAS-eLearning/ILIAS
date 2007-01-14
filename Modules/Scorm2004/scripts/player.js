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
(function (cp, api, cmi, remoting, gui) {
	var x = 123;
	
	/**
	 * recursive walk through activity tree
	 * to build up an item map to get items by id
	 * use map(something) to populate it
	 * use map[someIdentifier] to retrive an element
	 * should it also include other identifiers (manifest, organization, sequencing)?	 
	 * @param array Items of organization
	 */
	function map(items) {
		for (var i=0, ni=items.length; i<ni; i+=1) {
			var itm = items[i];
			map[itm.id] = itm;
			if (itm.item) {
				map(itm.item);
			}
		}
	}
	
	function toDataset(cmiitem) {
		return cmiitem;
	}
	
	function fromDataset(dataset) {
		return dataset;
	}  

	/**
	 * navigation request handler	
	 */	
	var nav = new function () {
		this.Start = function (id) {alert(['nav', id]);return false;};
		this.ResumeAll = function (id) {alert(['nav', id]);return false;};
		this.Continue = function (id) {alert(['nav', id]);return false;};
		this.Previous = function (id) {alert(['nav', id]);return false;};
		this.Forward = function (id) {alert(['nav', id]);return false;};
		this.Backward = function (id) {alert(['nav', id]);return false;};
		this.Exit = function (id) {alert(['nav', id]);return false;};
		this.ExitAll = function (id) {alert(['nav', id]);return false;};
		this.Abandon = function (id) {alert(['nav', id]);return false;};
		this.AbandonAll = function (id) {alert(['nav', id]);return false;};
		this.SuspendAll = function (id) {alert(['nav', id]);return false;};
	}
	
	var nav = new OP_SCORM_SEQUENCING_1_3( //manifest, cmidata, ondeliver, ondebug
		cp, // manifest
		cmi, // cmidata
		function (item) // ondeliver
		{
			var url = item.href;
			window[api.prototype.name] = new api( // cmiItem, nonSco, onCommit, onDebug
				Remoting.copyOf(cmi[item.id]), // cmiItem
				item.sco==1, // nonSco
				function (modifiedItem) // onCommit 
				{
					cmi[item.id] = modifiedItem; 				
				},
				function (diagnostic, returnValue, errCode, errInfo) // onDebug
				{
					gui.onAPIDebug([diagnostic, returnValue, errCode, errInfo].join(', '));
				}			
			);
			gui.deliver(item.href);
		},
		gui.onSequencerDebug
	);
	
	gui.attachEvent(window, 'load',   
	
		/**
		 * start the system
		 */	
		function () { 
			
			// inner functions
			
			function onChoice(target) {
				if (target.className.indexOf('content')!==-1) {
					var itm = map[target.id.substr(3)];
					if (itm.href) {
						if (true) {
							alert(itm.scormType)
							window[api.prototype.name] = new api(cmi[itm.id], function (event, value) {
								alert([event, value, itm.id])
							}); 
						}
						gui.deliver(itm.href + (itm.href.indexOf('?')===-1 ? '?' : '&') + 'id=' + itm.id);
					} else {
						
					}
				}
			}

			// populate id map from content package object
			map(cp.item.item);
	
			// convert dataset into cache
			cmi = fromDataset(cmi);
			
			gui.attachEvent(window, 'unload', function () {
				// saveData alert("unload");
			});
			   
			gui.attachEvent(document, 'click', function (e) {
				var target = e ? (e.target ? e.target : e.srcElement) : event.srcElement;
				if (target.tagName !== 'A') {
				} else if (target.className === 'btn') {
					//nav[target.id.substr(3)](target.id);
					document.title = nav.execNavigation({type: target.id.substr(3), target: target.id});
				} else if (target.className.indexOf('nde ')!==-1) {
					onChoice(target);
				}
				return false;
			});
			
			//var r = remoting.sendAndLoad('bla');
			
			gui.render(cp.item, cp.base);
			
			gui.all('listView').onchange = function () {
				onChoice(this.options[this.selectedIndex]);
			}
			
	});
	

	setTimeout(function () {nav.execNavigation({type: 'Start'})}, 1000)


})(
	Package, 
	OP_SCORM_RUNTIME, 
	Userdata, 
	Remoting, 
	Gui
);


/*
OP_SCORM_RUNTIME_1_3.prototype.toDataset = function (cmiitem) {
	// TODO implement this
	return cmiitem;
}


OP_SCORM_RUNTIME_1_3.prototype.fromDataset = function (dataset) {
	// TODO implement this
	return dataset;
}
*/
