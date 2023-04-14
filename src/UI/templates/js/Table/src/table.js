import il from 'il';
import $ from 'jquery';
import params from './params.js';
import data from './table.data.js';
import keyboardnav from './table.keyboardnav.js';

il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

il.UI.table.data = new data(
	$,
	new params(),
	new keyboardnav()
);