import drilldown from './drilldown.instances.js';
import dd from './drilldown.main.js';
import ddmodel from './drilldown.model.js';
import ddmapping from './drilldown.mapping.js';
import ddpersistence from './drilldown.persistence.js';


il = il || {};
il.UI = il.UI || {};
il.UI.menu = il.UI.menu || {};

il.UI.menu.drilldown = drilldown(
 	ddmodel,
	ddmapping,
	ddpersistence,
	dd
);