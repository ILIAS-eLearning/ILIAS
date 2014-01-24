/* * The MIT License

Copyright (c) 2010, 2011, 2012 by Juergen Marsch

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
/* Flot plugin for Spiders data sets

  series: {
    spider: { active: true, lineWidth: 8
  }
data: [

  $.plot($("#placeholder"), [{ data: [ [y, x, size], [....], ...], spider: {show: true, lineWidth: 5} } ])

*/

(function ($){
	var options ={
		series:{
			spider:{
				active: false
        ,show: false
        ,spiderSize: 0.8
        ,lineWidth: 1
			  ,lineStyle: "rgba(0,0,0,0.5)"
        ,pointSize: 6
			  ,scaleMode: "leg"
			  ,legMin: null
			  ,legMax: null
        ,connection: { width: 4 }
        ,highlight: { opacity: 0.5, mode: "point" }
        ,legs: { font: "20px Times New Roman"
						,fillStyle: "Black"
						,legScaleMin: 0.95
						,legScaleMax: 1.05
						,legStartAngle: 0 
				}
			}
		}
		,grid:{
			tickColor: "rgba(0,0,0,0.15)"
			,ticks: 5
			,mode: "radar"}
	};

	function init(plot){
		var data = null, canvas = null, target = null,opt = null, axes = null, offset = null;
		var maxRadius = null, centerLeft = null, centerTop = null;
		var lineRanges = [], hl; 
		plot.hooks.processOptions.push(processOptions);
		function processOptions(plot,options){
			if(options.series.spider.active){
				options.grid.show = false;
				plot.hooks.draw.push(draw);
				plot.hooks.bindEvents.push(bindEvents);
				plot.hooks.drawOverlay.push(drawOverlay);
			}
		}
		function draw(plot, ctx){
			canvas = plot.getCanvas();
			target = $(canvas).parent();
			data = plot.getData();
			opt = plot.getOptions();
			clear(ctx);
			setupspider();
			calculateRanges();
			drawspider(ctx,opt.grid);
		}
		function calculateRanges(){
			var ranges = [];
			if (data[0].spider.scaleMode == 'leg'){
				for (var j = 0; j < data[0].data.length; j++){	ranges.push(calculateItemRanges(j)); }
			}
			else{
				var range = calculateRange();
				for(var j = 0; j < data[0].data.length; j++){	ranges.push(range); }
			}
			data.ranges = ranges;
		}
		function calculateItemRanges(j){
			var min = Number.POSITIVE_INFINITY, max = Number.NEGATIVE_INFINITY;
			for(var i = 0; i < data.length; i++){
				min = Math.min(min,data[i].data[j][1]);
				max = Math.max(max,data[i].data[j][1]);
			} 
			min = min * data[0].spider.legs.legScaleMin;
			max = max * data[0].spider.legs.legScaleMax;
			if(opt.series.spider.legMin) min = opt.series.spider.legMin;
			if(opt.series.spider.legMax) max = opt.series.spider.legMax;
			return {min: min, max:max, range: max - min};
		}
		function calculateRange(){
			var min = Number.POSITIVE_INFINITY, max = Number.NEGATIVE_INFINITY;
			for(var j = 0; j < data[0].data.length; j++){
				for(var i = 0; i < data.length; i++){
					min = Math.min(min,data[i].data[j][1]);
					max = Math.max(max,data[i].data[j][1]);
				}
			}
			min = min * data[0].spider.legs.legScaleMin;
			max = max * data[0].spider.legs.legScaleMax;
			if(opt.series.spider.legMin) min = opt.series.spider.legMin;
			if(opt.series.spider.legMax) max = opt.series.spider.legMax;
			return {min: min, max:max, range: max - min};			
		}
		function clear(ctx){
			ctx.clearRect(0,0,canvas.width,canvas.height);
		}
		function setupspider(){
			maxRadius =  Math.min(canvas.width,canvas.height)/2 * data[0].spider.spiderSize;
			centerTop = (canvas.height/2);
			
			// alex-patch start
			centerLeft = (canvas.width/2);
			// centerLeft = centerTop;
			// alex-patch end
		}
		function drawspiderPoints(ctx,cnt,serie,opt){
			for(var j = 0; j < serie.data.length; j++) { drawspiderPoint(ctx,cnt,serie,j,opt); }
		}
		function drawspiderPoint(ctx,cnt,serie,j,c){
			var pos;
			var d = calculatePosition(serie,data.ranges,j);
			pos = calculateXY(cnt,j,d);
			ctx.beginPath();
			ctx.lineWidth = 1;
			ctx.fillStyle = c;
			ctx.strokeStyle = c;
			ctx.arc(pos.x,pos.y,serie.spider.pointSize,0,Math.PI * 2,true);
			ctx.closePath();
			ctx.fill();
		}
		function drawspiderConnections(ctx,cnt,serie,c,fill){
			var pos,d;
			if(!fill) fill = false;
			ctx.beginPath();
			ctx.lineWidth = serie.spider.connection.width;
			ctx.strokeStyle = c;
			ctx.fillStyle = c;
			d = calculatePosition(serie,data.ranges,0);
			pos = calculateXY(cnt,0,d);
			ctx.moveTo(pos.x,pos.y);
			for(var j = 1;j < serie.data.length; j++){
				d = calculatePosition(serie,data.ranges,j);
				pos = calculateXY(cnt,j,d);
				ctx.lineTo(pos.x,pos.y);
			}
			d = calculatePosition(serie,data.ranges,0);
			pos = calculateXY(cnt,0,d);
			ctx.lineTo(pos.x,pos.y);
			if(fill == true) ctx.fill(); 
			else 
			{ if(serie.spider.fill == true) ctx.fill(); else ctx.stroke(); }
		}
		function drawspider(ctx, opt){
			var cnt = data[0].data.length;
			for(var i = 0;i < data.length; i++){ drawspiderConnections(ctx,cnt,data[i],data[i].color); }
			for(var i = 0;i < data.length; i++){ drawspiderPoints(ctx,cnt,data[i],data[i].color); }
			drawGrid(ctx, opt);
			function drawGridRadar(ctx,opt){
				ctx.lineWidth = 1;
				ctx.strokeStyle = opt.tickColor;
				for (var i = 1; i <= opt.ticks; i++) {
					ctx.beginPath();
					ctx.arc(centerLeft, centerTop, maxRadius / opt.ticks * i, 0, Math.PI * 2, true);
					ctx.closePath();
					ctx.stroke();
				}
				// based on a patch from Thomasz Janik
				var startPoint = null;
				var breakPoint = null;
				for (var j = 0; j < cnt; j++){	
					if(startPoint == null){
						startPoint = calculateXY(cnt,j,100);
						breakPoint = calculateXY(cnt,Math.floor(cnt/4),100);
					}
					drawspiderLine(ctx, j);
					drawspiderLeg(ctx,j,startPoint,breakPoint);
				}
			}
			function drawGridSpider(ctx,opt){
				ctx.linewidth = 1;
				ctx.strokeStyle = opt.tickColor;
				for(var i = 0; i<= opt.ticks; i++){
					var pos = calculateXY(cnt,0,100 / opt.ticks * i);
					ctx.beginPath();
					ctx.moveTo(pos.x, pos.y);
					for(var j = 1; j < cnt; j++){
						pos = calculateXY(cnt,j,100 / opt.ticks * i);
						ctx.lineTo(pos.x, pos.y);
					}
					ctx.closePath();
					ctx.stroke();
				}
				var startPoint = null;
				var breakPoint = null;
				for (var j = 0; j < cnt; j++) {
					if(startPoint == null){
						startPoint = calculateXY(cnt,j,100);
						breakPoint = calculateXY(cnt,Math.floor(cnt/4),100);
					}
					drawspiderLine(ctx,j);
					drawspiderLeg(ctx,j,startPoint,breakPoint);
				}
			}
			function drawGrid(ctx, opt){
				switch(opt.mode){
					case "radar":
						drawGridRadar(ctx,opt);
						break;
					case "spider":
						drawGridSpider(ctx,opt);
						break;
					default:
						drawGridRadar(ctx,opt);
						break;
				}
			}
			function drawScale(ctx,opt){
				if(opt.series.spider.scaleMode != "leg"){
					for(var i = 0; i <= opt.ticks; i++){	
					}
				}
			}
      function drawspiderLine(ctx, j){
				var pos;
      	ctx.beginPath();
      	ctx.lineWidth = options.series.spider.lineWidth;
       	ctx.strokeStyle = options.series.spider.lineStyle;
       	// alex-patch start
     	// ctx.moveTo(centerTop, centerLeft);
     	ctx.moveTo(centerLeft, centerTop);
     	// alex-patch start
      	pos = calculateXY(cnt,j,100);
      	ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
   		}
      function drawspiderLeg(ctx,j,startPoint,breakPoint,gridColor){
      	  		// alex-patch: added tp
				var pos,metrics,extraX,extraY,tp;
				pos = calculateXY(cnt,j,100);
	// pos is position of end point
				ctx.font = data[0].spider.legs.font;
				ctx.fillStyle = data[0].spider.legs.fillStyle;
				// based on patch created by Thomasz Janik
				metrics = ctx.measureText(data[0].spider.legs.data[j].label);
	// pos is position of end point
	
				// alex-patch start
				tp = calculateTextPos(cnt, j, metrics.width);
/*				if(pos.y > startPoint.y){ extraY = 15;}
				else{ extraY = -15;}
				if(pos.y < startPoint.y+10 && pos.y >startPoint.y-10){ extraY = 0;}
				if(pos.x < breakPoint.x){ extraX = (metrics.width*-1)-metrics.width/2;}
				else{ extraX = 0;}
				if(pos.x < startPoint.x+10 && pos.x >startPoint.x-10){ extraX = metrics.width/2;}*/
				extraX = tp.x;
				extraY = tp.y;
				// alex-patch end
				
				ctx.fillText(data[0].spider.legs.data[j].label, pos.x + extraX, pos.y + extraY);
			}
		}
		function calculatePosition(serie,ranges,j){
			var p;
			p = Math.max(Math.min(serie.data[j][1],ranges[j].max),ranges[j].min);
			return (p - ranges[j].min) / ranges[j].range * 100; 
		}
		// alex-patch start
		function calculateTextPos(cnt, j, tw)
		{
			var s,c,lh,x,y,xs;
			
			// line height
			lh = 10;
			
			// x-space
			xs = 20;
			
			s = 2 * Math.PI * opt.series.spider.legs.legStartAngle / 360;
			// right: 1, left -1, top, bottom: 0
			co = Math.cos(2 * Math.PI / cnt * j + s);
			// right: 0, bottom: -1, top 1, left: 0
			si = Math.sin(2 * Math.PI / cnt * j + s);
			
			x = (co * xs) + ((co / 2 - 0.5) * tw);
			y = ((si * 1.5) + 0.5) * lh;
			
			return {x: x, y: y};
		}
		// alex-patch end
		function calculateXY(cnt,j,d){
			var x,y,s;
			s = 2 * Math.PI * opt.series.spider.legs.legStartAngle / 360;
			x = centerLeft + Math.round(Math.cos(2 * Math.PI / cnt * j + s) * maxRadius * d / 100);
			y = centerTop + Math.round(Math.sin(2 * Math.PI / cnt * j + s) * maxRadius * d / 100);
			return {x: x, y: y};
		}
		function calculateFromCenter(mx,my){
			var d;
			d = (mx - centerLeft) * (mx - centerLeft) + (my - centerTop) * (my - centerTop);
			d = Math.sqrt(d);
			d = d / maxRadius * 100;
			return d;
		}
		function calculateValue(i,d){
			var v,range = data.ranges[i];
			v = range.min + range.range / 100 * d; 
			return v;
		}
		function bindEvents(plot, eventHolder){
			hl = new HighLighting(plot, eventHolder, findNearby, opt.series.spider.active);
		}
		function findNearby(mousex, mousey){
			var r, cnt;
			data = plot.getData();
			cnt = data[0].data.length;
			axes = plot.getAxes();
			r = new NearByReturn();
			r.item = findNearByItem(mousex,mousey);
			if (hl.mouseItem.editActive == true) { 
			  r.edit = findNearByEdit(mousex, mousey);}
			else r.edit = new NearByReturnData();
			return r;
			function findNearByEdit(mousex,mousey) { 
				var v,pos,d,r = new NearByReturnData();
				var d = calculateFromCenter(mousex,mousey); 
				v = calculateValue(hl.mouseItem.datapoint,d);
				pos = calculateXY(cnt,hl.mouseItem.datapoint,d);
				var dx = Math.abs(pos.x - mousex)
					,dy = Math.abs(pos.y - mousey)
					,dist = Math.sqrt(dx * dx + dy * dy); 
				var serie = data[hl.mouseItem.serie];
				if (dist <= serie.spider.pointSize) { 
					r.found = true;
					r.pos = d;
					r.value = v;				
				}
				return r;
			}
			function findNearByItem(mousex,mousey) {
				var serie,r = new NearByReturnData();
				for(var i = 0;i < data.length;i++){
					serie = data[i];
					if(serie.spider.show){
						for(var j = 0; j < serie.data.length; j++){
							var pos = calculateXY(cnt,j,calculatePosition(serie,data.ranges,j));
							var dx = Math.abs(pos.x - mousex)
								,dy = Math.abs(pos.y - mousey)
								,dist = Math.sqrt(dx * dx + dy * dy); 
							if (dist <= serie.spider.pointSize) { 
								r.found = true;
								r.serie = i;
								r.datapoint = j;
								r.value = serie.data[j];
								r.label = serie.label + ":" + opt.series.spider.legs.data[j].label;
							}
						}
					}
				}
				return r;
			}
		}
		function drawOverlay(plot, octx){
			var cnt = data[0].data.length;
			octx.save();
			octx.clearRect(0, 0, target.width(), target.height());
			if(opt.series.editmode == true){
				if(hl.mouseItem.editActive == true) { 
					octx.beginPath();
					octx.lineWidth = 1;
					var c = "rgba(255, 0, 0, " + options.series.spider.highlight.opacity + ")";
					octx.fillStyle = c;
					octx.strokeStyle = c;
					var pos = calculateXY(cnt,hl.mouseItem.datapoint,hl.mouseItem.pos);
					octx.arc(pos.x,pos.y,options.series.spider.pointSize,0,Math.PI * 2,true);
					octx.closePath();
					octx.fill();
				}
			}
			else {
				for(i = 0; i < hl.highlights.length; ++i) { drawHighlight(hl.highlights[i]);}
				octx.restore();
			}
			function drawHighlight(hl){
				var c = "rgba(255, 255, 255, " + opt.series.spider.highlight.opacity + ")";
				var serie = data[hl.item.serie];
				switch(opt.series.spider.highlight.mode){
					case "point":
               			drawspiderPoints(octx,cnt,serie,c);
						break;
					case "line":
						drawspiderConnections(octx,cnt,serie,c,false);
						break;
					case "area":
						drawspiderConnections(octx,cnt,serie,serie.color,true);
						break;
					default:
						break;
				}
			}
		}
	}
	$.plot.plugins.push({
		init: init,
		options: options,
		name: 'spider',
		version: '0.3'
	});
})(jQuery);	
