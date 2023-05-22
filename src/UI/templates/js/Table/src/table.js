import il from 'il';
import $ from 'jquery';
import Params from './Params';
import Data from './Data';
import Keyboardnav from './Keyboardnav';

il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

il.UI.table.data = new Data(
  $,
  new Params(),
  new Keyboardnav(),
);
