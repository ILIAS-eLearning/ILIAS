var ilImageMapInputTemplate = {

	polygons     : [],
	rects        : [],
	circles      : [],
	tag_container: 'tbody.imgmap',
	tag_row      : 'tr.imgmap',
	tag_button   : 'area',

	getPolygons: function(rootel) {
		var allpolys = [];
		$(rootel).find("td.poly").contents().filter(function() {
			return this.nodeType === 3;
		}).each(function() {
			var c = [];
			var carr = $(this).text().replace(/ /, "").split(",");
			for(var i = 0; i < carr.length; i += 2)
			{
				c.push({x: parseInt(carr[i]), y: parseInt(carr[i + 1])})
			}
			allpolys.push({
				row: $(this).parent().parent(),
				coordinates: c
			});
		});
		return allpolys;
	},

	getRects: function(rootel) {
		var allrects = [];
		$(rootel).find("td.rect").contents().filter(function() {
			return this.nodeType === 3;
		}).each(function() {
			var carr = $(this).text().replace(/ /, "").split(",");
			allrects.push({
				row: $(this).parent().parent(),
				coordinates: {x1: parseInt(carr[0]), y1: parseInt(carr[1]), x2: parseInt(carr[2]), y2: parseInt(carr[3])}
			});
		});
		return allrects;
	},

	getCircles: function(rootel) {
		var allcircles = [];
		$(rootel).find("td.circle").contents().filter(function() {
			return this.nodeType === 3;
		}).each(function() {
			var carr = $(this).text().replace(/ /, "").split(",");
			allcircles.push({
				row: $(this).parent().parent(),
				coordinates: {x: parseInt(carr[0]), y: parseInt(carr[1]), r: parseInt(carr[2])}
			});
		});
		return allcircles;
	},

	isPointInRect: function(rect, pt) {
		return pt.x >= rect.x1 && pt.x <= rect.x2 && pt.y >= rect.y1 && pt.y <= rect.y2;
	},

	isPointInCircle: function(circle, pt) {
		var square_dist = Math.pow((circle.x - pt.x), 2) + Math.pow((circle.y - pt.y), 2);
		return square_dist <= Math.pow(circle.r, 2);
	},

	isPointInPoly: function(poly, pt) {
		for (var c = false, i = -1, l = poly.length, j = l - 1; ++i < l; j = i)
			((poly[i].y <= pt.y && pt.y < poly[j].y) || (poly[j].y <= pt.y && pt.y < poly[i].y))
			&& (pt.x < (poly[j].x - poly[i].x) * (pt.y - poly[i].y) / (poly[j].y - poly[i].y) + poly[i].x)
			&& (c = !c);
		return c;
	},

	getRowFromEvent: function(e) {
		return $(e.target).closest(this.tag_row);
	},

	getContainerFromEvent: function(e) {
		return $(e.target).closest(this.tag_container);
	},

	cleanRow: function(row) {
	},

	reindexRows: function(tbody) {
	},

	initEvents: function(rootel) {
		var that = this, context = $(rootel);

		this.polygons = this.getPolygons(rootel);
		this.rects    = this.getRects(rootel);
		this.circles  = this.getCircles(rootel);

		context.find('button.' + that.tag_button + '_remove').on('click', function(e) {
			that.removeRow(e);
		});

		context.closest('form').find('img.imagemap').on('mousemove', function(e) {
			var px = e.offsetX;
			var py = e.offsetY;

			$(that.polygons).each(function(i) {
				if (that.isPointInPoly(this.coordinates, { x: px, y: py })) {
					this.row.addClass('active-area');
				} else {
					this.row.removeClass('active-area');
				}
			});

			$(that.rects).each(function(i) {
				if (that.isPointInRect(this.coordinates, { x: px, y: py })) {
					this.row.addClass('active-area')
				} else {
					this.row.removeClass('active-area');
				}
			});

			$(that.circles).each(function(i) {
				if (that.isPointInCircle(this.coordinates, { x: px, y: py })) {
					this.row.addClass('active-area')
				} else {
					this.row.removeClass('active-area');
				}
			});
		});
	},

	removeRow: function(e) {
		// Each row can be removed
		var source = this.getRowFromEvent(e);
		$(source).remove();

		// Reinit shapes
		this.polygons = this.getPolygons(this.tag_container);
		this.rects    = this.getRects(this.tag_container);
		this.circles  = this.getCircles(this.tag_container);
	}
};

$(document).ready(function() {
	var ilImageMapInput = $.extend({}, ilWizardInput, ilImageMapInputTemplate);
	ilImageMapInput.init();
});