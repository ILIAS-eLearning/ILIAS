import $ from 'jquery';
import il from 'il';
import DataTableFactory from './datatable.factory';
import Params from './Params';

il.UI = il.UI || {};
il.UI.table = il.UI.table || {};
il.UI.table.data = new DataTableFactory($, new Params());
