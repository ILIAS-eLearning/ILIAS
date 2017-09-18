
var mainGroup = new YAHOO.util.ImageLoader.group('everything', 'mouseover', 2);
mainGroup.registerBgImage('topmain', 'http://us.i1.yimg.com/us.yimg.com/i/travel/tg/lp/42/240x240_42f4203a640ac50146b0fcd6e892a34f.jpg');
mainGroup.name = 'topmain';

// two images and two triggers
var firstGroup = new YAHOO.util.ImageLoader.group('hoverme', 'mouseover', 4);
firstGroup.registerBgImage('hoverme', 'http://us.i1.yimg.com/us.yimg.com/i/fifa/gen/vip/es/h_wlcm.gif');
firstGroup.registerBgImage('hoverme2', 'http://us.i1.yimg.com/us.yimg.com/i/fifa/gen/vip/de/h_wlcm.gif');
firstGroup.addTrigger('hoverme2', 'click');
firstGroup.name = 'fifa';

// by class name
var classGroup = new YAHOO.util.ImageLoader.group('classtest1', 'mouseover', null);
classGroup.className = 'yui-imgload';

// custom event
var myCustomEvent = new YAHOO.util.CustomEvent('some imageloader event');
var customeventGroup = new YAHOO.util.ImageLoader.group();
customeventGroup.addCustomTrigger(myCustomEvent);
customeventGroup.registerBgImage('customevent', 'http://us.i1.yimg.com/us.yimg.com/i/ca/mus/pol/album_bss.jpg');
customeventGroup.name = 'customevent';
YAHOO.util.Event.addListener('customevent', 'dblclick', function() { myCustomEvent.fire(); });

// visibility setting
var squareGroup = new YAHOO.util.ImageLoader.group(window, 'scroll', 8);
var aquImg = squareGroup.registerSrcImage('squareImg', 'http://us.i1.yimg.com/us.yimg.com/i/b5/ast/hsign/aqu.gif');
aquImg.setVisible = true;
squareGroup.name = 'scroll';

// png, no trigger
var pngGroup = new YAHOO.util.ImageLoader.group(null, null, 5);
pngGroup.registerPngBgImage('pngimg', 'http://us.i1.yimg.com/us.yimg.com/i/us/nws/weather/gr/47s.png');

// scrolling, mix of registerd images and class-name images
var downGroup = new YAHOO.util.ImageLoader.group(window, 'scroll', null);
downGroup.foldConditional = true;
downGroup.registerBgImage('waydown', 'http://us.i1.yimg.com/us.yimg.com/i/ar/sp/fifa/rooney77x42.jpg');
downGroup.registerBgImage('waydown2', 'http://us.i1.yimg.com/us.yimg.com/i/ar/sp/fifa/rooney77x42.jpg');
downGroup.registerBgImage('waydown3', 'http://us.i1.yimg.com/us.yimg.com/i/ar/sp/fifa/rooney77x42.jpg');
downGroup.className = 'waydownCF';
downGroup.addTrigger(window, 'resize');
downGroup.name = 'conditional';

// loading image until image loads
var loadingClassGroup = new YAHOO.util.ImageLoader.group('classloadingtest', 'mouseover', null);
loadingClassGroup.className = 'yui-imgloadwithload';

