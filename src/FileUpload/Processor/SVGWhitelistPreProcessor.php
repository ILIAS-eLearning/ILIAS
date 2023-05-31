<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\FileUpload\Processor;

use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\ProcessingStatus;

/**
 * Class SVGWhitelistPreProcessor
 *
 * PreProcessor which checks SVGs for unknown tags and attributes.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class SVGWhitelistPreProcessor implements PreProcessor
{
    use IsMimeTypeOrExtension;

    private const SVG_MIME_TYPE = 'image/svg+xml';
    private const REGEX_SCRIPT = '/<script/m';
    private const REGEX_BASE64 = '/data:.*;base64/m';
    private const SVG = 'svg';
    /**
     * @var string
     */
    private $rejection_message = 'The SVG file contains possibily malicious code.';
    /**
     * @var string
     */
    private $ok_message = 'SVG OK';

    public function __construct(?string $rejection_message = null)
    {
        $this->rejection_message = $rejection_message ?? $this->rejection_message;
    }

    private function isSVG(Metadata $metadata) : bool
    {
        return $this->isMimeTypeOrExtension(
            $metadata,
            self::SVG,
            [self::SVG_MIME_TYPE]
        );
    }

    public function process(FileStream $stream, Metadata $metadata) : ProcessingStatus
    {
        if ($this->isSVG($metadata) && !$this->checkStream($stream)) {
            return new ProcessingStatus(ProcessingStatus::DENIED, $this->rejection_message);
        }
        return new ProcessingStatus(ProcessingStatus::OK, $this->ok_message);
    }

    protected function checkStream(FileStream $stream) : bool
    {
        // Check all Elements and Attributes against a whitelist
        // Credits to https://github.com/alnorris/SVG-Sanitizer
        $all_elements = $dom->getElementsByTagName("*");

        for ($i = 0; $i < $all_elements->length; $i++) {
            $current_node = $all_elements->item($i);

            $element_name = $original_element_name = $current_node->tagName;
            $whitelist_attr_arr = self::$whitelist[$element_name] ?? null;

            if ($whitelist_attr_arr !== null) {
                for ($x = 0; $x < $current_node->attributes->length; $x++) {
                    $attribute_name = $current_node->attributes->item($x)->name;
                    if (!in_array($attribute_name, $whitelist_attr_arr)) {
                        $this->rejection_message = $this->rejection_message
                            . ' (' . $original_element_name
                            . '/' . $attribute_name . ').';
                        return false;
                    }
                }
            } else {
                $this->rejection_message = $this->rejection_message
                    . ' (' . $original_element_name . ').';
                return false;
            }
        }

        return true;
    }

    private static $whitelist = [
        'a' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'fill',
                'fill-opacity',
                'fill-rule',
                'filter',
                'id',
                'mask',
                'opacity',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'systemLanguage',
                'transform',
                'href',
                'xlink:href',
                'xlink:title',
            ],
        'animate' => [
            'attributeName',
            'from',
            'to',
            'dur',
            'repeatCount',
            'begin'
        ],
        'circle' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'cx',
                'cy',
                'fill',
                'fill-opacity',
                'fill-rule',
                'filter',
                'id',
                'mask',
                'opacity',
                'r',
                'requiredFeatures',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'systemLanguage',
                'transform',
            ],
        'clipPath' =>
            [
                'class',
                'clipPathUnits',
                'id',
            ],
        'defs' =>
            [
                'id',
            ],
        'style' =>
            [
                'type',
            ],
        'desc' =>
            [],
        'ellipse' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'cx',
                'cy',
                'fill',
                'fill-opacity',
                'fill-rule',
                'filter',
                'id',
                'mask',
                'opacity',
                'requiredFeatures',
                'rx',
                'ry',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'systemLanguage',
                'transform',
            ],
        'feGaussianBlur' =>
            [
                'class',
                'color-interpolation-filters',
                'id',
                'requiredFeatures',
                'stdDeviation',
            ],
        'filter' =>
            [
                'class',
                'color-interpolation-filters',
                'filterRes',
                'filterUnits',
                'height',
                'id',
                'primitiveUnits',
                'requiredFeatures',
                'width',
                'x',
                'xlink:href',
                'y',
            ],
        'foreignObject' =>
            [
                'class',
                'font-size',
                'height',
                'id',
                'opacity',
                'requiredFeatures',
                'style',
                'transform',
                'width',
                'x',
                'y',
            ],
        'g' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'id',
                'display',
                'fill',
                'fill-opacity',
                'fill-rule',
                'filter',
                'mask',
                'opacity',
                'extraneous',
                'requiredFeatures',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'systemLanguage',
                'transform',
                'font-family',
                'font-size',
                'font-style',
                'font-weight',
                'text-anchor',
            ],
        'image' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'filter',
                'height',
                'id',
                'mask',
                'opacity',
                'requiredFeatures',
                'style',
                'systemLanguage',
                'transform',
                'width',
                'x',
                'xlink:href',
                'xlink:title',
                'y',
            ],
        'line' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'fill',
                'fill-opacity',
                'fill-rule',
                'filter',
                'id',
                'marker-end',
                'marker-mid',
                'marker-start',
                'mask',
                'opacity',
                'requiredFeatures',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'systemLanguage',
                'transform',
                'x1',
                'x2',
                'y1',
                'y2',
            ],
        'linearGradient' =>
            [
                'class',
                'collect',
                'href',
                'id',
                'gradientTransform',
                'gradientUnits',
                'requiredFeatures',
                'spreadMethod',
                'systemLanguage',
                'x1',
                'x2',
                'xlink:href',
                'y1',
                'y2',
            ],
        'marker' =>
            [
                'id',
                'class',
                'markerHeight',
                'markerUnits',
                'markerWidth',
                'orient',
                'preserveAspectRatio',
                'refX',
                'refY',
                'systemLanguage',
                'viewBox',
            ],
        'mask' =>
            [
                'class',
                'height',
                'id',
                'maskContentUnits',
                'maskUnits',
                'width',
                'x',
                'y',
            ],
        'metadata' =>
            [
                'class',
                'id',
            ],
        'path' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'd',
                'cx',
                'cy',
                'rx',
                'ry',
                'fill',
                'type',
                'fill-opacity',
                'fill-rule',
                'filter',
                'id',
                'marker-end',
                'marker-mid',
                'marker-start',
                'mask',
                'opacity',
                'requiredFeatures',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'systemLanguage',
                'transform',
                'connector-curvature',
            ],
        'pattern' =>
            [
                'class',
                'height',
                'id',
                'patternContentUnits',
                'patternTransform',
                'patternUnits',
                'requiredFeatures',
                'style',
                'systemLanguage',
                'viewBox',
                'width',
                'x',
                'xlink:href',
                'y',
            ],
        'polygon' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'id',
                'fill',
                'fill-opacity',
                'fill-rule',
                'filter',
                'id',
                'class',
                'marker-end',
                'marker-mid',
                'marker-start',
                'mask',
                'opacity',
                'points',
                'requiredFeatures',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'systemLanguage',
                'transform',
            ],
        'polyline' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'id',
                'fill',
                'fill-opacity',
                'fill-rule',
                'filter',
                'marker-end',
                'marker-mid',
                'marker-start',
                'mask',
                'opacity',
                'points',
                'requiredFeatures',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'systemLanguage',
                'transform',
            ],
        'rdf:RDF' =>
            [],
        'cc' =>
            [
                'about',
            ],
        'dc' =>
            [
                'resource',
                'title',
            ],
        'radialGradient' =>
            [
                'class',
                'cx',
                'cy',
                'fx',
                'fy',
                'gradientTransform',
                'gradientUnits',
                'id',
                'r',
                'requiredFeatures',
                'spreadMethod',
                'systemLanguage',
                'xlink:href',
            ],
        'rect' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'fill',
                'fill-opacity',
                'fill-rule',
                'filter',
                'height',
                'id',
                'mask',
                'opacity',
                'requiredFeatures',
                'rx',
                'ry',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'systemLanguage',
                'transform',
                'width',
                'x',
                'y',
            ],
        'stop' =>
            [
                'class',
                'id',
                'offset',
                'requiredFeatures',
                'stop-color',
                'stop-opacity',
                'style',
                'systemLanguage',
            ],
        'svg' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'filter',
                'id',
                'height',
                'fill',
                'mask',
                'preserveAspectRatio',
                'requiredFeatures',
                'style',
                'systemLanguage',
                'viewBox',
                'width',
                'version',
                'docname',
                'space',
                'enable-background',
                'x',
                'baseProfile',
                'xmlns',
                'xmlns:se',
                'xmlns:xlink',
                'y',
            ],
        'switch' =>
            [
                'class',
                'id',
                'requiredFeatures',
                'systemLanguage',
            ],
        'symbol' =>
            [
                'class',
                'fill',
                'fill-opacity',
                'fill-rule',
                'filter',
                'font-family',
                'font-size',
                'font-style',
                'font-weight',
                'id',
                'opacity',
                'preserveAspectRatio',
                'requiredFeatures',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'systemLanguage',
                'transform',
                'viewBox',
            ],
        'text' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'fill',
                'fill-opacity',
                'fill-rule',
                'filter',
                'font-family',
                'font-size',
                'font-style',
                'font-weight',
                'id',
                'mask',
                'opacity',
                'requiredFeatures',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'systemLanguage',
                'text-anchor',
                'transform',
                'x',
                'xml:space',
                'y',
            ],
        'textPath' =>
            [
                'class',
                'id',
                'method',
                'requiredFeatures',
                'spacing',
                'startOffset',
                'style',
                'systemLanguage',
                'transform',
                'xlink:href',
            ],
        'title' =>
            [],
        'tspan' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'dx',
                'dy',
                'fill',
                'fill-opacity',
                'fill-rule',
                'filter',
                'font-family',
                'font-size',
                'font-style',
                'font-weight',
                'id',
                'mask',
                'opacity',
                'requiredFeatures',
                'rotate',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'systemLanguage',
                'text-anchor',
                'textLength',
                'transform',
                'x',
                'xml:space',
                'y',
            ],
        'sodipodi' =>
            [
                'pagecolor',
                'bordercolor',
                'borderopacity',
                'objecttolerance',
            ],
        'use' =>
            [
                'class',
                'clip-path',
                'clip-rule',
                'fill',
                'fill-opacity',
                'fill-rule',
                'filter',
                'height',
                'id',
                'href',
                'overflow',
                'mask',
                'stroke',
                'stroke-dasharray',
                'stroke-dashoffset',
                'stroke-linecap',
                'stroke-linejoin',
                'stroke-miterlimit',
                'stroke-opacity',
                'stroke-width',
                'style',
                'transform',
                'width',
                'x',
                'xlink:href',
                'y',
            ],
        'sodipodi:namedview' =>
            [
                'gridtolerance',
                'guidetolerance',
                'pageopacity',
                'pageshadow',
                'window-width',
                'window-height',
                'id',
                'showgrid',
                'zoom',
                'cx',
                'cy',
                'window-x',
                'window-y',
                'window-maximized',
                'current-layer',
                'gridtolerance',
                'guidetolerance',
                'pageopacity',
                'pageshadow',
                'window-width',
                'window-height',
                'id',
                'showgrid',
                'zoom',
                'cx',
                'cy',
                'window-x',
                'window-y',
                'window-maximized',
                'current-layer',
                'gridtolerance',
                'guidetolerance',
                'pageopacity',
                'pageshadow',
                'window-width',
                'window-height',
                'id',
                'showgrid',
                'zoom',
                'cx',
                'cy',
                'window-x',
                'window-y',
                'window-maximized',
                'current-layer',
                'pagecolor',
                'bordercolor',
                'borderopacity',
                'objecttolerance',
                'fit-margin-bottom',
                'fit-margin-right',
                'fit-margin-left',
                'fit-margin-top',
            ],
        'cc:Work' =>
            [
                "about"
            ],
        'dc:format' =>
            [],
        'dc:type' =>
            [
                "resource"
            ],
        'dc:title' =>
            [
                ""
            ],
    ];
}
