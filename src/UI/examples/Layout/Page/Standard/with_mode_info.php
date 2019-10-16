<?php
include_once "ui.php";

function with_mode_info()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $url = 'src/UI/examples/Layout/Page/Standard/with_mode_info.php?new_ui=2';
    $btn = $f->button()->standard('See UI in fullscreen-mode', $url);

    return $renderer->render($btn);
}

if ($_GET['new_ui'] == '2') {
    _initIliasForPreview();

    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $logo = $f->image()->responsive("src/UI/examples/Image/HeaderIconLarge.svg", "ILIAS");
    $breadcrumbs = pagedemoCrumbs($f);
    $content = pagedemoContent($f);
    $metabar = pagedemoMetabar($f);
    $mainbar = pagedemoMainbar($f, $renderer)
        ->withActive("pws")/**
     * You can also activate a tool initially, e.g.:
     * ->withActive("tool2")
     */
    ;

    $footer = pagedemoFooter($f);

    $mode = $f->mainControls()->modeInfo("With Mode", $f->button()->close());

    $page = $f->layout()->page()->standard(
        $content,
        $metabar,
        $mainbar,
        $breadcrumbs,
        $logo,
        $footer,
        'UI PAGE DEMO'
    );
    $page = $page->withModeInfo($mode);

    $page = $page->withUIDemo(true);

    echo $renderer->render($page);
}
