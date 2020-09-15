import mainbar  from './mainbar.main.js';
import model  from './mainbar.model.js';
import persistence  from './mainbar.persistence.js';
import renderer  from './mainbar.renderer.js';

il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

il.UI.maincontrols.mainbar = mainbar();
il.UI.maincontrols.mainbar.model = model();
il.UI.maincontrols.mainbar.persistence = persistence();
il.UI.maincontrols.mainbar.renderer = renderer($);