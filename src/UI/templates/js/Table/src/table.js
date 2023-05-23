import il from 'il';
import $ from 'jquery';
import Params from './Params';
import Data from './Data';

il.UI = il.UI || {};
il.UI.table = il.UI.table || {};

il.UI.table.data = new Data(
  $,
  new Params(),
);
