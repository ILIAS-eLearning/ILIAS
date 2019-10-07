// Generic Authoring
let usingTiny = false;

let add_row = function() {
	usingTiny = false;
    let row = $(this).parents(".aot_table").find(".aot_row").eq(0);
    let table = $(this).parents(".aot_table").children("tbody");

    let new_row = row.clone();

    new_row = clear_row(new_row);
    new_count = table.children().length + 1;
    process_row(new_row, new_count);
    table.append(new_row);
    $(".js_count").val(new_count);
    
    if (usingTiny) {
    	tinymce.init(tinymce.get()[0].settings);
    }
};

let remove_row = function() {
    let row = $(this).parents(".aot_row");
    let table = $(this).parents(".aot_table").children("tbody");

    if (table.children().length > 1) {
        row.remove();
        set_input_ids(table);
    } else {
        clear_row(row);
    }
};

let clear_row = function(row) {
    row.find("input, textarea").each(function() {
    	$input = $(this);
    	
    	if ($input.attr('type') === 'radio' ||
    		$input.attr('type') === 'checkbox') {
    		$input.attr('checked', false);
    	}
    	else {
    		$input.val('');
    	}
        
        if ($input.siblings('.mceEditor').length > 0) {
        	$input.siblings('.mceEditor').remove();
        	$input.show();
        	usingTiny = true;
        }
    });

    return row;
};

let up_row = function() {
	let row = $(this).parents(".aot_row");
	row.prev('.aot_row').before(row);
	set_input_ids(row.parents(".aot_table").children("tbody"));
};

let down_row = function() {
	let row = $(this).parents(".aot_row");
	row.next('.aot_row').after(row);
	set_input_ids(row.parents(".aot_table").children("tbody"));
};

let set_input_ids = function(table) {
    $(".js_count").val(table.children().length);

    let current_row = 1;

    table.children().each(function() {
    	process_row($(this), current_row);
        current_row += 1;
    });
};

let process_row = function(row, current_row) {
    row.find("input[name],textarea[name]").each(function() {
        let input = $(this);
        input.attr("name", update_input_name(input.attr("name"), current_row));
        input.prop("id", update_input_name(input.prop("id"), current_row));
    });	
};

let update_input_name = function(old_name, current_row) {
    return current_row + old_name.match(/\D+/);
};

let update_form = function() {
	let initiator = $(this);
	// decode &amp to &
	let decoded_link = $('<div>').html($('#form_part_link').val()).text();

	let url = decoded_link + '&class=' + initiator.children("option:selected").text();

	$.ajax(url)
	.done (function (data) {
		let initiator_row = initiator.parents('.form-group');

		while (initiator_row.next().find("#editor, #presenter, #scoring, #answer_form").length === 0 &&
			   !initiator_row.next().hasClass("ilFormFooter")) {
			initiator_row.next().remove();
		}

		initiator_row.after($(data).find('.form-horizontal > .form-group'));
		$('#answer_form').parents('.form-group').remove();
	});
};

$(document).ready(function() {
	//TODO hack to prevent image verification error
	$('[name=ilfilehash]').remove();
});

$(document).on("click", ".js_add", add_row);
$(document).on("click", ".js_remove", remove_row);
$(document).on("click", ".js_up", up_row);
$(document).on("click", ".js_down", down_row);
$(document).on("change", "#editor, #presenter, #scoring", update_form);

//ImageMapQuestion Authoring

//consts track definitions in ImageMapEditorDisplayDefinition.php
const TYPE_RECTANGLE = '1';
const TYPE_CIRCLE = '2';
const TYPE_POLYGON = '3';

//point
class Point {
	constructor(X, Y) {
		this.X = X;
		this.Y = Y;
	}
}

let popup = null;
let canvas;
let typ;
let coordinates;
let current_coordinates = null;
let poly_points;
let start = null;

let display_coordinate_selector = function() {
	let image = $('.image_preview').attr('src');
	
	if (image.length === 0) {
		return;
	}
	
	$(this).blur();
	coordinates = $(this).parents('.aot_row').find('input[id$=imedd_coordinates]');
	typ = $(this).parents('.aot_row').find('select[id$=imedd_type]').val();

	popup = $('<img />');
	popup.css('z-index', 1000);
	popup.css('position', 'absolute');
	popup.css('left', 0);
	popup.css('right', 0);
	popup.css('top', 0);
	popup.css('bottom', 0);
	popup.css('margin', 'auto');
	popup.css('max-width', window.innerWidth - 100);
	popup.css('max-height', window.innerHeight - 100);
	popup.attr('src', image);
	popup.addClass('js_coordinate_selector');
	$('html').append(popup);
	
	canvas = $('<canvas />');
	canvas.css('z-index', 1001);
	canvas.css('position', 'absolute');
	canvas.css('left', 0);
	canvas.css('right', 0);
	canvas.css('top', 0);
	canvas.css('bottom', 0);
	canvas.css('margin', 'auto');
	canvas.attr('width', popup.width());
	canvas.attr('height', popup.height());
	$('html').append(canvas);
	
	switch(typ) {
		case TYPE_RECTANGLE:
			canvas.mousedown(record_start);
			canvas.mousemove(preview_rectangle);
			canvas.mouseup(generate_rectangle);
			break;
		case TYPE_CIRCLE:
			canvas.mousedown(record_start);
			canvas.mousemove(preview_circle);
			canvas.mouseup(generate_circle);
			break;
		case TYPE_POLYGON:
			poly_points = [];
			canvas.mouseup(generate_polygon);
	}
};

