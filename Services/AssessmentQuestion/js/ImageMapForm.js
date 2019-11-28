//consts track definitions in ImageMapEditorDisplayDefinition.php
const TYPE_RECTANGLE = '1';
const TYPE_CIRCLE = '2';
const TYPE_POLYGON = '3';

class Point {
    constructor(X, Y) {
        this.X = X;
        this.Y = Y;
    }
}

let popup = null;
let canvas;
let preview_canvas;
let typ;
let coordinates;
let label;
let current_coordinates = null;
let poly_points;
let start = null;
let shifted;
let existing_shapes;

let display_coordinate_selector = function() {
    let image = $('.image_preview').attr('src');

    if (image.length === 0) {
        return;
    }

    $(this).blur();
    shifted = false;
    coordinates = $(this).parents('.aot_row').find('input[id$=imedd_coordinates]');
    typ = $(this).parents('.aot_row').find('select[id$=imedd_type]').val();
    existing_shapes = $(this).parents('.aot_row').siblings();
    label = $(this).parents('.aot_row').find('span.imedd_coordinates');
    popup = $('.js_image_popup');
    canvas = $('.js_coordinate_selector_canvas');

    canvas.off();
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
            current_coordinates = null;
            canvas.mouseup(generate_polygon);
            break;
    }

    popup.show();

    let img = $('.js_coordinate_selector');
    let img_content = img.parents('.modal-body');
    img.css('max-width', img_content.width());
    img.css('max-height', img_content.height());
    canvas[0].height = img.height();
    canvas[0].width = img.width();
    canvas.css('left', (img_content.width() - img.width()) / 2 + 'px');
    canvas.css('top', (img_content.height() - img.height()) / 2 + 'px');
    popup.css('left', (window.innerWidth - popup.width()) / 2 + 'px');
    popup.css('top', (window.innerHeight - popup.height()) / 2 + 'px');
    
    draw_shapes(existing_shapes, canvas[0]);
};

let process_img_key_up = function(e) {
    if (popup === null) {
        return;
    }

    // ESC
    if (e.keyCode === 27) {
        close_popup();
    }
    // Enter
    else if (e.keyCode === 13) {
        submit_popup();
    }
    // shift
    else if (e.keyCode === 16) {
        shifted = false;
    }
};

let process_img_key_down = function(e) {
    // shift
    if (e.keyCode === 16) {
        shifted = true;
    }  
};

let submit_popup = function() {
    if (current_coordinates !== null) {
        coordinates.val(current_coordinates);
        label.html(current_coordinates);
        close_popup();
        update_preview();
    }
};

let close_popup = function(e) {
    popup.hide();
};

let transformToPercentage = function(part, whole) {
    return Math.round((part / whole) * 100);
};

let createPoints = function(stop) {
    
    
    let point = {
        top_left:
            new Point(Math.min(start.X, stop.offsetX),
                      Math.min(start.Y, stop.offsetY)),
        bottom_right:
            new Point(Math.max(start.X, stop.offsetX),
                      Math.max(start.Y, stop.offsetY))
    };
    
    if (shifted) {
        let width = point.bottom_right.X - point.top_left.X;
        
        //set height to width
        point.bottom_right.Y = point.top_left.Y + width;
    }
    
    return point;
};

let record_start = function(e) {
    start = new Point(e.offsetX, e.offsetY);
};

let preview_circle = function(e) {
    if (start === null) {
        return;
    }

    let points = createPoints(e);

    draw_circle(points.top_left, points.bottom_right);
};

let generate_circle = function(e) {
    let points = createPoints(e);

    draw_circle(points.top_left, points.bottom_right);

    current_coordinates =
        'cx:' + transformToPercentage((points.top_left.X + points.bottom_right.X) / 2, canvas.width()) + ';' +
        'cy:' + transformToPercentage((points.top_left.Y + points.bottom_right.Y) / 2, canvas.height()) + ';' +
        'rx:' + transformToPercentage((points.bottom_right.X - points.top_left.X) / 2, canvas.width()) + ';' +
        'ry:' + transformToPercentage((points.bottom_right.Y - points.top_left.Y) / 2, canvas.height());

    start = null;
};

let draw_circle = function(origin, destination) {
    let g = canvas[0].getContext('2d');

    g.clearRect(0, 0, canvas.width(), canvas.height());

    draw_shapes(existing_shapes, canvas[0]);
    
    g.beginPath();
    g.lineWidth = '3';
    g.strokeStyle = 'black';
    g.ellipse((origin.X + destination.X) / 2,
              (origin.Y + destination.Y) / 2,
              (destination.X - origin.X) / 2,
              (destination.Y - origin.Y) / 2,
              0, 0, 2 * Math.PI);
    g.stroke();

    g.beginPath();
    g.lineWidth = '1';
    g.strokeStyle = 'red';
    g.ellipse((origin.X + destination.X) / 2,
              (origin.Y + destination.Y) / 2,
              (destination.X - origin.X) / 2,
              (destination.Y - origin.Y) / 2,
              0, 0, 2 * Math.PI);
    g.stroke();
};

let preview_rectangle = function(e) {
    if (start === null) {
        return;
    }

    let points = createPoints(e);

    draw_rectangle(points.top_left, points.bottom_right);
};

let generate_rectangle = function(e) {
    let points = createPoints(e);

    draw_rectangle(points.top_left, points.bottom_right);

    current_coordinates =
        'x:' + transformToPercentage(points.top_left.X, canvas.width()) + ';' +
        'y:' + transformToPercentage(points.top_left.Y, canvas.height()) + ';' +
        'width:' + transformToPercentage(points.bottom_right.X - points.top_left.X, canvas.width()) + ';' +
        'height:' + transformToPercentage(points.bottom_right.Y - points.top_left.Y, canvas.height());

    start = null;
};

