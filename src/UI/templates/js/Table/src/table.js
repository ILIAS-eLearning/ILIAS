import params from './params.js';
import data from './table.data.js';
import keyboardnav from './table.keyboardnav.js';

il = il || {};
il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

il.UI.table.data = data(
	$,
	new params(),
	new keyboardnav()
);