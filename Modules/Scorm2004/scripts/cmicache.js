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
 * Derived from ADL Pseudocode
 *   
 * Content-Type: application/x-javascript; charset=ISO-8859-1
 * Modul: Cache for CMI Tracking 
 *  
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2007 Alfred Kohnert
 */ 

function CMICache (url, size, time) 
{
	var data, schema, lastId, map, dirty;
	var me = this;
	
	function getDirty(cp_node_id) 
	{
		return dirty['@' + cp_node_id]!==undefined;
	}

	function setDirty(cp_node_id, newValue) 
	{
		var k = '@' + cp_node_id;
		var n = dirty[k];
		if (newValue===true && n==undefined) 
		{
			dirty[k] = dirty.length;
			dirty.push(k);
		} 
		else if (newValue===false && n!==undefined) 
		{
			delete dirty[n];
			delete dirty[k];
		} 
	}
	
	function array_keys(obj) 
	{
		var r = [];
		for (var k in obj) 
		{
			r.push(k);
		}
		return r;
	}
	
	function mapData()
	{
		var k, i, ni, idx;
		for (var k in schema) 
		{
			for (i=0, ni = schema[k].length; i<ni; i++)
			{
				schema[k][schema[k][i]] = i;
			}
			if (k==='package') continue;
			map[k] = {};
			for (i=0, ni=data[k].length; i<ni; i++)
			{
				idx = schema[k]['cmi_' + k + '_id'];
				map[k][data[k][i][idx]] = i;
			}		
		}
		map.cp = {};
		for (i=0, ni=data.node.length; i<ni; i++)
		{
			map.cp[data.node[i][schema.node.cp_node_id]] = i;
		}
	} 
	
	function getAPI (cp_node_id)
	{
		function indentNode(row, nodeName, keys) 
		{
			var k, d = {};
			for (var i=0, ni=keys.length; i<ni; i+=1) 
			{
				k = keys[i];
				d[k] = row[k];
				delete row[k];
			}
			if (d) row[nodeName] = d;
		}
		
		function getDataNodes(rows, table, name, value)
		{
			var row, result = [];
			var k = schema[table][name];
			for (var i=0, ni=rows.length; i<ni; i++)
			{
				row = rows[i];
				if (row[k]==value) 
				{
					result.push(rows[i]);
				}
			}
			return result;
		}
		
		function process(container, table, foreign, value)
		{
			var k, node, elm;
			var nodes = getDataNodes(data[table], table, foreign, 
				value===undefined ? container[foreign] : value);
			for (var n=0, nn=nodes.length; n<nn; n++)
			{
				node = nodes[n];
				elm = {};
				for (var i=0, ni=node.length; i<ni; i++)
				{
					elm[schema[table][i]] = node[i];
				}
				k = table + 's';
				switch (table)
				{
					case 'node':
						k = 'cmi';
						indentNode(elm, 'score', ['raw', 'min', 'max', 'scaled']);
						indentNode(elm, 'learner_preference', ['audio_captioning', 'audio_level', 'delivery_speed', 'language']);
						process(elm, 'comment', 'cmi_node_id');
						process(elm, 'interaction', 'cmi_node_id');
						process(elm, 'objective', 'cmi_node_id');
						break;
					case 'comment':
						k = elm.sourceIsLMS==1 ?  'comments_from_lms' : 'comments_from_learner';
						break;
					case 'interaction':
						process(elm, 'correct_response', 'cmi_interaction_id');
						process(elm, 'objective', 'cmi_interaction_id');
						break;
					case 'objective':
						if (foreign==='cmi_node_id' && elm.cmi_interaction_id != 0) continue;
						indentNode(elm, 'score', ['raw', 'min', 'max', 'scaled']);
						break;
				}
				if (value!==undefined)
				{
					container[k] = elm;
				}
				else
				{
					if (!container[k]) 
					{
						container[k] = [];
					}
					container[k].push(elm);
				}
			}
		}
	
		// create api data element with some starting values
		var api = {'cmi' : {'cp_node_id': cp_node_id}};
		
		// start recursive process to add current cmi subelements
		process(api, 'node', 'cp_node_id', cp_node_id);
		
		// add some readonly default values for all sco's in package
		var k;
		api.cmi[k="credit"] = data['package'][0][schema['package'][k]]; 
		api.cmi[k="learner_name"] = data['package'][0][schema['package'][k]]; 
		api.cmi[k="learner_id"] = data['package'][0][schema['package']['user_id']]; 
		api.cmi[k="mode"] = data['package'][0][schema['package'][k]]; 
		
		return api;
	
	}
	
	function setAPI (cp_node_id, api, clean)
	{
		var tables = {
			correct_responses: "correct_response",
			interactions: "interaction",
			comments_from_learner: "comment",
			comments_from_lms: "comment",
			cmi: "node",
			objectives: "objective"
		};
		function outdentNode(row, nodeName, keys) 
		{
			var k, d = row[nodeName];
			if (!d) return;
			for (var i=0, ni=keys.length; i<ni; i+=1) 
			{
				k = keys[i];
				row[k] = d[k]
			}
			delete row[nodeName];
		}
		
		function process(container, element, foreign, value)
		{
			var k, t, elm, idx;
			var node;
			var elms = container[element];
			var table = tables[element];
			if (!elms) 
			{
				return;
			}
			else if (!(elms instanceof Array)) 
			{
				elms = [elms];
			}
			for (var n=0, nn=elms.length; n<nn; n++)
			{
				node = new Array();
				elm = elms[n];
				elm[foreign] = value ? value : container[foreign];

				idx = elm['cmi_' + table + '_id'];
				if (idx===undefined)
				{
					idx = "$" + lastId++;
					elm['cmi_' + table + '_id'] = idx; 
					map[table][idx] = data[table].length;
					if (table==="node") 
					{
						map.cp[elm.cp_node_id] = data[table].length;;
					}
				}
				idx = map[table][idx];

				switch (element)
				{
					case 'correct_responses':
						break;
					case 'interactions':
						process(elm, 'correct_responses', 'cmi_interaction_id');
						process(elm, 'objectives', 'cmi_interaction_id');
						break;
					case 'comments_from_learner':
						elm.sourceIsLMS = 0;
						break;
					case 'comments_from_lms':
						elm.sourceIsLMS = 1;
						break;
					case 'cmi':
						outdentNode(elm, 'score', ['raw', 'min', 'max', 'scaled']);
						outdentNode(elm, 'learner_preference', ['audio_captioning', 'audio_level', 'delivery_speed', 'language']);
						process(elm, 'comments_from_lms', 'cmi_node_id');
						process(elm, 'comments_from_learner', 'cmi_node_id');
						process(elm, 'interactions', 'cmi_node_id');
						process(elm, 'objectives', 'cmi_node_id');
						break;
					case 'objectives':
						outdentNode(elm, 'score', ['raw', 'min', 'max', 'scaled']);
						break;
				}
				for (var i=0, ni=schema[table].length; i<ni; i++)
				{
					t = elm[schema[table][i]];
					node.push(t!==undefined ? t : null);
				}
				data[table][idx] = node; // REPLACE mode
			}
		}
	
		process(api, 'cmi', 'cp_node_id', cp_node_id);

		if (!clean) 
		{
			setDirty(cp_node_id, true);
		}

		if (typeof size === "number" && dirty.length > size) 
		{
			return me.save();
		} 
		else
		{
			return true;
		}
		
	}
	
	this.getValue = function (cp_node_id, key)
	{
		var i = schema.node[key];
		var elm = data.node[map.cp[cp_node_id]];
		if (elm && i!==undefined) 
		{
			return elm[i];
		}
	}
	
	// get value in LAST instance index of objective with an specified ID
	this.getObjectiveValue = function (id, key)
	{
		var kid = schema.objective[key];
		var iid = schema.objective.id;
		for (var i=data.objective.length-1; i>-1; i--)
		{
			if (data.objective[i][iid] == id)
			{
				return data.objective[i][kid];
			}
		}
	}
	
	this.setValue = function (cp_node_id, key, value)
	{
		var elm;
		var key = schema.node[key];
		var idx = map.cp[cp_node_id];
		if (idx===undefined) 
		{
			elm = [];
			for (var i=0, ni=schema.node.length; i<ni; i++) elm[i] = null;
			var t = "$" + lastId++;
			idx = data.node.length;
			map.node[t] = idx
			map.cp[cp_node_id] = idx;
			elm[schema.node.cmi_node_id] = t;
			elm[schema.node.cp_node_id] = cp_node_id;
			data.node.push(elm);
		}
		var elm = data.node[idx];
		if (elm && key!==undefined) 
		{
			elm[key] = value;
			setDirty(cp_node_id, true);
		}
	}
	
	this.getAPI = function(cp_node_id)
	{
		return getAPI(cp_node_id);
	}; 

	this.setAPI = function(cp_node_id, value)
	{
		return setAPI(cp_node_id, value, false);
	}; 

	this.save = function (cp_node_ids)
	{
		function process(sink, cmi_node_no)
		{
			var cmi_node_id = data.node[cmi_node_no][schema.node.cmi_node_id];
			for (var k in schema)
			{
				var idx = schema[k].cmi_node_id;
				if (idx===undefined) continue;
				// TODO: add missing third level elements
				for (var i=0, ni=data[k].length; i<ni; i++)
				{
					if (data[k][i] && data[k][i][idx] == cmi_node_id)  
					{
						if (!(k in sink)) 
						{
							sink[k] = [];
						}
						sink[k].push(data[k][i]);
					}
				}
			}
		}
		if (me.timeout) 
		{
			clearTimeout(me.timeout);
		}
		var r = {};
		if (!cp_node_ids) 
		{
			cp_node_ids = array_keys(map.cp);
		}
		else if (typeof cp_node_ids === "string") 
		{
			cp_node_ids = [cp_node_ids];
		}
		for (var i=cp_node_ids.length-1; i>-1; i--) 
		{
			if (getDirty(cp_node_ids[i])) 
			{
				this.setValue(cp_node_ids[i], 'accessed', new Date());
				process(r, map.cp[cp_node_ids[i]]);
			}
		}
		if (!r) return; 
		r = Remoting.sendJSONRequest(url, r);
		// set successful updated elements to cleans
		i=0;
		for (var k in r) 
		{
			i++;
			setDirty(k, false);
		}
		if (typeof time === "number" && time>10) 
		{
			clearTimeout(me.timeout);
			me.timeout = setTimeout(me.save, time*1000);
		}
		return i;
	}; 

	this.load = function ()
	{
		var json = Remoting.sendJSONRequest(url);
		if (!json) return false;
		data = json.data;
		schema = json.schema;
		lastId = 0;
		map = {};
		dirty = [];
		mapData();
		if (typeof time === "number" && time>10) 
		{
			me.timeout = setTimeout(me.save, time*1000);
		}
		return true;
	}; 
	
}
