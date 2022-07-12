<?php declare(strict_types=1);

namespace ILIAS\UI\examples\MainControls\Footer;

use ILIAS\UI\examples\Layout\Page\Standard as PageStandardExample;

function footer()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $url = 'src/UI/examples/MainControls/Footer/footer.php?new_footer_ui=1';

    $page_demo = $f->button()->primary('See UI in fullscreen-mode', $url);

    return $renderer->render([
        $page_demo
    ]);
}

function pageFooterDemoFooter($f)
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $df = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();

    $text = 'Additional info:';
    $links = [];
    $links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");
    $links[] = $f->link()->standard("Goto Mantis", "http://mantis.ilias.de");

    return $f->mainControls()->footer($links, $text)->withPermanentURL(
        $df->uri(
            (isset($_SERVER['REQUEST_SCHEME']) ?  $_SERVER['REQUEST_SCHEME']:"http") . '://'
            . (isset($_SERVER['SERVER_NAME']) ?  $_SERVER['SERVER_NAME']:"localhost") . ':'
            . (isset($_SERVER['SERVER_PORT']) ?  $_SERVER['SERVER_PORT']:"80")
            . (isset($_SERVER['SCRIPT_NAME']) ?  $_SERVER['SCRIPT_NAME']:"")
            . "?new_footer_ui=1"
        )
    );
}

global $DIC;
$refinery = $DIC->refinery();
$request_wrapper = $DIC->http()->wrapper()->query();

if ($request_wrapper->has('new_footer_ui') && $request_wrapper->retrieve('new_footer_ui', $refinery->kindlyTo()->string()) == '1') {
    chdir('../../../../../');

    PageStandardExample\_initIliasForPreview();

    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $logo = $f->image()->responsive("templates/default/images/HeaderIcon.svg", "ILIAS");
    $responsive_logo = $f->image()->responsive("templates/default/images/HeaderIconResponsive.svg", "ILIAS");
    $breadcrumbs = pageFooterDemoCrumbs($f);
    $metabar = pageFooterDemoMetabar($f);
    $mainbar = pageFooterDemoMainbar($f, $renderer);
    $footer = pageFooterDemoFooter($f);

    $entries = $mainbar->getEntries();
    $tools = $mainbar->getToolEntries();
    $content = pageFooterDemoContent($f, $renderer, $mainbar);

    $page = $f->layout()->page()->standard(
        $content,
        $metabar,
        $mainbar,
        $breadcrumbs,
        $logo,
        $responsive_logo,
        null,
        $footer,
        'UI PAGE FOOTER DEMO', //page title
        'ILIAS', //short title
        'Std. Page Footer Demo' //view title
    )->withUIDemo(true);

    echo $renderer->render($page);
}


function pageFooterDemoContent($f, $r, $mainbar)
{
    return array(
        $f->panel()->standard(
            'All about the Footer',
            $f->legacy(
                "See bellow"
            )
        ),
    );
}

function pageFooterDemoMetabar($f)
{
    $help = $f->button()->bulky($f->symbol()->glyph()->help(), 'Help', '#');
    return $f->mainControls()->metabar()->withAdditionalEntry('help', $help);
}

function pageFooterDemoCrumbs($f)
{
    return $f->breadcrumbs([]);
}

function pageFooterDemoMainbar($f, $r)
{
    return $f->mainControls()->mainbar();
}
