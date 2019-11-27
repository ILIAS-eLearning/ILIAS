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
let typ;
let coordinates;
let label;
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
};

let process_imgkey = function(e) {
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
};

let submit_popup = function() {
    if (current_coordinates !== null) {
        coordinates.val(current_coordinates);
        label.html(current_coordinates);
        close_popup();
    }
};

let close_popup = function(e) {
    popup.hide();
};

let transformToPercentage = function(part, whole) {
    return Math.round((part / whole) * 100);
};

let createPoints = function(stop) {
    return {
        top_left:
            new Point(Math.min(start.X, stop.offsetX),
                      Math.min(start.Y, stop.offsetY)),
        bottom_right:
            new Point(Math.max(start.X, stop.offsetX),
                      Math.max(start.Y, stop.offsetY))
    };
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

$(document).on('keyup', process_imgkey);
$(document).on('click', '.js_select_coordinates', display_coordinate_selector);
$(document).on('click', '.js_image_select', submit_popup);
$(document).on('click', '.js_image_cancel, .close', close_popup);