let draw_rectangle = function(origin, destination) {
    let g = canvas[0].getContext('2d');

    g.clearRect(0, 0, canvas.width(), canvas.height());

    draw_shapes(existing_shapes, canvas[0]);
    
    g.beginPath();
    g.lineWidth = '3';
    g.strokeStyle = 'black';
    g.rect(origin.X, origin.Y, destination.X - origin.X, destination.Y - origin.Y);
    g.stroke();

    g.beginPath();
    g.lineWidth = '1';
    g.strokeStyle = 'red';
    g.rect(origin.X, origin.Y, destination.X - origin.X, destination.Y - origin.Y);
    g.stroke();
};

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
};

let draw_polygon = function() {
    let g = canvas[0].getContext('2d');

    g.clearRect(0, 0, canvas.width(), canvas.height());

    draw_shapes(existing_shapes, canvas[0]);
    
    g.beginPath();
    g.lineWidth = '3';
    g.strokeStyle = 'black';
    map_poly(g);
    g.stroke();

    g.beginPath();
    g.lineWidth = '1';
    g.strokeStyle = 'red';
    map_poly(g);
    g.stroke();
};

let map_poly = function(g) {
    if (poly_points.length < 2) {
        return;
    }

    g.moveTo(poly_points[0].X, poly_points[0].Y);

    let i;
    for (i = 1; i < poly_points.length; i += 1) {
        g.lineTo(poly_points[i].X, poly_points[i].Y);
    }

    g.closePath();
};

let paint_canvas;
let draw_shapes = function(rows, canvas) {
    paint_canvas = canvas;
    
    let g = canvas.getContext('2d');
    g.fillStyle = 'rgba(255, 255, 255, 0.8)';
    
    rows.each(function(index, item) {
        let type = $(item).find('select[id$=imedd_type]').val();
        let coordinates = $(item).find('input[id$=imedd_coordinates]').val();
        
        switch (type) {
            case TYPE_RECTANGLE:
                draw_preview_rectangle(coordinates, g);
                break;
            case TYPE_CIRCLE:
                draw_preview_circle(coordinates, g);
                break;
            case TYPE_POLYGON:
                draw_preview_polygon(coordinates, g);
                break;
        }
    });    
}

let update_preview = function() {
    if (preview_canvas == null) {
        initialize_preview();
    }
    
    let g = preview_canvas[0].getContext('2d');
    g.clearRect(0, 0, preview_canvas.width(), preview_canvas.height());
    
    draw_shapes($('.aot_row'), preview_canvas[0]);
};

let initialize_preview = function() {
    let preview_image = $('.image_preview');
    
    if (preview_image.length === 1) {
        preview_canvas = $('<canvas></canvas');
        preview_canvas.css('position', 'absolute');
        preview_canvas[0].width = preview_image.width();
        preview_canvas[0].height = preview_image.height();
        preview_canvas.css('bottom', preview_image.css('marginBottom'));
        preview_canvas.css('left', preview_image.parents('.col-sm-9').css('paddingLeft'));
        preview_image.after(preview_canvas);
    }
};

let rect_regex = /x:([^;]*);y:([^;]*);width:([^;]*);height:([^;]*)/;
let draw_preview_rectangle = function(coordinates, g) {
    
    let matches = coordinates.match(rect_regex);

    g.beginPath();
    g.rect(map_to_preview_width(matches[1]), 
           map_to_preview_height(matches[2]), 
           map_to_preview_width(matches[3]), 
           map_to_preview_height(matches[4]));
    g.fill();
};

let circle_regex = /cx:([^;]*);cy:([^;]*);rx:([^;]*);ry:([^;]*)/;
let draw_preview_circle = function(coordinates, g) {
    let matches = coordinates.match(circle_regex);
    
    g.beginPath();
    g.ellipse(map_to_preview_width(matches[1]), 
              map_to_preview_height(matches[2]), 
              map_to_preview_width(matches[3]), 
              map_to_preview_height(matches[4]),
              0, 0, 2 * Math.PI);
    g.fill();
};

let draw_preview_polygon = function(coordinates, g) {
    let points = coordinates.substring(7).split(' ');
    
    g.beginPath();

    let start = points[0].split(',');
    g.moveTo(map_to_preview_width(start[0]), 
             map_to_preview_height(start[1]));

    let i;
    for (i = 1; i < points.length; i += 1) {
        let point = points[i].split(',');
        g.lineTo(map_to_preview_width(point[0]), 
                 map_to_preview_height(point[1]));
    }

    g.closePath();
    
    g.fill();
};

let map_to_preview_height = function(value) {
    return map_to_preview(value, paint_canvas.height);
};

let map_to_preview_width = function(value) {
    return map_to_preview(value, paint_canvas.width);
};

let map_to_preview = function(value, preview_value) {
    let floatval = parseFloat(value);
    return (floatval / 100.0) * preview_value;
};

$(window).load(function() {
    update_preview();
});

$(document).on('keyup', process_img_key_up);
$(document).on('keydown', process_img_key_down);
$(document).on('click', '.js_select_coordinates', display_coordinate_selector);
$(document).on('click', '.js_image_select', submit_popup);
$(document).on('click', '.js_image_cancel, .close', close_popup);
$(document).on('click', '.js_remove', update_preview);
