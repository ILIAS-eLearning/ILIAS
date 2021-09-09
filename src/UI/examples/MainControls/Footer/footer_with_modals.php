<?php

function footer_with_modals()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $url = 'src/UI/examples/MainControls/Footer/footer_with_modals.php?new_footer_2_ui=1';

    $page_demo = $f->button()->primary('See UI in fullscreen-mode', $url);

    return $renderer->render([
        $page_demo
    ]);
}

function pageFooterDemo2Footer()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $text = 'Additional info:';
    $links = [];
    $links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");

    $footer = $f->mainControls()->footer($links, $text);

    $roundTripModal = $f->modal()->roundtrip('Withdrawal of Consent', $f->legacy('Withdrawal of Consent ...'));
    $shyButton = $f->button()->shy('Terms Of Service', '#');
    $footer = $footer->withAdditionalModalAndTrigger($roundTripModal, $shyButton);

    return $footer;
}

if ($_GET['new_footer_2_ui'] == '1') {
    chdir('../../../../../');
    require_once('src/UI/examples/Layout/Page/Standard/ui.php');
    require_once('src/UI/examples/MainControls/Footer/footer.php');

    _initIliasForPreview();

    global $DIC;

    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $logo = $f->image()->responsive("templates/default/images/HeaderIcon.svg", "ILIAS");
    $breadcrumbs = pageFooterDemoCrumbs($f);
    $metabar = pageFooterDemoMetabar($f);
    $mainbar = pageFooterDemoMainbar($f, $renderer);
    $footer = pageFooterDemo2Footer($f);

    $entries = $mainbar->getEntries();
    $tools = $mainbar->getToolEntries();
    $content = pageFooterDemoContent($f, $renderer, $mainbar);

    $page = $f->layout()->page()->standard(
        $content,
        $metabar,
        $mainbar,
        $breadcrumbs,
        $logo,
        $footer,
        'UI PAGE FOOTER DEMO', //page title
        'ILIAS', //short title
        'Std. Page Footer Demo' //view title
    )->withUIDemo(true);

    echo $renderer->render($page);
}
