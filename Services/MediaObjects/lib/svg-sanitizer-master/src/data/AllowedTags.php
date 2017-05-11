<?php


namespace enshrined\svgSanitize\data;


/**
 * Class AllowedTags
 *
 * @package enshrined\svgSanitize\data
 */
class AllowedTags implements TagInterface
{

    /**
     * Returns an array of tags
     *
     * @return array
     */
    public static function getTags()
    {
        return array (
            // HTML
            'a','abbr','acronym','address','area','article','aside','audio','b',
            'bdi','bdo','big','blink','blockquote','body','br','button','canvas',
            'caption','center','cite','code','col','colgroup','content','data',
            'datalist','dd','decorator','del','details','dfn','dir','div','dl','dt',
            'element','em','fieldset','figcaption','figure','font','footer','form',
            'h1','h2','h3','h4','h5','h6','head','header','hgroup','hr','html','i',
            'img','input','ins','kbd','label','legend','li','main','map','mark',
            'marquee','menu','menuitem','meter','nav','nobr','ol','optgroup',
            'option','output','p','pre','progress','q','rp','rt','ruby','s','samp',
            'section','select','shadow','small','source','spacer','span','strike',
            'strong','style','sub','summary','sup','table','tbody','td','template',
            'textarea','tfoot','th','thead','time','tr','track','tt','u','ul','var',
            'video','wbr',

            // SVG
            'svg','altglyph','altglyphdef','altglyphitem','animatecolor',
            'animatemotion','animatetransform','circle','clippath','defs','desc',
            'ellipse','filter','font','g','glyph','glyphref','hkern','image','line',
            'lineargradient','marker','mask','metadata','mpath','path','pattern',
            'polygon','polyline','radialgradient','rect','stop','switch','symbol',
            'text','textpath','title','tref','tspan','view','vkern',

            // SVG Filters
            'feblend','fecolormatrix','fecomponenttransfer','fecomposite',
            'feconvolvematrix','fediffuselighting','fedisplacementmap',
            'feflood','fefunca','fefuncb','fefuncg','fefuncr','fegaussianblur',
            'femerge','femergenode','femorphology','feoffset',
            'fespecularlighting','fetile','feturbulence',

            //MathML
            'math','menclose','merror','mfenced','mfrac','mglyph','mi','mlabeledtr',
            'mmuliscripts','mn','mo','mover','mpadded','mphantom','mroot','mrow',
            'ms','mpspace','msqrt','mystyle','msub','msup','msubsup','mtable','mtd',
            'mtext','mtr','munder','munderover',

            //text
            '#text'
        );
    }
}