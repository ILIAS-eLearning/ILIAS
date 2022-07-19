<?php declare(strict_types=1);
namespace ILIAS\UI\examples\MainControls\MetaBar;

use ILIAS\DI\Container;

function base_metabar() : string
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

    $page_demo = $f->link()->standard('See UI in fullscreen-mode', $url);

    return $renderer->render([
        $txt,
        $page_demo
    ]);
}

function buildMetabar(\ILIAS\UI\Factory $f) : \ILIAS\UI\Component\MainControls\MetaBar
{
    $help = $f->button()->bulky($f->symbol()->glyph()->help(), 'Help', '#');
    $search = $f->button()->bulky($f->symbol()->glyph()->search(), 'Search', '#');
    $user = $f->button()->bulky($f->symbol()->glyph()->user(), 'User', '#');

    $notes = $f->mainControls()->slate()->legacy(
        'Notification',
        $f->symbol()->glyph()->notification(),
        $f->legacy('some content')
    );

    return $f->mainControls()->metaBar()
             ->withAdditionalEntry('search', $search)
             ->withAdditionalEntry('help', $help)
             ->withAdditionalEntry('notes', $notes)
             ->withAdditionalEntry('user', $user);
}

global $DIC;

//Render Footer in Fullscreen mode
if (basename($_SERVER["SCRIPT_FILENAME"]) == "base_metabar.php") {
    chdir('../../../../../');
    require_once("libs/composer/vendor/autoload.php");
    \ilInitialisation::initILIAS();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();
}


if (isset($request_wrapper) && isset($refinery) && $request_wrapper->has('new_metabar_ui') && $request_wrapper->retrieve('new_metabar_ui', $refinery->kindlyTo()->string()) == '1') {
    echo renderMetaBarInFullscreenMode($DIC);
}

function renderMetaBarInFullscreenMode(Container $dic) : string
{
    $f = $dic->ui()->factory();
    $renderer = $dic->ui()->renderer();
    $logo = $f->image()->responsive("templates/default/images/HeaderIcon.svg", "ILIAS");
    $responsive_logo = $f->image()->responsive("templates/default/images/HeaderIconResponsive.svg", "ILIAS");
    $breadcrumbs = pageMetabarDemoCrumbs($f);
    $metabar = buildMetabar($f);
    $mainbar = pageMetabarDemoMainbar($f);
    $footer = pageMetabarDemoFooter($f);
    $tc = $dic->ui()->factory()->toast()->container();

    $content = pageMetabarDemoContent($f);

    $page = $f->layout()->page()->standard(
        $content,
        $metabar,
        $mainbar,
        $breadcrumbs,
        $logo,
        $responsive_logo,
        "./templates/default/images/favicon.ico",
        $tc,
        $footer,
        'UI Meta Bar DEMO', //page title
        'ILIAS', //short title
        'ILIAS Meta Bar Demo' //view title
    )->withUIDemo(true);

    return $renderer->render($page);
}


function pageMetabarDemoContent(\ILIAS\UI\Factory $f) : array
{
    return [
        $f->panel()->standard(
            'All about the Meta Bar',
            $f->legacy(
                "See above"
            )
        ),
    ];
}

function pageMetabarDemoCrumbs(\ILIAS\UI\Factory $f) : \ILIAS\UI\Component\Breadcrumbs\Breadcrumbs
{
    return $f->breadcrumbs([]);
}

function pageMetabarDemoMainbar(\ILIAS\UI\Factory $f) : \ILIAS\UI\Component\MainControls\MainBar
{
    return $f->mainControls()->mainBar();
}

function pageMetabarDemoFooter(\ILIAS\UI\Factory $f) : \ILIAS\UI\Component\MainControls\Footer
{
    $text = 'Footer';
    $links = [];

    return $f->mainControls()->footer($links, $text);
}
