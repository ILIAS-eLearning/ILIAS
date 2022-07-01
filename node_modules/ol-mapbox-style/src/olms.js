import olms, {apply, applyBackground, applyStyle} from './index.js';
import stylefunction, {
  recordStyleLayer,
  renderTransparent,
} from './stylefunction.js';
import {assign} from './util.js';

assign(olms, {
  apply,
  applyBackground,
  applyStyle,
  stylefunction,
  recordStyleLayer,
  renderTransparent,
});

export default olms;
