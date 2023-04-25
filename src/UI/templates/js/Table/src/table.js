import il from 'il';
import $ from 'jquery';
import Params from './params.js';
import Data from './table.data.js';
import Keyboardnav from './table.keyboardnav.js';

il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

il.UI.table.data = new Data(
    $,
    new Params(),
    new Keyboardnav()
);