let process_imgkey = function(e) {
	if (popup === null) {
		return;
	}
	
	//ESC
	if (e.keyCode === 27) {
		close_popup();
	}
	//Enter
	else if (e.keyCode === 13) {
		if (current_coordinates !== null) {
			coordinates.val(current_coordinates);
			close_popup();
		}
	}
}

let close_popup = function(e) {
	popup.remove();
	canvas.remove();
}

let transformToPercentage = function(part, whole) {
	return Math.round((part / whole) * 100);
}

let createPoints = function(stop) {
	return {
		top_left: 		new Point(Math.min(start.X, stop.offsetX),
                                  Math.min(start.Y, stop.offsetY)),
        bottom_right: 	new Point(Math.max(start.X, stop.offsetX),
                                  Math.max(start.Y, stop.offsetY))
	}
}

let record_start = function(e) {
	start = new Point(e.offsetX, e.offsetY);
}

let preview_circle = function(e) {
	if (start === null) {
		return;
	}
	
	let points = createPoints(e);
	
	draw_circle(points.top_left, points.bottom_right);
}

let generate_circle = function(e) {
	let points = createPoints(e);
	
	draw_circle(points.top_left, points.bottom_right);
	
	current_coordinates = 'cx:' + transformToPercentage((points.top_left.X + points.bottom_right.X) / 2, canvas.width()) + ';' +
                          'cy:' + transformToPercentage((points.top_left.Y + points.bottom_right.Y) / 2, canvas.height()) + ';' +
                          'rx:' + transformToPercentage((points.bottom_right.X - points.top_left.X) / 2, canvas.width()) + ';' + 
                          'ry:' + transformToPercentage((points.bottom_right.Y - points.top_left.Y) / 2, canvas.height());
	
	start = null;
}

let draw_circle = function(origin, destination) {
	g = canvas[0].getContext('2d');
	
	g.clearRect(0, 0, canvas.width(), canvas.height());
	
	g.beginPath();
	g.lineWidth = '3';
	g.strokeStyle = 'black';
	g.ellipse((origin.X + destination.X) / 2, 
			   (origin.Y + destination.Y) / 2, 
			   (destination.X - origin.X) / 2, 
			   (destination.Y - origin.Y) / 2,
			   0, 0, 2 * Math.PI);
	g.stroke()
	
	g.beginPath();
	g.lineWidth = '1';
	g.strokeStyle = 'red';
	g.ellipse((origin.X + destination.X) / 2, 
			   (origin.Y + destination.Y) / 2, 
			   (destination.X - origin.X) / 2, 
			   (destination.Y - origin.Y) / 2,
			   0, 0, 2 * Math.PI);
	g.stroke();	
}

let preview_rectangle = function(e) {
	if (start === null) {
		return;
	}
	
	let points = createPoints(e);
	
	draw_rectangle(points.top_left, points.bottom_right);
}

let generate_rectangle = function(e) {
	let points = createPoints(e);
	
	draw_rectangle(points.top_left, points.bottom_right);
	
	current_coordinates = 'x:' + transformToPercentage(points.top_left.X, canvas.width()) + ';' +
	                      'y:' + transformToPercentage(points.top_left.Y, canvas.height()) + ';' +
	                      'width:' + transformToPercentage(points.bottom_right.X - points.top_left.X, canvas.width()) + ';' + 
	                      'height:' + transformToPercentage(points.bottom_right.Y - points.top_left.Y, canvas.height());
	
	start = null;	
}

let draw_rectangle = function(origin, destination) {
	g = canvas[0].getContext('2d');
	
	g.clearRect(0, 0, canvas.width(), canvas.height());
	
	g.beginPath();
	g.lineWidth = '3';
	g.strokeStyle = 'black';
	g.rect(origin.X, origin.Y, destination.X - origin.X, destination.Y - origin.Y);
	g.stroke()
	
	g.beginPath();
	g.lineWidth = '1';
	g.strokeStyle = 'red';
	g.rect(origin.X, origin.Y, destination.X - origin.X, destination.Y - origin.Y);
	g.stroke();
}

let generate_polygon = function(e) {
	if (e.button === 1) {
		current_coordinates = null;
		poly_points = [];
		draw_polygon();
		e.preventDefault();
		return;
	}
	
	if (current_coordinates === null) {
		current_coordinates = 'points:';
	}
	
	current_coordinates += transformToPercentage(e.offsetX, canvas.width()) + ',' +
	                       transformToPercentage(e.offsetY, canvas.height()) + ' ';
	
	poly_points.push(new Point(e.offsetX, e.offsetY));
	
	draw_polygon();
}

let draw_polygon = function() {
	g = canvas[0].getContext('2d');
	
	g.clearRect(0, 0, canvas.width(), canvas.height());
	
	g.beginPath();
	g.lineWidth = '3';
	g.strokeStyle = 'black';
	map_poly(g);
	g.stroke()
	
	g.beginPath();
	g.lineWidth = '1';
	g.strokeStyle = 'red';
	map_poly(g);
	g.stroke();	
}

let map_poly = function(g) {
	if (poly_points.length < 2) {
		return;
	}
	
	g.moveTo(poly_points[0].X, poly_points[0].Y);
	
	for (let i = 1; i < poly_points.length; i++) {
		g.lineTo(poly_points[i].X, poly_points[i].Y);
	}
	
	g.closePath();
}

$(document).on('keyup', process_imgkey);
$(document).on('click', '.js_select_coordinates', display_coordinate_selector);
