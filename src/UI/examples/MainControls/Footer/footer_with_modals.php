<?php declare(strict_types=1);

namespace ILIAS\UI\examples\MainControls\Footer;

use ILIAS\DI\Container;

function footer_with_modals() : string
{
    global $DIC;
    $f = $DIC->ui()->factory();

    $renderer = $DIC->ui()->renderer();

    $url = 'src/UI/examples/MainControls/Footer/footer_with_modals.php?new_footer_2_ui=1';

    $page_demo = $f->link()->standard('See UI in fullscreen-mode', $url);

    return $renderer->render([
        $page_demo
    ]);
}

function pageFooterDemo2Footer() : \ILIAS\UI\Component\MainControls\Footer
{
    global $DIC;
    $f = $DIC->ui()->factory();

    $text = 'Additional info:';
    $links = [];
    $links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");

    $footer = $f->mainControls()->footer($links, $text);

    $roundTripModal = $f->modal()->roundtrip('Withdrawal of Consent', $f->legacy('Withdrawal of Consent ...'));
    $shyButton = $f->button()->shy('Terms Of Service', '#');
    return $footer->withAdditionalModalAndTrigger($roundTripModal, $shyButton);
}

global $DIC;

//Render Footer in Fullscreen mode
if (basename($_SERVER["SCRIPT_FILENAME"]) == "footer_with_modals.php") {
    chdir('../../../../../');
    require_once("libs/composer/vendor/autoload.php");
    require_once("src/UI/examples/MainControls/Footer/footer.php");
    \ilInitialisation::initILIAS();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();
}


if (isset($request_wrapper) && isset($refinery) && $request_wrapper->has('new_footer_2_ui') && $request_wrapper->retrieve('new_footer_2_ui', $refinery->kindlyTo()->string()) == '1') {
    echo renderFooterWithModalsInFullscreenMode($DIC);
}

function renderFooterWithModalsInFullscreenMode(Container $dic) : string
{
    $f = $dic->ui()->factory();
    $renderer = $dic->ui()->renderer();
    $logo = $f->image()->responsive("templates/default/images/HeaderIcon.svg", "ILIAS");
    $responsive_logo = $f->image()->responsive("templates/default/images/HeaderIconResponsive.svg", "ILIAS");
    $breadcrumbs = pageFooterDemoCrumbs($f);
    $metabar = pageFooterDemoMetabar($f);
    $mainbar = pageFooterDemoMainbar($f);
    $footer = pageFooterDemo2Footer();
    $tc = $dic->ui()->factory()->toast()->container();

    $content = pageFooterDemoContent($f);

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
        'UI PAGE FOOTER DEMO', //page title
        'ILIAS', //short title
        'Std. Page Footer Demo' //view title
    )->withUIDemo(true);

    return $renderer->render($page);
}
