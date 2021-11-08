#!/usr/local/bin/python

"""
This script converts a subset of SVG into an HTML imagemap

Note *subset*.  It only handles <path> elements, for which it only pays
attention to the M and L commands.  Futher, it only notices the "translate"
transform.

It was written to generate the examples in the documentation for maphilight,
and thus is very squarely aimed at handling several SVG maps from wikipedia.
It *assumes* that all the <path>s it will need are inside a <g>.  Any <path>
outside of a <g> will be ignored.

It takes several possible arguments, in the form:
$ svn2imagemap.py FILENAME [x y [group1 group2 ... groupN]]

FILENAME must be the name of an SVG file.  All other arguments are optional.

x and y, if present, are the dimensions of the image you'll be creating from
the SVG.  If not present, it assumes the values of the width and height
attributes in the SVG file.

group1 through groupN are group ids.  If only want particular groups used,
enter their ids here and all others will be ignored.
"""

import os
import re
import sys
import xml.dom.minidom

import parse_path

if len(sys.argv) == 1:
    sys.exit("svn2imagemap.py FILENAME [x y [group1 group2 ... groupN]]")
if not os.path.exists(sys.argv[1]):
    sys.exit("Input file does not exist")
x, y, groups = None, None, None
if len(sys.argv) >= 3:
    x = float(sys.argv[2])
    y = float(sys.argv[3])
    if len(sys.argv) > 3:
        groups = sys.argv[4:]

svg_file = xml.dom.minidom.parse(sys.argv[1])
svg = svg_file.getElementsByTagName('svg')[0]

raw_width = float(svg.getAttribute('width'))
raw_height = float(svg.getAttribute('height'))
width_ratio = x and (x / raw_width) or 1
height_ratio = y and (y / raw_height) or 1

if groups:
    elements = [g for g in svg.getElementsByTagName('g') if (g.hasAttribute('id') and g.getAttribute('id') in groups)]
    elements.extend([p for p in svg.getElementsByTagName('path') if (p.hasAttribute('id') and p.getAttribute('id') in groups)])
else:
    elements = svg.getElementsByTagName('g')


def apply_element_transform(paths, element):
    if element.hasAttribute('transform'):
        print element.getAttribute('id'), element.getAttribute('transform')
        for transform in re.findall(r'(\w+)\(([^\)]+)\)', element.getAttribute('transform')):
            matrix = None
            transform_values = [float(v) for v in re.split(r'[,\s]\s*', transform[1])]
            # TODO: https://www.w3.org/TR/SVG/coords.html#TransformMatrixDefined defines all the matrix-transforms
            if transform[0] == 'translate':
                matrix = (1, 0, transform_values[0], 0, 1, transform_values[1])
            elif transform[0] == 'matrix':
                matrix = transform_values

            if matrix:
                print('transforming', matrix)
                for path in paths:
                    # See: https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/transform
                    path[1] = [
                        ((matrix[0] * px) + (matrix[2] * py) + matrix[4],
                         (matrix[1] * px) + (matrix[3] * py) + matrix[5])
                        for px, py in path[1]
                    ]


def points_from_path(path):
    paths = []
    points = parse_path.get_points(path.getAttribute('d'))
    for pointset in points:
        paths.append([path.getAttribute('id'), pointset])
    apply_element_transform(paths, path)
    return paths


parsed_groups = {}
for e in elements:
    paths = []
    if e.nodeName == 'g':
        for path in e.getElementsByTagName('path'):
            paths.extend(points_from_path(path))
        apply_element_transform(paths, e)
    else:
        paths.extend(points_from_path(e))

    parsed_groups[e.getAttribute('id')] = paths

# print(parsed_groups)
out = []
for g in parsed_groups:
    for path in parsed_groups[g]:
        out.append(
            '<area href="#" title="%s" shape="poly" coords="%s"></area>' %
            (path[0], ', '.join([("%d,%d" % (p[0] * width_ratio, p[1] * height_ratio)) for p in path[1]]))
        )

outfile = open(sys.argv[1].replace('.svg', '.html'), 'w')
outfile.write('\n'.join(out))
