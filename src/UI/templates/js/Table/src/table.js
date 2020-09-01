import params from './params.js';
import data from './table.data.js';

il = il || {};
il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

il.UI.table.data = data(
	new params(),
	$
);