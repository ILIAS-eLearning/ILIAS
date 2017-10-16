

// scrolling, mix of registerd images and class-name images
var downGroup = new YAHOO.util.ImageLoader.group(window, 'scroll', null);
downGroup.foldConditional = true;
downGroup.registerBgImage('waydown', 'http://us.i1.yimg.com/us.yimg.com/i/ar/sp/fifa/rooney77x42.jpg');
downGroup.registerBgImage('waydown2', 'http://us.i1.yimg.com/us.yimg.com/i/ar/sp/fifa/rooney77x42.jpg');
downGroup.registerBgImage('waydown3', 'http://us.i1.yimg.com/us.yimg.com/i/ar/sp/fifa/rooney77x42.jpg');
downGroup.className = 'waydownCF';
downGroup.addTrigger(window, 'resize');
downGroup.name = 'conditional';

downGroup.fetch = function() { this._foldCheck(); }
