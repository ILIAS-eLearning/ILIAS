import drilldown from './drilldown.main.js';
import ddmodel from './drilldown.model.js';
import ddmapping from './drilldown.mapping.js';


il = il || {};
il.UI = il.UI || {};
il.UI.menu = il.UI.menu || {};

il.UI.menu.drilldown = drilldown(
 	ddmodel(),
	ddmapping()
);