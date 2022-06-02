<?php
declare(strict_types=1);
namespace ILIAS\UI\examples\MainControls\MetaBar;

use ILIAS\UI\examples\Layout\Page\Standard as PageStandardExample;
use GuzzleHttp\Psr7\ServerRequest;

function base_metabar()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $url = 'src/UI/examples/MainControls/Metabar/base_metabar.php?new_metabar_ui=1';
    $txt = $f->legacy('<p>
            The Metabar Example opens in Fullscreen to showcase the behaviour of the metabar best.
            Note, an comprensive example for developers on how to access the JS API of the Metabar
            feature bellow in the second example.
            </p>');

    $page_demo = $f->button()->primary('See UI in fullscreen-mode', $url);

    return $renderer->render([
        $txt,
        $page_demo
    ]);
}

function buildMetabar($f)
{
    $help = $f->button()->bulky($f->symbol()->glyph()->help(), 'Help', '#');
    $search = $f->button()->bulky($f->symbol()->glyph()->search(), 'Search', '#');
    $user = $f->button()->bulky($f->symbol()->glyph()->user(), 'User', '#');

    $notes = $f->maincontrols()->slate()->legacy(
        'Notification',
        $f->symbol()->glyph()->notification(),
        $f->legacy('some content')
    );

    $metabar = $f->mainControls()->metabar()
                 ->withAdditionalEntry('search', $search)
                 ->withAdditionalEntry('help', $help)
                 ->withAdditionalEntry('notes', $notes)
                 ->withAdditionalEntry('user', $user)
    ;

    return $metabar;
}

global $DIC;
$refinery = $DIC->refinery();
$request_wrapper = $DIC->http()->wrapper()->query();

if ($request_wrapper->has('new_metabar_ui') && $request_wrapper->retrieve('new_metabar_ui', $refinery->kindlyTo()->string()) == '1') {
    chdir('../../../../../');
    PageStandardExample\_initIliasForPreview();

    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $logo = $f->image()->responsive("templates/default/images/HeaderIcon.svg", "ILIAS");
    $responsive_logo = $f->image()->responsive("templates/default/images/HeaderIconResponsive.svg", "ILIAS");
    $breadcrumbs = pageMetabarDemoCrumbs($f);
    $metabar = buildMetabar($f);
    $mainbar = pageMetabarDemoMainbar($f, $renderer);
    $footer = pageMetabarDemoFooter($f);

    $entries = $mainbar->getEntries();
    $tools = $mainbar->getToolEntries();
    $content = pageMetabarDemoContent($f, $renderer, $mainbar);

    $page = $f->layout()->page()->standard(
        $content,
        $metabar,
        $mainbar,
        $breadcrumbs,
        $logo,
        $responsive_logo,
        null,
        $footer,
        'UI Meta Bar DEMO', //page title
        'ILIAS', //short title
        'ILIAS Meta Bar Demo' //view title
    )->withUIDemo(true);

    echo $renderer->render($page);
}


function pageMetabarDemoContent($f, $r, $mainbar)
{
    return array(
        $f->panel()->standard(
            'All about the Meta Bar',
            $f->legacy(
                "See above"
            )
        ),
    );
}

function pageMetabarDemoCrumbs($f)
{
    return $f->breadcrumbs([]);
}

function pageMetabarDemoMainbar($f, $r)
{
    return $f->mainControls()->mainbar();
}

function pageMetabarDemoFooter($f)
{
    $text = 'Footer';
    $links = [];

    return $f->mainControls()->footer($links, $text);
}
